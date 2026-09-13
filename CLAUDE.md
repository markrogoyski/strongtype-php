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
    ConstraintValidator.php   # Engine: reflection, caching, applicability, validation
    ValueType.php             # Enum: Int, Float, String, Bool, Array -- a base's value domain
    AnyValueType.php          # Trait: acceptedValueTypes() => every type (for custom constraints)
    StringValueType.php, ArrayValueType.php, IntValueType.php, NumericValueType.php
                              # Traits: acceptedValueTypes() for the 4 literal sets shared by
                              # more than one built-in constraint (String/Array/Int/[Int,Float])
    IntersectsChildValueTypes.php  # Trait: shared AnyOf/AllOf acceptedValueTypes() intersection
    Min.php, Max.php, ...     # 38 built-in constraint attributes
  Int/                  # Integer base class + 10 concrete types
  Float/                # FloatingPoint base class + 6 concrete types
  String/               # StringType base class + 21 concrete types
  Arrays/               # ArrayType base class + 15 concrete types
  Bool/                 # BoolType base class + 2 concrete types
  DateTime/             # DateTime base class + 5 concrete types
  Nullable.php          # Nullable wrapper for any strong type
  HasEquals.php         # Marker interface for structural equality
  Exception/
    StrongTypeException.php   # extends \RuntimeException; umbrella for all validation failures
    ConstraintViolationException.php  # StrongTypeException subclass: a constraint attribute rejected the value
    FormatException.php               # StrongTypeException subclass: a manual parse step rejected the string shape
    ConstraintConfigurationException.php  # extends \LogicException; type/constraint mis-definition (programmer error)
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

`ArrayType` cannot carry the annotation because `FixedSizeArray` legitimately adds a `$size` param; the `new static` calls there are suppressed inline and `FixedSizeArray` overrides all three factories (`withValues` handles the extra arg; `tryFrom` and `nullable` throw `\BadMethodCallException` since they can't be satisfied without a size -- the factory is intentionally unavailable, not misconfigured, so it stays out of `ConstraintConfigurationException`).

`BoolType` cannot carry the annotation because `TrueValue` / `FalseValue` narrow `bool` to `true` / `false` literal types; those invalid values surface as `\TypeError` caught by `tryFrom`.

User subclasses with divergent constructors must follow the same pattern: override the inherited factories.

The same rule extends to `__serialize()`/`__unserialize()` (see Serialization below): each base's implementation only captures its own constructor argument(s) (`value`, or `values` for `ArrayType`). A subclass with additional, non-derivable constructor state — promoted or not, any property whose value isn't recomputable from `value`/`values` alone — must override both methods to include it, the same way `FixedSizeArray` overrides them (and the three factories) to also carry its `$size`. Unlike the factory obligation, this one is enforced at runtime: every base's `__serialize()` calls `StrongType\Util\SerializationStateGuard::assertNoUnhandledSerializationState()`, which reflects on the runtime class's declared properties and throws `ConstraintConfigurationException` if any exist beyond what that base's own `__serialize()` captures — a subclass that inherits the base's `__serialize()` unchanged but has extra state cannot silently corrupt its round trip.

A subclass can also diverge from this contract *without* declaring any extra property, by overriding the constructor to transform its single argument before storing it (e.g. scale it) rather than only validating it — `assertNoUnhandledSerializationState()` can't see that, since nothing about it looks like an extra property. `SerializationStateGuard::assertConstructorIsSafeToInherit()` closes this separately, and purely structurally (no invocation, so it can't be fooled or destabilized by a validation-only override that legitimately depends on external state, e.g. `FutureTimestamp`/`PastTimestamp` comparing against `time()`): any override below the base's own class is untrusted by default and rejected, unless the subclass either (a) overrides `__serialize()`/`__unserialize()` itself (see `FixedSizeArray`), or (b) certifies, by overriding the protected static `constructorPreservesSerializedStateVerbatim()` to return `true`, that its override stores the argument unchanged and only adds validation — see `FutureTimestamp`, `PastTimestamp`, `Timestamp`, `DateString`, `TimeString`, `EmptyString`, `TrueValue`, and `FalseValue` for the pattern. It also independently rejects a constructor requiring more required arguments than inherited `__unserialize()` supplies, regardless of certification — an unstored, un-carriable argument is never safe to inherit around. The certification is bound to the specific class that declares the constructor it describes (checked by comparing the constructor's and the certification method's own `getDeclaringClass()`), not inherited transitively — otherwise a further override (`ScaledTimestamp extends Timestamp` calling `parent::__construct($value * 100)`) would silently inherit `Timestamp`'s "I only validate" certification for a constructor `Timestamp`'s author never saw. It also independently rejects a `private` constructor regardless of certification: inherited `__unserialize()` calls `$this->__construct(...)` from the base's own scope (e.g. `Integer`), and PHP's `protected` visibility permits that call because the calling scope is an ancestor of the declaring class, but `private` is scoped to the exact declaring class only — a subclass gating construction behind a `private` constructor plus a public static factory can otherwise pass arity and certification checks cleanly and still fail at deserialization time with `Error: Call to private method ...::__construct() from scope ...`. Declare the constructor `protected` or `public`, or override `__serialize()`/`__unserialize()` yourself.

