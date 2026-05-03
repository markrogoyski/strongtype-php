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

`Nullable` validates the wrapped type at construction. Passing an abstract class, a non-existent class, or a class that does not implement both `HasEquals` and `\JsonSerializable` throws `\LogicException` immediately — even when `$value` is `null`. This is a programmer-error fail-fast, not a value-validation failure, which is why it is a `\LogicException` rather than a `StrongTypeException` subclass.

`PositiveInt::nullable($value)` is a convenience shortcut for `new Nullable(PositiveInt::class, $value)`; every base class exposes the same factory.

## Base class methods

Base classes provide these helpers (array-only helpers are prefixed with `ArrayType::`):

| Method | Returns | Description |
| --- | --- | --- |
| `tryFrom(mixed $value)` | `static \| null` | Returns an instance or `null` if the input is the wrong PHP type or fails validation. Does **not** widen across scalar types (strict matching) — except `FloatingPoint::tryFrom` accepts `int` and widens to `float`, mirroring PHP's native int-to-float param coercion. |
| `equals(HasEquals $other)` | `bool` | Strict structural equality: same concrete class and same value (`ArrayType::equals` compares values **and** key-order, since insertion order is part of the array identity). |
| `ArrayType::equalsUnordered(HasEquals $other)` | `bool` | Multiset equality: same concrete subclass and same values (each repeated the same number of times) regardless of keys or insertion order. Strict element comparison; nested arrays are compared as-is. Only defined on `ArrayType`. |
| `nullable(mixed $value)` | `Nullable` | Convenience shortcut for `new Nullable(static::class, $value)`. |
| `ArrayType::withValues(array $values)` | `static` | Returns a new instance of the same concrete subclass with a different value array. Only defined on `ArrayType`. |

```php
use StrongType\Int\PositiveInt;

$a = PositiveInt::tryFrom(5);     // PositiveInt(5)
$b = PositiveInt::tryFrom(-1);    // null (constraint failure)
$c = PositiveInt::tryFrom('5');   // null (wrong type — no coercion)

$a->equals(new PositiveInt(5));   // true
$a->equals(new PositiveInt(6));   // false

PositiveInt::nullable(null);      // Nullable<PositiveInt>(null)
```

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

`FixedSizeArray::tryFrom` and `FixedSizeArray::nullable` throw `\LogicException` — the required `$size` parameter cannot be satisfied through these factories. Use `new FixedSizeArray($values, $size)` or `$existing->withValues($values)` instead. See [architecture.md](architecture.md#why-some-types-keep-manual-constructors) for the rationale.

## Implemented interfaces

Every strong type implements a small, stable set of standard interfaces so it can interoperate with native PHP language features and generic code:

| Interface | Where | What you get |
| --- | --- | --- |
| `\Stringable` | All types | `__toString()` — cast any strong type to `string` (integers/floats/bools use `strval`; strings pass through; arrays and datetimes JSON-encode). |
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
