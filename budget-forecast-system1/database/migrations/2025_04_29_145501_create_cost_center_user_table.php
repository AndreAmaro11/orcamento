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
        Schema::create("cost_center_user", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->foreignId("cost_center_id")->constrained("cost_centers")->onDelete("cascade");
            $table->timestamps();

            // Ensure a user isn't assigned to the same cost center multiple times
            $table->unique(["user_id", "cost_center_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("cost_center_user");
    }
};
