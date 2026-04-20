<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\Rfc3339DateTimeString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class Rfc3339DateTimeStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $rfc3339 = new Rfc3339DateTimeString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $rfc3339 = new Rfc3339DateTimeString($value);

        // When
        $obtainedValue = $rfc3339->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['2024-01-15T10:30:00+00:00'],
            ['2024-01-15T10:30:00Z'],
            ['2024-01-15T10:30:00-05:00'],
            ['2024-01-15T10:30:00.123+00:00'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $rfc3339 = new Rfc3339DateTimeString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['2024-01-15'],
            ['not-a-date'],
            ['2024-02-30T10:30:00+00:00'],
        ];
    }
}
