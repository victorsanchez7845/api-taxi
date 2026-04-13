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
        //
        Schema::table('openpay_transactions', function (Blueprint $table) {
            // Add a string column that can be null
            $table->string('referal_uuid')->nullable()->after('reservation_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('openpay_transactions', function (Blueprint $table) {
            $table->dropColumn('referal_uuid');
        });
    }
};
