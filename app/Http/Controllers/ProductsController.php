<?php

namespace App\Http\Controllers;

use App\Models\ClassificationProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{
    public function AddProduct1(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:products',
            'price' => 'required|numeric',
            'description' => 'required',
            'classifications' => 'required|array',
            'classifications.*' => 'required|string',
            'type' => 'nullable|string',
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
            'description' => $request->input('description'),
            'images' => json_encode($images),
        ]);
        if ($product) {

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
            'classifications' => 'required|array',
            'classifications.*' => 'required|string',
            'type' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:png,jpg,jpeg,webp',
            'delete_images' => 'nullable|array',
        ]);


        $existingImages = json_decode($product->images, true);
        foreach ($existingImages as $image) {
            $imagePath = 'uploads/Products/' . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // $images = json_decode($product->images, true);
        // Upload new images
        $images = [];
        foreach ($request->file('images') as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = uniqid() . '.' . $extension;
            $path = 'uploads/Products/';
            $file->move($path, $filename);
            $images[] = $filename;
        }

        $product->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'type' => $request->type,
            'description' => $request->input('description'),
            'images' => json_encode($images),
        ]);

     
        ClassificationProduct::where('product_id', $product->id)->delete();
        foreach ($request->input('classifications') as $classification) {
            ClassificationProduct::create([
                'classification_id' => $classification,
                'product_id' => $product->id,
            ]);
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

    $essentialProducts1 = Product::where(function ($q) {
        $q->where('type', 'essential')
        ->orWhere('is_public', true);
    })->get();

    $essentialProducts2 = ClassificationProduct::where('classification_id', $classification_id)
        ->where('displayOrNot', true)
        ->pluck('product_id')
        ->toArray();

    $essentialProducts = $essentialProducts1->whereIn('id', $essentialProducts2);

    $publicProducts = Product::where('type', 'essential')->where('is_public', true)
        ->whereDoesntHave('classification', function ($query) use ($classification_id) {
            $query->where('classification_id', $classification_id)
                ->where('displayOrNot', true);
        })
        ->get();

    $allProducts = $essentialProducts->merge($publicProducts);

    if ($allProducts->isEmpty()) {
        return response()->json([
            'message' => 'There are no essential or public products that are set to display for your classification',
        ], 404);
    }

    return response()->json([
        'theEssentialProducts' => $allProducts,
    ], 200);
}


    public function ExtraProducts()
    {
        $user = Auth::user();
        $classification_id = $user->classification_id;

        $products = Product::where('type', 'extra')
            ->whereHas('classification', function ($query) use ($classification_id) {
                $query->where('classification_products.classification_id', $classification_id)
                    ->where('classification_products.displayOrNot', true);
            })->orWhere('is_public', true)->where('type', 'extra')
            ->get();

        return response()->json([
            'the Extra Products :' => $products
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




//     public function ExtraProducts()
// {
//     $user = Auth::user();
//     $classification_id = $user->classification_id;

//     $ProductsForUser = ClassificationProduct::where('classification_products.classification_id', $classification_id)
//                                             ->where('classification_products.displayOrNot', true)
//                                             ->whereHas('product', function($query) {
//                                                 $query->where('products.type', 'extra');
//                                             })
//                                             ->with('product')
//                                             ->get();

//     if ($ProductsForUser->isEmpty()) {
//         return response()->json([
//             'message' => 'There are no extra products'
//         ], 404);
//     }

//     $extraProducts = $ProductsForUser->map(function($classificationProduct) {
//         return $classificationProduct->product;
//     });

//     return response()->json([
//         'theExtraProducts' => $extraProducts
//     ], 200);
// }


 // public function sukerProducts()
    // {
    //     $user = Auth::user();
    //     $classification_id = $user->classification_id;

    //     $essentialProducts = Product::whereHas('classification', function ($query) use ($classification_id) {
    //         $query->where('classification_id', $classification_id)
    //             ->where('displayOrNot', true);
    //     })
    //         ->where(function ($q) {
    //             $q->where('type', 'essential')
    //                 ->orWhere('is_public', true);
    //         })
    //         ->get();

    //     // Merge the essential products with the public products
    //     $publicProducts = Product::where('is_public', true)
    //         ->whereDoesntHave('classification', function ($query) use ($classification_id) {
    //             $query->where('classification_id', $classification_id)
    //                 ->where('displayOrNot', true);
    //         })
    //         ->get();

    //     $allProducts = $essentialProducts->merge($publicProducts);

    //     if ($allProducts->isEmpty()) {
    //         return response()->json([
    //             'message' => 'There are no essential or public products that are set to display for your classification',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'theEssentialProducts' => $allProducts,
    //     ], 200);
    // }