# CLAUDE.md

## Project Overview

StrongType PHP is a library of strongly typed value objects for PHP 8.4+. It provides validated integer, float, string, array, boolean, and datetime types, plus an attribute-based constraint composition system that lets users define new types declaratively.

Core philosophy: **validate once at construction, trust everywhere after**. Every type is immutable, every invalid value throws `StrongTypeException` immediately, and types are designed to be used as parameter and return type hints.

## Commands

```bash
# Run tests
make tests
# or: vendor/bin/phpunit tests/ --configuration=tests/phpunit.xml

# Run all checks (lint, tests, style, phpstan, psalm, composer-unused, composer-require-checker)
make all

# Individual checks
make lint       # php-parallel-lint
make style      # phpcs (PSR-12, no line length limit)
make phpstan    # PHPStan at max level
make psalm      # Psalm static analysis
make phpmd      # PHP Mess Detector

# Coverage
make coverage
```

## Architecture

### Source Layout

```
src/
  Constraint/           # Attribute-based constraint system
    ConstraintInterface.php   # All constraints implement this
    ConstraintValidator.php   # Engine: reflection, caching, validation
    Min.php, Max.php, ...     # 38 built-in constraint attributes
  Int/                  # Integer base class + 10 concrete types
  Float/                # FloatingPoint base class + 6 concrete types
  String/               # StringType base class + 21 concrete types
  Arrays/               # ArrayType base class + 13 concrete types
  Bool/                 # BoolType base class + 2 concrete types
  DateTime/             # DateTime base class + 5 concrete types
  Nullable.php          # Nullable wrapper for any strong type
  HasEquals.php         # Marker interface for structural equality
  Exception/
    StrongTypeException.php   # All validation failures throw this
  Util/
    Stringify.php       # Value-to-string helper
```

### How Types Work

Every type extends one of 6 abstract base classes: `Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`, `ArrayType`. Each base class constructor calls `ConstraintValidator::validate($this, $value)`.

Types are validated either via:
1. **Constraint attributes** (preferred) -- e.g., `#[Positive] readonly class PositiveInt extends Integer {}`
2. **Manual constructors** (special cases only) -- when validation needs something a class-level attribute can't express

