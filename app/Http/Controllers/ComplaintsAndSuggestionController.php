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
    public function ComplaintsOrSuggestionDetails($ComplaintsOrSuggestion_id){
        return response([
            'ComplaintsOrSuggestion' => ComplaintsAndSuggestion::where('id', $ComplaintsOrSuggestion_id)->with('users')->get(),
        ], 200);    }
}
