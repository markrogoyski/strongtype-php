<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\AnyValueType;
use StrongType\Constraint\ConstraintInterface;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\ValueType;
use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayType;
use StrongType\Bool\BoolType;
use StrongType\DateTime\DateTime;
use StrongType\Float\FloatingPoint;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\Test;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Palindrome implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \StrongType\Util\ShortClassName::of($className);

        if (\is_string($value) && $value !== \strrev($value)) {
            return "{$short} type must be a palindrome, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 200;
    }

    /** @return non-empty-list<ValueType> */
    public function acceptedValueTypes(): array
    {
        return [ValueType::String];
    }
}

// A genuinely type-agnostic custom constraint: it rejects the same forbidden value on
// every base, so AnyValueType is the honest declaration rather than a shortcut.
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class NotForbidden implements ConstraintInterface
{
    use AnyValueType;

    public function validate(mixed $value, string $className): ?string
    {
        $short = \StrongType\Util\ShortClassName::of($className);

        if ($value === 0 || $value === 0.0 || $value === 'forbidden' || $value === false || $value === []) {
            return "{$short} type must not be the forbidden value";
        }

        return null;
    }

    public function priority(): int
    {
        return 200;
    }
}

#[Nonempty, Palindrome]
readonly class PalindromeString extends StringType
{
}

#[NotForbidden]
readonly class AnyValueTypeInt extends Integer
{
}

#[NotForbidden]
readonly class AnyValueTypeFloat extends FloatingPoint
{
}

#[NotForbidden]
readonly class AnyValueTypeString extends StringType
{
}

#[NotForbidden]
readonly class AnyValueTypeBool extends BoolType
{
}

#[NotForbidden]
readonly class AnyValueTypeDateTime extends DateTime
{
}

#[NotForbidden]
class AnyValueTypeArray extends ArrayType
{
}

class CustomConstraintTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testCustomConstraintAcceptsValid()
    {
        $str = new PalindromeString('racecar');
        $this->assertSame('racecar', $str->value);
    }

    #[Test]
    public function testCustomConstraintAcceptsSingleChar()
    {
        $str = new PalindromeString('a');
        $this->assertSame('a', $str->value);
    }

    #[Test]
    public function testCustomConstraintRejectsInvalid()
    {
        $this->expectException(StrongTypeException::class);
        new PalindromeString('hello');
    }

    #[Test]
    public function testCustomConstraintRejectsEmpty()
    {
        $this->expectException(StrongTypeException::class);
        new PalindromeString('');
    }

    #[Test]
    public function testCustomConstraintErrorMessage()
    {
        try {
            new PalindromeString('hello');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertStringContainsString('PalindromeString', $e->getMessage());
            $this->assertStringContainsString('palindrome', $e->getMessage());
            $this->assertStringContainsString('hello', $e->getMessage());
        }
    }

    #[Test]
    public function testCustomConstraintDeclaresItsAcceptedValueTypes()
    {
        // Given
        $constraint = new Palindrome();

        // When
        $accepted = $constraint->acceptedValueTypes();

        // Then
        $this->assertSame([ValueType::String], $accepted);
    }

    #[Test]
    public function testAnyValueTypeTraitReturnsEveryValueType()
    {
        // Given
        $constraint = new NotForbidden();

        // When
        $accepted = $constraint->acceptedValueTypes();

        // Then
        $this->assertSame(ValueType::cases(), $accepted);
    }

    #[Test]
    public function testAnyValueTypeConstraintResolvesOnEveryBase()
    {
        // Given / When -- a constraint using AnyValueType must be applicable everywhere,
        // so none of these definitions may be rejected at resolve time.
        $int      = new AnyValueTypeInt(1);
        $float    = new AnyValueTypeFloat(1.5);
        $string   = new AnyValueTypeString('ok');
        $bool     = new AnyValueTypeBool(true);
        $dateTime = new AnyValueTypeDateTime('2024-01-01');
        $array    = new AnyValueTypeArray([1, 2]);

        // Then
        $this->assertSame(1, $int->value);
        $this->assertSame(1.5, $float->value);
        $this->assertSame('ok', $string->value);
        $this->assertTrue($bool->value);
        $this->assertSame('2024-01-01', $dateTime->value);
        $this->assertSame([1, 2], $array->values);
    }

    #[Test]
    public function testAnyValueTypeConstraintStillValidatesOnEveryBase()
    {
        // Given / When / Then -- applicable everywhere means it also fires everywhere.
        foreach (
            [
                fn(): object => new AnyValueTypeInt(0),
                fn(): object => new AnyValueTypeFloat(0.0),
                fn(): object => new AnyValueTypeString('forbidden'),
                fn(): object => new AnyValueTypeBool(false),
                fn(): object => new AnyValueTypeArray([]),
            ] as $index => $construct
        ) {
            try {
                $construct();
                $this->fail("Expected StrongTypeException for case {$index}");
            } catch (StrongTypeException $e) {
                $this->assertStringContainsString('must not be the forbidden value', $e->getMessage());
            }
        }
    }
}
