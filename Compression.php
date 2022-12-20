<?php
require_once('./Ascii.php');

class Compression
{

    /**
     * find patterns in a word visualizing it as a serie of words divided by a space.
     * sort already while building
     * @param string $data
     * @return array at least 31 most recurring patterns
     */
    public static function quadratic_matches(string $data): array
    {
        $data_length = strlen($data);
        $pattern = "";
        $max_pattern = 0;

        //key is pattern, value is how many times a pattern repeats.
        //is used to track previous patterns, and values in pattern is used to find where this pattern is positioned in $most_recurring_patterns
        $matches = array();

        //key is how many times a pattern repeats, value is another map with pattern itself and how many times it repeats.
        //is used to arrange (so sort) repeating patterns
        $most_recurring_patterns = array();

        //Parse $pattern until it finds a space.
        for ($i = 0; $i < $data_length; $i++) {
            $char = $data[$i];

            //if it's multibyte, get char and increment i by more positions occupied by mb char.
            if (MultiByteHelper::is_multi_byte($char)) {
                $char = MultiByteHelper::get_character($data, $i);
                $more_positions = MultiByteHelper::get_positions($char);
                $i += ($more_positions - 1);
            }

            //If this is a space, add $pattern to $matches if it doesn't exists, else add +1
            $char_length = strlen(trim($char));

            //Add to patterns if reached space or reached end of string.
            if ($char_length < 1 || $i === ($data_length - 1)) {

                //If it's on last char, don't forget to add it to pattern.
                if ($i === ($data_length - 1)) {
                    $pattern .= $char;
                }

                //If $pattern was already found, increment its value in $matches by 1 then manage it in $recurring_patterns as well.
                if (key_exists($pattern, $matches)) {
                    $previous_recurring_position = $matches[$pattern];
                    $matches[$pattern] += 1;
                    $current_recurring_position = $matches[$pattern];
                    unset($most_recurring_patterns[$previous_recurring_position][$pattern]);
                    $most_recurring_patterns[$current_recurring_position][$pattern] = $current_recurring_position;

                    if ($current_recurring_position > $max_pattern) {
                        $max_pattern = $current_recurring_position;
                    }
                } else {
                    $matches[$pattern] = 1;
                    $most_recurring_patterns[1][$pattern] = 1;
                }

                $pattern = "";
            } else {
                $pattern .= $char;
            }
        }

        //Only a restricted number of patterns are needed (in our case 31). Take only those and return a sorted array with first (max) 31 recurring.
        //$max_pattern is the pattern with more occurances in the word.
        $sorted_restricted_patterns = array();
        $added_patterns = 0;

        for ($i = $max_pattern; $i > 0; $i--) {

            //If some of the occurances were unset in previous stage, this array will be empty.
            $patterns = $most_recurring_patterns[$i];

            foreach ($patterns as $pattern => $occurances) {
                $sorted_restricted_patterns[] = $pattern;
                $added_patterns++;

                if ($added_patterns > 31) {
                    break;
                }
            }
        }

        return $sorted_restricted_patterns;
    }

    /**
     * Parse the string (it may contains mb chars) for any recurring patterns and replace them with a non-printing character
     * The best would be, if the number of repetitions were to exceed the number of non-printable characters (31), for the algorithm to favor the replacement of the most frequent repetitions, in order to optimize the saving of memory space
     * To carry out this procedure you can use all the "safe" characters, i.e. all ASCII characters from 0x00 to 0x1F inclusive.  
     * Ex input:  
     *   
     * mmdsm opoie rsltt euoa mmr  
     *   
     * Ex output:  
     *   
     * \x00dsm opoie rsltt euoa \x00r
     * @param string $data
     * @return string compressed string
     */
    public static function ascii_controls_compression(string $data): string
    {
        $ret_str = $data;
        $data_length = strlen($data);
        $most_recurring_patterns = Compression::quadratic_matches($data);

        $control_characters = Ascii::get_excaped_control_characters();
        $more_positions = 0;
        $pattern = "";
        $pattern_start = 0;
        $visited_patterns = array();
        $visited_control_characters = 0;

        for ($i = 0; $i < $data_length; $i++) {
            $char = $data[$i];

            //if pattern is empty, pattern starts here
            $pattern_length = strlen(trim($pattern));

            if ($pattern_length < 1) {
                $pattern_start = $i;
            }

            //if it's multibyte, get char and increment i by more positions occupied by mb char.
            if (MultiByteHelper::is_multi_byte($char)) {
                $char = MultiByteHelper::get_character($data, $i);
                $i += (MultiByteHelper::get_positions($char) - 1);
            }

            //If this is a space, take care of pattern and substitute with control char.
            $char_length = strlen(trim($char));

            //Add to patterns if reached space or reached end of string.
            if ($char_length < 1 || $i === ($data_length - 1)) {

                //If it's on last char, don't forget to add it to pattern.
                if ($i === ($data_length - 1)) {
                    $pattern .= $char;
                    $pattern_length += 1;
                }
            }

            //key_exists is O(n), but $most_recurring_patterns has 31 length in worst case.
            if (in_array($pattern, $most_recurring_patterns)) {

                $control_character = "";

                //if this pattern has been already associated to a control character use it, else select next control character.
                if (key_exists($pattern, $visited_patterns)) {
                    $control_character = $visited_patterns[$pattern];
                } else {
                    $control_character = $control_characters[$visited_control_characters];
                    $visited_patterns[$pattern] = $control_character;
                    $visited_control_characters++;
                }

                //substitute starting with a offset of $pattern_start.
                $control_character_length = strlen($control_character);
                $ret_str = substr_replace($ret_str, $control_character, $pattern_start + $more_positions, $pattern_length);

                $control_character_length = strlen($control_character);
                $more_positions += ($control_character_length - $pattern_length);
                $pattern = "";
            } else {
                $pattern .= $char;
            }
        }

        return $ret_str;
    }
}
