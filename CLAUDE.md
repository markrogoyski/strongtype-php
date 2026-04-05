# CLAUDE.md

## Project Overview

StrongType PHP is a library of strongly typed value objects for PHP 8.4+. It provides validated integer, float, string, array, boolean, and datetime types, plus an attribute-based constraint composition system that lets users define new types declaratively.

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
  Exception/
    StrongTypeException.php   # All validation failures throw this
  Util/
    Stringify.php       # Value-to-string helper
```

### How Types Work

Every type extends one of 6 abstract base classes: `Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`, `ArrayType`. Each base class constructor calls `ConstraintValidator::validate($this, $value)`.

Types are validated either via:
1. **Constraint attributes** (preferred) -- e.g., `#[Positive] readonly class PositiveInt extends Integer {}`
2. **Manual constructors** (legacy/special cases) -- for types needing default params, extra constructor args, or PHP literal types

Most built-in types have been refactored to use attributes. Types that keep manual constructors: `EmptyString` (default param), `FixedSizeArray` (runtime `$size` param), `TrueValue`/`FalseValue` (PHP `true`/`false` type hints), `Timestamp`, `FutureTimestamp`, `PastTimestamp`, `DateString`, `TimeString`.

### Constraint System

- `ConstraintInterface`: `validate(mixed $value, string $className): ?string` returns null on success, error message on failure. `priority(): int` controls execution order (lower = first).
- `ConstraintValidator`: Reads attributes via `ReflectionClass`, walks class hierarchy (child + all parents), sorts by priority, caches per class. Static cache means reflection happens once per class per process.
- Priority bands: 50 (range/size), 60 (content), 100 (format), 150 (semantic), 200+ (custom).
- Attributes use `\Attribute::TARGET_CLASS`. `Pattern` is `IS_REPEATABLE`.

### Key Conventions

- All base types are `readonly abstract` (except `ArrayType` which is `abstract` without `readonly` due to `protected(set)` property).
- Concrete types are `readonly` (except array types which are non-readonly).
- Error messages follow: `"{ShortClassName} type must {description}, got {value}"`.
- Short class name extracted via: `\substr($className, \strrpos($className, '\\') + 1)`.
- Namespace maps directly to directory: `StrongType\` -> `src/`, `StrongType\Tests\` -> `tests/`.

### Tests

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

Test style: PHPUnit 11.5+ with `#[Test]` and `#[DataProvider]` attributes. Valid values use `expectNotToPerformAssertions()` or `assertSame()`. Invalid values use `expectException(StrongTypeException::class)`.

## Style

- PSR-12 (no line length limit).
- `declare(strict_types=1)` in every file.
- Root PHP namespace for built-in functions (e.g., `\strlen()`, `\count()`).
- PHPStan level max.
