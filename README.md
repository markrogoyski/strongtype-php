# StrongType PHP

Strong types for your PHP code.


Quick Reference
-----------

#### Ints
| StrongType | Description | Details |
| ----------- | ----------- | ----------- |
| [`NegativeInt`](#Ints) | Negative integer| < 0 |
| [`NonnegativeInt`](#Ints) | Nonnegative integer| >= 0 |
| [`NonpositiveInt`](#Ints) | Nonpositive integer| <= 0 |
| [`NonzeroInt`](#Ints) | Nonzero integer| < 0 or > 0 |
| [`PositiveInt`](#Ints) | Positive integer| > 0 |

#### Floats
| StrongType | Description | Details |
| ----------- | ----------- | ----------- |
| [`NegativeFloat`](#Floats) | Negative float| < 0.0 |
| [`NonnegativeFloat`](#Floats) | Nonnegative float| >= 0.0 |
| [`NonpositiveFloat`](#Floats) | Nonpositive float| <= 0.0 |
| [`NonzeroFloat`](#Floats) | Nonzero float| < 0.0 or > 0.0 |
| [`PositiveFloat`](#Floats) | Positive float| > 0.0 |

#### Strings
| StrongType | Description | Details |
| ----------- | ----------- | ----------- |
| [`AlphanumericString`](#Strings) | Alphanumeric string | [a-zA-Z0-9]+ |
| [`AlphaString`](#Strings) | Alphabetic string| [a-zA-Z]+ |
| [`BinaryString`](#Strings) | Binary digit string | [01]+ |
| [`EmptyString`](#Strings) | Empty string | "" |
| [`HexString`](#Strings) | Hexadecimal string | [a-fA-F0-9]+ |
| [`LowercaseAlphaString`](#Strings) | Lowercase alphabetic string | [a-z]+ |
| [`NonemptyString`](#Strings) | Nonempty string | Anything but "" |
| [`NumericString`](#Strings) | Numeric string | [0-9]+ |
| [`UppercaseAlphaString`](#Strings) | Uppercase alphabetic string | [A-Z]+ |

Quick Overview
-----------
PHP has basic scalar types. But even with them, you often find yourself writing repetitive validations on them.

```php
class Person
{
    private string $name;
    private int    $age;
    private array  $hobbies;

    public function __construct(string $name, int $age, array $hobbies)
    {
        if (strlen($name) === 0) {
            throw new \RuntimeException('Name cannot be empty');
        }
        if ($age < 0) {
            throw new \RuntimeException('Age cannot be negative');
        }
        foreach ($hobbies as $hobby) {
            if (!is_string($hobby)) {
                throw new \RuntimeException('Hobbies must be strings');
            }
        }
        $this->name    = $name;
        $this->age     = $age;
        $this->hobbies = $hobbies;
    }
}
```

StrongTypes allow you to write cleaner, safer, self-documenting code with built-in validations.

```php
class Person
{
    private string $name;
    private int    $age;
    private array  $hobbies;

    public function __construct(NonemptyString $name, NonnegativeInt $age, ArrayOfStrings $hobbies)
    {
        $this->name    = $name->getValue();
        $this->age     = $age->getValue();
        $this->hobbies = $hobbies->getValue();
    }
}
```

Setup
-----

 Add the library to your `composer.json` file in your project:

```javascript
{
  "require": {
      "markrogoyski/strongtype-php": "0.*"
  }
}
```

Use [composer](http://getcomposer.org) to install the library:

```bash
$ php composer.phar install
```

Composer will install StrongType inside your vendor folder. Then you can add the following to your
.php files to use the library with Autoloading.

```php
require_once __DIR__ . '/vendor/autoload.php';
```

Alternatively, use composer on the command line to require and install StrongType:

```
$ php composer.phar require markrogoyski/strongtype-php:0.*
```

#### Minimum Requirements
 * PHP 7.4


Usage
-----


## Ints

```php
use StrongType\Int\{NegativeInt, NonnegativeInt, NonpositiveInt, NonzeroInt, PositiveInt};

$positiveInt = new PositiveInt(5);
$negativeInt = new NegativeInt(-5);

$nonnegativeInt = new NonnegativeInt(4);
$nonpositiveInt = new NonpositiveInt(0);

$nonzeroInt = new NonzeroInt(5);
```

## Floats

```php
use StrongType\Float\{NegativeFloat, NonnegativeFloat, NonpositiveFloat, NonzeroFloat, PositiveFloat};

$positiveFloat = new PositiveFloat(5);
$negativeFloat = new NegativeFloat(-5);

$nonnegativeFloat = new NonnegativeFloat(4);
$nonpositiveFloat = new NonpositiveFloat(0);

$nonzeroFloat = new NonzeroFloat(5);
```

Standards
---------

StrongType PHP conforms to the following standards:

 * PSR-1  - Basic coding standard (http://www.php-fig.org/psr/psr-1/)
 * PSR-4  - Autoloader (http://www.php-fig.org/psr/psr-4/)
 * PSR-12 - Extended coding style guide (http://www.php-fig.org/psr/psr-12/)

License
-------

StrongType PHP is licensed under the MIT License.