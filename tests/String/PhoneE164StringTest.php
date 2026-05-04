<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\PhoneE164String;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PhoneE164StringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $phoneE164String = new PhoneE164String($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $phoneE164String = new PhoneE164String($value);

        // When
        $obtainedValue = $phoneE164String->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $phoneE164String = new PhoneE164String($value);

        // When
        $debugInfo = $phoneE164String->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['+15551234567'],
            ['+442071234567'],
            ['+123456789012345'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $phoneE164String = new PhoneE164String($value);

        // When
        $stringRepresentation = (string) $phoneE164String;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['+15551234567', '+15551234567'],
            ['+442071234567', '+442071234567'],
            ['+123456789012345', '+123456789012345'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $phoneE164String = new PhoneE164String($value);

        // When
        $jsonSerialization = \json_encode($phoneE164String);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['+15551234567', '"+15551234567"'],
            ['+442071234567', '"+442071234567"'],
            ['+123456789012345', '"+123456789012345"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $phoneE164String = new PhoneE164String($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['5551234567'],
            ['+0551234567'],
            ['++1234'],
            ['+1'],
            ["+12025551234\n"],
        ];
    }
}
