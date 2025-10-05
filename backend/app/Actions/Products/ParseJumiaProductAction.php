<?php

namespace App\Actions\Products;

use App\Actions\Products\Concerns\ParsesProductCommon;
use Symfony\Component\DomCrawler\Crawler;

class ParseJumiaProductAction
{
    use ParsesProductCommon;

    public function __invoke(string $html): array
    {
        $crawler = new Crawler($html);
        $title = $this->text($crawler, 'h1.-pbxs');
        $priceText = $this->resolveJumiaPriceText($crawler, $html);
        $price = $this->normalizePrice($priceText);
        $imageUrl = $this->attr($crawler, 'img.-fw', 'data-src');

        return [
            'title' => $title ?: null,
            'price' => $price,
            'image_url' => $imageUrl,
            'asin' => null,
            'currency' => 'EGP',
            'raw_payload' => [
                'price_text' => $priceText,
            ],
        ];
    }

    private function resolveJumiaPriceText(Crawler $crawler, string $html): ?string
    {
        $primary = $this->text($crawler, '.-fs24 .-b');
        if ($primary) {
            return $primary;
        }

        $fallback = $this->firstMatchingText($crawler, [
            'span.-b.-fanin',
            'div.-phs span[data-price]',
            'span.-b.-fs24',
            'span.-fs24.-b',
            'div.-fs20.-b',
        ]);
        if ($fallback) {
            return $fallback;
        }

        $meta = $this->attr($crawler, 'meta[property="product:price:amount"]', 'content');
        if ($meta) {
            return $meta;
        }

        return $this->regexJumiaPrice($html);
    }

    private function regexJumiaPrice(string $html): ?string
    {
        return preg_match('/EGP\s?([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?)/i', $html, $m)
            ? $m[0]
            : null;
    }
}
