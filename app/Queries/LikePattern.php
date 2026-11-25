<?php

namespace App\Queries;

/**
 * Patterns for whereLike(), the one way the search forms match text: case-insensitive (ILIKE on
 * PostgreSQL, folding Cyrillic as well when the database has a UTF-8 aware locale).
 */
final class LikePattern
{
    /** "Contains the term", with the user's %, _ and \ matched literally. */
    public static function contains(string $term): string
    {
        return '%'.addcslashes($term, '\\%_').'%';
    }

    /** "Starts with the term", with the user's %, _ and \ matched literally. */
    public static function startsWith(string $term): string
    {
        return addcslashes($term, '\\%_').'%';
    }
}
