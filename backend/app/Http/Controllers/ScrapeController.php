<?php

namespace App\Http\Controllers;

use App\Actions\Products\ScrapeProductUrlAction;
use App\Http\Requests\ScrapeProductRequest;
use App\Http\Resources\ProductResource;

class ScrapeController extends Controller
{
    public function store(ScrapeProductRequest $request, ScrapeProductUrlAction $scrape)
    {
        $product = $scrape($request->validated('url'));

        return (new ProductResource($product))->response()->setStatusCode(201);
    }
}
