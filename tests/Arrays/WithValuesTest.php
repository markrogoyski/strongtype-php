<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\NonemptyArray;
use StrongType\Arrays\ArrayOfStrings;
use StrongType\Arrays\FixedSizeArray;

class WithValuesTest extends TestCase
{
    #[Test]
    public function testWithValuesReturnsNewInstance(): void
    {
        $original = new NonemptyArray([1, 2, 3]);
        $new = $original->withValues([4, 5, 6]);
        $this->assertSame([4, 5, 6], $new->getValues());
        $this->assertSame([1, 2, 3], $original->getValues());
    }

    #[Test]
    public function testWithValuesRevalidates(): void
    {
        $arr = new NonemptyArray([1, 2, 3]);
        $this->expectException(StrongTypeException::class);
        $arr->withValues([]);
    }

    #[Test]
    public function testWithValuesPreservesType(): void
    {
        $arr = new ArrayOfStrings(['a', 'b']);
        $new = $arr->withValues(['c', 'd']);
        $this->assertInstanceOf(ArrayOfStrings::class, $new);
        $this->assertSame(['c', 'd'], $new->getValues());
    }

    #[Test]
    public function testWithValuesRejectsInvalidElementTypes(): void
    {
        $arr = new ArrayOfStrings(['a']);
        $this->expectException(StrongTypeException::class);
        $arr->withValues([1, 2, 3]);
    }

    #[Test]
    public function testFixedSizeArrayWithValuesPreservesSize(): void
    {
        $arr = new FixedSizeArray([1, 2, 3], 3);
        $new = $arr->withValues([4, 5, 6]);
        $this->assertSame([4, 5, 6], $new->getValues());
    }

    #[Test]
    public function testFixedSizeArrayWithValuesRejectsWrongSize(): void
    {
        $arr = new FixedSizeArray([1, 2, 3], 3);
        $this->expectException(StrongTypeException::class);
        $arr->withValues([1, 2]);
    }
}
