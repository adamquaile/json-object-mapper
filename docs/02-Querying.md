# Querying

*Assuming you have an instance of the `EntityManager` `$em`. If not, [see how to setup](01-Installation.md).*

## Find a single entity by ID

An ID is based on the filename relative to the data-store root you have setup. `/path/to/data/books/1984.json` would have an ID of `books/1984`.

To find an object by its ID:

    $book = $em->find('books/1984');

To find all objects of a particular type:

    $books = $em->findAll('books');

You will get back your object (or array of objects)

