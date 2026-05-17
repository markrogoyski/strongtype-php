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
class IsListTestArray extends ArrayType
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
