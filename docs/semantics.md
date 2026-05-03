# Library Semantics

Back to [README](../README.md) · [Documentation index](README.md)

A few cross-cutting policies govern every type and constraint in the library. These are the rules to keep in mind when picking a built-in type or designing your own. Each section describes a deliberate design choice — these are not implementation details that may change.

## Format vs. registry validation

Types whose values come from external registries — `CountryCodeAlpha2String`, `CountryCodeAlpha3String`, `CurrencyCodeString`, `LanguageCodeString`, `MimeTypeString` — validate **format only**. They confirm the input matches the documented shape (e.g. `[A-Z]{2}` for ISO 3166-1 alpha-2) but do not check membership in the active registry. Unassigned codes such as `ZZ` or `XYZ` are accepted. Registries change; format does not, and embedding a frozen snapshot would silently rot. If you need active-registry membership, layer it on with a custom constraint that consults your own source of truth.

`Rfc3339` and `Rfc3339DateTimeString` similarly accept `:60` seconds syntactically (leap-second tolerance per the grammar) without verifying the minute is an actual IERS-inserted leap second.

## Byte length vs. Unicode length

`MinLength` and `MaxLength` count **bytes**, not Unicode characters or grapheme clusters. They are implemented with `strlen()`. A multibyte character such as `é` (two bytes in UTF-8) consumes two units against the bound, and an emoji like `🎉` (four bytes) consumes four. This matches what most byte-oriented protocols, database columns, and storage limits actually care about, and it stays predictable across PHP installs that may or may not have `mbstring` / `intl` extensions enabled.

If you need character or grapheme counting, write a [custom constraint](defining-types.md#custom-constraints) over `mb_strlen()` or `grapheme_strlen()`. The same applies to the equivalent array-side bounds (`MinCount`, `MaxCount`, `ExactCount`), which always count elements via `count()` regardless of key type.

## First-error behavior

When multiple constraints apply to a type, they run in priority order (see [Priority Ordering](defining-types.md#priority-ordering)) and validation **stops at the first failing constraint**. The thrown `ConstraintViolationException` carries that constraint's message; later constraints are not consulted, so you will not see an aggregated multi-error report.

This keeps error messages targeted and avoids cascading reports that are often artifacts of an earlier failure (e.g. a `Pattern` complaint about an empty string when `Nonempty` already caught it). If you need to collect every violation, validate by calling each constraint individually rather than relying on construction.

## Equality semantics

`equals()` is **type-strict**: comparing two strong types returns `true` only when the concrete subclass on both sides is identical. A `PositiveInt(5)` is not equal to an `Integer(5)` of a different subclass, and a `Nullable<PositiveInt>` is never equal to a bare `PositiveInt`.

For `ArrayType::equals`, insertion order and keys are part of the value: `['a' => 1, 'b' => 2]` is **not** equal to `['b' => 2, 'a' => 1]`, and `[1, 2, 3]` is not equal to `[3, 2, 1]`. Use [`ArrayType::equalsUnordered`](nullable-and-helpers.md#base-class-methods) when you want multiset equality (same values, ignoring keys and order). Both forms compare values with strict (`===`) equality and recurse structurally into nested arrays.

## Shallow array validation

`ElementType` validates **direct children only** — it does not recurse into nested arrays. `#[ElementType('array')]` accepts `[[1, 'two', 3.0], ['a', null]]` because every top-level element is itself an array; the mixed types inside the nested arrays are not inspected. Likewise `Unique` checks uniqueness at the top level only.

To validate nested element types, wrap the inner arrays in their own strong type. For example, an `ArrayType` of `ArrayOfInts` enforces "list of int-only lists" by validating at each level explicitly rather than depending on a recursive descent the constraint system intentionally does not provide.

## Mutability

Every strong type is **immutable** once constructed. There are no setters, no in-place transforms, and no mutating array methods. The intended workflow is:

1. Construct at the system boundary (HTTP input, database load, message deserialization, CLI args).
2. Pass the typed value through your code via parameter and return types.
3. Trust the value everywhere it appears without re-validating.

When you need a different value, construct a new instance. `ArrayType::withValues($values)` is provided as a convenience for producing a same-type instance with a different array; use the constructor directly for everything else. This design keeps the validate-once-trust-everywhere contract intact across the lifetime of the value.

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
