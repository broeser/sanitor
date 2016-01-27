# Sanitor
Sanitor is a thin wrapper around PHP's sanitization functions filter_…

[![Build Status](https://travis-ci.org/broeser/sanitor.svg?branch=master)](https://travis-ci.org/broeser/sanitor)
[![codecov.io](https://codecov.io/github/broeser/sanitor/coverage.svg?branch=master)](https://codecov.io/github/broeser/sanitor?branch=master)
[![License](http://img.shields.io/:license-mit-blue.svg)](http://mit-license.org)
[![SemVer 2.0.0](https://img.shields.io/badge/semver-2.0.0-blue.svg)](http://semver.org/spec/v2.0.0.html)

Latest stable version: 1.1.1

## Why?

- PHP's filter extension is a bit buggy, INPUT_ENV does not work properly, Sanitor can handle it
- PHP's filter extension currently does not support INPUT_SESSION and INPUT_REQUEST, Sanitor does
- PHP's filter extension does not offer object oriented syntax, Sanitor does
- You can build sanitizable data objects with Sanitor

## Goals

- Sanitor should be easy to use and easy to learn
- Sanitor should never implement its own fancy filters but just expose PHP's 
  built-in filters in OOP-style

## Installation

Sanitor works with PHP 5.4, 5.5., 5.6 and 7.0.

The package can be installed via composer:

``composer require broeser/sanitor``

## How to use

**IMPORTANT:** Sanitor does only sanitization – never try to use it as a
validation filter. It will not work as expected.

### Basic usage _with filter(), filterGet(), filterPost(), filterRequest(), filterCookie() etc._

```PHP
<?php
/*
 * Example 1: Using filter()
 */
$value = 'mail@benedictroeser.de';
$sanitizer = new Sanitor\Sanitizer(FILTER_SANITIZE_EMAIL);
$sanitizedValue = $sanitizer->filter($value);

/*
 * Example 2: Using filterPost()
 */
$sanitizer = new Sanitor\Sanitizer(FILTER_SANITIZE_EMAIL);
$email = $sanitizer->filterPost('email');
```

The constructor takes the filter as first argument and, optionally, flags as
second argument. The FILTER_NULL_ON_FAILURE-flag, that is used internally is 
always set by default, so you don't have to set it.

List of important public methods of **Sanitizer**:

- **filter**($v) – corresponds to filter_var($v)
- **filterCookie**($x) – corresponds to filter_input(INPUT_COOKIE, $x)
- **filterEnv**($x) – an actually working alternative to filter_input(INPUT_ENV, $x)
- **filterGet**($x) – corresponds to filter_input(INPUT_GET, $x)
- **filterHas($type, $y)** – enhanced version of filter_has_var($type, $y)
- **filterPost**($x) – corresponds to filter_input(INPUT_POST, $x)
- **filterRequest**($x) – enhancement to retrieve a filtered value from $_REQUEST
- **filterServer**($x) – corresponds to filter_input(INPUT_SERVER, $x)
- **filterSession**($x) – _Experimental_ – enhancement to retrieve a filtered value from $_SESSION

If something went wrong while trying to filter, a **SanitizationException** is 
thrown. If anything else fails (e.g. a parameter was given in a different format
than expected, a normal \Exception is thrown.

### Changing the filter or flag

While usefulness might be debateable, you can change the filter and flags of an
existing Sanitizer with the **setSanitizeFilter()**, **setSanitizeFlags()** and
**addSanitizeFlag()**-methods.

### Sanitize objects _with SanitizableInterface and SanitizableTrait or AbstractSanitizable_

If you'd like to sanitize objects, just let their class implement 
SanitizableInterface and use the SanitizableTrait within them. You have to 
implement **getRawValue()** to return the "raw", unfiltered value of your 
object and **getSanitizer()** to return the Sanitizer-class that shall be used 
to filter this value:

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

In case you prefer extending an abstract class, you can use 
**AbstractSanitizable**. That class (partly) implements SanitizableInterface and
uses SanitizableTrait. It already contains a getSanitizer()-method returning 
$this->sanitizer, make sure to set it somewhere or override the method.

## Contributing?

Yes, please!

See [CONTRIBUTING.md](CONTRIBUTING.md) for details and/or open an issue with your questions.

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). 
By participating in this project you agree to abide by its terms.


## Sanitor?

It is a pun on sanitization / sane / janitor. Probably not a good one, though.