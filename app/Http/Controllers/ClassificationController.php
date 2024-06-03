<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    public function AddClassification(Request $request){
        $attr = $request->validate([
            'name'=>'required|string|unique:classifications',
        ]);
        $classicification = Classification::create([
            'name'=>$request->name,
        ]);
        if(!$classicification){
            return response()->json([
                'message'=>'something wrong',
            ],422);
        }
        return response()->json([
            'message'=>'the classification created successfully',
            'classification'=>$classicification
        ],200);
    }
    public function allClassifications(){
        $classicifications= Classification::all();
        return response()->json([
            'classification'=>$classicifications,
        ],200);
    }

    public function deleteClassification($classification_id)
    {
        $classification = Classification::findOrFail($classification_id);
        $classification->delete();
        return response()->json(['message' => 'Product deleted successfully'],200);
    }
}
