<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\DateFormat;
use StrongType\Constraint\InFuture;
use StrongType\Constraint\InPast;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\TimeFormat;
use StrongType\Exception\StrongTypeException;
use StrongType\DateTime\DateTime;
use StrongType\Int\Integer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[DateFormat('Y-m-d')]
readonly class DateFormatTestDT extends DateTime
{
}

#[TimeFormat]
readonly class TimeFormatTestDT extends DateTime
{
}

#[InFuture]
readonly class FutureTestInt extends Integer
{
}

#[InPast]
readonly class PastTestInt extends Integer
{
}

class DateTimeConstraintsTest extends \PHPUnit\Framework\TestCase
{
    // DateFormat
    #[Test]
    #[DataProvider('dataProviderForValidDateFormat')]
    public function testDateFormatAccepts(string $value)
    {
        $dt = new DateFormatTestDT($value);
        $this->assertSame($value, $dt->value);
    }

    public static function dataProviderForValidDateFormat(): array
    {
        return [['2024-01-01'], ['2024-12-31'], ['2000-06-15']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidDateFormat')]
    public function testDateFormatRejects(string $value)
    {
        $this->expectException(StrongTypeException::class);
        new DateFormatTestDT($value);
    }

    public static function dataProviderForInvalidDateFormat(): array
    {
        return [['01-01-2024'], ['2024/01/01'], ['not-a-date'], ['2024-13-01'], ['2024-02-30']];
    }

    // TimeFormat
    #[Test]
    #[DataProvider('dataProviderForValidTimeFormat')]
    public function testTimeFormatAccepts(string $value)
    {
        $dt = new TimeFormatTestDT($value);
        $this->assertSame($value, $dt->value);
    }

    public static function dataProviderForValidTimeFormat(): array
    {
        return [['00:00:00'], ['12:30:45'], ['23:59:59']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidTimeFormat')]
    public function testTimeFormatRejects(string $value)
    {
        $this->expectException(StrongTypeException::class);
        new TimeFormatTestDT($value);
    }

    public static function dataProviderForInvalidTimeFormat(): array
    {
        return [['24:00:00'], ['12:60:00'], ['12:00:60'], ['12:00'], ['not-a-time']];
    }

    // InFuture
    #[Test]
    public function testInFutureAccepts()
    {
        $future = \time() + 86400;
        $int = new FutureTestInt($future);
        $this->assertSame($future, $int->value);
    }

    #[Test]
    public function testInFutureRejectsPast()
    {
        $this->expectException(StrongTypeException::class);
        new FutureTestInt(1000000);
    }

    // InPast
    #[Test]
    public function testInPastAccepts()
    {
        $int = new PastTestInt(1000000);
        $this->assertSame(1000000, $int->value);
    }

    #[Test]
    public function testInPastRejectsFuture()
    {
        $this->expectException(StrongTypeException::class);
        new PastTestInt(\time() + 86400);
    }

    #[Test]
    public function testDateFormatPriorityIsFormatBand(): void
    {
        // Given
        $constraint = new DateFormat('Y-m-d');

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(100, $priority);
    }

    #[Test]
    public function testTimeFormatPriorityIsFormatBand(): void
    {
        // Given
        $constraint = new TimeFormat();

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(100, $priority);
    }

    #[Test]
    public function testInFuturePriorityIsFormatBand(): void
    {
        // Given
        $constraint = new InFuture();

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(100, $priority);
    }

    #[Test]
    public function testInPastPriorityIsFormatBand(): void
    {
        // Given
        $constraint = new InPast();

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(100, $priority);
    }
}
