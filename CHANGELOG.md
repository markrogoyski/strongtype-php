# Changelog

All notable changes to StrongType PHP are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

This release introduces the attribute-based constraint composition system, a large set of new concrete types, and uniform `tryFrom` / `equals` / `nullable` / `withValues` factories on every base class.

### Breaking changes

- **`FloatingPoint` subtypes now reject `INF`, `-INF`, and `NAN`.** The base class carries a new `#[Finite]` constraint (priority 40, runs before range checks). There is no opt-out when extending `FloatingPoint`; define a custom base type if non-finite floats are required.
- **`ArrayType` switched from `\Iterator` to `\IteratorAggregate`.** The typed `current()` / `key()` / `next()` / `rewind()` / `valid()` overrides have been removed. Iteration is unchanged for `foreach` consumers, but direct calls to those methods on instances no longer resolve.
- **`FixedSizeArray::tryFrom()` and `FixedSizeArray::nullable()` throw `\LogicException`.** The required `$size` parameter cannot be supplied through either factory. Construct via `new FixedSizeArray($values, $size)` or use `withValues()` on an existing instance. This is a documented exception to the "tryFrom never throws" contract.
- **`ArrayType::equals` is order-sensitive for keys.** Insertion order is considered part of the value, so `['a' => 1, 'b' => 2]` is not equal to `['b' => 2, 'a' => 1]`.
- **`FloatingPoint::tryFrom` widens `int` to `float`.** This mirrors PHP's native int-to-float parameter widening. `tryFrom` on every other base class remains strict: `tryFrom('5')` returns `null`, not a coerced instance.
- **All six base classes now implement the new `StrongType\HasEquals` interface.** Their `equals()` parameter type widens from `self` to `HasEquals`, which is a contravariant change. User subclasses that override `equals()` with a narrower parameter type must widen to match.
- **`Nullable` validates the wrapped type before looking at the value.** The constructor now throws `\LogicException` if the `$type` argument is not a concrete class implementing `StrongType\HasEquals` and `\JsonSerializable`. Previously, `new Nullable(SomeAbstract::class, null)` or `new Nullable(NotAStrongType::class, null)` succeeded silently when the value was `null`; now they fail fast. The throw is `\LogicException` rather than `StrongTypeException` because passing the wrong `class-string` is a programmer error at the API boundary, not a value-validation failure. Callers wrapping external `\JsonSerializable&\Stringable` classes that do not implement `HasEquals` will need to adopt `HasEquals` (or wrap in a `StrongType` subclass).

### Added

#### Constraint engine
- `StrongType\Constraint\ConstraintInterface` — `validate(mixed $value, string $className): ?string` returning `null` on success or an error message on failure, plus `priority(): int` for execution ordering.
- `StrongType\Constraint\ConstraintValidator` — reads constraint attributes via reflection, walks the full class hierarchy (child + parents), sorts by priority band (50 range/size → 60 content → 100 format → 150 semantic → 200+ custom), caches the resolved set per class, and runs every constraint on instantiation. User-defined constraints plug in by implementing `ConstraintInterface` and tagging a class with the attribute.

#### Base-class methods
- `tryFrom(mixed $value): ?static` on `Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`, `ArrayType`.
- `equals(HasEquals $other): bool` on all six bases plus `Nullable`. `Nullable` now implements `HasEquals` itself, so it can flow through any code that accepts a `HasEquals` argument; cross-comparisons between a `Nullable` and a non-`Nullable` always return `false`.
- `equalsUnordered(HasEquals $other): bool` on `ArrayType` — type-strict multiset equality that ignores key/order at the top level. Use this when comparing collections that share contents but not insertion order; use `equals()` when key/order significance is required.
- `nullable(mixed $value): Nullable` convenience factory on all six bases.
- `withValues(array $values): static` on `ArrayType`.
- `StrongType\HasEquals` interface — implemented by every base class and by `Nullable`.

