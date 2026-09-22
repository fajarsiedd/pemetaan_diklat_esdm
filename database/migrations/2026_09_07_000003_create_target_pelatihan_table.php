<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_pelatihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->foreignId('pelatihan_id')->constrained('pelatihan')->cascadeOnDelete();
            $table->unsignedTinyInteger('prioritas');
            $table->enum('status', ['ditargetkan', 'sedang_proses', 'selesai'])->default('ditargetkan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_pelatihan');
    }
};
