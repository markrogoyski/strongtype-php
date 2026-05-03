# Getting Started

Back to [README](../README.md) · [Documentation index](README.md)

This guide takes you from `composer require` to using strong types throughout your domain code. Read it top-to-bottom on a first pass; come back to specific sections (especially [Error handling](#error-handling)) as references.

## Installation

```bash
composer require markrogoyski/strongtype-php
```

**Requirements**

- PHP 8.4 or newer. The library uses class-level `readonly` and `protected(set)` properties, both PHP 8.4 features.
- Three PHP extensions, all bundled with PHP by default: `ctype`, `filter`, `json`.

To verify the install and confirm your environment is ready:

```bash
php -r "echo PHP_VERSION, PHP_EOL;"                            # expect >= 8.4
php -r "var_dump(extension_loaded('ctype'), extension_loaded('filter'), extension_loaded('json'));"
composer show markrogoyski/strongtype-php                      # confirms the package resolved
```

The library has no PHP-side runtime dependencies beyond those three extensions.

## Your first typed value

Construct a value object — validation runs automatically. There are no extra steps, no separate `validate()` calls, and no opt-in flags.

```php
use StrongType\String\EmailString;

$email = new EmailString('alice@example.com');   // OK
echo $email;                                     // alice@example.com

new EmailString('not-an-email');                 // throws StrongTypeException
```

Once `$email` exists, every downstream caller can trust it's a valid address. There's no need to re-validate.

## Construction, value access, and serialization

Every type validates at construction and is immutable. Invalid values throw `StrongTypeException`.

```php
use StrongType\Int\PositiveInt;
use StrongType\String\EmailString;
use StrongType\Arrays\ArrayOfStrings;
use StrongType\Exception\StrongTypeException;

// Construct -- validates automatically
$age   = new PositiveInt(25);
$email = new EmailString('alice@example.com');
$tags  = new ArrayOfStrings(['php', 'types']);

// Access the value
$age->value;         // 25 (public readonly property)
$age->getValue();    // 25 (getter method)

// String and JSON representations
(string) $age;                // "25"
json_encode($email);          // '"alice@example.com"'
json_encode($tags);           // '["php","types"]'

// Invalid values throw immediately
try {
    new PositiveInt(-1);
} catch (StrongTypeException $e) {
    echo $e->getMessage(); // "PositiveInt type must be > 0, got -1"
}
```

## Type hints throughout your domain code

Use strong types as constructor, parameter, and return types so validation lives at the system boundary and the rest of your code can trust every value:

```php
use StrongType\Int\NonnegativeInt;
use StrongType\String\NonemptyString;
use StrongType\String\EmailString;
use StrongType\Arrays\ArrayOfStrings;

class User
{
    public function __construct(
        public readonly NonemptyString $name,
        public readonly NonnegativeInt $age,
        public readonly EmailString    $email,
        public readonly ArrayOfStrings $roles,
    ) {}
}

// Validation happens at construction -- no manual checks needed
$user = new User(
    new NonemptyString('Alice'),
    new NonnegativeInt(30),
    new EmailString('alice@example.com'),
    new ArrayOfStrings(['admin', 'editor']),
);
```

The intent is to push parsing and validation to the edges of your system — HTTP request decoding, database loading, message deserialization, CLI argument parsing — and let the rest of the code work with already-validated values.

## Error handling

`StrongType\Exception\StrongTypeException` is the umbrella for all validation failures and remains the right type to catch when you want to handle any invalid value uniformly. It has two subclasses for code that wants to distinguish between failure modes:

| Failure mode | Result |
| --- | --- |
| Constructor argument type mismatch (e.g. `new PositiveInt('5')`) under `declare(strict_types=1)` | PHP `\TypeError` |
| `tryFrom()` with a type-mismatched argument | returns `null` |
| Constraint attribute fails during validation | `ConstraintViolationException` |
| Manual parse fails in `DateString` / `TimeString` | `FormatException` |

> **Strict types matter.** PHP only raises `\TypeError` for `new PositiveInt('5')` when the **caller's** file declares `declare(strict_types=1);`. Without it, PHP coerces `'5'` to `5` before the constructor runs and the value passes through validation. The library files all declare strict types; your application code should too. Add `declare(strict_types=1);` to every file that constructs strong types so type mismatches surface immediately rather than silently coercing.

Both `ConstraintViolationException` and `FormatException` extend `StrongTypeException`, so existing `catch (StrongTypeException $e)` blocks continue to catch every validation failure. Catch a subclass when you want to react specifically to a constraint violation or a format-parse failure:

```php
use StrongType\DateTime\DateString;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Exception\FormatException;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\PositiveInt;

try {
    new PositiveInt(-1);
} catch (ConstraintViolationException $e) {
    // Constraint attribute rejected the value.
    echo $e->getMessage(); // "PositiveInt type must be > 0, got -1"
}

try {
    new DateString('not-a-date');
} catch (FormatException $e) {
    // Hand-rolled parser rejected the string shape.
}

try {
    new PositiveInt(-1);
} catch (StrongTypeException $e) {
    // Catches both subclasses.
}
```

## `tryFrom`, `nullable`, and `equals`

Three helpers cover the common patterns for working with strong types defensively, working with optional values, and comparing instances.

**`tryFrom`** — return an instance or `null` instead of throwing. Useful when you already have a value of the right PHP type and want to branch on validity rather than catch an exception:

```php
use StrongType\Int\PortNumber;

$port = PortNumber::tryFrom($parsedPort); // $parsedPort is an int
if ($port === null) {
    // Out of range — render an error or fall back to a default.
}
```

`tryFrom` is type-strict and does **not** parse — `PortNumber::tryFrom('80')` returns `null`, not a coerced instance. Inputs that arrive as strings (HTTP query parameters, CLI arguments, JSON payloads) need to be cast or parsed by your own boundary code before being handed to `tryFrom`:

```php
use StrongType\Int\PortNumber;

$raw = $_GET['port'] ?? '';
$port = \filter_var($raw, FILTER_VALIDATE_INT);
$port = $port === false ? null : PortNumber::tryFrom($port);
if ($port === null) {
    // Either not an integer, or out of port range.
}
```

The one exception to strict matching is `FloatingPoint::tryFrom`, which accepts `int` and widens it to `float` to mirror PHP's native int-to-float parameter widening.

**`nullable`** — wrap a value (or `null`) in a `Nullable<T>` that validates the type even when the value is absent:

```php
use StrongType\String\EmailString;

$contact = EmailString::nullable($maybeEmail); // Nullable<EmailString>
if ($contact->isNull()) {
    // No email on file.
}
```

**`equals`** — type-strict structural equality:

```php
use StrongType\String\UuidString;

$a = new UuidString('550e8400-e29b-41d4-a716-446655440000');
$b = new UuidString('550e8400-e29b-41d4-a716-446655440000');
$a->equals($b); // true
```

For the full reference (including `equalsUnordered` on `ArrayType` and the `withValues` factory), see [Nullable & base-class helpers](nullable-and-helpers.md).

## What next

- Browse the catalog: [built-in types](built-in-types.md).
- Compose your own: [defining types](defining-types.md).
- Learn the rules: [library semantics](semantics.md).

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
