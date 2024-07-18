<?php

namespace App\Http\Controllers;

use App\Models\PointsOrder;
use App\Models\PointsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointsOrderController extends Controller
{
    
    public function createPointsOrder(Request $request)
    {
            $validatedData = $request->validate([
                'pointsProduct_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
            ]);
            
            $order = PointsOrder::create([
                'user_id' => Auth::user()->id,
                'pointsProduct_id' => $validatedData['pointsProduct_id'],
                'quantity' => $validatedData['quantity'],
                'ReadOrNot' => false,
            ]);
        
            $product = PointsProduct::findOrFail($validatedData['pointsProduct_id']);
            $product->number -= $validatedData['quantity'];
            $product->save();
        
            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);
        }

}
