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
        Schema::create("accounts", function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique(); // e.g., 0.1.1.01.01
            $table->string("name");
            // Optional: Add parent_id for hierarchy if needed, requires self-referencing foreign key
            // $table->foreignId("parent_id")->nullable()->constrained("accounts")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("accounts");
    }
};
