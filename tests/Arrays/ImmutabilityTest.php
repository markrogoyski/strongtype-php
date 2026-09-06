<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Arrays\ArrayOfInts;
use StrongType\Arrays\ArrayType;
use StrongType\Arrays\AssociativeArray;
use StrongType\Arrays\EmptyArray;
use StrongType\Arrays\FixedSizeArray;
use StrongType\Arrays\ListArray;
use StrongType\Arrays\NonemptyArray;
use StrongType\Arrays\UniqueArray;

/**
 * A subclass that tries to mutate the validated array after construction. The whole
 * ArrayType hierarchy is readonly, so the write is an Error rather than a silent
 * bypass of the type's constraints.
 */
readonly class MutatingNonemptyArray extends NonemptyArray
{
    public function clear(): void
    {
        $this->values = [];
    }
}

class ImmutabilityTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProviderForArrayTypeClasses')]
    public function testArrayTypeClassIsReadonly(string $class): void
    {
        // Given
        $reflection = new \ReflectionClass($class);

        // When
        $isReadonly = $reflection->isReadOnly();

        // Then
        $this->assertTrue($isReadonly, "{$class} must be readonly");
    }

    public static function dataProviderForArrayTypeClasses(): array
    {
        $classes = [ArrayType::class];
        foreach (\glob(__DIR__ . '/../../src/Arrays/*.php') ?: [] as $file) {
            $classes[] = 'StrongType\\Arrays\\' . \basename($file, '.php');
        }

        return \array_map(static fn(string $class): array => [$class], \array_values(\array_unique($classes)));
    }

    #[Test]
    public function testExternalReassignmentThrows(): void
    {
        // Given
        $array = new NonemptyArray([1, 2, 3]);

        // Then
        $this->expectException(\Error::class);

        // When
        $array->values = [];
    }

    #[Test]
    public function testSubclassReassignmentThrows(): void
    {
        // Given
        $array = new MutatingNonemptyArray([1, 2, 3]);

        // Then
        $this->expectException(\Error::class);

        // When
        $array->clear();
    }

    #[Test]
    public function testValuesSurviveAFailedSubclassReassignment(): void
    {
        // Given
        $array = new MutatingNonemptyArray([1, 2, 3]);

        // When
        try {
            $array->clear();
        } catch (\Error) {
            // expected
        }

        // Then the type's #[Nonempty] invariant still holds
        $this->assertSame([1, 2, 3], $array->getValues());
    }

    #[Test]
    public function testFixedSizeArraySizeIsAlsoReadonly(): void
    {
        // Given
        $array = new FixedSizeArray([1, 2, 3], 3);

        // Then
        $this->expectException(\Error::class);

        // When
        $array->size = 4;
    }

    /**
     * The assignment above would also fail on `private(set)` alone, so assert the readonly
     * flag directly — the point is that the readonly class made $size write-once, not merely
     * externally unwritable.
     */
    #[Test]
    public function testFixedSizeArraySizeCarriesTheReadonlyFlag(): void
    {
        // Given
        $property = new \ReflectionProperty(FixedSizeArray::class, 'size');

        // When
        $isReadonly = $property->isReadOnly();

        // Then
        $this->assertTrue($isReadonly);
    }

    #[Test]
    #[DataProvider('dataProviderForConstructedInstances')]
    public function testConstructionAndValueAccessStillWork(ArrayType $array, array $expected): void
    {
        // When
        $values = $array->getValues();

        // Then
        $this->assertSame($expected, $values);
    }

    public static function dataProviderForConstructedInstances(): array
    {
        return [
            'ListArray'        => [new ListArray([1, 2, 3]), [1, 2, 3]],
            'NonemptyArray'    => [new NonemptyArray(['a']), ['a']],
            'AssociativeArray' => [new AssociativeArray(['k' => 'v']), ['k' => 'v']],
            'UniqueArray'      => [new UniqueArray([1, 2]), [1, 2]],
            'EmptyArray'       => [new EmptyArray([]), []],
            'ArrayOfInts'      => [new ArrayOfInts([1, 2]), [1, 2]],
            'FixedSizeArray'   => [new FixedSizeArray([1, 2], 2), [1, 2]],
        ];
    }

    #[Test]
    public function testWithValuesReturnsANewInstanceRatherThanMutating(): void
    {
        // Given
        $original = new NonemptyArray([1, 2, 3]);

        // When
        $copy = $original->withValues([4, 5]);

        // Then
        $this->assertSame([1, 2, 3], $original->getValues());
        $this->assertSame([4, 5], $copy->getValues());
        $this->assertNotSame($original, $copy);
    }

    /**
     * Shallow validation by design: elements are validated, not deep-copied, so an
     * object element remains externally mutable even though the array itself is frozen.
     */
    #[Test]
    public function testObjectElementsRemainAliased(): void
    {
        // Given
        $element = new \stdClass();
        $element->flag = 'before';
        $array = new NonemptyArray([$element]);

        // When
        $element->flag = 'after';

        // Then
        $this->assertSame('after', $array->getValues()[0]->flag);
    }

    /**
     * A by-reference element goes further than object aliasing: it can be replaced with a
     * value of a different type after validation, so even ElementType stops holding. Locked
     * in as a documented limitation (docs/semantics.md#element-aliasing), not as desired
     * behavior — the library deliberately does not detach references on construction.
     */
    #[Test]
    public function testByReferenceElementsCanBeReplacedAfterValidation(): void
    {
        // Given an int element passed by reference, which validates as an int
        $count = 1;
        $counts = new ArrayOfInts([&$count]);
        $this->assertSame([1], $counts->getValues());

        // When the caller writes a different type through the reference it still holds
        $count = 'not an int';

        // Then the stored element changed type, despite #[ElementType('int')]
        $this->assertSame(['not an int'], $counts->getValues());
    }

    #[Test]
    public function testByReferenceObjectElementsCanBeSwappedWholesale(): void
    {
        // Given
        $element = new \stdClass();
        $element->flag = 'before';
        $array = new NonemptyArray([&$element]);

        // When the reference is repointed at an entirely different object
        $replacement = new \stdClass();
        $replacement->flag = 'replaced';
        $element = $replacement;

        // Then
        $this->assertSame('replaced', $array->getValues()[0]->flag);
    }

    /**
     * PHP permits reinitializing a readonly property inside __clone(), so `readonly` stops
     * post-construction reassignment but does not by itself make a clone re-validate.
     * Documented in docs/semantics.md#mutability; no built-in type declares __clone().
     */
    #[Test]
    public function testNoBuiltInArrayTypeDeclaresACloneHook(): void
    {
        foreach (self::dataProviderForArrayTypeClasses() as [$class]) {
            // Given
            $reflection = new \ReflectionClass($class);

            // When
            $hasClone = $reflection->hasMethod('__clone');

            // Then
            $this->assertFalse($hasClone, "{$class} must not declare __clone()");
        }
    }

    #[Test]
    public function testPlainCloneKeepsTheValidatedValues(): void
    {
        // Given
        $original = new NonemptyArray([1, 2, 3]);

        // When
        $copy = clone $original;

        // Then
        $this->assertSame([1, 2, 3], $copy->getValues());
        $this->assertTrue($original->equals($copy));
    }
}
