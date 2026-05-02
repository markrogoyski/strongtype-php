<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\AllOf;
use StrongType\Constraint\AnyOf;
use StrongType\Constraint\MaxLength;
use StrongType\Constraint\MinLength;
use StrongType\Constraint\Nonblank;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\Not;
use StrongType\Constraint\Pattern;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

// Nested: AnyOf([AllOf([Pattern + MinLength]), Not(Pattern)])
//   either: starts with a letter and length >= 3,
//   or:     does NOT contain digits (i.e., not matching /\d/)
#[AnyOf(
    new AllOf(new Pattern('/^[A-Za-z]/'), new MinLength(3)),
    new Not(new Pattern('/\d/')),
)]
readonly class CombinatorNested extends StringType
{
}

// Combinator alongside non-combinator attributes (mirrors the README ScopedUsername example)
#[
    Nonempty,
    MaxLength(20),
    AnyOf(new Pattern('/^user_/'), new Pattern('/^admin_/')),
    Not(new Pattern('/^\d+$/')),
]
readonly class CombinatorAlongsideAttrs extends StringType
{
}

class CombinatorIntegrationTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForNestedValid')]
    public function testNestedValid(string $value): void
    {
        // Given / When
        $obj = new CombinatorNested($value);

        // Then
        $this->assertSame($value, $obj->value);
    }

    public static function dataProviderForNestedValid(): array
    {
        return [
            ['abc'],             // AllOf passes (letter + len 3)
            ['hello world'],     // AllOf passes
            [''],                // empty has no digits -> Not(Pattern(\d)) passes
            ['no digits here'],  // no digits -> Not branch passes
            ['ab1'],             // AllOf passes (leading letter + len 3); also has digit, but AnyOf only needs one
        ];
    }

    public static function dataProviderForNestedInvalid(): array
    {
        return [
            ['Z9'],   // both fail (len < 3 and contains digit)
            ['12'],   // AllOf fails (no leading letter), has digits
            ['9a'],   // AllOf fails (leading digit), has digit
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForNestedInvalid')]
    public function testNestedInvalid(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new CombinatorNested($value);
    }

    #[Test]
    #[DataProvider('dataProviderForAlongsideValid')]
    public function testAlongsideValid(string $value): void
    {
        // Given / When
        $obj = new CombinatorAlongsideAttrs($value);

        // Then
        $this->assertSame($value, $obj->value);
    }

    public static function dataProviderForAlongsideValid(): array
    {
        return [
            ['user_alice'],
            ['admin_root'],
            ['user_a'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForAlongsideInvalid')]
    public function testAlongsideInvalid(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new CombinatorAlongsideAttrs($value);
    }

    public static function dataProviderForAlongsideInvalid(): array
    {
        return [
            [''],                      // Nonempty
            ['guest_xyz'],             // AnyOf fails (no matching prefix)
            ['user_aaaaaaaaaaaaaaaaaaaa'],  // 25 chars > MaxLength(20)
        ];
    }

    #[Test]
    public function testAllOfRunsChildrenInArgumentOrderNotPriorityOrder(): void
    {
        // Given a class where AllOf wraps a high-priority Pattern (100) before a
        // low-priority Nonblank (60). Stacked attributes would run Nonblank first
        // (priority order); AllOf must run them in argument order.
        $combinator = new AllOf(new Pattern('/^[a-z]+$/'), new Nonblank());

        // When validating a value that fails both children
        $error = $combinator->validate('   ', 'Demo\\Thing');

        // Then the Pattern message wins because it is the first argument
        $this->assertNotNull($error);
        $this->assertStringContainsString('match pattern', $error);
        $this->assertStringNotContainsString('blank', $error);
    }

    #[Test]
    public function testPriorityInteractionWithSiblingAttribute(): void
    {
        // The combinator's effective priority is min of children, so it can
        // interleave with non-combinator siblings. We assert behavior, not a
        // specific exception text — first failure wins per phase 6 policy.
        // Given a value that fails Nonempty first (priority lower than AnyOf),
        // we get Nonempty's message even though AnyOf would also fail.
        try {
            // When
            new CombinatorAlongsideAttrs('');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            // Then — Nonempty (priority 50) runs before AnyOf (min child = Pattern = 100)
            $message = $e->getMessage();
            $this->assertStringContainsString('CombinatorAlongsideAttrs', $message);
            // First-failure semantic: 'one of' should NOT be present because
            // Nonempty fired before the combinator was reached.
            $this->assertStringNotContainsString('one of', $message);
        }
    }
}
