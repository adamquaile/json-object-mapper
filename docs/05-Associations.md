# Associations

If we want to have a small object graph, we can link two objects together.

Take `books/1984.json`

    {
        "title": "1984",
        "author": "@authors/george-orwell"
    }

This will populate `$book->author` with the full author object. The same also works for arrays.

    {
        "name": "George Orwell",
        "books": ["@books/1984", "@books/animal-farm"]
    }

You can now use `$author->books[0]->title` (or `$author->getBooks()[0]->getTitle()`) depending on how your [mappings](04-Custom_Mappings.md) are set up.