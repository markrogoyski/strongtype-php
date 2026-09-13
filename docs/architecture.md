# Architecture

Back to [README](../README.md) · [Documentation index](README.md)

This document explains how the constraint system works internally, why a small set of types intentionally keeps hand-written constructors, the consistent-constructor contract that the inherited factories rely on, the value-type applicability check that rejects constraints which could never engage, and the constructor invariants that fail fast when a constraint is configured impossibly.

## How the constraint system works

The constraint system uses PHP 8 attributes and reflection:

1. **At first instantiation** of a type, `ConstraintValidator` reads all `ConstraintInterface` attributes from the class and its parents via reflection, sorts them by priority, [checks each one against the class's value type](#value-type-applicability), and caches the result.
2. **On every instantiation**, it iterates the cached constraint list and calls `validate()` on each. The first failure throws `ConstraintViolationException` (a `StrongTypeException` subclass, which extends `\RuntimeException` — validation failures come from runtime input).
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

`Nullable` has the same constraint from the other side: it constructs the wrapped type with exactly one argument. Its constructor therefore checks the wrapped type's arity by reflection alongside its other type-wiring guards, and throws `ConstraintConfigurationException` when the constructor requires more than one parameter (`new Nullable(FixedSizeArray::class, [1, 2])`). The check runs before the value is looked at, so it fires for a `null` value too — otherwise a `Nullable` that can never hold a non-null value would construct successfully. Only *required* parameters count, so optional and variadic extras stay wrappable.

It is deliberately a precheck rather than a `catch (\ArgumentCountError)` around the instantiation. An `ArgumentCountError` can just as easily come from inside a perfectly wrappable one-argument constructor; catching it there would relabel an unrelated bug as "this type needs more than one argument". Reflection answers the question that is actually being asked — the arity of *this* class's constructor — and deeper `ArgumentCountError`s propagate untouched.

`ArrayType` cannot carry the consistency annotation because `FixedSizeArray` legitimately adds a `$size` parameter. The `new static` calls inside `ArrayType` are suppressed inline, and `FixedSizeArray` overrides all three factories (`withValues` handles the extra arg; `tryFrom` and `nullable` throw `\BadMethodCallException` since they cannot be satisfied without a size — the factory is intentionally unavailable, not misconfigured, so it stays out of the `ConstraintConfigurationException` category below while remaining a `\LogicException` under the hood).

`BoolType` cannot carry the annotation either, because `TrueValue` and `FalseValue` narrow `bool` to `true` and `false` literal types in the constructor signature. Direct construction with the wrong literal (`new TrueValue(false)`) raises `\TypeError` straight from PHP's own parameter-type check, before any validation logic runs — that is inherent to literal-type narrowing and happens regardless of `tryFrom`. Going through `tryFrom`, though, **each class overrides `tryFrom()` to pre-filter the mismatched literal before calling `parent::tryFrom()`** (see `TrueValue::tryFrom()` / `FalseValue::tryFrom()`), so `TrueValue::tryFrom(false)` returns `null` instead of leaking that same `\TypeError`. The inherited `BoolType::tryFrom()` does not do this filtering itself.

**This matters for your own types.** If you define a `BoolType` subclass that narrows its constructor parameter to `true` or `false` (the same literal-type-narrowing pattern as `TrueValue`/`FalseValue`) and do *not* override `tryFrom()`, calling the inherited `tryFrom()` with the wrong literal does not return `null` the way it does for every non-narrowed `BoolType` — it forwards the value straight into `new static($value)` and lets PHP's own literal-type check raise an uncaught `\TypeError`. Copy the `TrueValue`/`FalseValue` `tryFrom()` override (pre-filter with `if ($value !== true) { return null; }` or the `false` equivalent) whenever you narrow a `BoolType` constructor this way.

User subclasses that diverge from the base constructor must follow the same pattern: override the inherited factories, or only call the base constructor signature you actually support.

## Serialization

Native PHP `unserialize()` builds an object and writes straight into its (even readonly) properties, bypassing the constructor entirely — and with it, every constraint attribute and manual validation check. Left unguarded, a crafted or tampered serialized payload could produce an instance no legitimate construction could ever reach — a `PositiveInt` holding a negative value, for example.

Every base type, `FixedSizeArray`, and `Nullable` implements `__serialize()`/`__unserialize()` to close this. `__unserialize()` re-enters through the constructor (`$this->__construct(...)`), so the full validation chain — constraint attributes *and* any manual checks a subclass adds — runs again on the way back in, exactly as it would for a fresh `new`:

```php
$original = new PositiveInt(5);
$restored = unserialize(serialize($original));   // revalidates; PositiveInt(5) again

$tampered = str_replace('i:5;', 'i:-5;', serialize($original));
unserialize($tampered);                           // throws StrongTypeException — caught, not silently accepted
```

`Nullable` reconstructs the wrapped object from its own nested serialized form (not a re-derived raw value), so the wrapped type's full chain reruns too, and a payload pairing a `type` string with an unrelated wrapped object is rejected.

**This matters for your own types.** A subclass that adds non-derivable constructor state beyond what its base already carries — promoted or not, anything whose value isn't recomputable from `value`/`values` alone — must override `__serialize()`/`__unserialize()` to include it, the same existing obligation a divergent constructor already places on `tryFrom()`/`nullable()` (see [Constructor consistency contract](#constructor-consistency-contract) above). `FixedSizeArray` is the built-in example: its `__serialize()`/`__unserialize()` additionally carry `size`, the extra constructor argument its base doesn't have.

This obligation is enforced at runtime, not just documented: every base's `__serialize()` calls a shared guard (`StrongType\Util\SerializationStateGuard::assertNoUnhandledSerializationState()`) that reflects on the runtime class's declared properties and throws `ConstraintConfigurationException` if any exist beyond what that base's own `__serialize()` captures. When the base's own `__serialize()` runs at all (rather than a subclass override), any property beyond the known set is unaccounted for — either silently dropped (an optional extra property survives construction but not the round trip) or an `\ArgumentCountError` waiting inside `__unserialize()`'s `$this->__construct()` call (a required one). A subclass with extra state must write a **full**, self-contained `__serialize()`/`__unserialize()` override — the `FixedSizeArray` pattern — not one that delegates back to `parent::__serialize()` after handling its own extra property: the parent's own `__serialize()` still checks against its own fixed known-property list, with no way to know about state a subclass added, so a delegating override would still throw on exactly the property it just tried to handle. The check runs when `__serialize()` is actually called, not at construction, since that is the only point the gap can matter.

The guard also walks the *entire* class hierarchy, not just the runtime class: `ReflectionClass::getProperties()` called on a class only surfaces that class's own declared properties plus any public/protected ones inherited from a parent — a parent's *private* property is silently omitted from a more distant descendant's reflection, even though it's still part of the object. A `ScaledInt extends Integer` with its own private promoted `$scale`, further extended by an empty `PreciseScaledInt extends ScaledInt`, would otherwise let `PreciseScaledInt`'s guard check miss `scale` entirely. `assertNoUnhandledSerializationState()` reflects every class in the hierarchy on its own terms (where each ancestor's own private properties are always visible) rather than reflecting once on the runtime class, and matches each found property against the known list by its actual `getDeclaringClass()` — not by which walk step happened to surface it, since an inherited public/protected property is visible starting from every descendant's own reflection, not only its true declaring class. This closes a narrower but real variant of the same gap: a subclass can legally declare its *own* private property sharing a name with a known one (private-property shadowing gives it a distinct storage slot from the ancestor's), and that must still be flagged as unhandled rather than mistaken for the base's own state because the name matches.

