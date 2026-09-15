<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\MimeTypeString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MimeTypeStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $mimeType = new MimeTypeString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $mimeType = new MimeTypeString($value);

        // When
        $obtainedValue = $mimeType->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['text/plain'],
            ['application/json'],
            ['image/png'],
            ['text/html'],
            ['application/octet-stream'],
            ['application/vnd.api+json'],
            ['application/hal+json'],
            ['application/ld+json'],
            ['multipart/form-data'],
            ['image/svg+xml'],
            ['a/b'],
            // RFC 6838 restricted-name-chars restricts only the first character of each
            // side, not the last -- trailing punctuation is syntactically legal.
            ['text-/plain'],
            ['text/plain.'],
            ['text/plain+'],
            ['text/plain-'],
            // 127 characters (1 restricted-name-first + 126 restricted-name-chars) is
            // the maximum length RFC 6838 allows per side.
            [\str_repeat('a', 127) . '/b'],
            ['b/' . \str_repeat('a', 127)],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $mimeType = new MimeTypeString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['text'],
            ['/plain'],
            ['text/'],
            ['text plain'],
            ['./plain'],
            ['text/.plain'],
            ['text/plain/extra'],
            ['TEXT PLAIN'],
            ['text/ plain'],
            [' text/plain'],
            ['text/plain '],
            ["text/html\n"],
            // 128 characters exceeds RFC 6838's 127-character-per-side cap.
            [\str_repeat('a', 128) . '/b'],
            ['b/' . \str_repeat('a', 128)],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $mimeType = new MimeTypeString($value);

        // When
        $debugInfo = $mimeType->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testStringRepresentation(string $value)
    {
        // Given
        $mimeType = new MimeTypeString($value);

        // When
        $stringRepresentation = (string) $mimeType;

        // Then
        $this->assertSame($value, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testJsonSerialization(string $value)
    {
        // Given
        $mimeType = new MimeTypeString($value);

        // When
        $jsonSerialization = \json_encode($mimeType);

        // Then
        $this->assertSame(\json_encode($value), $jsonSerialization);
    }
}
