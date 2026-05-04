<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\LanguageCodeString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class LanguageCodeStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $languageCode = new LanguageCodeString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $languageCode = new LanguageCodeString($value);

        // When
        $obtainedValue = $languageCode->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['en'],
            ['fr'],
            ['de'],
            ['ja'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $languageCode = new LanguageCodeString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['EN'],
            ['eng'],
            ['12'],
            ['a'],
            ["en\n"],
        ];
    }
}
