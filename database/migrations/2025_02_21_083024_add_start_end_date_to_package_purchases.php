<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('package_purchases', function (Blueprint $table) {
        $table->timestamp('start_date')->nullable();  // Adding start_date
        $table->timestamp('end_date')->nullable();    // Adding end_date
    });
}

public function down()
{
    Schema::table('package_purchases', function (Blueprint $table) {
        $table->dropColumn(['start_date', 'end_date']);
    });
}

};
