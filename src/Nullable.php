<?php

declare(strict_types=1);

namespace StrongType;

use StrongType\Exception\ConstraintConfigurationException;

readonly class Nullable implements \JsonSerializable, \Stringable, HasEquals
{
    private (\JsonSerializable&\Stringable&HasEquals)|null $strongType;

    /**
     * @param class-string<\JsonSerializable&\Stringable&HasEquals> $type
     */
    public function __construct(
        private string $type,
        mixed $value,
    ) {
        if (
            !\is_subclass_of($this->type, HasEquals::class)
            || !\is_subclass_of($this->type, \JsonSerializable::class)
            || !\is_subclass_of($this->type, \Stringable::class)
        ) {
            throw new ConstraintConfigurationException("Nullable type must reference a valid StrongType class, got {$this->type}");
        }
        $reflection = new \ReflectionClass($this->type);
        if ($reflection->isAbstract()) {
            throw new ConstraintConfigurationException("Nullable type must reference a concrete StrongType class, got abstract {$this->type}");
        }
        // Nullable constructs the wrapped type with exactly one argument, so a type whose
        // constructor requires more (FixedSizeArray, which also takes a $size) can never be
        // wrapped. Checked by reflection, up front like the guards above, so the error
        // surfaces even when $value is null -- otherwise a Nullable that could never hold a
        // value would construct fine. Optional and variadic parameters are not "required",
        // so they stay wrappable.
        //
        // Deliberately a precheck rather than a catch around the instantiation: an
        // ArgumentCountError can also come from *inside* a perfectly wrappable one-argument
        // constructor, and relabelling that as "this type needs more than one argument"
        // would misreport the failure. Deeper ArgumentCountErrors propagate untouched.
        $constructor = $reflection->getConstructor();
        if ($constructor !== null && !$constructor->isPublic()) {
            throw new ConstraintConfigurationException("Nullable type must reference a StrongType class with a public constructor, got {$this->type}");
        }
        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 1) {
            throw new ConstraintConfigurationException("Nullable type must reference a StrongType class whose constructor accepts a single value, got {$this->type}");
        }
        if ($value === null) {
            $this->strongType = null;
        } else {
            $this->strongType = new $this->type($value);
        }
    }

    /**
     * The wrapped strong type instance, or null.
     *
     * Distinct from {@see getValue()}, which returns the wrapped value as a serialized
     * primitive. Use unwrap() when the strong type object itself is needed — for example
     * to reach its own accessors (`getValue()` / `getValues()`) or `equals()`.
     */
    public function unwrap(): (\JsonSerializable&\Stringable&HasEquals)|null
    {
        return $this->strongType;
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
    public function equals(HasEquals $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }
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