**A subclass can also diverge from the contract without declaring any extra property at all** — by overriding the constructor to *transform* its single argument (e.g. scale it) rather than only validate it. `assertNoUnhandledSerializationState()` looks for extra properties, so it cannot see this: the stored property is still just `value`, holding the already-transformed number. Left unguarded, `__serialize()` would capture the post-transformation value, and inherited `__unserialize()` would feed that already-transformed value back through the same constructor a second time, silently reconstructing a different value than the one serialized (`value * 100` stored as `500`, restored as `50000`). A related divergence: a constructor requiring an additional argument that is never stored anywhere, which would make inherited `__unserialize()`'s single-argument `$this->__construct()` call fail with `\ArgumentCountError`. `SerializationStateGuard::assertConstructorIsSafeToInherit()` closes both, called from the same `__serialize()` alongside `assertNoUnhandledSerializationState()`. It never invokes the constructor to check this empirically — a validation-only override can legitimately depend on external, mutable state (`FutureTimestamp`/`PastTimestamp` compare against `time()`) and fail on a *later* call for reasons that have nothing to do with whether it stores its argument verbatim, so re-running it here to compare would misreport ordinary time drift as a configuration error. Instead, it's purely structural: a constructor requiring more arguments than inherited `__unserialize()` supplies is unconditionally rejected, and any override at all below the base's own declaring class is untrusted by default and rejected too, *unless* the subclass either writes a full `__serialize()`/`__unserialize()` override itself (`FixedSizeArray`'s pattern), or certifies -- by overriding the protected static `constructorPreservesSerializedStateVerbatim()` to return `true` -- that its override stores the argument unchanged and only adds validation. `FutureTimestamp`, `PastTimestamp`, `Timestamp`, `DateString`, `TimeString`, `EmptyString`, `TrueValue`, and `FalseValue` all certify this way: each overrides its base's constructor purely to validate (or, for `EmptyString`, to supply a default parameter), never to change what gets stored. The certification is bound to the specific class that declares the constructor it describes (by comparing the constructor's and the certification method's own `getDeclaringClass()`), not inherited transitively: a further override -- `ScaledTimestamp extends Timestamp` calling `parent::__construct($value * 100)` -- would otherwise silently inherit `Timestamp`'s "I only validate" certification for a constructor `Timestamp`'s author never saw and that does exactly the transforming thing the certification exists to rule out. It also independently rejects a `private` constructor, regardless of certification: inherited `__unserialize()` calls `$this->__construct(...)` from the base's own scope (e.g. `Integer`), and PHP's `protected` visibility permits that call because the calling scope is an ancestor of the declaring class -- but `private` is scoped to the exact declaring class only, so a subclass gating construction behind a `private` constructor plus a public static factory can otherwise pass arity and certification checks cleanly (including a truthful verbatim-storage certification) and still fail at deserialization time with `Error: Call to private method ...::__construct() from scope ...`, the first time a serialized instance is actually unserialized rather than the moment it's serialized. Declaring the constructor `protected` or `public` resolves it; so does a full `__serialize()`/`__unserialize()` override.

