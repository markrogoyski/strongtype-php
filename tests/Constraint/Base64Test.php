<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Base64;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Base64]
readonly class Base64TestString extends StringType
{
}

class Base64Test extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value): void
    {
        // When
        $obj = new Base64TestString($value);

        // Then
        $this->assertSame($value, $obj->value);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            'empty string'        => [''],
            'one byte (a)'        => ['YQ=='],
            'two bytes (ab)'      => ['YWI='],
            'three bytes (abc)'   => ['YWJj'],
            'four bytes (abcd)'   => ['YWJjZA=='],
            'five bytes (abcde)'  => ['YWJjZGU='],
            'six bytes (abcdef)'  => ['YWJjZGVm'],
            'hello'               => ['aGVsbG8='],
            'test'                => ['dGVzdA=='],
            'longer'              => ['dGVzdGluZzEyMw=='],
            'all alphabet bytes'  => ['ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'],
            'plus and slash'      => ['Pz8/Pz8/'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new Base64TestString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            'single char unpadded'             => ['A'],
            'two chars unpadded'               => ['AA'],
            'three chars unpadded'             => ['AAA'],
            'six chars unpadded (truncated)'   => ['YWJjZA'],
            'wrong padding for 1-byte'         => ['A='],
            'too few padding chars'            => ['YQ='],
            'three pad only'                   => ['==='],
            'four pad only'                    => ['===='],
            'leading equals'                   => ['=YQ=='],
            'embedded whitespace'              => ['YQ ==' ],
            'leading space'                    => [' YQ=='],
            'trailing space'                   => ['YQ== '],
            'embedded newline'                 => ["YQ\n=="],
            'trailing newline'                 => ["YQ==\n"],
            'non-alphabet char (!)'            => ['abc@def'],
            'non-alphabet sentence'            => ['hello world'],
            'random punctuation'               => ['not valid base64!!!'],
            'base64url dash'                   => ['Pz8-Pz8_'],
            'base64url underscore'             => ['YQ__'],
            'extra chars after padding'        => ['aGVsbG8=extra!'],
            'padding in middle'                => ['YQ==YQ=='],
            'wrong-length valid alphabet'      => ['abcde'],
        ];
    }

    #[Test]
    public function testErrorMessageFormat(): void
    {
        // Given
        $invalid = 'not base64!';

        // When
        try {
            new Base64TestString($invalid);
            $this->fail('Expected StrongTypeException');
        } catch (StrongTypeException $e) {
            // Then
            $this->assertStringContainsString('Base64TestString', $e->getMessage());
            $this->assertStringContainsString('base64', \strtolower($e->getMessage()));
            $this->assertStringContainsString($invalid, $e->getMessage());
        }
    }
}
