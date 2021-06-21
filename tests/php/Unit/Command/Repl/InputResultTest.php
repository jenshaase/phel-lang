<?php

declare(strict_types=1);

namespace PhelTest\Unit\Command\Repl;

use Phel\Command\Repl\InputResult;
use PHPUnit\Framework\TestCase;

final class InputResultTest extends TestCase
{
    public function test_empty(): void
    {
        $result = InputResult::empty();
        $actual = $result->readBuffer(['__']);

        self::assertSame('__', $actual);
    }

    public function test_buffer_boolean(): void
    {
        $result = InputResult::fromAny(true);
        $actual = $result->readBuffer(['__']);

        self::assertSame('true', $actual);
    }

    public function test_buffer_null(): void
    {
        $result = InputResult::fromAny(null);
        $actual = $result->readBuffer(['__']);

        self::assertSame('nil', $actual);
    }

    public function test_buffer_numerical(): void
    {
        $result = InputResult::fromAny(2.3);
        $actual = $result->readBuffer(['(+ 1 __)']);

        self::assertSame('(+ 1 2.3)', $actual);
    }

    public function test_buffer_string(): void
    {
        $result = InputResult::fromAny('hello');
        $actual = $result->readBuffer(['(concat __ __)']);

        self::assertSame('(concat "hello" "hello")', $actual);
    }

    public function test_buffer_multiline(): void
    {
        $result = InputResult::fromAny('str');
        $actual = $result->readBuffer(['(concat', '__', '__)']);
        $expected = <<<TXT
(concat
"str"
"str")
TXT;
        self::assertSame($expected, $actual);
    }

    public function test_buffer_with_normal_underscore(): void
    {
        $result = InputResult::fromAny(true);
        $actual = $result->readBuffer(['(let [a __ _ 20] a)']);

        self::assertSame('(let [a true _ 20] a)', $actual);
    }

    public function test_buffer_inside_string(): void
    {
        $result = InputResult::fromAny(1);
        $actual = $result->readBuffer(['"__" __']);

        self::assertSame('"__" 1', $actual);
    }
}
