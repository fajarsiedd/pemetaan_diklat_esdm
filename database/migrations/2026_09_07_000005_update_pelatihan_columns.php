<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            if (!Schema::hasColumn('pelatihan', 'target_jabatan')) {
                $table->json('target_jabatan')->nullable()->after('subsektor');
            }
        });

        if (Schema::hasColumn('pelatihan', 'kategori')) {
            DB::statement(
                "ALTER TABLE pelatihan MODIFY kategori ENUM('technical', 'legal', 'commercial', 'soft_skill') NOT NULL DEFAULT 'technical'"
            );
        }

        if (Schema::hasColumn('pelatihan', 'status')) {
            DB::statement(
                "ALTER TABLE pelatihan MODIFY status ENUM('wajib', 'opsional') NOT NULL DEFAULT 'opsional'"
            );
        }

        DB::table('pelatihan')->whereNull('target_jabatan')->update([
            'target_jabatan' => json_encode(['Inspektur Ketenagalistrikan', 'Penyelidik Bumi']),
        ]);
    }

    public function down(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->dropColumn('target_jabatan');
        });

        DB::statement(
            "ALTER TABLE pelatihan MODIFY kategori ENUM('wajib', 'opsional') NOT NULL"
        );

        DB::statement(
            "ALTER TABLE pelatihan MODIFY status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif'"
        );
    }
};