# Stump Changelog

### 0.6.0 (2016-03-16)

* **[NEW, BC]** Added support for PHP 7 `Throwable`
* **[NEW, BC]** Added separate message renderers to handle plain/ANSI rendering
* **[IMPROVED]** Open the stream in append mode to support actual files (not just STDOUT)

### 0.5.0 (2015-01-31)

* **[NEW]** Added optional support for ANSI color codes

### 0.4.1 (2015-01-18)

* **[FIXED]** Using the `{exception}` placeholder in a log message now correctly shows the exception message rather than the stack trace

### 0.4.0 (2015-01-15)

* **[NEW]** Added exception logging
* **[BC]** Removed sub-logger functionality

### 0.3.0 (2014-11-19)

* **[IMPROVED]** Improved readability of log output by moving the date to the start of the line and using a fixed-width log level string
* **[BC, FIXED]** `Logger::substitutePlaceholders()` is now (correctly) marked as `private`

### 0.2.0 (2014-10-25)

* **[BC]** Replaced `PrefixableLoggerInterface` with `ParentLoggerInterface` which allows for chaining of "sub-loggers"

### 0.1.1 (2014-10-24)

* **[IMPROVED]** Allow either version 2.* or 3.* of [icecave/isolator](https://github.com/isolator)

### 0.1.0 (2014-10-24)

* Initial release
