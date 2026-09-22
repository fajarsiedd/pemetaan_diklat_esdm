<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use App\Models\TargetPelatihan;
use App\Models\User;
use App\Services\TargetPelatihanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function makePegawai(array $overrides = []): array
    {
        $user = User::factory()->create(['role' => 'pegawai']);

        return [
            'user' => $user,
            'pegawai' => Pegawai::factory()->create([
                'user_id' => $user->id,
                'jabatan' => 'Inspektur Ketenagalistrikan',
                'jenjang' => 'Muda',
                ...$overrides,
            ]),
        ];
    }

    public function test_admin_area_rejects_pegawai(): void
    {
        $pegawai = $this->makePegawai();

        $this->actingAs($pegawai['user'])
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($pegawai['user'])
            ->get('/admin/pegawai')
            ->assertForbidden();
    }

    public function test_pegawai_area_rejects_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/pegawai/profile')
            ->assertForbidden();
    }

    public function test_admin_can_recalculate_targets_and_pegawai_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pegawai = $this->makePegawai();

        $this->actingAs($pegawai['user'])
            ->post('/admin/dashboard/recalculate')
            ->assertForbidden();

        $this->actingAs($admin)
            ->post('/admin/dashboard/recalculate')
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_recalculate_assigns_remaining_trainings_and_respects_quorum(): void
    {
        $group = $this->makePegawai();
        $this->makePegawai();

        $wajib = Pelatihan::factory()->create(['jenjang' => 'Muda', 'kategori' => 'wajib', 'status' => 'aktif']);
        Pelatihan::factory()->create(['jenjang' => 'Muda', 'kategori' => 'wajib', 'status' => 'aktif']);
        Pelatihan::factory()->create(['jenjang' => 'Muda', 'kategori' => 'opsional', 'status' => 'aktif']);
        Pelatihan::factory()->create(['jenjang' => 'Madya', 'kategori' => 'wajib', 'status' => 'aktif']);

        RiwayatPelatihan::factory()->create([
            'pegawai_id' => $group['pegawai']->id,
            'pelatihan_id' => $wajib->id,
            'tahun' => '2024',
        ]);

        app(TargetPelatihanService::class)->recalculate();

        foreach (Pegawai::all() as $pegawai) {
            $targets = $pegawai->targetPelatihan()->get();
            $this->assertLessThanOrEqual(4, $targets->count());

            $completed = RiwayatPelatihan::where('pegawai_id', $pegawai->id)
                ->whereNotNull('pelatihan_id')
                ->pluck('pelatihan_id');

            $targets->each(function (TargetPelatihan $target) use ($completed) {
                $this->assertFalse($completed->contains($target->pelatihan_id));
            });
        }
    }

    public function test_maximum_of_four_active_targets_per_pegawai(): void
    {
        $this->makePegawai();

        Pelatihan::factory()->count(6)->create(['jenjang' => 'Muda', 'status' => 'aktif']);

        app(TargetPelatihanService::class)->recalculate();

        $this->assertLessThanOrEqual(4, TargetPelatihan::count());
    }

    public function test_completing_target_moves_to_riwayat_and_fills_freed_slot(): void
    {
        $pegawai = $this->makePegawai();

        $completed = Pelatihan::factory()->create(['jenjang' => 'Muda', 'status' => 'aktif']);
        $next = Pelatihan::factory()->create(['jenjang' => 'Muda', 'status' => 'aktif']);

        $target = TargetPelatihan::create([
            'pegawai_id' => $pegawai['pegawai']->id,
            'pelatihan_id' => $completed->id,
            'prioritas' => 1,
            'status' => 'ditargetkan',
        ]);

        $this->actingAs($pegawai['user'])
            ->post("/pegawai/targets/{$target->id}/complete", ['status' => 'selesai'])
            ->assertRedirect();

        $this->assertDatabaseHas('riwayat_pelatihan', [
            'pegawai_id' => $pegawai['pegawai']->id,
            'pelatihan_id' => $completed->id,
        ]);

        $remaining = TargetPelatihan::where('pegawai_id', $pegawai['pegawai']->id)
            ->where('status', '!=', 'selesai')
            ->get();

        $this->assertNotEmpty($remaining);
        $this->assertFalse($remaining->contains('pelatihan_id', $completed->id));
        $this->assertTrue($remaining->contains('pelatihan_id', $next->id));
    }
}
