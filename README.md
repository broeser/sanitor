# sanitor
Sanitor is a thin wrapper around PHP's sanitization functions filter_…

## Goals

- Sanitor should be easy to use and easy to learn
- Sanitor should never implement its own fancy filters but just expose PHP's built-in functions in OOP-style

## Installation

The package is called broeser/sanitor and can be installed via composer:

composer require broeser/sanitor

## How to use

**IMPORTANT:** Sanitor does only sanitization – never try to use it as a
validation filter. It will not work as expected.

```PHP
<?php
/*
 * Example 1: Using filter()
 */
$value = 'mail@benedictroeser.de';
$sanitizer = new Sanitizer(FILTER_SANITIZE_EMAIL);
$sanitizedValue = $sanitizer->filter();

/*
 * Example 2: Using filterPost()
 */
$sanitizer = new Sanitizer(FILTER_SANITIZE_EMAIL);
$email = $sanitizer->filterPost('email');
```

It is up to you, how much of the Sanitor-package you use. If you'd like to also
sanitize objects, just let their class implement SanitizableInterface and use
the SanitizableTrait within them:

```PHP
<?php
/*
 * Example 3: Using SanitizableInterface and SanitizableTrait
 */
class Email implements Sanitor\SanitizableInterface {
   use Sanitor\SanitizableTrait;

   public function getRawValue() {
      return 'mail@benedictroeser.de';
   }

   public function getSanitizer() {
      return new Sanitor\Sanitizer(FILTER_SANITIZE_EMAIL);
   }
}

$myEmail = new Email();
$myFilteredEmail = $myEmail->getFilteredValue();

```


## List of important public methods

- **filter**($v) – corresponds to filter_var($v)
- **filterPost**($x) – corresponds to filter_input(INPUT_POST, $x)
- **filterGet**($x) – corresponds to filter_input(INPUT_GET, $x)
- **filterServer**($x) – corresponds to filter_input(INPUT_SERVER, $x)
- **filterEnv**($x) – corresponds to filter_input(INPUT_ENV, $x)
- **filterSession**($x) – _Experimental_ – enhancement to retrieve a filtered value from $_SESSION
- **filterRequest**($x) – _Experimental_ – enhancement to retrieve a filtered value from $_REQUEST

## Sanitor?

It is a pun on sanitization / sane / janitor. Probably not a good one, though.