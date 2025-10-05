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
        $priceText = $this->text($crawler, '#corePriceDisplay_desktop_feature_div span.a-offscreen');
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
