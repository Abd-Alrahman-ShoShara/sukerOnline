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
                'totalPrice' => $totalPrice,
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
                'message' => 'created successfully',
                'theOrder' => $pointsOrder->load('pointCarts'),
            ]);
        } else {
            return response()->json([
                'message' => 'Your points does not enough.',
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
    public function updatePointsOrder(Request $request, $order_id)
{
    $request->validate([
        'products.*' => 'required|array',
        'products.*.pointsProduct_id' => 'required|exists:points_products,id',
        'products.*.quantity' => 'required|integer|min:1',
    ]);

    $user = Auth::user();
    $pointsOrder = PointsOrder::find($order_id);

    if (!$pointsOrder || $pointsOrder->user_id !== $user->id || !in_array($pointsOrder->state, ['pending', 'preparing'])) {
        return response()->json([
            'message' => 'Order cannot be updated in its current state.',
        ], 403);
    }

    $currentTotalPrice = $pointsOrder->totalPrice;

    $newTotalPrice = 0;
    $existingProducts = $pointsOrder->pointCarts->keyBy('pointsProduct_id');

    foreach ($request->products as $product) {
        $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
        $availableQuantity = $pointsProduct->quantity + ($existingProducts->get($product['pointsProduct_id'], (object) ['quantity' => 0])->quantity);

        if ($product['quantity'] > $availableQuantity) {
            return response()->json([
                'message' => 'The quantity of the product with ID ' . $pointsProduct->id . ' is not available.',
            ], 400);
        }

        $productPrice = $pointsProduct->price;
        $newTotalPrice += $productPrice * $product['quantity'];

        // Remove from existing products if updating the quantity
        if ($existingProducts->has($product['pointsProduct_id'])) {
            $existingProducts->forget($product['pointsProduct_id']);
        }
    }

    // Return quantities for removed products
    foreach ($existingProducts as $removedProduct) {
        $pointsProduct = PointsProduct::find($removedProduct->pointsProduct_id);
        $pointsProduct->increment('quantity', $removedProduct->quantity);
    }

    $pointsDifference = $newTotalPrice - $currentTotalPrice;

    // Check if the user has enough points for the update
    if ($pointsDifference > 0 && $user->userPoints < $pointsDifference) {
        return response()->json([
            'message' => 'You do not have enough points to update this order.',
        ], 400);
    }

    // Adjust user points
    if ($pointsDifference > 0) {
        $user->userPoints -= $pointsDifference;
    } else {
        // If pointsDifference is negative, add back the difference
        $user->userPoints += abs($pointsDifference);
    }
    $user->save();

    // Update the order
    $pointsOrder->pointCarts()->delete();

    foreach ($request->products as $product) {
        PointsCart::create([
            'pointsOrders_id' => $pointsOrder->id,
            'pointsProduct_id' => $product['pointsProduct_id'],
            'quantity' => $product['quantity'],
        ]);

        $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
        $pointsProduct->decrement('quantity', $product['quantity']);
    }

    // Update the total price of the order
    $pointsOrder->totalPrice = $newTotalPrice;
    $pointsOrder->save();

    return response()->json([
        'message' => 'Order updated successfully',
        'theOrder' => $pointsOrder->load('pointCarts'),
    ]);
}
    // public function updatePointsOrder(Request $request, $pointsOrder_id)
    // {
    //     $pointsOrder = PointsOrder::find($pointsOrder_id);

    //     if (!$pointsOrder || !in_array($pointsOrder->state, ['pending', 'preparing'])) {
    //         return response()->json(['error' => 'Order cannot be updated in its current state.'], 403);
    //     }

    //     $request->validate([
    //         'products.*' => 'required|array',
    //     ]);

    //     $user = User::find($pointsOrder->user_id);
    //     if (!$user) {
    //         return response()->json(['error' => 'User not found for this order.'], 404);
    //     }
    //     $totalPrice = 0;


    //     PointsCart::where('pointsOrders_id', $pointsOrder_id)->delete();

    //     foreach ($request->products as $product) {
    //         $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
    //         if ($product['quantity'] > $pointsProduct->quantity) {
    //             return response()->json([
    //                 'message' => 'The quantity of the product with ID ' . $pointsProduct->id . ' is not available.',
    //             ], 400);
    //         }
    //         $productPrice = $pointsProduct->price;
    //         $totalPrice += $productPrice * $product['quantity'];
    //     }

    //     $user->userPoints += $pointsOrder->totalPrice;
    //     $user->save();

    //     $pointsOrder->totalPrice = $totalPrice;
    //     $pointsOrder->save();

    //     foreach ($request->products as $product) {
    //         PointsCart::create([
    //             'pointsOrders_id' => $pointsOrder_id,
    //             'pointsProduct_id' => $product['pointsProduct_id'],
    //             'quantity' => $product['quantity'],
    //         ]);

    //         $pointsProduct = PointsProduct::find($product['pointsProduct_id']);
    //         $pointsProduct->decrement('quantity', $product['quantity']);
    //     }

    //     $user->userPoints -= $totalPrice;
    //     $user->save();

    //     return response()->json([
    //         'theOrder' => $pointsOrder,
    //     ]);
    // }

    public function pointsOrdersOfUser(Request $request)
    {

        $attrs = $request->validate([
            'sortBy' => 'sometimes|in:pending,preparing,sent,received',
        ]);

        if ($request->has('sortBy')) {
            $pointsOrder = PointsOrder::where([['user_id', auth()->user()->id], ['state', $attrs['sortBy']]])->get();
        } else {
            $pointsOrder = PointsOrder::where([['user_id', auth()->user()->id]])->get();
        }

        $pointsOrder = $pointsOrder->sortByDesc('created_at')->values();

        if ($pointsOrder->isEmpty()) {
            return response()->json([
                'message' => 'threr is no Order',
            ]);
        }

        return response()->json([
            'Orders' => $pointsOrder
        ], 200);
    }

    public function allPointsOrders()
    {
        $pointsOrders = PointsOrder::with('users.classification','pointCarts')->get();

        if ($pointsOrders->isNotEmpty()) {
            return response()->json([
                'Orders' => $pointsOrders
            ], 200);
        } else {
            return response()->json([
                'message' => 'ther is no orders'
            ], 403);
        }
    }

    public function editStateOfPointsOrder(Request $request, $pointsOrder_id)
{
    $request->validate([
        'state' => 'required|in:preparing,sent,received'
    ]);

    $order = PointsOrder::find($pointsOrder_id);

    if ($order) {
        switch ($request->input('state')) {
            case 'preparing':
                if ($order->state == 'pending') {
                    $order->update(['state' => $request->input('state')]);
                    return response()->json([
                        'message' => 'Order state updated successfully'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Order is not in the pending state'
                    ], 403);
                }
            case 'sent':
                if ($order->state == 'preparing') {
                    $order->update(['state' => $request->input('state')]);
                    return response()->json([
                        'message' => 'Order state updated successfully'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Order is not in the preparing state'
                    ], 403);
                }
            case 'received':
                if ($order->state == 'sent') {
                    $order->update(['state' => $request->input('state')]);
                    return response()->json([
                        'message' => 'Order state updated successfully'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Order is not in the sent state'
                    ], 403);
                }
            default:
                return response()->json([
                    'message' => 'Invalid state'
                ], 403);
        }
    } else {
        return response()->json([
            'message' => 'Order not found'
        ], 404);
    }
}


}
