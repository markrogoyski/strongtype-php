<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\CountryCodeAlpha3String;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CountryCodeAlpha3StringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $countryCode = new CountryCodeAlpha3String($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $countryCode = new CountryCodeAlpha3String($value);

        // When
        $obtainedValue = $countryCode->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['USA'],
            ['GBR'],
            ['DEU'],
            ['JPN'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $countryCode = new CountryCodeAlpha3String($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['US'],
            ['usa'],
            ['123'],
            ['ABCD'],
        ];
    }
}
