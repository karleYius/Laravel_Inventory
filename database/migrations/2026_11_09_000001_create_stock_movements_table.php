<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        $timestamp = now();

        DB::table('products')->orderBy('id')->chunk(200, function ($products) use ($timestamp): void {
            foreach ($products as $product) {
                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'movement_type' => 'opening',
                    'quantity' => 0,
                    'previous_qty' => $product->qty,
                    'resulting_qty' => $product->qty,
                    'reason' => 'Opening balance snapshot at tracking launch; no prior movement history is available.',
                    'user_id' => null,
                    'user_name' => 'System migration',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
