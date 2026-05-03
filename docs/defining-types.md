# Defining Your Own Types

Back to [README](../README.md) · [Documentation index](README.md)

The attribute-based constraint system lets you define new validated types declaratively by stacking constraint attributes on a class. No constructor needed — constraints compose automatically. This document covers everything from the simplest single-attribute class to writing your own constraints, combining them with `AnyOf` / `AllOf` / `Not`, and wrapping enums.

## Why declarative composition

Hand-written validation in constructors works, but it has a few persistent costs:

- Every new validated type means new constructor boilerplate. Two types that share a rule (say, "non-empty plus an uppercase ISO-3166 alpha-2 shape") repeat the same checks twice.
- Reusing validation across the type hierarchy is awkward. Inheriting a constructor and then layering another check on top of it forces every subclass to know about every parent rule.
- The validation rules aren't visible in the class signature. Reading a `class CountryCode extends StringType` doesn't tell you what makes a country code valid.

Constraint attributes solve all three. The rules live next to the class definition, every constraint composes automatically along the inheritance chain, and shared rules are reusable attributes rather than duplicated control flow. Most concrete types in this library are a single line of attributes plus an empty class body.

## Defining a typed value with attributes

Extend any base type and add constraint attributes:

```php
use StrongType\Int\Integer;
use StrongType\Float\FloatingPoint;
use StrongType\String\StringType;
use StrongType\Arrays\ArrayType;
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, MaxLength, Unique, ElementType, Positive};

// Numeric ranges
#[Min(1), Max(65535)]
readonly class Port extends Integer {}

#[Min(200), Max(999)]
readonly class AreaCode extends Integer {}

#[Positive]
readonly class Price extends FloatingPoint {}

#[Min(0.0), Max(1.0)]
readonly class Probability extends FloatingPoint {}

// String formats
#[Nonempty, Pattern('/^[a-z0-9]+(-[a-z0-9]+)*$/'), MaxLength(255)]
readonly class Slug extends StringType {}

#[Nonempty, Pattern('/^[A-Z]{2}$/')]
readonly class CountryCode extends StringType {}

#[Nonempty, Pattern('/^[A-Z0-9]{6,10}$/')]
readonly class TrackingNumber extends StringType {}

// Array shapes
#[Nonempty, Unique, ElementType('string')]
class TagSet extends ArrayType {}

#[Nonempty, ElementType('int'), Unique]
class UniqueIdList extends ArrayType {}
```

> **Note:** scalar subclasses (`Integer`, `FloatingPoint`, `StringType`, `BoolType`, `DateTime`) are declared `readonly`, but `ArrayType` subclasses are **not** — the base class uses a `protected(set)` property that can't appear inside a `readonly` class. The value remains immutable in practice; you simply omit the keyword.

## Inheriting constraints

Constraints are inherited through the class hierarchy. Child classes get all parent constraints plus their own:

```php
use StrongType\Constraint\{DivisibleBy, Min, Max};
use StrongType\Int\Integer;

#[Min(1), Max(1000)]
readonly class OrderQuantity extends Integer {}

// Wholesale ships in dozens. Inherits Min(1) and Max(1000) from OrderQuantity, adds DivisibleBy(12).
#[DivisibleBy(12)]
readonly class CasePackQuantity extends OrderQuantity {}

new CasePackQuantity(24);   // OK -- divisible by 12 and in range
new CasePackQuantity(13);   // StrongTypeException -- not divisible by 12
new CasePackQuantity(1008); // StrongTypeException -- exceeds Max(1000) (inherited)
```

This also works with the built-in types, since they use constraints themselves:

```php
use StrongType\Constraint\Max;
use StrongType\Int\PositiveInt;

// PositiveInt already has #[Positive], so PageSize inherits > 0
#[Max(100)]
readonly class PageSize extends PositiveInt {}

new PageSize(50);  // OK
new PageSize(0);   // StrongTypeException -- not positive (inherited from PositiveInt)
new PageSize(101); // StrongTypeException -- exceeds max (added by PageSize)
```

## Priority ordering

Constraints execute in priority order (lower runs first). This ensures range checks happen before format checks, and format checks before semantic checks:

