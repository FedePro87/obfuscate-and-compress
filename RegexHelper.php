<?php

class RegexHelper {

    /**
     * Find consecutive occurances of the same characters inside a word.
     * @param string $data
     * @return array associative array with couple of repeated character as 0 index and position inside the word as first index.
     */
    public static function simple_double_pattern(string $data) : array
    {
        //([A-Za-z])\\1 would ignore mb characters, this regex with u modifier catch mbs too.
        $regex = "/(.)\\1/u";
        preg_match_all($regex, $data, $matches, PREG_OFFSET_CAPTURE);
        return $matches[0];
    }

    /**
     * Find multiple consecutive occurances of the same characters inside a word.
     * @param string $data
     * @return array array with repeated character as associative arrays with pattern as index 0 and first position as index 1.
     */
    public static function char_repetition_pattern(string $data) : array
    {
        //(.)\1{1,} more then 2 chars repetead to infinite
        $regex = "/(.)\\1{1,}/u";
        preg_match_all($regex, $data, $matches, PREG_OFFSET_CAPTURE);
        return $matches[0];
    }

}

?>