<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;

class CartsController extends Controller
{
    //
    public function editCart(Request $request,$id){
        $cart = Cart::find($id);

        $order_id=$cart->order_id;

        $eorder=Order::where('id',$order_id)->where('state','pending')->first();

        if (!$eorder||!$cart) {
            return response()->json([
                'message' => 'You cannot edit the order',
            ], 200);
        }

        $attrs = $request->validate([
            'quantity' => 'required|integer',
        ]);

        $cart->update([
            'quantity' => $attrs['quantity'],
        ]);

        return response()->json([
            'message' => 'The order quantity has been updated',
            'updated_quantity' => $cart,
        ], 200);
    }

    public function deleteCart($id){
        $cart=Cart::find($id);

        $eorder=Order::where('id',$cart->order_id)->where('state','pending')->first();

        if($eorder){
            return response()->json([
                'message'=>'the cart was deleted successfully',
                'deltedone'=>$cart->delete(),
            ],200);

        }
            return response()->json([
                'message'=>'you can not remove the cart',
            ],200);
    }
}
