<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->string('jenjang')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->string('jenjang')->nullable(false)->change();
        });
    }
};