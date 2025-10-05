<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScrapeProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', function ($attr, $value, $fail) {
                $allowed = collect(config('scrape.allowed_domains', []))
                    ->filter()->values();
                $host = parse_url($value, PHP_URL_HOST);
                if (! $host) {
                    return $fail('Invalid host.');
                }
                if ($allowed->isNotEmpty() && $allowed->filter(fn ($d) => str_ends_with($host, $d))->isEmpty()) {
                    $fail('Domain not allowed for scraping.');
                }
            }],
        ];
    }
}
