<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Constraint\ValueType;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;
use StrongType\Util\SerializationStateGuard;

/**
 * @phpstan-consistent-constructor
 * @psalm-consistent-constructor
 */
readonly abstract class Integer implements \JsonSerializable, \Stringable, HasEquals
{
    use SerializationStateGuard;

    final public const ValueType VALUE_TYPE = ValueType::Int;

    public function __construct(public int $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return \strval($this->value);
    }

    /**
     * @return int[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }

    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_int($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static($value);
        } catch (StrongTypeException) {
            return null;
        }
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        if (!$other instanceof self || $other::class !== static::class) {
            return false;
        }
        return $this->value === $other->value;
    }

    public static function nullable(mixed $value): \StrongType\Nullable
    {
        return new \StrongType\Nullable(static::class, $value);
    }

    /**
     * Native unserialize() populates readonly properties directly, bypassing the
     * constructor entirely -- a crafted payload could otherwise produce e.g. a
     * PositiveInt holding a negative value. __unserialize() re-enters via $this->__construct()
     * (virtual dispatch, so a subclass's own manual checks run too, not just this base's
     * ConstraintValidator call), which is also the only write these properties ever get,
     * satisfying readonly's write-once rule.
     *
     * @return array{value: int}
     */
    public function __serialize(): array
    {
        $this->assertNoUnhandledSerializationState(['value']);
        $this->assertConstructorIsSafeToInherit(1);
        return ['value' => $this->value];
    }

    /**
     * @param array{value: int} $data
     * @psalm-suppress DirectConstructorCall Deliberate: unserialize() creates this object
     *     without ever initializing it, so this is the sole write these properties get,
     *     not a re-initialization of an already-constructed instance.
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['value']);
    }
}
