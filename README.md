# StrongType PHP

Strongly typed values for PHP -- validate once at construction, trust everywhere after.

StrongType provides a library of ready-to-use typed value objects (integers, floats, strings, arrays, booleans, datetimes) and an **attribute-based constraint composition system** that lets you define new validated types declaratively -- no constructor boilerplate needed.

```php
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, MaxLength, Unique, ElementType};

// Define custom types with stacked attributes -- no constructor needed
#[Min(1), Max(65535)]
readonly class Port extends \StrongType\Int\Integer {}

#[Nonempty, Pattern('/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/')]
readonly class Username extends \StrongType\String\StringType {}

#[Nonempty, Unique, ElementType('int')]
class UniqueIntSet extends \StrongType\Arrays\ArrayType {}

// Use them -- invalid values throw StrongTypeException
$port = new Port(8080);           // OK
$port = new Port(0);              // StrongTypeException: "Port type must be >= 1, got 0"

$user = new Username('alice_99'); // OK
$user = new Username('99bad');    // StrongTypeException
```

## Quick Reference

### Integers

| Type | Constraint | Details |
| --- | --- | --- |
| `PositiveInt` | `> 0` | Strictly positive |
| `NegativeInt` | `< 0` | Strictly negative |
| `NonnegativeInt` | `>= 0` | Zero or positive |
| `NonpositiveInt` | `<= 0` | Zero or negative |
| `NonzeroInt` | `!= 0` | Any nonzero integer |
| `EvenInt` | `% 2 === 0` | Even integers |
| `OddInt` | `% 2 !== 0` | Odd integers |
| `ByteInt` | `0..255` | Unsigned byte range |
| `PercentInt` | `0..100` | Percentage range |
| `PortNumber` | `1..65535` | Valid port number |
| `HttpStatusCode` | `100..599` | Valid HTTP status code range |

### Floats

Float subtypes reject `INF`, `-INF`, and `NAN` by default (via the inherited `#[Finite]` constraint).

| Type | Constraint | Details |
| --- | --- | --- |
| `PositiveFloat` | `> 0` | Strictly positive |
| `NegativeFloat` | `< 0` | Strictly negative |
| `NonnegativeFloat` | `>= 0` | Zero or positive |
| `NonpositiveFloat` | `<= 0` | Zero or negative |
| `NonzeroFloat` | `!= 0` | Any nonzero float |
| `UnitFloat` | `0.0..1.0` | Unit interval |
| `PercentFloat` | `0.0..100.0` | Percentage range |
| `Latitude` | `-90.0..90.0` | Geographic latitude |
| `Longitude` | `-180.0..180.0` | Geographic longitude |

### Strings

