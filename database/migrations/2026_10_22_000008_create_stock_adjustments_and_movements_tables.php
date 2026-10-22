<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_no')->unique()->index();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('type', 20); // 'addition' or 'subtraction'
            $table->integer('previous_quantity');
            $table->integer('quantity_change'); // positive magnitude
            $table->integer('new_quantity');
            $table->string('reason', 50); // damaged, lost, opening_stock, manual_correction, expired, recount, other
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('type', 30)->index(); // 'purchase', 'sale', 'adjustment'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable()->index();
            $table->integer('previous_stock');
            $table->integer('quantity_change'); // can be negative (sale/subtraction) or positive (purchase/addition)
            $table->integer('new_stock');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_adjustments');
    }
};
