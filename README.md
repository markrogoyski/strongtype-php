# StrongType PHP

Strongly typed values for PHP -- validate once at construction, trust everywhere after.

StrongType provides two things:

- **Ready-to-use typed value objects**: integers, floats, strings, arrays, booleans, and datetimes that validate themselves at construction.
- **An attribute-based constraint composition system**: define new validated types declaratively by stacking constraint attributes. No constructor boilerplate.

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

new Port(8080);                  // OK
new Port(0);                     // StrongTypeException: "Port type must be >= 1, got 0"
new Username('alice_99');        // OK
new Username('99bad');           // StrongTypeException
new UserIds([42, 108, 1337]);    // OK
new UserIds([42, 42, 1337]);     // StrongTypeException (duplicate)
```

## Setup

```bash
composer require markrogoyski/strongtype-php
```

Requires PHP 8.4+ and the `ctype`, `filter`, `json` extensions (all bundled with PHP by default). See [docs/getting-started.md](docs/getting-started.md) for installation troubleshooting and full requirements.

## Built-in Types

Every type below is a validated, immutable value object. Click a name for a usage example.

### Integers

| Type | Description |
| --- | --- |
| [`PositiveInt`](docs/built-in-types.md#positiveint) | Strictly positive (`> 0`) |
| [`NegativeInt`](docs/built-in-types.md#negativeint) | Strictly negative (`< 0`) |
| [`NonnegativeInt`](docs/built-in-types.md#nonnegativeint) | Zero or positive |
| [`NonpositiveInt`](docs/built-in-types.md#nonpositiveint) | Zero or negative |
| [`NonzeroInt`](docs/built-in-types.md#nonzeroint) | Any nonzero integer |
| [`EvenInt`](docs/built-in-types.md#evenint) | Even integers |
| [`OddInt`](docs/built-in-types.md#oddint) | Odd integers |
| [`ByteInt`](docs/built-in-types.md#byteint) | Unsigned byte (`0..255`) |
| [`PercentInt`](docs/built-in-types.md#percentint) | Percentage (`0..100`) |
| [`PortNumber`](docs/built-in-types.md#portnumber) | TCP/UDP port (`1..65535`) |
| [`HttpStatusCode`](docs/built-in-types.md#httpstatuscode) | HTTP status code (`100..599`) |

### Floats

Float subtypes reject `INF`, `-INF`, and `NAN` by default (via inherited `#[Finite]`).

