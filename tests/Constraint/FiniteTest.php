<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\FloatingPoint;
use StrongType\Constraint\Finite;

#[Finite]
readonly class FiniteTestFloat extends FloatingPoint
{
}

class FiniteTest extends TestCase
{
    #[Test]
    #[DataProvider('validValues')]
    public function testValidValues(float $value): void
    {
        $float = new FiniteTestFloat($value);
        $this->assertSame($value, $float->getValue());
    }

    public static function validValues(): array
    {
        return [
            [0.0],
            [1.5],
            [-1.5],
            [PHP_FLOAT_MAX],
            [PHP_FLOAT_MIN],
            [-PHP_FLOAT_MAX],
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function testInvalidValues(float $value): void
    {
        $this->expectException(StrongTypeException::class);
        new FiniteTestFloat($value);
    }

    public static function invalidValues(): array
    {
        return [
            [INF],
            [-INF],
            [NAN],
        ];
    }

    #[Test]
    public function testAllFloatTypesRejectInfinity(): void
    {
        $this->expectException(StrongTypeException::class);
        new \StrongType\Float\UnitFloat(INF);
    }

    #[Test]
    public function testAllFloatTypesRejectNan(): void
    {
        $this->expectException(StrongTypeException::class);
        new \StrongType\Float\UnitFloat(NAN);
    }
}