### Constraint System

- `ConstraintInterface`: `validate(mixed $value, string $className): ?string` returns null on success, error message on failure. `priority(): int` controls execution order (lower = first). `acceptedValueTypes(): list<ValueType>` declares the value types the constraint can engage on (see Value Type Applicability below).
- `ConstraintValidator`: Reads attributes via `ReflectionClass`, walks class hierarchy (child + all parents), sorts by priority, checks applicability, caches per class. Static cache means reflection happens once per class per process. The cache is unbounded by design — the keyspace is the set of strong-type classes, which is bounded at compile time, so it grows once at warm-up and stays flat. Don't add eviction.
- Priority bands: 50 (range/size), 60 (content), 100 (format), 150 (semantic), 200+ (custom).
- Attributes use `\Attribute::TARGET_CLASS`. `Pattern` is `IS_REPEATABLE`.

### Value Type Applicability

Each base declares a typed class constant `final public const ValueType VALUE_TYPE` (**`final` is load-bearing** — a subclass that redeclared it could relabel its value domain and reopen the fail-open hole) (`Integer`→`Int`, `FloatingPoint`→`Float`, `StringType`→`String`, `BoolType`→`Bool`, `DateTime`→`String`, `ArrayType`→`Array`), inherited by every subclass. Every constraint declares `acceptedValueTypes(): list<ValueType>`. After collecting and sorting, `ConstraintValidator::resolveConstraints()` reads the concrete class's `VALUE_TYPE` (via `getReflectionConstant()`, so "absent" is distinguishable from a falsy value; the value itself is read through `\constant()` because `ReflectionClassConstant::getValue()` is declared as scalar/array/null and would mis-type the enum) and throws `ConstraintConfigurationException` for any constraint whose accepted set excludes it — **before** the cache assignment, so a rejected definition never poisons the cache. Message: `"{Constraint} is not applicable to {ValueType} values on {class}; it applies to [{accepted}]"` (or `"...it applies to no value type"` for an empty set).

This exists because every constraint guards its own value type and returns "valid" otherwise — a fail-open default that, on a base whose value type is fixed, means the constraint can *never* engage. It closes four holes at once: a misapplied constraint validating nothing, `Even`/`Odd`/`DivisibleBy` no-op'ing on floats, `AnyOf` vacuous-pass (a non-engaging child short-circuits the OR to satisfied), and `Not` dead-type (the same child inverts to reject-everything).

