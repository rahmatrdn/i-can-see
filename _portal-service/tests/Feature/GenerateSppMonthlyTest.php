<?php

use App\Console\Commands\GenerateSppMonthly;
use App\Http\Controllers\GenerateSppMonthlyController;
use Illuminate\Support\Facades\Route;

test('generate monthly spp route is not registered in iCanSee', function () {
    expect(Route::has('spp.generate_monthly'))->toBeFalse();
});

test('generate monthly spp controller and command are removed', function () {
    expect(class_exists(GenerateSppMonthlyController::class, false))->toBeFalse()
        ->and(class_exists(GenerateSppMonthly::class, false))->toBeFalse();
});
