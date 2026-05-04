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
            ['text-/plain'],
            ['text/plain.'],
            ['text/plain+'],
            ['text/plain-'],
            ['./plain'],
            ['text/.plain'],
            ['text/plain/extra'],
            ['TEXT PLAIN'],
            ['text/ plain'],
            [' text/plain'],
            ['text/plain '],
            ["text/html\n"],
        ];
    }
}
