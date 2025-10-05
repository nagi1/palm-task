<?php

namespace App\Http\Controllers;

use App\Exceptions\ProductNotFoundException;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        return ProductResource::collection(
            Product::query()->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function show(int $id)
    {
        $product = Product::find($id);
        if (! $product) {
            throw new ProductNotFoundException("Product {$id} not found");
        }

        return new ProductResource($product);
    }
}
