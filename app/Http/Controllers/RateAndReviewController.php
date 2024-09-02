<?php

namespace App\Http\Controllers;

use App\Models\RateAndReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RateAndReviewController extends Controller
{
    public function createRateAndReview(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|between:1,5',
            'rateBody' => 'required|string',
        ]);
    
        $done = RateAndReview::create([
            'user_id' => Auth::user()->id,
            'rate' => $request->rate,
            'rateBody' => $request->rateBody,
        ]);
    
        if ($done) {
            return response()->json([
                'message' => trans('product.rated'),
                'Rate' => $done,
            ], 201);
        }
    
        return response()->json([
            'message' => trans('product.unRated'),
        ], 400);
    }
    public function RateAndReviewDetails($RateAndReview_id){
        return response([
            'RateAndReview' => RateAndReview::where('id', $RateAndReview_id)->with('users')->get(),
        ], 200);    
    }
    public function getReviewsUseer(){
        return response([
            'RateAndReview' => RateAndReview::where('displayOrNot', true)->with('users')->get(),
        ], 200);    
    }
    public function getReviewsAdmin(){
        return response([
            'RateAndReview' => RateAndReview::with('users')->get(),
        ], 200);    
    }

    
    public function displayRateOrNot($rateAndReview_id)
    {
        $rateAndReview = RateAndReview::findOrFail($rateAndReview_id);
    
        $rateAndReview->displayOrNot = !$rateAndReview->displayOrNot;
        $rateAndReview->save();
    
        return response()->json([
            'afterUpdate' => $rateAndReview
        ]);
    }
}
