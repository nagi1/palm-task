<?php

namespace App\Actions\Products;

use App\Actions\Products\Concerns\ParsesProductCommon;
use Symfony\Component\DomCrawler\Crawler;

class ParseAmazonProductAction
{
    use ParsesProductCommon;

    public function __invoke(string $html): array
    {
        $crawler = new Crawler($html);
        $title = $this->text($crawler, '#productTitle');
        $priceText = $this->resolveAmazonPriceText($crawler, $html);
        $price = $this->normalizePrice($priceText);
        $imageUrl = $this->attr($crawler, '#imgTagWrapperId img', 'data-old-hires')
            ?: $this->attr($crawler, '#imgTagWrapperId img', 'src');

        $asin = $this->extractAsin($crawler, $html);
        $currency = $this->guessCurrency($priceText);

        return [
            'title' => $title ?: null,
            'price' => $price,
            'image_url' => $imageUrl,
            'asin' => $asin,
            'currency' => $currency,
            'raw_payload' => [
                'price_text' => $priceText,
            ],
        ];
    }

    private function resolveAmazonPriceText(Crawler $crawler, string $html): ?string
    {
        $primary = $this->text($crawler, '#corePriceDisplay_desktop_feature_div span.a-offscreen');
        if ($primary) {
            return $primary;
        }

        $fallback = $this->firstMatchingText($crawler, [
            '#apex_desktop span.a-offscreen',
            '#corePrice_feature_div span.a-offscreen',
            '#priceblock_ourprice',
            '#priceblock_dealprice',
            'span.a-price.aok-align-center span.a-offscreen',
        ]);
        if ($fallback) {
            return $fallback;
        }

        $meta = $this->attr($crawler, 'meta[property="og:price:amount"]', 'content');
        if ($meta) {
            return $meta;
        }

        $assembled = $this->assembleAmazonFragmentedPrice($crawler);
        if ($assembled) {
            return $assembled;
        }

        return $this->regexAmazonPrice($html);
    }

    private function assembleAmazonFragmentedPrice(Crawler $crawler): ?string
    {
        $whole = $this->text($crawler, 'span.a-price-whole');
        if (! $whole) {
            return null;
        }
        $fraction = $this->text($crawler, 'span.a-price-fraction');
        $wholeClean = rtrim($whole, '.');

        return $wholeClean.'.'.($fraction ?: '00');
    }

    private function regexAmazonPrice(string $html): ?string
    {
        return preg_match('/(?:USD|US\$|\$|EUR|€|GBP|£|AED)\s?([0-9]{1,3}(?:[,.][0-9]{3})*(?:[\.,][0-9]{2})?)/', $html, $m)
            ? $m[0]
            : null;
    }

    private function extractAsin(Crawler $crawler, string $html): ?string
    {
        // Common patterns: URL /dp/ASIN/ or meta tag or data-asin attribute
        if (preg_match('#/dp/([A-Z0-9]{10})#i', $html, $m)) {
            return strtoupper($m[1]);
        }
        $dataAsin = $crawler->filter('[data-asin]')->first();
        if ($dataAsin->count()) {
            $asin = $dataAsin->attr('data-asin');
            if ($asin && preg_match('/^[A-Z0-9]{10}$/i', $asin)) {
                return strtoupper($asin);
            }
        }

        return null;
    }

    private function guessCurrency(string $priceText): ?string
    {
        if (! $priceText) {
            return null;
        }

        return match (true) {
            str_contains($priceText, '€') => 'EUR',
            str_contains($priceText, '£') => 'GBP',
            str_contains($priceText, '¥') => 'JPY',
            str_contains($priceText, 'AED') => 'AED',
            default => (str_contains($priceText, '$') ? 'USD' : null),
        };
    }
}
