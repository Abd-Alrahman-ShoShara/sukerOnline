<?php

namespace App\Http\Controllers;

use App\Models\PointsCart;
use App\Models\PointsOrder;
use App\Models\PointsProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;

class PointsOrderController extends Controller
{
    
  
    
    public function createPointsOrder(Request $request)
    {
        $request->validate([
            'products.*' => 'required|array',
        ]);
    
        $user = Auth::user();
        $totalPrice = 0;
    
        foreach ($request->products as $product) {
            $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
            if ($product['quantity'] > $pointsProduct->quantity) {
                return response()->json([
                    'message' => ' The quantity of the product with ID ' . $pointsProduct->id . ' is not available.',
                ], 400);
            }
            $productPrice = $pointsProduct->price;
            $totalPrice += $productPrice * $product['quantity'];
        }
    
        if ($user->userPoints >= $totalPrice) {
            $pointsOrder = PointsOrder::create([
                'user_id' => $user->id,
            ]);
            
            $user->userPoints -= $totalPrice;
            $user->save();
    
            foreach ($request->products as $product) {
                PointsCart::create([
                    'pointsOrders_id' => $pointsOrder->id,
                    'pointsProduct_id' => $product['pointsProduct_id'],
                    'quantity' => $product['quantity'],
                ]);
    
                $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
                $pointsProduct->decrement('quantity', $product['quantity']);
            }
    
            return response()->json([
                'message'=>'created successfully',
                'theOrder' => $pointsOrder->load('pointCarts'),
            ]);
        } else {
            return response()->json([
                'message' => 'Your points do not have enough.',
            ], 400);
        }
        
    }
    
    
    public function pointsOrderDetails($pointsOrder_id)
    {
        $pointsOrder = PointsOrder::with('pointCarts.pointsProduct:id,name,price')
            ->whereId($pointsOrder_id)
            ->first();
    
        if (!$pointsOrder) {
            return response()->json([
                'message' => 'There is no order to show',
            ], 404);
        }
    
        return response()->json([
            'pointsOrder' => $pointsOrder
        ], 200);
    }
    
        public function deletePointsOrder($pointsOrder_id)
        {
            $pointsOrder = PointsOrder::where(['id' => $pointsOrder_id, 'state' => 'pending'])->first();
    
            if (!$pointsOrder) {
                return response()->json([
                    'message' => 'You cannot remove the order',
                ], 200);
            }
            $pointsOrder->delete();
            return response()->json([
                'message' => 'The order was deleted successfully',
            ], 200);
        }

        public function updatePointsOrder(Request $request, $pointsOrder_id)
        {
            $pointsOrder = PointsOrder::find($pointsOrder_id);
        
            if (!$pointsOrder || !in_array($pointsOrder->state, ['pending', 'preparing'])) {
                return response()->json(['error' => 'Order cannot be updated in its current state.'], 403);
            }
        
            $request->validate([
                'products.*' => 'required|array',
            ]);
        
            $user = User::find($pointsOrder->user_id);
            if (!$user) {
            return response()->json(['error' => 'User not found for this order.'], 404);
            }
            $totalPrice = 0;
        
            
            PointsCart::where('pointsOrders_id', $pointsOrder_id)->delete();
        
            foreach ($request->products as $product) {
                $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
                if ($product['quantity'] > $pointsProduct->quantity) {
                    return response()->json([
                        'message' => 'The quantity of the product with ID ' . $pointsProduct->id . ' is not available.',
                    ], 400);
                }
                $productPrice = $pointsProduct->price;
                $totalPrice += $productPrice * $product['quantity'];
            }
        
            $user->userPoints += $pointsOrder->totalPrice;
            $user->save();
        
            $pointsOrder->totalPrice = $totalPrice;
            $pointsOrder->save();
        
            foreach ($request->products as $product) {
                PointsCart::create([
                    'pointsOrders_id' => $pointsOrder_id,
                    'pointsProduct_id' => $product['pointsProduct_id'],
                    'quantity' => $product['quantity'],
                ]);
        
                $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
                $pointsProduct->decrement('quantity', $product['quantity']);
            }
        
            $user->userPoints -= $totalPrice;
            $user->save();
        
            return response()->json([
                'theOrder' => $pointsOrder,
            ]);
        }
    
        public function pointsOrdersOfUser()
{
    $user = auth()->user();
    $pointsOrder = PointsOrder::where('user_id', $user->id)->get();

    return response()->json([
        'pointsOrder' => $pointsOrder
    ], 200);
}

public function preparingPointsOrder($pointsOrder_id)
{
    $pointsOrder = PointsOrder::where(['id' => $pointsOrder_id, 'state' => 'pending'])->first();

    if ($pointsOrder) {
        $pointsOrder->update(['state' => 'preparing']);
        return response()->json([
            'message' => 'Order state updated successfully'
        ], 200);
    } else {
        return response()->json([
            'message' => 'Unable to update the order due to its current state'
        ], 403);
    }
}

public function sentPointsOrder($pointsOrder_id)
{
    $pointsOrder = PointsOrder::where([['id', $pointsOrder_id], ['state', 'preparing']])->first();

    if ($pointsOrder) {
        $pointsOrder->update(['state' => 'sent']);

        return response()->json([
            'message' => 'Order has been sent',
            'state' => 'sent'
        ], 200);
    } else {
        return response()->json([
            'message' => 'Unable to update the order due to its current state'
        ], 403);
    }
}

public function receivedPointsOrder($pointsOrder_id)
{
    $pointsOrder = PointsOrder::where(['id' => $pointsOrder_id, 'state' => 'sent'])->first();

    if ($pointsOrder) {
        $pointsOrder->update(['state' => 'received']);

        return response()->json([
            'message' => 'Order has been received',
            'state' => 'received'
        ], 200);
    } else {
        return response()->json([
            'message' => 'Unable to update the order due to its current state'
        ], 403);
    }
}
    
