<?php

use App\Support\Http\IniSize;

test('ini size parser converts megabytes to bytes', function () {
    expect(IniSize::toBytes('2M'))->toBe(2 * 1024 * 1024)
        ->and(IniSize::toBytes('256M'))->toBe(256 * 1024 * 1024)
        ->and(IniSize::human('2M'))->toBe('2 MB');
});
