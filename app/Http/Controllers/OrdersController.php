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
            'type' => $request->type,
        ]);
        $totalPrice = 0;
        $AllQuantity = 0;
        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
            $productPrice = Product::find($product['product_id'])->price;
            $totalPrice += $productPrice * $product['quantity'];
            $AllQuantity += $product['quantity'];
        }
        $AllPrice = 0;
        if ($request->type == "stored") {

            $configPath = config_path('staticPrice.json');
            $config = json_decode(File::get($configPath), true);
            $storePrice = $config['storePrice'];
            $AllPrice = $storePrice * $request->storingTime * $AllQuantity;
            StoredOrder::create([
                'order_id' => $order->id,
                'storingTime' => $request->storingTime,
            ]);
        }
        if ($request->type == "urgent") {

            $configPath = config_path('staticPrice.json');
            $config = json_decode(File::get($configPath), true);
            $urgentPrice = $config['urgentPrice'];
            $AllPrice = $urgentPrice * $AllQuantity;
        }

        $order->totalPrice = $totalPrice + $AllPrice;
        $order->save();

        return response()->json([
            'theOrder' => $order,
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
        $totalPrice = 0;

        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
            $productPrice = Product::find($product['product_id'])->price;
            $totalPrice += $productPrice * $product['quantity'];
        }
        $order->totalPrice = $totalPrice;
        $order->save();

        return response()->json([
            'theOrder' => $order,
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
        $order = Order::where(['id' => $order_id, 'state' => 'pending'])->first();

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
    public function updateEssentialOrder(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        if (!$order || !in_array($order->state, ['pending', 'preparing'])) {
            return response()->json(['error' => 'Order cannot be updated in its current state.'], 403);
        }

        $request->validate([
            'products.*' => 'required|array',
            'type' => 'sometimes|in:urgent,regular,stored',
            'storingTime' => 'required_if:type,stored|integer'
        ]);

        if (!$request->has('type')) {
            $request->merge(['type' => 'regular']);
        }

        $order->type = $request->type;
        $order->save();

        $totalPrice = 0;
        $AllQuantity = 0;


        Cart::where('order_id', $order->id)->delete();

        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
            $productPrice = Product::find($product['product_id'])->price;
            $totalPrice += $productPrice * $product['quantity'];
            $AllQuantity += $product['quantity'];
        }

        $AllPrice = 0;

        if ($request->type == "stored") {
            $configPath = config_path('staticPrice.json');
            $config = json_decode(File::get($configPath), true);
            $storePrice = $config['storePrice'];
            $AllPrice = $storePrice * $request->storingTime * $AllQuantity;

            StoredOrder::updateOrCreate(
                ['order_id' => $order->id],
                ['storingTime' => $request->storingTime]
            );
        } else {
            StoredOrder::where('order_id', $order->id)->delete();
        }

        if ($request->type == "urgent") {
            $configPath = config_path('staticPrice.json');
            $config = json_decode(File::get($configPath), true);
            $urgentPrice = $config['urgentPrice'];
            $AllPrice = $urgentPrice * $AllQuantity;
        }

        $order->totalPrice = $totalPrice + $AllPrice;
        $order->save();

        return response()->json([
            'theOrder' => $order,
        ]);
    }

    public function updateExtraOrder(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        if (!$order || !in_array($order->state, ['pending', 'preparing'])) {
            return response()->json(['error' => 'Order cannot be updated in its current state.'], 403);
        }

        $request->validate([
            'products.*' => 'required|array',
        ]);

        $totalPrice = 0;


        Cart::where('order_id', $order->id)->delete();

        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
            $productPrice = Product::find($product['product_id'])->price;
            $totalPrice += $productPrice * $product['quantity'];
        }

        $order->totalPrice = $totalPrice;
        $order->save();

        return response()->json([
            'theOrder' => $order,
        ]);
    }

    public function ordresOfuser()
    {
        return response([
            'orders' => Order::where('user_id', auth()->user()->id)->get()
        ], 200);
    }

    public function preparingOrder($order_id)
    {
        $order = Order::where(['id' => $order_id, 'state' => 'pending'])->first();

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

    public function reportUserOrders(Request $request)
    {
        $request->validate([
            'date' => 'required_without_all:start_date,end_date|date|date_format:Y-m-d',
            'start_date' => 'required_with:end_date|date|date_format:Y-m-d',
            'end_date' => 'required_with:start_date|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);
    
        $userId = Auth::user()->id;
    
        if ($request->has('date')) {
            $orders = Order::where('user_id', $userId)
                ->whereDate('created_at', $request->date)
                ->get();
        } else {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $orders = Order::where('user_id', $userId)
                ->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ])
                ->get();
        }
    
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for the specified date or date range.']);
        }
    
        $report = $orders->map(function ($order) {
            $items = Cart::where('order_id', $order->id)->get()->map(function ($item) {
                $product = Product::find($item->product_id);
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $product->name,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                    'total' => $product->price * $item->quantity,
                ];
            });
    
            return [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'type' => $order->type,
                'totalPrice' => $order->totalPrice,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $items,
            ];
        });
    
        return response()->json([
            'report' => $report
        ]);
    }
    public function reportAdminOrdersBetweenDates(Request $request)
{
    $request->validate([
        'start_date' => 'required|date|date_format:Y-m-d',
        'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
    ]);

    $startDate = $request->start_date;
    $endDate = $request->end_date;

    $orders = Order::whereBetween('created_at', [
        $startDate . ' 00:00:00',
        $endDate . ' 23:59:59'
    ])->get();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found in the specified date range.']);
    }

    $report = $orders->map(function ($order) {
        $items = Cart::where('order_id', $order->id)->get()->map(function ($item) {
            $product = Product::find($item->product_id);
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $product->price,
                'total' => $product->price * $item->quantity,
            ];
        });

        return [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => $order->type,
            'totalPrice' => $order->totalPrice,
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'items' => $items,
        ];
    });

    return response()->json([
        'report' => $report
    ]);
}
public function orderByState(Request $request)
    {
        $request->validate([
           'state'=>'required|in:pending,preparing,sent,received', 
        ]);
        $state = $request->input('state'); 
        $orders = Order::where('state', $state)
            ->orderBy('state') 
            ->get();

        return response()->json(['orders' => $orders]);
    }
}
