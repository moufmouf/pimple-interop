Pimple-interop
==============
[![Latest Stable Version](https://poser.pugx.org/mouf/pimple-interop/v/stable.svg)](https://packagist.org/packages/mouf/pimple-interop)
[![Latest Unstable Version](https://poser.pugx.org/mouf/pimple-interop/v/unstable.svg)](https://packagist.org/packages/mouf/pimple-interop)
[![License](https://poser.pugx.org/mouf/pimple-interop/license.svg)](https://packagist.org/packages/mouf/pimple-interop)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/98d5c42d-9d46-4e73-936d-6eac8b92b3c3/mini.png)](https://insight.sensiolabs.com/projects/98d5c42d-9d46-4e73-936d-6eac8b92b3c3)

This package contains an extension to the [Pimple DI container](http://pimple.sensiolabs.org/) that makes Pimple 1 compatible with the [container-interop](https://github.com/container-interop/container-interop) API.

How to use it?
--------------
Instead of using the `Pimple` class, you can use the `PimpleInterop` class. This class extends `Pimple`.
`PimpleInterop` implements `ContainerInterface`. This means
you can access your Pimple entries by using the `get` and `has` methods.
`PimpleInterop` constructor accepts an optional "root" container as a first argument. This means you can chain `PimpleInterop` with
another container. Dependencies will be fetched from the "root" container rather than from PimpleInterop.

Here is a sample chaining 2 Pimple instances (in the real world, you would rather chain Pimple with a composite container than contains
all the DI containers you are working with):

```php
// Let's declare a first container
$pimpleParent = new PimpleInterop();
$pimpleParent['hello'] = 'world';

// Let's declare another container
// Please note the "parent" container is passed in parameter of the constructor.
$pimple = new PimpleInterop($pimpleParent);
$pimple['test']->share(function(ContainerInterop $container) {
	return "Hello ".$container->get('hello');
});

// Prints "Hello world".
echo $pimple->get('test');
// Prints "Hello world" too.
echo $pimple['test'];
```

Why the need for this package?
------------------------------
This package is part of a long-term effort to bring [interoperability between DI containers](https://github.com/container-interop/container-interop). The ultimate goal is to
make sure that multiple containers can communicate together by sharing entries (one container might use an entry from another
container, etc...)


But can't we already do this using Acclimate?
---------------------------------------------
The excellent [Acclimate library](https://github.com/jeremeamia/acclimate-container) can already provide an adapter around Pimple.
The adapter implements the `ContainerInterface`.

However, the adapter design pattern cannot be used to have Pimple delegate its dependencies fetching to another
container. Indeed, to implement this feature, you need to modify the very behaviour of the container, 
and the adapter design pattern is not always well suited for this. 

Also, there are other cases where the adapter design pattern is not enough. For instance, the Silex MVC microframework 
is directly extending the Pimple class. We could fork the Silex MVC microframework 
to make it use PimpleInterop instead of Pimple easily. However, it would be almost impossible for Silex 
to use the adapted Pimple instance from Acclimate (because Silex relies on all the methods of Pimple that are not implemented
by the adapter).