| Type | Constraint | Details |
| --- | --- | --- |
| `NonemptyString` | `strlen > 0` | Not empty |
| `NonblankString` | `trim() !== ''` | Not empty or whitespace-only |
| `AlphaString` | `ctype_alpha` | Alphabetic characters only |
| `UppercaseAlphaString` | `ctype_upper` | Uppercase alphabetic only |
| `LowercaseAlphaString` | `ctype_lower` | Lowercase alphabetic only |
| `AlphanumericString` | `ctype_alnum` | Alphanumeric characters only |
| `NumericString` | `ctype_digit` | Numeric digits only |
| `BinaryString` | `[01]+` | Binary digit string |
| `HexString` | `ctype_xdigit` | Hexadecimal digits only |
| `Base64String` | Canonical Base64 (RFC 4648 §4) | Padded, standard alphabet only; Base64URL rejected |
| `JsonString` | `json_validate` | Valid JSON |
| `EmailString` | `FILTER_VALIDATE_EMAIL` | Valid email address |
| `UrlString` | `FILTER_VALIDATE_URL` | Valid URL |
| `IpAddressString` | `FILTER_VALIDATE_IP` | Valid IP address (v4 or v6) |
| `Ipv4AddressString` | IPv4 filter | Valid IPv4 address only |
| `Ipv6AddressString` | IPv6 filter | Valid IPv6 address only |
| `CidrString` | CIDR notation | `ip/prefix` with prefix in valid range |
| `MacAddressString` | MAC pattern | `xx:xx:xx:xx:xx:xx` |
| `UuidString` | UUID pattern | `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` |
| `JwtString` | JWT pattern | `header.payload.signature` base64url segments |
| `PhoneE164String` | E.164 pattern | `+` followed by 2–15 digits |
| `CountryCodeAlpha2String` | `[A-Z]{2}` | ISO 3166-1 alpha-2 format (format only) |
| `CountryCodeAlpha3String` | `[A-Z]{3}` | ISO 3166-1 alpha-3 format (format only) |
| `CurrencyCodeString` | `[A-Z]{3}` | ISO 4217 format (format only) |
| `LanguageCodeString` | `[a-z]{2}` | ISO 639-1 format (format only) |
| `MimeTypeString` | RFC 6838 | `type/subtype` restricted-name format |
| `SlugString` | Slug pattern | `lowercase-words-with-dashes` |
| `SemverString` | SemVer 2.0.0 | `MAJOR.MINOR.PATCH[-prerelease][+build]` per [semver.org](https://semver.org/) spec |
| `HexColorString` | Hex color pattern | `#RGB` or `#RRGGBB` |
| `ClassString` | `class_exists` | Existing class, interface, or enum |
| `DateTimeString` | `DateTimeImmutable` | Parseable date/time string |
| `Rfc3339DateTimeString` | RFC 3339 | RFC 3339 date-time grammar with offset; `:60` accepted syntactically (leap-second tolerance) |
| `EmptyString` | `strlen === 0` | Must be empty (default `''`) |

### Arrays

| Type | Constraint | Details |
| --- | --- | --- |
| `NonemptyArray` | `count > 0` | At least one element |
| `EmptyArray` | `count === 0` | Must be empty |
| `UniqueArray` | No duplicates | Nonempty with unique values |
| `FixedSizeArray` | `count === $size` | Exact element count (runtime) |
| `ListArray` | `array_is_list` | Sequential integer keys starting at 0 |
| `AssociativeArray` | Not a list | Nonempty with at least one non-list key |
| `ArrayOfStrings` | Element type check | Nonempty, all strings |
| `ArrayOfInts` | Element type check | Nonempty, all ints |
| `ArrayOfFloats` | Element type check | Nonempty, all floats |
| `ArrayOfBools` | Element type check | Nonempty, all bools |
| `ArrayOfObjects` | Element type check | Nonempty, all objects |
| `ArrayOfCallables` | Element type check | Nonempty, all callables |
| `ArrayOfResources` | Element type check | Nonempty, all resources |
| `ArrayOfIterables` | Element type check | Nonempty, all iterables |
| `ArrayOfArrays` | Element type check | Nonempty, all arrays |

### Booleans

| Type | Constraint | Details |
| --- | --- | --- |
| `TrueValue` | `true` | PHP `true` literal type |
| `FalseValue` | `false` | PHP `false` literal type |

### DateTime

| Type | Constraint | Details |
| --- | --- | --- |
| `Timestamp` | `>= 0` | Non-negative Unix timestamp |
| `FutureTimestamp` | `> time()` | Timestamp in the future |
| `PastTimestamp` | `< time()` | Timestamp in the past |
| `DateString` | `YYYY-MM-DD` | Valid calendar date |
| `TimeString` | `HH:MM:SS` | Valid 24-hour time |

### Nullable Wrapper

`Nullable` requires a concrete StrongType class — the wrapped value is validated against that type whenever it is non-null, and the type itself is checked even when the value is `null`.

```php
use StrongType\Nullable;
use StrongType\Int\PositiveInt;

$value = new Nullable(PositiveInt::class, 5);    // wraps PositiveInt(5)
$null  = new Nullable(PositiveInt::class, null);  // wraps null

$value->getValue();  // 5
$null->getValue();   // null
$null->isNull();     // true

// Structural equality delegates to the wrapped type's equals().
$value->equals(new Nullable(PositiveInt::class, 5));    // true
$value->equals(new Nullable(PositiveInt::class, null)); // false

// Nullable itself implements HasEquals, so it can flow through generic code
// that accepts any strong type. Cross-comparison with a non-Nullable is always false.
$value->equals(new PositiveInt(5));                     // false (Nullable<PositiveInt> != PositiveInt)
```

### Base Class Methods

Base classes provide these helpers (array-only helpers are prefixed with `ArrayType::`):

| Method | Returns | Description |
| --- | --- | --- |
| `tryFrom(mixed $value)` | `static \| null` | Returns an instance or `null` if the input is the wrong PHP type or fails validation. Does **not** widen across scalar types (strict matching) — except `FloatingPoint::tryFrom` accepts `int` and widens to `float`, mirroring PHP's native int-to-float param coercion. |
| `equals(HasEquals $other)` | `bool` | Strict structural equality: same concrete class and same value (`ArrayType::equals` compares values **and** key-order, since insertion order is part of the array identity). |
| `ArrayType::equalsUnordered(HasEquals $other)` | `bool` | Multiset equality: same concrete subclass and same values (each repeated the same number of times) regardless of keys or insertion order. Strict element comparison; nested arrays are compared as-is. Only defined on `ArrayType`. |
| `nullable(mixed $value)` | `Nullable` | Convenience shortcut for `new Nullable(static::class, $value)`. |
| `ArrayType::withValues(array $values)` | `static` | Returns a new instance of the same concrete subclass with a different value array. Only defined on `ArrayType`. |

```php
use StrongType\Int\PositiveInt;

$a = PositiveInt::tryFrom(5);     // PositiveInt(5)
$b = PositiveInt::tryFrom(-1);    // null (constraint failure)
$c = PositiveInt::tryFrom('5');   // null (wrong type — no coercion)

$a->equals(new PositiveInt(5));   // true
$a->equals(new PositiveInt(6));   // false

PositiveInt::nullable(null);      // Nullable<PositiveInt>(null)
```

```php
use StrongType\Arrays\ListArray;

// equalsUnordered — same multiset of values, any order or keys.
$shipped = new ListArray([101, 204, 309]);
$received = new ListArray([309, 101, 204]);

$shipped->equals($received);          // false — order differs
$shipped->equalsUnordered($received); // true  — same multiset

// Multiplicity matters: duplicates must match.
$a = new ListArray([1, 2, 2, 3]);
$b = new ListArray([1, 1, 2, 3]);
$a->equalsUnordered($b);              // false — different multiset
```

`FixedSizeArray::tryFrom` and `FixedSizeArray::nullable` throw `\LogicException` — the required `$size` parameter cannot be satisfied through these factories. Use `new FixedSizeArray($values, $size)` or `$existing->withValues($values)` instead.

### Implemented Interfaces

Every strong type implements a small, stable set of standard interfaces so it can interoperate with native PHP language features and generic code:

| Interface | Where | What you get |
| --- | --- | --- |
| `\Stringable` | All types | `__toString()` — cast any strong type to `string` (integers/floats/bools use `strval`; strings pass through; arrays and datetimes JSON-encode). |
| `\JsonSerializable` | All types | `jsonSerialize()` — `json_encode($value)` produces the underlying scalar/array. |
| `\StrongType\HasEquals` | All types and `Nullable` | `equals(HasEquals $other): bool` — strict structural equality. Lets generic code compare two strong-type instances without knowing the concrete type. Type-hint against `HasEquals` when you want to accept "any strong type" (including a `Nullable`) in a signature. |
| `\Countable` | `ArrayType` only | `count($arr)` returns the element count. |
| `\IteratorAggregate` | `ArrayType` only | `foreach ($arr as $key => $value) { ... }` iterates the underlying values, preserving original keys (via `\ArrayIterator`). |

```php
use StrongType\Arrays\ArrayOfStrings;

$tags = new ArrayOfStrings(['php', 'types', 'validation']);

\count($tags);              // 3 (Countable)
foreach ($tags as $tag) {   // IteratorAggregate
    echo $tag, "\n";
}

(string) $tags;             // '["php","types","validation"]' (Stringable)
\json_encode($tags);        // '["php","types","validation"]' (JsonSerializable)
```

```php
use StrongType\HasEquals;

// Accept any strong type in a generic signature.
function areEqual(HasEquals $a, HasEquals $b): bool
{
    return $a->equals($b);
}
```

## Setup

```bash
composer require markrogoyski/strongtype-php
```

#### Requirements
- PHP 8.4+
- Extensions: `ctype`, `filter`, `json`

## Usage

### Built-in Types

Every type validates at construction and is immutable. Invalid values throw `StrongTypeException`.

```php
use StrongType\Int\PositiveInt;
use StrongType\String\EmailString;
use StrongType\Arrays\ArrayOfStrings;
use StrongType\Exception\StrongTypeException;

// Construct -- validates automatically
$age   = new PositiveInt(25);
$email = new EmailString('alice@example.com');
$tags  = new ArrayOfStrings(['php', 'types']);

// Access the value
$age->value;         // 25 (public readonly property)
$age->getValue();    // 25 (getter method)

// String and JSON representations
(string) $age;                // "25"
json_encode($email);          // '"alice@example.com"'
json_encode($tags);           // '["php","types"]'

// Invalid values throw immediately
try {
    new PositiveInt(-1);
} catch (StrongTypeException $e) {
    echo $e->getMessage(); // "PositiveInt type must be > 0, got -1"
}
```

### Error Handling

`StrongType\Exception\StrongTypeException` is the umbrella for all validation failures and remains the right type to catch when you want to handle any invalid value uniformly. It has two subclasses for code that wants to distinguish between failure modes:

| Failure mode | Result |
| --- | --- |
| Constructor argument type mismatch (e.g. `new PositiveInt('5')`) | PHP `\TypeError` |
| `tryFrom()` with a type-mismatched argument | returns `null` |
| Constraint attribute fails during validation | `ConstraintViolationException` |
| Manual parse fails in `DateString` / `TimeString` | `FormatException` |

Both `ConstraintViolationException` and `FormatException` extend `StrongTypeException`, so existing `catch (StrongTypeException $e)` blocks continue to catch every validation failure. Catch a subclass when you want to react specifically to a constraint violation or a format-parse failure:

```php
use StrongType\DateTime\DateString;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\FormatException;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\PositiveInt;

try {
    new PositiveInt(-1);
} catch (ConstraintViolationException $e) {
    // Constraint attribute rejected the value.
}

try {
    new DateString('not-a-date');
} catch (FormatException $e) {
    // Hand-rolled parser rejected the string shape.
}

try {
    new PositiveInt(-1);
} catch (StrongTypeException $e) {
    // Catches both subclasses.
}
```

### Type Hints in Your Code

StrongTypes shine as parameter and return types:

```php
use StrongType\Int\NonnegativeInt;
use StrongType\String\NonemptyString;
use StrongType\String\EmailString;
use StrongType\Arrays\ArrayOfStrings;

class User
{
    public function __construct(
        public readonly NonemptyString $name,
        public readonly NonnegativeInt $age,
        public readonly EmailString    $email,
        public readonly ArrayOfStrings $roles,
    ) {}
}

// Validation happens at construction -- no manual checks needed
$user = new User(
    new NonemptyString('Alice'),
    new NonnegativeInt(30),
    new EmailString('alice@example.com'),
    new ArrayOfStrings(['admin', 'editor']),
);
```

## Constraint Composition

The attribute-based constraint system lets you define new types declaratively by stacking constraint attributes on a class. No constructor needed -- constraints compose automatically.

### Defining Custom Types

Extend any base type and add constraint attributes:

```php
use StrongType\Int\Integer;
use StrongType\Float\FloatingPoint;
use StrongType\String\StringType;
use StrongType\Arrays\ArrayType;
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, MaxLength, Unique, ElementType, Positive};

// Numeric ranges
#[Min(1), Max(65535)]
readonly class Port extends Integer {}

#[Min(100), Max(999)]
readonly class ThreeDigitCode extends Integer {}

#[Positive]
readonly class Price extends FloatingPoint {}

#[Min(0.0), Max(1.0)]
readonly class Probability extends FloatingPoint {}

// String formats
#[Nonempty, Pattern('/^[a-z0-9]+(-[a-z0-9]+)*$/'), MaxLength(255)]
readonly class Slug extends StringType {}

#[Nonempty, Pattern('/^[A-Z]{2}$/')]
readonly class CountryCode extends StringType {}

#[Nonempty, Pattern('/^[A-Z0-9]{6,10}$/')]
readonly class TrackingNumber extends StringType {}

// Array shapes
#[Nonempty, Unique, ElementType('string')]
class TagSet extends ArrayType {}

#[Nonempty, ElementType('int'), Unique]
class UniqueIdList extends ArrayType {}
```

### Available Constraint Attributes

#### Numeric Constraints (for Integer and FloatingPoint)

| Attribute | Parameters | Description |
| --- | --- | --- |
| `Min` | `int\|float $min, bool $exclusive = false` | `>= $min` (or `> $min` if exclusive) |
| `Max` | `int\|float $max, bool $exclusive = false` | `<= $max` (or `< $max` if exclusive) |
| `InRange` | `int\|float $min, int\|float $max, bool $exclusiveMin = false, bool $exclusiveMax = false` | Combined min + max with optional open bounds |
| `Positive` | none | `> 0` |
| `Negative` | none | `< 0` |
| `Nonnegative` | none | `>= 0` |
| `Nonpositive` | none | `<= 0` |
| `Nonzero` | none | `!= 0` |
| `Even` | none | `% 2 === 0` |
| `Odd` | none | `% 2 !== 0` |
| `DivisibleBy` | `int $divisor` | `% $divisor === 0` |
| `Finite` | none | Rejects `INF`, `-INF`, `NAN` (already applied by default to all FloatingPoint subtypes) |
| `InList` | `mixed ...$allowed` | Strict membership check against an allowlist |

#### String Constraints (for StringType)

| Attribute | Parameters | Description |
| --- | --- | --- |
| `Nonempty` | none | `strlen > 0` (also works on arrays) |
| `Nonblank` | none | `trim() !== ''` |
| `MinLength` | `int $minLength` | `strlen >= $minLength` |
| `MaxLength` | `int $maxLength` | `strlen <= $maxLength` |
| `Pattern` | `string $pattern` | `preg_match($pattern, $value)` (repeatable) |
| `NotPattern` | `string $pattern` | Value must **not** match pattern (repeatable) |
| `Alpha` | none | `ctype_alpha` |
| `Alphanumeric` | none | `ctype_alnum` |
| `NumericDigits` | none | `ctype_digit` |
| `HexDigits` | none | `ctype_xdigit` |
| `Lowercase` | none | `ctype_lower` |
| `Uppercase` | none | `ctype_upper` |
| `Base64` | none | Canonical standard Base64 (RFC 4648 §4, padded); Base64URL alphabet (`-`, `_`) is rejected |
| `Email` | none | `filter_var(FILTER_VALIDATE_EMAIL)` |
| `Url` | none | `filter_var(FILTER_VALIDATE_URL)` |
| `IpAddress` | none | `filter_var(FILTER_VALIDATE_IP)` |
| `Ipv4` | none | `filter_var(FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)` |
| `Ipv6` | none | `filter_var(FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)` |
| `Cidr` | none | Valid IPv4 or IPv6 CIDR (`ip/prefix`) |
| `Uuid` | none | UUID pattern `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` |
| `Json` | none | `json_validate()` |
| `Rfc3339` | none | RFC 3339 date-time grammar + calendar round-trip; `:60` accepted syntactically (not verified against IERS leap-second schedule) |
| `ClassExists` | none | `class_exists \|\| interface_exists \|\| enum_exists` |
| `DateTimeParseable` | none | `new DateTimeImmutable()` succeeds |

#### Array Constraints (for ArrayType)

| Attribute | Parameters | Description |
| --- | --- | --- |
| `Nonempty` | none | `count > 0` (shared with strings) |
| `IsEmpty` | none | `count === 0` |
| `MinCount` | `int $minCount` | `count >= $minCount` |
| `MaxCount` | `int $maxCount` | `count <= $maxCount` |
| `ExactCount` | `int $count` | `count === $count` |
| `Unique` | none | No duplicate values |
| `ElementType` | `string $type` | Each element matches type |
| `IsList` | none | `array_is_list` — sequential integer keys starting at 0 |
| `IsAssociative` | none | `!array_is_list` — rejects sequential-integer-keyed arrays (including `[]`, since `array_is_list([])` is `true`) |

The `ElementType` attribute accepts: `'string'`, `'int'`, `'float'`, `'bool'`, `'array'`, `'object'`, `'callable'`, `'resource'`, `'iterable'`, or any class/interface name for `instanceof` checks.

#### DateTime Constraints

| Attribute | Parameters | Description |
| --- | --- | --- |
| `DateFormat` | `string $format` | Validates against date format with roundtrip check |
| `TimeFormat` | none | `HH:MM:SS` pattern |
| `InFuture` | none | `> time()` (for integer timestamps) |
| `InPast` | none | `< time()` (for integer timestamps) |

### Constraint Inheritance

Constraints are inherited through the class hierarchy. Child classes get all parent constraints plus their own:

```php
use StrongType\Constraint\{Min, Max, Even};
use StrongType\Int\Integer;

#[Min(1), Max(1000)]
readonly class BoundedInt extends Integer {}

// Inherits Min(1) and Max(1000) from parent, adds Even
#[Even]
readonly class EvenBoundedInt extends BoundedInt {}

new EvenBoundedInt(42);   // OK -- even and in range
new EvenBoundedInt(43);   // StrongTypeException -- not even
new EvenBoundedInt(1001); // StrongTypeException -- exceeds max
```

This also works with the built-in types, since they use constraints themselves:

```php
use StrongType\Constraint\Max;
use StrongType\Int\PositiveInt;

// PositiveInt already has #[Positive], so this inherits > 0
#[Max(100)]
readonly class SmallPositiveInt extends PositiveInt {}

new SmallPositiveInt(50);  // OK
new SmallPositiveInt(0);   // StrongTypeException -- not positive (from parent)
new SmallPositiveInt(101); // StrongTypeException -- exceeds max (from own)
```

### Priority Ordering

Constraints execute in priority order (lower runs first). This ensures range checks happen before format checks, and format checks before semantic checks:

| Priority | Category | Attributes |
| --- | --- | --- |
| 50 | Range / size | Min, Max, Positive, Negative, Nonempty, MinLength, MaxLength, MinCount, MaxCount, etc. |
| 60 | Content | Nonblank |
| 100 | Format | Pattern, Alpha, Alphanumeric, Lowercase, Uppercase, Unique, ElementType, DateFormat, etc. |
| 150 | Semantic | Email, Url, IpAddress, Json, ClassExists, DateTimeParseable |

### Custom Constraints

Create your own constraint by implementing `ConstraintInterface`. The interface has just two methods: `validate()` returns `null` on success or an error message string on failure, and `priority()` controls execution order.

#### A minimal example: `Palindrome`

```php
use StrongType\Constraint\ConstraintInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Palindrome implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = substr($className, strrpos($className, '\\') + 1);

        if (\is_string($value) && $value !== strrev($value)) {
            return "{$short} type must be a palindrome, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 200; // Custom constraints typically use 200+
    }
}
```

Compose it with built-in constraints:

```php
use StrongType\Constraint\Nonempty;
use StrongType\String\StringType;

#[Nonempty, Palindrome]
readonly class PalindromeString extends StringType {}

new PalindromeString('racecar'); // OK
new PalindromeString('hello');   // StrongTypeException -- not a palindrome
new PalindromeString('');        // StrongTypeException -- empty (Nonempty runs first)
```

#### A real-world example: `Luhn` checksum

```php
use StrongType\Constraint\ConstraintInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Luhn implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $digits = str_split(strrev($value));
        $sum = 0;
        foreach ($digits as $i => $digit) {
            $n = (int) $digit;
            if ($i % 2 === 1) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
        }

        if ($sum % 10 !== 0) {
            $short = substr($className, strrpos($className, '\\') + 1);
            return "{$short} type must pass Luhn checksum, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 200; // Runs after format checks
    }
}
```

Use it just like built-in constraints:

```php
use StrongType\Constraint\{Nonempty, Pattern};
use StrongType\String\StringType;

#[Nonempty, Pattern('/^\d{13,19}$/'), Luhn]
readonly class CreditCardNumber extends StringType {}

new CreditCardNumber('4532015112830366'); // OK
new CreditCardNumber('1234567890123456'); // StrongTypeException -- fails Luhn
```

No registration step -- the validator automatically discovers any attribute implementing `ConstraintInterface`.

### Constraint Constructor Invariants

Built-in constraints fail fast with `\LogicException` when configured impossibly — these are programmer errors, not validation failures, and surface during attribute instantiation (the first time a typed value of the affected class is constructed), not at PHP class-load time.

| Constraint                        | Invariant                                       |
| --------------------------------- | ----------------------------------------------- |
| `DivisibleBy`                     | divisor must not be zero                        |
| `MinLength`, `MaxLength`          | length must be `>= 0`                           |
| `MinCount`, `MaxCount`, `ExactCount` | count must be `>= 0`                         |
| `Pattern`, `NotPattern`           | regex must compile                              |
| `ElementType`                     | type must be a builtin (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) or an existing class/interface name |
| `InRange`                         | `min <= max`; if equal, no exclusive flag       |
| `InList`                          | at least one allowed value                      |
| `DateFormat`                      | format string must be non-empty                 |

```php
new MinLength(-1);            // \LogicException
new DivisibleBy(0);           // \LogicException
new Pattern('not-a-regex');   // \LogicException
new ElementType('integer');   // \LogicException -- 'integer' is a PHP type alias, not a builtin name
new InRange(100, 1);          // \LogicException
```

## Examples

### Domain Modeling

```php
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, MaxCount, Positive, Nonnegative, ElementType, Unique};
use StrongType\Int\Integer;
use StrongType\Float\FloatingPoint;
use StrongType\String\StringType;
use StrongType\Arrays\ArrayType;

// E-commerce
#[Positive]
readonly class Quantity extends Integer {}

#[Min(0), Max(99, exclusive: true)]
readonly class DiscountPercent extends Integer {}

#[Nonnegative]
readonly class Money extends FloatingPoint {}

#[Nonempty, Pattern('/^[A-Z]{3}$/')]
readonly class CurrencyCode extends StringType {}

#[Nonempty, Pattern('/^SKU-[A-Z0-9]{8}$/')]
readonly class Sku extends StringType {}

// Networking
#[Min(1), Max(65535)]
readonly class Port extends Integer {}

#[Nonempty, Pattern('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/')]
readonly class Hostname extends StringType {}

// Configuration
#[Nonempty, Unique, ElementType('string'), MaxCount(50)]
class FeatureFlags extends ArrayType {}
```

### Extending Built-in Types

```php
use StrongType\Constraint\{Max, DivisibleBy};
use StrongType\Int\PositiveInt;
use StrongType\String\NonemptyString;
use StrongType\Constraint\{MaxLength, Pattern};

// Build on existing validated types
#[Max(120)]
readonly class HumanAge extends PositiveInt {}

#[DivisibleBy(100)]
readonly class CentAmount extends PositiveInt {}

#[MaxLength(50), Pattern('/^\S.*\S$/')]
readonly class DisplayName extends NonemptyString {}
```

## How It Works

The constraint system uses PHP 8 attributes and reflection:

1. **At first instantiation** of a type, `ConstraintValidator` reads all `ConstraintInterface` attributes from the class and its parents via reflection, sorts them by priority, and caches the result.
2. **On every instantiation**, it iterates the cached constraint list and calls `validate()` on each. The first failure throws `ConstraintViolationException` (a `StrongTypeException` subclass).
3. **Subsequent instantiations** skip reflection entirely -- it's a hash lookup plus iterating a small array.

Existing types with manual constructors continue to work unchanged. The validator is a no-op for classes with no constraint attributes.

## Standards

StrongType PHP conforms to the following standards:

- PSR-1 - Basic coding standard
- PSR-4 - Autoloader
- PSR-12 - Extended coding style guide

## License

StrongType PHP is licensed under the MIT License.
