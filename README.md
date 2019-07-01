[![Latest Stable Version]](https://packagist.org/packages/wikimedia/services) [![License]](https://packagist.org/packages/wikimedia/services)

Services
=====================

A [PSR-11][]-compliant services framework.
Services are created by instantiators (callables),
which are usually defined in separate wiring files.

Usage
-----

```php
$services = new ServiceContainer();

$services->defineService(
    'MyService',
    function ( ServiceContainer $services ) {
        return new MyService();
    }
);

$services->loadWiringFiles( [
    'path/to/ServiceWiring.php',
] );
```

Where `ServiceWiring.php` looks like this:

```php
return [

    'MyOtherService' => function ( ServiceContainer $services ) {
        return new MyOtherService( $services->get( 'MyService' ) );
    },

    // ...

];
```

Each instantiator receives the service container as the first argument,
from which it may retrieve further services as needed.
Additional arguments for each instantiator may be specified
when constructing the `ServiceContainer`.

Custom subclasses of `ServiceContainer`
may offer easier access to certain services:

```php
class MyServiceContainer extends ServiceContainer {

    public function getMyService(): MyService {
        return $this->get( 'MyService' );
    }

    public function getMyOtherService(): MyOtherService {
        return $this->get( 'MyOtherService' );
    }

}

// ServiceWiring.php
return [

    'MyOtherService' => function ( MyServiceContainer $services ) {
        return new MyOtherService( $services->getMyService() );
    },

];
```

Running tests
-------------

    composer install --prefer-dist
    composer test


History
-------

Review and update this section.
This library was first introduced in [MediaWiki 1.16][] ([r52203][]). It was
split out of the MediaWiki codebase and published as an independent library
during the [MediaWiki 1.25][] development cycle.


---
[PSR-11]: https://www.php-fig.org/psr/psr-11/
[MediaWiki 1.16]: https://www.mediawiki.org/wiki/MediaWiki_1.16
[r52203]: https://www.mediawiki.org/wiki/Special:Code/MediaWiki/52203
[MediaWiki 1.25]: https://www.mediawiki.org/wiki/MediaWiki_1.25
[Latest Stable Version]: https://poser.pugx.org/wikimedia/services/v/stable.svg
[License]: https://poser.pugx.org/wikimedia/services/license.svg

