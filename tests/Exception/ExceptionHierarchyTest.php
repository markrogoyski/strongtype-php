<?php

declare(strict_types=1);

namespace StrongType\Tests\Exception;

use StrongType\Arrays\ArrayType;
use StrongType\Bool\BoolType;
use StrongType\Constraint\InList;
use StrongType\Constraint\IsList;
use StrongType\Constraint\Nonempty;
use StrongType\DateTime\DateString;
use StrongType\DateTime\DateTime;
use StrongType\DateTime\TimeString;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\FormatException;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\PositiveFloat;
use StrongType\Int\PositiveInt;
use StrongType\Nullable;
use StrongType\String\NonemptyString;
use PHPUnit\Framework\Attributes\Test;

#[InList(true)]
readonly class ExceptionHierarchyTrueOnlyBool extends BoolType
{
}

#[IsList]
class ExceptionHierarchyListArray extends ArrayType
{
}

#[Nonempty]
readonly class ExceptionHierarchyNonemptyDateTime extends DateTime
{
}

class ExceptionHierarchyTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testIntegerConstraintFailureThrowsConstraintViolationException()
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new PositiveInt(-1);
    }

    #[Test]
    public function testFloatingPointConstraintFailureThrowsConstraintViolationException()
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new PositiveFloat(-1.0);
    }

    #[Test]
    public function testStringTypeConstraintFailureThrowsConstraintViolationException()
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new NonemptyString('');
    }

    #[Test]
    public function testBoolTypeConstraintFailureThrowsConstraintViolationException()
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new ExceptionHierarchyTrueOnlyBool(false);
    }

    #[Test]
    public function testArrayTypeConstraintFailureThrowsConstraintViolationException()
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new ExceptionHierarchyListArray(['a' => 1]);
    }

    #[Test]
    public function testDateTimeConstraintFailureThrowsConstraintViolationException()
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new ExceptionHierarchyNonemptyDateTime('');
    }

    #[Test]
    public function testDateStringParseFailureThrowsFormatException()
    {
        // Then
        $this->expectException(FormatException::class);

        // When
        new DateString('not-a-date');
    }

    #[Test]
    public function testDateStringInvalidCalendarThrowsFormatException()
    {
        // Then
        $this->expectException(FormatException::class);

        // When
        new DateString('2024-02-30');
    }

    #[Test]
    public function testTimeStringParseFailureThrowsFormatException()
    {
        // Then
        $this->expectException(FormatException::class);

        // When
        new TimeString('25:00:00');
    }

    #[Test]
    public function testCatchStrongTypeExceptionStillCatchesConstraintViolationException()
    {
        // Given
        $caught = false;

        // When
        try {
            new PositiveInt(-1);
        } catch (StrongTypeException $e) {
            $caught = true;
            $thrownClass = $e::class;
        }

        // Then
        $this->assertTrue($caught);
        $this->assertSame(ConstraintViolationException::class, $thrownClass ?? null);
    }

    #[Test]
    public function testCatchStrongTypeExceptionStillCatchesFormatException()
    {
        // Given
        $caught = false;

        // When
        try {
            new DateString('not-a-date');
        } catch (StrongTypeException $e) {
            $caught = true;
            $thrownClass = $e::class;
        }

        // Then
        $this->assertTrue($caught);
        $this->assertSame(FormatException::class, $thrownClass ?? null);
    }

    #[Test]
    public function testConstraintViolationExceptionIsAStrongTypeException()
    {
        // When
        $exception = new ConstraintViolationException('test');

        // Then
        $this->assertInstanceOf(StrongTypeException::class, $exception);
    }

    #[Test]
    public function testFormatExceptionIsAStrongTypeException()
    {
        // When
        $exception = new FormatException('test');

        // Then
        $this->assertInstanceOf(StrongTypeException::class, $exception);
    }

    #[Test]
    public function testNullableInvalidTypeReferenceThrowsLogicException()
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new Nullable('NonExistentClass', 5);
    }

    #[Test]
    public function testNullableInvalidTypeReferenceDoesNotThrowStrongTypeException()
    {
        // Given
        $thrownIsStrongTypeException = null;

        // When
        try {
            new Nullable('NonExistentClass', 5);
        } catch (\LogicException $e) {
            $thrownIsStrongTypeException = $e instanceof StrongTypeException;
        }

        // Then
        $this->assertFalse($thrownIsStrongTypeException);
    }

    #[Test]
    public function testNullableAbstractTypeReferenceThrowsLogicException()
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new Nullable(\StrongType\Int\Integer::class, 5);
    }

    #[Test]
    public function testNullableAbstractTypeReferenceDoesNotThrowStrongTypeException()
    {
        // Given
        $thrownIsStrongTypeException = null;

        // When
        try {
            new Nullable(\StrongType\Int\Integer::class, 5);
        } catch (\LogicException $e) {
            $thrownIsStrongTypeException = $e instanceof StrongTypeException;
        }

        // Then
        $this->assertFalse($thrownIsStrongTypeException);
    }
}