**`Nullable` additionally requires the wrapped type to be safely serializable in its own right.** Nullable does not require the wrapped class to extend one of the six bases — any class implementing `HasEquals`, `\JsonSerializable`, and `\Stringable` with a compatible constructor is wrappable (see `NullableTest`'s custom fixtures) — but nesting an *unsafe* wrapped object in `__serialize()` would only be safe to trust back if native `unserialize()` is guaranteed to revalidate it too. A class with no `__serialize()`/`__unserialize()` of its own gets its properties written straight in by PHP's default object unserialization, with no constructor call at all — the exact bypass this whole mechanism exists to close, just one level down. Declaring the modern hooks is not sufficient on its own, either: a class that *also* implements the legacy `\Serializable` interface can still be exploited, because a serialized payload's own format sigil (`O:` vs `C:`) — not which methods the target class declares — decides whether native `unserialize()` calls `__unserialize()` or the legacy `Serializable::unserialize()`; a hand-crafted `C:`-sigil payload selects the unsafe legacy path regardless of a safe `__unserialize()` sitting right next to it. `Nullable::__serialize()` and `__unserialize()` both check (via `assertSafelySerializable()`) that the wrapped class declares its own `__serialize()`/`__unserialize()` *and* does not implement `\Serializable`, before trusting it; a wrapped type without that guarantee can still be constructed, compared, and JSON-encoded through `Nullable` normally — it just cannot be serialized. The check is duplicated in `__unserialize()` deliberately: a hand-crafted payload never has to go through `__serialize()` in the first place, so that is the only guard such a payload cannot route around.

**`Nullable`'s own inherited hooks are unconditionally unsafe for any subclass.** `Nullable::__unserialize()` intentionally never calls `$this->__construct(...)` (see above — doing so would mean deriving the wrapped value from `jsonSerialize()` output, reintroducing the lossiness the nested-object design avoids). Unlike a subclass of the six value-type bases, where inherited `__unserialize()` re-running `$this->__construct(...)` safely re-validates *any* override, a `Nullable` subclass's own constructor logic — validation, or any additional property — never gets re-run or re-carried by inherited hooks at all, and there is no reflection-based certification (à la `constructorPreservesSerializedStateVerbatim()`) that could make it safe to trust anyway. So `Nullable::__serialize()` and `__unserialize()` both call `assertNotInheritingUnsafeSerialization($method)` first, which throws `ConstraintConfigurationException` whenever the *specific* method named is still declared by `Nullable` itself for this instance — checked via `(new \ReflectionMethod($this, $method))->getDeclaringClass()`, deliberately not via a `static::class !== self::class` identity check: late static binding means `static::class` still resolves to the subclass even *inside* a call the subclass routed here itself via `parent::__serialize()`/`parent::__unserialize()`, so an identity check would incorrectly reject exactly that recommended delegating pattern — every `Nullable` subclass would then be unserializable regardless of what it overrode. The reflection check instead correctly distinguishes "the subclass didn't touch this method at all" (unsafe, reject) from "the subclass overrides this method and chose to delegate back here" (trusted — see `ProperlyOverridingNullableSubclass` in `DeserializationTest` for the pattern). Checked in both methods for the same reason `assertSafelySerializable()` is: a hand-crafted payload naming the subclass directly never has to go through `__serialize()` first, so the `__unserialize()` copy is the only guard such a payload cannot route around.

That trust in a delegating override is bounded to what it could have known about, not unconditional: a class further derived than the one that overrides these hooks can still add *its own* unhandled state — a new property, or its own constructor validation with no new property at all (e.g. forbidding null) — that the overriding class's implementation was written without any knowledge of (`GrandChild extends MidLevel extends Nullable`, where only `MidLevel` overrides the hooks and `GrandChild` silently adds a property or a constructor check, would otherwise slip through). `assertNotInheritingUnsafeSerialization()` closes this the same way `SerializationStateGuard` closes the analogous gap for the six bases: it walks every class strictly between the runtime class and the one whose override is actually running (exclusive of that class, which is trusted), reflecting each individually (a private property is invisible from a more distant descendant's own reflection call, the same reason `SerializationStateGuard` walks by name rather than reflecting once on the runtime class), and rejects any property or constructor declared there. A `Nullable` subclass that additionally implements the legacy `\Serializable` interface is rejected outright too — the identical threat `assertSafelySerializable()` guards against for a *wrapped* type, one level down — though unlike that case, this can only fail fast on an ordinary `serialize()` call: a hand-crafted `'C:'`-sigil payload naming such a subclass directly invokes `Serializable::unserialize()` instead of `__unserialize()`, bypassing this whole method (and the constructor) entirely, with nothing left here to intercept it.

