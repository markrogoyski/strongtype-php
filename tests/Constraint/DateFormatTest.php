<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Constraint\DateFormat;
use StrongType\DateTime\DateTime;
use StrongType\Exception\StrongTypeException;

#[DateFormat('Y-m-d')]
readonly class DateFormatCompleteTest extends DateTime
{
}

#[DateFormat('Y')]
readonly class DateFormatYearOnlyTest extends DateTime
{
}

#[DateFormat('m-d')]
readonly class DateFormatMonthDayTest extends DateTime
{
}

#[DateFormat('H:i')]
readonly class DateFormatTimeOnlyTest extends DateTime
{
}

#[DateFormat('Y\!m')]
readonly class DateFormatEscapedBangTest extends DateTime
{
}

#[DateFormat('Y\|m')]
readonly class DateFormatEscapedPipeTest extends DateTime
{
}

class DateFormatTest extends TestCase
{
    // Complete format: Y-m-d

    #[Test]
    #[DataProvider('dataProviderForValidCompleteFormat')]
    public function testCompleteFormatAccepts(string $value): void
    {
        // When
        $dt = new DateFormatCompleteTest($value);

        // Then
        $this->assertSame($value, $dt->value);
    }

    public static function dataProviderForValidCompleteFormat(): array
    {
        return [
            ['2024-01-01'],
            ['2024-12-31'],
            ['2000-02-29'],
            ['1970-01-01'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidCompleteFormat')]
    public function testCompleteFormatRejects(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new DateFormatCompleteTest($value);
    }

    public static function dataProviderForInvalidCompleteFormat(): array
    {
        return [
            // Wrong layout.
            'wrong order'   => ['01-01-2024'],
            'wrong sep'     => ['2024/01/01'],
            'not a date'    => ['not-a-date'],
            // Invalid calendar dates.
            'bad month'     => ['2024-13-01'],
            'feb 30'        => ['2024-02-30'],
            'non-leap feb29' => ['2023-02-29'],
            // Trailing garbage is caught by the round-trip comparison.
            'trailing char' => ['2024-01-01x'],
            'trailing space' => ['2024-01-01 '],
            'trailing newline' => ["2024-01-01\n"],
            'empty'         => [''],
        ];
    }

    // Partial format determinism: the result must not depend on the wall clock.
    // The '!' prefix pins absent fields to the Unix epoch (year 1970), so 'm-d'
    // validation is identical on every day, in every year.

    #[Test]
    #[DataProvider('dataProviderForValidMonthDay')]
    public function testMonthDayAccepts(string $value): void
    {
        // When
        $dt = new DateFormatMonthDayTest($value);

        // Then
        $this->assertSame($value, $dt->value);
    }

    public static function dataProviderForValidMonthDay(): array
    {
        return [
            ['01-01'],
            ['02-28'],
            ['12-31'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidMonthDay')]
    public function testMonthDayRejects(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new DateFormatMonthDayTest($value);
    }

    public static function dataProviderForInvalidMonthDay(): array
    {
        return [
            // 1970 is not a leap year, so Feb 29 is rejected deterministically —
            // not accepted-in-leap-years / rejected-otherwise as it was before the
            // '!' fix.
            'feb 29'    => ['02-29'],
            'bad month' => ['13-01'],
            'bad day'   => ['01-32'],
        ];
    }

    // Partial format: year only.

    #[Test]
    #[DataProvider('dataProviderForValidYearOnly')]
    public function testYearOnlyAccepts(string $value): void
    {
        // When
        $dt = new DateFormatYearOnlyTest($value);

        // Then
        $this->assertSame($value, $dt->value);
    }

    public static function dataProviderForValidYearOnly(): array
    {
        return [
            ['2024'],
            ['1970'],
            ['9999'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidYearOnly')]
    public function testYearOnlyRejects(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new DateFormatYearOnlyTest($value);
    }

    public static function dataProviderForInvalidYearOnly(): array
    {
        return [
            'two digits'    => ['24'],
            'non-numeric'   => ['abc'],
            'trailing char' => ['2024x'],
            'empty'         => [''],
        ];
    }

    // Partial format: time only.

    #[Test]
    #[DataProvider('dataProviderForValidTimeOnly')]
    public function testTimeOnlyAccepts(string $value): void
    {
        // When
        $dt = new DateFormatTimeOnlyTest($value);

        // Then
        $this->assertSame($value, $dt->value);
    }

    public static function dataProviderForValidTimeOnly(): array
    {
        return [
            ['00:00'],
            ['14:30'],
            ['23:59'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidTimeOnly')]
    public function testTimeOnlyRejects(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new DateFormatTimeOnlyTest($value);
    }

    public static function dataProviderForInvalidTimeOnly(): array
    {
        return [
            'hour overflow' => ['24:00'],
            'unpadded hour' => ['9:30'],
            'not a time'    => ['noon'],
        ];
    }

    // Escaped '!' / '|' are literal characters, not reset directives, and round-trip
    // fine — only a bare, unescaped '!' or '|' is rejected at construction.

    #[Test]
    public function testEscapedBangIsALiteralCharacterAndRoundTrips(): void
    {
        // When
        $dt = new DateFormatEscapedBangTest('2024!01');

        // Then
        $this->assertSame('2024!01', $dt->value);
    }

    #[Test]
    public function testEscapedPipeIsALiteralCharacterAndRoundTrips(): void
    {
        // When
        $dt = new DateFormatEscapedPipeTest('2024|01');

        // Then
        $this->assertSame('2024|01', $dt->value);
    }

    #[Test]
    public function testEscapedBangStillRejectsNonMatchingInput(): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new DateFormatEscapedBangTest('2024-01');
    }

    // A NUL byte makes DateTimeImmutable::createFromFormat() throw ValueError instead of
    // returning false -- that must surface as a constraint violation, not an uncaught
    // engine exception.

    #[Test]
    public function testNulByteRejectsAsConstraintViolationNotValueError(): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new DateFormatCompleteTest("2024-01-01\0");
    }
}
