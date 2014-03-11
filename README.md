# JSON Object Mapper

Small library to read JSON files from a directory and turn them into PHP objects. Designed for simple content authoring and model separation by developers without the need for a whole DBMS.

**Not intended as any kind of DBMS, or application writable persistence layer**

## Installation

    composer require adamquaile/json-object-mapper

## Usage

    <?php

    require __DIR__.'/vendor/autoload.php';

    $manager = new \AdamQuaile\JsonObjectMapper\EntityManager('/path/to/storage');

    // Either
    $manager->find('object-id');

    // or

    $manager->findAll('sub-folder');

