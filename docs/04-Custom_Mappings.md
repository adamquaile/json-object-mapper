# Custom Mapping

Keeping with the books and authors examples from [here](03-Storing_Data.md), let's move on from using simple naïve `stdObject`s.

Let's create a new class, `\Acme\Bookstore\Book` and `\Acme\Bookstore\Author`.

```php
<?php

namespace \Acme\Bookstore;

class Book {
    // ...

    public function setTitle() {...}
    public function getTitle() {...}
}

```

```php
<?php

namespace \Acme\Bookstore;

class Author {
    // ...

    public function setName() {...}
    public function getName() {...}
}

```

To make sure all books are mapped to the `Book` class, add a file inside the `books/` directory, `books/_meta.json` with the following JSON:

    {
        "class": "\Acme\Bookstore\Book"
    }

If you only want to affect a single file in a directory, add inside the object's JSON file, `books/1984.json`:

    {
        "title": "1984",
        "_meta": {
            "class": "\Acme\Bookstore\Book"
        }
    }

This will take precedence over values set at a directory level.