| Type | Description |
| --- | --- |
| [`PositiveFloat`](docs/built-in-types.md#positivefloat) | Strictly positive (`> 0`) |
| [`NegativeFloat`](docs/built-in-types.md#negativefloat) | Strictly negative (`< 0`) |
| [`NonnegativeFloat`](docs/built-in-types.md#nonnegativefloat) | Zero or positive |
| [`NonpositiveFloat`](docs/built-in-types.md#nonpositivefloat) | Zero or negative |
| [`NonzeroFloat`](docs/built-in-types.md#nonzerofloat) | Any nonzero float |
| [`UnitFloat`](docs/built-in-types.md#unitfloat) | Unit interval (`0.0..1.0`) |
| [`PercentFloat`](docs/built-in-types.md#percentfloat) | Percentage (`0.0..100.0`) |
| [`Latitude`](docs/built-in-types.md#latitude) | Geographic latitude (`-90..90`) |
| [`Longitude`](docs/built-in-types.md#longitude) | Geographic longitude (`-180..180`) |

### Strings

| Type | Description |
| --- | --- |
| [`NonemptyString`](docs/built-in-types.md#nonemptystring) | Not empty |
| [`NonblankString`](docs/built-in-types.md#nonblankstring) | Not empty or whitespace-only |
| [`AlphaString`](docs/built-in-types.md#alphastring) | Alphabetic characters only |
| [`UppercaseAlphaString`](docs/built-in-types.md#uppercasealphastring) | Uppercase alphabetic only |
| [`LowercaseAlphaString`](docs/built-in-types.md#lowercasealphastring) | Lowercase alphabetic only |
| [`AlphanumericString`](docs/built-in-types.md#alphanumericstring) | Alphanumeric only |
| [`NumericString`](docs/built-in-types.md#numericstring) | Numeric digits only |
| [`BinaryString`](docs/built-in-types.md#binarystring) | Binary digit string |
| [`HexString`](docs/built-in-types.md#hexstring) | Hexadecimal digits only |
| [`Base64String`](docs/built-in-types.md#base64string) | Canonical Base64 (RFC 4648 §4) |
| [`JsonString`](docs/built-in-types.md#jsonstring) | Valid JSON |
| [`EmailString`](docs/built-in-types.md#emailstring) | Valid email address |
| [`UrlString`](docs/built-in-types.md#urlstring) | Valid URL |
| [`IpAddressString`](docs/built-in-types.md#ipaddressstring) | IPv4 or IPv6 address |
| [`Ipv4AddressString`](docs/built-in-types.md#ipv4addressstring) | IPv4 only |
| [`Ipv6AddressString`](docs/built-in-types.md#ipv6addressstring) | IPv6 only |
| [`CidrString`](docs/built-in-types.md#cidrstring) | CIDR notation `ip/prefix` |
| [`MacAddressString`](docs/built-in-types.md#macaddressstring) | `xx:xx:xx:xx:xx:xx` |
| [`UuidString`](docs/built-in-types.md#uuidstring) | UUID shape |
| [`JwtString`](docs/built-in-types.md#jwtstring) | JWT `header.payload.signature` |
| [`PhoneE164String`](docs/built-in-types.md#phonee164string) | E.164 phone number |
| [`CountryCodeAlpha2String`](docs/built-in-types.md#countrycodealpha2string) | ISO 3166-1 alpha-2 (format only) |
| [`CountryCodeAlpha3String`](docs/built-in-types.md#countrycodealpha3string) | ISO 3166-1 alpha-3 (format only) |
| [`CurrencyCodeString`](docs/built-in-types.md#currencycodestring) | ISO 4217 (format only) |
| [`LanguageCodeString`](docs/built-in-types.md#languagecodestring) | ISO 639-1 (format only) |
| [`MimeTypeString`](docs/built-in-types.md#mimetypestring) | RFC 6838 `type/subtype` |
| [`SlugString`](docs/built-in-types.md#slugstring) | `lowercase-words-with-dashes` |
| [`SemverString`](docs/built-in-types.md#semverstring) | SemVer 2.0.0 |
| [`HexColorString`](docs/built-in-types.md#hexcolorstring) | `#RGB` or `#RRGGBB` |
| [`ClassString`](docs/built-in-types.md#classstring) | Existing class, interface, or enum |
| [`DateTimeString`](docs/built-in-types.md#datetimestring) | Parseable date/time string |
| [`Rfc3339DateTimeString`](docs/built-in-types.md#rfc3339datetimestring) | RFC 3339 date-time |
| [`EmptyString`](docs/built-in-types.md#emptystring) | Must be empty |

### Arrays

| Type | Description |
| --- | --- |
| [`NonemptyArray`](docs/built-in-types.md#nonemptyarray) | At least one element |
| [`EmptyArray`](docs/built-in-types.md#emptyarray) | Must be empty |
| [`UniqueArray`](docs/built-in-types.md#uniquearray) | Nonempty with unique values |
| [`FixedSizeArray`](docs/built-in-types.md#fixedsizearray) | Exact count (runtime `$size`) |
| [`ListArray`](docs/built-in-types.md#listarray) | Sequential integer keys from 0 |
| [`AssociativeArray`](docs/built-in-types.md#associativearray) | Nonempty, not a list |
| [`ArrayOfStrings`](docs/built-in-types.md#arrayofstrings) | Nonempty, all strings |
| [`ArrayOfInts`](docs/built-in-types.md#arrayofints) | Nonempty, all ints |
| [`ArrayOfFloats`](docs/built-in-types.md#arrayoffloats) | Nonempty, all floats |
| [`ArrayOfBools`](docs/built-in-types.md#arrayofbools) | Nonempty, all bools |
| [`ArrayOfObjects`](docs/built-in-types.md#arrayofobjects) | Nonempty, all objects |
| [`ArrayOfCallables`](docs/built-in-types.md#arrayofcallables) | Nonempty, all callables |
| [`ArrayOfResources`](docs/built-in-types.md#arrayofresources) | Nonempty, all resources |
| [`ArrayOfIterables`](docs/built-in-types.md#arrayofiterables) | Nonempty, all iterables |
| [`ArrayOfArrays`](docs/built-in-types.md#arrayofarrays) | Nonempty, all arrays |

### Booleans

| Type | Description |
| --- | --- |
| [`TrueValue`](docs/built-in-types.md#truevalue) | PHP `true` literal type |
| [`FalseValue`](docs/built-in-types.md#falsevalue) | PHP `false` literal type |

### DateTime

| Type | Description |
| --- | --- |
| [`Timestamp`](docs/built-in-types.md#timestamp) | Non-negative Unix timestamp |
| [`FutureTimestamp`](docs/built-in-types.md#futuretimestamp) | Timestamp in the future |
| [`PastTimestamp`](docs/built-in-types.md#pasttimestamp) | Timestamp in the past |
| [`DateString`](docs/built-in-types.md#datestring) | `YYYY-MM-DD` calendar date |
| [`TimeString`](docs/built-in-types.md#timestring) | `HH:MM:SS` 24-hour time |

## Constraint Attributes

Stack attributes on the matching base type shown below — each attribute only validates against the value type it's designed for. Putting a string-only attribute (e.g. `#[Email]`) on an `Integer` subclass is a silent no-op, not a definition-time error.

### Numeric

| Attribute | Description |
| --- | --- |
| [`Min`](docs/constraint-attributes.md#min) | `>= $min` (or `>` if exclusive) |
| [`Max`](docs/constraint-attributes.md#max) | `<= $max` (or `<` if exclusive) |
| [`InRange`](docs/constraint-attributes.md#inrange) | Combined min + max |
| [`Positive`](docs/constraint-attributes.md#positive) | `> 0` |
| [`Negative`](docs/constraint-attributes.md#negative) | `< 0` |
| [`Nonnegative`](docs/constraint-attributes.md#nonnegative) | `>= 0` |
| [`Nonpositive`](docs/constraint-attributes.md#nonpositive) | `<= 0` |
| [`Nonzero`](docs/constraint-attributes.md#nonzero) | `!= 0` |
| [`Even`](docs/constraint-attributes.md#even) | Even integer |
| [`Odd`](docs/constraint-attributes.md#odd) | Odd integer |
| [`DivisibleBy`](docs/constraint-attributes.md#divisibleby) | `% $divisor === 0` |
| [`Finite`](docs/constraint-attributes.md#finite) | Rejects `INF`, `-INF`, `NAN` |
| [`InList`](docs/constraint-attributes.md#inlist) | Strict allowlist |

### String

| Attribute | Description |
| --- | --- |
| [`Nonempty`](docs/constraint-attributes.md#nonempty) | `strlen > 0` (also works on arrays) |
| [`Nonblank`](docs/constraint-attributes.md#nonblank) | `trim() !== ''` |
| [`MinLength`](docs/constraint-attributes.md#minlength) | Minimum byte length |
| [`MaxLength`](docs/constraint-attributes.md#maxlength) | Maximum byte length |
| [`Pattern`](docs/constraint-attributes.md#pattern) | `preg_match` (repeatable) |
| [`NotPattern`](docs/constraint-attributes.md#notpattern) | Must not match (repeatable) |
| [`Alpha`](docs/constraint-attributes.md#alpha) | `ctype_alpha` |
| [`Alphanumeric`](docs/constraint-attributes.md#alphanumeric) | `ctype_alnum` |
| [`NumericDigits`](docs/constraint-attributes.md#numericdigits) | `ctype_digit` |
| [`HexDigits`](docs/constraint-attributes.md#hexdigits) | `ctype_xdigit` |
| [`Lowercase`](docs/constraint-attributes.md#lowercase) | `ctype_lower` |
| [`Uppercase`](docs/constraint-attributes.md#uppercase) | `ctype_upper` |
| [`Base64`](docs/constraint-attributes.md#base64) | Canonical Base64 (RFC 4648 §4) |
| [`Email`](docs/constraint-attributes.md#email) | `FILTER_VALIDATE_EMAIL` |
| [`Url`](docs/constraint-attributes.md#url) | `FILTER_VALIDATE_URL` |
| [`IpAddress`](docs/constraint-attributes.md#ipaddress) | IPv4 or IPv6 |
| [`Ipv4`](docs/constraint-attributes.md#ipv4) | IPv4 only |
| [`Ipv6`](docs/constraint-attributes.md#ipv6) | IPv6 only |
| [`Cidr`](docs/constraint-attributes.md#cidr) | `ip/prefix` |
| [`Uuid`](docs/constraint-attributes.md#uuid) | UUID shape |
| [`Json`](docs/constraint-attributes.md#json) | `json_validate()` |
| [`Rfc3339`](docs/constraint-attributes.md#rfc3339) | RFC 3339 date-time |
| [`ClassExists`](docs/constraint-attributes.md#classexists) | Existing class, interface, or enum |
| [`DateTimeParseable`](docs/constraint-attributes.md#datetimeparseable) | `new DateTimeImmutable()` succeeds |

### Array

| Attribute | Description |
| --- | --- |
| [`Nonempty`](docs/constraint-attributes.md#nonempty) | `count > 0` (shared with strings) |
| [`IsEmpty`](docs/constraint-attributes.md#isempty) | `count === 0` |
| [`MinCount`](docs/constraint-attributes.md#mincount) | `count >= $minCount` |
| [`MaxCount`](docs/constraint-attributes.md#maxcount) | `count <= $maxCount` |
| [`ExactCount`](docs/constraint-attributes.md#exactcount) | `count === $count` |
| [`Unique`](docs/constraint-attributes.md#unique) | No duplicate values |
| [`ElementType`](docs/constraint-attributes.md#elementtype) | Each element matches type |
| [`IsList`](docs/constraint-attributes.md#islist) | `array_is_list` |
| [`IsAssociative`](docs/constraint-attributes.md#isassociative) | `!array_is_list` |

### DateTime

| Attribute | Description |
| --- | --- |
| [`DateFormat`](docs/constraint-attributes.md#dateformat) | Validates against a date format |
| [`TimeFormat`](docs/constraint-attributes.md#timeformat) | `HH:MM:SS` |
| [`InFuture`](docs/constraint-attributes.md#infuture) | `> time()` |
| [`InPast`](docs/constraint-attributes.md#inpast) | `< time()` |

### Enum

| Attribute | Description |
| --- | --- |
| [`InEnum`](docs/constraint-attributes.md#inenum) | Match a backing value or case name |

### Combinators

| Attribute | Description |
| --- | --- |
| [`AnyOf`](docs/constraint-attributes.md#anyof) | At least one child passes |
| [`AllOf`](docs/constraint-attributes.md#allof) | All children pass; first failure wins |
| [`Not`](docs/constraint-attributes.md#not) | Inverts a single child |

## Defining Your Own Types

Constraints inherit through the class hierarchy — stack attributes on a child class to layer additional rules on top of an existing validated type:

```php
use StrongType\Constraint\{DivisibleBy, Min, Max};
use StrongType\Int\Integer;

#[Min(1), Max(1000)]
readonly class OrderQuantity extends Integer {}

// Wholesale ships in dozens. Inherits Min(1) and Max(1000), adds DivisibleBy(12).
#[DivisibleBy(12)]
readonly class CasePackQuantity extends OrderQuantity {}

new CasePackQuantity(24);   // OK
new CasePackQuantity(13);   // StrongTypeException -- not divisible by 12
new CasePackQuantity(1008); // StrongTypeException -- exceeds Max(1000) (inherited)
```

**[Full guide: defining types, inheritance, priority, custom constraints, combinators, wrapping enums →](docs/defining-types.md)**

## Error Handling

`StrongType\Exception\StrongTypeException` is the umbrella for all validation failures. Catch it when you want to handle any invalid value uniformly; catch `ConstraintViolationException` or `FormatException` when you want to react to a specific failure mode.

| Failure mode | Result |
| --- | --- |
| Constructor argument type mismatch (e.g. `new PositiveInt('5')`) under `declare(strict_types=1)` | PHP `\TypeError` |
| `tryFrom()` with a type-mismatched argument | returns `null` |
| Constraint attribute fails during validation | `ConstraintViolationException` |
| Manual parse fails in `DateString` / `TimeString` | `FormatException` |

> **Strict types matter.** `\TypeError` for `new PositiveInt('5')` only fires from a caller file declared with `declare(strict_types=1)`. Without it, PHP coerces `'5'` to `5` and construction succeeds. Always declare strict types in your application code so type mismatches surface immediately.

**[Full guide: catching specific failures, tryFrom semantics →](docs/getting-started.md#error-handling)**

## Semantics in 30 Seconds

A few cross-cutting policies govern every type and constraint. The full reasoning lives in [docs/semantics.md](docs/semantics.md).

- **[Format vs. registry validation](docs/semantics.md#format-vs-registry-validation)** — country / currency / language / MIME types validate format only, not active-registry membership.
- **[Byte length vs. Unicode length](docs/semantics.md#byte-length-vs-unicode-length)** — `MinLength` / `MaxLength` count bytes, not graphemes.
- **[First-error behavior](docs/semantics.md#first-error-behavior)** — validation stops at the first failing constraint.
- **[Equality is type-strict](docs/semantics.md#equality-semantics)** — same concrete subclass required; `ArrayType::equals` is order-sensitive.
- **[Shallow array validation](docs/semantics.md#shallow-array-validation)** — `ElementType` / `Unique` check direct children only.

## Documentation

- **Getting started:** [installation & first values](docs/getting-started.md)
- **Reference:** [built-in types](docs/built-in-types.md) · [constraint attributes](docs/constraint-attributes.md) · [Nullable & base-class helpers](docs/nullable-and-helpers.md)
- **Guides:** [defining your own types](docs/defining-types.md) · [library semantics & gotchas](docs/semantics.md)
- **Internals:** [how it works + constructor invariants](docs/architecture.md)
- **[Full documentation index →](docs/README.md)**

## Standards

StrongType PHP conforms to PSR-1 (basic coding standard), PSR-4 (autoloader), and PSR-12 (extended coding style guide).

## License

StrongType PHP is licensed under the MIT License.
