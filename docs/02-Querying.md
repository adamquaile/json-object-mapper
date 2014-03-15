# Querying

*Assuming you have an instance of the `EntityManager` `$em`. If not, [see how to setup](01-Installation.md).*

## Find a single entity by ID

An ID is based on the filename relative to the data-store root you have setup. `/path/to/data/books/1984.json` would have an ID of `books/1984`.

To find an object by its ID:

    $book = $em->find('books/1984');

## Finding multiple entities of a type

To find all objects of a particular type (within a particular namespace / directory):

    $books = $em->findAll('books');

You will get back your object (or array of objects)

## More complex queries

As a second argument to `findAll`, you can pass in a `Query` object to filter the results. You can get this object from the `EntityManager` itself.

    // All books whose `title` equals `1984`
    $books = $em->findAll('books', $em->query()->equals('title', '1984'))

    // All books by George Orwell
    $books = $em->findAll('books', $em->query()->equals('author.name', 'George Orwell');

    // All books by people called george
    $books = $em->findAll('books', $em->query()->matches('author.name', '/george/i');

