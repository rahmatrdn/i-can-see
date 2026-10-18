<?php

use Illuminate\Support\Facades\Route;

test('wali murid payment routes are not registered in starter kit', function () {
    expect(Route::has('wali_murid.spp.bayar'))->toBeFalse()
        ->and(Route::has('wali_murid.up.bayar'))->toBeFalse()
        ->and(Route::has('wali_murid.tagihan-spp'))->toBeFalse()
        ->and(Route::has('wali_murid.tagihan-up'))->toBeFalse();
});
