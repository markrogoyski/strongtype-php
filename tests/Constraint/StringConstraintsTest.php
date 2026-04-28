<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Alpha;
use StrongType\Constraint\Alphanumeric;
use StrongType\Constraint\Base64;
use StrongType\Constraint\ClassExists;
use StrongType\Constraint\DateTimeParseable;
use StrongType\Constraint\Email;
use StrongType\Constraint\HexDigits;
use StrongType\Constraint\IpAddress;
use StrongType\Constraint\Json;
use StrongType\Constraint\Lowercase;
use StrongType\Constraint\MaxLength;
use StrongType\Constraint\MinLength;
use StrongType\Constraint\Nonblank;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\NumericDigits;
use StrongType\Constraint\Pattern;
use StrongType\Constraint\Uppercase;
use StrongType\Constraint\Url;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Nonempty] readonly class NonemptyTestStr extends StringType
{
}
#[Nonempty, Nonblank] readonly class NonblankTestStr extends StringType
{
}
#[MinLength(3)] readonly class MinLenTestStr extends StringType
{
}
#[MaxLength(5)] readonly class MaxLenTestStr extends StringType
{
}
#[Pattern('/^[a-z]+$/')] readonly class PatternTestStr extends StringType
{
}
#[Nonempty, Alpha] readonly class AlphaTestStr extends StringType
{
}
#[Nonempty, Alphanumeric] readonly class AlnumTestStr extends StringType
{
}
#[Nonempty, NumericDigits] readonly class DigitsTestStr extends StringType
{
}
#[Nonempty, HexDigits] readonly class HexDigitsTestStr extends StringType
{
}
#[Nonempty, Lowercase] readonly class LowerTestStr extends StringType
{
}
#[Nonempty, Uppercase] readonly class UpperTestStr extends StringType
{
}
#[Nonempty, Base64] readonly class Base64TestStr extends StringType
{
}
#[Nonempty, Email] readonly class EmailTestStr extends StringType
{
}
#[Nonempty, Url] readonly class UrlTestStr extends StringType
{
}
#[Nonempty, IpAddress] readonly class IpTestStr extends StringType
{
}
#[Nonempty, Json] readonly class JsonTestStr extends StringType
{
}
#[Nonempty, ClassExists] readonly class ClassExistsTestStr extends StringType
{
}
#[Nonempty, DateTimeParseable] readonly class DateTimeParseTestStr extends StringType
{
}

class StringConstraintsTest extends \PHPUnit\Framework\TestCase
{
    // Nonempty
    #[Test]
    public function testNonemptyAccepts()
    {
        $this->assertSame('hello', (new NonemptyTestStr('hello'))->value);
    }

    #[Test]
    public function testNonemptyRejects()
    {
        $this->expectException(StrongTypeException::class);
        new NonemptyTestStr('');
    }

    // Nonblank
    #[Test]
    public function testNonblankAccepts()
    {
        $this->assertSame('hello', (new NonblankTestStr('hello'))->value);
    }

    #[Test]
    public function testNonblankRejectsWhitespace()
    {
        $this->expectException(StrongTypeException::class);
        new NonblankTestStr('   ');
    }

    #[Test]
    public function testNonblankRejectsEmpty()
    {
        $this->expectException(StrongTypeException::class);
        new NonblankTestStr('');
    }

    // MinLength
    #[Test]
    public function testMinLengthAccepts()
    {
        $this->assertSame('abc', (new MinLenTestStr('abc'))->value);
        $this->assertSame('abcd', (new MinLenTestStr('abcd'))->value);
    }

    #[Test]
    public function testMinLengthRejects()
    {
        $this->expectException(StrongTypeException::class);
        new MinLenTestStr('ab');
    }

    // MaxLength
    #[Test]
    public function testMaxLengthAccepts()
    {
        $this->assertSame('abcde', (new MaxLenTestStr('abcde'))->value);
        $this->assertSame('', (new MaxLenTestStr(''))->value);
    }

