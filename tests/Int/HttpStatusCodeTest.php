<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\HttpStatusCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class HttpStatusCodeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $httpStatusCode = new HttpStatusCode($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $httpStatusCode = new HttpStatusCode($value);

        // When
        $obtainedValue = $httpStatusCode->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $httpStatusCode = new HttpStatusCode($value);

        // When
        $debugInfo = $httpStatusCode->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [100],
            [200],
            [301],
            [404],
            [500],
            [599],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $httpStatusCode = new HttpStatusCode($value);

        // When
        $stringRepresentation = (string) $httpStatusCode;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $httpStatusCode = new HttpStatusCode($value);

        // When
        $jsonSerialization = \json_encode($httpStatusCode);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [100, '100'],
            [200, '200'],
            [301, '301'],
            [404, '404'],
            [500, '500'],
            [599, '599'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $httpStatusCode = new HttpStatusCode($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [99],
            [600],
            [0],
            [-1],
            [1000],
        ];
    }
}
