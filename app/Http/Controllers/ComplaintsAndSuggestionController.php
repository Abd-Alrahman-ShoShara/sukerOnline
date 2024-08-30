<?php

namespace App\Http\Controllers;

use App\Models\ComplaintsAndSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintsAndSuggestionController extends Controller
{
    public function createComplaintsOrSuggestion(Request $request)
    {
        $request->validate([
            "type" => "required|in:complaints,suggestions",
            "body" => "required|string",
        ]);

        $done = ComplaintsAndSuggestion::create([
            'user_id' => Auth::user()->id,
            'type' => $request->type,
            'body' => $request->body,
        ]);

        if ($done) {
            return response()->json([
                'message' => 'Complaint or Suggestion created successfully',
                'data' => $done,
            ], 201);
        }

        return response()->json([
            'message' => 'Failed to create Complaint or Suggestion',
        ], 400);
    }
    public function allComplaintsOrSuggestion(){
       $cc=ComplaintsAndSuggestion::with('users')->get();
       if($cc->isNotEmpty()){

           return response([
               'ComplaintsOrSuggestion' => $cc,
           ], 200);    }
           else{
            return response([
                'message' => 'the is no Complaints or Suggestion',
            ], 200);
           }
       }


    public function ComplaintsOrSuggestionDetails($ComplaintsOrSuggestion_id){
        return response([
            'ComplaintsOrSuggestion' => ComplaintsAndSuggestion::where('id', $ComplaintsOrSuggestion_id)->with('users')->get(),
        ], 200);    }

    public function ComplaintsOrSuggestionUser(){
        $ComplaintsOrSuggestion=ComplaintsAndSuggestion::where('user_id',Auth::user()->id )->with('users')->get();
        if($ComplaintsOrSuggestion->isEmpty()){

            return response([
                'messagw'=>'there is no Complaints Or Suggestion',
            ], 200);    }

        return response([
            'ComplaintsOrSuggestion'=>$ComplaintsOrSuggestion,
        ], 200);    }
}
