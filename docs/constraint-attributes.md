# Constraint Attributes

Back to [README](../README.md) · [Documentation index](README.md)

This is the full catalog of constraint attributes shipped with the library, organised by category. Each section opens with a quick-reference table and follows with one subsection per attribute — a one-line description, the constructor signature, an example, and (where relevant) a constructor-invariants line for fail-fast misconfigurations. Failure messages on per-attribute entries are abbreviated to `// throws StrongTypeException`; refer to the [README](../README.md#error-handling) and [getting-started.md](getting-started.md#error-handling) for verbatim message format. The full table of constructor invariants lives in [architecture.md](architecture.md#constructor-invariants).

The catalog covers six categories:

- [Numeric](#numeric) — 13 attributes
- [String](#string) — 24 attributes
- [Array](#array) — 9 attributes (note: `Nonempty` is shared with strings)
- [DateTime](#datetime) — 4 attributes
- [Enum](#enum) — 1 attribute
- [Combinators](#combinators) — 3 attributes

The full priority-bands reference for ordering attribute execution is at the [bottom of this file](#priority-bands).

---

## Numeric

These attributes apply to `Integer` and `FloatingPoint` subclasses.

| Attribute | Parameters | Description |
| --- | --- | --- |
| [`Min`](#min) | `int\|float $min, bool $exclusive = false` | `>= $min` (or `> $min` if exclusive) |
| [`Max`](#max) | `int\|float $max, bool $exclusive = false` | `<= $max` (or `< $max` if exclusive) |
| [`InRange`](#inrange) | `int\|float $min, int\|float $max, bool $exclusiveMin = false, bool $exclusiveMax = false` | Combined min + max |
| [`Positive`](#positive) | none | `> 0` |
| [`Negative`](#negative) | none | `< 0` |
| [`Nonnegative`](#nonnegative) | none | `>= 0` |
| [`Nonpositive`](#nonpositive) | none | `<= 0` |
| [`Nonzero`](#nonzero) | none | `!= 0` |
| [`Even`](#even) | none | `% 2 === 0` |
| [`Odd`](#odd) | none | `% 2 !== 0` |
| [`DivisibleBy`](#divisibleby) | `int $divisor` | `% $divisor === 0` |
| [`Finite`](#finite) | none | Rejects `INF`, `-INF`, `NAN` (auto-applied to every `FloatingPoint` subtype) |
| [`InList`](#inlist) | `mixed ...$allowed` | Strict allowlist membership |

### `Min`

Lower bound (inclusive by default). Representative attribute — verbatim error message shown.

**Constructor:** `Min(int|float $min, bool $exclusive = false)`

```php
use StrongType\Constraint\Min;
use StrongType\Int\Integer;

#[Min(1)]
readonly class PageNumber extends Integer {}

new PageNumber(1);    // OK
new PageNumber(0);    // StrongTypeException: "PageNumber type must be >= 1, got 0"

#[Min(0, exclusive: true)]
readonly class StrictlyPositive extends Integer {}

new StrictlyPositive(1);  // OK
new StrictlyPositive(0);  // StrongTypeException: "StrictlyPositive type must be > 0, got 0"
```

### `Max`

Upper bound (inclusive by default).

**Constructor:** `Max(int|float $max, bool $exclusive = false)`

```php
use StrongType\Constraint\{Max, Min};
use StrongType\Int\Integer;

#[Min(1), Max(99)]
readonly class PercentRangeInt extends Integer {}

new PercentRangeInt(99);   // OK
new PercentRangeInt(100);  // throws StrongTypeException
```

### `InRange`

Combined `min..max` with optional open bounds. Convenience for stacking `Min` + `Max`.

**Constructor:** `InRange(int|float $min, int|float $max, bool $exclusiveMin = false, bool $exclusiveMax = false)`

**Constructor invariants:** `min > max` raises `\LogicException`; equal bounds combined with an exclusive flag raises `\LogicException` (the range would be empty).

```php
use StrongType\Constraint\InRange;
use StrongType\Int\Integer;

#[InRange(1, 100)]
readonly class HundredScale extends Integer {}

new HundredScale(50);   // OK
new HundredScale(0);    // throws StrongTypeException
```

### `Positive`

Strictly greater than zero.

**Constructor:** `Positive()`

```php
use StrongType\Constraint\Positive;
use StrongType\Int\Integer;

#[Positive]
readonly class Quantity extends Integer {}

new Quantity(1);   // OK
new Quantity(0);   // throws StrongTypeException
```

### `Negative`

Strictly less than zero.

**Constructor:** `Negative()`

```php
use StrongType\Constraint\Negative;
use StrongType\Int\Integer;

#[Negative]
readonly class TemperatureDelta extends Integer {}

new TemperatureDelta(-1);  // OK
new TemperatureDelta(0);   // throws StrongTypeException
```

### `Nonnegative`

Greater than or equal to zero.

**Constructor:** `Nonnegative()`

```php
use StrongType\Constraint\Nonnegative;
use StrongType\Float\FloatingPoint;

#[Nonnegative]
readonly class Money extends FloatingPoint {}

new Money(0.0);    // OK
new Money(-0.01);  // throws StrongTypeException
```

### `Nonpositive`

Less than or equal to zero.

**Constructor:** `Nonpositive()`

```php
use StrongType\Constraint\Nonpositive;
use StrongType\Int\Integer;

#[Nonpositive]
readonly class DebtBalance extends Integer {}

new DebtBalance(0);    // OK
new DebtBalance(-100); // OK
new DebtBalance(1);    // throws StrongTypeException
```

### `Nonzero`

Any value except zero.

**Constructor:** `Nonzero()`

```php
use StrongType\Constraint\Nonzero;
use StrongType\Int\Integer;

#[Nonzero]
readonly class Divisor extends Integer {}

new Divisor(7);   // OK
new Divisor(0);   // throws StrongTypeException
```

### `Even`

Even integer.

**Constructor:** `Even()`

```php
use StrongType\Constraint\Even;
use StrongType\Int\Integer;

#[Even]
readonly class PairedCount extends Integer {}

new PairedCount(0);  // OK
new PairedCount(7);  // throws StrongTypeException
```

### `Odd`

Odd integer.

**Constructor:** `Odd()`

```php
use StrongType\Constraint\Odd;
use StrongType\Int\Integer;

#[Odd]
readonly class CenteredKernelSize extends Integer {}

new CenteredKernelSize(3);  // OK
new CenteredKernelSize(4);  // throws StrongTypeException
```

### `DivisibleBy`

Divisor must evenly divide the value.

**Constructor:** `DivisibleBy(int $divisor)`

**Constructor invariants:** `divisor === 0` raises `\LogicException`.

```php
use StrongType\Constraint\DivisibleBy;
use StrongType\Int\PositiveInt;

#[DivisibleBy(100)]
readonly class CentAmount extends PositiveInt {}

new CentAmount(100);   // OK
new CentAmount(150);   // throws StrongTypeException
```

### `Finite`

Rejects `INF`, `-INF`, and `NAN`. Already auto-applied to every `FloatingPoint` subtype at priority 40 (before any range check). Add it explicitly only if you've subclassed a non-Float base and want similar protection.

**Constructor:** `Finite()`

```php
use StrongType\Float\NonnegativeFloat;

new NonnegativeFloat(1.5);  // OK
new NonnegativeFloat(INF);  // throws StrongTypeException — Finite caught it
new NonnegativeFloat(NAN);  // throws StrongTypeException
```

### `InList`

Strict membership check against an allowlist.

**Constructor:** `InList(mixed ...$allowed)`

**Constructor invariants:** zero allowed values raises `\LogicException`.

```php
use StrongType\Constraint\InList;
use StrongType\Int\Integer;

#[InList(1, 2, 5, 10)]
readonly class CoinDenomination extends Integer {}

new CoinDenomination(5);   // OK
new CoinDenomination(3);   // throws StrongTypeException
```

---

## String

These attributes apply to `StringType` subclasses.

| Attribute | Parameters | Description |
| --- | --- | --- |
| [`Nonempty`](#nonempty) | none | `strlen > 0` (also works on arrays — see [Array](#array)) |
| [`Nonblank`](#nonblank) | none | `trim() !== ''` |
| [`MinLength`](#minlength) | `int $minLength` | `strlen >= $minLength` (byte length) |
| [`MaxLength`](#maxlength) | `int $maxLength` | `strlen <= $maxLength` (byte length) |
| [`Pattern`](#pattern) | `string $pattern` | `preg_match($pattern, $value)` (repeatable) |
| [`NotPattern`](#notpattern) | `string $pattern` | Must not match (repeatable) |
| [`Alpha`](#alpha) | none | `ctype_alpha` |
| [`Alphanumeric`](#alphanumeric) | none | `ctype_alnum` |
| [`NumericDigits`](#numericdigits) | none | `ctype_digit` |
| [`HexDigits`](#hexdigits) | none | `ctype_xdigit` |
| [`Lowercase`](#lowercase) | none | `ctype_lower` |
| [`Uppercase`](#uppercase) | none | `ctype_upper` |
| [`Base64`](#base64) | none | Canonical standard Base64 (RFC 4648 §4) |
| [`Email`](#email) | none | `filter_var(FILTER_VALIDATE_EMAIL)` |
| [`Url`](#url) | none | `filter_var(FILTER_VALIDATE_URL)` |
| [`IpAddress`](#ipaddress) | none | `filter_var(FILTER_VALIDATE_IP)` |
| [`Ipv4`](#ipv4) | none | `FILTER_FLAG_IPV4` |
| [`Ipv6`](#ipv6) | none | `FILTER_FLAG_IPV6` |
| [`Cidr`](#cidr) | none | `ip/prefix` for IPv4 or IPv6 |
| [`Uuid`](#uuid) | none | UUID shape |
| [`Json`](#json) | none | `json_validate()` |
| [`Rfc3339`](#rfc3339) | none | RFC 3339 date-time grammar |
| [`ClassExists`](#classexists) | none | `class_exists \|\| interface_exists \|\| enum_exists` |
| [`DateTimeParseable`](#datetimeparseable) | none | `new \DateTimeImmutable()` succeeds |

`MinLength` and `MaxLength` count **bytes**, not graphemes. See [byte length vs. Unicode length](semantics.md#byte-length-vs-unicode-length).

### `Nonempty`

Non-empty string (`strlen > 0`) or non-empty array (`count > 0`). Works on both string and array bases.

**Constructor:** `Nonempty()`

```php
use StrongType\Constraint\Nonempty;
use StrongType\String\StringType;

#[Nonempty]
readonly class NonemptyName extends StringType {}

new NonemptyName('Alice');  // OK
new NonemptyName('');       // throws StrongTypeException
```

### `Nonblank`

Non-empty after `trim()` — rejects whitespace-only strings.

**Constructor:** `Nonblank()`

```php
use StrongType\Constraint\Nonblank;
use StrongType\String\StringType;

#[Nonblank]
readonly class CommentBody extends StringType {}

new CommentBody('hi');     // OK
new CommentBody('   ');    // throws StrongTypeException
```

### `MinLength`

Minimum byte length.

**Constructor:** `MinLength(int $minLength)`

**Constructor invariants:** negative length raises `\LogicException`.

```php
use StrongType\Constraint\MinLength;
use StrongType\String\StringType;

#[MinLength(8)]
readonly class Password extends StringType {}

new Password('correct horse');  // OK
new Password('abc');            // throws StrongTypeException
```

### `MaxLength`

Maximum byte length.

**Constructor:** `MaxLength(int $maxLength)`

**Constructor invariants:** negative length raises `\LogicException`.

```php
use StrongType\Constraint\{MaxLength, Nonempty};
use StrongType\String\StringType;

#[Nonempty, MaxLength(280)]
readonly class TweetBody extends StringType {}

new TweetBody('hello');                                       // OK
new TweetBody(str_repeat('x', 281));                          // throws StrongTypeException
```

### `Pattern`

`preg_match` against a regular expression. Repeatable — stack multiple `#[Pattern(...)]` attributes to require all of them. Representative attribute — verbatim error message shown.

**Constructor:** `Pattern(string $pattern)`

**Constructor invariants:** regex that fails to compile raises `\LogicException`.

```php
use StrongType\Constraint\{Nonempty, Pattern};
use StrongType\String\StringType;

#[Nonempty, Pattern('/^[a-z][a-z0-9-]*$/')]
readonly class KebabIdentifier extends StringType {}

new KebabIdentifier('blog-post');  // OK
new KebabIdentifier('Blog-Post');  // StrongTypeException: "KebabIdentifier type must match pattern /^[a-z][a-z0-9-]*$/, got Blog-Post"
```

### `NotPattern`

Inverted pattern match — must not match. Repeatable.

**Constructor:** `NotPattern(string $pattern)`

**Constructor invariants:** regex that fails to compile raises `\LogicException`.

```php
use StrongType\Constraint\{Nonempty, NotPattern};
use StrongType\String\StringType;

// Reject SQL-injection-shaped inputs in a free-text search box.
#[Nonempty, NotPattern('/(--|;|\bDROP\b)/i')]
readonly class SearchTerm extends StringType {}

new SearchTerm('alice');                  // OK
new SearchTerm("alice'; DROP TABLE x;"); // throws StrongTypeException
```

### `Alpha`

`ctype_alpha`.

**Constructor:** `Alpha()`

```php
use StrongType\Constraint\Alpha;
use StrongType\String\StringType;

#[Alpha]
readonly class GreekLetter extends StringType {}

new GreekLetter('alpha');  // OK
new GreekLetter('alpha1'); // throws StrongTypeException
```

### `Alphanumeric`

`ctype_alnum`.

**Constructor:** `Alphanumeric()`

```php
use StrongType\Constraint\Alphanumeric;
use StrongType\String\StringType;

#[Alphanumeric]
readonly class TrackingId extends StringType {}

new TrackingId('AB12CD');  // OK
new TrackingId('AB-12');   // throws StrongTypeException
```

### `NumericDigits`

`ctype_digit` — ASCII digits only.

**Constructor:** `NumericDigits()`

```php
use StrongType\Constraint\NumericDigits;
use StrongType\String\StringType;

#[NumericDigits]
readonly class ZipCode extends StringType {}

new ZipCode('94110');  // OK
new ZipCode('941-10'); // throws StrongTypeException
```

### `HexDigits`

`ctype_xdigit` — accepts `0-9a-f` and `0-9A-F`.

**Constructor:** `HexDigits()`

```php
use StrongType\Constraint\HexDigits;
use StrongType\String\StringType;

#[HexDigits]
readonly class GitShortSha extends StringType {}

new GitShortSha('a1b2c3d');  // OK
new GitShortSha('xyz');      // throws StrongTypeException
```

### `Lowercase`

`ctype_lower`.

**Constructor:** `Lowercase()`

```php
use StrongType\Constraint\Lowercase;
use StrongType\String\StringType;

#[Lowercase]
readonly class LowercaseTag extends StringType {}

new LowercaseTag('php');  // OK
new LowercaseTag('PHP');  // throws StrongTypeException
```

### `Uppercase`

`ctype_upper`.

**Constructor:** `Uppercase()`

```php
use StrongType\Constraint\Uppercase;
use StrongType\String\StringType;

#[Uppercase]
readonly class StateAbbreviation extends StringType {}

new StateAbbreviation('CA');  // OK
new StateAbbreviation('Ca');  // throws StrongTypeException
```

### `Base64`

Canonical, padded standard Base64 (RFC 4648 §4). Length must be a multiple of 4 with 0–2 trailing `=` pads; the value must round-trip through `base64_decode($v, strict: true)`. Base64URL alphabet (`-`, `_`) is rejected.

**Constructor:** `Base64()`

```php
use StrongType\Constraint\Base64;
use StrongType\String\StringType;

#[Base64]
readonly class ImageBlob extends StringType {}

new ImageBlob('SGVsbG8=');  // OK
new ImageBlob('SGVsbG8');   // throws StrongTypeException — unpadded
```

### `Email`

`filter_var($value, FILTER_VALIDATE_EMAIL)`.

**Constructor:** `Email()`

```php
use StrongType\Constraint\Email;
use StrongType\String\StringType;

#[Email]
readonly class ContactEmail extends StringType {}

new ContactEmail('alice@example.com');  // OK
new ContactEmail('not-an-email');       // throws StrongTypeException
```

### `Url`

`filter_var($value, FILTER_VALIDATE_URL)`.

**Constructor:** `Url()`

```php
use StrongType\Constraint\Url;
use StrongType\String\StringType;

#[Url]
readonly class WebhookEndpoint extends StringType {}

new WebhookEndpoint('https://example.com/hook');  // OK
new WebhookEndpoint('not a url');                 // throws StrongTypeException
```

### `IpAddress`

Either IPv4 or IPv6 (`FILTER_VALIDATE_IP`).

**Constructor:** `IpAddress()`

```php
use StrongType\Constraint\IpAddress;
use StrongType\String\StringType;

#[IpAddress]
readonly class ClientIp extends StringType {}

new ClientIp('192.0.2.1');  // OK
new ClientIp('::1');        // OK
new ClientIp('256.0.0.1');  // throws StrongTypeException
```

### `Ipv4`

IPv4 only (`FILTER_FLAG_IPV4`).

**Constructor:** `Ipv4()`

```php
use StrongType\Constraint\Ipv4;
use StrongType\String\StringType;

#[Ipv4]
readonly class V4Only extends StringType {}

new V4Only('10.0.0.1');  // OK
new V4Only('::1');       // throws StrongTypeException
```

### `Ipv6`

IPv6 only (`FILTER_FLAG_IPV6`).

**Constructor:** `Ipv6()`

```php
use StrongType\Constraint\Ipv6;
use StrongType\String\StringType;

#[Ipv6]
readonly class V6Only extends StringType {}

new V6Only('2001:db8::1');  // OK
new V6Only('192.0.2.1');    // throws StrongTypeException
```

### `Cidr`

IPv4 or IPv6 CIDR notation (`ip/prefix`).

**Constructor:** `Cidr()`

```php
use StrongType\Constraint\Cidr;
use StrongType\String\StringType;

#[Cidr]
readonly class AllowedSubnet extends StringType {}

new AllowedSubnet('10.0.0.0/8');     // OK
new AllowedSubnet('2001:db8::/32');  // OK
new AllowedSubnet('10.0.0.0/33');    // throws StrongTypeException
```

### `Uuid`

UUID shape `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` (format only).

**Constructor:** `Uuid()`

```php
use StrongType\Constraint\Uuid;
use StrongType\String\StringType;

#[Uuid]
readonly class ResourceId extends StringType {}

new ResourceId('550e8400-e29b-41d4-a716-446655440000');  // OK
new ResourceId('not-a-uuid');                            // throws StrongTypeException
```

### `Json`

`json_validate()`.

**Constructor:** `Json()`

```php
use StrongType\Constraint\Json;
use StrongType\String\StringType;

#[Json]
readonly class JsonBlob extends StringType {}

new JsonBlob('{"a":1}');  // OK
new JsonBlob('{a:1}');    // throws StrongTypeException
```

### `Rfc3339`

Strict RFC 3339 date-time grammar plus calendar round-trip. `:60` accepted syntactically (leap-second tolerance).

**Constructor:** `Rfc3339()`

```php
use StrongType\Constraint\Rfc3339;
use StrongType\String\StringType;

#[Rfc3339]
readonly class IsoTimestamp extends StringType {}

new IsoTimestamp('2024-01-15T10:30:00Z');     // OK
new IsoTimestamp('2024-02-30T00:00:00Z');     // throws StrongTypeException — invalid date
```

### `ClassExists`

`class_exists || interface_exists || enum_exists`.

**Constructor:** `ClassExists()`

```php
use StrongType\Constraint\ClassExists;
use StrongType\String\StringType;

#[ClassExists]
readonly class HandlerClassName extends StringType {}

new HandlerClassName(\DateTimeImmutable::class);  // OK
new HandlerClassName('App\\NoSuchClass');         // throws StrongTypeException
```

### `DateTimeParseable`

`new \DateTimeImmutable($value)` succeeds.

**Constructor:** `DateTimeParseable()`

```php
use StrongType\Constraint\DateTimeParseable;
use StrongType\String\StringType;

#[DateTimeParseable]
readonly class FlexibleDate extends StringType {}

new FlexibleDate('2024-01-15');     // OK
new FlexibleDate('next Tuesday');   // OK — relative formats accepted
new FlexibleDate('not-a-date');     // throws StrongTypeException
```

---

## Array

These attributes apply to `ArrayType` subclasses. `MinCount` / `MaxCount` / `ExactCount` count via `count()`. `ElementType` and `Unique` validate **direct children only** — see [shallow array validation](semantics.md#shallow-array-validation).

| Attribute | Parameters | Description |
| --- | --- | --- |
| [`Nonempty`](#nonempty) | none | `count > 0` (shared with strings) |
| [`IsEmpty`](#isempty) | none | `count === 0` |
| [`MinCount`](#mincount) | `int $minCount` | `count >= $minCount` |
| [`MaxCount`](#maxcount) | `int $maxCount` | `count <= $maxCount` |
| [`ExactCount`](#exactcount) | `int $count` | `count === $count` |
| [`Unique`](#unique) | none | No duplicate values |
| [`ElementType`](#elementtype) | `string $type` | Each element matches type |
| [`IsList`](#islist) | none | `array_is_list` |
| [`IsAssociative`](#isassociative) | none | `!array_is_list` (rejects `[]`) |

### `IsEmpty`

Array must be empty (`count === 0`).

**Constructor:** `IsEmpty()`

```php
use StrongType\Constraint\IsEmpty;
use StrongType\Arrays\ArrayType;

#[IsEmpty]
class MustBeEmpty extends ArrayType {}

new MustBeEmpty([]);    // OK
new MustBeEmpty([1]);   // throws StrongTypeException
```

### `MinCount`

Minimum element count.

**Constructor:** `MinCount(int $minCount)`

**Constructor invariants:** negative count raises `\LogicException`.

```php
use StrongType\Constraint\{MinCount, ElementType};
use StrongType\Arrays\ArrayType;

#[MinCount(2), ElementType('string')]
class Cosigners extends ArrayType {}

new Cosigners(['alice', 'bob']);   // OK
new Cosigners(['alice']);          // throws StrongTypeException
```

### `MaxCount`

Maximum element count.

**Constructor:** `MaxCount(int $maxCount)`

**Constructor invariants:** negative count raises `\LogicException`.

```php
use StrongType\Constraint\{MaxCount, ElementType};
use StrongType\Arrays\ArrayType;

#[MaxCount(50), ElementType('string')]
class FeatureFlags extends ArrayType {}

new FeatureFlags(['flag1', 'flag2']);                            // OK
new FeatureFlags(array_map(fn($i) => "flag$i", range(1, 51)));   // throws StrongTypeException
```

### `ExactCount`

Element count must equal exactly `$count`.

**Constructor:** `ExactCount(int $count)`

**Constructor invariants:** negative count raises `\LogicException`.

```php
use StrongType\Constraint\{ExactCount, ElementType};
use StrongType\Arrays\ArrayType;

// RGB color: exactly 3 channels.
#[ExactCount(3), ElementType('int')]
class RgbChannels extends ArrayType {}

new RgbChannels([255, 128, 0]);   // OK
new RgbChannels([255, 128]);      // throws StrongTypeException
```

### `Unique`

No duplicate values at the top level. Use strict comparison.

**Constructor:** `Unique()`

```php
use StrongType\Constraint\{Unique, Nonempty, ElementType};
use StrongType\Arrays\ArrayType;

#[Nonempty, Unique, ElementType('int')]
class UserIds extends ArrayType {}

new UserIds([1, 2, 3]);   // OK
new UserIds([1, 1, 2]);   // throws StrongTypeException
```

### `ElementType`

Every element must match the given type. Accepts a builtin (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) or any class/interface name for `instanceof` checks.

**Constructor:** `ElementType(string $type)`

**Constructor invariants:** anything other than the builtin set or an existing class/interface raises `\LogicException`. PHP type aliases (`integer`, `boolean`, `double`) are explicitly rejected — use the canonical name.

```php
use StrongType\Constraint\{ElementType, Nonempty};
use StrongType\Arrays\ArrayType;

#[Nonempty, ElementType(\DateTimeImmutable::class)]
class EventDates extends ArrayType {}

new EventDates([new \DateTimeImmutable('2024-01-15')]);  // OK
new EventDates(['2024-01-15']);                          // throws StrongTypeException
```

### `IsList`

`array_is_list` — sequential integer keys starting at 0. Empty arrays count as a list.

**Constructor:** `IsList()`

```php
use StrongType\Constraint\IsList;
use StrongType\Arrays\ArrayType;

#[IsList]
class OrderedSteps extends ArrayType {}

new OrderedSteps(['init', 'fetch', 'render']);  // OK
new OrderedSteps(['a' => 1]);                   // throws StrongTypeException
```

### `IsAssociative`

`!array_is_list` — must have at least one non-sequential key. Note that `array_is_list([])` is `true`, so `[]` is rejected.

**Constructor:** `IsAssociative()`

```php
use StrongType\Constraint\IsAssociative;
use StrongType\Arrays\ArrayType;

#[IsAssociative]
class Headers extends ArrayType {}

new Headers(['Content-Type' => 'application/json']);  // OK
new Headers([]);                                      // throws StrongTypeException
new Headers(['a', 'b', 'c']);                         // throws StrongTypeException
```

---

## DateTime

These attributes apply to `Integer` (for timestamps) or `StringType` subclasses (for parseable date/time strings).

| Attribute | Parameters | Description |
| --- | --- | --- |
| [`DateFormat`](#dateformat) | `string $format` | Validates against a date format with roundtrip check |
| [`TimeFormat`](#timeformat) | none | `HH:MM:SS` pattern |
| [`InFuture`](#infuture) | none | `> time()` (for integer timestamps) |
| [`InPast`](#inpast) | none | `< time()` (for integer timestamps) |

### `DateFormat`

Validates a string against a `DateTimeImmutable::createFromFormat()` format with a round-trip check (the parsed date must serialize back to the same string).

**Constructor:** `DateFormat(string $format)`

**Constructor invariants:** empty format string raises `\LogicException`.

```php
use StrongType\Constraint\DateFormat;
use StrongType\String\StringType;

#[DateFormat('Y/m/d')]
readonly class SlashDate extends StringType {}

new SlashDate('2024/01/15');  // OK
new SlashDate('2024-01-15');  // throws StrongTypeException
```

### `TimeFormat`

`HH:MM:SS` pattern.

**Constructor:** `TimeFormat()`

```php
use StrongType\Constraint\TimeFormat;
use StrongType\String\StringType;

#[TimeFormat]
readonly class ScheduledTime extends StringType {}

new ScheduledTime('14:30:00');  // OK
new ScheduledTime('14:30');     // throws StrongTypeException
```

### `InFuture`

Integer timestamp strictly greater than `time()` at validation. Compares against externally-mutable state.

**Constructor:** `InFuture()`

```php
use StrongType\Constraint\InFuture;
use StrongType\Int\Integer;

#[InFuture]
readonly class ExpiryTimestamp extends Integer {}

new ExpiryTimestamp(time() + 3600);  // OK
new ExpiryTimestamp(time() - 1);     // throws StrongTypeException
```

### `InPast`

Integer timestamp strictly less than `time()` at validation.

**Constructor:** `InPast()`

```php
use StrongType\Constraint\InPast;
use StrongType\Int\Integer;

#[InPast]
readonly class CreatedAtTimestamp extends Integer {}

new CreatedAtTimestamp(time() - 60);   // OK
new CreatedAtTimestamp(time() + 60);   // throws StrongTypeException
```

---

## Enum

| Attribute | Parameters | Description |
| --- | --- | --- |
| [`InEnum`](#inenum) | `class-string $enumClass` | Value matches a backing value (for `BackedEnum`) or a case name (for pure `UnitEnum`) |

### `InEnum`

Constrains a strong type's value to the enum's backing values (for `BackedEnum`) or case names (for pure `UnitEnum`). Works on `Integer` (for `int`-backed enums) and `StringType` (for `string`-backed or pure enums). No `EnumString` / `EnumInt` base class is needed.

**Constructor:** `InEnum(class-string $enumClass)`

**Constructor invariants:** anything other than an existing enum class — non-existent class, interface, or non-enum class — raises `\LogicException`.

```php
use StrongType\Constraint\InEnum;
use StrongType\String\StringType;

enum Status: string {
    case Active   = 'active';
    case Inactive = 'inactive';
}

#[InEnum(Status::class)]
readonly class StatusCode extends StringType {}

new StatusCode('active');    // OK
new StatusCode('archived');  // throws StrongTypeException
```

For full coverage of int-backed and pure enums, see [wrapping enums](defining-types.md#wrapping-enums-with-inenum).

---

## Combinators

Combinators wrap other constraints. Children are passed as constructor arguments using `new`, which PHP allows in attribute argument expressions. Combinators implement `ConstraintInterface` themselves, so they nest freely and compose with non-combinator attributes. **Effective priority is `min(children)`** — the combinator runs alongside its lowest-band child rather than at a fixed point.

| Attribute | Parameters | Description |
| --- | --- | --- |
| [`AnyOf`](#anyof) | `ConstraintInterface ...$children` | Passes if at least one child passes |
| [`AllOf`](#allof) | `ConstraintInterface ...$children` | Passes only if every child passes; first failure wins (verbatim) |
| [`Not`](#not) | `ConstraintInterface $child` | Inverts a single child constraint |

### `AnyOf`

At least one child must pass. When every child fails, emits a composite message: `"{Type} type must satisfy one of: [child1msg | child2msg | …]"`.

**Constructor:** `AnyOf(ConstraintInterface ...$children)`

**Constructor invariants:** zero children raises `\LogicException`.

```php
use StrongType\Constraint\{AnyOf, InRange};
use StrongType\Int\Integer;

// Well-known (1–1023) or IANA dynamic/private (49152–65535).
#[AnyOf(new InRange(1, 1023), new InRange(49152, 65535))]
readonly class WellKnownOrEphemeralPort extends Integer {}

new WellKnownOrEphemeralPort(80);     // OK
new WellKnownOrEphemeralPort(50000);  // OK
new WellKnownOrEphemeralPort(8080);   // throws StrongTypeException
```

### `AllOf`

Every child must pass. **Children run in argument order**, not priority order — `AllOf` does not sort. Use it when you need a single groupable unit you can nest inside `AnyOf` or `Not`, not as a drop-in replacement for stacked attributes (which do sort by priority). The first failing child's message is returned verbatim.

**Constructor:** `AllOf(ConstraintInterface ...$children)`

**Constructor invariants:** zero children raises `\LogicException`.

```php
use StrongType\Constraint\{AllOf, MinLength, Pattern};
use StrongType\String\StringType;

#[AllOf(new MinLength(2), new Pattern('/^[A-Z_][A-Z0-9_]*$/'))]
readonly class EnvVarName extends StringType {}

new EnvVarName('LOG_LEVEL');  // OK
new EnvVarName('a');          // throws StrongTypeException
```

### `Not`

Inverts a single child. The error message names the inverted constraint by class (`Pattern`, `Email`, etc.) but does not describe the inverted condition in detail.

**Constructor:** `Not(ConstraintInterface $child)`

```php
use StrongType\Constraint\{Not, Pattern};
use StrongType\String\StringType;

// Reject all-uppercase words (likely shouting in user-generated content).
#[Not(new Pattern('/^[A-Z]+$/'))]
readonly class NoAllCapsString extends StringType {}

new NoAllCapsString('Hello');  // OK
new NoAllCapsString('HELLO');  // throws StrongTypeException
```

---

## Priority bands

Constraints execute in priority order (lower runs first). The bands below sort attribute execution within a single class and across the inheritance chain.

| Priority | Category | Attributes |
| --- | --- | --- |
| 40 | Domain default | `Finite` (auto-applied to every `FloatingPoint` subtype) |
| 50 | Range / size | `Min`, `Max`, `InRange`, `Positive`, `Negative`, `Nonnegative`, `Nonpositive`, `Nonzero`, `Even`, `Odd`, `DivisibleBy`, `Nonempty`, `MinLength`, `MaxLength`, `MinCount`, `MaxCount`, `ExactCount`, `IsEmpty`, `IsList`, `IsAssociative` |
| 60 | Content | `Nonblank`, `InList`, `InEnum` |
| 100 | Format | `Pattern`, `NotPattern`, `Alpha`, `Alphanumeric`, `Lowercase`, `Uppercase`, `NumericDigits`, `HexDigits`, `Base64`, `Unique`, `ElementType`, `DateFormat`, `TimeFormat`, `InFuture`, `InPast` |
| 150 | Semantic | `Email`, `Url`, `IpAddress`, `Ipv4`, `Ipv6`, `Cidr`, `Uuid`, `Json`, `Rfc3339`, `ClassExists`, `DateTimeParseable` |
| 200+ | Custom | User-defined constraints |
| min(children) | Combinator | `AnyOf`, `AllOf`, `Not` |

The bands are not arbitrary: a `Pattern` complaint about an empty string is rarely useful when `Nonempty` already caught it, and a `Json` complaint about a malformed string is rarely useful when `MaxLength` already caught it. Running cheap structural checks before expensive format/semantic checks also keeps the failure path fast.

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
