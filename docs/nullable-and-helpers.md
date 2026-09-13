# Nullable and Base-Class Helpers

Back to [README](../README.md) · [Documentation index](README.md)

Every base class ships a small, uniform set of factory methods (`tryFrom`, `nullable`, plus `withValues` on `ArrayType`), an `equals()` contract via the `HasEquals` interface, and a handful of standard PHP interfaces that let strong types interoperate with native language features. The `Nullable` wrapper opts a strong type into representing the absence of a value while preserving the same equality and serialization semantics.

## Nullable wrapper

`Nullable` requires a concrete StrongType class — the wrapped value is validated against that type whenever it is non-null, and the type itself is checked even when the value is `null`.

```php
use StrongType\Nullable;
use StrongType\Int\PositiveInt;

$value = new Nullable(PositiveInt::class, 5);    // wraps PositiveInt(5)
$null  = new Nullable(PositiveInt::class, null);  // wraps null

$value->getValue();  // 5
$null->getValue();   // null
$null->isNull();     // true

// Structural equality delegates to the wrapped type's equals().
$value->equals(new Nullable(PositiveInt::class, 5));    // true
$value->equals(new Nullable(PositiveInt::class, null)); // false

// Nullable itself implements HasEquals, so it can flow through generic code
// that accepts any strong type. Cross-comparison with a non-Nullable is always false.
$value->equals(new PositiveInt(5));                     // false (Nullable<PositiveInt> != PositiveInt)
```

`Nullable` validates the wrapped type at construction. Passing an abstract class, a non-existent class, a class whose constructor is not public, or a class that does not implement all three of `HasEquals`, `\JsonSerializable`, and `\Stringable` throws `StrongType\Exception\ConstraintConfigurationException` immediately — even when `$value` is `null`. So does wrapping a type whose constructor *requires* more than the single value `Nullable` can supply — `FixedSizeArray`, which also takes a `$size`. (Only required parameters count: a constructor with optional or variadic extras is still wrappable.) These are programmer-error fail-fasts, not value-validation failures, which is why they are `ConstraintConfigurationException` (a `\LogicException`) rather than a `StrongTypeException` subclass.

The arity check is a reflection precheck on the wrapped class's own constructor, not a `catch` around the instantiation. An `\ArgumentCountError` raised from *inside* an otherwise wrappable one-argument constructor is a different bug and propagates unchanged rather than being relabelled as a `Nullable` configuration error.

```php
use StrongType\Nullable;
use StrongType\Arrays\FixedSizeArray;
use StrongType\Int\Integer;

new Nullable(Integer::class, 5);            // ConstraintConfigurationException — abstract
new Nullable('NoSuchClass', 5);             // ConstraintConfigurationException — not a strong type
new Nullable(FixedSizeArray::class, [1, 2]); // ConstraintConfigurationException — constructor needs $size
new Nullable(FixedSizeArray::class, null);   // ConstraintConfigurationException — same, even for null
```

`PositiveInt::nullable($value)` is a convenience shortcut for `new Nullable(PositiveInt::class, $value)`; every base class exposes the same factory.

### `getValue()` vs `unwrap()`

`Nullable` offers two accessors, and they return different things:

| Method | Returns | Use when |
| --- | --- | --- |
| `getValue()` | the wrapped value **serialized to a primitive** (`$strongType->jsonSerialize()`), or `null` | you want the plain `int` / `string` / `bool` / `array` behind the wrapper |
| `unwrap()` | the wrapped **strong type object** — `(\JsonSerializable&\Stringable&HasEquals)\|null` | you need the object itself: its own accessors, `equals()`, or its string form |