    #[Test]
    public function testMaxLengthRejects()
    {
        $this->expectException(StrongTypeException::class);
        new MaxLenTestStr('abcdef');
    }

    // MinLength / MaxLength count bytes, not Unicode characters.
    // 'é' is one character but two bytes in UTF-8.

    #[Test]
    public function testMinLengthCountsBytesNotCharacters()
    {
        // Given: 'éé' is two characters but four bytes — passes MinLength(3).
        // When
        $obj = new MinLenTestStr('éé');

        // Then
        $this->assertSame('éé', $obj->value);
    }

    #[Test]
    public function testMinLengthRejectsByByteCount()
    {
        // Given: 'é' is one character but two bytes — fails MinLength(3).
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new MinLenTestStr('é');
    }

    #[Test]
    public function testMaxLengthRejectsByByteCount()
    {
        // Given: 'ééé' is three characters but six bytes — fails MaxLength(5).
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new MaxLenTestStr('ééé');
    }

    // Pattern
    #[Test]
    public function testPatternAccepts()
    {
        $this->assertSame('hello', (new PatternTestStr('hello'))->value);
    }

    #[Test]
    public function testPatternRejects()
    {
        $this->expectException(StrongTypeException::class);
        new PatternTestStr('Hello');
    }

    #[Test]
    public function testPatternAllowsEmptyWhenNoNonempty()
    {
        // Pattern alone doesn't enforce nonempty - empty string doesn't match but that's the regex
        $this->expectException(StrongTypeException::class);
        new PatternTestStr('');
    }

    // Alpha
    #[Test]
    #[DataProvider('dataProviderForValidAlpha')]
    public function testAlphaAccepts(string $value)
    {
        $this->assertSame($value, (new AlphaTestStr($value))->value);
    }

    public static function dataProviderForValidAlpha(): array
    {
        return [['hello'], ['WORLD'], ['Test']];
    }

    #[Test]
    public function testAlphaRejectsDigits()
    {
        $this->expectException(StrongTypeException::class);
        new AlphaTestStr('hello123');
    }

    // Alphanumeric
    #[Test]
    public function testAlphanumericAccepts()
    {
        $this->assertSame('hello123', (new AlnumTestStr('hello123'))->value);
    }

    #[Test]
    public function testAlphanumericRejectsSpecial()
    {
        $this->expectException(StrongTypeException::class);
        new AlnumTestStr('hello!');
    }

    // NumericDigits
    #[Test]
    public function testNumericDigitsAccepts()
    {
        $this->assertSame('12345', (new DigitsTestStr('12345'))->value);
    }

    #[Test]
    public function testNumericDigitsRejectsAlpha()
    {
        $this->expectException(StrongTypeException::class);
        new DigitsTestStr('123a');
    }

    // HexDigits
    #[Test]
    public function testHexDigitsAccepts()
    {
        $this->assertSame('1a2b3c', (new HexDigitsTestStr('1a2b3c'))->value);
    }

    #[Test]
    public function testHexDigitsRejects()
    {
        $this->expectException(StrongTypeException::class);
        new HexDigitsTestStr('xyz');
    }

    // Lowercase
    #[Test]
    public function testLowercaseAccepts()
    {
        $this->assertSame('hello', (new LowerTestStr('hello'))->value);
    }

    #[Test]
    public function testLowercaseRejects()
    {
        $this->expectException(StrongTypeException::class);
        new LowerTestStr('Hello');
    }

    // Uppercase
    #[Test]
    public function testUppercaseAccepts()
    {
        $this->assertSame('HELLO', (new UpperTestStr('HELLO'))->value);
    }

    #[Test]
    public function testUppercaseRejects()
    {
        $this->expectException(StrongTypeException::class);
        new UpperTestStr('Hello');
    }

