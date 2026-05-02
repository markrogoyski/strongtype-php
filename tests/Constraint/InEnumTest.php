<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\InEnum;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

enum InEnumTestStringStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}

enum InEnumTestIntPriority: int
{
    case Low = 1;
    case Medium = 5;
    case High = 10;
}

enum InEnumTestPureColor
{
    case Red;
    case Green;
    case Blue;
}

#[InEnum(InEnumTestStringStatus::class)]
readonly class InEnumTestStatusCode extends StringType
{
}

#[InEnum(InEnumTestIntPriority::class)]
readonly class InEnumTestPriorityCode extends Integer
{
}

#[InEnum(InEnumTestPureColor::class)]
readonly class InEnumTestColorName extends StringType
{
}

class InEnumTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidStringBackedValues')]
    public function testValidStringBackedValue(string $value): void
    {
        // Given / When
        $code = new InEnumTestStatusCode($value);

        // Then
        $this->assertSame($value, $code->value);
    }

    public static function dataProviderForValidStringBackedValues(): array
    {
        return [
            ['active'],
            ['inactive'],
            ['pending'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidStringBackedValues')]
    public function testInvalidStringBackedValue(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new InEnumTestStatusCode($value);
    }

    public static function dataProviderForInvalidStringBackedValues(): array
    {
        return [
            [''],
            ['Active'],          // case-sensitive: enum value is 'active'
            ['ACTIVE'],
            ['unknown'],
            ['Active '],         // trailing whitespace
            [' active'],         // leading whitespace
            ['Red'],              // a case name from a different enum
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidIntBackedValues')]
    public function testValidIntBackedValue(int $value): void
    {
        // Given / When
        $code = new InEnumTestPriorityCode($value);

        // Then
        $this->assertSame($value, $code->value);
    }

    public static function dataProviderForValidIntBackedValues(): array
    {
        return [
            [1],
            [5],
            [10],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidIntBackedValues')]
    public function testInvalidIntBackedValue(int $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new InEnumTestPriorityCode($value);
    }

    public static function dataProviderForInvalidIntBackedValues(): array
    {
        return [
            [0],
            [-1],
            [2],
            [11],
            [\PHP_INT_MAX],
            [\PHP_INT_MIN],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidPureEnumValues')]
    public function testValidPureEnumValue(string $value): void
    {
        // Given / When
        $name = new InEnumTestColorName($value);

        // Then
        $this->assertSame($value, $name->value);
    }

    public static function dataProviderForValidPureEnumValues(): array
    {
        return [
            ['Red'],
            ['Green'],
            ['Blue'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidPureEnumValues')]
    public function testInvalidPureEnumValue(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new InEnumTestColorName($value);
    }

    public static function dataProviderForInvalidPureEnumValues(): array
    {
        return [
            [''],
            ['red'],         // case-sensitive: case name is 'Red'
            ['RED'],
            ['Yellow'],
            ['active'],       // backing value of a different enum
            ['Red '],
        ];
    }

    #[Test]
    public function testThrowsConstraintViolationException(): void
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new InEnumTestStatusCode('unknown');
    }

    #[Test]
    public function testErrorMessageMentionsTypeAndValueForStringBacked(): void
    {
        try {
            // When
            new InEnumTestStatusCode('unknown');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            // Then
            $this->assertStringContainsString('InEnumTestStatusCode', $e->getMessage());
            $this->assertStringContainsString('unknown', $e->getMessage());
        }
    }

    #[Test]
    public function testErrorMessageMentionsTypeAndValueForPureEnum(): void
    {
        try {
            // When
            new InEnumTestColorName('Yellow');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            // Then
            $this->assertStringContainsString('InEnumTestColorName', $e->getMessage());
            $this->assertStringContainsString('Yellow', $e->getMessage());
        }
    }

    #[Test]
    public function testLogicExceptionWhenNonEnumClass(): void
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new InEnum(\stdClass::class);
    }

    #[Test]
    public function testLogicExceptionWhenNonExistentClass(): void
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new InEnum('StrongType\\Tests\\Constraint\\DoesNotExistEnum');
    }

    #[Test]
    public function testLogicExceptionWhenInterfaceName(): void
    {
        // Then
        $this->expectException(\LogicException::class);

        // When
        new InEnum(\Stringable::class);
    }

    #[Test]
    public function testPriorityIsContentBand(): void
    {
        // Given
        $constraint = new InEnum(InEnumTestStringStatus::class);

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(60, $priority);
    }
}