A subclass must override both hooks itself — even a trivial override that calls `parent::__serialize()`/`parent::__unserialize()` and then re-runs its own validation is enough. Subclassing `Nullable` itself is not a documented extension point the way the six bases are; closing this some other way would require `Nullable` to accept a pre-built wrapped instance internally, which is deferred past 1.0.

## Value type applicability

Every base declares the single PHP value type it holds as a typed class constant, inherited by every subclass:

| Base | `VALUE_TYPE` |
|------|--------------|
| `Integer` | `ValueType::Int` |
| `FloatingPoint` | `ValueType::Float` |
| `StringType` | `ValueType::String` |
| `BoolType` | `ValueType::Bool` |
| `DateTime` | `ValueType::String` |
| `ArrayType` | `ValueType::Array` |

The constant is `final`: a subclass that redeclared it could relabel its own value domain and reopen the very hole this check closes.

Every constraint declares the value types it can engage on via `ConstraintInterface::acceptedValueTypes(): list<ValueType>`. After `resolveConstraints()` has collected and sorted a class's constraints — and after the constructor invariants below have had their chance to fire — it reads the concrete class's `VALUE_TYPE` once and rejects any constraint whose accepted set excludes it:

```
Positive is not applicable to String values on App\Score; it applies to [Int, Float]
```

