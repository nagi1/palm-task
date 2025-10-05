"use client";
import { useEffect, useState } from 'react';
import { fetchProducts } from '@/lib/api';
import type { Product } from '@/types/product';
import Link from 'next/link';
import { formatPrice } from '@/lib/format';

export function ProductsList() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [lastUpdated, setLastUpdated] = useState<Date | null>(null);

  async function load() {
    try {
      setError(null);
      const data = await fetchProducts();
      setProducts(data);
      setLastUpdated(new Date());
    } catch (e) {
      const message = e instanceof Error ? e.message : 'Failed to load';
      setError(message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    const id = setInterval(load, 30_000); // 30 seconds
    return () => clearInterval(id);
  }, []);

  if (loading && products.length === 0) {
    return <p className="text-sm text-gray-500">Loading products…</p>;
  }

  if (error) {
    return (
      <div className="text-sm text-red-600">
        Error: {error} <button className="ml-2 underline" onClick={load}>Retry</button>
      </div>
    );
  }

  if (products.length === 0) {
    return <p className="text-sm text-gray-500">No products yet. Trigger a scrape via the API.</p>;
  }

  return (
    <div>
      <div className="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        {products.map(p => (
          <Link
            key={p.id}
            href={`/products/${p.id}`}
            className="flex flex-col p-3 transition-colors bg-white border border-gray-200 rounded-lg shadow-sm group focus:outline-none focus:ring-2 focus:ring-blue-500/50 hover:border-blue-300"
            aria-label={`View product ${p.title}`}
          >
            <div className="relative flex items-center justify-center mb-3 overflow-hidden rounded-md h-44 bg-gradient-to-br from-gray-50 to-white ring-1 ring-inset ring-gray-100">
              {p.image_url ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                  src={p.image_url}
                  alt={p.title}
                  className="object-contain max-w-full max-h-full transition-transform duration-300 group-hover:scale-105 drop-shadow-sm"
                  loading="lazy"
                  decoding="async"
                />
              ) : (
                <div className="text-[11px] font-medium tracking-wide text-gray-400">No Image</div>
              )}
              {p.price != null && (
                <span className="absolute bottom-1 right-1 rounded bg-black/70 px-1.5 py-0.5 text-[11px] font-mono text-white backdrop-blur-sm shadow">
                  {formatPrice(p.price, p.currency)}
                </span>
              )}
            </div>
            <h2 className="mb-2 text-sm font-medium text-gray-800 transition-colors line-clamp-3 group-hover:text-blue-700">
              {p.title}
            </h2>
            <p className="text-xs font-medium text-gray-500">
              Added {new Date(p.created_at).toLocaleDateString()}
            </p>
            <p className="mt-auto text-[10px] text-gray-400">Updated {new Date(p.updated_at).toLocaleTimeString()}</p>
          </Link>
        ))}
      </div>
      <div className="flex flex-wrap items-center gap-4 mt-4 text-xs text-gray-500">
        <span className="font-medium text-gray-600">Auto-refresh: 30s</span>
        {lastUpdated && <span>Last updated: {lastUpdated.toLocaleTimeString()}</span>}
        <button
          onClick={load}
          className="px-2 py-1 text-gray-700 transition border rounded hover:bg-gray-50 active:scale-95"
        >
          Refresh now
        </button>
      </div>
    </div>
  );
}
