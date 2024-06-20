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

    public function ordreOfuser()
    {
        return response([
            'orders' => Order::where('user_id', auth()->user()->id)->get()
        ], 200);
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


//     public function preparingOrder($id)
//     {
//         $accOrder = Order::where(['id' => $id, 'state' => 'pending'])->first();

//         if ($accOrder) {
//             $accOrder->update(['state' => 'preparing']);

//             $phar_id = $accOrder->user_id;
//             $phar = User::find($phar_id);
//             $token = $phar->notiToken;
//             $this->noti("your order is preparing", $token);
//             return response([
//                 'message' => 'the order accepted',
//                 'state' => 'preparing'
//             ], 200);
//         } else {
//             return response()->json(['message' => 'it is not possible to edit due to preparing'], 403);
//         }
//     }

//     public function has_been_sentOrder($order_id)
//     {
//         $accOrder = Order::where([['id', $order_id], ['state', 'preparing']])->first();

//         if ($accOrder) {
//             $accOrder->update(['state' => 'has_been_sent']);
            
//             return response([
//                 'message' => 'the order accepted',
//                 'state' => 'has_been_sent'
//             ], 200);
//         } else {
//             return response()->json(['message' => 'it is not possible to edit due to has_been_sent'], 403);
//         }
//     }

//     public function receivedOrder($id)
//     {
//         $accOrder = Order::where(['id' => $id, 'state' => 'has_been_sent'])->first();

//         if ($accOrder) {
//             $accOrder->update(['state' => 'received']);

//             return response([
//                 'message' => 'the order accepted',
//                 'state' => 'received'
//             ], 200);
//         } else {
//             return response()->json(['message' => 'it is not possible to edit due to received'], 403);
//         }
//     }


//     public function createOrders(Request $request)
// {
//     $user = Auth::user();

//     // Validate the request
//     $request->validate([
//         'products' => 'required|array',
//         'products.*' => 'required|integer|exists:classification_products,id',
//         'type' => 'required|in:urgent,regular,stored',
//     ]);

//     // Check if the order contains only essential products
//     $essentialProductIds = ClassificationProduct::where('classification_id', 1)->pluck('id')->toArray();
//     $orderProductIds = $request->input('products');
//     $isEssentialOnly = count(array_diff($orderProductIds, $essentialProductIds)) === 0;

//     // Determine the order type based on the product types and the user's request
//     if ($isEssentialOnly && $request->input('type') === 'urgent') {
//         $orderType = 'urgent';
//     } elseif ($isEssentialOnly && $request->input('type') === 'stored') {
//         $orderType = 'stored';
//     } else {
//         $orderType = 'regular';
//     }

//     // Create the order
//     $order = Order::create([
//         'user_id' => $user->id,
//         'type' => $orderType,
//         'state' => 'underConstuction',
//     ]);

//     // Add the products to the cart
//     $cartItems = [];
//     foreach ($orderProductIds as $productId) {
//         $cartItems[] = [
//             'order_id' => $order->id,
//             'classificationProduct_id' => $productId,
//             'quantity' => 1, // Assuming default quantity of 1 for now
//         ];
//     }
//     $order->cart()->createMany($cartItems);

//     return response()->json([
//         'message' => 'Order created successfully',
//         'order' => $order,
//     ], 201);
// }

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
