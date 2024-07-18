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
            'number' => 'required|integer',
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
            'number' => $request->input('number'),
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
        $PointsProduct = PointsProduct::findOrFail($pointsProduct_id);

        $PointsProduct->update([
            'displayOrNot' => !$PointsProduct->displayOrNot,
        ]);

        return response()->json([
            'afterUpdate' => $PointsProduct->fresh(),
        ]);
    }

    // public function createPointsOrder(Request $request)
    // {
    //     $request->validate([
    //         'products.*' => 'required|array',
    //         'type' => 'sometimes|in:urgent,regular,stored',
    //     ]);

    //     if (!$request->has('type')) {
    //         $request->merge(['type' => 'regular']);
    //     }

    //     $order = Order::create([
    //         'user_id' => Auth::user()->id,
    //         'type' => $request->type,
    //     ]);
    //     $totalPrice = 0;
    //     $AllQuantity = 0;
    //     foreach ($request->products as $product) {
    //         Cart::create([
    //             'order_id' => $order->id,
    //             'product_id' => $product['product_id'],
    //             'quantity' => $product['quantity'],
    //         ]);
    //         $productPrice = Product::find($product['product_id'])->price;
    //         $totalPrice += $productPrice * $product['quantity'];
    //         $AllQuantity += $product['quantity'];
    //     }
    //     $AllPrice = 0;
    //     if ($request->type == "stored") {

    //         $configPath = config_path('staticPrice.json');
    //         $config = json_decode(File::get($configPath), true);
    //         $storePrice = $config['storePrice'];
    //         $AllPrice = $storePrice * $request->storingTime * $AllQuantity;
    //         StoredOrder::create([
    //             'order_id' => $order->id,
    //             'storingTime' => $request->storingTime,
    //         ]);
    //     }
    //     if ($request->type == "urgent") {

    //         $configPath = config_path('staticPrice.json');
    //         $config = json_decode(File::get($configPath), true);
    //         $urgentPrice = $config['urgentPrice'];
    //         $AllPrice = $urgentPrice * $AllQuantity;
    //     }

    //     $order->totalPrice = $totalPrice + $AllPrice;
    //     $order->save();

    //     return response()->json([
    //         'theOrder' => $order,
    //     ]);
    // }


}
