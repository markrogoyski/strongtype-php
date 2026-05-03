# StrongType PHP Documentation

Back to [Project README](../README.md)

This index is the front door for the deep documentation. The top-level [README](../README.md) gives a hero pitch, a catalog spine, and quick-reference tables; everything past that lives here. Pick a learning path below if you're new, or jump directly to a reference if you know what you need.

## Quick navigation

### New users
- **[Getting started](getting-started.md)** — install, construct your first typed value, use strong types as parameter and return types, handle errors.

### Reference
- **[Built-in types](built-in-types.md)** — every concrete type (integers, floats, strings, arrays, booleans, datetimes) with an example per row.
- **[Constraint attributes](constraint-attributes.md)** — every constraint attribute (numeric, string, array, datetime, enum, combinators) with an example per row.
- **[Nullable & base-class helpers](nullable-and-helpers.md)** — `Nullable` wrapper, `tryFrom`, `equals`, `equalsUnordered`, `withValues`, implemented standard interfaces.

### Guides
- **[Defining your own types](defining-types.md)** — declarative composition, inheritance, priority ordering, custom constraints, combinators, wrapping enums.
- **[Library semantics](semantics.md)** — cross-cutting policies: format-vs-registry, byte-vs-Unicode length, first-error behavior, equality semantics, shallow array validation, mutability.

### Internals
- **[Architecture](architecture.md)** — how the constraint system works, why some types keep manual constructors, the consistent-constructor contract, constructor invariants.

---

## Learning paths

Three named paths through the docs depending on where you're starting.

### Beginner path

You've just installed the library and want to use built-in types in real code.

1. [Install and construct your first typed value](getting-started.md#installation)
2. [Use strong types throughout your domain code](getting-started.md#type-hints-throughout-your-domain-code)
3. [Browse the catalog](built-in-types.md) and pick a type for each domain concept.
4. [Catch validation errors](getting-started.md#error-handling) and decide whether to use `tryFrom` or `try/catch`.

### Intermediate path

You've used the built-ins and now want a type the library doesn't ship.

1. [Define a typed value with attributes](defining-types.md#defining-a-typed-value-with-attributes)
2. [Stack multiple attributes and inherit from another type](defining-types.md#inheriting-constraints)
3. [Understand priority ordering](defining-types.md#priority-ordering) so your validation runs in the right order.
4. [Compose with `AnyOf` / `AllOf` / `Not`](defining-types.md#combinators-anyof-allof-not) for disjunction, conjunction, and inversion.

### Advanced path

You're writing your own constraints or working with edge cases.

1. [Write a custom constraint](defining-types.md#custom-constraints) implementing `ConstraintInterface`.
2. [Wrap an enum with `InEnum`](defining-types.md#wrapping-enums-with-inenum) for string-backed, int-backed, or pure enums.
3. [Use `Nullable` to model optional values](nullable-and-helpers.md#nullable-wrapper) without losing type identity.
4. [Read the semantics doc](semantics.md) — byte-vs-Unicode length, format-vs-registry, shallow array validation, equality semantics — to avoid surprises on edge cases.
5. [Skim the architecture doc](architecture.md) — how the constraint cache works, when to override factories, the constructor-invariants table.

---

## Quick reference: by use case

Real reader intents mapped to the right doc page.

- **I want to validate user input at an HTTP boundary** → [type hints throughout your domain code](getting-started.md#type-hints-throughout-your-domain-code) + browse the [string catalog](built-in-types.md#strings) for `EmailString`, `UrlString`, `UuidString`.
- **I want to model a domain concept** → [defining your own types](defining-types.md), then the [domain modeling examples](defining-types.md#real-world-domain-modeling).
- **I want to add a validation rule the library doesn't ship** → [custom constraints](defining-types.md#custom-constraints) (Palindrome, Luhn examples).
- **I want to handle null safely** → [Nullable wrapper](nullable-and-helpers.md#nullable-wrapper).
- **I want to catch a specific failure mode** → [error handling](getting-started.md#error-handling).
- **I want to constrain a value to enum cases** → [wrapping enums with `InEnum`](defining-types.md#wrapping-enums-with-inenum).
- **I want to combine "either of these" / "all of these" / "not this" rules** → [combinators](defining-types.md#combinators-anyof-allof-not).
- **I want to know why my type is rejecting valid-looking input** → [library semantics](semantics.md), especially [byte length](semantics.md#byte-length-vs-unicode-length) and [format vs. registry validation](semantics.md#format-vs-registry-validation).

---

## Documentation index

Flat list of every doc with a one-line summary.

- **[getting-started.md](getting-started.md)** — install, construction, value access, type hints, error handling, `tryFrom` / `nullable` / `equals` teasers.
- **[built-in-types.md](built-in-types.md)** — the catalog of integer, float, string, array, boolean, and datetime types.
- **[constraint-attributes.md](constraint-attributes.md)** — the catalog of constraint attributes and the priority bands table.
- **[defining-types.md](defining-types.md)** — composition, inheritance, priority, combinators, custom constraints, wrapping enums, real-world modeling.
- **[nullable-and-helpers.md](nullable-and-helpers.md)** — `Nullable` wrapper, base-class methods, implemented interfaces.
- **[semantics.md](semantics.md)** — cross-cutting policies governing every type and constraint.
- **[architecture.md](architecture.md)** — internal mechanics: how constraints are resolved, manual-constructor exceptions, consistent-constructor contract, constructor invariants.

---

**Back to [Project README](../README.md)**
