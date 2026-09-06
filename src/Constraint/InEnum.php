<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InEnum implements ConstraintInterface
{
    /** @var enum-string */
    private string $enumClass;

    /**
     * @param class-string $enumClass
     */
    public function __construct(string $enumClass)
    {
        if (!\enum_exists($enumClass)) {
            throw new ConstraintConfigurationException("InEnum: class must be an existing enum, got '{$enumClass}'");
        }
        $this->enumClass = $enumClass;
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $cases = $this->cases();

        foreach ($cases as $case) {
            $candidate = $case instanceof \BackedEnum ? $case->value : $case->name;
            if ($candidate === $value) {
                return null;
            }
        }

        $allowed = \implode(', ', \array_map(
            static fn(\UnitEnum $c): string => $c instanceof \BackedEnum
                ? (\is_string($c->value) ? "'{$c->value}'" : (string) $c->value)
                : $c->name,
            $cases,
        ));

        $short    = ShortClassName::of($className);
        $valueStr = \is_scalar($value) ? \strval($value) : \get_debug_type($value);

        return "{$short} type must be one of [{$allowed}], got {$valueStr}";
    }

    /**
     * @return list<\UnitEnum>
     */
    private function cases(): array
    {
        $cases = [];
        foreach ((new \ReflectionEnum($this->enumClass))->getCases() as $caseRef) {
            $cases[] = $caseRef->getValue();
        }

        return $cases;
    }

    #[\Override]
    public function priority(): int
    {
        return 60;
    }

    /**
     * Derived from the enum's backing type rather than declared as "any": the candidate
     * compared against the value is `$case->value` for a backed enum and `$case->name`
     * (always a string) for a pure one, and the comparison is strict. So an int-backed
     * enum only ever matches an integer type, and a string-backed or pure enum only ever
     * matches a string type -- anything else is a constraint that can never match.
     *
     * A caseless *pure* enum has nothing to match either, but reports no backing type and
     * so lands on `[String]` (a caseless *backed* enum still reports its backing type, so
     * `enum Empty: int {}` correctly yields `[Int]`). Like a degenerate `InList`, that
     * stays a runtime validation failure: the check catches domain mismatches, not empty
     * domains.
     *
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        $backingType = (new \ReflectionEnum($this->enumClass))->getBackingType();

        return ($backingType instanceof \ReflectionNamedType && $backingType->getName() === 'int')
            ? [ValueType::Int]
            : [ValueType::String];
    }
}
