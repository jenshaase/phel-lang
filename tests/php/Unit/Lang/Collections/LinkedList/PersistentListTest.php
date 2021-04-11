<?php

declare(strict_types=1);

namespace PhelTest\Unit\Lang\Collections\Vector;

use Phel\Lang\Collections\LinkedList\EmptyList;
use Phel\Lang\Collections\LinkedList\PersistentList;
use PhelTest\Unit\Lang\Collections\ModuloHasher;
use PhelTest\Unit\Lang\Collections\SimpleEqualizer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PersistentListTest extends TestCase
{
    public function testPrependOnEmptyList(): void
    {
        $list = PersistentList::empty(new ModuloHasher(), new SimpleEqualizer())->prepend('foo');

        $this->assertTrue($list instanceof PersistentList);
        $this->assertEquals(1, $list->count());
        $this->assertEquals('foo', $list->get(0));
    }

    public function testPrependOnList(): void
    {
        $list = PersistentList::empty(new ModuloHasher(), new SimpleEqualizer())
            ->prepend('foo')
            ->prepend('bar');

        $this->assertTrue($list instanceof PersistentList);
        $this->assertEquals(2, $list->count());
        $this->assertEquals('bar', $list->get(0));
        $this->assertEquals('foo', $list->get(1));
    }

    public function testFromEmptyArray(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), []);

        $this->assertEquals(0, $list->count());
        $this->assertTrue($list instanceof EmptyList);
    }

    public function testFromArray(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertEquals(2, $list->count());
        $this->assertEquals('foo', $list->get(0));
        $this->assertEquals('bar', $list->get(1));
    }

    public function testPopWithRest(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar'])
            ->pop();

        $this->assertEquals(1, $list->count());
        $this->assertEquals('bar', $list->get(0));
    }

    public function testPopWithoutRest(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo'])
            ->pop();

        $this->assertInstanceOf(EmptyList::class, $list);
        $this->assertEquals(0, $list->count());
    }

    public function testGet(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);

        $this->assertEquals('bar', $list->get(1));
    }

    public function testGetNegativeNumber(): void
    {
        $this->expectException(RuntimeException::class);

        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $list->get(-1);
    }

    public function testGetOutOfBound(): void
    {
        $this->expectException(RuntimeException::class);

        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $list->get(3);
    }

    public function testEqualsOtherType(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);

        $this->assertFalse($list->equals(['foo', 'bar', 'foobar']));
    }

    public function testEqualsDifferentLength(): void
    {
        $a = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $b = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    public function testEqualsDifferentValues(): void
    {
        $a = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $b = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'foobar', 'bar']);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    public function testEqualsSameValues(): void
    {
        $a = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $b = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    public function testHash(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), [2]);

        $this->assertEquals(33, $list->hash());
    }

    public function testIterator(): void
    {
        $xs = ['foo', 'bar', 'foobar'];
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), $xs);

        $result = [];
        foreach ($list as $index => $value) {
            $result[$index] = $value;
        }

        $this->assertEquals($xs, $result);
    }

    public function testFirst(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo']);

        $this->assertEquals('foo', $list->first());
    }

    public function testRest(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo']);

        $this->assertEquals(PersistentList::empty(new ModuloHasher(), new SimpleEqualizer()), $list->rest());
    }

    public function testCdr(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo']);

        $this->assertNull($list->cdr());
    }

    public function testCdr2(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertEquals(PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['bar']), $list->cdr());
    }
}