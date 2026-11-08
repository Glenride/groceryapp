<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'data' => $addresses,
            'message' => 'Addresses retrieved successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'nullable|string|max:50',
            'street_address' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:50',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_instructions' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        // If setting as default, remove default from other addresses
        if ($request->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = Address::create($data);

        return response()->json([
            'data' => $address,
            'message' => 'Address created successfully',
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'label' => 'nullable|string|max:50',
            'street_address' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:50',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_instructions' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        // If setting as default, remove default from other addresses
        if ($request->is_default && !$address->is_default) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json([
            'data' => $address,
            'message' => 'Address updated successfully',
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }
}
