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

### Floats

| Type | Constraint | Details |
| --- | --- | --- |
| `PositiveFloat` | `> 0` | Strictly positive |
| `NegativeFloat` | `< 0` | Strictly negative |
| `NonnegativeFloat` | `>= 0` | Zero or positive |
| `NonpositiveFloat` | `<= 0` | Zero or negative |
| `NonzeroFloat` | `!= 0` | Any nonzero float |
| `UnitFloat` | `0.0..1.0` | Unit interval |

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
| `Base64String` | Base64 charset | Valid base64 encoding |
| `JsonString` | `json_validate` | Valid JSON |
| `EmailString` | `FILTER_VALIDATE_EMAIL` | Valid email address |
| `UrlString` | `FILTER_VALIDATE_URL` | Valid URL |
| `IpAddressString` | `FILTER_VALIDATE_IP` | Valid IP address (v4 or v6) |
| `UuidString` | UUID pattern | `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` |
| `SlugString` | Slug pattern | `lowercase-words-with-dashes` |
| `SemverString` | Semver pattern | `MAJOR.MINOR.PATCH[-prerelease][+build]` |
| `HexColorString` | Hex color pattern | `#RGB` or `#RRGGBB` |
| `ClassString` | `class_exists` | Existing class, interface, or enum |
| `DateTimeString` | `DateTimeImmutable` | Parseable date/time string |
| `EmptyString` | `strlen === 0` | Must be empty (default `''`) |

### Arrays

| Type | Constraint | Details |
| --- | --- | --- |
| `NonemptyArray` | `count > 0` | At least one element |
| `EmptyArray` | `count === 0` | Must be empty |
| `UniqueArray` | No duplicates | Nonempty with unique values |
| `FixedSizeArray` | `count === $size` | Exact element count (runtime) |
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

```php
use StrongType\Nullable;
use StrongType\Int\PositiveInt;

$value = new Nullable(PositiveInt::class, 5);    // wraps PositiveInt(5)
$null  = new Nullable(PositiveInt::class, null);  // wraps null

$value->getValue();  // 5
$null->getValue();   // null
$null->isNull();     // true
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
| `Positive` | none | `> 0` |
| `Negative` | none | `< 0` |
| `Nonnegative` | none | `>= 0` |
| `Nonpositive` | none | `<= 0` |
| `Nonzero` | none | `!= 0` |
| `Even` | none | `% 2 === 0` |
| `Odd` | none | `% 2 !== 0` |
| `DivisibleBy` | `int $divisor` | `% $divisor === 0` |

#### String Constraints (for StringType)

| Attribute | Parameters | Description |
| --- | --- | --- |
| `Nonempty` | none | `strlen > 0` (also works on arrays) |
| `Nonblank` | none | `trim() !== ''` |
| `MinLength` | `int $minLength` | `strlen >= $minLength` |
| `MaxLength` | `int $maxLength` | `strlen <= $maxLength` |
| `Pattern` | `string $pattern` | `preg_match($pattern, $value)` |
| `Alpha` | none | `ctype_alpha` |
| `Alphanumeric` | none | `ctype_alnum` |
| `NumericDigits` | none | `ctype_digit` |
| `HexDigits` | none | `ctype_xdigit` |
| `Lowercase` | none | `ctype_lower` |
| `Uppercase` | none | `ctype_upper` |
| `Base64` | none | Valid base64 charset and padding |
| `Email` | none | `filter_var(FILTER_VALIDATE_EMAIL)` |
| `Url` | none | `filter_var(FILTER_VALIDATE_URL)` |
| `IpAddress` | none | `filter_var(FILTER_VALIDATE_IP)` |
| `Json` | none | `json_validate()` |
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

Create your own constraint by implementing `ConstraintInterface`:

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

## Examples

### Domain Modeling

```php
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, MaxLength, Positive, Nonnegative, ElementType, Unique};
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
#[Nonempty, Unique, ElementType('string'), MaxLength(50)]
readonly class FeatureFlags extends ArrayType {}
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
2. **On every instantiation**, it iterates the cached constraint list and calls `validate()` on each. The first failure throws `StrongTypeException`.
3. **Subsequent instantiations** skip reflection entirely -- it's a hash lookup plus iterating a small array.

Existing types with manual constructors continue to work unchanged. The validator is a no-op for classes with no constraint attributes.

## Standards

StrongType PHP conforms to the following standards:

- PSR-1 - Basic coding standard
- PSR-4 - Autoloader
- PSR-12 - Extended coding style guide

## License

StrongType PHP is licensed under the MIT License.
