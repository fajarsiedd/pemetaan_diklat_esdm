<?php

namespace App\Console\Commands;

use App\Services\TargetPelatihanService;
use Illuminate\Console\Command;

class CalculateTargets extends Command
{
    protected $signature = 'target:calculate';

    protected $description = 'Recalculate training targets based on cohort completion gaps';

    public function handle(TargetPelatihanService $service): int
    {
        $stats = $service->recalculate();

        $this->info(sprintf(
            'Target pelatihan dihitung: %d kelompok, %d target ditugaskan, %d pegawai penuh.',
            $stats['groups'],
            $stats['assigned'],
            $stats['skipped_full'],
        ));

        return self::SUCCESS;
    }
}
