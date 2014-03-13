# Installation

The recommended way to install is via [composer](http://getcomposer.org). Run `composer require adamquaile/json-object-mapper` or make sure you have this in `composer.json`

    {
        "require": "adamquaile/json-object-mapper"
    }

and run `composer update`.

Next you choose a folder to be the root of your *database*. Let's say `/path/to/project/data`. To start using the mapper, you'll want an instance of the `EntityManager`.

    $em = new \AdamQuaile\JsonObjectMapper\EntityManager('/path/to/project/data');

From here you can start [Storing data](03-Storing_Data.md) and querying as described in [Querying](02-Querying.md)