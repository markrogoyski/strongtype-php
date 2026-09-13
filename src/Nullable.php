<?php

declare(strict_types=1);

namespace StrongType;

use StrongType\Exception\ConstraintConfigurationException;

readonly class Nullable implements \JsonSerializable, \Stringable, HasEquals
{
    /** @var class-string<\JsonSerializable&\Stringable&HasEquals> */
    private string $type;

    private (\JsonSerializable&\Stringable&HasEquals)|null $strongType;

    /**
     * @param class-string<\JsonSerializable&\Stringable&HasEquals> $type
     */
    public function __construct(string $type, mixed $value)
    {
        // Canonicalized rather than storing the raw $type argument, so $this->type is
        // always the canonical class name -- no leading '\', whatever case or alias the
        // caller spelled it with resolved to the real declared name. This is the single
        // source of truth every later exact-class comparison (equals(), __unserialize())
        // relies on, rather than each of them re-canonicalizing (or forgetting to) a
        // possibly-non-canonical string independently.
        $this->type = self::assertWireable($type);
        if ($value === null) {
            $this->strongType = null;
        } else {
            $this->strongType = new $this->type($value);
        }
    }

    /**
     * The type-wiring guards shared by the constructor and __unserialize(): both need
     * $type to be a concrete, publicly-constructible, single-value-constructor StrongType
     * class before trusting it to build or hold an instance. Returns the canonical class
     * name (see the constructor).
     *
     * @param class-string $type
     * @return class-string<\JsonSerializable&\Stringable&HasEquals>
     */
    private static function assertWireable(string $type): string
    {
        if (
            !\is_subclass_of($type, HasEquals::class)
            || !\is_subclass_of($type, \JsonSerializable::class)
            || !\is_subclass_of($type, \Stringable::class)
        ) {
            throw new ConstraintConfigurationException("Nullable type must reference a valid StrongType class, got {$type}");
        }
        $reflection = new \ReflectionClass($type);
        if ($reflection->isAbstract()) {
            throw new ConstraintConfigurationException("Nullable type must reference a concrete StrongType class, got abstract {$type}");
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
            throw new ConstraintConfigurationException("Nullable type must reference a StrongType class with a public constructor, got {$type}");
        }
        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 1) {
            throw new ConstraintConfigurationException("Nullable type must reference a StrongType class whose constructor accepts a single value, got {$type}");
        }
        return $reflection->getName();
    }

    /**
     * Guards Nullable's own __serialize()/__unserialize(), which nest the wrapped object
     * directly rather than re-deriving it from jsonSerialize() (see __serialize() below for
     * why). That only stays safe if the wrapped class declares __serialize()/__unserialize()
     * of its own -- every built-in base does, following the same constructor-revalidating
     * pattern this class itself uses. A class that satisfies assertWireable()'s three
     * interface/arity/visibility guards but doesn't declare these is still perfectly usable
     * directly (construction, equals(), jsonSerialize(), casting to string) -- Nullable does
     * not require it to be a StrongType subclass -- but native unserialize() would rebuild it
     * by writing straight into its properties, bypassing its constructor entirely. There is
     * no generic way for Nullable to re-validate an arbitrary foreign class's state itself,
     * so a wrapped type without this guarantee is rejected here rather than silently trusted.
     *
     * Also rejects a wrapped class that implements the legacy \Serializable interface, even
     * alongside __serialize()/__unserialize(): the serialized string's own format sigil (O:
     * for the modern hooks, C: for Serializable::unserialize()) -- not which methods the
     * target class happens to declare -- decides which path native unserialize() takes, so a
     * hand-crafted C:-sigil payload naming this class would invoke Serializable::unserialize()
     * regardless of a safe __unserialize() sitting right next to it. Declaring
     * __serialize()/__unserialize() is only actually a guarantee when \Serializable is
     * entirely absent.
     *
     * @param class-string $wrappedClass
     */
    private static function assertSafelySerializable(string $wrappedClass): void
    {
        $reflection = new \ReflectionClass($wrappedClass);
        if (!$reflection->hasMethod('__serialize') || !$reflection->hasMethod('__unserialize')) {
            throw new ConstraintConfigurationException(
                "Nullable cannot safely serialize a wrapped {$wrappedClass} instance: {$wrappedClass} " .
                'does not implement __serialize()/__unserialize() to revalidate itself when ' .
                'reconstructed, so native serialization could bypass its constructor invariants ' .
                'entirely. Implement safe serialization on the wrapped type (see any built-in base for ' .
                'the pattern) before serializing a Nullable that wraps it.'
            );
        }
        if ($reflection->implementsInterface(\Serializable::class)) {
            throw new ConstraintConfigurationException(
                "Nullable cannot safely serialize a wrapped {$wrappedClass} instance: {$wrappedClass} " .
                'implements the legacy \Serializable interface alongside __serialize()/__unserialize(). ' .
                'A hand-crafted payload can select the legacy serialize()/unserialize() pair by its ' .
                "serialized string format alone, bypassing {$wrappedClass}'s own safe hooks entirely. " .
                'Remove the \Serializable implementation from the wrapped type before serializing a ' .
                'Nullable that wraps it.'
            );
        }
    }

    /**
     * Guards Nullable's own __serialize()/__unserialize() against being inherited unchanged
     * by a subclass. Unlike the six value-type bases (Integer, StringType, ...), whose
     * inherited __unserialize() re-enters via $this->__construct() and so re-runs a
     * subclass's own validation automatically, Nullable's __unserialize() never invokes the
     * constructor at all -- deliberately, since deriving the wrapped value from
     * jsonSerialize() to pass through the single-value constructor would be lossy (see
     * __serialize() below). That means a subclass adding its own constructor validation
     * (e.g. forbidding a null value) or its own additional properties gets none of it
     * re-checked by inherited __unserialize(): native unserialize() would simply assign
     * $type/$strongType straight into the readonly properties, silently producing an
     * instance the subclass's own constructor would have refused. There is no reflection-
     * based certification that can safely paper over this the way
     * constructorPreservesSerializedStateVerbatim() does for the six bases -- Nullable's
     * inherited hooks are unconditionally unsafe for any subclass, full stop -- so every
     * subclass must override the method named here itself (even a trivial override that
     * calls parent::__serialize()/parent::__unserialize() and then re-validates -- see
     * ProperlyOverridingNullableSubclass in DeserializationTest for the pattern).
     *
     * A subclass implementing the legacy \Serializable interface is rejected outright: a
     * hand-crafted 'C:'-sigil payload naming such a subclass directly would invoke
     * Serializable::unserialize() instead of __unserialize(), bypassing this whole method
     * (and the constructor) entirely -- the identical threat assertSafelySerializable()
     * guards against for the *wrapped* type, one level down. Unlike that case, this check
     * cannot retroactively catch a crafted payload exploiting it (Nullable's own
     * __unserialize() never runs on that path, so there is nothing here left to run the
     * check); it only fails fast for an ordinary serialize() call, the same way declaring
     * \Serializable on a wrapped type is only caught when that wrapped type is actually
     * reconstructed as a nested object inside Nullable's own, always-reachable
     * __unserialize().
     *
     * Deliberately checked via ReflectionMethod::getDeclaringClass() on $method, not via
     * `static::class !== self::class`: late static binding means static::class still
     * resolves to the subclass even *inside* a call the subclass routed here itself via
     * parent::__serialize()/parent::__unserialize() -- a same-class-identity check would
     * reject exactly the delegating override this method's own docs recommend as the fix.
     * Checking whether $method's declaring class is still self::class instead correctly
     * distinguishes "the subclass didn't touch this method at all" (unsafe, reject) from
     * "the subclass overrides this method and chose to delegate back here" (trusted: the
     * subclass is assumed to have done, or be about to do, its own validation around the
     * delegated call).
     *
     * That trust is bounded, not unconditional: the overriding class ($handler below) wrote
     * its override without any knowledge of a class more derived than itself, so *either* a
     * property *or* a constructor override declared below $handler -- by an intermediate
     * ancestor or the runtime class itself -- is still unhandled by it (checked separately
     * below, since a further-derived class can add unhandled constructor *validation*, e.g.
     * forbidding null, without declaring any new property at all -- a property-only check
     * would miss it entirely). This is the exact gap SerializationStateGuard closes for the
     * six bases (a `GrandChild extends MidLevel extends Nullable`, where only MidLevel
     * overrides these hooks and GrandChild silently adds its own property or constructor
     * check, would otherwise have that state or validation dropped). The walk below only
     * inspects classes strictly between static::class and $handler (exclusive of $handler
     * itself, which is trusted), reflecting each on its own terms the same way
     * SerializationStateGuard does, since a private property is invisible from a more
     * distant descendant's own reflection call.
     *
     * @param '__serialize'|'__unserialize' $method
     */
    private function assertNotInheritingUnsafeSerialization(string $method): void
    {
        if ($this instanceof \Serializable) {
            throw new ConstraintConfigurationException(
                static::class . ' must not implement the legacy \Serializable interface: a hand-crafted ' .
                "payload can select Serializable::unserialize() by the serialized string's format sigil " .
                'alone, bypassing __unserialize() (and this guard) entirely regardless of what else this ' .
                'class implements -- the identical threat assertSafelySerializable() guards against for ' .
                'a wrapped type. Remove the \Serializable implementation.'
            );
        }
        if (static::class === self::class) {
            return;
        }
        $handler = (new \ReflectionMethod($this, $method))->getDeclaringClass()->getName();
        if ($handler === self::class) {
            throw new ConstraintConfigurationException(
                static::class . " must override {$method}() itself: Nullable's own inherited " .
                "{$method}() never invokes the constructor, so a subclass's own constructor " .
                'validation or additional properties would be silently bypassed by native ' .
                "(un)serialize(). Override {$method}() yourself, even if only to call " .
                "parent::{$method}() and then re-run your own validation."
            );
        }
        // Checked in this order -- properties before the constructor -- purely so that a
        // class doing both at once (the common shape: a promoted constructor parameter adds
        // both) gets the more specific, name-bearing property message rather than the
        // generic constructor one; both are unconditionally rejected either way.
        $extra = [];
        $className = static::class;
        while ($className !== false && $className !== $handler) {
            $reflection = new \ReflectionClass($className);
            foreach ($reflection->getProperties() as $property) {
                $declaringClass = $property->getDeclaringClass()->getName();
                $isAtOrAboveHandler = $declaringClass === $handler || \is_subclass_of($handler, $declaringClass);
                if (!$isAtOrAboveHandler) {
                    $extra[] = $property->getName();
                }
            }
            $parent = $reflection->getParentClass();
            $className = $parent === false ? false : $parent->getName();
        }
        $extra = \array_unique($extra);
        if ($extra !== []) {
            $names = \implode(', ', $extra);
            throw new ConstraintConfigurationException(
                static::class . " declares additional state ({$names}) that {$handler}'s own " .
                "{$method}() was written without knowledge of; a class further derived than the " .
                "class that overrides {$method}() must write its own override too (see the " .
                'Constructor Consistency Contract in CLAUDE.md)'
            );
        }

        // A constructor override declared below $handler is unhandled the same way an extra
        // property is: $handler's own __serialize()/__unserialize() was written without any
        // knowledge of it, so it cannot be trusted to have re-run (or accounted for the
        // absence of) whatever validation that override adds -- e.g. a further-derived
        // class forbidding null with no new property at all, which the property walk above
        // could never see.
        $constructor = (new \ReflectionClass(static::class))->getConstructor();
        if ($constructor !== null) {
            $constructorDeclaringClass = $constructor->getDeclaringClass()->getName();
            $isAtOrAboveHandler = $constructorDeclaringClass === $handler
                || \is_subclass_of($handler, $constructorDeclaringClass);
            if (!$isAtOrAboveHandler) {
                throw new ConstraintConfigurationException(
                    static::class . " overrides the constructor below {$handler}, whose own {$method}() " .
                    "was written without knowledge of it; a class further derived than the class that " .
                    "overrides {$method}() must write its own override too (see the Constructor " .
                    'Consistency Contract in CLAUDE.md)'
                );
            }
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
        // instanceof narrows $other's type for the property accesses below; the exact
        // class-string comparison alongside it is what actually enforces HasEquals' exact-
        // type-equality contract, since instanceof alone would also admit a subclass
        // instance. static::class (not self::class) so the check is symmetric: called on a
        // subclass instance, self::class would still hardcode to Nullable and miss a
        // mismatch the other direction already catches.
        if (!$other instanceof self || $other::class !== static::class) {
            return false;
        }
        // No canonicalization needed here: $this->type and $other->type are both already
        // the canonical class name (see the constructor), never a caller's raw spelling.
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

    /**
     * Native unserialize() populates readonly properties directly, bypassing the
     * constructor entirely. This nests the wrapped object itself -- not
     * jsonSerialize()'s output -- so unserialize() reconstructs it via *its own*
     * __serialize()/__unserialize() (see Integer::__unserialize() and friends), re-running
     * every invariant the wrapped type's constructor enforces. Round-tripping through
     * jsonSerialize() instead would be lossy for any wrapped type whose jsonSerialize()
     * output isn't identical to its constructor input (legal per the JsonSerializable
     * contract -- e.g. a type that normalizes case for its JSON form) and would silently
     * reconstruct a different value than the one originally wrapped.
     *
     * @return array{type: class-string<\JsonSerializable&\Stringable&HasEquals>, strongType: (\JsonSerializable&\Stringable&HasEquals)|null}
     */
    public function __serialize(): array
    {
        $this->assertNotInheritingUnsafeSerialization('__serialize');
        if ($this->strongType !== null) {
            self::assertSafelySerializable($this->strongType::class);
        }
        return ['type' => $this->type, 'strongType' => $this->strongType];
    }

    /**
     * $data['strongType'] arrives already fully reconstructed (native unserialize()
     * resolves nested objects bottom-up, before calling this), so this only needs to
     * re-run the type-wiring guards and confirm the two fields are still consistent with
     * each other -- a crafted payload could otherwise pair a 'type' string with an
     * unrelated wrapped object whose own class doesn't match it. $data['type'] is
     * canonicalized the same way the constructor canonicalizes it (rather than trusted
     * verbatim), so a payload built from a non-canonical spelling -- e.g.
     * `new Nullable('\' . PositiveInt::class, 5)`, which the constructor accepts --
     * round-trips instead of being rejected by a raw string mismatch against
     * $strongType::class, which is always canonical.
     *
     * @param array{type: class-string<\JsonSerializable&\Stringable&HasEquals>, strongType: (\JsonSerializable&\Stringable&HasEquals)|null} $data
     */
    public function __unserialize(array $data): void
    {
        $this->assertNotInheritingUnsafeSerialization('__unserialize');
        $this->type = self::assertWireable($data['type']);
        $strongType = $data['strongType'];
        if ($strongType !== null) {
            $actualType = $strongType::class;
            if ($actualType !== $this->type) {
                throw new ConstraintConfigurationException(
                    "Nullable wrapped value must be an instance of {$this->type}, got {$actualType}"
                );
            }
            // $strongType has already been rebuilt by native unserialize() by this point (nested
            // objects resolve bottom-up), but *how* safely depends entirely on whether its own
            // class implements __serialize()/__unserialize() -- a class that doesn't gets its
            // properties written straight in by PHP's default object unserialization, with no
            // constructor call at all. Re-checking here (not just in __serialize()) is what
            // actually closes the hole: a hand-crafted payload never has to go through
            // __serialize() in the first place, so this is the only guard a tampered payload
            // targeting an unsafe wrapped type cannot route around.
            self::assertSafelySerializable($actualType);
        }
        $this->strongType = $strongType;
    }
}
