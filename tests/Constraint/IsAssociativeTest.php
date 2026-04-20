<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Exception\StrongTypeException;
use StrongType\Constraint\IsAssociative;
use StrongType\Arrays\ArrayType;

#[IsAssociative]
class IsAssociativeTestArray extends ArrayType
{
}

class IsAssociativeTest extends TestCase
{
    #[Test]
    #[DataProvider('validValues')]
    public function testValidValues(array $value): void
    {
        $arr = new IsAssociativeTestArray($value);
        $this->assertSame($value, $arr->getValues());
    }

    public static function validValues(): array
    {
        return [
            [['a' => 1, 'b' => 2]],
            [[1 => 'a', 0 => 'b']],
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function testInvalidValues(array $value): void
    {
        $this->expectException(StrongTypeException::class);
        new IsAssociativeTestArray($value);
    }

    public static function invalidValues(): array
    {
        return [
            [[1, 2, 3]],
            [['a', 'b', 'c']],
            // array_is_list([]) === true, so [] fails !array_is_list.
            // Stack with Nonempty if you want a different error message for [].
            [[]],
        ];
    }
}
