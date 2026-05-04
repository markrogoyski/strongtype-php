# Changelog

All notable changes to StrongType PHP are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

StrongType PHP 1.0 — first stable release.

This release introduces an **attribute-based constraint composition system** that lets users define new validated types declaratively, fills in the standard library of value types across every category, hardens the correctness of the existing types, and gives every base class a uniform set of factories (`tryFrom`, `nullable`, `withValues`) and an `equals()` contract via the new `HasEquals` interface. Every existing built-in type has been re-implemented on top of the new constraint engine, and a new `Exception` hierarchy distinguishes constraint violations from manual-parser failures while remaining catchable through the existing `StrongTypeException`.

### Breaking changes

- **`FloatingPoint` subtypes now reject `INF`, `-INF`, and `NAN`.** The base class carries a new `#[Finite]` constraint (priority 40, runs before range checks). There is no opt-out when extending `FloatingPoint`; define a custom base type if non-finite floats are required.
- **`ArrayType` switched from `\Iterator` to `\IteratorAggregate`.** The typed `current()` / `key()` / `next()` / `rewind()` / `valid()` overrides have been removed. Iteration is unchanged for `foreach` consumers, but direct calls to those methods on instances no longer resolve.
- **`FixedSizeArray::tryFrom()` and `FixedSizeArray::nullable()` throw `\LogicException`.** The required `$size` parameter cannot be supplied through either factory. Construct via `new FixedSizeArray($values, $size)` or use `withValues()` on an existing instance. This is a documented exception to the "tryFrom never throws" contract.
- **`ArrayType::equals` is order-sensitive for keys.** Insertion order is considered part of the value, so `['a' => 1, 'b' => 2]` is not equal to `['b' => 2, 'a' => 1]`.
- **`FloatingPoint::tryFrom` widens `int` to `float`.** This mirrors PHP's native int-to-float parameter widening. `tryFrom` on every other base class remains strict: `tryFrom('5')` returns `null`, not a coerced instance.
- **All six base classes now implement the new `StrongType\HasEquals` interface.** Their `equals()` parameter type widens from `self` to `HasEquals`, which is a contravariant change. User subclasses that override `equals()` with a narrower parameter type must widen to match.
- **`Nullable` validates the wrapped type before looking at the value.** The constructor now throws `\LogicException` if the `$type` argument is not a concrete class implementing `StrongType\HasEquals` and `\JsonSerializable`. Previously, `new Nullable(SomeAbstract::class, null)` or `new Nullable(NotAStrongType::class, null)` succeeded silently when the value was `null`; now they fail fast. The throw is `\LogicException` rather than `StrongTypeException` because passing the wrong `class-string` is a programmer error at the API boundary, not a value-validation failure. Callers wrapping external `\JsonSerializable&\Stringable` classes that do not implement `HasEquals` will need to adopt `HasEquals` (or wrap in a `StrongType` subclass).

### Constraint composition system

The headline addition. Types are now defined by stacking constraint attributes on a class — no constructor needed.

