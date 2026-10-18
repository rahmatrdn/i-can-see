<?php

use Illuminate\Support\Facades\Route;

test('kegiatan routes are not registered in starter kit', function () {
    expect(Route::has('admin.keuangan.kegiatan.index'))->toBeFalse()
        ->and(Route::has('admin.keuangan.kegiatan.keuangan.create'))->toBeFalse()
        ->and(Route::has('admin.keuangan.kegiatan.keuangan.void'))->toBeFalse();
});

test('activity domain files are removed from starter kit', function () {
    expect(class_exists(App\Constants\ActivityStatusConst::class, false))->toBeFalse()
        ->and(class_exists(App\Http\Controllers\Admin\ActivityController::class, false))->toBeFalse()
        ->and(class_exists(App\Usecase\ActivityUsecase::class, false))->toBeFalse()
        ->and(class_exists(App\Usecase\ActivityFinanceUsecase::class, false))->toBeFalse();
});
