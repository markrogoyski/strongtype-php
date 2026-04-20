<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InList implements ConstraintInterface
{
    /** @var list<mixed> */
    private array $allowed;

    public function __construct(mixed ...$allowed)
    {
        $this->allowed = \array_values($allowed);
        if ($this->allowed === []) {
            throw new \LogicException('InList requires at least one allowed value');
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

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
}
