<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InList implements ConstraintInterface
{
    /** @var list<mixed> */
    private array $allowed;

    public function __construct(mixed ...$allowed)
    {
        $this->allowed = \array_values($allowed);
        if ($this->allowed === []) {
            throw new ConstraintConfigurationException('InList requires at least one allowed value');
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (!\in_array($value, $this->allowed, strict: true)) {
            $list = \implode(', ', \array_map(static fn(mixed $v): string => \is_scalar($v) || $v === null ? \strval($v) : \get_debug_type($v), $this->allowed));
            $valueStr = \is_scalar($value) ? \strval($value) : \get_debug_type($value);
            return "{$short} type must be one of [{$list}], got {$valueStr}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 60;
    }

    /**
     * Derived from the allowed values rather than declared as "any": membership is tested
     * with `in_array(..., strict: true)`, so `#[InList(1, 2)]` on a string type can never
     * match a single value. Declaring the operands' own types turns that dead constraint
     * into a definition-time error.
     *
     * `null` and object operands are ignored -- a strong type's value is never either --
     * and an allowed set of nothing else falls back to every type. That fallback is
     * deliberately permissive: such a list can never match either, but the applicability
     * check exists to catch domain *mismatches*, not to adjudicate degenerate lists, so
     * it stays a runtime validation failure rather than a definition-time error.
     *
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        $accepted = [];

        foreach ($this->allowed as $value) {
            $valueType = match (true) {
                \is_int($value)    => ValueType::Int,
                \is_float($value)  => ValueType::Float,
                \is_string($value) => ValueType::String,
                \is_bool($value)   => ValueType::Bool,
                \is_array($value)  => ValueType::Array,
                default            => null,
            };

            if ($valueType !== null && !\in_array($valueType, $accepted, true)) {
                $accepted[] = $valueType;
            }
        }

        return $accepted === [] ? ValueType::cases() : $accepted;
    }
}
