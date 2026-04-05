<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\ConstraintInterface;
use StrongType\Constraint\Min;
use StrongType\Constraint\Max;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\Pattern;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\Int\PositiveInt;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\Test;

// Test fixture: no attributes
readonly class PlainInt extends Integer {}

// Test fixture: single attribute
#[Min(0)]
readonly class NonNegTestInt extends Integer {}

// Test fixture: multiple attributes with priority ordering
#[Min(1), Max(100)]
readonly class RangeTestInt extends Integer {}

// Test fixture: child inherits parent attributes
#[Max(50)]
readonly class SmallRangeTestInt extends RangeTestInt {}

class ConstraintValidatorTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testClassWithNoAttributesNoValidation()
    {
        $int = new PlainInt(42);
        $this->assertSame(42, $int->value);

        $int = new PlainInt(-999);
        $this->assertSame(-999, $int->value);
    }

    #[Test]
    public function testClassWithSingleAttribute()
    {
        $int = new NonNegTestInt(0);
        $this->assertSame(0, $int->value);

        $int = new NonNegTestInt(100);
        $this->assertSame(100, $int->value);
    }

    #[Test]
    public function testClassWithSingleAttributeRejects()
    {
        $this->expectException(StrongTypeException::class);
        new NonNegTestInt(-1);
    }

    #[Test]
    public function testClassWithMultipleAttributes()
    {
        $int = new RangeTestInt(1);
        $this->assertSame(1, $int->value);

        $int = new RangeTestInt(100);
        $this->assertSame(100, $int->value);
    }

    #[Test]
    public function testClassWithMultipleAttributesRejectsTooLow()
    {
        $this->expectException(StrongTypeException::class);
        new RangeTestInt(0);
    }

    #[Test]
    public function testClassWithMultipleAttributesRejectsTooHigh()
    {
        $this->expectException(StrongTypeException::class);
        new RangeTestInt(101);
    }

    #[Test]
    public function testChildInheritsParentAttributes()
    {
        // SmallRangeTestInt has Max(50) and inherits Min(1), Max(100) from RangeTestInt
        $int = new SmallRangeTestInt(25);
        $this->assertSame(25, $int->value);
    }

    #[Test]
    public function testChildInheritsParentAttributeRejectsViaParent()
    {
        // Min(1) from parent should still apply
        $this->expectException(StrongTypeException::class);
        new SmallRangeTestInt(0);
    }

    #[Test]
    public function testChildOwnAttributeRejects()
    {
        // Max(50) from child should apply
        $this->expectException(StrongTypeException::class);
        new SmallRangeTestInt(51);
    }

    #[Test]
    public function testCachingSecondInstantiationWorks()
    {
        // First instantiation populates cache
        $a = new RangeTestInt(50);
        // Second instantiation uses cache
        $b = new RangeTestInt(75);
        $this->assertSame(50, $a->value);
        $this->assertSame(75, $b->value);
    }

    #[Test]
    public function testExistingTypesWithManualConstructorsStillWork()
    {
        // PositiveInt has its own constructor with manual validation
        $int = new PositiveInt(5);
        $this->assertSame(5, $int->value);

        $this->expectException(StrongTypeException::class);
        new PositiveInt(-1);
    }
}
