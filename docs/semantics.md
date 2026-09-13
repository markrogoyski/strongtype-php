# Library Semantics

Back to [README](../README.md) · [Documentation index](README.md)

A few cross-cutting policies govern every type and constraint in the library. These are the rules to keep in mind when picking a built-in type or designing your own. Each section describes a deliberate design choice — these are not implementation details that may change.

## Format vs. registry validation

Types whose values come from external registries — `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString` — validate **format only**. They confirm the input matches the documented shape (e.g. `[A-Z]{2}` for ISO 3166-1 alpha-2) but do not check membership in the active registry. Unassigned codes such as `ZZ` or `XYZ` are accepted. Registries change; format does not, and embedding a frozen snapshot would silently rot. If you need active-registry membership, layer it on with a custom constraint that consults your own source of truth.

`Rfc3339` and `Rfc3339DateTimeString` similarly accept `:60` seconds syntactically (leap-second tolerance per the grammar) without verifying the minute is an actual IERS-inserted leap second.

## Clock-relative validation

`FutureTimestamp`, `PastTimestamp`, and the `InFuture`/`InPast` constraints compare the value against `time()` **once, at construction**. That comparison is not re-evaluated later: a `FutureTimestamp` holding a timestamp ten seconds out becomes a timestamp in the past ten seconds after it was built, and the object does not know or care. This is the one place the "validate once, trust everywhere" contract is time-relative rather than absolute — the value stays exactly what it was validated to be, but *what "future" means* moves on without it. If you need a timestamp that is still ahead of `time()` at the moment you read it, re-check `time()` at read time rather than trusting a previously-constructed instance.

## Byte length vs. Unicode length

`MinLength` and `MaxLength` count **bytes**, not Unicode characters or grapheme clusters. They are implemented with `strlen()`. A multibyte character such as `é` (two bytes in UTF-8) consumes two units against the bound, and an emoji like `🎉` (four bytes) consumes four. This matches what most byte-oriented protocols, database columns, and storage limits actually care about, and it stays predictable across PHP installs that may or may not have `mbstring` / `intl` extensions enabled.