| Priority | Category | Attributes |
| --- | --- | --- |
| 40 | Domain default | `Finite` (auto-applied to every `FloatingPoint` subtype) |
| 50 | Range / size | `Min`, `Max`, `InRange`, `Positive`, `Negative`, `Nonnegative`, `Nonpositive`, `Nonzero`, `Even`, `Odd`, `DivisibleBy`, `Nonempty`, `MinLength`, `MaxLength`, `MinCount`, `MaxCount`, `ExactCount`, `IsEmpty`, `IsList`, `IsAssociative` |
| 60 | Content | `Nonblank`, `InList`, `InEnum` |
| 100 | Format | `Pattern`, `NotPattern`, `Alpha`, `Alphanumeric`, `Lowercase`, `Uppercase`, `NumericDigits`, `HexDigits`, `Base64`, `Unique`, `ElementType`, `DateFormat`, `TimeFormat`, `InFuture`, `InPast` |
| 150 | Semantic | `Email`, `Url`, `IpAddress`, `Ipv4`, `Ipv6`, `Cidr`, `Uuid`, `Json`, `Rfc3339`, `ClassExists`, `DateTimeParseable` |
| 200+ | Custom | User-defined constraints (`Palindrome`, `Luhn`, etc.) — see [Custom constraints](#custom-constraints) |
| min(children) | Combinator | `AnyOf`, `AllOf`, `Not` — execute at the lowest priority among their children |

The priority bands are not arbitrary: a `Pattern` complaint about an empty string is rarely useful when `Nonempty` already caught it, and a `Json` complaint about a malformed string is rarely useful when `MaxLength` already caught it. Running cheap structural checks before expensive format/semantic checks also keeps the failure path fast.

## Combinators: `AnyOf`, `AllOf`, `Not`

The `AnyOf`, `AllOf`, and `Not` combinators are themselves constraints, so they compose with every built-in or user-defined `ConstraintInterface` — including each other. Children are passed as constructor arguments using `new`, which PHP allows in attribute argument expressions.

**`AnyOf` — at least one child must pass.** Useful for disjoint-but-valid ranges, alternate formats, or "either of these patterns":

```php
use StrongType\Constraint\{AnyOf, InRange};
use StrongType\Int\Integer;

// A port that is either well-known (1–1023) or in the IANA dynamic/private range (49152–65535).
#[AnyOf(new InRange(1, 1023), new InRange(49152, 65535))]
readonly class WellKnownOrEphemeralPort extends Integer {}

new WellKnownOrEphemeralPort(80);    // OK
new WellKnownOrEphemeralPort(50000); // OK
new WellKnownOrEphemeralPort(8080);  // StrongTypeException -- "must satisfy one of: [...]"
```

When every child fails, `AnyOf` emits a composite message: `"{Type} type must satisfy one of: [child1msg | child2msg | …]"`.

**`AllOf` — every child must pass.** Children run in **argument order** — `AllOf` does not sort by priority, so the first child you pass is the first child evaluated. This differs from stacking attributes, which run in priority order. Use `AllOf` when you need a single groupable unit you can nest inside `AnyOf` or `Not`, not as a drop-in replacement for stacked attributes:

```php
use StrongType\Constraint\{AllOf, MinLength, Pattern};
use StrongType\String\StringType;

// Standard env-var shape: at least 2 chars, must start with a letter or underscore,
// then uppercase letters / digits / underscores.
#[AllOf(new MinLength(2), new Pattern('/^[A-Z_][A-Z0-9_]*$/'))]
readonly class EnvVarName extends StringType {}
```

The first failing child wins; its message is returned verbatim. Order the children intentionally: cheaper checks first, or the message you would prefer to surface first.

**`Not` — invert a single child.** Useful for blocklists or "anything but" rules:

```php
use StrongType\Constraint\{Not, Pattern};
use StrongType\String\StringType;

// Reject all-uppercase words (likely shouting in user-generated content).
#[Not(new Pattern('/^[A-Z]+$/'))]
readonly class NoAllCapsString extends StringType {}

new NoAllCapsString('Hello');  // OK
new NoAllCapsString('HELLO');  // StrongTypeException -- "must NOT satisfy Pattern"
```

`Not`'s error message names the inverted constraint by class (`Pattern`, `Email`, etc.) but does not describe the inverted condition in detail.

**Nesting and composition with non-combinator attributes.** Combinators are constraints, so they nest freely and sit alongside ordinary attributes:

```php
use StrongType\Constraint\{AnyOf, MaxLength, Nonempty, Not, Pattern};
use StrongType\String\StringType;

// A username scoped to a known prefix, between 1 and 20 chars, that is not all digits.
#[
    Nonempty,
    MaxLength(20),
    AnyOf(new Pattern('/^user_/'), new Pattern('/^admin_/')),
    Not(new Pattern('/^\d+$/')),
]
readonly class ScopedUsername extends StringType {}
```

Combinators with zero children (`new AnyOf()`, `new AllOf()`) throw `\LogicException` at the same site as the other [constraint constructor invariants](architecture.md#constructor-invariants).

## Wrapping enums with `InEnum`

`#[InEnum(MyEnum::class)]` constrains a strong type's value to the enum's backing values (for `BackedEnum`) or case names (for pure `UnitEnum`). No `EnumString` / `EnumInt` base class is needed — `InEnum` on a `StringType` or `Integer` subclass is sufficient:

```php
use StrongType\Constraint\InEnum;
use StrongType\String\StringType;

enum Status: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
}

#[InEnum(Status::class)]
readonly class StatusCode extends StringType {}

new StatusCode('active');   // OK
new StatusCode('Active');   // StrongTypeException -- case-sensitive backing-value match
new StatusCode('archived'); // StrongTypeException -- not a Status backing value

// Hand the validated value back to the enum when you need the case object.
$status = Status::from((new StatusCode('pending'))->value);
```

Int-backed and pure (non-backed) enums work the same way:

```php
use StrongType\Constraint\InEnum;
use StrongType\Int\Integer;
use StrongType\String\StringType;

enum Priority: int { case Low = 1; case Medium = 5; case High = 10; }

#[InEnum(Priority::class)]
readonly class PriorityCode extends Integer {}

new PriorityCode(5);   // OK
new PriorityCode(2);   // StrongTypeException

enum Color { case Red; case Green; case Blue; }

// Pure enums match by case name (case-sensitive).
#[InEnum(Color::class)]
readonly class ColorName extends StringType {}

new ColorName('Red');  // OK
new ColorName('red');  // StrongTypeException -- case names are case-sensitive
```

`InEnum` constructed with anything other than an existing enum class — a non-existent class, an interface, or a non-enum class — throws `\LogicException` at the same site as the other [constraint constructor invariants](architecture.md#constructor-invariants).

## Custom constraints

Create your own constraint by implementing `ConstraintInterface`. The interface has just two methods: `validate()` returns `null` on success or an error message string on failure, and `priority()` controls execution order.

### A minimal example: `Palindrome`

```php
use StrongType\Constraint\ConstraintInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Palindrome implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = substr($className, strrpos($className, '\\') + 1);

        if (\is_string($value) && $value !== strrev($value)) {
            return "{$short} type must be a palindrome, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 200; // Custom constraints typically use 200+
    }
}
```

Compose it with built-in constraints:

```php
use StrongType\Constraint\Nonempty;
use StrongType\String\StringType;

#[Nonempty, Palindrome]
readonly class PalindromeString extends StringType {}

new PalindromeString('racecar'); // OK
new PalindromeString('hello');   // StrongTypeException -- not a palindrome
new PalindromeString('');        // StrongTypeException -- empty (Nonempty runs first)
```

### A real-world example: `Luhn` checksum

```php
use StrongType\Constraint\ConstraintInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Luhn implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $digits = str_split(strrev($value));
        $sum = 0;
        foreach ($digits as $i => $digit) {
            $n = (int) $digit;
            if ($i % 2 === 1) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
        }

        if ($sum % 10 !== 0) {
            $short = substr($className, strrpos($className, '\\') + 1);
            return "{$short} type must pass Luhn checksum, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 200; // Runs after format checks
    }
}
```

Use it just like built-in constraints:

```php
use StrongType\Constraint\{Nonempty, Pattern};
use StrongType\String\StringType;

#[Nonempty, Pattern('/^\d{13,19}$/'), Luhn]
readonly class CreditCardNumber extends StringType {}

new CreditCardNumber('4532015112830366'); // OK
new CreditCardNumber('1234567890123456'); // StrongTypeException -- fails Luhn
```

No registration step — the validator automatically discovers any attribute implementing `ConstraintInterface`.

## Real-world domain modeling

The library shines when used to model the vocabulary of a domain. A handful of categories with realistic examples:

```php
use StrongType\Constraint\{Min, Max, Nonempty, Pattern, MaxCount, Positive, Nonnegative, ElementType, Unique};
use StrongType\Int\Integer;
use StrongType\Float\FloatingPoint;
use StrongType\String\StringType;
use StrongType\Arrays\ArrayType;

// E-commerce
#[Positive]
readonly class Quantity extends Integer {}

#[Min(0), Max(99, exclusive: true)]
readonly class DiscountPercent extends Integer {}

#[Nonnegative]
readonly class Money extends FloatingPoint {}

#[Nonempty, Pattern('/^[A-Z]{3}$/')]
readonly class CurrencyCode extends StringType {}

#[Nonempty, Pattern('/^SKU-[A-Z0-9]{8}$/')]
readonly class Sku extends StringType {}

// Networking
#[Min(1), Max(65535)]
readonly class Port extends Integer {}

#[Nonempty, Pattern('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/')]
readonly class Hostname extends StringType {}

// Configuration
#[Nonempty, Unique, ElementType('string'), MaxCount(50)]
class FeatureFlags extends ArrayType {}
```

Or build on top of an existing validated type when you only need to layer one extra rule:

```php
use StrongType\Constraint\{Max, DivisibleBy, MaxLength, Pattern};
use StrongType\Int\PositiveInt;
use StrongType\String\NonemptyString;

// Build on existing validated types
#[Max(120)]
readonly class HumanAge extends PositiveInt {}

#[DivisibleBy(100)]
readonly class CentAmount extends PositiveInt {}

#[MaxLength(50), Pattern('/^\S.*\S$/')]
readonly class DisplayName extends NonemptyString {}
```

The pattern repeats: a domain word becomes a class, its rules are written once on the class, and every consumer of that domain word hands a validated instance to the next layer.

---

**Back to [Documentation index](README.md) · [Project README](../README.md)**
