<?php

use Illuminate\Support\Facades\Route;

test('generate monthly spp route is not registered in starter kit', function () {
    expect(Route::has('spp.generate_monthly'))->toBeFalse();
});

test('generate monthly spp controller and command are removed', function () {
    expect(class_exists(App\Http\Controllers\GenerateSppMonthlyController::class, false))->toBeFalse()
        ->and(class_exists(App\Console\Commands\GenerateSppMonthly::class, false))->toBeFalse();
});
