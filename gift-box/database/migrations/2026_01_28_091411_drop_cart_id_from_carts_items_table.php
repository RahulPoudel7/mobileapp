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
         Schema::table('carts_items', function (Blueprint $table) {
            // If there is a foreign key on cart_id, drop it by name or column
            // If this line fails, comment it and see the note below
            $table->dropForeign(['cart_id']);

            // Now drop the column
            $table->dropColumn('cart_id');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts_items', function (Blueprint $table) {
            $table->foreignId('cart_id')
                ->constrained('carts')
                ->onDelete('cascade');
        });
    }
};
