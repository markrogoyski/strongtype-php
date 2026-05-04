<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\CountryCodeAlpha2String;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CountryCodeAlpha2StringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $countryCode = new CountryCodeAlpha2String($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $countryCode = new CountryCodeAlpha2String($value);

        // When
        $obtainedValue = $countryCode->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['US'],
            ['GB'],
            ['DE'],
            ['JP'],
            ['ZZ'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $countryCode = new CountryCodeAlpha2String($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['USA'],
            ['us'],
            ['12'],
            ['A'],
            ["US\n"],
        ];
    }
}
