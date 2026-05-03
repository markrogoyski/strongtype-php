# Built-in Types

Back to [README](../README.md) · [Documentation index](README.md)

This is the full catalog of concrete value types shipped with the library, organised by category. Each section opens with a quick-reference table and follows with one subsection per type — a one-line description, a successful construction, a failing construction, and (where relevant) a serialization or `tryFrom` example. Failure messages on per-type entries are abbreviated to `// throws StrongTypeException`; refer to the [README](../README.md#error-handling) and [getting-started.md](getting-started.md#error-handling) for verbatim message format.

The standard-library content covers six categories:

- [Integers](#integers) — 11 types
- [Floats](#floats) — 9 types
- [Strings](#strings) — 33 types
- [Arrays](#arrays) — 15 types
- [Booleans](#booleans) — 2 types
- [DateTime](#datetime) — 5 types

A handful of types use [manual constructors](architecture.md#why-some-types-keep-manual-constructors) rather than constraint attributes. Those entries are marked with a brief note where they appear.

---

## Integers

All integer types extend `StrongType\Int\Integer`. Use them as parameter and return type hints anywhere your domain expects a constrained whole number.

| Type | Constraint | Details |
| --- | --- | --- |
| [`PositiveInt`](#positiveint) | `> 0` | Strictly positive |
| [`NegativeInt`](#negativeint) | `< 0` | Strictly negative |
| [`NonnegativeInt`](#nonnegativeint) | `>= 0` | Zero or positive |
| [`NonpositiveInt`](#nonpositiveint) | `<= 0` | Zero or negative |
| [`NonzeroInt`](#nonzeroint) | `!= 0` | Any nonzero integer |
| [`EvenInt`](#evenint) | `% 2 === 0` | Even integers |
| [`OddInt`](#oddint) | `% 2 !== 0` | Odd integers |
| [`ByteInt`](#byteint) | `0..255` | Unsigned byte range |
| [`PercentInt`](#percentint) | `0..100` | Percentage range |
| [`PortNumber`](#portnumber) | `1..65535` | Valid port number |
| [`HttpStatusCode`](#httpstatuscode) | `100..599` | Valid HTTP status code range |

### `PositiveInt`

Strictly positive integer (`> 0`). Representative type — verbatim error message shown.

```php
use StrongType\Int\PositiveInt;

new PositiveInt(25);              // OK
new PositiveInt(-1);              // StrongTypeException: "PositiveInt type must be > 0, got -1"
new PositiveInt(0);               // throws StrongTypeException

PositiveInt::tryFrom(25);         // PositiveInt(25)
PositiveInt::tryFrom(0);          // null

(string) new PositiveInt(25);     // "25"
json_encode(new PositiveInt(25)); // "25"
```

### `NegativeInt`

Strictly negative integer (`< 0`).

```php
use StrongType\Int\NegativeInt;

new NegativeInt(-5);  // OK
new NegativeInt(0);   // throws StrongTypeException
```

### `NonnegativeInt`

Zero or positive (`>= 0`).

```php
use StrongType\Int\NonnegativeInt;

new NonnegativeInt(0);    // OK
new NonnegativeInt(42);   // OK
new NonnegativeInt(-1);   // throws StrongTypeException
```

### `NonpositiveInt`

Zero or negative (`<= 0`).

```php
use StrongType\Int\NonpositiveInt;

new NonpositiveInt(0);   // OK
new NonpositiveInt(-7);  // OK
new NonpositiveInt(1);   // throws StrongTypeException
```

### `NonzeroInt`

Any integer except zero (`!= 0`).

```php
use StrongType\Int\NonzeroInt;

new NonzeroInt(7);   // OK
new NonzeroInt(-7);  // OK
new NonzeroInt(0);   // throws StrongTypeException
```

### `EvenInt`

Even integer (`% 2 === 0`). `0` is even.

```php
use StrongType\Int\EvenInt;

new EvenInt(0);   // OK
new EvenInt(42);  // OK
new EvenInt(7);   // throws StrongTypeException
```

### `OddInt`

Odd integer (`% 2 !== 0`).

```php
use StrongType\Int\OddInt;

new OddInt(1);   // OK
new OddInt(-7);  // OK
new OddInt(0);   // throws StrongTypeException
```

### `ByteInt`

Unsigned byte (`0..255`). Useful for image channels, raw byte buffers, and protocol fields.

```php
use StrongType\Int\ByteInt;

new ByteInt(0);    // OK
new ByteInt(255);  // OK
new ByteInt(256);  // throws StrongTypeException
new ByteInt(-1);   // throws StrongTypeException
```

### `PercentInt`

Integer percentage (`0..100`).

```php
use StrongType\Int\PercentInt;

new PercentInt(0);    // OK
new PercentInt(100);  // OK
new PercentInt(101);  // throws StrongTypeException
```

### `PortNumber`

Valid TCP/UDP port (`1..65535`). Representative type — verbatim error message shown.

```php
use StrongType\Int\PortNumber;

new PortNumber(80);    // OK
new PortNumber(65535); // OK
new PortNumber(0);     // StrongTypeException: "PortNumber type must be >= 1, got 0"
new PortNumber(70000); // throws StrongTypeException
```

### `HttpStatusCode`

Valid HTTP status code range (`100..599`). Format-only — does not enforce that the specific code is registered.

```php
use StrongType\Int\HttpStatusCode;

new HttpStatusCode(200);  // OK
new HttpStatusCode(404);  // OK
new HttpStatusCode(99);   // throws StrongTypeException
new HttpStatusCode(600);  // throws StrongTypeException
```

---

## Floats

All float types extend `StrongType\Float\FloatingPoint`. Float subtypes reject `INF`, `-INF`, and `NAN` by default — the base class carries `#[Finite]` at priority 40, which runs before any range check. There is no opt-out when extending `FloatingPoint`; if you need non-finite floats, define a custom base type.

`FloatingPoint::tryFrom` accepts `int` and widens to `float`, mirroring PHP's native int-to-float parameter widening. Every other base class is strict.

| Type | Constraint | Details |
| --- | --- | --- |
| [`PositiveFloat`](#positivefloat) | `> 0` | Strictly positive |
| [`NegativeFloat`](#negativefloat) | `< 0` | Strictly negative |
| [`NonnegativeFloat`](#nonnegativefloat) | `>= 0` | Zero or positive |
| [`NonpositiveFloat`](#nonpositivefloat) | `<= 0` | Zero or negative |
| [`NonzeroFloat`](#nonzerofloat) | `!= 0` | Any nonzero float |
| [`UnitFloat`](#unitfloat) | `0.0..1.0` | Unit interval |
| [`PercentFloat`](#percentfloat) | `0.0..100.0` | Percentage range |
| [`Latitude`](#latitude) | `-90.0..90.0` | Geographic latitude |
| [`Longitude`](#longitude) | `-180.0..180.0` | Geographic longitude |

### `PositiveFloat`

Strictly positive float (`> 0`), finite.

```php
use StrongType\Float\PositiveFloat;

new PositiveFloat(0.5);  // OK
new PositiveFloat(0.0);  // throws StrongTypeException
new PositiveFloat(INF);  // throws StrongTypeException (Finite runs first)

PositiveFloat::tryFrom(2);   // PositiveFloat(2.0) — int widened to float
PositiveFloat::tryFrom(-1);  // null
```

### `NegativeFloat`

Strictly negative float (`< 0`), finite.

```php
use StrongType\Float\NegativeFloat;

new NegativeFloat(-0.1);  // OK
new NegativeFloat(0.0);   // throws StrongTypeException
```

### `NonnegativeFloat`

Zero or positive (`>= 0`), finite.

```php
use StrongType\Float\NonnegativeFloat;

new NonnegativeFloat(0.0);  // OK
new NonnegativeFloat(3.14); // OK
new NonnegativeFloat(-0.1); // throws StrongTypeException
```

### `NonpositiveFloat`

Zero or negative (`<= 0`), finite.

```php
use StrongType\Float\NonpositiveFloat;

new NonpositiveFloat(0.0);  // OK
new NonpositiveFloat(-1.5); // OK
new NonpositiveFloat(0.5);  // throws StrongTypeException
```

### `NonzeroFloat`

Any nonzero float (`!= 0`), finite.

```php
use StrongType\Float\NonzeroFloat;

new NonzeroFloat(0.5);   // OK
new NonzeroFloat(-0.5);  // OK
new NonzeroFloat(0.0);   // throws StrongTypeException
new NonzeroFloat(NAN);   // throws StrongTypeException
```

### `UnitFloat`

Unit interval (`0.0..1.0` inclusive). Useful for probabilities, normalized scores, mix ratios.

```php
use StrongType\Float\UnitFloat;

new UnitFloat(0.0);  // OK
new UnitFloat(0.5);  // OK
new UnitFloat(1.0);  // OK
new UnitFloat(1.5);  // throws StrongTypeException
```

### `PercentFloat`

Percentage range (`0.0..100.0` inclusive).

```php
use StrongType\Float\PercentFloat;

new PercentFloat(0.0);    // OK
new PercentFloat(99.99);  // OK
new PercentFloat(100.1);  // throws StrongTypeException
```

### `Latitude`

Geographic latitude in decimal degrees (`-90.0..90.0`).

```php
use StrongType\Float\Latitude;

new Latitude(0.0);     // OK (equator)
new Latitude(40.7128); // OK (NYC)
new Latitude(-90.0);   // OK (south pole)
new Latitude(91.0);    // throws StrongTypeException
```

### `Longitude`

Geographic longitude in decimal degrees (`-180.0..180.0`).

```php
use StrongType\Float\Longitude;

new Longitude(0.0);      // OK (prime meridian)
new Longitude(-74.0060); // OK (NYC)
new Longitude(180.0);    // OK
new Longitude(-180.0);   // OK
new Longitude(180.1);    // throws StrongTypeException
```

---

## Strings

All string types extend `StrongType\String\StringType`. `MinLength` and `MaxLength` count **bytes**, not graphemes — see [byte length vs. Unicode length](semantics.md#byte-length-vs-unicode-length).

| Type | Constraint | Details |
| --- | --- | --- |
| [`NonemptyString`](#nonemptystring) | `strlen > 0` | Not empty |
| [`NonblankString`](#nonblankstring) | `trim() !== ''` | Not empty or whitespace-only |
| [`AlphaString`](#alphastring) | `ctype_alpha` | Alphabetic characters only |
| [`UppercaseAlphaString`](#uppercasealphastring) | `ctype_upper` | Uppercase alphabetic only |
| [`LowercaseAlphaString`](#lowercasealphastring) | `ctype_lower` | Lowercase alphabetic only |
| [`AlphanumericString`](#alphanumericstring) | `ctype_alnum` | Alphanumeric characters only |
| [`NumericString`](#numericstring) | `ctype_digit` | Numeric digits only |
| [`BinaryString`](#binarystring) | `[01]+` | Binary digit string |
| [`HexString`](#hexstring) | `ctype_xdigit` | Hexadecimal digits only |
| [`Base64String`](#base64string) | Canonical Base64 (RFC 4648 §4) | Padded, standard alphabet only; Base64URL rejected |
| [`JsonString`](#jsonstring) | `json_validate` | Valid JSON |
| [`EmailString`](#emailstring) | `FILTER_VALIDATE_EMAIL` | Valid email address |
| [`UrlString`](#urlstring) | `FILTER_VALIDATE_URL` | Valid URL |
| [`IpAddressString`](#ipaddressstring) | `FILTER_VALIDATE_IP` | Valid IP address (v4 or v6) |
| [`Ipv4AddressString`](#ipv4addressstring) | IPv4 filter | Valid IPv4 address only |
| [`Ipv6AddressString`](#ipv6addressstring) | IPv6 filter | Valid IPv6 address only |
| [`CidrString`](#cidrstring) | CIDR notation | `ip/prefix` with prefix in valid range |
| [`MacAddressString`](#macaddressstring) | MAC pattern | `xx:xx:xx:xx:xx:xx` |
| [`UuidString`](#uuidstring) | UUID pattern | `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` |
| [`JwtString`](#jwtstring) | JWT pattern | `header.payload.signature` base64url segments |
| [`PhoneE164String`](#phonee164string) | E.164 pattern | `+` followed by 2–15 digits |
| [`CountryCodeAlpha2String`](#countrycodealpha2string) | `[A-Z]{2}` | ISO 3166-1 alpha-2 format (format only) |
| [`CountryCodeAlpha3String`](#countrycodealpha3string) | `[A-Z]{3}` | ISO 3166-1 alpha-3 format (format only) |
| [`CurrencyCodeString`](#currencycodestring) | `[A-Z]{3}` | ISO 4217 format (format only) |
| [`LanguageCodeString`](#languagecodestring) | `[a-z]{2}` | ISO 639-1 format (format only) |
| [`MimeTypeString`](#mimetypestring) | RFC 6838 | `type/subtype` restricted-name format |
| [`SlugString`](#slugstring) | Slug pattern | `lowercase-words-with-dashes` |
| [`SemverString`](#semverstring) | SemVer 2.0.0 | `MAJOR.MINOR.PATCH[-prerelease][+build]` |
| [`HexColorString`](#hexcolorstring) | Hex color pattern | `#RGB` or `#RRGGBB` |
| [`ClassString`](#classstring) | `class_exists` | Existing class, interface, or enum |
| [`DateTimeString`](#datetimestring) | `DateTimeImmutable` | Parseable date/time string |
| [`Rfc3339DateTimeString`](#rfc3339datetimestring) | RFC 3339 | RFC 3339 grammar; `:60` accepted (leap-second tolerance) |
| [`EmptyString`](#emptystring) | `strlen === 0` | Must be empty (default `''`) |

Codes from external registries (`CountryCode*`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString`) validate **format only** and do not check active-registry membership. See [format vs. registry validation](semantics.md#format-vs-registry-validation).

### `NonemptyString`

Any non-empty string. Representative type — verbatim error message shown.

```php
use StrongType\String\NonemptyString;

new NonemptyString('hello');  // OK
new NonemptyString(' ');      // OK (whitespace is non-empty; use NonblankString to reject)
new NonemptyString('');       // StrongTypeException: "NonemptyString type must not be empty, got "
```

### `NonblankString`

Non-empty *and* not whitespace-only (`trim() !== ''`).

```php
use StrongType\String\NonblankString;

new NonblankString('hi');     // OK
new NonblankString('   ');    // throws StrongTypeException
new NonblankString("\t\n");   // throws StrongTypeException
```

### `AlphaString`

Alphabetic characters only (`ctype_alpha`).

```php
use StrongType\String\AlphaString;

new AlphaString('Hello');   // OK
new AlphaString('Hello1');  // throws StrongTypeException
```

### `UppercaseAlphaString`

Uppercase alphabetic only (`ctype_upper`).

```php
use StrongType\String\UppercaseAlphaString;

new UppercaseAlphaString('USA');  // OK
new UppercaseAlphaString('USa');  // throws StrongTypeException
```

### `LowercaseAlphaString`

Lowercase alphabetic only (`ctype_lower`).

```php
use StrongType\String\LowercaseAlphaString;

new LowercaseAlphaString('hello');  // OK
new LowercaseAlphaString('Hello');  // throws StrongTypeException
```

### `AlphanumericString`

Letters and digits only (`ctype_alnum`).

```php
use StrongType\String\AlphanumericString;

new AlphanumericString('abc123');  // OK
new AlphanumericString('abc-123'); // throws StrongTypeException
```

### `NumericString`

ASCII digits only (`ctype_digit`). Rejects signs, decimals, and leading whitespace.

```php
use StrongType\String\NumericString;

new NumericString('12345');   // OK
new NumericString('+123');    // throws StrongTypeException
new NumericString('1.5');     // throws StrongTypeException
```

### `BinaryString`

A non-empty string of `0` and `1` characters.

```php
use StrongType\String\BinaryString;

new BinaryString('1010');  // OK
new BinaryString('102');   // throws StrongTypeException
new BinaryString('');      // throws StrongTypeException
```

### `HexString`

Hexadecimal digit string (`ctype_xdigit`). Accepts both `0-9a-f` and `0-9A-F`.

```php
use StrongType\String\HexString;

new HexString('deadBEEF');  // OK
new HexString('xyz');       // throws StrongTypeException
```

### `Base64String`

Canonical, padded standard Base64 (RFC 4648 §4). Length must be a multiple of 4 with 0–2 trailing `=` pad characters; the value must round-trip through `base64_decode($v, strict: true)`. Base64URL alphabet (`-`, `_`) is rejected.

```php
use StrongType\String\Base64String;

new Base64String('SGVsbG8=');     // OK ("Hello")
new Base64String('SGVsbG8');      // throws StrongTypeException — unpadded
new Base64String('SGVs-G8=');     // throws StrongTypeException — Base64URL alphabet
```

### `JsonString`

A string that `json_validate()` accepts.

```php
use StrongType\String\JsonString;

new JsonString('{"a":1}');  // OK
new JsonString('[1,2,3]');  // OK
new JsonString('null');     // OK
new JsonString('{a:1}');    // throws StrongTypeException
```

### `EmailString`

A string accepted by `filter_var($value, FILTER_VALIDATE_EMAIL)`. Representative type — verbatim error message shown.

```php
use StrongType\String\EmailString;

new EmailString('alice@example.com');  // OK
new EmailString('not-an-email');       // StrongTypeException: "EmailString type must be a valid email address, got not-an-email"
```

### `UrlString`

A string accepted by `filter_var($value, FILTER_VALIDATE_URL)`.

```php
use StrongType\String\UrlString;

new UrlString('https://example.com/path?q=1');  // OK
new UrlString('not a url');                     // throws StrongTypeException
```

### `IpAddressString`

Either IPv4 or IPv6 (`FILTER_VALIDATE_IP`).

```php
use StrongType\String\IpAddressString;

new IpAddressString('192.0.2.1');  // OK
new IpAddressString('::1');        // OK
new IpAddressString('256.0.0.1');  // throws StrongTypeException
```

### `Ipv4AddressString`

IPv4 only.

```php
use StrongType\String\Ipv4AddressString;

new Ipv4AddressString('192.0.2.1');  // OK
new Ipv4AddressString('::1');        // throws StrongTypeException
```

### `Ipv6AddressString`

IPv6 only.

```php
use StrongType\String\Ipv6AddressString;

new Ipv6AddressString('2001:db8::1');  // OK
new Ipv6AddressString('192.0.2.1');    // throws StrongTypeException
```

### `CidrString`

Valid IPv4 or IPv6 CIDR notation (`ip/prefix`).

```php
use StrongType\String\CidrString;

new CidrString('10.0.0.0/24');     // OK
new CidrString('2001:db8::/32');   // OK
new CidrString('10.0.0.0/33');     // throws StrongTypeException — prefix out of range
```

### `MacAddressString`

`xx:xx:xx:xx:xx:xx` MAC address format (case-insensitive hex).

```php
use StrongType\String\MacAddressString;

new MacAddressString('aa:bb:cc:dd:ee:ff');  // OK
new MacAddressString('aabbccddeeff');       // throws StrongTypeException
```

### `UuidString`

UUID shape `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`. Format-only — does not enforce a particular UUID version.

```php
use StrongType\String\UuidString;

new UuidString('550e8400-e29b-41d4-a716-446655440000');  // OK
new UuidString('not-a-uuid');                            // throws StrongTypeException
```

### `JwtString`

JWT shape `header.payload.signature` with three base64url segments.

```php
use StrongType\String\JwtString;

new JwtString('eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjMifQ.dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk');  // OK
new JwtString('not-a-jwt');           // throws StrongTypeException — needs three dot-separated segments
new JwtString('only.two');            // throws StrongTypeException — only two segments
new JwtString('header.pay load.sig'); // throws StrongTypeException — space is not a base64url character
```

### `PhoneE164String`

E.164 international phone number format (`+` followed by 2–15 digits).

```php
use StrongType\String\PhoneE164String;

new PhoneE164String('+14155552671');  // OK
new PhoneE164String('14155552671');   // throws StrongTypeException — missing +
new PhoneE164String('+1');            // throws StrongTypeException — too short
```

### `CountryCodeAlpha2String`

ISO 3166-1 alpha-2 country code format (`[A-Z]{2}`). **Format only** — does not check the active ISO registry. See [format vs. registry validation](semantics.md#format-vs-registry-validation).

```php
use StrongType\String\CountryCodeAlpha2String;

new CountryCodeAlpha2String('US');  // OK
new CountryCodeAlpha2String('ZZ');  // OK — unassigned but matches format
new CountryCodeAlpha2String('us');  // throws StrongTypeException — must be uppercase
```

### `CountryCodeAlpha3String`

ISO 3166-1 alpha-3 country code format (`[A-Z]{3}`). Format only.

```php
use StrongType\String\CountryCodeAlpha3String;

new CountryCodeAlpha3String('USA');  // OK
new CountryCodeAlpha3String('USAS'); // throws StrongTypeException
```

### `CurrencyCodeString`

ISO 4217 currency code format (`[A-Z]{3}`). Format only.

```php
use StrongType\String\CurrencyCodeString;

new CurrencyCodeString('USD');  // OK
new CurrencyCodeString('EUR');  // OK
new CurrencyCodeString('us');   // throws StrongTypeException
```

### `LanguageCodeString`

ISO 639-1 language code format (`[a-z]{2}`). Format only.

```php
use StrongType\String\LanguageCodeString;

new LanguageCodeString('en');  // OK
new LanguageCodeString('EN');  // throws StrongTypeException — must be lowercase
```

### `MimeTypeString`

`type/subtype` restricted-name format per RFC 6838. Format only — does not verify IANA registry membership.

```php
use StrongType\String\MimeTypeString;

new MimeTypeString('application/json');         // OK
new MimeTypeString('text/plain');               // OK
new MimeTypeString('text/');                    // throws StrongTypeException
new MimeTypeString('application/vnd.example.'); // throws StrongTypeException — trailing punctuation
```

### `SlugString`

Lowercase, dash-separated slug — `lowercase-words-with-dashes`. No leading/trailing/consecutive dashes.

```php
use StrongType\String\SlugString;

new SlugString('blog-post-2024');  // OK
new SlugString('My-Post');         // throws StrongTypeException — uppercase
new SlugString('-bad');            // throws StrongTypeException
```

### `SemverString`

Semantic Versioning 2.0.0 (`MAJOR.MINOR.PATCH[-prerelease][+build]`) per [semver.org](https://semver.org/). The pattern is anchored with `\A...\z` so trailing whitespace or newlines are rejected.

```php
use StrongType\String\SemverString;

new SemverString('1.2.3');               // OK
new SemverString('1.0.0-alpha-1');       // OK — hyphens allowed in pre-release
new SemverString('1.0.0+build.123');     // OK
new SemverString('1.0.0-01');            // throws StrongTypeException — leading-zero numeric pre-release
new SemverString('v1.2.3');              // throws StrongTypeException — leading "v"
```

### `HexColorString`

`#RGB` or `#RRGGBB` hex color.

```php
use StrongType\String\HexColorString;

new HexColorString('#fff');    // OK
new HexColorString('#aabbcc'); // OK
new HexColorString('aabbcc');  // throws StrongTypeException — missing #
new HexColorString('#abcd');   // throws StrongTypeException — wrong digit count
```

### `ClassString`

A string referring to an existing class, interface, or enum (`class_exists || interface_exists || enum_exists`).

```php
use StrongType\String\ClassString;

new ClassString(\DateTimeImmutable::class);  // OK
new ClassString('App\\NoSuchClass');         // throws StrongTypeException
```

### `DateTimeString`

Any string accepted by `new \DateTimeImmutable($value)` without throwing.

```php
use StrongType\String\DateTimeString;

new DateTimeString('2024-01-15 10:30:00');   // OK
new DateTimeString('next Tuesday');          // OK — PHP relative formats are accepted
new DateTimeString('not-a-date');            // throws StrongTypeException
```

### `Rfc3339DateTimeString`

Strict RFC 3339 date-time grammar with offset, plus a calendar round-trip check. `:60` is accepted syntactically (leap-second tolerance per the grammar) without verifying the minute is an actual IERS-inserted leap second.

```php
use StrongType\String\Rfc3339DateTimeString;

new Rfc3339DateTimeString('2024-01-15T10:30:00Z');         // OK
new Rfc3339DateTimeString('2024-01-15T10:30:00+02:00');    // OK
new Rfc3339DateTimeString('2024-01-15 10:30:00');          // throws StrongTypeException — missing T
new Rfc3339DateTimeString('2024-02-30T00:00:00Z');         // throws StrongTypeException — invalid date
```

### `EmptyString`

Must be the empty string `''`. Uses a manual constructor with a default parameter (`new EmptyString()` works) — see [why some types keep manual constructors](architecture.md#why-some-types-keep-manual-constructors).

```php
use StrongType\String\EmptyString;

new EmptyString();      // OK
new EmptyString('');    // OK
new EmptyString(' ');   // throws StrongTypeException
```

---

## Arrays

All array types extend `StrongType\Arrays\ArrayType`. `ArrayType` subclasses are **not** declared `readonly` — the base class uses a `protected(set)` property that can't appear inside a `readonly` class. The value remains immutable in practice.

`MinCount`, `MaxCount`, and `ExactCount` count elements via `count()` regardless of key type. `ElementType` and `Unique` validate **direct children only** — see [shallow array validation](semantics.md#shallow-array-validation).

| Type | Constraint | Details |
| --- | --- | --- |
| [`NonemptyArray`](#nonemptyarray) | `count > 0` | At least one element |
| [`EmptyArray`](#emptyarray) | `count === 0` | Must be empty |
| [`UniqueArray`](#uniquearray) | No duplicates | Nonempty with unique values |
| [`FixedSizeArray`](#fixedsizearray) | `count === $size` | Exact element count (runtime) |
| [`ListArray`](#listarray) | `array_is_list` | Sequential integer keys starting at 0 |
| [`AssociativeArray`](#associativearray) | Not a list | Nonempty with at least one non-list key |
| [`ArrayOfStrings`](#arrayofstrings) | Element type check | Nonempty, all strings |
| [`ArrayOfInts`](#arrayofints) | Element type check | Nonempty, all ints |
| [`ArrayOfFloats`](#arrayoffloats) | Element type check | Nonempty, all floats |
| [`ArrayOfBools`](#arrayofbools) | Element type check | Nonempty, all bools |
| [`ArrayOfObjects`](#arrayofobjects) | Element type check | Nonempty, all objects |
| [`ArrayOfCallables`](#arrayofcallables) | Element type check | Nonempty, all callables |
| [`ArrayOfResources`](#arrayofresources) | Element type check | Nonempty, all resources |
| [`ArrayOfIterables`](#arrayofiterables) | Element type check | Nonempty, all iterables |
| [`ArrayOfArrays`](#arrayofarrays) | Element type check | Nonempty, all arrays |

### `NonemptyArray`

Any non-empty array.

```php
use StrongType\Arrays\NonemptyArray;

$arr = new NonemptyArray([1, 'two', 3.0]);  // OK — heterogeneous values allowed
\count($arr);                               // 3
foreach ($arr as $v) { /* ... */ }

new NonemptyArray([]);                      // throws StrongTypeException
```

### `EmptyArray`

Must be exactly `[]`.

```php
use StrongType\Arrays\EmptyArray;

new EmptyArray([]);     // OK
new EmptyArray([1]);    // throws StrongTypeException
```

### `UniqueArray`

Non-empty array with no duplicate values (top-level only).

```php
use StrongType\Arrays\UniqueArray;

new UniqueArray([1, 2, 3]);     // OK
new UniqueArray([1, 1, 2]);     // throws StrongTypeException
new UniqueArray([]);            // throws StrongTypeException — must be non-empty
```

### `FixedSizeArray`

Exact element count chosen at construction time. Uses a manual constructor with a runtime `$size` parameter — see [why some types keep manual constructors](architecture.md#why-some-types-keep-manual-constructors). `FixedSizeArray::tryFrom` and `FixedSizeArray::nullable` throw `\LogicException` because the `$size` argument can't be supplied through the inherited factory; use `withValues()` on an existing instance instead.

```php
use StrongType\Arrays\FixedSizeArray;

$pair = new FixedSizeArray(['x', 'y'], 2);            // OK
$pair = $pair->withValues(['a', 'b']);                // OK — same size

new FixedSizeArray(['x'], 2);                         // throws StrongTypeException
FixedSizeArray::tryFrom(['x', 'y']);                  // \LogicException
```

### `ListArray`

`array_is_list` — sequential integer keys starting at 0. Empty arrays count as a list.

```php
use StrongType\Arrays\ListArray;

new ListArray([10, 20, 30]);             // OK
new ListArray([]);                       // OK — array_is_list([]) is true
new ListArray(['a' => 1]);               // throws StrongTypeException
new ListArray([1 => 'a', 2 => 'b']);     // throws StrongTypeException — doesn't start at 0
```

### `AssociativeArray`

Non-empty array that is **not** a list. Note that `array_is_list([])` is `true`, so `[]` is rejected.

```php
use StrongType\Arrays\AssociativeArray;

new AssociativeArray(['name' => 'alice', 'age' => 30]);  // OK
new AssociativeArray([1, 2, 3]);                         // throws StrongTypeException
new AssociativeArray([]);                                // throws StrongTypeException
```

### `ArrayOfStrings`

Non-empty array of strings.

```php
use StrongType\Arrays\ArrayOfStrings;

new ArrayOfStrings(['php', 'types']);   // OK
new ArrayOfStrings(['a', 1]);           // throws StrongTypeException
new ArrayOfStrings([]);                 // throws StrongTypeException
```

### `ArrayOfInts`

Non-empty array of integers.

```php
use StrongType\Arrays\ArrayOfInts;

new ArrayOfInts([1, 2, 3]);   // OK
new ArrayOfInts([1, '2']);    // throws StrongTypeException — strings not coerced
```

### `ArrayOfFloats`

Non-empty array of floats. Integers are not silently widened — pass `1.0` not `1`.

```php
use StrongType\Arrays\ArrayOfFloats;

new ArrayOfFloats([1.0, 2.5]);  // OK
new ArrayOfFloats([1, 2.5]);    // throws StrongTypeException
```

### `ArrayOfBools`

Non-empty array of booleans.

```php
use StrongType\Arrays\ArrayOfBools;

new ArrayOfBools([true, false, true]);  // OK
new ArrayOfBools([1, 0]);               // throws StrongTypeException
```

### `ArrayOfObjects`

Non-empty array of objects. Any object class passes; constrain a specific class with `#[ElementType(MyClass::class)]` on a custom array type.

```php
use StrongType\Arrays\ArrayOfObjects;

new ArrayOfObjects([new \stdClass(), new \DateTimeImmutable()]);  // OK
new ArrayOfObjects(['not an object']);                            // throws StrongTypeException
```

### `ArrayOfCallables`

Non-empty array of PHP callables.

```php
use StrongType\Arrays\ArrayOfCallables;

new ArrayOfCallables(['strlen', 'trim', fn($x) => $x]);  // OK
new ArrayOfCallables(['no_such_function']);              // throws StrongTypeException
```

### `ArrayOfResources`

Non-empty array of PHP `resource` values.

```php
use StrongType\Arrays\ArrayOfResources;

$f1 = fopen('php://memory', 'r');
$f2 = fopen('php://memory', 'r');
new ArrayOfResources([$f1, $f2]);  // OK
new ArrayOfResources(['not a resource']); // throws StrongTypeException
```

### `ArrayOfIterables`

Non-empty array of iterables (arrays or `\Traversable` instances).

```php
use StrongType\Arrays\ArrayOfIterables;

new ArrayOfIterables([[1, 2], new \ArrayIterator(['x'])]);  // OK
new ArrayOfIterables([42]);                                 // throws StrongTypeException
```

### `ArrayOfArrays`

Non-empty array of arrays. Inner contents are not validated (shallow check).

```php
use StrongType\Arrays\ArrayOfArrays;

new ArrayOfArrays([[1, 2], ['a', 'b']]);  // OK
new ArrayOfArrays([[1, 2], 'not array']); // throws StrongTypeException
```

---

## Booleans

`TrueValue` and `FalseValue` use PHP's `true` / `false` literal types in their constructor signatures. Invalid values surface as `\TypeError` before any validation runs. Use them where a value is conceptually fixed (e.g. a feature flag's enabled side, an "always on" sentinel) but you still want an object to participate in equality and serialization.

| Type | Constraint | Details |
| --- | --- | --- |
| [`TrueValue`](#truevalue) | `true` | PHP `true` literal type |
| [`FalseValue`](#falsevalue) | `false` | PHP `false` literal type |

### `TrueValue`

Wraps the literal value `true`. Uses a manual constructor — see [why some types keep manual constructors](architecture.md#why-some-types-keep-manual-constructors).

```php
use StrongType\Bool\TrueValue;

new TrueValue(true);    // OK
new TrueValue(false);   // \TypeError (PHP literal-type mismatch)

(string) new TrueValue(true);    // "1"
json_encode(new TrueValue(true)); // "true"
```

### `FalseValue`

Wraps the literal value `false`. Uses a manual constructor.

```php
use StrongType\Bool\FalseValue;

new FalseValue(false);  // OK
new FalseValue(true);   // \TypeError
```

---

## DateTime

DateTime types extend `StrongType\DateTime\DateTime`. `Timestamp`, `FutureTimestamp`, `PastTimestamp`, `DateString`, and `TimeString` all use [manual constructors](architecture.md#why-some-types-keep-manual-constructors) because they parse or compare against externally-mutable state (`time()`, calendar validity).

| Type | Constraint | Details |
| --- | --- | --- |
| [`Timestamp`](#timestamp) | `>= 0` | Non-negative Unix timestamp |
| [`FutureTimestamp`](#futuretimestamp) | `> time()` | Timestamp in the future |
| [`PastTimestamp`](#pasttimestamp) | `< time()` | Timestamp in the past |
| [`DateString`](#datestring) | `YYYY-MM-DD` | Valid calendar date |
| [`TimeString`](#timestring) | `HH:MM:SS` | Valid 24-hour time |

### `Timestamp`

Non-negative integer Unix timestamp.

```php
use StrongType\DateTime\Timestamp;

new Timestamp(0);             // OK (epoch)
new Timestamp(1700000000);    // OK
new Timestamp(-1);            // throws StrongTypeException
```

### `FutureTimestamp`

Integer timestamp strictly greater than `time()` at construction. Compares against externally-mutable state.

```php
use StrongType\DateTime\FutureTimestamp;

new FutureTimestamp(time() + 60);   // OK
new FutureTimestamp(time() - 60);   // throws StrongTypeException
```

### `PastTimestamp`

Integer timestamp strictly less than `time()` at construction.

```php
use StrongType\DateTime\PastTimestamp;

new PastTimestamp(time() - 60);   // OK
new PastTimestamp(time() + 60);   // throws StrongTypeException
```

### `DateString`

`YYYY-MM-DD` format with calendar-validity check. Uses a manual parser, so both shape failures and calendar-validity failures throw `FormatException` (a `StrongTypeException` subclass).

```php
use StrongType\DateTime\DateString;
use StrongType\Exception\FormatException;

new DateString('2024-01-15');  // OK
new DateString('2024-02-30');  // throws FormatException — invalid calendar date
new DateString('15-01-2024');  // throws FormatException — wrong format
```

### `TimeString`

`HH:MM:SS` 24-hour format. Uses a manual parser; both shape failures and out-of-range time fields throw `FormatException`.

```php
use StrongType\DateTime\TimeString;
use StrongType\Exception\FormatException;

new TimeString('00:00:00');   // OK
new TimeString('23:59:59');   // OK
new TimeString('24:00:00');   // throws FormatException — hour out of range
new TimeString('10:30');      // throws FormatException — wrong format
```

---

## Domain modeling examples

Real-world usage tends to combine the built-in types with a handful of custom constraint compositions. Three sketches:

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

For a deeper guide on how to compose your own types, see [defining types](defining-types.md).

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
