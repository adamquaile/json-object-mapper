# JSON Object Mapper

[![Build Status](https://travis-ci.org/adamquaile/json-object-mapper.png?branch=master)](https://travis-ci.org/adamquaile/json-object-mapper)

Small library to read JSON files from a directory and turn them into PHP objects. Designed for simple content authoring and model separation by developers without the need for a whole DBMS.

**Not intended as any kind of DBMS, or application writable persistence layer**


## Main Features

 - Small / simple [query API](docs/02-Querying.md)
 - Can either [map to objects you define](docs/04-Custom_Mappings.md), or use default provided (behaviour similar to `stdClass`)

## Installation

    composer require adamquaile/json-object-mapper


## Usage

**Full documentation [here](docs)**

    <?php

    require __DIR__.'/vendor/autoload.php';

    $manager = new \AdamQuaile\JsonObjectMapper\EntityManager('/path/to/storage');

    // Either
    $book = $manager->find('books/1984');
    $book->isbn;
    $book->getTitle(); // etc

    // or

    $books = $manager->findAll('books');
    $books[0]->isbn
    $books[0]->getTitle() // etc

    // or

    $books = $manager->findAll('books', $manager->query()->matches('author.name', '/george/i'));
    $books[0]->getTitle() // etc

