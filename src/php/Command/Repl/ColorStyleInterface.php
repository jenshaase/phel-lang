<?php

declare(strict_types=1);

namespace Phel\Command\Repl;

interface ColorStyleInterface
{
    public function green(string $str): string;

    public function yellow(string $str): string;

    public function blue(string $str): string;

    public function red(string $str): string;
}
