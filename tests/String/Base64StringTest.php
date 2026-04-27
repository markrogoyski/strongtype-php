<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\Base64String;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class Base64StringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $base64String = new Base64String($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $base64String = new Base64String($value);

        // When
        $obtainedValue = $base64String->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $base64String = new Base64String($value);

        // When
        $debugInfo = $base64String->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            'one byte (a)'        => ['YQ=='],
            'two bytes (ab)'      => ['YWI='],
            'three bytes (abc)'   => ['YWJj'],
            'four bytes (abcd)'   => ['YWJjZA=='],
            'hello'               => ['aGVsbG8='],
            'test'                => ['dGVzdA=='],
            'longer'              => ['dGVzdGluZzEyMw=='],
            'binary 0x01 0x02 0x03' => ['AQID'],
            'plus and slash chars'  => ['Pz8/Pz8/'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $base64String = new Base64String($value);

        // When
        $stringRepresentation = (string) $base64String;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['aGVsbG8=', 'aGVsbG8='],
            ['dGVzdA==', 'dGVzdA=='],
            ['YWJj', 'YWJj'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $base64String = new Base64String($value);

        // When
        $jsonSerialization = \json_encode($base64String);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['aGVsbG8=', '"aGVsbG8="'],
            ['dGVzdA==', '"dGVzdA=="'],
            ['YWJj', '"YWJj"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $base64String = new Base64String($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            'empty (rejected by Nonempty)'   => [''],
            'random punctuation'             => ['not base64!'],
            'sentence'                       => ['hello world'],
            'non-alphabet (@)'               => ['abc@def'],
            'three pad only'                 => ['==='],
            'four pad only'                  => ['===='],
            'extra chars after padding'      => ['aGVsbG8=extra!'],
            'single char unpadded'           => ['A'],
            'two chars unpadded'             => ['AA'],
            'three chars unpadded'           => ['AAA'],
            'six chars unpadded'             => ['YWJjZA'],
            'wrong padding for 1-byte'       => ['A='],
            'too few padding chars'          => ['YQ='],
            'leading equals'                 => ['=YQ=='],
            'embedded whitespace'            => ['YQ =='],
            'leading space'                  => [' YQ=='],
            'trailing space'                 => ['YQ== '],
            'embedded newline'               => ["YQ\n=="],
            'trailing newline'               => ["YQ==\n"],
            'base64url dash'                 => ['Pz8-Pz8_'],
            'base64url underscore'           => ['YQ__'],
            'padding in middle'              => ['YQ==YQ=='],
        ];
    }
}