- **`StrongType\Constraint\ConstraintInterface`** — `validate(mixed $value, string $className): ?string` returning `null` on success or an error message on failure, plus `priority(): int` for execution ordering.
- **`StrongType\Constraint\ConstraintValidator`** — reads constraint attributes via reflection, walks the full class hierarchy (child + parents), sorts by priority band (50 range/size → 60 content → 100 format → 150 semantic → 200+ custom), caches the resolved set per class, and runs every constraint on instantiation. User-defined constraints plug in by implementing `ConstraintInterface` and tagging a class with the attribute.
- **All built-in `Int`, `Float`, `String`, `Array`, `Bool`, and `DateTime` types are now defined declaratively via constraint attributes** instead of hand-written constructor checks. A small set of types intentionally keeps manual constructors and is documented in `CLAUDE.md`: `EmptyString` (default param), `FixedSizeArray` (runtime `$size`), `TrueValue` / `FalseValue` (PHP `true` / `false` literal types), `Timestamp` / `FutureTimestamp` / `PastTimestamp`, `DateString`, `TimeString`.
- **Consistent-constructor contract codified** on `Integer`, `FloatingPoint`, `StringType`, and `DateTime` (`@phpstan-consistent-constructor` / `@psalm-consistent-constructor`). Inherited `tryFrom` and `nullable` factories instantiate via `new static(...)` and assume subclasses share the base constructor signature; subclasses that diverge must override these factories. `ArrayType` and `BoolType` cannot carry the annotation for documented reasons (`FixedSizeArray`'s extra `$size` param; `TrueValue` / `FalseValue` narrowing `bool` to literal types).

### New constraint attributes

#### Numeric
- `Finite` — rejects `INF`, `-INF`, `NAN`.
- `InRange` — combined `min..max` with optional exclusive endpoints.
- `InList` — strict allowlist membership.

#### String
- `NotPattern` — inverted pattern match (repeatable).
- `Uuid` — UUID shape.
- `Ipv4`, `Ipv6`, `Cidr` — address-family-specific IP validation.
- `Rfc3339` — strict RFC 3339 date-time grammar plus calendar round-trip.

#### Array
- `IsList`, `IsAssociative` — array key-shape checks.

#### Enum
- `InEnum` — `#[InEnum(MyEnum::class)]` validates a value against an enum. For `BackedEnum`, the value must match a backing value (string-backed → `StringType`, int-backed → `Integer`). For pure (non-backed) `UnitEnum`, the value must match a case name (used on `StringType`). No `EnumString` / `EnumInt` base classes are introduced; `InEnum` on a `StringType` or `Integer` subclass is sufficient.

#### Combinators
- `AnyOf`, `AllOf`, `Not` — constraint combinators that wrap other constraints. Children are passed as constructor arguments (`#[AnyOf(new InRange(1, 1023), new InRange(49152, 65535))]`) using PHP 8.1's support for `new` in attribute argument expressions. Combinators implement `ConstraintInterface` themselves, so they nest freely (`AnyOf(AllOf(...), Not(...))`) and compose with built-in or user-defined constraints. The combinator's effective priority — used to order it relative to sibling attributes on the same class — is the minimum of child priorities, so a combinator slots into the existing priority order alongside its lowest-band child rather than at a fixed point. **`AllOf` evaluates its own children in argument order, not priority order.** This differs from stacking attributes (which run in priority order); `AllOf` is intended as a groupable unit for nesting under `AnyOf` / `Not`, not a drop-in replacement for stacked attributes. `AnyOf` emits a composite error message listing every child failure; `AllOf` returns the first failing child's message verbatim; `Not` reports the inverted constraint's class name.

### New concrete types

- **Integers:** `HttpStatusCode`.
- **Floats:** `PercentFloat`, `Latitude`, `Longitude`.
- **Strings:** `Ipv4AddressString`, `Ipv6AddressString`, `CidrString`, `MacAddressString`, `JwtString`, `PhoneE164String`, `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString`, `Rfc3339DateTimeString`.
- **Arrays:** `ListArray`, `AssociativeArray`.

### Base-class methods and the `HasEquals` interface

- `tryFrom(mixed $value): ?static` on `Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`, `ArrayType`.
- `equals(HasEquals $other): bool` on all six bases plus `Nullable`. `Nullable` itself implements `HasEquals`, so it can flow through any code that accepts a `HasEquals` argument; cross-comparisons between a `Nullable` and a non-`Nullable` always return `false`.
- `equalsUnordered(HasEquals $other): bool` on `ArrayType` — type-strict multiset equality that ignores key/order at the top level. Use this when comparing collections that share contents but not insertion order; use `equals()` when key/order significance is required.
- `nullable(mixed $value): Nullable` convenience factory on all six bases.
- `withValues(array $values): static` on `ArrayType`.
- `StrongType\HasEquals` interface — implemented by every base class and by `Nullable`. Type-hint against `HasEquals` to accept "any strong type" generically.

### Error-type hierarchy

`StrongTypeException` gains two subclasses, both additive and non-breaking:

- `StrongType\Exception\ConstraintViolationException extends StrongTypeException` — thrown by `ConstraintValidator::validate()` for every attribute-driven validation failure.
- `StrongType\Exception\FormatException extends StrongTypeException` — thrown by the hand-rolled parsers in `DateString` and `TimeString` when the input does not match the expected `YYYY-MM-DD` / `HH:MM:SS` shape (or, for `DateString`, fails the calendar-validity check).

Existing `catch (StrongTypeException $e)` blocks continue to catch every validation failure unchanged. Callers that want to react specifically to a constraint violation or a format-parse failure can now catch the subclass directly. PHP `\TypeError` from constructor parameter type hints (e.g. `new PositiveInt('5')`) and `tryFrom()` returning `null` for type-mismatched input remain unchanged.

### Correctness fixes

- **`Base64` constraint and `Base64String`** now enforce canonical, padded standard Base64 (RFC 4648 §4). The previous regex-only check accepted shape-only strings such as `"A"`, `"AAA"`, `"A="`, `"==="`, and unpadded forms like `"YWJjZA"`. Validation now requires the length to be a multiple of 4 with 0–2 trailing `=` pad characters, the value to decode under `base64_decode($v, strict: true)`, and a re-encode round-trip equal to the input. Base64URL alphabet (`-`, `_`) is rejected. **Behavior change:** values previously accepted under the lax regex but not canonical Base64 will now throw `StrongTypeException`.
- **`SemverString`** is now SemVer 2.0.0 spec compliant. The previous regex incorrectly **rejected** valid versions whose pre-release identifiers contained hyphens (e.g. `1.0.0-alpha-1`, `1.0.0-x.7.z.92`) and incorrectly **accepted** invalid versions with leading-zero numeric pre-release identifiers (e.g. `1.0.0-01`). The pattern is now derived from the official semver.org BNF and is anchored with `\A...\z` so trailing whitespace, tabs, or newlines (which PHP's `$` would otherwise tolerate before a final `\n`) are rejected. **Behavior change in both directions:** previously rejected valid SemVer strings now construct successfully, and previously accepted invalid strings now throw `StrongTypeException`.
- **`Unique` constraint now uses strict equality.** Previously backed by `array_unique(SORT_REGULAR)`, which used loose comparison and incorrectly collapsed distinct values such as `[0, '0']`, `[1, '1']`, and `[true, 1]` (treating them as duplicates) while accepting `[NAN, NAN]` (treating each NaN as distinct). Uniqueness is now pairwise `===` with explicit NaN handling: loosely-equal-but-strictly-distinct scalars are accepted, two NaNs are rejected as duplicates. This matches the strict-equality semantics of `equals()` / `equalsUnordered()`. **Behavior change in both directions:** some arrays that previously threw will now succeed; some that previously succeeded will now throw.
- **`ArrayType::__toString()` no longer throws on values JSON cannot encode.** Arrays containing `NAN`, `INF`, `-INF`, or resources (all valid in `ArrayOfFloats`, `ArrayOfResources`, etc.) caused `(string) $array` to throw `JsonException` because `__toString()` used `json_encode(..., JSON_THROW_ON_ERROR)`. Stringification now falls back to a depth-bounded per-element renderer for inputs JSON would reject, preserving the JSON output for normal cases while honoring Stringable's contract that `__toString()` must not throw. The fallback also: terminates cleanly on self-referential arrays and on `JsonSerializable::jsonSerialize()` implementations that return `$this` (or otherwise re-enter themselves), via a 64-level depth cap that emits `"*RECURSION*"`; absorbs exceptions thrown by user `jsonSerialize()` methods (and exceptions propagated through `json_encode()` from a plain object's nested `JsonSerializable` properties) into a stable `object(FQCN)` marker; and avoids `print_r()` for arbitrary objects, since `print_r()` invokes `__debugInfo()` and PHP fatals if `__debugInfo()` throws or returns a non-array.
- **Regex anchor consistency across format-validating string types.** Eleven types used `^...$` anchors, which in PHP allow a single trailing `\n` to satisfy the match: `HexColorString`, `JwtString`, `MacAddressString`, `PhoneE164String`, `SlugString`, `BinaryString`, `MimeTypeString`, `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `LanguageCodeString`, `CurrencyCodeString`. All are now anchored with `\A...\z` (matching `SemverString`), so values like `"#fff\n"`, `"hello-world\n"`, `"US\n"` now correctly throw `StrongTypeException`.
- **`Nonzero` constraint now guards numeric inputs.** Previously used loose comparison (`$value == 0`) on `mixed`, which would have rejected non-numeric values like `null` and `false` (where PHP's loose comparison `null == 0` and `false == 0` are both `true`) if `#[Nonzero]` were applied to a non-numeric type. Now matches the pattern used by sibling numeric constraints (`Positive`, `Negative`, `Even`, `Odd`): the check only fires when `is_numeric($value)`. Numeric zero strings (`'0'`, `'0.0'`, `'0e123'`) are still rejected — they are `is_numeric()` and equal to `0`. No change in behavior for the only built-in users (`NonzeroInt`, `NonzeroFloat`), which already narrow at construction.
- **Short-class-name extraction handles classes in the global namespace.** Forty-nine constraints used the inline pattern `\substr($className, (int) \strrpos($className, '\\') + 1)` to derive the short class name for error messages. When `$className` had no namespace separator, `strrpos` returned `false`, the `(int)` cast turned that into `0`, and `substr(..., 1)` then dropped the first character — so a custom global-namespace type like `Portish` produced `"ortish type must be > 0, …"`. All forty-nine sites now route through `StrongType\Util\ShortClassName::of()`, matching the four sites (`AnyOf`, `AllOf`, `Not`, `InEnum`) that already did so.
- **Constraint constructor invariants.** Constructor-bearing constraints now fail fast with `\LogicException` when configured impossibly: `DivisibleBy(0)`; `MinLength(-1)`, `MaxLength(-1)`, `MinCount(-1)`, `MaxCount(-1)`, `ExactCount(-1)`; `Pattern` / `NotPattern` with a regex that fails to compile; `ElementType` with a string that is neither one of the locked builtin set (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) nor an existing class/interface name; `InRange` with `min > max` or with equal bounds and an exclusive flag; `InList` with an empty allowed set; `DateFormat('')`; `AnyOf` / `AllOf` with zero children; `InEnum` constructed with a non-existent class, an interface, or a non-enum class. Previously many of these were accepted and either produced misleading runtime behavior, surfaced later as `\Error` / PHP warnings during validation, or silently always passed/failed. PHP type aliases (`integer`, `boolean`, `double`) are explicitly rejected in `ElementType` — use the canonical name. Failures surface at the constraint's instantiation site — for attribute-driven types, that's `ReflectionAttribute::newInstance()` during the first construction of a value of the affected class, not at PHP class-load time.

### Documentation

- Documentation restructured: the canonical reference moves from a single `README.md` into a hero/catalog landing page plus a new `docs/` directory. The README serves both first-time readers (hero examples, narrative pitch) and catalog users (compact 2-column tables of every built-in type and constraint attribute, each name linking into the deep docs). The seven topic files plus index in `docs/` cover getting started, the full type and attribute catalogs with per-row examples, defining custom types, library semantics, the `Nullable` wrapper and base-class helpers, internals (how the validator caches, manual-constructor rationale, the consistent-constructor contract, the constructor-invariants table), and an index with named learning paths and use-case navigation. No code changes.
- New comprehensive coverage of the 1.0 surface area now lives across the README and `docs/`:
  - Quick-reference tables for every built-in type and every constraint attribute (`README.md` catalog spine plus per-row examples in `docs/built-in-types.md` and `docs/constraint-attributes.md`).
  - Combinators (`AnyOf` / `AllOf` / `Not`) with realistic, domain-driven examples — well-known-or-ephemeral ports, scoped usernames, rejected all-caps strings (`docs/defining-types.md`).
  - Enum wrapping via `InEnum` for string-backed, int-backed, and pure enums (`docs/defining-types.md`).
  - Error handling table mapping each failure mode (constructor type mismatch, `tryFrom` mismatch, constraint failure, manual parse failure) to its result type, plus a `declare(strict_types=1)` callout (`README.md`, `docs/getting-started.md`).
  - Cross-cutting "Scope and Semantics" coverage of format-vs-registry validation for ISO/MIME types, byte-not-Unicode length counting in `MinLength` / `MaxLength`, first-failure short-circuit during validation, type-strict equality semantics (and `ArrayType` key/order sensitivity), shallow `ElementType` validation that does not recurse into nested arrays, and the validate-at-boundaries immutability model (`docs/semantics.md`). Adds backing tests for previously undertested claims: byte-length semantics for `MinLength` / `MaxLength` against multibyte input, short-circuit halt at the first failing constraint, and `ElementType('array')` not recursing into nested array contents.
  - Clarification that `Nullable` requires a concrete StrongType class and that the type is validated even when the wrapped value is `null` (`docs/nullable-and-helpers.md`).
  - Constraint constructor invariants table — every fail-fast `\LogicException` configuration documented in one place (`docs/architecture.md`).
- New `CLAUDE.md` documents source layout, the constraint system, the consistent-constructor contract, naming and namespace conventions, the test layout, and the documentation layout (README + `docs/` + CHANGELOG).

### Notes on scope

- ISO code types (`CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`) validate **format only**; they do not check active registry membership. Unassigned codes like `ZZ` and `XYZ` are accepted.
- `MimeTypeString` enforces RFC 6838 restricted-name shape for both type and subtype (including rejection of trailing punctuation). It does not verify IANA registry membership.
- `Rfc3339` accepts `:60` seconds syntactically to tolerate leap-second notation; it does not verify the minute is an actual IERS-inserted leap second.