**Authoring a constraint:** declare only the types `validate()` genuinely inspects. `use AnyValueType;` returns `ValueType::cases()` and is for genuinely type-agnostic constraints only — it is public API for users; no built-in uses it (which is why `tools/phpstan.neon` carries a scoped `trait.unused` ignore for that one file, mirroring the consumer-facing suppressions in `tools/psalm.xml`). **A constraint's `validate()` guard must agree with its declared set** — a guard wider than the declaration re-admits the half-dead case the check exists to catch (this is why the numeric constraints guard `is_int() || is_float()` rather than `is_numeric()`, which also accepted numeric strings). Assignments: numeric (`Min`, `Max`, `InRange`, `Positive`, `Negative`, `Nonnegative`, `Nonpositive`, `Nonzero`) → `[Int, Float]`; `Finite` → `[Float]`; `Even`, `Odd`, `DivisibleBy`, `InFuture`, `InPast` → `[Int]`; all string constraints → `[String]`; all array constraints → `[Array]`; `Nonempty` → `[String, Array]` (it guards both, and backs `NonemptyString` *and* `NonemptyArray`/`AssociativeArray`). Every built-in whose set is one of the four literal, non-derived assignments above (`[String]`, `[Array]`, `[Int]`, `[Int, Float]`) implements it via `use StringValueType;` / `ArrayValueType` / `IntValueType` / `NumericValueType` instead of restating the method body — these are internal-only (no `trait.unused` suppression needed, since every one has at least two built-in users); a constraint whose set is derived, instance-specific, or the sole user of a literal set still declares `acceptedValueTypes()` itself -- except `AnyOf`/`AllOf`, whose derived (intersection) set is shared via `IntersectsChildValueTypes` instead (see Combinators).

**Derived sets:** `InList` and `InEnum` compute an *instance-specific* set from their constructor arguments — `InList` from the deduped value types of its allowed values (`null`/object operands ignored; an otherwise-empty result falls back to all types so a degenerate list is never falsely rejected), `InEnum` from the enum's backing type (`[Int]` for int-backed, `[String]` for string-backed **or** pure, since a pure enum is compared against `$case->name`). Making them `AnyValueType` would re-open the hole: `#[InList(1, 2)]` on a `StringType` would pass an "any" check yet never match under strict `in_array`.

**Combinators intersect:** `AnyOf`/`AllOf`/`Not` return the intersection of their children's sets (for `Not`, the single child's). Conservative, not permissive — that is what makes the vacuous-pass and dead-`Not` cases detectable. An empty intersection is a configuration error wherever the combinator is attached. Don't switch this to a union.

### Constraint Constructor Invariants

Constraints with constructor parameters fail fast with `StrongType\Exception\ConstraintConfigurationException` (extends `\LogicException`) for impossible configurations: `DivisibleBy` rejects divisor 0; `MinLength`/`MaxLength`/`MinCount`/`MaxCount`/`ExactCount` reject negative bounds; `Pattern`/`NotPattern` reject regexes that fail to compile; `ElementType` rejects strings that are neither one of the locked builtin set (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) nor an existing class/interface; `Min`/`Max` reject a `NAN` bound and `InRange` rejects a `NAN` min or max (every comparison against `NAN` is false, so a `NAN` bound would otherwise never reject a value — the same fail-open shape the value-type applicability check exists to close); `InRange` also rejects `min > max` and equal bounds with an exclusive flag; `InList` rejects an empty allowed set; `DateFormat` rejects an empty format string or one containing an unescaped `!`/`|` (a backslash-escaped `\!`/`\|` is a literal character and is accepted); `AnyOf`/`AllOf` reject zero children; `InEnum` rejects any class name that is not an existing enum (non-existent class, interface, or non-enum class all fail `enum_exists`). `Nullable` uses the same exception for its type-wiring guards (non-strong-type or abstract `$type`). These are surfaced when the constraint is instantiated — for attribute-driven types that means the first time `ReflectionAttribute::newInstance()` runs in `ConstraintValidator::resolveConstraints()` (i.e. the first construction of a value of the affected class), not at PHP class-load time.

### Exception Hierarchy

`StrongTypeException` extends `\RuntimeException` (a validated *value* was invalid — a runtime concern). `ConstraintConfigurationException` extends `\LogicException` (a *type or constraint* is mis-defined — a programmer error: an impossible constraint constructor argument, or a constraint attached to a value type it cannot engage on) and is never also a `StrongTypeException`; the two hierarchies are independently catchable in both directions. `FixedSizeArray::tryFrom()`/`nullable()` throw plain `\BadMethodCallException` instead of `ConstraintConfigurationException` — nothing is misconfigured, the single-value factory is just intentionally unavailable without a `$size`.

