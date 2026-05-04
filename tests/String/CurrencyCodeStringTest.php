<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\CurrencyCodeString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CurrencyCodeStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $currencyCode = new CurrencyCodeString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $currencyCode = new CurrencyCodeString($value);

        // When
        $obtainedValue = $currencyCode->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['USD'],
            ['EUR'],
            ['GBP'],
            ['JPY'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $currencyCode = new CurrencyCodeString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['US'],
            ['usd'],
            ['123'],
            ['ABCD'],
            ["USD\n"],
        ];
    }
}
