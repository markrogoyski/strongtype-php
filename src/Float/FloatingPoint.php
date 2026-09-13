<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Constraint\ValueType;
use StrongType\Constraint\Finite;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;
use StrongType\Util\SerializationStateGuard;

/**
 * @phpstan-consistent-constructor
 * @psalm-consistent-constructor
 */
#[Finite]
readonly abstract class FloatingPoint implements \JsonSerializable, \Stringable, HasEquals
{
    use SerializationStateGuard;

    final public const ValueType VALUE_TYPE = ValueType::Float;

    public function __construct(public float $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): float
    {
        return $this->value;
    }

    /**
     * Rendered through json_encode so the string form matches jsonSerialize() (scoped below).
     * strval() applies the `precision` ini and upper-case exponents, which diverges from
     * json_encode's `serialize_precision` rendering for high-precision values
     * (1.2345678901234567) and scientific notation (1.0e+20). Under PHP's defaults
     * (precision=14, serialize_precision=-1) that is a 14-digit vs shortest-round-trip
     * difference; the point is that both forms now come from the same setting, whatever
     * it is, so they cannot disagree with each other.
     *
     * The cast is a deliberate choice over JSON_THROW_ON_ERROR. PHP has allowed __toString()
     * to throw since 7.4, but a string cast is reached from interpolation, concatenation and
     * error-message building, where an exception is far more disruptive than an odd string --
     * so this one does not throw.
     *
     * It does not need to: for any instance built through this constructor the inherited
     * #[Finite] constraint rules out NAN/INF, the only float values json_encode refuses, so
     * the call cannot return false. The `(string) $obj === json_encode($obj)` guarantee is
     * likewise scoped to such validated instances using the inherited methods under default
     * JSON options. An instance that bypassed validation entirely (a subclass constructor
     * that never calls parent::__construct, or crafted unserialize() data) can still hold
     * NAN, and there the three renderings genuinely differ -- '' here, false from
     * json_encode(), NAN from jsonSerialize().
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) \json_encode($this->value);
    }

    /**
     * @return float[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }

    /**
     * Accepts float and int (int is widened to float, matching PHP's implicit
     * int-to-float conversion; precision may be lost for ints beyond 2^53).
     * Numeric strings such as "5" or "5.0" return null — string parsing is
     * locale- and format-sensitive, so callers must cast explicitly. Returns
     * null on constraint failure or non-finite input.
     */
    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_float($value) && !\is_int($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static((float) $value);
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
     * dispatch, so a subclass's own manual checks run too, not just this base's
     * ConstraintValidator call), which is also the only write this property ever gets,
     * satisfying readonly's write-once rule.
     *
     * @return array{value: float}
     */
    public function __serialize(): array
    {
        $this->assertNoUnhandledSerializationState(['value']);
        $this->assertConstructorIsSafeToInherit(1);
        return ['value' => $this->value];
    }

    /**
     * @param array{value: float} $data
     * @psalm-suppress DirectConstructorCall Deliberate: unserialize() creates this object
     *     without ever initializing it, so this is the sole write this property gets, not
     *     a re-initialization of an already-constructed instance.
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['value']);
    }
}
