<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ClassificationProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoredOrder;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function createEssentialOrder(Request $request)
    {
        $request->validate([
            'products.*' => 'required|array',
            'type' => 'sometimes|in:urgent,regular,stored',
            'storingTime' => 'required_if:type,stored|integer'
        ]);
        
        if (!$request->has('type')) {
            $request->merge(['type' => 'regular']);
        }
        
        $order = Order::create([
            'user_id' => Auth::user()->id,
            'type'=>$request->type,
        ]);
        $totalPrice=0;
        $AllQuantity=0;
        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
            $productPrice = Product::find($product['product_id'])->price;
            $totalPrice += $productPrice * $product['quantity'];
            $AllQuantity+=$product['quantity'];
        }
        $AllPrice=0;
        if($request->type=="stored"){

            $configPath = config_path('staticPrice.json');
            $config = json_decode(File::get($configPath), true);
            $storePrice= $config['storePrice'];
            $AllPrice= $storePrice * $request->storingTime * $AllQuantity;
            StoredOrder::create([
                'order_id'=>$order->id,
                'storingTime'=>$request->storingTime,
            ]);
        }
        if($request->type=="urgent"){

            $configPath = config_path('staticPrice.json');
            $config = json_decode(File::get($configPath), true);
            $urgentPrice= $config['urgentPrice'];
            $AllPrice= $urgentPrice * $AllQuantity;
        }
        
        $order->totalPrice = $totalPrice +$AllPrice;
        $order->save();

        return response()->json([
            'theOrder'=>$order,
        ]);
    }
    public function createExtraOrder(Request $request)
    {
        $request->validate([
            'products.*' => 'required|array',
        ]);
       
        $order = Order::create([
            'user_id' => Auth::user()->id,
        ]);
        $totalPrice=0;
        
        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
            $productPrice = Product::find($product['product_id'])->price;
            $totalPrice += $productPrice * $product['quantity'];
        }
        $order->totalPrice = $totalPrice ;
        $order->save();

        return response()->json([
            'theOrder'=>$order,
        ]);
    }

    public function orderDetails($order_id)
    {
        return response([
            'carts' => order::where('id', $order_id)->with('carts.product:id,name,price')->get(),
        ], 200);
    }

    public function deleteOrder($order_id)
    {
        $order = Order::where(['id' => $order_id, 'state' => 'underConstuction'])->first();

        if (!$order) {
            return response()->json([
                'message' => 'You cannot remove the order',
            ], 200);
        }
        $order->delete();
        return response()->json([
            'message' => 'The order was deleted successfully',
        ], 200);
    }

    public function ordreOfuser()
    {
        return response([
            'orders' => Order::where('user_id', auth()->user()->id)->get()
        ], 200);
    }

    public function preparingOrder($order_id)
    {
        $order = Order::where(['id' => $order_id, 'state' => 'underConstuction'])->first();

        if ($order) {
            $order->update(['state' => 'preparing']);
            return response()->json([
                'message' => 'order state is updating successfully'
            ], 403);
        } else {
            return response()->json([
                'message' => 'it is not possible to edit due to preparing'
            ], 403);
        }
    }

    public function sentOrder($order_id)
    {
        $order = Order::where([['id', $order_id], ['state', 'preparing']])->first();

        if ($order) {
            $order->update(['state' => 'sent']);
            
            return response([
                'message' => 'the order accepted',
                'state' => 'has_been_sent'
            ], 200);
        } else {
            return response()->json([
                'message' => 'it is not possible to edit due to has_been_sent'
            ], 403);
        }
    }

    public function receivedOrder($order_id)
    {
        $order = Order::where(['id' => $order_id, 'state' => 'sent'])->first();

        if ($order) {
            $order->update(['state' => 'received']);

            return response([
                'message' => 'the order accepted',
                'state' => 'received'
            ], 200);
        } else {
            return response()->json([
                'message' => 'it is not possible to edit due to received'
            ], 403);
        }
    }



    // public function report(Request $request)
    //     {
    //         $attrs=$request->validate([
    //             'first_date'=>'required|date',
    //             'second_date'=>'required|date',
    //         ]);
    //         if($attrs['first_date'] > $attrs['second_date'])
    //         return response(['message'=>'the first date is greater than the second date']);
    //         $it=auth()->user()->id;
    //         $orders=Order::with('users')
    //         ->whereHas('carts.depotmedicines', function ($query) use ($it) {
    //             $query->where('depot_id',$it);
    //         })
    //         ->whereDate('created_at', '>=',$attrs['first_date'])
    //         ->whereDate('created_at', '<=',$attrs['second_date'])
    //         ->get();

    //         $carts=Depot::with('medicines')
    //         ->whereDate('created_at', '>=',$attrs['first_date'])
    //         ->whereDate('created_at', '<=',$attrs['second_date'])
    //         ->get();

    //         $medicines=Depot::where('depot_id',$it)
    //         ->whereDate('date_of_end', '>=',$attrs['first_date'])
    //         ->whereDate('date_of_end', '<=',$attrs['second_date'])
    //         ->get();

    //         return response([
    //             'message_1'=>'orders during this period',
    //             'orders'=>$orders,
    //             'message_2'=>'mmmm',
    //             'carts'=>$carts,
    //             'message_3'=>'expired medications during this period',
    //             'medicines'=>$medicines

    //         ]);
    //     }
}