    //     public function reportUserOrders(Request $request)
    //     {
    //         $request->validate([
    //             'date' => 'required_without_all:start_date,end_date|date|date_format:Y-m-d',
    //             'start_date' => 'required_with:end_date|date|date_format:Y-m-d',
    //             'end_date' => 'required_with:start_date|date|date_format:Y-m-d|after_or_equal:start_date',
    //         ]);
        
    //         $userId = Auth::user()->id;
        
    //         if ($request->has('date')) {
    //             $orders = Order::where('user_id', $userId)
    //                 ->whereDate('created_at', $request->date)
    //                 ->get();
    //         } else {
    //             $startDate = $request->start_date;
    //             $endDate = $request->end_date;
    //             $orders = Order::where('user_id', $userId)
    //                 ->whereBetween('created_at', [
    //                     $startDate . ' 00:00:00',
    //                     $endDate . ' 23:59:59'
    //                 ])
    //                 ->get();
    //         }
        
    //         if ($orders->isEmpty()) {
    //             return response()->json(['message' => 'No orders found for the specified date or date range.']);
    //         }
        
    //         $report = $orders->map(function ($order) {
    //             $items = Cart::where('order_id', $order->id)->get()->map(function ($item) {
    //                 $product = Product::find($item->product_id);
    //                 return [
    //                     'product_id' => $item->product_id,
    //                     'product_name' => $product->name,
    //                     'quantity' => $item->quantity,
    //                     'price' => $product->price,
    //                     'total' => $product->price * $item->quantity,
    //                 ];
    //             });
        
    //             return [
    //                 'order_id' => $order->id,
    //                 'user_id' => $order->user_id,
    //                 'type' => $order->type,
    //                 'totalPrice' => $order->totalPrice,
    //                 'created_at' => $order->created_at->format('Y-m-d H:i:s'),
    //                 'items' => $items,
    //             ];
    //         });
        
    //         return response()->json([
    //             'report' => $report
    //         ]);
    //     }
    //     public function reportAdminOrdersBetweenDates(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date|date_format:Y-m-d',
    //         'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
    //     ]);
    
    //     $startDate = $request->start_date;
    //     $endDate = $request->end_date;
    
    //     $orders = Order::whereBetween('created_at', [
    //         $startDate . ' 00:00:00',
    //         $endDate . ' 23:59:59'
    //     ])->get();
    
    //     if ($orders->isEmpty()) {
    //         return response()->json(['message' => 'No orders found in the specified date range.']);
    //     }
    
    //     $report = $orders->map(function ($order) {
    //         $items = Cart::where('order_id', $order->id)->get()->map(function ($item) {
    //             $product = Product::find($item->product_id);
    //             return [
    //                 'product_id' => $item->product_id,
    //                 'quantity' => $item->quantity,
    //                 'price' => $product->price,
    //                 'total' => $product->price * $item->quantity,
    //             ];
    //         });
    
    //         return [
    //             'order_id' => $order->id,
    //             'user_id' => $order->user_id,
    //             'type' => $order->type,
    //             'totalPrice' => $order->totalPrice,
    //             'created_at' => $order->created_at->format('Y-m-d H:i:s'),
    //             'items' => $items,
    //         ];
    //     });
    
    //     return response()->json([
    //         'report' => $report
    //     ]);
    // }
    // public function orderByState(Request $request, $user_id)
    // {
    //     $request->validate([
    //         'state' => 'required|in:pending,preparing,sent,received',
    //     ]);
    
    //     $orders = Order::where('user_id', $user_id)
    //         ->orderBy('state', $request->state == 'pending' ? 'ASC' : 'DESC')
    //         ->get();
    
    //     $sortedOrders = [];
    //     foreach ($orders as $order) {
    //         if ($order->state == $request->state) {
    //             array_unshift($sortedOrders, $order);
    //         } else {
    //             $sortedOrders[] = $order;
    //         }
    //     }
    
    //     return response()->json(['orders' => $sortedOrders]);
    // }
    // public function orderByStateForAdmin(Request $request)
    // {
    //     $request->validate([
    //         'state' => 'required|in:pending,preparing,sent,received',
    //     ]);
    
    //     $orders = Order::orderBy('state', $request->state == 'pending' ? 'ASC' : 'DESC')
    //         ->get();
    
    //     $sortedOrders = [];
    //     foreach ($orders as $order) {
    //         if ($order->state == $request->state) {
    //             array_unshift($sortedOrders, $order);
    //         } else {
    //             $sortedOrders[] = $order;
    //         }
    //     }
    
    //     return response()->json(['orders' => $sortedOrders]);
    // }
    
   
}
