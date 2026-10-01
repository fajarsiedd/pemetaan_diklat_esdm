<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPegawaiController;
use App\Http\Controllers\Admin\AdminPelatihanController;
use App\Http\Controllers\Admin\AdminTargetPelatihanController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Pegawai\PegawaiProfileController;
use App\Http\Controllers\Pegawai\RiwayatPelatihanController;
use App\Http\Controllers\Pegawai\TargetPelatihanController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::post('/dashboard/recalculate', [AdminDashboardController::class, 'recalculate'])->name('dashboard.recalculate');
            Route::get('/target-pelatihan', [AdminTargetPelatihanController::class, 'index'])->name('target-pelatihan.index');

            Route::as('pegawai.')
                ->prefix('pegawai')
                ->controller(AdminPegawaiController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::post('/import', 'import')->name('import');
                    Route::get('/{pegawai}/profile', 'edit')->name('edit');
                    Route::put('/{pegawai}', 'update')->name('update');
                    Route::delete('/{pegawai}', 'destroy')->name('destroy');

                    Route::post('/{pegawai}/targets/{target}/complete', 'completeTarget')->name('targets.complete');
                    Route::post('/{pegawai}/riwayat', 'storeRiwayat')->name('riwayat.store');
                    Route::get('/{pegawai}/riwayat/{riwayat}/sertifikat', 'downloadRiwayat')->name('riwayat.download');
                    Route::delete('/{pegawai}/riwayat/{riwayat}', 'destroyRiwayat')->name('riwayat.destroy');
                });

            Route::as('pelatihan.')
                ->prefix('pelatihan')
                ->controller(AdminPelatihanController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{pelatihan}/edit', 'edit')->name('edit');
                    Route::put('/{pelatihan}', 'update')->name('update');
                    Route::delete('/{pelatihan}', 'destroy')->name('destroy');
                    Route::post('/import', 'import')->name('import');
                });
        });

    Route::middleware('role:pegawai')
        ->prefix('pegawai')
        ->name('pegawai.')
        ->group(function () {
            Route::get('/profile', [PegawaiProfileController::class, 'show'])->name('profile');
            Route::put('/profile', [PegawaiProfileController::class, 'update'])->name('profile.update');

            Route::post('/targets/{target}/complete', [TargetPelatihanController::class, 'complete'])->name('targets.complete');

            Route::get('/riwayat/{riwayat}/sertifikat', [RiwayatPelatihanController::class, 'download'])->name('riwayat.download');
            Route::post('/riwayat', [RiwayatPelatihanController::class, 'store'])->name('riwayat.store');
            Route::delete('/riwayat/{riwayat}', [RiwayatPelatihanController::class, 'destroy'])->name('riwayat.destroy');
        });
});
