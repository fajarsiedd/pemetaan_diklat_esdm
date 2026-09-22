<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelatihan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_diklat')->unique();
            $table->string('judul');
            $table->string('jenjang');
            $table->string('subsektor');
            $table->json('target_jabatan');
            $table->enum('kategori', ['technical', 'legal', 'commercial', 'soft_skill']);
            $table->enum('status', ['wajib', 'opsional'])->default('opsional');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihan');
    }
};