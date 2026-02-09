<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add delivery_lat and delivery_lng if they don't exist
            if (!Schema::hasColumn('orders', 'delivery_lat')) {
                $table->decimal('delivery_lat', 10, 8)
                      ->nullable()
                      ->after('delivery_address');
            }

            if (!Schema::hasColumn('orders', 'delivery_lng')) {
                $table->decimal('delivery_lng', 11, 8)
                      ->nullable()
                      ->after('delivery_lat');
            }

            // Add delivery_date column
            if (!Schema::hasColumn('orders', 'delivery_date')) {
                $table->date('delivery_date')
                      ->nullable()
                      ->after('delivery_lng');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_lat')) {
                $table->dropColumn('delivery_lat');
            }
            if (Schema::hasColumn('orders', 'delivery_lng')) {
                $table->dropColumn('delivery_lng');
            }
            if (Schema::hasColumn('orders', 'delivery_date')) {
                $table->dropColumn('delivery_date');
            }
        });
    }
};
