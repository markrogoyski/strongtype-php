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
- **`Nullable` validates the wrapped type before looking at the value.** The constructor now throws `StrongTypeException` if the `$type` argument is not a concrete class implementing `StrongType\HasEquals` and `\JsonSerializable`. Previously, `new Nullable(SomeAbstract::class, null)` or `new Nullable(NotAStrongType::class, null)` succeeded silently when the value was `null`; now they fail fast. Callers wrapping external `\JsonSerializable&\Stringable` classes that do not implement `HasEquals` will need to adopt `HasEquals` (or wrap in a `StrongType` subclass).

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

#### Constraints
- `Finite` — rejects `INF`, `-INF`, `NAN`.
- `InRange` — combined `min..max` with optional exclusive endpoints.
- `InList` — strict allowlist membership; throws `\LogicException` if constructed with zero allowed values.
- `NotPattern` — inverted pattern match (repeatable).
- `Uuid` — UUID shape.
- `Ipv4`, `Ipv6`, `Cidr` — address-family-specific IP validation.
- `IsList`, `IsAssociative` — array key-shape checks.
- `Rfc3339` — strict RFC 3339 date-time grammar plus calendar round-trip.

#### Concrete types
- Integers: `HttpStatusCode`.
- Floats: `PercentFloat`, `Latitude`, `Longitude`.
- Strings: `Ipv4AddressString`, `Ipv6AddressString`, `CidrString`, `MacAddressString`, `JwtString`, `PhoneE164String`, `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString`, `Rfc3339DateTimeString`.
- Arrays: `ListArray`, `AssociativeArray`.

### Changed

- Existing `Int`, `Float`, `String`, `Array`, `Bool`, and `DateTime` types are now defined declaratively via constraint attributes instead of hand-written constructor checks. A small set of types intentionally keeps manual constructors and is documented in `CLAUDE.md`: `EmptyString` (default param), `FixedSizeArray` (runtime `$size`), `TrueValue` / `FalseValue` (PHP `true` / `false` literal types), `Timestamp` / `FutureTimestamp` / `PastTimestamp`, `DateString`, `TimeString`.
- Codified the consistent-constructor contract on `Integer`, `FloatingPoint`, `StringType`, and `DateTime` (`@phpstan-consistent-constructor` / `@psalm-consistent-constructor`). Inherited `tryFrom` and `nullable` factories instantiate via `new static(...)` and assume subclasses share the base constructor signature; subclasses that diverge must override these factories. `ArrayType` and `BoolType` cannot carry the annotation for documented reasons (`FixedSizeArray`'s extra `$size` param; `TrueValue` / `FalseValue` narrowing `bool` to literal types).

### Documentation

- README: clarified that `Nullable` requires a concrete StrongType class and that the type is validated even when the wrapped value is `null`.
- New `CLAUDE.md` documents source layout, the constraint system, the consistent-constructor contract, naming and namespace conventions, and the test layout.

### Notes on scope

- ISO code types (`CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`) validate **format only**; they do not check active registry membership. Unassigned codes like `ZZ` and `XYZ` are accepted.
- `MimeTypeString` enforces RFC 6838 restricted-name shape for both type and subtype (including rejection of trailing punctuation). It does not verify IANA registry membership.
- `Rfc3339` accepts `:60` seconds syntactically to tolerate leap-second notation; it does not verify the minute is an actual IERS-inserted leap second.
