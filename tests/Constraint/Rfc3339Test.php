<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Exception\StrongTypeException;
use StrongType\Constraint\Rfc3339;
use StrongType\String\StringType;

#[Rfc3339]
readonly class Rfc3339TestString extends StringType
{
}

class Rfc3339Test extends TestCase
{
    #[Test]
    #[DataProvider('validValues')]
    public function testValidValues(string $value): void
    {
        $str = new Rfc3339TestString($value);
        $this->assertSame($value, $str->value);
    }

    public static function validValues(): array
    {
        return [
            ['2024-01-15T10:30:00+00:00'],
            ['2024-01-15T10:30:00Z'],
            ['2024-01-15T10:30:00z'],
            ['2024-01-15T10:30:00-05:00'],
            ['2024-01-15T10:30:00+05:30'],
            ['2024-01-15T10:30:00.1Z'],
            ['2024-01-15T10:30:00.12Z'],
            ['2024-01-15T10:30:00.123Z'],
            ['2024-01-15T10:30:00.1234Z'],
            ['2024-01-15T10:30:00.123456Z'],
            ['2024-01-15T10:30:00.123456789Z'],
            ['2024-01-15T10:30:00.123+00:00'],
            ['2024-01-15T10:30:00.000+00:00'],
            ['2024-01-15T10:30:00.5-05:00'],
            ['2024-01-15T10:30:00-00:00'],
            ['2024-01-15T10:30:00.123-00:00'],
            // RFC 3339 §5.6 allows seconds 00-60 to accommodate leap seconds.
            // The constraint accepts :60 syntactically; it does not verify the minute
            // is an actual inserted leap second from IERS bulletins.
            ['2024-01-15T23:59:60Z'],
            ['2024-01-15T23:59:60.5+00:00'],
            ['2024-01-15T12:34:60-05:00'],
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function testInvalidValues(string $value): void
    {
        $this->expectException(StrongTypeException::class);
        new Rfc3339TestString($value);
    }

    public static function invalidValues(): array
    {
        return [
            ['2024-01-15'],
            ['10:30:00'],
            ['not a date'],
            ['2024-02-30T10:30:00+00:00'],
            ['2024-01-15T25:30:00+00:00'],
            ['2024-01-15T10:30:00.+00:00'],
            ['2024-01-15T10:30:00.Z'],
            ['2024-01-15T10:30:00'],
            ['2024-01-15T10:30:00+25:00'],
            ['2024-01-15 10:30:00Z'],
        ];
    }

    #[Test]
    public function testReturnsNullForNonString(): void
    {
        // Given
        $constraint = new Rfc3339();

        // When
        $result = $constraint->validate(12345, 'X');

        // Then
        $this->assertNull($result);
    }
}
