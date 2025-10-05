package main

// A tiny proxy & user-agent rotation helper.
// Keep it intentionally small and readable.

import (
    "context"
    "encoding/json"
    "log"
    "math/rand"
    "net/http"
    "os"
    "os/signal"
    "strings"
    "sync"
    "sync/atomic"
    "syscall"
    "time"
)

// rotation is the payload returned to clients requesting a proxy & user agent.
type rotation struct {
    Proxy     *string `json:"proxy"`
    UserAgent string  `json:"user_agent"`
}

// banRequest is the body accepted by /v1/ban.
type banRequest struct {
    Proxy string `json:"proxy"`
}

// splitEnv returns a cleaned slice (comma separated env var).
func splitEnv(envKey string) []string {
    rawValue := strings.TrimSpace(os.Getenv(envKey))
    if rawValue == "" {
        return nil
    }
    splitParts := strings.Split(rawValue, ",")
    cleanedValues := make([]string, 0, len(splitParts))
    for _, part := range splitParts {
        trimmedPart := strings.TrimSpace(part)
        if trimmedPart != "" {
            cleanedValues = append(cleanedValues, trimmedPart)
        }
    }
    return cleanedValues
}

// pick returns a random element or fallback.
func pick(items []string, fallback string) string {
    if len(items) == 0 {
        return fallback
    }
    return items[rand.Intn(len(items))]
}

// roundRobinCounter provides atomic index rotation for proxy selection.
var roundRobinCounter atomic.Uint32

func main() {
    rand.Seed(time.Now().UnixNano())

    userAgents := defaultUserAgents(splitEnv("USER_AGENTS"))
    proxies := splitEnv("PROXIES")

    bannedProxies := newBanMap()
    banTimeToLive := 5 * time.Minute

    mux := http.NewServeMux()
    mux.HandleFunc("/v1/rotation", rotationHandler(userAgents, proxies, bannedProxies))
    mux.HandleFunc("/v1/ban", banHandler(bannedProxies, banTimeToLive))
    mux.HandleFunc("/health-check", func(w http.ResponseWriter, _ *http.Request) { w.WriteHeader(http.StatusOK) })

    startServer(mux, ":8081")
}

// defaultUserAgents returns provided or built‑in list.
func defaultUserAgents(existingList []string) []string {
    if len(existingList) != 0 { return existingList }
    return []string{
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15",
        "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0",
    }
}

// banMap wraps a TTL map.
type banMap struct {
    sync.RWMutex
    deadlines map[string]time.Time
}

func newBanMap() *banMap {
    return &banMap{deadlines: make(map[string]time.Time)}
}

// rotationHandler returns a handler closure.
func rotationHandler(userAgents, proxies []string, bannedMap *banMap) http.HandlerFunc {
    return func(w http.ResponseWriter, _ *http.Request) {
        userAgent := pick(userAgents, "Mozilla/5.0")
        proxyPointer := selectProxy(proxies, bannedMap)
        w.Header().Set("Content-Type", "application/json")
        _ = json.NewEncoder(w).Encode(rotation{Proxy: proxyPointer, UserAgent: userAgent})
    }
}

func selectProxy(proxies []string, bannedMap *banMap) *string {
    if len(proxies) == 0 {
        return nil
    }
    attemptBudget := len(proxies)
    for attemptBudget > 0 {
        index := int(roundRobinCounter.Add(1)-1) % len(proxies)
        proxyCandidate := proxies[index]
        if proxyCandidate == "" {
            attemptBudget--
            continue
        }
        if stillBanned := checkAndCleanup(proxyCandidate, bannedMap); !stillBanned {
            return &proxyCandidate
        }
        attemptBudget--
    }
    return nil
}

// checkAndCleanup returns true if still banned, false if usable.
func checkAndCleanup(proxy string, bannedMap *banMap) bool {
    bannedMap.RLock()
    expiryTime, isBanned := bannedMap.deadlines[proxy]
    bannedMap.RUnlock()
    if !isBanned {
        return false
    }
    if time.Now().After(expiryTime) {
        bannedMap.Lock()
        delete(bannedMap.deadlines, proxy)
        bannedMap.Unlock()
        return false
    }
    return true
}

// banHandler registers bans.
func banHandler(bannedMap *banMap, timeToLive time.Duration) http.HandlerFunc {
    return func(w http.ResponseWriter, r *http.Request) {
        if r.Method != http.MethodPost {
            w.WriteHeader(http.StatusMethodNotAllowed)
            return
        }
        var requestPayload banRequest
        if err := json.NewDecoder(r.Body).Decode(&requestPayload); err != nil || requestPayload.Proxy == "" {
            http.Error(w, `{"error":"invalid payload"}`, http.StatusBadRequest)
            return
        }
        bannedMap.Lock()
        bannedMap.deadlines[requestPayload.Proxy] = time.Now().Add(timeToLive)
        bannedMap.Unlock()
        w.WriteHeader(http.StatusNoContent)
    }
}

// startServer bootstraps HTTP server with graceful shutdown.
func startServer(handler http.Handler, addr string) {
    srv := &http.Server{Addr: addr, Handler: handler}
    go func() {
        log.Printf("proxy-service listening on %s", addr)
        if err := srv.ListenAndServe(); err != nil && err != http.ErrServerClosed {
            log.Fatalf("listen error: %v", err)
        }
    }()

    sigCh := make(chan os.Signal, 1)
    signal.Notify(sigCh, syscall.SIGINT, syscall.SIGTERM)
    <-sigCh
    log.Println("shutting down")
    ctx, cancel := context.WithTimeout(context.Background(), 3*time.Second)
    defer cancel()
    _ = srv.Shutdown(ctx)
}
