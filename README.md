# StrongType PHP

Strongly typed values for PHP -- validate once at construction, trust everywhere after.

StrongType provides a library of ready-to-use typed value objects (integers, floats, strings, arrays, booleans, datetimes) and an **attribute-based constraint composition system** that lets you define new validated types declaratively -- no constructor boilerplate needed.

## Quick Overview

Use the built-in types directly as parameter and return type hints:

```php
use StrongType\Int\PortNumber;
use StrongType\String\{EmailString, UuidString};

function sendWelcome(EmailString $to, UuidString $userId, PortNumber $smtpPort): void
{
    // Inside this function, every value is already validated -- no defensive checks needed.
}

sendWelcome(
    new EmailString('alice@example.com'),
    new UuidString('550e8400-e29b-41d4-a716-446655440000'),
    new PortNumber(587),
);

new EmailString('not-an-email'); // StrongTypeException
new PortNumber(0);               // StrongTypeException: "PortNumber type must be >= 1, got 0"
```

When you need a type the library doesn't ship, compose one with stacked attributes:

```php
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, Unique, ElementType};

#[Min(1), Max(65535)]
readonly class Port extends \StrongType\Int\Integer {}

#[Nonempty, Pattern('/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/')]
readonly class Username extends \StrongType\String\StringType {}

#[Nonempty, Unique, ElementType('int')]
class UserIds extends \StrongType\Arrays\ArrayType {}

$port = new Port(8080);           // OK
$port = new Port(0);              // StrongTypeException: "Port type must be >= 1, got 0"

$user = new Username('alice_99'); // OK
$user = new Username('99bad');    // StrongTypeException

$userIds = new UserIds([42, 108, 1337]); // OK
$userIds = new UserIds([42, 42, 1337]);  // StrongTypeException (duplicate)
```

## Setup

```bash
composer require markrogoyski/strongtype-php
```

**Requirements**
- PHP 8.4+
- Extensions: `ctype`, `filter`, `json` (all bundled with PHP by default)

## Usage

### Construction, Value Access, and Serialization

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

### Type Hints Throughout Your Domain Code

Use strong types as constructor, parameter, and return types so validation lives at the system boundary and the rest of your code can trust every value:

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

## Built-in Types

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

#[Min(200), Max(999)]
readonly class AreaCode extends Integer {}

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

> **Note:** scalar subclasses (`Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`) are declared `readonly`, but `ArrayType` subclasses are **not** — the base class uses a `protected(set)` property that can't appear inside a `readonly` class. The value remains immutable in practice; you simply omit the keyword.

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

#### Enum Constraint

