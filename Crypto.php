<?php

require_once('./MultiByteHelper.php');

class Crypto
{

    /**
     * Imaginatively arrange characters of a word in a square, removing all spaces then
     * recompose words by selecting characters by columns
     * ex: given word "lorem ipsum dolor sit amet"  
     * 
     * resulting square:  
     * lorem  
     * ipsum  
     * dolor  
     * sit  
     * amet  
     * 
     * result: lidsa opoim rslte euot mmr
     * @param string $data
     * @return string crypt data
     */
    public static function box_column_obfuscate(string $data): string
    {

        $data_mb_length = mb_strlen($data);
        $data_length = strlen($data);
        $box_rows = floor(sqrt($data_mb_length));
        $column_strings = array();
        $row = 0;
        $column = 0;

        //traverse string, adding each element to $column_strings depending on $column.
        for ($i = 0; $i < $data_length; $i++) {

            //avoid spaces
            if (strlen(trim($data[$i])) === 0){
                continue;
            }

            $char = $data[$i];

            //if it's multibyte, get char and increment i by more positions occupied by mb char.
            if (MultiByteHelper::is_multi_byte($char)) {
                $char = MultiByteHelper::get_character($data, $i);
                $more_positions = MultiByteHelper::get_positions($char);
                $i += ($more_positions - 1);
            }

            if(key_exists($column, $column_strings)) {
                $column_strings[$column] .= $char;
            } else {
                $column_strings[$column] = $char;
            }

            $column++;
            
            if($column >= $box_rows) {
                $column = 0;
                $row++;
            }
        }

        return join(" ", $column_strings);
    }
}