```php
use StrongType\Nullable;
use StrongType\Arrays\ArrayOfStrings;
use StrongType\String\EmailString;

$email = new Nullable(EmailString::class, 'ops@example.com');

$email->getValue();          // 'ops@example.com' — the primitive
$email->unwrap();            // EmailString('ops@example.com') — the object
$email->unwrap()?->equals(new EmailString('ops@example.com')); // true

$roles = new Nullable(ArrayOfStrings::class, ['admin', 'auditor']);
$roles->unwrap()?->getValues();  // ['admin', 'auditor']
$roles->unwrap()?->count();      // 2 — reachable only through the object

(new Nullable(EmailString::class, null))->unwrap();  // null
```

`unwrap()` is declared with the intersection type `(\JsonSerializable&\Stringable&HasEquals)|null` rather than `?HasEquals`, so `jsonSerialize()`, `__toString()`, and `equals()` are all reachable without a narrowing cast. Reaching a type-specific method such as `getValues()` or `count()` still needs an `instanceof` check (or a cast) to satisfy static analysis, since `Nullable` is not parameterised by the wrapped class.

There is deliberately no single unified value accessor across the library: scalar types expose `getValue()` and array types expose `getValues()`, returning different shapes. `unwrap()->getValue()` / `unwrap()->getValues()` covers both without collapsing that distinction.

## Base class methods

Base classes provide these helpers (array-only helpers are prefixed with `ArrayType::`):

