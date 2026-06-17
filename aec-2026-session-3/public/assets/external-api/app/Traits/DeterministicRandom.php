<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use ReflectionClass;

trait DeterministicRandom
{
    protected function deterministicRandom(string $key): int
    {
        $globalSeed = Cache::get('rng_global_seed', 'rng');
        $controllerName = (new ReflectionClass($this))->getShortName();
        $seed = $globalSeed.':'.$controllerName.':'.$key;

        return hexdec(substr(md5($seed), 0, 8)) & 0x7FFFFFFF;
    }

    protected function deterministicChance(string $key): float
    {
        return ($this->deterministicRandom($key) % 100) / 100.0;
    }
}
