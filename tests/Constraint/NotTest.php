<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Not;
use StrongType\Constraint\Pattern;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Not(new Pattern('/^[A-Z]+$/'))]
readonly class NotTestNoAllCaps extends StringType
{
}

class NotTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value): void
    {
        // Given / When
        $obj = new NotTestNoAllCaps($value);

        // Then
        $this->assertSame($value, $obj->value);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [''],           // empty doesn't match /^[A-Z]+$/
            ['hello'],      // lowercase
            ['Hello'],      // mixed
            ['ABC123'],     // contains digits, doesn't match
            ['A B'],        // contains space
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new NotTestNoAllCaps($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            ['A'],      // single uppercase
            ['ABC'],    // multi uppercase
            ['HELLO'],  // all caps word
        ];
    }

    #[Test]
    public function testErrorMessageMentionsChildClassButNotPatternBody(): void
    {
        try {
            // When
            new NotTestNoAllCaps('HELLO');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            // Then
            $message = $e->getMessage();
            $this->assertStringContainsString('NotTestNoAllCaps', $message);
            $this->assertStringContainsString('NOT', $message);
            $this->assertStringContainsString('Pattern', $message);
            // Rich inverted messages require a future description() addition to
            // ConstraintInterface; intentionally not in 1.0.
            $this->assertStringNotContainsString('/^[A-Z]+$/', $message);
        }
    }

    #[Test]
    public function testThrowsConstraintViolationException(): void
    {
        // Then
        $this->expectException(ConstraintViolationException::class);

        // When
        new NotTestNoAllCaps('ABC');
    }

    #[Test]
    public function testPriorityEqualsChildPriority(): void
    {
        // Given — Pattern priority is 100
        $not = new Not(new Pattern('/^a/'));

        // When
        $priority = $not->priority();

        // Then
        $this->assertSame(100, $priority);
    }
}
