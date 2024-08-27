<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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

        $user = Auth::user();
        $order = Order::create([
            'user_id' => $user->id,
            'type' => $request->type,
        ]);

        $PointsToAdd = 0;
        $totalPrice = 0;
        $AllQuantity = 0;

        foreach ($request->products as $product) {
            Cart::create([
                'order_id' => $order->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);

            $theproduct = Product::find($product['product_id']);
            $productPrice = $theproduct->price;
            $totalPrice += $productPrice * $product['quantity'];
            $AllQuantity += $product['quantity'];
            $PointsToAdd += $theproduct->points * $product['quantity'];
        }

        $user->userPoints += $PointsToAdd;
        $user->save();

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
        $order->points = $PointsToAdd;
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

    $user = auth()->user();
    $order = Order::create([
        'user_id' => $user->id,
    ]);

    $totalPrice = 0;
    $PointsToAdd = 0;

    foreach ($request->products as $product) {
        Cart::create([
            'order_id' => $order->id,
            'product_id' => $product['product_id'],
            'quantity' => $product['quantity'],
        ]);

        $theproduct = Product::find($product['product_id']);
        $productPrice = $theproduct->price;
        $totalPrice += $productPrice * $product['quantity'];
        $PointsToAdd += $theproduct->points * $product['quantity'];
    }

    $order->totalPrice = $totalPrice;
    $order->points = $PointsToAdd;
    $order->save();

    $user->userPoints += $PointsToAdd;
    $user->save();

    return response()->json([
        'theOrder' => $order,
    ]);
}

public function orderDetails($order_id)
{
    $order = Order::where('id', $order_id)->with('carts.product:id,name,price')->first();

    return response()->json([
        'carts' => $order ? $order->carts : [],
    ], 200);
}

public function deleteOrder($order_id)
{
    $order = Order::where(['id' => $order_id, 'state' => 'pending'])->first();

    if (!$order) {
        return response()->json([
            'message' => 'You cannot remove the order',
        ], 403);
    }
    $user = User::find($order->user_id);
    if (!$user) {
        return response()->json(['error' => 'User not found for this order.'], 404);
    }

    // Deduct points from the user
    $user = User::find($order->user_id);
    if (!$user) {
        return response()->json(['error' => 'User not found for this order.'], 404);
    }
    $user->userPoints -= $order->points;
    $user->save();

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
    $PointsToAdd = 0;

    Cart::where('order_id', $order->id)->delete();

    foreach ($request->products as $product) {
        Cart::create([
            'order_id' => $order->id,
            'product_id' => $product['product_id'],
            'quantity' => $product['quantity'],
        ]);

        $theproduct = Product::find($product['product_id']);
        $productPrice = $theproduct->price;
        $totalPrice += $productPrice * $product['quantity'];
        $AllQuantity += $product['quantity'];
        $PointsToAdd += $theproduct->points * $product['quantity'];
    }

    // Adjust user points
    $user = User::find($order->user_id);
    if (!$user) {
        return response()->json(['error' => 'User not found for this order.'], 404);
    }
    $user->userPoints -= $order->points;  // Remove old points
    $user->userPoints += $PointsToAdd;    // Add new points
    $user->save();

    $order->points = $PointsToAdd;  // Update the order points

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
    $PointsToAdd = 0;

    Cart::where('order_id', $order->id)->delete();

    foreach ($request->products as $product) {
        Cart::create([
            'order_id' => $order->id,
            'product_id' => $product['product_id'],
            'quantity' => $product['quantity'],
        ]);

        $theproduct = Product::find($product['product_id']);
        $productPrice = $theproduct->price;
        $totalPrice += $productPrice * $product['quantity'];
        $PointsToAdd += $theproduct->points * $product['quantity'];
    }

    // Adjust user points
    $user = User::find($order->user_id);
    if (!$user) {
        return response()->json(['error' => 'User not found for this order.'], 404);
    }
    $user->userPoints -= $order->points;  // Remove old points
    $user->userPoints += $PointsToAdd;    // Add new points
    $user->save();

    $order->points = $PointsToAdd;  // Update the order points

    $order->totalPrice = $totalPrice;
    $order->save();

    return response()->json([
        'theOrder' => $order,
    ]);
}

    public function ordersOfuser()
    {
        return response([
            'orders' => Order::where('user_id', auth()->user()->id)->get()
        ], 200);
    }

    public function storedOrdersOfuser()
    {
        return response([
            'orders' => Order::where([['user_id', auth()->user()->id],[
                'type','stored'
            ]])->get()
        ], 200);
    }

    public function notStoredOrdersOfuser(Request $request)
    {
        $attrs = $request->validate([
            'sortBy' => 'sometimes|in:newest,pending,preparing,sent,received',
        ]);

        if ($request->has('sortBy')&& $attrs['sortBy'] != 'newest') {
        $order = Order::where([['user_id', auth()->user()->id],['type','!=','stored'],['state',$attrs['sortBy']]])->get();
        }else{
            $order = Order::where([['user_id', auth()->user()->id],['type','!=','stored']])->get();
        }

        $order=$order->sortBy('created_at')->values();

        return response([
            'orders' => $order
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
public function orderByState(Request $request, $user_id)
{
    $request->validate([
        'state' => 'required|in:pending,preparing,sent,received',
    ]);

    $orders = Order::where('user_id', $user_id)
        ->orderBy('state', $request->state == 'pending' ? 'ASC' : 'DESC')
        ->get();

    $sortedOrders = [];
    foreach ($orders as $order) {
        if ($order->state == $request->state) {
            array_unshift($sortedOrders, $order);
        } else {
            $sortedOrders[] = $order;
        }
    }

    return response()->json(['orders' => $sortedOrders]);
}
public function orderByStateForAdmin(Request $request)
{
    $request->validate([
        'state' => 'required|in:pending,preparing,sent,received',
    ]);

    $orders = Order::orderBy('state', $request->state == 'pending' ? 'ASC' : 'DESC')
        ->get();

    $sortedOrders = [];
    foreach ($orders as $order) {
        if ($order->state == $request->state) {
            array_unshift($sortedOrders, $order);
        } else {
            $sortedOrders[] = $order;
        }
    }

    return response()->json(['orders' => $sortedOrders]);

}

public function NormalOrders(Request $request){
    $attrs = $request->validate([
        'sortBy' => 'sometimes|in:newest,pending,preparing,sent,received',
    ]);

    if ($request->has('sortBy')&& $attrs['sortBy'] != 'newest') {
    $orders= Order::where([['type','regular'],['state',$attrs['sortBy']]])->get();
    }else{
        $orders = Order::where('type','regular')->get();
    }
    $orders=$orders->sortBy('created_at')->values();
    if($orders->isEmpty()){

        return response()->json([
            'message'=>'threr is no Order',
        ]);
    }
        return response()->json([
            'reularOrders'=>$orders,
        ]);
    }
}