#### Exception subclasses
- `StrongType\Exception\ConstraintViolationException extends StrongTypeException` — thrown by `ConstraintValidator::validate()` for every attribute-driven validation failure. Lets callers distinguish constraint failures from other `StrongTypeException` subtypes without parsing messages.
- `StrongType\Exception\FormatException extends StrongTypeException` — thrown by the hand-rolled parsers in `DateString` and `TimeString` when the input does not match the expected `YYYY-MM-DD` / `HH:MM:SS` shape (or, for `DateString`, fails the calendar-validity check). Distinguishes parse-shape failures from other validation failures.

Both subclasses extend `StrongTypeException`, so existing `catch (StrongTypeException $e)` blocks continue to catch every validation failure unchanged. This change is non-breaking for that pattern; callers that want to react specifically to a constraint violation or a format-parse failure can now catch the subclass directly. PHP `\TypeError` from constructor parameter type hints (e.g. `new PositiveInt('5')`) and `tryFrom()` returning `null` for type-mismatched input remain unchanged.

#### Constraints
- `Finite` — rejects `INF`, `-INF`, `NAN`.
- `InRange` — combined `min..max` with optional exclusive endpoints.
- `InList` — strict allowlist membership; throws `\LogicException` if constructed with zero allowed values.
- `NotPattern` — inverted pattern match (repeatable).
- `Uuid` — UUID shape.
- `Ipv4`, `Ipv6`, `Cidr` — address-family-specific IP validation.
- `IsList`, `IsAssociative` — array key-shape checks.
- `Rfc3339` — strict RFC 3339 date-time grammar plus calendar round-trip.
- `AnyOf`, `AllOf`, `Not` constraint combinators. Children are passed as constructor arguments (`#[AnyOf(new InRange(1, 1023), new InRange(49152, 65535))]`) using PHP 8.1's support for `new` in attribute argument expressions. Combinators implement `ConstraintInterface` themselves, so they nest freely (`AnyOf(AllOf(...), Not(...))`) and compose with built-in or user-defined constraints. The combinator's effective priority — used to order it relative to sibling attributes on the same class — is the minimum of child priorities, so a combinator slots into the existing priority order alongside its lowest-band child rather than at a fixed point. **`AllOf` evaluates its own children in argument order, not priority order.** This differs from stacking attributes (which run in priority order); `AllOf` is intended as a groupable unit for nesting under `AnyOf` / `Not`, not a drop-in replacement for stacked attributes. `AnyOf` emits a composite error message listing every child failure; `AllOf` returns the first failing child's message verbatim; `Not` reports the inverted constraint's class name. `AnyOf` and `AllOf` constructed with zero children throw `\LogicException` per the constraint constructor invariants.

#### Concrete types
- Integers: `HttpStatusCode`.
- Floats: `PercentFloat`, `Latitude`, `Longitude`.
- Strings: `Ipv4AddressString`, `Ipv6AddressString`, `CidrString`, `MacAddressString`, `JwtString`, `PhoneE164String`, `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString`, `Rfc3339DateTimeString`.
- Arrays: `ListArray`, `AssociativeArray`.

### Fixed

