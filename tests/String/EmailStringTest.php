<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\EmailString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class EmailStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $emailString = new EmailString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $emailString = new EmailString($value);

        // When
        $obtainedValue = $emailString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $emailString = new EmailString($value);

        // When
        $debugInfo = $emailString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['test@example.com'],
            ['user.name@domain.org'],
            ['user+tag@example.co.uk'],
            ['a@b.cc'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $emailString = new EmailString($value);

        // When
        $stringRepresentation = (string) $emailString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['test@example.com', 'test@example.com'],
            ['user.name@domain.org', 'user.name@domain.org'],
            ['user+tag@example.co.uk', 'user+tag@example.co.uk'],
            ['a@b.cc', 'a@b.cc'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $emailString = new EmailString($value);

        // When
        $jsonSerialization = \json_encode($emailString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['test@example.com', '"test@example.com"'],
            ['user.name@domain.org', '"user.name@domain.org"'],
            ['user+tag@example.co.uk', '"user+tag@example.co.uk"'],
            ['a@b.cc', '"a@b.cc"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $emailString = new EmailString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['notanemail'],
            ['@domain.com'],
            ['user@'],
            ['user@.com'],
            ['user@domain'],
            ['@'],
            ['hello, world'],
        ];
    }
}
