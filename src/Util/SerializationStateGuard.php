<?php

declare(strict_types=1);

namespace StrongType\Util;

use StrongType\Exception\ConstraintConfigurationException;

/**
 * Shared by every base's __serialize() to catch a subclass that adds constructor state --
 * promoted or not -- without overriding __serialize()/__unserialize() to carry it (see the
 * Constructor Consistency Contract in CLAUDE.md). Without this, that state is either
 * silently dropped (an optional extra property survives construction but is lost on the
 * round trip) or crashes with \ArgumentCountError deep inside __unserialize()'s
 * $this->__construct() call (a required one) -- and either way, only the first time someone
 * actually serializes an affected instance, not at the point the subclass was written.
 */
trait SerializationStateGuard
{
    /**
     * @param list<string> $knownProperties property names this base's own __serialize()
     *     already captures; anything declared beyond these anywhere in the runtime class's
     *     hierarchy is unhandled.
     */
    private function assertNoUnhandledSerializationState(array $knownProperties): void
    {
        // ReflectionClass::getProperties() called on a class only returns *that* class's own
        // declared properties plus any *public or protected* ones inherited from a parent --
        // a parent's *private* property (e.g. from an intermediate subclass two levels up)
        // is silently omitted, even though it is still part of the object (PHP just does not
        // consider it "a property of" a class it isn't declared or visible in). So this walks
        // every class in the hierarchy by name (rather than holding onto a single reassigned
        // ReflectionClass object, which PHPStan's invariant generics reject once reassigned
        // to a parent's ReflectionClass) and reflects each one on its own terms, which is the
        // only way each level's own private properties are ever surfaced.
        //
        // A name in $knownProperties is only trusted for a property actually *declared* at or
        // above self::class -- the class whose own copy of this trait method is running (every
        // host class does its own `use SerializationStateGuard`, so self::class here is always
        // that specific base, e.g. Integer, ArrayType, FixedSizeArray, Nullable). Checked via
        // ReflectionProperty::getDeclaringClass(), not "which walk level surfaced it": a public
        // or protected property is visible starting from every descendant's own reflection, so
        // the level it's first seen at says nothing about where it's actually declared. A
        // property declared *below* self::class -- by static::class itself or an intermediate
        // subclass -- is unconditionally unhandled, even if it happens to share a name with a
        // known one: a subclass can legally declare its own same-named *private* property (a
        // distinct storage slot, due to PHP's private-property shadowing), and that must not
        // be mistaken for the base's own state.
        $extra = [];
        $className = static::class;
        while ($className !== false) {
            $reflection = new \ReflectionClass($className);
            foreach ($reflection->getProperties() as $property) {
                $name = $property->getName();
                $declaringClass = $property->getDeclaringClass()->getName();
                $isDeclaredAtOrAboveSelf = $declaringClass === self::class
                    || \is_subclass_of(self::class, $declaringClass);
                if ($isDeclaredAtOrAboveSelf && \in_array($name, $knownProperties, true)) {
                    continue;
                }
                $extra[] = $name;
            }
            $parent = $reflection->getParentClass();
            $className = $parent === false ? false : $parent->getName();
        }
        $extra = \array_unique($extra);
        if ($extra !== []) {
            $names = \implode(', ', $extra);
            throw new ConstraintConfigurationException(
                static::class . " declares additional constructor state ({$names}) that this base's " .
                'default __serialize() does not capture; override __serialize()/__unserialize() to ' .
                'include it (see the Constructor Consistency Contract in CLAUDE.md and FixedSizeArray ' .
                'for the pattern)'
            );
        }
    }

