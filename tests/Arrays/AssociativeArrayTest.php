<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\AssociativeArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class AssociativeArrayTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $associativeArray = new AssociativeArray($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $associativeArray = new AssociativeArray($values);

        // When
        $obtainedValues = $associativeArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [['a' => 1, 'b' => 2]],
            [['key' => 'value']],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $associativeArray = new AssociativeArray($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1, 2, 3]],
        ];
    }

    /**
     * Locks in that Nonempty fires before IsAssociative on [], so users get the
     * "must not be empty" message rather than "must be an associative array".
     * Both constraints have priority 50; this test guards against a future
     * priority change silently swapping the message.
     */
    #[Test]
    public function testEmptyArrayProducesNonemptyMessage()
    {
        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('AssociativeArray type must not be empty');

        // When
        new AssociativeArray([]);
    }
}
