<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ListArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ListArrayTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $listArray = new ListArray($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $listArray = new ListArray($values);

        // When
        $obtainedValues = $listArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[]],
            [[1, 2, 3]],
            [['a', 'b']],
            [[null]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $listArray = new ListArray($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [['a' => 1, 'b' => 2]],
            [[1 => 'a', 0 => 'b']],
        ];
    }
}