| Attribute | Parameters | Description |
| --- | --- | --- |
| `InEnum` | `class-string $enumClass` | Value matches a backing value (for `BackedEnum`) or a case name (for pure `UnitEnum`). Works on `Integer` (for `int`-backed enums) and `StringType` (for `string`-backed or pure enums). See [Wrapping Enums](#wrapping-enums). |

#### Constraint Combinators

| Attribute | Parameters | Description |
| --- | --- | --- |
| `AnyOf` | `ConstraintInterface ...$children` | Passes if at least one child passes; combined error message lists every child failure. See [Composing Constraints](#composing-constraints). |
| `AllOf` | `ConstraintInterface ...$children` | Passes only if every child passes; first failure wins (verbatim). |
| `Not` | `ConstraintInterface $child` | Inverts a single child constraint. |

Combinator priority equals the minimum priority of its children, so a combinator wrapping range-band children (priority 50) runs alongside range checks rather than after format checks.

### Constraint Inheritance

Constraints are inherited through the class hierarchy. Child classes get all parent constraints plus their own:

```php
use StrongType\Constraint\{DivisibleBy, Min, Max};
use StrongType\Int\Integer;

#[Min(1), Max(1000)]
readonly class OrderQuantity extends Integer {}

// Wholesale ships in dozens. Inherits Min(1) and Max(1000) from OrderQuantity, adds DivisibleBy(12).
#[DivisibleBy(12)]
readonly class CasePackQuantity extends OrderQuantity {}

new CasePackQuantity(24);   // OK -- divisible by 12 and in range
new CasePackQuantity(13);   // StrongTypeException -- not divisible by 12
new CasePackQuantity(1008); // StrongTypeException -- exceeds Max(1000) (inherited)
```

This also works with the built-in types, since they use constraints themselves:

```php
use StrongType\Constraint\Max;
use StrongType\Int\PositiveInt;

// PositiveInt already has #[Positive], so PageSize inherits > 0
#[Max(100)]
readonly class PageSize extends PositiveInt {}

new PageSize(50);  // OK
new PageSize(0);   // StrongTypeException -- not positive (inherited from PositiveInt)
new PageSize(101); // StrongTypeException -- exceeds max (added by PageSize)
```

### Priority Ordering

Constraints execute in priority order (lower runs first). This ensures range checks happen before format checks, and format checks before semantic checks:

| Priority | Category | Attributes |
| --- | --- | --- |
| 50 | Range / size | Min, Max, Positive, Negative, Nonempty, MinLength, MaxLength, MinCount, MaxCount, etc. |
| 60 | Content | Nonblank |
| 100 | Format | Pattern, Alpha, Alphanumeric, Lowercase, Uppercase, Unique, ElementType, DateFormat, etc. |
| 150 | Semantic | Email, Url, IpAddress, Json, ClassExists, DateTimeParseable |
| min(children) | Combinator | `AnyOf`, `AllOf`, `Not` — execute at the lowest priority among their children |

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

### Composing Constraints

The `AnyOf`, `AllOf`, and `Not` combinators are themselves constraints, so they compose with every built-in or user-defined `ConstraintInterface` — including each other. Children are passed as constructor arguments using `new`, which PHP allows in attribute argument expressions.

**`AnyOf` — at least one child must pass.** Useful for disjoint-but-valid ranges, alternate formats, or "either of these patterns":

```php
use StrongType\Constraint\{AnyOf, InRange};
use StrongType\Int\Integer;

// A port that is either well-known (1–1023) or in the IANA dynamic/private range (49152–65535).
#[AnyOf(new InRange(1, 1023), new InRange(49152, 65535))]
readonly class WellKnownOrEphemeralPort extends Integer {}

new WellKnownOrEphemeralPort(80);    // OK
new WellKnownOrEphemeralPort(50000); // OK
new WellKnownOrEphemeralPort(8080);  // StrongTypeException -- "must satisfy one of: [...]"
```

When every child fails, `AnyOf` emits a composite message: `"{Type} type must satisfy one of: [child1msg | child2msg | …]"`.

**`AllOf` — every child must pass.** Children run in **argument order** — `AllOf` does not sort by priority, so the first child you pass is the first child evaluated. This differs from stacking attributes, which run in priority order. Use `AllOf` when you need a single groupable unit you can nest inside `AnyOf` or `Not`, not as a drop-in replacement for stacked attributes:

```php
use StrongType\Constraint\{AllOf, MinLength, Pattern};
use StrongType\String\StringType;

// Standard env-var shape: at least 2 chars, must start with a letter or underscore,
// then uppercase letters / digits / underscores.
#[AllOf(new MinLength(2), new Pattern('/^[A-Z_][A-Z0-9_]*$/'))]
readonly class EnvVarName extends StringType {}
```

The first failing child wins; its message is returned verbatim. Order the children intentionally: cheaper checks first, or the message you would prefer to surface first.

**`Not` — invert a single child.** Useful for blocklists or "anything but" rules:

```php
use StrongType\Constraint\{Not, Pattern};
use StrongType\String\StringType;

// Reject all-uppercase words (likely shouting in user-generated content).
#[Not(new Pattern('/^[A-Z]+$/'))]
readonly class NoAllCapsString extends StringType {}

new NoAllCapsString('Hello');  // OK
new NoAllCapsString('HELLO');  // StrongTypeException -- "must NOT satisfy Pattern"
```

`Not`'s error message names the inverted constraint by class (`Pattern`, `Email`, etc.) but does not describe the inverted condition in detail.

**Nesting and composition with non-combinator attributes.** Combinators are constraints, so they nest freely and sit alongside ordinary attributes:

```php
use StrongType\Constraint\{AnyOf, MaxLength, Nonempty, Not, Pattern};
use StrongType\String\StringType;

// A username scoped to a known prefix, between 1 and 20 chars, that is not all digits.
#[
    Nonempty,
    MaxLength(20),
    AnyOf(new Pattern('/^user_/'), new Pattern('/^admin_/')),
    Not(new Pattern('/^\d+$/')),
]
readonly class ScopedUsername extends StringType {}
```

Combinators with zero children (`new AnyOf()`, `new AllOf()`) throw `\LogicException` at the same site as the other [constraint constructor invariants](#constraint-constructor-invariants).

### Wrapping Enums

`#[InEnum(MyEnum::class)]` constrains a strong type's value to the enum's backing values (for `BackedEnum`) or case names (for pure `UnitEnum`). No `EnumString` / `EnumInt` base class is needed — `InEnum` on a `StringType` or `Integer` subclass is sufficient:

```php
use StrongType\Constraint\InEnum;
use StrongType\String\StringType;

enum Status: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
}

#[InEnum(Status::class)]
readonly class StatusCode extends StringType {}

new StatusCode('active');   // OK
new StatusCode('Active');   // StrongTypeException -- case-sensitive backing-value match
new StatusCode('archived'); // StrongTypeException -- not a Status backing value

// Hand the validated value back to the enum when you need the case object.
$status = Status::from((new StatusCode('pending'))->value);
```

Int-backed and pure (non-backed) enums work the same way:

```php
use StrongType\Constraint\InEnum;
use StrongType\Int\Integer;
use StrongType\String\StringType;

enum Priority: int { case Low = 1; case Medium = 5; case High = 10; }

#[InEnum(Priority::class)]
readonly class PriorityCode extends Integer {}

new PriorityCode(5);   // OK
new PriorityCode(2);   // StrongTypeException

enum Color { case Red; case Green; case Blue; }

// Pure enums match by case name (case-sensitive).
#[InEnum(Color::class)]
readonly class ColorName extends StringType {}

new ColorName('Red');  // OK
new ColorName('red');  // StrongTypeException -- case names are case-sensitive
```

`InEnum` constructed with anything other than an existing enum class — a non-existent class, an interface, or a non-enum class — throws `\LogicException` at the same site as the other [constraint constructor invariants](#constraint-constructor-invariants).

## Scope and Semantics

A few cross-cutting policies govern every type and constraint in the library. These are the rules to keep in mind when picking a built-in type or designing your own.

### Format vs. registry validation

Types whose values come from external registries — `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString` — validate **format only**. They confirm the input matches the documented shape (e.g. `[A-Z]{2}` for ISO 3166-1 alpha-2) but do not check membership in the active registry. Unassigned codes such as `ZZ` or `XYZ` are accepted. Registries change; format does not, and embedding a frozen snapshot would silently rot. If you need active-registry membership, layer it on with a custom constraint that consults your own source of truth.

`Rfc3339` and `Rfc3339DateTimeString` similarly accept `:60` seconds syntactically (leap-second tolerance per the grammar) without verifying the minute is an actual IERS-inserted leap second.

### Byte length vs. Unicode length

`MinLength` and `MaxLength` count **bytes**, not Unicode characters or grapheme clusters. They are implemented with `strlen()`. A multibyte character such as `é` (two bytes in UTF-8) consumes two units against the bound, and an emoji like `🎉` (four bytes) consumes four. This matches what most byte-oriented protocols, database columns, and storage limits actually care about, and it stays predictable across PHP installs that may or may not have `mbstring` / `intl` extensions enabled.

If you need character or grapheme counting, write a [custom constraint](#custom-constraints) over `mb_strlen()` or `grapheme_strlen()`. The same applies to the equivalent array-side bounds (`MinCount`, `MaxCount`, `ExactCount`), which always count elements via `count()` regardless of key type.

### First-error behavior

When multiple constraints apply to a type, they run in priority order (see [Priority Ordering](#priority-ordering)) and validation **stops at the first failing constraint**. The thrown `ConstraintViolationException` carries that constraint's message; later constraints are not consulted, so you will not see an aggregated multi-error report.

This keeps error messages targeted and avoids cascading reports that are often artifacts of an earlier failure (e.g. a `Pattern` complaint about an empty string when `Nonempty` already caught it). If you need to collect every violation, validate by calling each constraint individually rather than relying on construction.

### Equality semantics

`equals()` is **type-strict**: comparing two strong types returns `true` only when the concrete subclass on both sides is identical. A `PositiveInt(5)` is not equal to an `Integer(5)` of a different subclass, and a `Nullable<PositiveInt>` is never equal to a bare `PositiveInt`.

For `ArrayType::equals`, insertion order and keys are part of the value: `['a' => 1, 'b' => 2]` is **not** equal to `['b' => 2, 'a' => 1]`, and `[1, 2, 3]` is not equal to `[3, 2, 1]`. Use [`ArrayType::equalsUnordered`](#base-class-methods) when you want multiset equality (same values, ignoring keys and order). Both forms compare values with strict (`===`) equality and recurse structurally into nested arrays.

### Shallow array validation

`ElementType` validates **direct children only** — it does not recurse into nested arrays. `#[ElementType('array')]` accepts `[[1, 'two', 3.0], ['a', null]]` because every top-level element is itself an array; the mixed types inside the nested arrays are not inspected. Likewise `Unique` checks uniqueness at the top level only.

To validate nested element types, wrap the inner arrays in their own strong type. For example, an `ArrayType` of `ArrayOfInts` enforces "list of int-only lists" by validating at each level explicitly rather than depending on a recursive descent the constraint system intentionally does not provide.

### Mutability

Every strong type is **immutable** once constructed. There are no setters, no in-place transforms, and no mutating array methods. The intended workflow is:

1. Construct at the system boundary (HTTP input, database load, message deserialization, CLI args).
2. Pass the typed value through your code via parameter and return types.
3. Trust the value everywhere it appears without re-validating.

When you need a different value, construct a new instance. `ArrayType::withValues($values)` is provided as a convenience for producing a same-type instance with a different array; use the constructor directly for everything else. This design keeps the validate-once-trust-everywhere contract intact across the lifetime of the value.

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

## Additional Features

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

## How It Works

The constraint system uses PHP 8 attributes and reflection:

1. **At first instantiation** of a type, `ConstraintValidator` reads all `ConstraintInterface` attributes from the class and its parents via reflection, sorts them by priority, and caches the result.
2. **On every instantiation**, it iterates the cached constraint list and calls `validate()` on each. The first failure throws `ConstraintViolationException` (a `StrongTypeException` subclass).
3. **Subsequent instantiations** skip reflection entirely -- it's a hash lookup plus iterating a small array.

The validator is a no-op for classes with no constraint attributes — types that keep manual constructors (such as `EmptyString`, `FixedSizeArray`, `TrueValue` / `FalseValue`, the `Timestamp` family, and `DateString` / `TimeString`) bypass it entirely and run their own validation logic instead.

## Reference

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
| `AnyOf`, `AllOf`                  | at least one child constraint                   |
| `InEnum`                          | class must be an existing enum (`enum_exists`)  |

```php
new MinLength(-1);            // \LogicException
new DivisibleBy(0);           // \LogicException
new Pattern('not-a-regex');   // \LogicException
new ElementType('integer');   // \LogicException -- 'integer' is a PHP type alias, not a builtin name
new InRange(100, 1);          // \LogicException
```

## Standards

StrongType PHP conforms to the following standards:

- PSR-1 - Basic coding standard
- PSR-4 - Autoloader
- PSR-12 - Extended coding style guide

## License

StrongType PHP is licensed under the MIT License.
