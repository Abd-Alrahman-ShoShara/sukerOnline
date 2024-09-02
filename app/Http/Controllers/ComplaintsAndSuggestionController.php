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
        $com=$request->type=='complaints'? trans('Complaints.Complaint') : trans('Complaints.suggestion');

        $done = ComplaintsAndSuggestion::create([
            'user_id' => Auth::user()->id,
            'type' => $request->type,
            'body' => $request->body,
        ]);

        if ($done) {
            return response()->json([
                'message' =>trans('Complaints.Created').$com,
                'data' => $done,
            ], 200);
        }

        return response()->json([
            'message' => trans('Complaints.inCreated').$com,
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
                'message' => trans('Complaints.noComplaints')
            ], 200);
           }
       }


    public function ComplaintsOrSuggestionDetails($ComplaintsOrSuggestion_id){
        return response([
            'ComplaintsOrSuggestion' => ComplaintsAndSuggestion::where('id', $ComplaintsOrSuggestion_id)->with('users')->get(),
        ], 200);
        }

    public function ComplaintsOrSuggestionUser(){
        $ComplaintsOrSuggestion=ComplaintsAndSuggestion::where('user_id',Auth::user()->id )->with('users')->get();
        if($ComplaintsOrSuggestion->isEmpty()){

            return response([
                'message'=> trans('Complaints.noComplaints'),
            ], 200);    }

        return response([
            'ComplaintsOrSuggestion'=>$ComplaintsOrSuggestion,
        ], 200);  
      }
}
