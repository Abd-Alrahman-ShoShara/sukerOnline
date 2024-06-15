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
            'description' => 'required',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:png,jpg,jpeg,webp',

        ]);

        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid() . '.' . $extension;
                $path = 'uploads/Products/';
                $file->move($path, $filename);
                $images[] = $filename;
            }
        }

        $pointsProduct = PointsProduct::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'description' => $request->input('description'),
            'images' => json_encode($images),
        ]);


        return response()->json([
            'message' => 'Product created successfully',
            'pointsProduct' => $pointsProduct,
        ], 201);
    }




    public function pointsProdctDetails($pointsProduct_id)
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


    public function updatePointsProdct(Request $request, $pointsProduct_id)
    {
        $PointsProduct = PointsProduct::findOrFail($pointsProduct_id);

        $request->validate([
            'name' => 'required|unique:products,name,' . $PointsProduct->id,
            'price' => 'required|numeric',
            'description' => 'required',

            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',
        ]);

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

    public function deletePointsProdct($PointsProduct_id)
    {
        $PointsProduct = PointsProduct::findOrFail($PointsProduct_id);
        $PointsProduct->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
    public function onOffProduct($pointsProduct_id)
    {
        $PointsProduct = PointsProduct::findOrFail($pointsProduct_id);

        $PointsProduct->update([
            'displayOrNot' => !$PointsProduct->displayOrNot,
        ]);

        return response()->json([
            'afterUpdate' => $PointsProduct->fresh(),
        ]);
    }
}