If you need character or grapheme counting, write a [custom constraint](defining-types.md#custom-constraints) over `mb_strlen()` or `grapheme_strlen()`. The same applies to the equivalent array-side bounds (`MinCount`, `MaxCount`, `ExactCount`), which always count elements via `count()` regardless of key type.

## First-error behavior

When multiple constraints apply to a type, they run in priority order (see [Priority Ordering](defining-types.md#priority-ordering)) and validation **stops at the first failing constraint**. The thrown `ConstraintViolationException` carries that constraint's message; later constraints are not consulted, so you will not see an aggregated multi-error report.

This keeps error messages targeted and avoids cascading reports that are often artifacts of an earlier failure (e.g. a `Pattern` complaint about an empty string when `Nonempty` already caught it). If you need to collect every violation, validate by calling each constraint individually rather than relying on construction.

## Equality semantics

`equals()` is **type-strict**: comparing two strong types returns `true` only when the concrete subclass on both sides is identical. A `PositiveInt(5)` is not equal to an `Integer(5)` of a different subclass, and a `Nullable<PositiveInt>` is never equal to a bare `PositiveInt`.

For `ArrayType::equals`, insertion order and keys are part of the value: `['a' => 1, 'b' => 2]` is **not** equal to `['b' => 2, 'a' => 1]`, and `[1, 2, 3]` is not equal to `[3, 2, 1]`. Use [`ArrayType::equalsUnordered`](nullable-and-helpers.md#base-class-methods) when you want multiset equality (same values, ignoring keys and order). Both forms compare values with strict (`===`) equality and recurse structurally into nested arrays.

The one exception is `NAN`. `equalsUnordered` treats two `NAN` floats as the same element — the comparison `Unique` already uses for duplicate detection — because `NAN === NAN` is false and a strict match would otherwise make `$a->equalsUnordered($a)` return `false` for an array holding one. The allowance is **top-level only**: a `NAN` nested inside an element array is compared by PHP's array comparison, which is strict.

The two methods already differ by design on keys and order; `NAN` is the one place they differ on the *element comparison itself*, so they disagree even when keys and order match:

```php
use StrongType\Arrays\ListArray;

$a = new ListArray([\NAN]);
$b = new ListArray([\NAN]);

$a->equalsUnordered($b);   // true  — NAN matches NAN
$a->equals($b);            // false — element-wise ===, and NAN === NAN is false
$a->equals($a);            // true  — PHP short-circuits identical arrays

// Nested NAN is not covered by the allowance.
(new ListArray([[\NAN]]))->equalsUnordered(new ListArray([[\NAN]]));  // false
```

**Self-referential arrays never crash a comparison.** PHP's native `===` throws `Error: Nesting level too deep` comparing two *different* arrays that each contain a cycle back to themselves (`$a['self'] = &$a;`) — a legal `ArrayType` value (the `__toString()` fallback renderer already supports it). `equals()` and `equalsUnordered()` catch that `Error` and report `false` rather than let it escape: there is no way to verify whether two such structures are "really" equal without risking the same unbounded recursion, so this only ever avoids the crash — it never claims two arrays are equal without having actually compared them.

`Unique` catches the same `Error` but responds differently, because it is a gatekeeper rather than an informational comparison. Silently treating an unprovable pair as "not duplicates" — the same conservative answer `equals()` gives — would let a genuinely duplicated circular structure pass uniqueness undetected. Instead, `Unique` rejects the value outright with a dedicated message (`"... type must not contain elements whose uniqueness cannot be verified (circular array reference)"`) whenever a pairwise comparison it needs to make is indeterminate.

## Shallow array validation

`ElementType` validates **direct children only** — it does not recurse into nested arrays. `#[ElementType('array')]` accepts `[[1, 'two', 3.0], ['a', null]]` because every top-level element is itself an array; the mixed types inside the nested arrays are not inspected. Likewise `Unique` checks uniqueness at the top level only.

To validate nested element types, wrap the inner arrays in their own strong type. For example, an `ArrayType` of `ArrayOfInts` enforces "list of int-only lists" by validating at each level explicitly rather than depending on a recursive descent the constraint system intentionally does not provide.

## Mutability

Every strong type is **immutable** once constructed. There are no setters, no in-place transforms, and no mutating array methods. Every base class — `Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`, and `ArrayType` — is a `readonly class`, so the validated value is written once in the constructor and any later assignment is a PHP `Error`. That holds from outside the object *and* from inside a subclass method, so a subclass cannot quietly replace a value that has already passed validation.

```php
use StrongType\Arrays\NonemptyArray;

$tags = new NonemptyArray(['php', 'types']);
$tags->values = [];   // Error: Cannot modify readonly property
```

Because PHP forbids a non-readonly class extending a readonly one, your own subclasses must be `readonly class` as well — see [defining-types.md](defining-types.md).

One qualification: PHP allows a readonly property to be *reinitialized inside `__clone()`*, so a subclass that declares `__clone()` can hand out a clone holding a value the constructor never validated. No built-in type declares one, and a subclass of your own shouldn't either — construct a new instance (or use `withValues()`) instead of rewriting state in a clone hook.

The intended workflow is:

1. Construct at the system boundary (HTTP input, database load, message deserialization, CLI args).
2. Pass the typed value through your code via parameter and return types.
3. Trust the value everywhere it appears without re-validating.

When you need a different value, construct a new instance. `ArrayType::withValues($values)` is provided as a convenience for producing a same-type instance with a different array; use the constructor directly for everything else. This design keeps the validate-once-trust-everywhere contract intact across the lifetime of the value.

### Element aliasing

Immutability applies to the **value**, not to what the value points at. Array elements are validated, not copied, so an object or resource element stays reachable — and mutable — through any reference the caller still holds:

```php
use StrongType\Arrays\ArrayOfObjects;

$config = new \stdClass();
$config->debug = false;

$objects = new ArrayOfObjects([$config]);
$config->debug = true;                  // mutates the element in place

$objects->getValues()[0]->debug;        // true — the strong type sees the change
```

This is the same shallow-validation policy described above: the library validates the array it was handed and does not deep-clone it. It matters when a constraint's predicate depends on element *state* rather than element type — a custom constraint asserting something about an object's fields is only true at construction time.

**A by-reference element is aliased the same way, and it defeats even element-type constraints.** PHP arrays copy on assignment, but a slot holding an explicit reference keeps pointing at the caller's variable, so a later write through that variable changes the stored element — including its *type*:

```php
use StrongType\Arrays\ArrayOfInts;

$count = 1;
$counts = new ArrayOfInts([&$count]);   // validates: element is an int

$count = 'not an int';

$counts->getValues();                   // ['not an int'] — #[ElementType('int')] no longer holds
```

This is not specific to arrays of objects: any element passed by reference can be swapped for a value of any type after validation. The library deliberately does not detach references or deep-copy on construction (that would impose a copy on every construction to defend against a construction the caller had to write out of their way), so **passing elements by reference into a strong type voids the validate-once-trust-everywhere guarantee.** Don't do it.

Barring references, an element can only be trusted to stay put if the element itself cannot change: a scalar, or an object whose entire relevant state is immutable. "It's a strong type, so it's readonly" is not enough on its own — an `ArrayOfObjects` is readonly but still holds mutable objects, so nesting one inside another array type inherits the same aliasing. Scalars all the way down is the guarantee; anything else is validated once, not frozen.

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
