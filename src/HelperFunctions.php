<?php

namespace HelperFunctions;

/**
 * HelperFunctions\HelperFunctions.
 *
 * Provides repeatable use functions that should have been part of PHP but weren't
 *
 * @author  David Hoffman
 *
 * @version 0.0.1
 */
class HelperFunctions
{
    /**
     * Turns a multiword string into a single word.
     *
     * @param string $string The String you want to slugify
     * @param string $joiner (optional) The string to concatinate with, default is -
     */
    public static function slugify($string, $joiner = '-')
    {
        return strtolower(
            str_replace(' ', $joiner, str_replace('  ', ' ', str_replace(',', '', str_replace('.', '', str_replace('&', '', ucwords(strtolower(str_replace('_', ' ', $string))))))))
        );
    }

    public static function hashtagify($string)
    {
        return '#'.str_replace(' ', '', str_replace(',', '', str_replace('.', '', str_replace('&', '', ucwords(strtolower(str_replace('_', ' ', $string)))))));
    }

    public static function buildHashtagArray($array_in)
    {
        $outArray = [];
        foreach ($array_in as $string) {
            $outArray[] = HelperFunctions::hashtagify($string);
        }

        return $outArray;
    }
}