The rule for picking between them: **use attributes for value-shape rules that depend only on the input value; use a manual constructor only when the type needs a runtime parameter (e.g. `FixedSizeArray`'s `$size`), a PHP literal type narrowing (e.g. `TrueValue`/`FalseValue`), a default parameter (e.g. `EmptyString`), validation against externally-mutable state (e.g. comparing to `time()`), or a hand-rolled parse step that's cleaner inline than as an attribute.** Anything else should be expressed as one or more constraint attributes — that's the path users learn first and the path that composes cleanly under inheritance.

Built-in types currently keeping manual constructors: `EmptyString` (default param), `FixedSizeArray` (runtime `$size` param), `TrueValue` / `FalseValue` (PHP `true` / `false` type hints), `Timestamp` (non-negative integer timestamp), `FutureTimestamp` / `PastTimestamp` (compare against `time()`), `DateString` (`YYYY-MM-DD` parse + calendar-validity check), `TimeString` (`HH:MM:SS` parse + 24-hour-validity check).

### Constructor Consistency Contract

`Integer`, `FloatingPoint`, `StringType`, and `DateTime` are marked `@phpstan-consistent-constructor` / `@psalm-consistent-constructor`. The inherited static factories (`tryFrom`, `nullable`) instantiate via `new static(...)` and assume subclasses share the base constructor signature. Subclasses that diverge (add params, reorder, etc.) **must override** these factories or they will throw `ArgumentCountError` at runtime. (`withValues` is an `ArrayType`-only factory; it does not exist on the scalar bases.)

`ArrayType` cannot carry the annotation because `FixedSizeArray` legitimately adds a `$size` param; the `new static` calls there are suppressed inline and `FixedSizeArray` overrides all three factories (`withValues` handles the extra arg; `tryFrom` and `nullable` throw `\LogicException` since they can't be satisfied without a size).

`BoolType` cannot carry the annotation because `TrueValue` / `FalseValue` narrow `bool` to `true` / `false` literal types; those invalid values surface as `\TypeError` caught by `tryFrom`.

User subclasses with divergent constructors must follow the same pattern: override the inherited factories.

### Constraint System

- `ConstraintInterface`: `validate(mixed $value, string $className): ?string` returns null on success, error message on failure. `priority(): int` controls execution order (lower = first).
- `ConstraintValidator`: Reads attributes via `ReflectionClass`, walks class hierarchy (child + all parents), sorts by priority, caches per class. Static cache means reflection happens once per class per process. The cache is unbounded by design — the keyspace is the set of strong-type classes, which is bounded at compile time, so it grows once at warm-up and stays flat. Don't add eviction.
- Priority bands: 50 (range/size), 60 (content), 100 (format), 150 (semantic), 200+ (custom).
- Attributes use `\Attribute::TARGET_CLASS`. `Pattern` is `IS_REPEATABLE`.

### Constraint Constructor Invariants

Constraints with constructor parameters fail fast with `\LogicException` for impossible configurations: `DivisibleBy` rejects divisor 0; `MinLength`/`MaxLength`/`MinCount`/`MaxCount`/`ExactCount` reject negative bounds; `Pattern`/`NotPattern` reject regexes that fail to compile; `ElementType` rejects strings that are neither one of the locked builtin set (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) nor an existing class/interface; `InRange` rejects `min > max` and equal bounds with an exclusive flag; `InList` rejects an empty allowed set; `DateFormat` rejects an empty format string; `AnyOf`/`AllOf` reject zero children; `InEnum` rejects any class name that is not an existing enum (non-existent class, interface, or non-enum class all fail `enum_exists`). These are surfaced when the constraint is instantiated — for attribute-driven types that means the first time `ReflectionAttribute::newInstance()` runs in `ConstraintValidator::resolveConstraints()` (i.e. the first construction of a value of the affected class), not at PHP class-load time.

### Combinators

`AnyOf`, `AllOf`, and `Not` are constraints that wrap other constraints. Children are passed as constructor arguments (`new` is allowed in attribute argument expressions since PHP 8.1). Combinators implement `ConstraintInterface` themselves, so they nest freely and compose with non-combinator attributes. **Effective priority is `min(children)`** (for `Not`, the single child's priority) — this is the *external* priority used by `ConstraintValidator` to order the combinator relative to sibling attributes on the same class. **Internal child order is argument order**, not priority order — `AllOf` does not sort. This is deliberate: it preserves "first-failure-wins verbatim" with predictable, author-controlled message selection, and keeps `AllOf` honest as a unit for nesting under `AnyOf` / `Not` rather than a drop-in for stacked attributes (which do sort by priority). Don't sort children inside combinators when adding new ones. Error-message policy: `AnyOf` returns `"{Short} type must satisfy one of: [child1msg | child2msg | …]"` only when *every* child fails; `AllOf` returns the first failing child's message verbatim; `Not` returns `"{Short} type must NOT satisfy {ChildShortClassName}"`. Rich inverted descriptions for `Not` would require adding a `description()` method to `ConstraintInterface`; deferred past 1.0.

### Standard Interfaces

All types implement `\Stringable`, `\JsonSerializable`, and `\StrongType\HasEquals`. `ArrayType` additionally implements `\Countable` and `\IteratorAggregate`. Type-hint against `HasEquals` to accept "any strong type" generically.

### Key Conventions

- All base types are `readonly abstract` (except `ArrayType` which is `abstract` without `readonly` due to `protected(set)` property).
- Concrete types are `readonly` (except array types which are non-readonly).
- Error messages follow: `"{ShortClassName} type must {description}, got {value}"`.
- Short class name extracted via: `StrongType\Util\ShortClassName::of($className)` (correctly handles classes in the global namespace; do not use the inline `\substr(..., \strrpos(...) + 1)` form — it drops the first character when the FQCN has no `\`).
- Namespace maps directly to directory: `StrongType\` -> `src/`, `StrongType\Tests\` -> `tests/`.
- Value access: every type exposes both `$obj->value` (public readonly property) and `$obj->getValue()` (method).

## Testing

### Layout

```
tests/
  Constraint/     # Constraint system tests
    ConstraintValidatorTest.php   # Engine: caching, hierarchy, priority
    CompositionTest.php           # Multi-attribute integration
    CustomConstraintTest.php      # User-defined constraint extension
    MinTest.php, MaxTest.php, ... # Per-attribute tests
    NumericConstraintsTest.php    # Positive, Negative, Even, Odd, etc.
    StringConstraintsTest.php     # All string constraints
    ArrayConstraintsTest.php      # All array constraints
    DateTimeConstraintsTest.php   # DateTime constraints
  Int/, Float/, String/, Arrays/, Bool/, DateTime/  # Per-type tests
```

PHPUnit 11.5+ with `#[Test]` and `#[DataProvider]` attributes (no `test` prefix dependency, no `@test` annotations).

### BDD Comment Style — Given / When / Then

**Every test method body uses `// Given`, `// When`, `// Then` comments** to label the three phases. This is non-negotiable for new tests — match the existing style exactly.

```php
#[Test]
#[DataProvider('dataProviderForValidValues')]
public function testGetValue(int $value)
{
    // Given
    $positiveInt = new PositiveInt($value);

    // When
    $obtainedValue = $positiveInt->getValue();

    // Then
    $this->assertSame($value, $obtainedValue);
}
```

Phases may be omitted when not meaningful. Two common shapes:

- **Construction-only valid test** — only `// When` (construction is the action) and `// Then` (`expectNotToPerformAssertions`).
- **Invalid value test** — `// Then` (`expectException`) comes *before* `// When` (the throwing construction), because PHPUnit requires the expectation set up first.

```php
#[Test]
#[DataProvider('dataProviderForInvalidValues')]
public function testInvalidValue(int $value)
{
    // Then
    $this->expectException(StrongTypeException::class);

    // When
    new PositiveInt($value);
}
```

### Standard Test Surface for a Type

Every concrete type test covers, at minimum:

1. `testValidValue` — construction succeeds for valid values (`expectNotToPerformAssertions`).
2. `testGetValue` — `getValue()` returns the input.
3. `testDebugInfo` — `__debugInfo()['value']` returns the input.
4. `testStringRepresentation` — `(string) $obj` matches expected string form.
5. `testJsonSerialization` — `json_encode($obj)` matches expected JSON.
6. `testInvalidValue` — invalid values throw `StrongTypeException`.

Add `testEquals`, `testTryFrom`, `testNullable`, etc. when the type or change touches that surface. `ArrayType` tests add `testCount` and `testIteration`.

### Exhaustive Data Providers

Tests are **data-driven and exhaustive** — push the type's boundaries. Every provider should include:

- **Boundary values** — `0`, `1`, `-1`, `PHP_INT_MAX`, `PHP_INT_MIN`, empty string `''`, single character, just-below/just-above thresholds.
- **Typical values** — a handful of representative cases.
- **Edge cases specific to the type** — Unicode for strings, nested arrays for arrays, leap-second `:60` for RFC 3339, `INF`/`-INF`/`NAN` for floats, etc.
- **Invalid providers** — symmetric coverage: every "must be > 0" rule needs `0` and a negative; every length rule needs both ends.

Providers are `public static function dataProviderForX(): array` and live in the same file as the tests that use them.

### When Adding or Changing a Type

- Add a per-type test file under `tests/{Category}/` that covers the full standard surface above.
- If the type uses constraint attributes, also add coverage in the relevant `tests/Constraint/{Category}ConstraintsTest.php` file or per-attribute test.

## Build Must Stay Clean

**Run `make all` after every change — no exceptions — and do not declare a task done until it passes.** This runs lint, the full test suite, PSR-12 style, PHPStan (max level), Psalm, composer-unused, and composer-require-checker. A green `make all` is the contract for "done"; if any check fails, fix the root cause rather than narrowing the check or skipping it. For tight inner loops you can run individual targets (`make tests`, `make phpstan`, etc.), but the final gate before handing work back is always `make all`.

## Codex Second-Opinion Review

Before presenting a non-trivial **plan** or **implementation** back to the user, solicit a Codex review via the `codex-reviewer` subagent and iterate on its feedback. This is a quality gate, not a formality.

**When to invoke:**
- After drafting any plan that proposes more than a single localized edit (new type, new constraint, refactor across files, behavior change).
- After finishing an implementation that touches more than one file or introduces new public API.
- **Skip** for: trivial edits (typos, single-line fixes, doc-only tweaks), exploratory questions, or when the user asks for a quick answer rather than production-quality work.

**Loop:**
1. Draft the plan or implementation.
2. Invoke the `codex-reviewer` agent (or run the `/codex-review` slash command) with the plan text or the diff + summary.
3. Address every **Blocking** item; weigh **Suggestions** and apply the worthwhile ones.
4. If you made non-trivial changes, run another round. **Cap at 3 rounds.**
5. Then run `make all`, then present to the user — including a 1-2 sentence summary of what Codex flagged, what you addressed, and any blocking items you declined (with reasoning).

The reviewer runs `codex exec -s read-only` so it cannot modify the working tree. Each round costs Codex tokens, so keep rounds purposeful — don't loop just to loop.

## Documentation

Keep docs in sync with code in the same change. Documentation lives in three places:

- **`README.md`** — the entry point: hero usage examples, the catalog spine of every built-in type and attribute name (linking into the deep docs), error handling, and a 30-second semantics summary. Update the relevant Quick Reference table whenever you add a type, attribute, base method, or interface, or change error-message format.
- **`docs/`** — the deep reference. Seven topic files plus an index (`docs/README.md`), eight files total:
  - `docs/getting-started.md` — install, construction, value access, type hints, error handling.
  - `docs/built-in-types.md` — every concrete type with a usage example (catalog rows can use `// throws StrongTypeException` shorthand; verbatim messages live in the README, `docs/getting-started.md`, and one or two representative examples per category).
  - `docs/constraint-attributes.md` — every constraint attribute with constructor signature, an example, and the priority-bands table.
  - `docs/defining-types.md` — composition, inheritance, priority, custom constraints, combinators, wrapping enums, real-world domain modeling.
  - `docs/nullable-and-helpers.md` — `Nullable`, `tryFrom`, `equals`, `equalsUnordered`, `withValues`, implemented standard interfaces.
  - `docs/semantics.md` — cross-cutting policies (format-vs-registry, byte-vs-Unicode length, first-error, equality, shallow array validation, mutability).
  - `docs/architecture.md` — internals: how the validator caches, why some types keep manual constructors, the consistent-constructor contract, the constructor-invariants table.
  - `docs/README.md` — index with learning paths and use-case navigation.
  When adding a type or attribute, update **the README catalog row(s) and the matching `docs/built-in-types.md` or `docs/constraint-attributes.md` subsection** in the same change. Verify any new anchor link in the README resolves to a real heading.
- **`CHANGELOG.md`** — append a human-readable entry for any user-visible change (new type, new attribute, behavior change, bug fix).
- **`CLAUDE.md`** (this file) — update when architecture, conventions, or workflow change.

### Real-World Examples

Examples in `README.md` and `docs/` must be **realistic, domain-driven** — `Port`, `Username`, `Sku`, `Hostname`, `CurrencyCode`, `CreditCardNumber`, `Probability`, `FeatureFlags`. Avoid abstract `Foo`/`Bar`/`MyType` placeholders. When adding a constraint or feature, demonstrate it with a use case a reader would plausibly encounter in production code.

## Style

- PSR-12 (no line length limit).
- `declare(strict_types=1)` in every file.
- Root PHP namespace for built-in functions (e.g., `\strlen()`, `\count()`).
- PHPStan level max, Psalm clean.
