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
        Schema::table('order', function (Blueprint $table) {
            // // 1) Drop foreign key on cart_id if it exists
            // // adjust constraint name if different
            // $table->dropForeign(['cart_id']);

            // // 2) Drop the cart_id column
            // $table->dropColumn('cart_id');

            // // 3) Add order_id instead
            // $table->foreignId('order_id')
            //     ->after('id')
            //     ->constrained('orders')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            // $table->dropForeign(['order_id']);
            // $table->dropColumn('order_id');

            // $table->foreignId('cart_id')
            //     ->constrained('carts')
            //     ->onDelete('cascade');
        });
    }
};
