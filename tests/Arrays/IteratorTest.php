<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Arrays\ArrayOfStrings;
use StrongType\Arrays\AssociativeArray;
use StrongType\Arrays\FixedSizeArray;
use StrongType\Arrays\NonemptyArray;

class IteratorTest extends TestCase
{
    #[Test]
    public function testForeachOverListArray(): void
    {
        $collected = [];
        foreach (new NonemptyArray([10, 20, 30]) as $key => $value) {
            $collected[$key] = $value;
        }

        $this->assertSame([10, 20, 30], $collected);
    }

    #[Test]
    public function testForeachOverAssociativeArrayPreservesKeys(): void
    {
        $source = ['alpha' => 1, 'beta' => 2, 'gamma' => 3];
        $collected = [];
        foreach (new AssociativeArray($source) as $key => $value) {
            $collected[$key] = $value;
        }

        $this->assertSame($source, $collected);
    }

    #[Test]
    public function testForeachOverFixedSizeArray(): void
    {
        $collected = [];
        foreach (new FixedSizeArray(['a', 'b', 'c'], 3) as $value) {
            $collected[] = $value;
        }

        $this->assertSame(['a', 'b', 'c'], $collected);
    }

    #[Test]
    public function testIteratorReturnsArrayIterator(): void
    {
        $array = new ArrayOfStrings(['x', 'y']);

        $iterator = $array->getIterator();

        $this->assertInstanceOf(\ArrayIterator::class, $iterator);
        $this->assertCount(2, $iterator);
    }

    #[Test]
    public function testIteratorCursorBehavior(): void
    {
        $iterator = (new NonemptyArray(['first', 'second']))->getIterator();

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertSame(0, $iterator->key());
        $this->assertSame('first', $iterator->current());

        $iterator->next();
        $this->assertSame(1, $iterator->key());
        $this->assertSame('second', $iterator->current());

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    #[Test]
    public function testIteratorIsIndependentSnapshot(): void
    {
        // ArrayIterator wraps a copy of the array, so repeated iteration
        // should always yield the original values.
        $array = new NonemptyArray([1, 2, 3]);

        $first = \iterator_to_array($array->getIterator());
        $second = \iterator_to_array($array->getIterator());

        $this->assertSame([1, 2, 3], $first);
        $this->assertSame($first, $second);
    }
}
