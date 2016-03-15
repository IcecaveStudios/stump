# Stump

[![Build Status]](https://travis-ci.org/IcecaveStudios/stump)
[![Test Coverage]](https://coveralls.io/r/IcecaveStudios/stump?branch=develop)
[![SemVer]](http://semver.org)

**Stump** is a simple PSR-3 compliant logger for [Twelve-Factor](http://12factor.net/) applications.

* Install via [Composer](http://getcomposer.org) package [icecave/stump](https://packagist.org/packages/icecave/stump)
* Read the [API documentation](http://icecavestudios.github.io/stump/artifacts/documentation/api/)

## Example

The provided logger simply prints log output to `STDOUT`, as per the [Twelve-Factor Application logging recommendations](http://12factor.net/logs).

```php
use Icecave\Stump\Logger;

$logger = new Logger();
$logger->info("It's better than bad... it's good!");
```

The output of the example above is:

```
2014-10-24 16:26:13 INFO It's better than bad... it's good!
```

## Contact us

* Follow [@IcecaveStudios](https://twitter.com/IcecaveStudios) on Twitter
* Visit the [Icecave Studios website](http://icecave.com.au)
* Join `#icecave` on [irc.freenode.net](http://webchat.freenode.net?channels=icecave)

<!-- references -->
[Build Status]: http://img.shields.io/travis/IcecaveStudios/stump/develop.svg?style=flat-square
[Test Coverage]: http://img.shields.io/coveralls/IcecaveStudios/stump/develop.svg?style=flat-square
[SemVer]: http://img.shields.io/:semver-0.6.0-yellow.svg?style=flat-square
