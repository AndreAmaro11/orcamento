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
        Schema::create('budget_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->integer('year');
            $table->tinyInteger('month'); // 1 for January, 12 for December
            $table->decimal('value', 15, 2); // Adjust precision and scale as needed
            $table->timestamps();

            // Ensure uniqueness for a given cost center, account, year, and month
            $table->unique(['cost_center_id', 'account_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_entries');
    }
};
