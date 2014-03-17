# Storing data

## The basics

Assuming everything is [installed](01-Installation.md) and all directories are relative to `/path/to/project/data`.

Organise your data in folders, so for example to store information about books and their authors, you could have 2 folders, `books/` and `authors/`.

    books/
        1984.json
        crime-and-punishment.json
        great-gatsby.json
    authors/
        george-orwell.json
        scott-fitzgerald.json

In each of these files, place a JSON dictionary containing data about that object. E.g. `books/1984.json`

    {
        "title": "1984"
        "genre": "fiction"
    }

You will then be able to [query this data](02-Querying.md)

## Sorting

By default, items will be sorted naturally using [`strnatcasecmp`](http://us3.php.net/manual/en/function.strnatcasecmp.php) on their filename.
You may wish to add `01-`, `02-` to your files to order them. This will not affect the ID returned, or the ID required to search.

