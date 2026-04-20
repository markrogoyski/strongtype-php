<?php

declare(strict_types=1);

namespace StrongType;

use StrongType\Exception\StrongTypeException;

readonly class Nullable implements \JsonSerializable, \Stringable
{
    private (\JsonSerializable&\Stringable&HasEquals)|null $strongType;

    /**
     * @param class-string<\JsonSerializable&\Stringable&HasEquals> $type
     */
    public function __construct(
        private string $type,
        mixed $value,
    ) {
        if (!\is_subclass_of($this->type, HasEquals::class) || !\is_subclass_of($this->type, \JsonSerializable::class)) {
            throw new StrongTypeException("Nullable type must reference a valid StrongType class, got {$this->type}");
        }
        if ((new \ReflectionClass($this->type))->isAbstract()) {
            throw new StrongTypeException("Nullable type must reference a concrete StrongType class, got abstract {$this->type}");
        }
        if ($value === null) {
            $this->strongType = null;
        } else {
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

    public function equals(self $other): bool
    {
        if ($this->type !== $other->type) {
            return false;
        }
        if ($this->strongType === null || $other->strongType === null) {
            return $this->strongType === $other->strongType;
        }
        return $this->strongType->equals($other->strongType);
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
