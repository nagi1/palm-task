<?php

namespace App\Actions\Products;

use App\Enums\ProductSource;
use App\Models\Product;

class StoreProductAction
{
    /**
     * Persist a product (create or update) based on identity rules.
     */
    public function __invoke(array $data): Product
    {
        $source = $this->normalizeSource($data['source'] ?? null);
        $identity = $this->resolveIdentity($data, $source);
        $attributes = $this->buildAttributes($data, $source);

        return $identity
            ? Product::updateOrCreate($identity, $attributes)
            : Product::create($attributes);
    }

    private function normalizeSource(mixed $source): ?string
    {
        return $source instanceof ProductSource ? $source->value : ($source ?: null);
    }

    private function resolveIdentity(array $data, ?string $source): array
    {
        return $this->asinIdentity($data, $source)
            ?? $this->urlIdentity($data)
            ?? [];
    }

    private function asinIdentity(array $data, ?string $source): ?array
    {
        return (! empty($data['asin']) && $source)
            ? ['source' => $source, 'asin' => $data['asin']]
            : null;
    }

    private function urlIdentity(array $data): ?array
    {
        return ! empty($data['url'])
            ? ['url' => $data['url']]
            : null;
    }

    private function buildAttributes(array $data, ?string $source): array
    {
        return [
            'title' => $data['title'] ?? 'Unknown',
            'price' => $data['price'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'currency' => $data['currency'] ?? null,
            'source' => $source,
            'asin' => $data['asin'] ?? null,
            'url' => $data['url'] ?? null,
            'raw_payload' => $data['raw_payload'] ?? null,
        ];
    }
}
