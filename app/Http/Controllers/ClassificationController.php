<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Stichoza\GoogleTranslate\GoogleTranslate;

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
            'message'=>'تم اضافة تصنيف بنجاح',
            'classification'=>$classicification
        ],200);
    }
    public function allClassifications(Request $request){
        $attr = $request->validate([
            'language'=>'required|in:ar,en',
        ]);
        $classicifications= Classification::all();
        $tr=new GoogleTranslate();
        foreach($classicifications as $classicification){

            $name=$tr->setTarget($request->language)->translate($classicification->name);
            $translatedClassifications[] = [
                'id' => $classicification->id,
                'name' => $name,
            ];
        }
        return response()->json([
            'classification'=>$translatedClassifications,
        ],200);
    }

    public function deleteClassification($classification_id)
    {
        $classification = Classification::findOrFail($classification_id);
        $classification->delete();
        return response()->json(['message' => 'تم حذف التصنيف بنجاح'],200);
    }
}
