<?php

declare(strict_types=1);

namespace StrongType\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Int\PositiveInt;
use StrongType\Int\PortNumber;
use StrongType\Float\UnitFloat;
use StrongType\Float\PositiveFloat;
use StrongType\String\NonemptyString;
use StrongType\String\UuidString;
use StrongType\Bool\TrueValue;
use StrongType\Bool\FalseValue;
use StrongType\DateTime\DateString;
use StrongType\DateTime\Timestamp;
use StrongType\Arrays\NonemptyArray;
use StrongType\Arrays\ListArray;
use StrongType\Arrays\UniqueArray;

class EqualsTest extends TestCase
{
    #[Test]
    public function testIntegerEqual(): void
    {
        $a = new PositiveInt(5);
        $b = new PositiveInt(5);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testIntegerNotEqualByValue(): void
    {
        $a = new PositiveInt(5);
        $b = new PositiveInt(10);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testIntegerNotEqualByType(): void
    {
        $a = new PositiveInt(80);
        $b = new PortNumber(80);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testFloatEqual(): void
    {
        $a = new UnitFloat(0.5);
        $b = new UnitFloat(0.5);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testFloatNotEqualByValue(): void
    {
        $a = new UnitFloat(0.5);
        $b = new UnitFloat(0.7);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testFloatNotEqualByType(): void
    {
        $a = new UnitFloat(0.5);
        $b = new PositiveFloat(0.5);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testStringEqual(): void
    {
        $a = new NonemptyString('hello');
        $b = new NonemptyString('hello');
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testStringNotEqualByValue(): void
    {
        $a = new NonemptyString('hello');
        $b = new NonemptyString('world');
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testStringNotEqualByType(): void
    {
        $a = new NonemptyString('550e8400-e29b-41d4-a716-446655440000');
        $b = new UuidString('550e8400-e29b-41d4-a716-446655440000');
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testBoolEqual(): void
    {
        $a = new TrueValue(true);
        $b = new TrueValue(true);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testBoolNotEqualByType(): void
    {
        // TrueValue and FalseValue can't have the same value, so just test type difference
        $a = new TrueValue(true);
        $b = new FalseValue(false);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testTimestampEqual(): void
    {
        $ts = \time();
        $a = new Timestamp($ts);
        $b = new Timestamp($ts);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testTimestampNotEqualByValue(): void
    {
        $a = new Timestamp(\time());
        $b = new Timestamp(\time() + 100);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testDateTimeEqual(): void
    {
        $a = new DateString('2026-04-18');
        $b = new DateString('2026-04-18');
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testDateTimeNotEqualByValue(): void
    {
        $a = new DateString('2026-04-18');
        $b = new DateString('2026-04-19');
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testArrayEqual(): void
    {
        $a = new NonemptyArray([1, 2, 3]);
        $b = new NonemptyArray([1, 2, 3]);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testArrayNotEqualByValue(): void
    {
        $a = new NonemptyArray([1, 2, 3]);
        $b = new NonemptyArray([4, 5, 6]);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testArrayNotEqualByOrder(): void
    {
        $a = new NonemptyArray(['a' => 1, 'b' => 2]);
        $b = new NonemptyArray(['b' => 2, 'a' => 1]);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testArrayNotEqualAcrossSubclassesSameValues(): void
    {
        // Two different ArrayType subclasses with identical contents must not
        // compare equal — equals() is type-strict.
        $a = new ListArray([1, 2, 3]);
        $b = new UniqueArray([1, 2, 3]);
        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    #[Test]
    public function testArrayNotEqualAcrossSubclassHierarchy(): void
    {
        // NonemptyArray and ListArray are both concrete ArrayType subclasses.
        // Even with identical values, a cross-subclass compare must be false.
        $a = new NonemptyArray([10, 20]);
        $b = new ListArray([10, 20]);
        $this->assertFalse($a->equals($b));
    }
}
