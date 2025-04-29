<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Fetch comments for a specific context (via AJAX).
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            "year" => ["required", "integer"],
            "month" => ["required", "integer"],
            "cost_center_id" => ["required", "integer", "exists:cost_centers,id"],
            "account_id" => ["required", "integer", "exists:accounts,id"],
            "entry_type" => ["required", "string", "in:budget,forecast"],
        ]);

        // Check if user has access to the cost center
        if (!Auth::user()->hasCostCenterAccess($validated["cost_center_id"])) {
            return response()->json(["message" => "Acesso não autorizado a este centro de custo."], 403);
        }

        $comments = Comment::where("cost_center_id", $validated["cost_center_id"])
                           ->where("account_id", $validated["account_id"])
                           ->where("year", $validated["year"])
                           ->where("month", $validated["month"])
                           ->where("entry_type", $validated["entry_type"])
                           ->with("user:id,name") // Eager load user name
                           ->orderBy("created_at", "asc")
                           ->get();

        return response()->json($comments);
    }

    /**
     * Store a new comment (via AJAX).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "year" => ["required", "integer"],
            "month" => ["required", "integer"],
            "cost_center_id" => ["required", "integer", "exists:cost_centers,id"],
            "account_id" => ["required", "integer", "exists:accounts,id"],
            "entry_type" => ["required", "string", "in:budget,forecast"],
            "comment" => ["required", "string", "max:1000"],
        ]);

        // Check if user has access to the cost center
        if (!Auth::user()->hasCostCenterAccess($validated["cost_center_id"])) {
            return response()->json(["message" => "Acesso não autorizado a este centro de custo."], 403);
        }

        try {
            $comment = Comment::create([
                "user_id" => Auth::id(),
                "cost_center_id" => $validated["cost_center_id"],
                "account_id" => $validated["account_id"],
                "year" => $validated["year"],
                "month" => $validated["month"],
                "entry_type" => $validated["entry_type"],
                "comment" => $validated["comment"],
            ]);

            // Eager load user for the response
            $comment->load("user:id,name"); 

            return response()->json($comment, 201); // Return the created comment with status 201

        } catch (\Exception $e) {
            // Log error $e->getMessage()
            return response()->json(["message" => "Erro ao salvar o comentário."], 500);
        }
    }
}
