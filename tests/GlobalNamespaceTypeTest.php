<?php

declare(strict_types=1);

namespace {
    use StrongType\Constraint\Positive;
    use StrongType\Int\Integer;

    // Test fixture in the *global* namespace — verifies that constraint error
    // messages do not drop the first character of the short class name when
    // the FQCN contains no namespace separator.
    #[Positive]
    readonly class GlobalNamespacePositiveInt extends Integer
    {
    }
}

namespace StrongType\Tests {
    use StrongType\Exception\StrongTypeException;
    use PHPUnit\Framework\Attributes\Test;

    class GlobalNamespaceTypeTest extends \PHPUnit\Framework\TestCase
    {
        #[Test]
        public function testGlobalNamespaceErrorMessageRetainsFullClassName()
        {
            // Given / When
            try {
                new \GlobalNamespacePositiveInt(0);
                $this->fail('expected StrongTypeException');
            } catch (StrongTypeException $e) {
                // Then
                $this->assertStringStartsWith('GlobalNamespacePositiveInt type must', $e->getMessage());
            }
        }
    }
}
