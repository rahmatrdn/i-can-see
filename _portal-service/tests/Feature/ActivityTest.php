<?php

use App\Constants\ActivityStatusConst;
use App\Http\Controllers\Admin\ActivityController;
use App\Usecase\ActivityFinanceUsecase;
use App\Usecase\ActivityUsecase;
use Illuminate\Support\Facades\Route;

test('kegiatan routes are not registered in iCanSee', function () {
    expect(Route::has('admin.keuangan.kegiatan.index'))->toBeFalse()
        ->and(Route::has('admin.keuangan.kegiatan.keuangan.create'))->toBeFalse()
        ->and(Route::has('admin.keuangan.kegiatan.keuangan.void'))->toBeFalse();
});

test('activity domain files are removed from iCanSee', function () {
    expect(class_exists(ActivityStatusConst::class, false))->toBeFalse()
        ->and(class_exists(ActivityController::class, false))->toBeFalse()
        ->and(class_exists(ActivityUsecase::class, false))->toBeFalse()
        ->and(class_exists(ActivityFinanceUsecase::class, false))->toBeFalse();
});