The throw happens **before** the resolved list is written to the cache, so a rejected definition never poisons the cache — and because nothing was cached, every subsequent construction of that class re-resolves and re-throws rather than silently succeeding. The cache therefore holds one entry per *successfully* resolved class, and the timing of the error matches the constructor invariants exactly (once resolved, on the first construction of a value of that type).

**Why this is definition-time, not validation-time.** Every constraint guards its own value type (`is_string()`, `is_int() || is_float()`, …) and returns "valid" for anything else — a fail-open default that is correct within a single `validate()` call but wrong across a whole type. Because a base's value type is *fixed*, an inapplicable constraint can never engage on any value of that type, so the honest answer is to reject the definition rather than silently pass every value. That closes these failure modes at once:

- A **misapplied constraint** (`#[Positive]` on a `StringType`) validates nothing.
- **`Even` / `Odd` / `DivisibleBy` on a float type** no-op, because they guard `is_int()`.
- **A numeric constraint on a string type** was only *half* dead — `is_numeric()` let `#[Positive]` reject `'-1'` while silently accepting every non-numeric string. Those guards are now `is_int() || is_float()`, matching the declared set, so the whole case is a definition error.
- **`AnyOf` passes vacuously**: a child that cannot engage always "succeeds", so the OR short-circuits to satisfied and the type accepts everything.
- **`Not` rejects everything**: the same always-succeeding child inverts to an always-failing constraint.

**Derived sets.** Two constraints have no fixed domain and compute one per instance from their arguments — `InList` from the value types of its allowed values, `InEnum` from the enum's backing type (`Int` for an int-backed enum, `String` for a string-backed or pure one, since a pure enum is compared against its case names). Declaring them as "applies to anything" would have re-opened the same hole: `#[InList(1, 2)]` on a `StringType` passes an "any" check yet, under strict membership, could never match a value.

**Combinators intersect.** `AnyOf`, `AllOf`, and `Not` derive their accepted set as the intersection of their children's. That is the conservative rule, and it is what makes the vacuous-pass and dead-`Not` cases above detectable. An empty intersection — children with no value type in common — means the combinator can never engage on any single type, so it is a configuration error wherever it is attached.

**Custom constraints** must implement `acceptedValueTypes()`; it is a required interface method rather than an opt-in capability, because a partial guarantee would leave exactly the holes this check exists to close. The `AnyValueType` trait returns `ValueType::cases()` for a genuinely type-agnostic constraint in one line. No built-in uses it — every built-in inspects a specific value type.

A class that does not declare `VALUE_TYPE` at all is skipped: it is not a strong type, so there is no domain to check against. `getReflectionConstant()` is used rather than `getConstant()` so that "absent" is distinguishable from a legitimately falsy constant value.

## Constructor invariants

