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
          Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Link to users table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Total quantity for the order (sum of items)
            $table->integer('quantity')->default(0);

            // Financials
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // Distance (km)
            $table->float('distance_km')->default(0);

            // Payment method (e.g., 'cod')
            $table->enum('payment_method', ['cod','esewa'])->default('cod');

            // Recipient info
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 30)->nullable();

            $table->string('delivery_address');

            // Status and timestamps
            $table->string('status')->default('pending'); // pending, accepted, delivered, cancelled
            $table->timestamp('delivered_at')->nullable();

            // Extras
            $table->boolean('has_personal_note')->default(false);
            $table->boolean('has_gift_wrapping')->default(false);

            $table->timestamps();
          });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
