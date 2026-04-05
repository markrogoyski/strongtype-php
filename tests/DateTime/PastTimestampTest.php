<?php

declare(strict_types=1);

namespace StrongType\Tests\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\DateTime\PastTimestamp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PastTimestampTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testValidValue()
    {
        // When
        $pastTimestamp = new PastTimestamp(\time() - 86400);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function testValidValueZero()
    {
        // When
        $pastTimestamp = new PastTimestamp(0);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function testGetValue()
    {
        // Given
        $value = \time() - 86400;
        $pastTimestamp = new PastTimestamp($value);

        // When
        $obtainedValue = $pastTimestamp->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    public function testDebugInfo()
    {
        // Given
        $value = \time() - 86400;
        $pastTimestamp = new PastTimestamp($value);

        // When
        $debugInfo = $pastTimestamp->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    #[Test]
    public function testStringRepresentation()
    {
        // Given
        $value = \time() - 86400;
        $pastTimestamp = new PastTimestamp($value);

        // When
        $stringRepresentation = (string) $pastTimestamp;

        // Then
        $this->assertSame(\strval($value), $stringRepresentation);
    }

    #[Test]
    public function testJsonSerialization()
    {
        // Given
        $value = \time() - 86400;
        $pastTimestamp = new PastTimestamp($value);

        // When
        $jsonSerialization = \json_encode($pastTimestamp);

        // Then
        $this->assertSame(\strval($value), $jsonSerialization);
    }

    #[Test]
    public function testInvalidValueInFuture()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $pastTimestamp = new PastTimestamp(\time() + 86400);
    }
}