| Method | Returns | Description |
| --- | --- | --- |
| `tryFrom(mixed $value)` | `static \| null` | Returns an instance or `null` if the input is the wrong PHP type or fails validation. Does **not** widen across scalar types (strict matching) — except `FloatingPoint::tryFrom` accepts `int` and widens to `float`, mirroring PHP's native int-to-float param coercion. |
| `equals(HasEquals $other)` | `bool` | Strict structural equality: same concrete class and same value (`ArrayType::equals` compares values **and** key-order, since insertion order is part of the array identity). |
| `ArrayType::equalsUnordered(HasEquals $other)` | `bool` | Multiset equality: same concrete subclass and same values (each repeated the same number of times) regardless of keys or insertion order. Strict element comparison, except that two top-level `NAN` floats count as the same element — an element-comparison difference from `equals()` on top of ignoring keys and order (see [semantics.md](semantics.md#equality-semantics)); nested arrays are compared as-is. Only defined on `ArrayType`. |
| `nullable(mixed $value)` | `Nullable` | Convenience shortcut for `new Nullable(static::class, $value)`. |
| `ArrayType::withValues(array $values)` | `static` | Returns a new instance of the same concrete subclass with a different value array. Only defined on `ArrayType`. |
| `__serialize()` / `__unserialize()` | `array` / `void` | Implement native PHP `serialize()`/`unserialize()` safely: unserializing re-validates through the constructor, so a tampered or hand-crafted payload throws instead of silently producing an invalid instance. See [architecture.md](architecture.md#serialization) for how, and the subclassing obligation this places on extra constructor state. |

```php
use StrongType\Int\PositiveInt;

$a = PositiveInt::tryFrom(5);     // PositiveInt(5)
$b = PositiveInt::tryFrom(-1);    // null (constraint failure)
$c = PositiveInt::tryFrom('5');   // null (wrong type — no coercion)

$a->equals(new PositiveInt(5));   // true
$a->equals(new PositiveInt(6));   // false

PositiveInt::nullable(null);      // Nullable<PositiveInt>(null)
```

`tryFrom` only swallows *validation* failures into `null` — a type whose constraint attributes are themselves misconfigured (e.g. `#[Positive]` on a `StringType`) still throws `ConstraintConfigurationException` from `tryFrom`, same as `new`. A programmer error in the type definition is not something a value-level API should hide behind `null`.

```php
use StrongType\Arrays\ListArray;

// equalsUnordered — same multiset of values, any order or keys.
$shipped  = new ListArray([101, 204, 309]);
$received = new ListArray([309, 101, 204]);

$shipped->equals($received);          // false — order differs
$shipped->equalsUnordered($received); // true  — same multiset

// Multiplicity matters: duplicates must match.
$a = new ListArray([1, 2, 2, 3]);
$b = new ListArray([1, 1, 2, 3]);
$a->equalsUnordered($b);              // false — different multiset
```

`FixedSizeArray::tryFrom` and `FixedSizeArray::nullable` throw `\BadMethodCallException` — the required `$size` parameter cannot be satisfied through these factories. Use `new FixedSizeArray($values, $size)` or `$existing->withValues($values)` instead. See [architecture.md](architecture.md#why-some-types-keep-manual-constructors) for the rationale.

```php
use StrongType\Int\PositiveInt;
use StrongType\Nullable;

$original = new PositiveInt(5);
$restored = unserialize(serialize($original));   // revalidates through the constructor
$original->equals($restored);                    // true

// A tampered payload is rejected rather than silently producing an invalid instance.
$tampered = str_replace('i:5;', 'i:-5;', serialize($original));
unserialize($tampered);                           // throws StrongTypeException

// Nullable serializes the wrapped object itself, so its own validation reruns too.
unserialize(serialize(new Nullable(PositiveInt::class, 5)));   // Nullable<PositiveInt>(5)
```

`Nullable` additionally requires the wrapped type to be safely serializable in its own right: the wrapped class must implement `__serialize()`/`__unserialize()` itself (every built-in base does) and must not also implement the legacy `\Serializable` interface (which a crafted payload could select instead, regardless of a safe `__unserialize()` sitting next to it). A custom wrapped type that doesn't meet this is still fully usable through `Nullable` — construction, `equals()`, `getValue()`/`unwrap()`, JSON — it just cannot be serialized, since `serialize()`/`unserialize()` would have no way to guarantee that type's own constructor invariants are re-checked on the way back in. The check only applies when there's an actual wrapped object to protect: a `Nullable` currently holding `null` serializes fine regardless of what `$type` names, since there is no wrapped instance for an unsafe reconstruction to corrupt.

```php
use StrongType\Nullable;

// A custom wrapped type without its own __serialize()/__unserialize().
new Nullable(MyCustomType::class, $value);            // works fine
serialize(new Nullable(MyCustomType::class, $value));  // throws ConstraintConfigurationException
serialize(new Nullable(MyCustomType::class, null));     // fine -- nothing wrapped to protect
```

## Implemented interfaces

Every strong type implements a small, stable set of standard interfaces so it can interoperate with native PHP language features and generic code:

| Interface | Where | What you get |
| --- | --- | --- |
| `\Stringable` | All types | `__toString()` — cast any strong type to `string` (integers use `strval`; bools render as `'true'`/`'false'`; floats render through `json_encode`; strings and datetimes pass through verbatim; arrays JSON-encode). |
| `\JsonSerializable` | All types | `jsonSerialize()` — `json_encode($value)` produces the underlying scalar/array. |
| `\StrongType\HasEquals` | All types and `Nullable` | `equals(HasEquals $other): bool` — strict structural equality. Lets generic code compare two strong-type instances without knowing the concrete type. Type-hint against `HasEquals` when you want to accept "any strong type" (including a `Nullable`) in a signature. |
| `\Countable` | `ArrayType` only | `count($arr)` returns the element count. |
| `\IteratorAggregate` | `ArrayType` only | `foreach ($arr as $key => $value) { ... }` iterates the underlying values, preserving original keys (via `\ArrayIterator`). |

```php
use StrongType\Arrays\ArrayOfStrings;

$tags = new ArrayOfStrings(['php', 'types', 'validation']);

\count($tags);              // 3 (Countable)
foreach ($tags as $tag) {   // IteratorAggregate
    echo $tag, "\n";
}

(string) $tags;             // '["php","types","validation"]' (Stringable)
\json_encode($tags);        // '["php","types","validation"]' (JsonSerializable)
```

```php
use StrongType\HasEquals;

// Accept any strong type in a generic signature.
function areEqual(HasEquals $a, HasEquals $b): bool
{
    return $a->equals($b);
}
```

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
