<?php

namespace App\Http\Controllers;

use App\Models\PointsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PointsProductController extends Controller
{
    public function AddPointsProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:products',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'quantity' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',

        ]);

        $imageUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $image) {
                $imageName = time() . $key . '.' . $image->extension();
                $image->move(public_path('uploads/'), $imageName);
                $imageUrls[] = URL::asset('uploads/' . $imageName);
            }
        } else {
            $imageUrls = null;
        }

        $pointsProduct = PointsProduct::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'description' => $request->input('description'),
            'quantity' => $request->input('quantity'),
            'images' => $imageUrls ? json_encode($imageUrls) : null,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'pointsProduct' => $pointsProduct,
        ], 201);
    }

    public function pointsProductDetails($pointsProduct_id)
    {
        $pointsProduct = PointsProduct::find($pointsProduct_id);

        if (!$pointsProduct) {
            return response()->json([
                'message' => 'no iteam to desplay',

            ], 404);
        }
        return response()->json([

            'the prodct:' => $pointsProduct,
        ], 200);
    }


    public function updatePointsProduct(Request $request, $pointsProduct_id)
    {
        $PointsProduct = PointsProduct::findOrFail($pointsProduct_id);

        $request->validate([
            'name' => 'required|unique:products,name,' . $PointsProduct->id,
            'price' => 'required|numeric',
            'description' => 'required',
            'quantity' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',
        ]);


        // Handle image uploads
        $imageUrls = [];
        if ($request->hasFile('images')) {
            // Delete old images
            $oldImages = json_decode($PointsProduct->images, true);
            if ($oldImages) {
                foreach ($oldImages as $oldImage) {
                    if (file_exists(public_path($oldImage))) {
                        unlink(public_path($oldImage));
                    }
                }
            }

            // Upload new images
            foreach ($request->file('images') as $key => $image) {
                $imageName = time() . $key . '.' . $image->extension();
                $image->move(public_path('uploads/'), $imageName);
                $imageUrls[] = URL::asset('uploads/' . $imageName);
            }
        } else {
            $imageUrls = json_decode($PointsProduct->images, true);
        }


        $PointsProduct->update([
            'name' => $request->input('name'),
            'quantity' => $request->input('quantity'),
            'price' => $request->input('price'),
            'description' => $request->input('description'),
            'images' => $imageUrls ? json_encode($imageUrls) : null,
        ]);

        return response()->json([
            'message' => 'PointsProduct updated successfully',
            'PointsProduct' => $PointsProduct,
        ], 200);
    }

    public function deletePointsProduct($PointsProduct_id)
    {
        $PointsProduct = PointsProduct::findOrFail($PointsProduct_id);
        $PointsProduct->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    public function onOffPointsProduct($pointsProduct_id)
    {
        $product = PointsProduct::find($pointsProduct_id);
        if ($product) {
            $product->displayOrNot = !$product->displayOrNot;
            $product->save();
            $state = $product->displayOrNot ? "the product is on" : "the product is off";
            return response()->json([
                'message' => $state,
            ]);
        } else {
    
            return response()->json([
                'message' => 'there is no product',
            ]);
        }
    }
    public function PointsProducts()
    {
        $PointsProducts = PointsProduct::where('displayOrNot',true)->get();
        $PointsProducts = $PointsProducts->map(function ($product) {
            $product->images = json_decode($product->images);
            return $product;
        });

        return response()->json([
            'PointsProducts' => $PointsProducts,
        ]);
    }
    public function PointsProductsAdmin()
    {
        $PointsProducts = PointsProduct::all();
        $PointsProducts = $PointsProducts->map(function ($product) {
            $product->images = json_decode($product->images);
            return $product;
        });

        return response()->json([
            'product' => $PointsProducts,
        ]);
    }

}
