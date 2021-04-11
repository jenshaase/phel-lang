<?php

declare(strict_types=1);

namespace PhelTest\Benchmark\Lang\Collections\HashMap;

use Phel\Lang\HasherInterface;

class SimpleHasher implements HasherInterface
{
    public function hash($value): int
    {
        return $value;
    }
}