`ConstraintEvaluationException extends StrongTypeException` covers a distinct case from an ordinary violation: the constraint could not determine whether the value is valid at all (a regex engine failure, a cyclic-array comparison `Unique` can't resolve), as opposed to evaluating it and finding it invalid. It's thrown directly from `validate()` rather than returned as a normal violation string — see Combinators below for why that distinction matters.

### Combinators

`AnyOf`, `AllOf`, and `Not` are constraints that wrap other constraints. Children are passed as constructor arguments (`new` is allowed in attribute argument expressions since PHP 8.1). Combinators implement `ConstraintInterface` themselves, so they nest freely and compose with non-combinator attributes. **Applicability is the intersection of the children's** (see Value Type Applicability) — `AnyOf` and `AllOf` share this via `use IntersectsChildValueTypes;`; `Not` doesn't need it, since with exactly one child its accepted set already *is* that child's. **Effective priority is `min(children)`** (for `Not`, the single child's priority) — this is the *external* priority used by `ConstraintValidator` to order the combinator relative to sibling attributes on the same class. **Internal child order is argument order**, not priority order — `AllOf` does not sort. This is deliberate: it preserves "first-failure-wins verbatim" with predictable, author-controlled message selection, and keeps `AllOf` honest as a unit for nesting under `AnyOf` / `Not` rather than a drop-in for stacked attributes (which do sort by priority). Don't sort children inside combinators when adding new ones. Error-message policy: `AnyOf` returns `"{Short} type must satisfy one of: [child1msg | child2msg | …]"` only when *every* child fails; `AllOf` returns the first failing child's message verbatim; `Not` returns `"{Short} type must NOT satisfy {ChildShortClassName}"`. Rich inverted descriptions for `Not` would require adding a `description()` method to `ConstraintInterface`; deferred past 1.0.

