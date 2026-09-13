<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Not;
use StrongType\Constraint\NotPattern;
use StrongType\Exception\ConstraintEvaluationException;
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

#[NotPattern('/x/u')]
readonly class NotPatternUnicodeTestString extends StringType
{
}

#[Not(new NotPattern('/^safe$/u'))]
readonly class NotOfNotPatternUnicodeTestString extends StringType
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

    /**
     * A `/u` pattern against a malformed-UTF-8 subject makes preg_match() return false
     * (a regex engine error), not 0 (no match). `false` is falsy, so a naive
     * `if (preg_match(...))` treats an engine error identically to "no match" and fails
     * open -- the value must be rejected instead.
     */
    #[Test]
    public function testRegexEngineErrorFailsClosedRatherThanOpen(): void
    {
        // Then
        $this->expectException(ConstraintEvaluationException::class);

        // When
        new NotPatternUnicodeTestString("\xB1\x31");
    }

    /**
     * #[Not(new NotPattern('/^safe$/u'))] accepts only values matching '^safe$' exactly
     * (NotPattern rejects a match, so Not inverts that rejection into an accept). Locks in
     * the normal, non-error path before the two tests below exercise the actual regression.
     */
    #[Test]
    public function testNotOfNotPatternAcceptsAValueMatchingTheInnerPattern(): void
    {
        // Given / When / Then
        $this->expectNotToPerformAssertions();
        new NotOfNotPatternUnicodeTestString('safe');
    }

    #[Test]
    public function testNotOfNotPatternRejectsAValueNotMatchingTheInnerPattern(): void
    {
        // Then -- 'unsafe' doesn't match '^safe$', so NotPattern passes and Not inverts
        // that pass into an ordinary rejection.
        $this->expectException(StrongTypeException::class);

        // When
        new NotOfNotPatternUnicodeTestString('unsafe');
    }

    /**
     * Regression test: NotPattern throws ConstraintEvaluationException (rather than
     * returning a normal violation string) specifically so a #[Not(new NotPattern(...))]
     * composition can't invert "couldn't tell whether this matches" into "so it must be
     * fine". Before this fix, malformed UTF-8 under NotPattern's own '/u' pattern returned
     * an ordinary violation string, which Not read as "the inner constraint failed" and
     * inverted into an accept -- silently admitting a value neither side could actually
     * evaluate.
     */
    #[Test]
    public function testRegexEngineErrorUnderNotStillRejectsRatherThanBeingInvertedToAPass(): void
    {
        // Then
        $this->expectException(ConstraintEvaluationException::class);

        // When
        new NotOfNotPatternUnicodeTestString("\xB1\x31");
    }

    #[Test]
    public function testRegexEngineErrorMessageFormat(): void
    {
        // Given
        $caught = null;

        // When
        try {
            new NotPatternUnicodeTestString("\xB1\x31");
        } catch (StrongTypeException $e) {
            $caught = $e;
        }

        // Then
        $this->assertNotNull($caught);
        $this->assertSame(
            "NotPatternUnicodeTestString type must be evaluable against pattern /x/u (regex engine error), got \xB1\x31",
            $caught->getMessage()
        );
    }
}
