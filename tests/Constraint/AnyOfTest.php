<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\AnyOf;
use StrongType\Constraint\InRange;
use StrongType\Constraint\Min;
use StrongType\Constraint\MinLength;
use StrongType\Constraint\Pattern;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[AnyOf(new InRange(1, 1023), new InRange(49152, 65535))]
readonly class AnyOfTestPort extends Integer
{
}

#[AnyOf(new MinLength(8), new Pattern('/^[A-Z]/'))]
readonly class AnyOfTestPasswordOrCapitalized extends StringType
{
}

class AnyOfTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidPorts')]
    public function testValidPort(int $value): void
    {
        // Given
        $port = new AnyOfTestPort($value);

        // When
        $obtained = $port->getValue();

        // Then
        $this->assertSame($value, $obtained);
    }

    public static function dataProviderForValidPorts(): array
    {
        return [
            [1],        // boundary low of well-known
            [80],       // typical well-known
            [1023],     // boundary high of well-known
            [49152],    // boundary low of ephemeral
            [50000],    // typical ephemeral
            [65535],    // boundary high of ephemeral
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidPorts')]
    public function testInvalidPort(int $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new AnyOfTestPort($value);
    }

    public static function dataProviderForInvalidPorts(): array
    {
        return [
            [0],        // below well-known
            [-1],       // negative
            [1024],     // gap (registered range)
            [10000],    // mid-gap
            [49151],    // just below ephemeral
            [65536],    // above max
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidPasswordOrCapitalized')]
    public function testValidPasswordOrCapitalized(string $value): void
    {
        // Given / When
        $obj = new AnyOfTestPasswordOrCapitalized($value);

        // Then
        $this->assertSame($value, $obj->value);
    }

    public static function dataProviderForValidPasswordOrCapitalized(): array
    {
        return [
            ['Apple'],          // capitalized, short
            ['hello123world'],  // long, not capitalized
            ['Z'],              // single capital letter
            ['12345678'],       // 8 chars, not capitalized
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidPasswordOrCapitalized')]
    public function testInvalidPasswordOrCapitalized(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new AnyOfTestPasswordOrCapitalized($value);
    }

    public static function dataProviderForInvalidPasswordOrCapitalized(): array
    {
        return [
            [''],           // fails both (empty, not capitalized)
            ['short'],      // 5 chars and lowercase
            ['1234567'],    // 7 chars and starts with digit
        ];
    }

    #[Test]
    public function testErrorMessageMentionsOneOf(): void
    {
        try {
            // When
            new AnyOfTestPort(10000);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            // Then
            $this->assertStringContainsString('AnyOfTestPort', $e->getMessage());
            $this->assertStringContainsString('one of', $e->getMessage());
            $this->assertStringContainsString('1023', $e->getMessage());
            $this->assertStringContainsString('49152', $e->getMessage());
            $this->assertStringContainsString('10000', $e->getMessage());
        }
    }

    #[Test]
    public function testThrowsConstraintViolationException(): void
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new AnyOfTestPort(10000);
    }

    #[Test]
    public function testEmptyChildrenThrowsLogicException(): void
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new AnyOf();
    }

    #[Test]
    public function testPriorityIsMinimumOfChildren(): void
    {
        // Given
        $combinator = new AnyOf(new Pattern('/^a/'), new MinLength(5), new Min(0));

        // When
        $priority = $combinator->priority();

        // Then
        // Pattern=100, MinLength=50, Min=50 -> min=50
        $this->assertSame(50, $priority);
    }

    #[Test]
    public function testPriorityWithSingleChild(): void
    {
        // Given
        $combinator = new AnyOf(new Pattern('/^a/'));

        // When
        $priority = $combinator->priority();

        // Then
        $this->assertSame(100, $priority);
    }
}
