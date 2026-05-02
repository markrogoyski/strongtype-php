<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\AllOf;
use StrongType\Constraint\Max;
use StrongType\Constraint\MaxLength;
use StrongType\Constraint\Min;
use StrongType\Constraint\MinLength;
use StrongType\Constraint\Pattern;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[AllOf(new Min(0), new Max(100))]
readonly class AllOfTestPercentage extends Integer
{
}

#[AllOf(new MinLength(3), new MaxLength(10), new Pattern('/^[a-z]+$/'))]
readonly class AllOfTestSlug extends StringType
{
}

class AllOfTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidPercentages')]
    public function testValidPercentage(int $value): void
    {
        // Given / When
        $pct = new AllOfTestPercentage($value);

        // Then
        $this->assertSame($value, $pct->value);
    }

    public static function dataProviderForValidPercentages(): array
    {
        return [[0], [1], [50], [99], [100]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidPercentages')]
    public function testInvalidPercentage(int $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new AllOfTestPercentage($value);
    }

    public static function dataProviderForInvalidPercentages(): array
    {
        return [[-1], [-100], [101], [1000]];
    }

    #[Test]
    #[DataProvider('dataProviderForValidSlugs')]
    public function testValidSlug(string $value): void
    {
        // Given / When
        $slug = new AllOfTestSlug($value);

        // Then
        $this->assertSame($value, $slug->value);
    }

    public static function dataProviderForValidSlugs(): array
    {
        return [['foo'], ['hello'], ['abcdefghij']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidSlugs')]
    public function testInvalidSlug(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new AllOfTestSlug($value);
    }

    public static function dataProviderForInvalidSlugs(): array
    {
        return [
            [''],               // too short
            ['ab'],             // too short
            ['abcdefghijk'],    // too long
            ['Hello'],          // pattern fails (uppercase)
            ['has space'],      // pattern fails
        ];
    }

    #[Test]
    public function testFirstFailureWinsVerbatim(): void
    {
        // Given a value that fails MinLength first (too short, also fails Pattern)
        try {
            // When
            new AllOfTestSlug('A');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            // Then — should be MinLength's verbatim message, not Pattern's
            $this->assertStringContainsString('AllOfTestSlug', $e->getMessage());
            $this->assertStringContainsString('length >= 3', $e->getMessage());
        }
    }

    #[Test]
    public function testThrowsConstraintViolationException(): void
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new AllOfTestPercentage(101);
    }

    #[Test]
    public function testEmptyChildrenThrowsLogicException(): void
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new AllOf();
    }

    #[Test]
    public function testPriorityIsMinimumOfChildren(): void
    {
        // Given
        $combinator = new AllOf(new Pattern('/^a/'), new MinLength(5));

        // When
        $priority = $combinator->priority();

        // Then
        // Pattern=100, MinLength=50 -> min=50
        $this->assertSame(50, $priority);
    }
}
