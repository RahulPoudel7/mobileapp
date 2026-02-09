<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'default_delivery_address')) {
                $table->text('default_delivery_address')
                      ->nullable()
                      ->after('phone');
            }

            if (!Schema::hasColumn('users', 'default_delivery_lat')) {
                $table->decimal('default_delivery_lat', 10, 8)
                      ->nullable()
                      ->after('default_delivery_address');
            }

            if (!Schema::hasColumn('users', 'default_delivery_lng')) {
                $table->decimal('default_delivery_lng', 11, 8)
                      ->nullable()
                      ->after('default_delivery_lat');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'default_delivery_address')) {
                $table->dropColumn('default_delivery_address');
            }
            if (Schema::hasColumn('users', 'default_delivery_lat')) {
                $table->dropColumn('default_delivery_lat');
            }
            if (Schema::hasColumn('users', 'default_delivery_lng')) {
                $table->dropColumn('default_delivery_lng');
            }
        });
    }
};
