<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Arrays\ArrayType;
use StrongType\Constraint\DateFormat;
use StrongType\Constraint\DivisibleBy;
use StrongType\Constraint\ElementType;
use StrongType\Constraint\ExactCount;
use StrongType\Constraint\MaxCount;
use StrongType\Constraint\MaxLength;
use StrongType\Constraint\MinCount;
use StrongType\Constraint\MinLength;
use StrongType\Constraint\NotPattern;
use StrongType\Constraint\Pattern;
use StrongType\Exception\ConstraintConfigurationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[ElementType('integer')]
class ElementTypeInvalidAliasTestArr extends ArrayType
{
}

#[Pattern('not-a-valid-regex')]
readonly class PatternInvalidRegexTestStr extends \StrongType\String\StringType
{
}

#[NotPattern('not-a-valid-regex')]
readonly class NotPatternInvalidRegexTestStr extends \StrongType\String\StringType
{
}

class ConstructorInvariantsTest extends \PHPUnit\Framework\TestCase
{
    // DivisibleBy: divisor != 0

    #[Test]
    public function testDivisibleByZeroDivisorThrowsConstraintConfigurationException(): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new DivisibleBy(0);
    }

    // MinLength / MaxLength: length >= 0

    #[Test]
    #[DataProvider('dataProviderForNegativeInts')]
    public function testMinLengthNegativeThrowsConstraintConfigurationException(int $length): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new MinLength($length);
    }

    #[Test]
    #[DataProvider('dataProviderForNegativeInts')]
    public function testMaxLengthNegativeThrowsConstraintConfigurationException(int $length): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new MaxLength($length);
    }

    public static function dataProviderForNegativeInts(): array
    {
        return [[-1], [-2], [\PHP_INT_MIN]];
    }

    #[Test]
    public function testMinLengthZeroIsValid(): void
    {
        // When
        $constraint = new MinLength(0);

        // Then
        $this->assertNull($constraint->validate('', 'X'));
    }

    #[Test]
    public function testMaxLengthZeroIsValid(): void
    {
        // When
        $constraint = new MaxLength(0);

        // Then
        $this->assertNull($constraint->validate('', 'X'));
    }

    // MinCount / MaxCount / ExactCount: count >= 0

    #[Test]
    #[DataProvider('dataProviderForNegativeInts')]
    public function testMinCountNegativeThrowsConstraintConfigurationException(int $count): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new MinCount($count);
    }

    #[Test]
    #[DataProvider('dataProviderForNegativeInts')]
    public function testMaxCountNegativeThrowsConstraintConfigurationException(int $count): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new MaxCount($count);
    }

    #[Test]
    #[DataProvider('dataProviderForNegativeInts')]
    public function testExactCountNegativeThrowsConstraintConfigurationException(int $count): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new ExactCount($count);
    }

    #[Test]
    public function testMinCountZeroIsValid(): void
    {
        // When
        $constraint = new MinCount(0);

        // Then
        $this->assertNull($constraint->validate([], 'X'));
    }

    #[Test]
    public function testMaxCountZeroIsValid(): void
    {
        // When
        $constraint = new MaxCount(0);

        // Then
        $this->assertNull($constraint->validate([], 'X'));
    }

    #[Test]
    public function testExactCountZeroIsValid(): void
    {
        // When
        $constraint = new ExactCount(0);

        // Then
        $this->assertNull($constraint->validate([], 'X'));
    }

    // Pattern / NotPattern: regex compiles

    #[Test]
    #[DataProvider('dataProviderForInvalidRegex')]
    public function testPatternInvalidRegexThrowsConstraintConfigurationException(string $pattern): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new Pattern($pattern);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidRegex')]
    public function testNotPatternInvalidRegexThrowsConstraintConfigurationException(string $pattern): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new NotPattern($pattern);
    }

    public static function dataProviderForInvalidRegex(): array
    {
        return [
            ['no-delimiters'],
            ['/unterminated'],
            ['/(unbalanced/'],
            ['/[unclosed-class/'],
            ['/foo/Z'], // unknown modifier
        ];
    }

    #[Test]
    public function testPatternInvalidRegexSurfacesAtAttributeInstantiation(): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new PatternInvalidRegexTestStr('whatever');
    }

    #[Test]
    public function testNotPatternInvalidRegexSurfacesAtAttributeInstantiation(): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new NotPatternInvalidRegexTestStr('whatever');
    }

    // ElementType: type is builtin or class/interface

    #[Test]
    #[DataProvider('dataProviderForAcceptedElementTypeBuiltins')]
    public function testElementTypeAcceptsBuiltin(string $type): void
    {
        // When
        $constraint = new ElementType($type);

        // Then
        $this->assertNull($constraint->validate([], 'X'));
    }

    public static function dataProviderForAcceptedElementTypeBuiltins(): array
    {
        return [
            ['string'],
            ['int'],
            ['float'],
            ['bool'],
            ['array'],
            ['object'],
            ['callable'],
            ['resource'],
            ['iterable'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForAcceptedElementTypeClassRefs')]
    public function testElementTypeAcceptsClassOrInterface(string $type): void
    {
        // When
        $constraint = new ElementType($type);

        // Then
        $this->assertNull($constraint->validate([], 'X'));
    }

    public static function dataProviderForAcceptedElementTypeClassRefs(): array
    {
        return [
            [\DateTimeInterface::class],
            [\DateTimeImmutable::class],
            [\Stringable::class],
            [\Countable::class],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForRejectedElementTypeStrings')]
    public function testElementTypeRejectsInvalidType(string $type): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new ElementType($type);
    }

    public static function dataProviderForRejectedElementTypeStrings(): array
    {
        return [
            ['integer'],   // alias
            ['boolean'],   // alias
            ['double'],    // alias
            ['mixed'],     // not allowed
            ['null'],      // not allowed
            ['void'],      // not a value type
            ['number'],    // not a PHP type
            [''],          // empty
            ['NotAClass\\AnywhereInTheWorld'], // non-existent class
        ];
    }

    #[Test]
    public function testElementTypeInvalidSurfacesAtAttributeInstantiation(): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new ElementTypeInvalidAliasTestArr([]);
    }

    // DateFormat: non-empty format

    #[Test]
    public function testDateFormatEmptyStringThrowsConstraintConfigurationException(): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new DateFormat('');
    }

    // DateFormat: unescaped reset directives '!' and '|' cannot round-trip

    #[Test]
    #[DataProvider('dataProviderForDateFormatResetDirectives')]
    public function testDateFormatResetDirectiveThrowsConstraintConfigurationException(string $format): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new DateFormat($format);
    }

    public static function dataProviderForDateFormatResetDirectives(): array
    {
        return [
            'leading reset'  => ['!Y-m-d'],
            'embedded reset' => ['Y-m-d!'],
            'pipe'           => ['Y-m-d|'],
            'pipe only'      => ['|'],
            'bang only'      => ['!'],
            // A backslash-escaped directive is still a reset directive if the
            // backslash itself is escaped first (\\! is a literal backslash
            // followed by a bare, unescaped '!').
            'escaped backslash then bang' => ['\\\\!'],
        ];
    }

    // DateFormat: an escaped '!' or '|' is a literal character, not a reset
    // directive, and is accepted — it round-trips through format() unchanged.

    #[Test]
    #[DataProvider('dataProviderForDateFormatEscapedLiterals')]
    public function testDateFormatEscapedLiteralDoesNotThrow(string $format): void
    {
        // When
        new DateFormat($format);

        // Then
        $this->expectNotToPerformAssertions();
    }

    public static function dataProviderForDateFormatEscapedLiterals(): array
    {
        return [
            'escaped bang' => ['Y\\!m'],
            'escaped pipe' => ['Y\\|m'],
        ];
    }
}
