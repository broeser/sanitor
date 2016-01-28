# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [1.1.1] - 2016-01-28
### Fixed
- If by accident a string, false or null was passed to **filterHas()** instead of
  one of the INPUT_…-constants, silently INPUT_POST was assumed. That has now
  been fixed and changed to throwing an Exception
- **filterEnv()** did not work at all
- **getFilteredValue()** without setting a Sanitizer did not throw the correct
  Exception (namespacing typo)

## [1.1.0] - 2016-01-27
### Added
- **filterHas()**-method, a wrapper for filter_has_var() with additional support
  for INPUT_SESSION and INPUT_REQUEST
- **AbstractSanitizable**-class, that can be extended. Provides similar
  functionality as SanitizableTrait in combination with SanitizableInterface
- Builds are now automatically tested by Travis CI
- Added CHANGELOG, CONTRIBUTING document and CODE_OF_CONDUCT

### Fixed
- **setSanitizeFlags()** did not work without explicitely specifying 
  FILTER_NULL_ON_FAILURE


## [1.0.0] - 2016-01-22
- Initial release

[Unreleased]: https://github.com/broeser/sanitor/compare/1.1.1...HEAD
[1.1.1]: https://github.com/broeser/sanitor/releases/tag/1.1.1
[1.1.0]: https://github.com/broeser/sanitor/releases/tag/1.1.0
[1.0.0]: https://github.com/broeser/sanitor/releases/tag/1.0.0