    // Base64
    #[Test]
    #[DataProvider('dataProviderForValidBase64')]
    public function testBase64Accepts(string $value)
    {
        $this->assertSame($value, (new Base64TestStr($value))->value);
    }

    public static function dataProviderForValidBase64(): array
    {
        return [['SGVsbG8='], ['dGVzdA=='], ['YQ=='], ['YWJj']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidBase64')]
    public function testBase64Rejects(string $value)
    {
        $this->expectException(StrongTypeException::class);
        new Base64TestStr($value);
    }

    public static function dataProviderForInvalidBase64(): array
    {
        return [
            ['not valid base64!!!'],
            ['abc'],
            ['YWJjZA'],
            ['Pz8-Pz8_'],
            ['YQ== '],
        ];
    }

    // Email
    #[Test]
    #[DataProvider('dataProviderForValidEmail')]
    public function testEmailAccepts(string $value)
    {
        $this->assertSame($value, (new EmailTestStr($value))->value);
    }

    public static function dataProviderForValidEmail(): array
    {
        return [['test@example.com'], ['user.name@domain.org'], ['user+tag@example.co.uk']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidEmail')]
    public function testEmailRejects(string $value)
    {
        $this->expectException(StrongTypeException::class);
        new EmailTestStr($value);
    }

    public static function dataProviderForInvalidEmail(): array
    {
        return [['not-an-email'], ['@domain.com'], ['user@']];
    }

    // Url
    #[Test]
    public function testUrlAccepts()
    {
        $this->assertSame('https://example.com', (new UrlTestStr('https://example.com'))->value);
    }

    #[Test]
    public function testUrlRejects()
    {
        $this->expectException(StrongTypeException::class);
        new UrlTestStr('not a url');
    }

    // IpAddress
    #[Test]
    #[DataProvider('dataProviderForValidIp')]
    public function testIpAccepts(string $value)
    {
        $this->assertSame($value, (new IpTestStr($value))->value);
    }

    public static function dataProviderForValidIp(): array
    {
        return [['192.168.1.1'], ['127.0.0.1'], ['::1'], ['2001:db8::1']];
    }

    #[Test]
    public function testIpRejects()
    {
        $this->expectException(StrongTypeException::class);
        new IpTestStr('999.999.999.999');
    }

    // Json
    #[Test]
    #[DataProvider('dataProviderForValidJson')]
    public function testJsonAccepts(string $value)
    {
        $this->assertSame($value, (new JsonTestStr($value))->value);
    }

    public static function dataProviderForValidJson(): array
    {
        return [['{}'], ['[]'], ['{"key":"value"}'], ['"hello"'], ['42'], ['true']];
    }

    #[Test]
    public function testJsonRejects()
    {
        $this->expectException(StrongTypeException::class);
        new JsonTestStr('{invalid}');
    }

    // ClassExists
    #[Test]
    public function testClassExistsAccepts()
    {
        $this->assertSame(\stdClass::class, (new ClassExistsTestStr(\stdClass::class))->value);
    }

    #[Test]
    public function testClassExistsAcceptsInterface()
    {
        $this->assertSame(\Countable::class, (new ClassExistsTestStr(\Countable::class))->value);
    }

    #[Test]
    public function testClassExistsRejects()
    {
        $this->expectException(StrongTypeException::class);
        new ClassExistsTestStr('NonExistent\\ClassName');
    }

    // DateTimeParseable
    #[Test]
    #[DataProvider('dataProviderForValidDateTime')]
    public function testDateTimeParseableAccepts(string $value)
    {
        $this->assertSame($value, (new DateTimeParseTestStr($value))->value);
    }

    public static function dataProviderForValidDateTime(): array
    {
        return [['2024-01-01'], ['2024-01-01 12:00:00'], ['now'], ['next Monday']];
    }

    #[Test]
    public function testDateTimeParseableRejects()
    {
        $this->expectException(StrongTypeException::class);
        new DateTimeParseTestStr('not a date');
    }
}
