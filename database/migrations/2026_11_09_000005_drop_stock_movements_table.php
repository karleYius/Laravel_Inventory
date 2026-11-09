<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('stock_movements');
    }

    public function down(): void
    {
        // Rollback restores the empty schema; deleted movement history cannot be recovered.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('movement_type', 20);
            $table->integer('quantity');
            $table->integer('previous_qty');
            $table->integer('resulting_qty');
            $table->text('reason');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }
};
