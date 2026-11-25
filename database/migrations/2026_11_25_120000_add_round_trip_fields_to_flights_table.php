<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->string('trip_type', 20)->default('one_way')->after('status');
            $table->foreignId('return_flight_id')->nullable()->after('trip_type')->constrained('flights')->nullOnDelete();
            $table->boolean('is_return_leg')->default(false)->after('return_flight_id');
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropForeign(['return_flight_id']);
            $table->dropColumn(['trip_type', 'return_flight_id', 'is_return_leg']);
        });
    }
};
