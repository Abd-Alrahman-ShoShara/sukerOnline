<?php

namespace App\Http\Controllers;

use App\Models\ClassificationProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{

    public function AddProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:products',
            'price' => 'required|numeric',
            'description' => 'required',
            'is_public' => 'required|boolean',
            'classifications' => 'required_if:is_public,false|array',
            'classifications.*' => 'required_if:is_public,false|string',
            'type' => 'nullable|string',
            'points' => 'required|integer',
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

        $product = Product::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'type' => $request->type,
            'points' => $request->points,
            'description' => $request->input('description'),
            'images' => json_encode($images),
            'is_public' => $request->is_public,
        ]);

        if (!$request->is_public) {
            foreach ($request->input('classifications') as $classification) {
                ClassificationProduct::create([
                    'classification_id' => $classification,
                    'product_id' => $product->id,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }




     public function ProdctsDetails($product_id)
    {
        $iteam = ClassificationProduct::where('product_id', $product_id)->with('product', 'classification')->get();

        if (!$iteam) {
            return response()->json([
                'message' => 'no iteam to desplay',

            ], 404);
        }
        return response()->json([

            'the prodct:' => $iteam,
        ], 200);
    }


    public function updateProduct(Request $request, $product_id)
    {
        $product = Product::findOrFail($product_id);

        $request->validate([
            'name' => 'required|unique:products,name,' . $product->id,
            'price' => 'required|numeric',
            'description' => 'required',
            'is_public' => 'required|boolean',
            'classifications' => 'required_if:is_public,false|array',
            'classifications.*' => 'required_if:is_public,false|string',
            'type' => 'nullable|string',
            'points' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:4096',
        ]);

        $imageUrls = [];
        if ($request->hasFile('images')) {
            // Delete old images
            $oldImages = json_decode($product->images, true);
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
            $imageUrls = json_decode($product->images, true);
        }

        
        $product->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'type' => $request->type,
            'points' => $request->points,
            'description' => $request->input('description'),
            'is_public' => $request->is_public,
            'images' => $imageUrls ? json_encode($imageUrls) : null,
        ]);

        
        ClassificationProduct::where('product_id', $product->id)->delete();
        if (!$request->is_public) {
            foreach ($request->input('classifications') as $classification) {
                ClassificationProduct::create([
                    'classification_id' => $classification,
                    'product_id' => $product->id,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function deleteProduct($product_id)
    {
        $product = Product::findOrFail($product_id);
        $product->delete();


        return response()->json(['message' => 'Product deleted successfully'], 200);
    }


    ////////////////////////////////////////

    public function sukerProductsAdmin()
    {
        $sukerProducts = Product::where('type', 'essential')->get();
  
        if ($sukerProducts->isEmpty()) {
            return response()->json([
                'message' => 'There are no essential products',
            ], 404);
        }

        return response()->json([
            'theEssentialProducts' => $sukerProducts,
        ], 200);
    }
    public function ExtraProductsAdmin()
    {
        $extraProducts = Product::where('type', 'extra')->get();

        if ($extraProducts->isEmpty()) {
            return response()->json([
                'message' => 'There are no extra products',
            ], 404);
        }

        return response()->json([
            'theExtraProducts' => $extraProducts,
        ], 200);
    }
    //////////////////////////////////////

    public function sukerProducts()
    {
        $user = Auth::user();
        $classification_id = $user->classification_id;

        $products = Product::where('displayOrNot', true)
            ->where(function ($query) use ($classification_id) {
                $query->where('is_public', true)
                    ->orWhereHas('classification', function ($q) use ($classification_id) {
                        $q->where('classification_id', $classification_id);
                        // ->where('displayOrNot', true);
                    });
            })
            ->where('type', 'essential')
            ->get();
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'There are no essential or public products that are set to display for your classification',
            ], 404);
        }

        return response()->json([
            'theEssentialProducts' => $products,
        ], 200);
    }


    public function ExtraProducts()
    {
        $user = Auth::user();
        $classification_id = $user->classification_id;

        $products = Product::where('displayOrNot', true)
            ->where(function ($query) use ($classification_id) {
                $query->where('is_public', true)
                    ->orWhereHas('classification', function ($q) use ($classification_id) {
                        $q->where('classification_id', $classification_id);
                        // ->where('displayOrNot', true);
                    });
            })
            ->where('type', 'extra')
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'There are no extra or public products that are set to display for your classification',
            ], 404);
        }

        return response()->json([
            'theExtraProducts' => $products,
        ], 200);
    }



    public function onOffProduct($product_id)
    {
        $classificationProduct = ClassificationProduct::findOrFail($product_id);

        $classificationProduct->update([
            'displayOrNot' => !$classificationProduct->displayOrNot,
        ]);

        return response()->json([
            'afterUpdate' => $classificationProduct->fresh(),
        ]);
    }
}