    /**
     * Catches the case assertNoUnhandledSerializationState() cannot see: a subclass whose
     * constructor is overridden below self::class but stores no *extra* property -- either
     * because it transforms its single argument before storing it in the base's own
     * property (e.g. multiplies it), which would silently reconstruct a different value
     * every time inherited __unserialize() re-invokes the constructor with the
     * already-transformed stored value, or because it requires more arguments than
     * inherited __unserialize() supplies, which would fail outright instead.
     *
     * Deliberately does not invoke the constructor to check this empirically (e.g.
     * reconstruct-and-compare): a validation-only override can legitimately depend on
     * external state (FutureTimestamp/PastTimestamp compare against time()) and fail on a
     * later call for reasons that have nothing to do with whether it stores its argument
     * verbatim, so actually re-running it here would misreport ordinary time-drift as a
     * configuration error. Everything below is therefore purely structural (reflection
     * only), plus a subclass's own explicit, static certification that it stores its
     * argument unchanged.
     *
     * @param int $suppliedArgCount number of arguments inherited __unserialize() will pass
     *     to $this->__construct() (1 for every base except FixedSizeArray's own override, 2).
     */
    private function assertConstructorIsSafeToInherit(int $suppliedArgCount): void
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();
        if ($constructor === null || $constructor->getDeclaringClass()->getName() === self::class) {
            // Either no constructor at all (impossible for these bases, kept for safety) or
            // still exactly self::class's own -- the reference implementation, trivially safe.
            return;
        }
        if ($constructor->getNumberOfRequiredParameters() > $suppliedArgCount) {
            throw new ConstraintConfigurationException(
                static::class . "'s constructor requires more arguments than inherited __unserialize() " .
                "supplies ({$suppliedArgCount}); override __serialize()/__unserialize() to carry " .
                'whatever additional state the extra argument represents (see FixedSizeArray for the pattern)'
            );
        }
        // Inherited __unserialize() calls $this->__construct() from *this base's own scope*
        // (self::class), not the subclass's -- so the constructor must actually be callable
        // from there. protected works: PHP's visibility check for protected passes because
        // the calling scope (self::class) is an ancestor of the declaring class, which is
        // exactly the relationship PHP's "classes it inherits from" rule for protected access
        // covers. private does not: private is scoped to the exact declaring class only, so a
        // subclass with a private constructor (e.g. gated behind a public static factory)
        // would pass every other check in this method -- including a truthful verbatim-storage
        // certification -- and still fail at runtime with "Call to private method ... from
        // scope {self::class}" the first time __unserialize() actually re-enters it. No
        // certification can fix this; only a visibility change or a full
        // __serialize()/__unserialize() override can.
        if ($constructor->isPrivate()) {
            throw new ConstraintConfigurationException(
                static::class . "'s constructor is private, so inherited __unserialize() (defined on " .
                self::class . ') cannot call it -- private access is scoped to the exact declaring ' .
                'class, unlike protected, which permits access from an ancestor class such as ' .
                self::class . '. Declare the constructor protected or public, or override ' .
                '__serialize()/__unserialize() yourself (see FixedSizeArray for the pattern)'
            );
        }
        // The certification must be bound to *this specific* constructor, not merely
        // inherited from whichever ancestor happens to be the nearest one that declared it:
        // otherwise a further override -- e.g. `ScaledTimestamp extends Timestamp` calling
        // `parent::__construct($value * 100)` -- would silently inherit Timestamp's "I only
        // validate" certification for a constructor Timestamp's author never saw and that
        // does exactly the transforming thing the certification exists to rule out. Checked
        // by comparing the constructor's declaring class against the certification method's
        // own declaring class: they only describe the same override when they match.
        $constructorDeclaringClass = $constructor->getDeclaringClass()->getName();
        $certificationDeclaringClass = (new \ReflectionMethod(static::class, 'constructorPreservesSerializedStateVerbatim'))
            ->getDeclaringClass()->getName();
        if ($constructorDeclaringClass !== $certificationDeclaringClass || !static::constructorPreservesSerializedStateVerbatim()) {
            throw new ConstraintConfigurationException(
                static::class . "'s constructor is overridden below " . self::class . " and hasn't " .
                'certified that it stores its argument verbatim: inherited __unserialize() reconstructs ' .
                'by calling the constructor again with the exact value that was previously stored, so an ' .
                'override that transforms its input (e.g. scales or normalizes it) would silently ' .
                'reconstruct a different value than the one serialized every time. If this override only ' .
                'adds validation and truly stores its argument unchanged (see FutureTimestamp or ' .
                'DateString for the pattern), certify it by declaring, on this exact class, its own ' .
                'override of constructorPreservesSerializedStateVerbatim() returning true -- a ' .
                'certification inherited from an ancestor does not cover a constructor that ancestor ' .
                'never saw; otherwise override __serialize()/__unserialize() yourself (see FixedSizeArray ' .
                'for the pattern)'
            );
        }
    }

    /**
     * Declares whether this class's own __construct() override (if any) stores its single
     * argument verbatim into the properties this base's __serialize() captures -- adding
     * only validation, never transforming, dropping, or requiring additional unstored
     * input. Default false: an override is untrusted until a subclass author explicitly
     * certifies it, since assertConstructorIsSafeToInherit() has no generic way to tell a
     * value-preserving validation check (safe) apart from a value-transforming one (unsafe)
     * by reflection alone.
     *
     * The certification only covers the constructor declared by the *same* class that
     * declares this override -- assertConstructorIsSafeToInherit() checks both declaring
     * classes match, so overriding only this method (or only the constructor) without the
     * other on a given class does not certify anything: a further subclass that overrides
     * the constructor again *must* also override this method itself, even if an ancestor
     * already certified its own, different constructor.
     */
    protected static function constructorPreservesSerializedStateVerbatim(): bool
    {
        return false;
    }
}