**`Not`/`AnyOf`/`AllOf` never mistake "could not be evaluated" for a real pass or fail.** `ConstraintInterface::validate()` returns `?string` — null (valid) or a violation message (invalid) — with no third state built in for "the check itself couldn't run" (a regex engine failure, an indeterminate cyclic-array comparison). A constraint that hits that case throws `StrongType\Exception\ConstraintEvaluationException` directly from `validate()` instead of returning an ordinary violation string (see `Pattern`/`NotPattern`'s regex-engine-error handling and `Unique`'s circular-reference rejection). `Not` doesn't catch exceptions from its single child's `validate()` call at all — it only ever interprets the *return value* — so the exception propagates straight out uncaught, the same way `ConstraintConfigurationException` already does. `AnyOf` and `AllOf`, however, *do* catch `ConstraintEvaluationException` specifically (nothing else) from each child, because with more than one child, letting the first thrown exception abort the whole combinator immediately makes the result depend on child *order*: `AnyOf(NotPattern(...), MinLength(1))` and `AnyOf(MinLength(1), NotPattern(...))` must agree on the same value, even though only one of them would ever reach the throwing child first under naive short-circuiting. Both combinators retain the exception, keep evaluating the rest of the children, and only re-throw it once it can no longer be avoided: `AnyOf` accepts immediately if any child passes, and throws the retained exception only if none does (a confirmed failure from another child doesn't help — the indeterminate child might have passed); `AllOf` returns immediately on any child's confirmed violation (a real, known reason to reject beats reporting an indeterminate one), and throws the retained exception only if every other child passes (nothing else disproves the AND, so the indeterminate child is the only thing left in question). This is deliberate: if "couldn't evaluate" were instead returned as a normal violation string, `Not` would read it as "the child failed" and invert it into an accept (`#[Not(new Unique)]` would have silently accepted two cyclic elements neither side could actually compare) — the exception is what lets `Not`, and the final fallthrough in `AnyOf`/`AllOf`, tell "confirmed indeterminate" apart from an ordinary, confirmed failure, rather than conflating the two.

### Standard Interfaces

All types implement `\Stringable`, `\JsonSerializable`, and `\StrongType\HasEquals`. `ArrayType` additionally implements `\Countable` and `\IteratorAggregate`. Type-hint against `HasEquals` to accept "any strong type" generically.

### Serialization

Native `unserialize()` populates an object's (even readonly) properties directly, bypassing the constructor and therefore `ConstraintValidator` entirely — without a defense, a crafted payload could produce e.g. `PositiveInt(-5)`. Every base (`Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`, `ArrayType`), `FixedSizeArray`, and `Nullable` implements `__serialize()`/`__unserialize()`. `__unserialize()` re-enters via `$this->__construct(...)` — a normal method call, dispatched virtually, so a subclass's own manual checks run too (e.g. `EmptyString`'s non-empty check, `DateString`'s calendar-validity check), not just the declaring base's `ConstraintValidator` call. This is also the *only* write these readonly properties ever receive for an unserialized instance: the object `unserialize()` builds for a class with `__serialize()`/`__unserialize()` starts with none of its properties initialized, so calling the constructor here is a first write, not a re-initialization — the same reasoning `@psalm-suppress DirectConstructorCall` documents inline at each call site.

`Nullable::__unserialize()` does not re-derive the wrapped value from `jsonSerialize()` — that would be lossy for any wrapped type whose `jsonSerialize()` output isn't identical to its constructor input (legal per the `\JsonSerializable` contract). It instead nests the wrapped object itself in `__serialize()`, so `unserialize()` reconstructs it via the wrapped type's *own* `__serialize()`/`__unserialize()` first (native unserialize resolves nested objects bottom-up), then re-runs only its own type-wiring guards and confirms the reconstructed object's class still matches the stored `$type` string.

Nesting the wrapped object only stays safe if that object's own class is guaranteed to revalidate itself when reconstructed — which is only true if it implements `__serialize()`/`__unserialize()` itself, *and* does not also implement the legacy `\Serializable` interface. A crafted payload's own format sigil (`O:` for the modern hooks, `C:` for `Serializable::unserialize()`) — not which methods the target class happens to declare — decides which reconstruction path native `unserialize()` takes, so a class carrying both would still be exploitable via a hand-built `C:`-sigil payload even with a perfectly safe `__unserialize()` sitting right next to the unsafe legacy one. `Nullable` does not require the wrapped class to extend one of the six bases (any class implementing `HasEquals`, `\JsonSerializable`, and `\Stringable` with a compatible constructor is wrappable, and is perfectly usable for construction, `equals()`, and JSON without any of this), so both `Nullable::__serialize()` and `__unserialize()` separately check (`assertSafelySerializable()`) that the wrapped class declares its own `__serialize()`/`__unserialize()` *and* does not implement `\Serializable`, throwing `ConstraintConfigurationException` otherwise. The check is duplicated deliberately: a hand-crafted payload never goes through `__serialize()`, so the `__unserialize()` copy is the only guard such a payload cannot route around.

`Nullable::__unserialize()` never re-invokes `$this->__construct(...)` — deliberately, since doing so would mean deriving the wrapped value from `jsonSerialize()` output to pass through the single-value constructor, reintroducing the lossiness problem above. Unlike the six value-type bases, whose inherited `__unserialize()` re-running `$this->__construct(...)` safely re-validates *any* subclass (since none of their `jsonSerialize()` outputs diverge from constructor input), this means a `Nullable` subclass's own constructor logic — validation, or any additional property — never gets re-run or re-carried by inherited hooks at all. There is no reflection-based certification that can paper over this the way `constructorPreservesSerializedStateVerbatim()` does for the six bases: Nullable's inherited `__serialize()`/`__unserialize()` are unconditionally unsafe for *any* subclass. Both therefore call `assertNotInheritingUnsafeSerialization($method)` first, which throws `ConstraintConfigurationException` whenever the *specific* method named (`'__serialize'` or `'__unserialize'`) is still declared by `Nullable` itself for this instance — checked via `(new \ReflectionMethod($this, $method))->getDeclaringClass()`, not via a `static::class !== self::class` identity check: late static binding means `static::class` still resolves to the subclass even *inside* a call the subclass routed here itself via `parent::__serialize()`/`parent::__unserialize()`, so a same-class-identity check would incorrectly reject exactly that recommended delegating pattern. The reflection check instead correctly distinguishes "the subclass didn't touch this method at all" (unsafe, reject) from "the subclass overrides this method and chose to delegate back here" (trusted). That trust is bounded to what the overriding class could have known about: a further-derived class that adds its own property, *or* its own constructor validation with no new property at all (e.g. forbidding null), without also writing its own override, is still caught — checked the same way `SerializationStateGuard` checks it for the six bases, by walking every class strictly between the runtime class and the overriding one and rejecting any property or constructor declared there. A subclass implementing the legacy `\Serializable` interface is also rejected outright (the same threat `assertSafelySerializable()` guards against for a wrapped type, though this can only fail fast on an ordinary `serialize()` call — a crafted `'C:'`-sigil payload naming such a subclass bypasses `__unserialize()`, and this check, entirely). A subclass must override `__serialize()`/`__unserialize()` itself, even if only to call `parent::__serialize()`/`parent::__unserialize()` and then re-run its own validation. Subclassing `Nullable` beyond the test fixtures that exercise `equals()` is not an established, documented extension point the way the six bases are; fully closing this some other way would mean redesigning `Nullable`'s constructor to accept a pre-built wrapped instance, deferred past 1.0.

Subclasses of the six value-type bases and `ArrayType`/`FixedSizeArray` with extra constructor state must override `__serialize()`/`__unserialize()` too — see Constructor Consistency Contract above and `FixedSizeArray` for the pattern. The base's default `__serialize()` enforces this at runtime via `SerializationStateGuard`, which walks the full class hierarchy by name (not just the runtime class) since `ReflectionClass::getProperties()` silently omits a *private* property declared by an intermediate ancestor when reflected from a more distant descendant, and checks each found property's actual `getDeclaringClass()` (not merely which walk step surfaced it, since an inherited public/protected property is visible from every descendant's own reflection) against the known set — a subclass that redeclares its own same-named *private* property (a distinct storage slot from the ancestor's) is still correctly caught as unhandled state, not mistaken for the base's own. The same trait separately guards a constructor override that stores no extra property but still diverges from the contract another way (transforms its input, or needs an extra unstored argument) — see the constructor-fidelity paragraph under Constructor Consistency Contract above.

### Key Conventions

- All base types are `readonly abstract`, `ArrayType` included — its `$values` is a plain `public readonly array`, written once in the constructor.
- Concrete types are `readonly`, array types included. PHP forbids a readonly class extending a non-readonly one and vice versa, so the whole `src/Arrays/` hierarchy is readonly or none of it is; a user subclass of `ArrayType` must be `readonly class` too. `FixedSizeArray`'s promoted `private(set) int $size` stays legal inside a readonly class (it becomes write-once).
- Immutability is at the value level, not the element level: object/resource elements of an `ArrayType` are validated, not copied, so they remain externally mutable by design (shallow validation). An element passed *by reference* can additionally be replaced with a value of another type after validation, defeating even `ElementType` — documented in `docs/semantics.md#element-aliasing`, deliberately not defended against (detaching references would cost a copy on every construction).
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
  - `docs/constraint-attributes.md` — every constraint attribute with constructor signature, an example, the value-type applicability table, and the priority-bands table.
  - `docs/defining-types.md` — composition, inheritance, priority, value-type applicability, custom constraints, combinators, wrapping enums, real-world domain modeling.
  - `docs/nullable-and-helpers.md` — `Nullable`, `tryFrom`, `equals`, `equalsUnordered`, `withValues`, implemented standard interfaces.
  - `docs/semantics.md` — cross-cutting policies (format-vs-registry, byte-vs-Unicode length, first-error, equality, shallow array validation, mutability).
  - `docs/architecture.md` — internals: how the validator caches, why some types keep manual constructors, the consistent-constructor contract, the value-type applicability check, the constructor-invariants table.
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
