<?php

declare(strict_types=1);

namespace StrongType\Tests\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\DateTime\FutureTimestamp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FutureTimestampTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testValidValue()
    {
        // When
        $futureTimestamp = new FutureTimestamp(\time() + 86400);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function testGetValue()
    {
        // Given
        $value = \time() + 86400;
        $futureTimestamp = new FutureTimestamp($value);

        // When
        $obtainedValue = $futureTimestamp->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    public function testDebugInfo()
    {
        // Given
        $value = \time() + 86400;
        $futureTimestamp = new FutureTimestamp($value);

        // When
        $debugInfo = $futureTimestamp->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    #[Test]
    public function testStringRepresentation()
    {
        // Given
        $value = \time() + 86400;
        $futureTimestamp = new FutureTimestamp($value);

        // When
        $stringRepresentation = (string) $futureTimestamp;

        // Then
        $this->assertSame(\strval($value), $stringRepresentation);
    }

    #[Test]
    public function testJsonSerialization()
    {
        // Given
        $value = \time() + 86400;
        $futureTimestamp = new FutureTimestamp($value);

        // When
        $jsonSerialization = \json_encode($futureTimestamp);

        // Then
        $this->assertSame(\strval($value), $jsonSerialization);
    }

    #[Test]
    public function testInvalidValueInPast()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $futureTimestamp = new FutureTimestamp(\time() - 86400);
    }

    #[Test]
    public function testInvalidValueZero()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $futureTimestamp = new FutureTimestamp(0);
    }

    #[Test]
    public function testInvalidValueNegative()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $futureTimestamp = new FutureTimestamp(-1);
    }
}