- `Base64` constraint and `Base64String` now enforce canonical, padded standard Base64 (RFC 4648 §4). The previous regex-only check accepted shape-only strings such as `"A"`, `"AAA"`, `"A="`, `"==="`, and unpadded forms like `"YWJjZA"`. Validation now requires the length to be a multiple of 4 with 0–2 trailing `=` pad characters, the value to decode under `base64_decode($v, strict: true)`, and a re-encode round-trip equal to the input. Base64URL alphabet (`-`, `_`) is rejected. **Behavior change:** values previously accepted under the lax regex but not canonical Base64 will now throw `StrongTypeException`.
- Constraint constructors fail fast with `\LogicException` when configured impossibly: `DivisibleBy(0)`, `MinLength(-1)`, `MaxLength(-1)`, `MinCount(-1)`, `MaxCount(-1)`, `ExactCount(-1)`, `Pattern`/`NotPattern` with a regex that fails to compile, and `ElementType` with a string that is neither one of the locked builtin set (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) nor an existing class/interface name. Previously these were accepted and either produced misleading runtime behavior, surfaced later as `\Error`/PHP warnings during validation, or silently always passed/failed. PHP type aliases (`integer`, `boolean`, `double`) are explicitly rejected in `ElementType` — use the canonical name. Invariants on `InRange` (`min <= max`; equal bounds disallow exclusive flag) and `InList` (non-empty allowed set) were already enforced and gain explicit test coverage. Failures surface at the constraint's instantiation site — for attribute-driven types, that's `ReflectionAttribute::newInstance()` during the first construction of a value of the affected class, not at PHP class-load time.
- `SemverString` is now SemVer 2.0.0 spec compliant. The previous regex incorrectly **rejected** valid versions whose pre-release identifiers contained hyphens (e.g. `1.0.0-alpha-1`, `1.0.0-x.7.z.92`) and incorrectly **accepted** invalid versions with leading-zero numeric pre-release identifiers (e.g. `1.0.0-01`). The pattern is now derived from the official semver.org BNF and is anchored with `\A...\z` so trailing whitespace, tabs, or newlines (which PHP's `$` would otherwise tolerate before a final `\n`) are rejected. **Behavior change in both directions:** previously rejected valid SemVer strings now construct successfully, and previously accepted invalid strings now throw `StrongTypeException`.

### Changed

- `DateFormat('')` now throws `\LogicException` rather than being silently constructed; an empty format string can never accept any input and is a programmer error. Surfaced at the same site as the other constraint constructor invariants (attribute instantiation during the first validation for the affected class).
- Existing `Int`, `Float`, `String`, `Array`, `Bool`, and `DateTime` types are now defined declaratively via constraint attributes instead of hand-written constructor checks. A small set of types intentionally keeps manual constructors and is documented in `CLAUDE.md`: `EmptyString` (default param), `FixedSizeArray` (runtime `$size`), `TrueValue` / `FalseValue` (PHP `true` / `false` literal types), `Timestamp` / `FutureTimestamp` / `PastTimestamp`, `DateString`, `TimeString`.
- Codified the consistent-constructor contract on `Integer`, `FloatingPoint`, `StringType`, and `DateTime` (`@phpstan-consistent-constructor` / `@psalm-consistent-constructor`). Inherited `tryFrom` and `nullable` factories instantiate via `new static(...)` and assume subclasses share the base constructor signature; subclasses that diverge must override these factories. `ArrayType` and `BoolType` cannot carry the annotation for documented reasons (`FixedSizeArray`'s extra `$size` param; `TrueValue` / `FalseValue` narrowing `bool` to literal types).

### Documentation

- README: clarified that `Nullable` requires a concrete StrongType class and that the type is validated even when the wrapped value is `null`.
- README: new "Scope and Semantics" section documenting cross-cutting policy — format-vs-registry validation for ISO/MIME types, byte-not-Unicode length counting in `MinLength`/`MaxLength`, first-failure short-circuit during validation, type-strict equality semantics (and `ArrayType` key/order sensitivity), shallow `ElementType` validation that does not recurse into nested arrays, and the validate-at-boundaries immutability model. Adds backing tests for previously undertested claims: byte-length semantics for `MinLength`/`MaxLength` against multibyte input, short-circuit halt at the first failing constraint, and `ElementType('array')` not recursing into nested array contents.
- New `CLAUDE.md` documents source layout, the constraint system, the consistent-constructor contract, naming and namespace conventions, and the test layout.

### Notes on scope

- ISO code types (`CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`) validate **format only**; they do not check active registry membership. Unassigned codes like `ZZ` and `XYZ` are accepted.
- `MimeTypeString` enforces RFC 6838 restricted-name shape for both type and subtype (including rejection of trailing punctuation). It does not verify IANA registry membership.
- `Rfc3339` accepts `:60` seconds syntactically to tolerate leap-second notation; it does not verify the minute is an actual IERS-inserted leap second.
