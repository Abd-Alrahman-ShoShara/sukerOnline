<?php

namespace App\Http\Controllers;

use App\Models\PointsProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Services\FirebaseService;

use App\Notifications\FirebasePushNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Storage;

class PointsProductController extends Controller
{
    // public function AddPointsProduct(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|unique:products',
    //         'price' => 'required|numeric',
    //         'description' => 'required|string',
    //         'quantity' => 'required|integer',
    //         'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',

    //     ]);

    //     $imageUrls = [];
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $key => $image) {
    //             $imageName = time() . $key . '.' . $image->extension();
    //             $image->move(public_path('uploads/'), $imageName);
    //             $imageUrls[] = URL::asset('uploads/' . $imageName);
    //         }
    //     } else {
    //         $imageUrls = null;
    //     }

    //     $pointsProduct = PointsProduct::create([
    //         'name' => $request->input('name'),
    //         'price' => $request->input('price'),
    //         'description' => $request->input('description'),
    //         'quantity' => $request->input('quantity'),
    //         'images' => $imageUrls ? json_encode($imageUrls) : null,
    //     ]);

    //     return response()->json([
    //         'message' => trans('Complaints.Created'),
    //         'pointsProduct' => $pointsProduct,
    //     ], 201);
    // }

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
        foreach ($request->file('images') as $image) {
            $path = $image->store('uploads', 'public');
            $imageUrls[] = '/storage/' . $path;
        }
    }

    $pointsProduct = PointsProduct::create([
        'name' => $request->input('name'),
        'price' => $request->input('price'),
        'description' => $request->input('description'),
        'quantity' => $request->input('quantity'),
        'images' => !empty($imageUrls) ? json_encode($imageUrls) : null,
    ]);

    $pointsProduct->images = $pointsProduct->images ? json_decode($pointsProduct->images, true) : [];

    return response()->json([
        'message' => trans('Complaints.Created'),
        'pointsProduct' => $pointsProduct,
    ], 201);
}

public function pointsProductDetails($pointsProduct_id)
{
    $pointsProduct = PointsProduct::find($pointsProduct_id);

    if (!$pointsProduct) {
        return response()->json([
            'message' => 'No item to display',
        ], 404);
    }

    $pointsProduct->images = $pointsProduct->images ? json_decode($pointsProduct->images, true) : [];

    return response()->json([
        'the_product' => $pointsProduct,
    ], 200);
}

    // public function pointsProductDetails($pointsProduct_id)
    // {
    //     $pointsProduct = PointsProduct::find($pointsProduct_id);

    //     if (!$pointsProduct) {
    //         return response()->json([
    //             'message' => 'no iteam to desplay',

    //         ], 404);
    //     }
    //     return response()->json([

    //         'the prodct:' => $pointsProduct,
    //     ], 200);
    // }


    // public function updatePointsProduct(Request $request, $pointsProduct_id)
    // {
    //     $PointsProduct = PointsProduct::findOrFail($pointsProduct_id);

    //     $request->validate([
    //         'name' => 'required|unique:products,name,' . $PointsProduct->id,
    //         'price' => 'required|numeric',
    //         'description' => 'required',
    //         'quantity' => 'required|integer',
    //         'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',
    //     ]);


    //     // Handle image uploads
    //     $imageUrls = [];
    //     if ($request->hasFile('images')) {
    //         // Delete old images
    //         $oldImages = json_decode($PointsProduct->images, true);
    //         if ($oldImages) {
    //             foreach ($oldImages as $oldImage) {
    //                 if (file_exists(public_path($oldImage))) {
    //                     unlink(public_path($oldImage));
    //                 }
    //             }
    //         }

    //         // Upload new images
    //         foreach ($request->file('images') as $key => $image) {
    //             $imageName = time() . $key . '.' . $image->extension();
    //             $image->move(public_path('uploads/'), $imageName);
    //             $imageUrls[] = URL::asset('uploads/' . $imageName);
    //         }
    //     } else {
    //         $imageUrls = json_decode($PointsProduct->images, true);
    //     }


    //     $PointsProduct->update([
    //         'name' => $request->input('name'),
    //         'quantity' => $request->input('quantity'),
    //         'price' => $request->input('price'),
    //         'description' => $request->input('description'),
    //         'images' => $imageUrls ? json_encode($imageUrls) : null,
    //     ]);

    //     return response()->json([
    //         'message' => trans('normalOrder.updated'),
    //         'PointsProduct' => $PointsProduct,
    //     ], 200);
    // }

    public function updatePointsProduct(Request $request, $pointsProduct_id)
{
    $pointsProduct = PointsProduct::findOrFail($pointsProduct_id);

    $request->validate([
        'name' => 'required|unique:products,name,' . $pointsProduct->id,
        'price' => 'required|numeric',
        'description' => 'required|string',
        'quantity' => 'required|integer',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',
    ]);

    $imageUrls = $request->hasFile('images') ? [] : json_decode($pointsProduct->images, true) ?? [];

    if ($request->hasFile('images')) {
        // حذف الصور القديمة
        if ($pointsProduct->images) {
            foreach (json_decode($pointsProduct->images, true) as $oldImage) {
                $path = str_replace('/storage/', '', $oldImage);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        foreach ($request->file('images') as $image) {
            $path = $image->store('uploads', 'public');
            $imageUrls[] = '/storage/' . $path;
        }
    }

    $pointsProduct->update([
        'name' => $request->input('name'),
        'price' => $request->input('price'),
        'description' => $request->input('description'),
        'quantity' => $request->input('quantity'),
        'images' => !empty($imageUrls) ? json_encode($imageUrls) : null,
    ]);

    $pointsProduct->images = $pointsProduct->images ? json_decode($pointsProduct->images, true) : [];

    return response()->json([
        'message' => trans('normalOrder.updated'),
        'pointsProduct' => $pointsProduct,
    ], 200);
}

    public function deletePointsProduct($PointsProduct_id)
    {
        $PointsProduct = PointsProduct::findOrFail($PointsProduct_id);
        $PointsProduct->delete();

        return response()->json(['message' => trans('product.deleteProduct')], 200);
    }

    public function onOffPointsProduct($pointsProduct_id)
    {
        try {
            // Find the product; this will throw an exception if not found
            $product = PointsProduct::findOrFail($pointsProduct_id);
    
            // Toggle the display state
            $product->displayOrNot = !$product->displayOrNot;
            $product->save();
    
            // Check if the product is now displayed
            // if ($product->displayOrNot) {
            //     // Fetch FCM tokens of users to notify
            //     $users = User::where([['role', '1'], ['is_verified', true]])->get();
    
            //     // Send notifications to each user
            //     if ($users->isNotEmpty()) {
            //         $notificationController = new NotificationController(new FirebaseService());
            //         foreach ($users as $user) {
            //             $notificationController->sendPushNotification(
            //                 $user->fcm_token,
            //                 trans('product.Product'),
            //                 trans('product.newProduct'),
            //                 ['NewpointsProduct_id' => $pointsProduct_id]
            //             );
            //         }
            //     }
            // }
    
            $state = $product->displayOrNot ? trans('product.onProduct') : trans('product.offProduct');
    
            return response()->json([
                'message' => $state,
            ]);
        } catch (\Exception $e) {
            // Log the exception message for debugging
            Log::error('Error toggling product display: ' . $e->getMessage());
    
            return response()->json([
                'message' => trans('product.errorOccurred'),
            ], 500);
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
            'PointsProducts' => $PointsProducts,
        ]);
    }

}
