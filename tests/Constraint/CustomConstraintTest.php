<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\ConstraintInterface;
use StrongType\Constraint\Nonempty;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\Test;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Palindrome implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && $value !== \strrev($value)) {
            return "{$short} type must be a palindrome, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 200;
    }
}

#[Nonempty, Palindrome]
readonly class PalindromeString extends StringType {}

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
}
