<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseCleanupMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_migrations_drop_removed_feature_schema_without_losing_products_users_or_activity_logs(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('name');
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->restrictOnDelete();
        });

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Legacy category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Legacy supplier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Legacy product',
            'qty' => 17,
            'price' => 20.50,
            'description' => 'Must remain after cleanup.',
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $activityId = DB::table('activity_logs')->insertGetId([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'stock.stock_in',
            'subject_type' => 'App\\Models\\Product',
            'subject_id' => $productId,
            'subject_name' => 'Legacy product',
            'properties' => json_encode(['quantity' => 4]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::create('stock_movements', function (Blueprint $table): void {
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
        DB::table('stock_movements')->insert([
            'product_id' => $productId,
            'product_name' => 'Legacy product',
            'movement_type' => 'stock_in',
            'quantity' => 4,
            'previous_qty' => 13,
            'resulting_qty' => 17,
            'reason' => 'Retained only in activity history after cleanup.',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dropMovements = require database_path('migrations/2026_11_09_000005_drop_stock_movements_table.php');
        $dropCategoriesAndSuppliers = require database_path('migrations/2026_11_09_000006_drop_categories_and_suppliers.php');
        $dropMovements->up();
        $dropCategoriesAndSuppliers->up();

        $this->assertFalse(Schema::hasTable('stock_movements'));
        $this->assertFalse(Schema::hasTable('categories'));
        $this->assertFalse(Schema::hasTable('suppliers'));
        $this->assertFalse(Schema::hasColumn('products', 'category_id'));
        $this->assertFalse(Schema::hasColumn('products', 'supplier_id'));
        $this->assertTrue(Schema::hasColumn('products', 'qty'));
        $this->assertTrue(Schema::hasTable('activity_logs'));
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'qty' => 17,
            'description' => 'Must remain after cleanup.',
        ]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => User::ROLE_ADMIN]);
        $this->assertDatabaseHas('activity_logs', [
            'id' => $activityId,
            'action' => 'stock.stock_in',
            'subject_name' => 'Legacy product',
        ]);
    }
}
