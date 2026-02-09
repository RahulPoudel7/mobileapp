<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
        if (!Schema::hasColumn('orders', 'gift_wrapping_fee')) {
            $table->decimal('gift_wrapping_fee', 8, 2)
                  ->default(0.00)
                  ->after('subtotal');
        }

        if (!Schema::hasColumn('orders', 'personal_note_fee')) {
            $table->decimal('personal_note_fee', 8, 2)
                  ->default(0.00)
                  ->after('gift_wrapping_fee');
        }

        if (!Schema::hasColumn('orders', 'personal_note_text')) {
            $table->text('personal_note_text')
                  ->nullable()
                  ->after('has_personal_note');
        }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'gift_wrapping_fee', 
                'personal_note_fee', 
                'personal_note_text'
            ]);
        });
    }
};
