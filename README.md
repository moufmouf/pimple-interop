Pimple-interop
==============

This package contains an extension to the Pimple DI container that makes Pimple compatible with the [container-interop](https://github.com/container-interop/container-interop) API.

How to use it?
--------------
Instead of using the `Pimple` class, you can use the `PimpleInterop` class. This class extends `Pimple`.
`PimpleInterop` implements `ReadableContainerInterface` and `ParentAwareContainerInterface`. This means
you can access your Pimple entries by using the `get` and `has` methods, and that you can chain containers
using the `setParentContainer` method.

Here is a sample chaining 2 Pimple instances (in the real world, you would rather chain Pimple with another DI container,
of course:

```
// Let's declare a first container
$pimpleParent = new PimpleInterop();
$pimpleParent['hello'] = 'world';

// Let's declare another container
$pimple = new PimpleInterop();
$pimple->setParentContainer($pimpleParent);

// Prints "world".
echo $pimple->get('hello');
```

Why the need for this package?
------------------------------
The excellent [Acclimate](https://github.com/jeremeamia/acclimate-container) can already provide an adapter around Pimple.
The adapter implements the `ReadableContainerInterface` (or a similar interface).

The adapter design pattern could also be used to implement the `ParentAwareContainerInterface`. However, there are
situations where it might be impractical.

For instance, the Silex MVC microframework is directly extending the Pimple class. We could fork the Silex MVC microframework 
to make it use PimpleInterop instead of Pimple easily. However, it would be almost impossible for Silex 
to use the adapted Pimple instance from Acclimate (because Silex relies on all the methods of Pimple that are not implemented
by the adapter).
