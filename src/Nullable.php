<?php

declare(strict_types=1);

namespace StrongType;

use StrongType\Exception\StrongTypeException;

readonly class Nullable implements \JsonSerializable, \Stringable
{
    private (\JsonSerializable&\Stringable)|null $strongType;

    /**
     * @param class-string<\JsonSerializable&\Stringable> $type
     */
    public function __construct(
        private string $type,
        mixed $value,
    ) {
        if ($value === null) {
            $this->strongType = null;
        } else {
            if (!\is_subclass_of($this->type, \JsonSerializable::class)) {
                throw new StrongTypeException("Nullable type must reference a valid StrongType class, got {$this->type}");
            }
            $this->strongType = new $this->type($value);
        }
    }

    public function getValue(): mixed
    {
        return $this->strongType?->jsonSerialize();
    }

    public function isNull(): bool
    {
        return $this->strongType === null;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->strongType?->jsonSerialize();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->strongType === null ? 'null' : (string) $this->strongType;
    }

    public function __debugInfo(): array
    {
        return ['type' => $this->type, 'value' => $this->getValue()];
    }
}
