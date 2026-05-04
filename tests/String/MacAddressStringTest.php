<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\MacAddressString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MacAddressStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $macAddressString = new MacAddressString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $macAddressString = new MacAddressString($value);

        // When
        $obtainedValue = $macAddressString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $macAddressString = new MacAddressString($value);

        // When
        $debugInfo = $macAddressString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['00:1A:2B:3C:4D:5E'],
            ['aa:bb:cc:dd:ee:ff'],
            ['AA:BB:CC:DD:EE:FF'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $macAddressString = new MacAddressString($value);

        // When
        $stringRepresentation = (string) $macAddressString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['00:1A:2B:3C:4D:5E', '00:1A:2B:3C:4D:5E'],
            ['aa:bb:cc:dd:ee:ff', 'aa:bb:cc:dd:ee:ff'],
            ['AA:BB:CC:DD:EE:FF', 'AA:BB:CC:DD:EE:FF'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $macAddressString = new MacAddressString($value);

        // When
        $jsonSerialization = \json_encode($macAddressString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['00:1A:2B:3C:4D:5E', '"00:1A:2B:3C:4D:5E"'],
            ['aa:bb:cc:dd:ee:ff', '"aa:bb:cc:dd:ee:ff"'],
            ['AA:BB:CC:DD:EE:FF', '"AA:BB:CC:DD:EE:FF"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $macAddressString = new MacAddressString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['00-1A-2B-3C-4D-5E'],
            ['00:1A:2B:3C:4D'],
            ['GG:HH:II:JJ:KK:LL'],
            ['not-a-mac'],
            ["00:11:22:33:44:55\n"],
        ];
    }
}
