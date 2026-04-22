<?php

declare(strict_types=1);

namespace StrongType\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Int\PositiveInt;
use StrongType\Int\Integer;
use StrongType\Float\UnitFloat;
use StrongType\Float\FloatingPoint;
use StrongType\String\NonemptyString;
use StrongType\String\StringType;
use StrongType\Bool\BoolType;
use StrongType\Bool\TrueValue;
use StrongType\Bool\FalseValue;
use StrongType\DateTime\DateString;
use StrongType\DateTime\DateTime;
use StrongType\DateTime\Timestamp;
use StrongType\Arrays\ArrayType;
use StrongType\Arrays\NonemptyArray;
use StrongType\Arrays\FixedSizeArray;

class TryFromTest extends TestCase
{
    #[Test]
    public function testIntegerSuccess(): void
    {
        $result = PositiveInt::tryFrom(5);
        $this->assertInstanceOf(PositiveInt::class, $result);
        $this->assertSame(5, $result->getValue());
    }

    #[Test]
    public function testIntegerConstraintViolation(): void
    {
        $this->assertNull(PositiveInt::tryFrom(-1));
    }

    #[Test]
    public function testIntegerTypeMismatch(): void
    {
        $this->assertNull(PositiveInt::tryFrom('not a number'));
    }

    #[Test]
    public function testAbstractIntegerReturnsNull(): void
    {
        $this->assertNull(Integer::tryFrom(5));
    }

    #[Test]
    public function testFloatSuccess(): void
    {
        $result = UnitFloat::tryFrom(0.5);
        $this->assertInstanceOf(UnitFloat::class, $result);
    }

    #[Test]
    public function testFloatConstraintViolation(): void
    {
        $this->assertNull(UnitFloat::tryFrom(5.0));
    }

    #[Test]
    public function testAbstractFloatReturnsNull(): void
    {
        $this->assertNull(FloatingPoint::tryFrom(1.0));
    }

    #[Test]
    public function testFloatWidensIntToFloat(): void
    {
        $result = UnitFloat::tryFrom(0);
        $this->assertInstanceOf(UnitFloat::class, $result);
        $this->assertSame(0.0, $result->getValue());
    }

    #[Test]
    public function testFloatRejectsNumericString(): void
    {
        $this->assertNull(UnitFloat::tryFrom('0.5'));
        $this->assertNull(UnitFloat::tryFrom('5'));
    }

    #[Test]
    public function testStringSuccess(): void
    {
        $result = NonemptyString::tryFrom('hello');
        $this->assertInstanceOf(NonemptyString::class, $result);
    }

    #[Test]
    public function testStringConstraintViolation(): void
    {
        $this->assertNull(NonemptyString::tryFrom(''));
    }

    #[Test]
    public function testAbstractStringReturnsNull(): void
    {
        $this->assertNull(StringType::tryFrom('hello'));
    }

    #[Test]
    public function testBoolSuccess(): void
    {
        $result = TrueValue::tryFrom(true);
        $this->assertInstanceOf(TrueValue::class, $result);
    }

    #[Test]
    public function testBoolTypeMismatch(): void
    {
        $this->assertNull(TrueValue::tryFrom('not a bool'));
    }

    #[Test]
    public function testBoolLiteralMismatch(): void
    {
        $this->assertNull(TrueValue::tryFrom(false));
    }

    #[Test]
    public function testAbstractBoolReturnsNull(): void
    {
        $this->assertNull(BoolType::tryFrom(true));
    }

    #[Test]
    public function testFalseValueSuccess(): void
    {
        $result = FalseValue::tryFrom(false);
        $this->assertInstanceOf(FalseValue::class, $result);
    }

    #[Test]
    public function testFalseValueLiteralMismatch(): void
    {
        // FalseValue narrows bool to literal `false`; passing `true` must
        // surface as null via the TypeError catch in BoolType::tryFrom.
        $this->assertNull(FalseValue::tryFrom(true));
    }

    #[Test]
    public function testFalseValueTypeMismatch(): void
    {
        $this->assertNull(FalseValue::tryFrom('not a bool'));
        $this->assertNull(FalseValue::tryFrom(0));
    }

    #[Test]
    public function testTimestampSuccess(): void
    {
        $result = Timestamp::tryFrom(\time());
        $this->assertInstanceOf(Timestamp::class, $result);
    }

    #[Test]
    public function testDateTimeSuccess(): void
    {
        $result = DateString::tryFrom('2026-04-18');
        $this->assertInstanceOf(DateString::class, $result);
    }

    #[Test]
    public function testDateTimeConstraintViolation(): void
    {
        $this->assertNull(DateString::tryFrom('not-a-date'));
    }

    #[Test]
    public function testAbstractDateTimeReturnsNull(): void
    {
        $this->assertNull(DateTime::tryFrom('something'));
    }

    #[Test]
    public function testArraySuccess(): void
    {
        $result = NonemptyArray::tryFrom([1, 2, 3]);
        $this->assertInstanceOf(NonemptyArray::class, $result);
    }

    #[Test]
    public function testArrayConstraintViolation(): void
    {
        $this->assertNull(NonemptyArray::tryFrom([]));
    }

    #[Test]
    public function testAbstractArrayReturnsNull(): void
    {
        $this->assertNull(ArrayType::tryFrom([1]));
    }

    #[Test]
    public function testFixedSizeArrayTryFromThrows(): void
    {
        $this->expectException(\LogicException::class);
        FixedSizeArray::tryFrom([1, 2, 3]);
    }

    #[Test]
    public function testBugInSubclassConstructorSurfaces(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        BuggyInteger::tryFrom(5);
    }
}

/**
 * Test fixture: a subclass whose constructor contains a genuine bug.
 * `tryFrom()` must surface the error rather than swallow it as `null`.
 */
readonly class BuggyInteger extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct(\intdiv(1, 0));
    }
}
