<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCommerceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontCommerceController extends Controller
{
    public function __construct(private readonly StorefrontCommerceService $commerce) {}

    public function validateCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['present', 'array', 'max:100'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.slug' => ['nullable', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:255'],
            'items.*.size' => ['nullable', 'string', 'max:100'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.price' => ['nullable'],
            'items.*.price_value' => ['nullable', 'numeric'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.family' => ['nullable', 'string', 'max:255'],
            'items.*.image' => ['nullable', 'string', 'max:2048'],
            'items.*.line_key' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'data' => $this->commerce->validateCart($validated['items']),
        ]);
    }

    public function resolveWishlist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['present', 'array', 'max:100'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.slug' => ['nullable', 'string', 'max:255'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.family' => ['nullable', 'string', 'max:255'],
            'items.*.price' => ['nullable'],
            'items.*.price_value' => ['nullable', 'numeric'],
            'items.*.image' => ['nullable', 'string', 'max:2048'],
        ]);

        return response()->json([
            'data' => [
                'items' => $this->commerce->resolveWishlist($validated['items']),
            ],
        ]);
    }
}
