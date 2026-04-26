<?php

declare(strict_types=1);

namespace StrongType\Tests;

use PHPUnit\Framework\Attributes\Test;
use StrongType\HasEquals;
use StrongType\Int\PortNumber;
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

    #[Test]
    public function testImplementsHasEquals(): void
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // Then
        $this->assertInstanceOf(HasEquals::class, $nullable);
    }

    #[Test]
    public function testEqualsAcceptsHasEqualsParam(): void
    {
        // Given a Nullable and a non-Nullable HasEquals with identical underlying value
        $nullable = new Nullable(PositiveInt::class, 5);
        $bare = new PositiveInt(5);

        // When comparing through the HasEquals interface
        // Then a non-Nullable counterpart is never equal to a Nullable
        $this->assertFalse($nullable->equals($bare));
    }

    #[Test]
    public function testNotEqualDifferentTypesSameUnderlyingValue(): void
    {
        // PositiveInt(80) and PortNumber(80) carry identical scalar values but
        // are different wrapped types. Nullable equality must stay type-strict.
        $a = new Nullable(PositiveInt::class, 80);
        $b = new Nullable(PortNumber::class, 80);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }
}
