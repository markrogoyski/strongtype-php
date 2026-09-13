<?php

declare(strict_types=1);

namespace StrongType\Bool;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Constraint\ValueType;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;
use StrongType\Util\SerializationStateGuard;

readonly abstract class BoolType implements \JsonSerializable, \Stringable, HasEquals
{
    use SerializationStateGuard;

    final public const ValueType VALUE_TYPE = ValueType::Bool;

    public function __construct(public bool $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): bool
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    /**
     * @return bool[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }

    /**
     * Subclasses with narrower constructor types (e.g. `TrueValue` with `true`, `FalseValue` with `false`)
     * must override this method to pre-filter mismatched literals — otherwise the inherited `new static($value)`
     * will raise an uncaught `\TypeError` for valid-but-wrong-literal input. See `TrueValue::tryFrom()` and
     * `FalseValue::tryFrom()` for the pattern.
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_bool($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static($value); // @phpstan-ignore new.static
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
     * constructor entirely. __unserialize() re-enters via $this->__construct() (virtual
     * dispatch, so a subclass's own narrower literal-type check -- e.g. TrueValue's
     * `true $value` -- runs too, not just this base's ConstraintValidator call), which is
     * also the only write this property ever gets, satisfying readonly's write-once rule.
     *
     * @return array{value: bool}
     */
    public function __serialize(): array
    {
        $this->assertNoUnhandledSerializationState(['value']);
        $this->assertConstructorIsSafeToInherit(1);
        return ['value' => $this->value];
    }

    /**
     * @param array{value: bool} $data
     * @psalm-suppress DirectConstructorCall Deliberate: unserialize() creates this object
     *     without ever initializing it, so this is the sole write this property gets, not
     *     a re-initialization of an already-constructed instance.
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['value']);
    }
}
