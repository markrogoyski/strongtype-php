<?php

declare(strict_types=1);

namespace StrongType\Constraint;

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
            throw new \LogicException("InEnum: class must be an existing enum, got '{$enumClass}'");
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
}
