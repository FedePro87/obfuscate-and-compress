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

        $arr = explode(" ", $data); //O(N)
        $data_length = count($arr); //O(1)

        //If $arr has only 1 element, this means that has not spaces so return immediately word given.
        if ($data_length < 2) {
            return $data;
        }

        //Start from first word and traversing it, then jump to next row but skip it if is lesser then $column.
        //Countinue till have visited all rows.
        $column = 0;
        $row = 0;
        $ret_str = "";

        while ($row < $data_length) {

            $current_string_length = strlen($arr[$row]);

            //Jump directly to next iteration if column is ahead current string length (have already visited this column)
            if ($column >= $current_string_length) {
                $row++;
                continue;
            }

            //Add space before always except start.
            if ($column !== 0) {
                $ret_str .= " ";
            }

            $current_char = $arr[$row][$column];

            //check if current char is mb and if it's, remove positions from $current_string_length.
            //Useful because $current_string_length tracks current word length.
            if (MultiByteHelper::is_multi_byte($current_char)) {
                //if it's, remove n from $current_string_length
                $current_string_length -= MultiByteHelper::get_positions($current_char);
            }

            //Traverse current word.
            for ($i = 0; $i < $data_length; $i++) {

                $element = $arr[$i];

                //Get character at correct position assuming it may be multibyte.
                $char = MultiByteHelper::get_character($element, $column);

                //if it's multibyte, replace mb with a placeholder (in this case 'n') so next characters will be at correct position.
                if (MultiByteHelper::is_multi_byte($char)) {
                    $arr[$i] = substr_replace($element, 'n', $column, MultiByteHelper::get_positions($char));
                }

                $ret_str .= $char;
            }

            //next $column can be simply + 1 because we added already all chars before this position, even if going to next $row.
            $column++;

            //If column is ahead $current_string_length go to next $row, update accordingly $current_string_length.
            if ($column > $current_string_length) {
                $row++;
                $current_string_length = strlen($arr[$row]); //O(1)
            }
        }

        return $ret_str;
    }

}
