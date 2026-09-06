<?php

declare(strict_types=1);

namespace StrongType\Util;

class Stringify
{
    /**
     * Maximum nesting depth rendered for arrays before collapsing to a marker.
     */
    private const int MAX_DEPTH = 3;

    /**
     * Maximum number of elements rendered per array level before truncating.
     */
    private const int MAX_ELEMENTS = 10;

    /**
     * Render any value as a short, human-readable string for error messages.
     *
     * This never calls print_r (or any other routine that invokes a value's
     * __debugInfo()). A hostile __debugInfo() — one that throws or returns a
     * non-array — turns an otherwise catchable validation failure into an
     * uncatchable warning or a hard fatal, so objects are rendered by type name
     * only and arrays are walked element-wise with these same rules.
     */
    public static function value(mixed $value): string
    {
        return self::render($value, 0);
    }

    private static function render(mixed $value, int $depth): string
    {
        if ($value === null) {
            return 'null';
        }
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (\is_float($value)) {
            if (\is_nan($value)) {
                return 'NAN';
            }
            return (string) $value;
        }
        if (\is_int($value)) {
            return (string) $value;
        }
        if (\is_string($value)) {
            return $value;
        }
        if (\is_array($value)) {
            return self::renderArray($value, $depth);
        }

        // Objects, resources, and anything else: type name only, never __debugInfo.
        return \get_debug_type($value);
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private static function renderArray(array $value, int $depth): string
    {
        if ($depth >= self::MAX_DEPTH) {
            return '[...]';
        }

        $isList = \array_is_list($value);
        $parts = [];
        $rendered = 0;
        foreach ($value as $key => $element) {
            if ($rendered >= self::MAX_ELEMENTS) {
                $parts[] = '...';
                break;
            }
            $renderedElement = self::render($element, $depth + 1);
            $parts[] = $isList ? $renderedElement : self::render($key, $depth + 1) . ' => ' . $renderedElement;
            $rendered++;
        }

        return '[' . \implode(', ', $parts) . ']';
    }
}
