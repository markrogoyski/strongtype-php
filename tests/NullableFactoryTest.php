<?php

declare(strict_types=1);

namespace StrongType\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Nullable;
use StrongType\Int\PositiveInt;
use StrongType\Float\UnitFloat;
use StrongType\String\NonemptyString;
use StrongType\Bool\TrueValue;
use StrongType\DateTime\DateString;
use StrongType\DateTime\Timestamp;
use StrongType\Arrays\NonemptyArray;

class NullableFactoryTest extends TestCase
{
    #[Test]
    public function testIntegerNullableWithValue(): void
    {
        $result = PositiveInt::nullable(5);
        $this->assertInstanceOf(Nullable::class, $result);
        $this->assertFalse($result->isNull());
        $this->assertSame(5, $result->getValue());
    }

    #[Test]
    public function testIntegerNullableWithNull(): void
    {
        $result = PositiveInt::nullable(null);
        $this->assertInstanceOf(Nullable::class, $result);
        $this->assertTrue($result->isNull());
        $this->assertNull($result->getValue());
    }

    #[Test]
    public function testFloatNullableWithValue(): void
    {
        $result = UnitFloat::nullable(0.5);
        $this->assertInstanceOf(Nullable::class, $result);
        $this->assertSame(0.5, $result->getValue());
    }

    #[Test]
    public function testFloatNullableWithNull(): void
    {
        $result = UnitFloat::nullable(null);
        $this->assertTrue($result->isNull());
    }

    #[Test]
    public function testStringNullableWithValue(): void
    {
        $result = NonemptyString::nullable('hello');
        $this->assertInstanceOf(Nullable::class, $result);
        $this->assertSame('hello', $result->getValue());
    }

    #[Test]
    public function testStringNullableWithNull(): void
    {
        $result = NonemptyString::nullable(null);
        $this->assertTrue($result->isNull());
    }

    #[Test]
    public function testBoolNullableWithValue(): void
    {
        $result = TrueValue::nullable(true);
        $this->assertInstanceOf(Nullable::class, $result);
        $this->assertSame(true, $result->getValue());
    }

    #[Test]
    public function testBoolNullableWithNull(): void
    {
        $result = TrueValue::nullable(null);
        $this->assertTrue($result->isNull());
    }

    #[Test]
    public function testTimestampNullableWithValue(): void
    {
        $ts = \time();
        $result = Timestamp::nullable($ts);
        $this->assertInstanceOf(Nullable::class, $result);
    }

    #[Test]
    public function testTimestampNullableWithNull(): void
    {
        $result = Timestamp::nullable(null);
        $this->assertTrue($result->isNull());
    }

    #[Test]
    public function testDateTimeNullableWithValue(): void
    {
        $result = DateString::nullable('2026-04-18');
        $this->assertInstanceOf(Nullable::class, $result);
        $this->assertFalse($result->isNull());
    }

    #[Test]
    public function testDateTimeNullableWithNull(): void
    {
        $result = DateString::nullable(null);
        $this->assertTrue($result->isNull());
    }

    #[Test]
    public function testArrayNullableWithValue(): void
    {
        $result = NonemptyArray::nullable([1, 2, 3]);
        $this->assertInstanceOf(Nullable::class, $result);
    }

    #[Test]
    public function testArrayNullableWithNull(): void
    {
        $result = NonemptyArray::nullable(null);
        $this->assertTrue($result->isNull());
    }
}