Built-in constraints fail fast with `ConstraintConfigurationException` when configured impossibly — these are programmer errors, not validation failures, and surface during attribute instantiation (the first time a typed value of the affected class is constructed), not at PHP class-load time.

| Constraint                        | Invariant                                       |
| --------------------------------- | ----------------------------------------------- |
| `DivisibleBy`                     | divisor must not be zero                        |
| `MinLength`, `MaxLength`          | length must be `>= 0`                           |
| `MinCount`, `MaxCount`, `ExactCount` | count must be `>= 0`                          |
| `Pattern`, `NotPattern`           | regex must compile                              |
| `ElementType`                     | type must be a builtin (`string`, `int`, `float`, `bool`, `array`, `object`, `callable`, `resource`, `iterable`) or an existing class/interface name |
| `Min`, `Max`                      | bound must not be `NAN`                         |
| `InRange`                         | `min <= max`; if equal, no exclusive flag; neither bound may be `NAN` |
| `InList`                          | at least one allowed value                      |
| `DateFormat`                      | format string must be non-empty and must not contain an unescaped `!` or `\|` (a backslash-escaped `\!`/`\|` is a literal character and is accepted) |
| `AnyOf`, `AllOf`                  | at least one child constraint                   |
| `InEnum`                          | class must be an existing enum (`enum_exists`)  |

```php
new MinLength(-1);            // ConstraintConfigurationException
new DivisibleBy(0);           // ConstraintConfigurationException
new Pattern('not-a-regex');   // ConstraintConfigurationException
new ElementType('integer');   // ConstraintConfigurationException -- 'integer' is a PHP type alias, not a builtin name
new InRange(100, 1);          // ConstraintConfigurationException
```

## Exception hierarchy

Two independently catchable categories separate *what went wrong* from *who's at fault*:

```
\RuntimeException
  └── StrongTypeException           -- a validated VALUE was invalid (runtime input)
        ├── ConstraintViolationException  -- a constraint attribute rejected the value
        └── FormatException               -- a manual parse step rejected the string shape

\LogicException
  ├── ConstraintConfigurationException  -- a TYPE or CONSTRAINT is mis-defined (programmer error)
  └── \BadMethodCallException           -- a factory is intentionally unavailable (FixedSizeArray::tryFrom()/nullable())
```

`StrongTypeException` extends `\RuntimeException`: every validation failure is triggered by a caller-supplied runtime value, so `catch (StrongTypeException $e)` (or the broader `catch (\RuntimeException $e)`) uniformly catches every invalid-value case across every type.

`ConstraintConfigurationException` extends `\LogicException` and is the single catchable category for type/constraint mis-definition: an impossible constraint constructor argument (`new DivisibleBy(0)`), a combinator with zero children, [a constraint attached to a value type it cannot engage on](#value-type-applicability), or a `Nullable` pointed at a class that is not a usable concrete strong type (non-`HasEquals` / non-`\JsonSerializable` / non-`\Stringable`, abstract, non-public constructor, or a constructor requiring more than the single value `Nullable` can supply). `catch (ConstraintConfigurationException $e)` (or `catch (\LogicException $e)`) catches every type-definition bug, cleanly separated from `StrongTypeException` in both directions — a `ConstraintConfigurationException` is never also a `StrongTypeException`, and vice versa.

`FixedSizeArray::tryFrom()` / `::nullable()` throw plain `\BadMethodCallException` rather than `ConstraintConfigurationException`: nothing about the type is misconfigured — the single-value factories are intentionally unavailable because `FixedSizeArray` requires a runtime `$size` they cannot supply. `\BadMethodCallException extends \LogicException`, so it still lands in the programmer-error bucket without claiming to be a configuration error.

These are deliberately not caught by `StrongTypeException`. A divisor of zero, a regex that fails to compile, a `#[Positive]` on a string type, or an unavailable factory method is a bug in the type definition or its usage, not a runtime validation failure on a user-supplied value.

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
