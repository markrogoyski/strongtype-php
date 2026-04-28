<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\PositiveInt;
use StrongType\Nullable;
use StrongType\String\NonemptyString;
use PHPUnit\Framework\Attributes\Test;

class NullableTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testValidValueWithInt()
    {
        // When
        $nullable = new Nullable(PositiveInt::class, 5);

        // Then
        $this->assertSame(5, $nullable->getValue());
        $this->assertFalse($nullable->isNull());
    }

    #[Test]
    public function testValidValueWithNull()
    {
        // When
        $nullable = new Nullable(PositiveInt::class, null);

        // Then
        $this->assertNull($nullable->getValue());
        $this->assertTrue($nullable->isNull());
    }

    #[Test]
    public function testValidValueWithString()
    {
        // When
        $nullable = new Nullable(NonemptyString::class, 'hello');

        // Then
        $this->assertSame('hello', $nullable->getValue());
        $this->assertFalse($nullable->isNull());
    }

    #[Test]
    public function testDebugInfoWithValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $debugInfo = $nullable->__debugInfo();

        // Then
        $this->assertSame(PositiveInt::class, $debugInfo['type']);
        $this->assertSame(5, $debugInfo['value']);
    }

    #[Test]
    public function testDebugInfoWithNull()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $debugInfo = $nullable->__debugInfo();

        // Then
        $this->assertSame(PositiveInt::class, $debugInfo['type']);
        $this->assertNull($debugInfo['value']);
    }

    #[Test]
    public function testStringRepresentationWithValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $stringRepresentation = (string) $nullable;

        // Then
        $this->assertSame('5', $stringRepresentation);
    }

    #[Test]
    public function testStringRepresentationWithNull()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $stringRepresentation = (string) $nullable;

        // Then
        $this->assertSame('null', $stringRepresentation);
    }

    #[Test]
    public function testJsonSerializationWithValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $jsonSerialization = \json_encode($nullable);

        // Then
        $this->assertSame('5', $jsonSerialization);
    }

    #[Test]
    public function testJsonSerializationWithNull()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $jsonSerialization = \json_encode($nullable);

        // Then
        $this->assertSame('null', $jsonSerialization);
    }

    #[Test]
    public function testInvalidValueThrowsWrappedException()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nullable = new Nullable(PositiveInt::class, -1);
    }

    #[Test]
    public function testInvalidTypeThrowsLogicException()
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        $nullable = new Nullable('NonExistentClass', 5);
    }
}
