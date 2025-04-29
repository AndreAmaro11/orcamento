<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("comments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade"); // User who made the comment
            $table->foreignId("cost_center_id")->constrained()->onDelete("cascade"); // Context: Cost Center
            $table->foreignId("account_id")->constrained()->onDelete("cascade"); // Context: Account
            $table->integer("year"); // Context: Year
            $table->integer("month"); // Context: Month
            $table->string("entry_type"); // Context: 'budget' or 'forecast'
            $table->text("comment"); // The comment text
            $table->timestamps(); // Created_at and Updated_at

            // Add indexes for faster querying
            $table->index(["cost_center_id", "account_id", "year", "month", "entry_type"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("comments");
    }
};
