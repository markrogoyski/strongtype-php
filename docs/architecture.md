# Architecture

Back to [README](../README.md) · [Documentation index](README.md)

This document explains how the constraint system works internally, why a small set of types intentionally keeps hand-written constructors, the consistent-constructor contract that the inherited factories rely on, and the constructor invariants that fail fast when a constraint is configured impossibly.

## How the constraint system works

The constraint system uses PHP 8 attributes and reflection:

1. **At first instantiation** of a type, `ConstraintValidator` reads all `ConstraintInterface` attributes from the class and its parents via reflection, sorts them by priority, and caches the result.
2. **On every instantiation**, it iterates the cached constraint list and calls `validate()` on each. The first failure throws `ConstraintViolationException` (a `StrongTypeException` subclass).
3. **Subsequent instantiations** skip reflection entirely — it's a hash lookup plus iterating a small array.

The static cache is **process-local**: reflection runs once per class per process, and the cached entries persist for the life of the process. Eviction is intentionally not implemented. The keyspace is the set of strong-type classes, which is bounded at compile time, so the cache grows once at warm-up and then stays flat.

No constraint checks are executed for classes with an empty resolved constraint list. Types that keep manual constructors typically still call `parent::__construct()` first — the validator runs against the (empty) attribute set for that class, then the manual constructor runs its own checks. The first construction of any class still resolves and caches the constraint list via reflection, and every construction still does a cache lookup; what's elided is the per-instance iteration over constraints, since there are none to run. The next section explains why a handful of built-in types fall in that category.

## Why some types keep manual constructors

Most concrete types are defined declaratively — class-level constraint attributes plus an empty class body. A small set of built-ins keep hand-written constructors because their validation needs something a class-level attribute cannot express:

- **`EmptyString`** — a default parameter (`$value = ''`) that an attribute can't supply.
- **`FixedSizeArray`** — a runtime parameter (`$size`) chosen by the caller, not the class definition.
- **`TrueValue` / `FalseValue`** — PHP `true` / `false` literal types in the constructor signature, which surface invalid values as `\TypeError` before any constraint runs.
- **`Timestamp`** — a non-negative integer interpreted as a Unix timestamp.
- **`FutureTimestamp` / `PastTimestamp`** — comparison against `time()`, which is externally mutable state.
- **`DateString`** — parses `YYYY-MM-DD` and additionally checks calendar validity (so `2024-02-30` fails even though it matches the shape).
- **`TimeString`** — parses `HH:MM:SS` and validates the 24-hour ranges.

The rule when you're defining your own types: **use attributes for value-shape rules that depend only on the input value; use a manual constructor only when validation needs a runtime parameter, a default parameter, a PHP literal type narrowing, comparison against externally-mutable state, or a hand-rolled parse step that's cleaner inline than as an attribute.** Anything else should be expressed as one or more constraint attributes.

## Constructor consistency contract

`Integer`, `FloatingPoint`, `StringType`, and `DateTime` are marked `@phpstan-consistent-constructor` / `@psalm-consistent-constructor`. The inherited static factories (`tryFrom`, `nullable`) instantiate via `new static(...)` and assume subclasses share the base constructor signature.

**Subclasses that diverge from the base constructor signature must override these factories**, or the inherited factory will throw `ArgumentCountError` at runtime when the wrong number of arguments is forwarded to the subclass constructor. (`withValues` is an `ArrayType`-only factory; it does not exist on the scalar bases.)

`ArrayType` cannot carry the consistency annotation because `FixedSizeArray` legitimately adds a `$size` parameter. The `new static` calls inside `ArrayType` are suppressed inline, and `FixedSizeArray` overrides all three factories (`withValues` handles the extra arg; `tryFrom` and `nullable` throw `\LogicException` since they cannot be satisfied without a size).

`BoolType` cannot carry the annotation either, because `TrueValue` and `FalseValue` narrow `bool` to `true` and `false` literal types in the constructor signature. Invalid values surface as `\TypeError`, which `tryFrom` catches.

User subclasses that diverge from the base constructor must follow the same pattern: override the inherited factories, or only call the base constructor signature you actually support.

## Constructor invariants

Built-in constraints fail fast with `\LogicException` when configured impossibly — these are programmer errors, not validation failures, and surface during attribute instantiation (the first time a typed value of the affected class is constructed), not at PHP class-load time.

| Constraint                        | Invariant                                       |
| --------------------------------- | ----------------------------------------------- |
| `DivisibleBy`                     | divisor must not be zero                        |
| `MinLength`, `MaxLength`          | length must be `>= 0`                           |
| `MinCount`, `MaxCount`, `ExactCount` | count must be `>= 0`                          |
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

These are deliberately not caught by `StrongTypeException`. A divisor of zero or a regex that fails to compile is a bug in the type definition, not a runtime validation failure on a user-supplied value.

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
