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
readonly class PlainInt extends Integer
{
}

// Test fixture: single attribute
#[Min(0)]
readonly class NonNegTestInt extends Integer
{
}

// Test fixture: multiple attributes with priority ordering
#[Min(1), Max(100)]
readonly class RangeTestInt extends Integer
{
}

// Test fixture: child inherits parent attributes
#[Max(50)]
readonly class SmallRangeTestInt extends RangeTestInt
{
}

// Sibling test fixtures: common parent, different own constraints.
// Used to verify the per-class cache does not conflate siblings.
#[Min(0)]
readonly class SiblingParentInt extends Integer
{
}

#[Max(10)]
readonly class SiblingChildA extends SiblingParentInt
{
}

#[Max(100)]
readonly class SiblingChildB extends SiblingParentInt
{
}

// Constraint fixtures for priority-collision test: both share priority 75
// and each records its own invocation so order/presence can be asserted.
final class PriorityCollisionTracker
{
    /** @var list<string> */
    public static array $log = [];
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class TaggedCollisionA implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        PriorityCollisionTracker::$log[] = 'A';
        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 75;
    }
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class TaggedCollisionB implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        PriorityCollisionTracker::$log[] = 'B';
        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 75;
    }
}

#[TaggedCollisionA, TaggedCollisionB]
readonly class CollisionTestInt extends Integer
{
}

// Constraint fixtures for the short-circuit test: the lower-priority constraint
// always fails, the higher-priority constraint records its invocation. If
// validation is short-circuiting at the first failure, the second never runs.
final class ShortCircuitTracker
{
    /** @var list<string> */
    public static array $log = [];
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class FailingFirstConstraint implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        ShortCircuitTracker::$log[] = 'first';
        return 'first failed';
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class RecordingSecondConstraint implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        ShortCircuitTracker::$log[] = 'second';
        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}

#[FailingFirstConstraint, RecordingSecondConstraint]
readonly class ShortCircuitTestInt extends Integer
{
}

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

    #[Test]
    public function testSiblingSubclassesHaveIsolatedConstraintSets()
    {
        // Sibling cache keys are per-class, not per-parent. SiblingChildA
        // accepts up to 10, SiblingChildB up to 100 — their shared parent
        // constraints must compose with each child's own, independently.
        $a = new SiblingChildA(5);
        $b = new SiblingChildB(50);
        $this->assertSame(5, $a->value);
        $this->assertSame(50, $b->value);

        // And each child still enforces its own Max, not the sibling's.
        $b = new SiblingChildB(100);
        $this->assertSame(100, $b->value);
    }

    #[Test]
    public function testSiblingChildAEnforcesOwnMax()
    {
        $this->expectException(StrongTypeException::class);
        new SiblingChildA(11);
    }

    #[Test]
    public function testSiblingChildBEnforcesOwnMax()
    {
        $this->expectException(StrongTypeException::class);
        new SiblingChildB(101);
    }

    #[Test]
    public function testSharedParentConstraintStillAppliesToChild()
    {
        // Min(0) on SiblingParentInt must still reject negatives on children.
        $this->expectException(StrongTypeException::class);
        new SiblingChildA(-1);
    }

    #[Test]
    public function testEqualPriorityConstraintsAllFire()
    {
        // When two constraints share a priority, both must run — the sort is
        // stable enough that neither is silently dropped.
        PriorityCollisionTracker::$log = [];

        new CollisionTestInt(1);

        $this->assertContains('A', PriorityCollisionTracker::$log);
        $this->assertContains('B', PriorityCollisionTracker::$log);
        $this->assertCount(2, PriorityCollisionTracker::$log);
    }

    #[Test]
    public function testValidationStopsAtFirstFailingConstraint()
    {
        // Given: two constraints — first (priority 50) always fails, second
        // (priority 100) records its invocation. Validation should short-circuit
        // on the first failure and never invoke the second.
        ShortCircuitTracker::$log = [];

        // Then
        $this->expectException(StrongTypeException::class);

        // When
        try {
            new ShortCircuitTestInt(0);
        } finally {
            $this->assertSame(['first'], ShortCircuitTracker::$log);
        }
    }
}
