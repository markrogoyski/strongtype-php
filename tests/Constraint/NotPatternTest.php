<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\NotPattern;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[NotPattern('/\d/')]
readonly class NotPatternNoDigitsTestString extends StringType
{
}

#[NotPattern('/[A-Z]/')]
#[NotPattern('/\s/')]
readonly class NotPatternMultipleTestString extends StringType
{
}

class NotPatternTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidNoDigits')]
    public function testValidNoDigits(string $value): void
    {
        $str = new NotPatternNoDigitsTestString($value);
        $this->assertSame($value, $str->value);
    }

    public static function dataProviderForValidNoDigits(): array
    {
        return [['abc'], ['hello'], ['no-digits-here']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidNoDigits')]
    public function testInvalidNoDigits(string $value): void
    {
        $this->expectException(StrongTypeException::class);
        new NotPatternNoDigitsTestString($value);
    }

    public static function dataProviderForInvalidNoDigits(): array
    {
        return [['abc1'], ['123'], ['h3llo']];
    }

    #[Test]
    #[DataProvider('dataProviderForValidMultiplePatterns')]
    public function testValidMultiplePatterns(string $value): void
    {
        $str = new NotPatternMultipleTestString($value);
        $this->assertSame($value, $str->value);
    }

    public static function dataProviderForValidMultiplePatterns(): array
    {
        return [['abc'], ['hello'], ['lowercase-only']];
    }

    #[Test]
    public function testMultiplePatternsRejectsUppercase(): void
    {
        $this->expectException(StrongTypeException::class);
        new NotPatternMultipleTestString('Hello');
    }

    #[Test]
    public function testMultiplePatternsRejectsWhitespace(): void
    {
        $this->expectException(StrongTypeException::class);
        new NotPatternMultipleTestString('hello world');
    }

    #[Test]
    public function testErrorMessageFormat(): void
    {
        try {
            new NotPatternNoDigitsTestString('abc1');
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('NotPatternNoDigitsTestString type must not match pattern /\d/, got abc1', $e->getMessage());
        }
    }
}
