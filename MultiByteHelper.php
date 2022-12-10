<?php

class MultiByteHelper{

    /**
    * Check if char is multi byte
    * @param string $char
    * @return bool is multi byte
    */
    public static function is_multi_byte (string $char) : bool
    {
        return ord($char) >= 128;
    }

    /**
    * Tells how many positions are occupied by mb char
    * @param string $char
    * @return int positions occupied, 0 if it's not a mb.
    */
    public static function get_positions (string $char) : int
    {
        $bytes = ord($char);
        $positions = 0;

        if($bytes < 128){
            $positions = 1;
        } else if($bytes < 224) {
            $positions = 2;
        } else if($bytes < 240){
            $positions = 3;
        } else if($bytes < 248){
            $positions = 4;
        } else if($bytes == 252){
            $positions = 5;
        } else {
            $positions = 6;
        }

        return $positions;
    }

    /**
    * get a mb char starting from a certain position of a string
    * @param string $word
    * @param int $position
    * @return string mb char.
    */
    public static function get_character(string $word, int $position) : string
    {
        return substr($word, $position, MultiByteHelper::get_positions($word[$position]));
    }

}

?>