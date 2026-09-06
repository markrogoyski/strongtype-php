<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Exception\StrongTypeException;
use StrongType\Constraint\IsList;
use StrongType\Arrays\ArrayType;

#[IsList]
readonly class IsListTestArray extends ArrayType
{
}

class IsListTest extends TestCase
{
    #[Test]
    #[DataProvider('validValues')]
    public function testValidValues(array $value): void
    {
        $arr = new IsListTestArray($value);
        $this->assertSame($value, $arr->getValues());
    }

    public static function validValues(): array
    {
        return [
            [[]],
            [[1, 2, 3]],
            [['a', 'b', 'c']],
            [[null]],
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function testInvalidValues(array $value): void
    {
        $this->expectException(StrongTypeException::class);
        new IsListTestArray($value);
    }

    public static function invalidValues(): array
    {
        return [
            [['a' => 1, 'b' => 2]],
            [[1 => 'a', 0 => 'b']],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForElementCountMessages')]
    public function testRejectionMessageReportsElementCount(array $value, string $expectedMessage): void
    {
        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage($expectedMessage);

        // When
        new IsListTestArray($value);
    }

    public static function dataProviderForElementCountMessages(): array
    {
        return [
            'singular' => [['a' => 1], 'got a non-list array (1 element(s))'],
            'plural'   => [['a' => 1, 'b' => 2], 'got a non-list array (2 element(s))'],
        ];
    }

    #[Test]
    public function testNonListRejectionWithHostileElementIsCatchable(): void
    {
        // Given
        // A non-list array (so IsList fails) whose element has a throwing
        // __debugInfo(): the old print_r-based message would fatal here.
        $hostile = new class {
            public function __debugInfo(): array
            {
                throw new \RuntimeException('boom');
            }
        };

        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new IsListTestArray(['key' => $hostile]);
    }

    #[Test]
    public function testPriorityIsSizeBand(): void
    {
        // Given
        $constraint = new IsList();

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(50, $priority);
    }
}
