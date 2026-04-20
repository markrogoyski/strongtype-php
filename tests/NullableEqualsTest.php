<?php

declare(strict_types=1);

namespace StrongType\Tests;

use PHPUnit\Framework\Attributes\Test;
use StrongType\Int\PositiveInt;
use StrongType\Nullable;
use StrongType\String\NonemptyString;

class NullableEqualsTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testEqualSameTypeSameValue(): void
    {
        $a = new Nullable(PositiveInt::class, 5);
        $b = new Nullable(PositiveInt::class, 5);

        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testEqualBothNullSameType(): void
    {
        $a = new Nullable(PositiveInt::class, null);
        $b = new Nullable(PositiveInt::class, null);

        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function testNotEqualSameTypeDifferentValue(): void
    {
        $a = new Nullable(PositiveInt::class, 5);
        $b = new Nullable(PositiveInt::class, 6);

        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testNotEqualDifferentType(): void
    {
        $a = new Nullable(PositiveInt::class, 5);
        $b = new Nullable(NonemptyString::class, 'hello');

        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function testNotEqualNullVsValue(): void
    {
        $a = new Nullable(PositiveInt::class, 5);
        $b = new Nullable(PositiveInt::class, null);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    #[Test]
    public function testBothNullDifferentTypesNotEqual(): void
    {
        $a = new Nullable(PositiveInt::class, null);
        $b = new Nullable(NonemptyString::class, null);

        $this->assertFalse($a->equals($b));
    }
}
