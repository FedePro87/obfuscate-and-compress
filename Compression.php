<?php
require_once('./Ascii.php');
require_once('./RegexHelper.php');

class Compression
{

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
        $matches = RegexHelper::simple_double_pattern($data);

        //Return directly given word if no matches.
        $matches_number = count($matches); //O(1)

        if ($matches_number < 1) {
            return $data;
        }

        //Find out which are more recurring pattern.
        $most_recurring_patterns = array();

        foreach ($matches as &$match) {
            $pattern = $match[0];

            if (key_exists($pattern, $most_recurring_patterns)) {
                $most_recurring_patterns[$pattern] += 1;
            } else {
                $most_recurring_patterns[$pattern] = 1;
            }
        }

        //sort.
        arsort($most_recurring_patterns); //O(n)

        //take at least 31.
        $most_recurring_patterns = array_slice($most_recurring_patterns, 0, 31); //O(offset + length), so 31

        $control_characters = Ascii::get_excaped_control_characters();
        $more_positions = 0;
        $visited_patterns = array();
        $visited_control_characters = 0;

        for ($i = 0; $i < $data_length; $i++) {
            //remember that char may be multibyte so get it with MultiByteHelper::get_character
            $current_char = MultiByteHelper::get_character($data, $i);

            //get current and next characters. Manage if any of them is multibyte.
            //if $char is multibyte, go to next position adding positions occupied by this $char.
            if (MultiByteHelper::is_multi_byte($current_char)) {
                $i += (MultiByteHelper::get_positions($current_char) - 1);
            }

            $next_char = MultiByteHelper::get_character($data, $i + 1);

            //if current char or next char are spaces, ignore.
            if (strlen(trim($current_char)) < 1 || strlen(trim($next_char)) < 1) {
                continue;
            }

            //put them together and check if they exists in $most_recurring_patterns.
            //if they exist substitute wit control char.
            $current_pattern = $current_char . $next_char;

            //Without correct length (pattern may contain one one or more mb chars), $substr will take wrong offset.
            $pattern_length = mb_strlen($current_pattern);

            //key_exists is O(n), but $control_chars_map has 31 length in worst case.
            if (key_exists($current_pattern, $most_recurring_patterns)) {
                $control_character = "";

                //ensure this pattern repeat at least two times
                if ($most_recurring_patterns[$current_pattern] < 2) {
                    continue;
                }

                //if this pattern has been already associated to a control character use it, else select next control character.
                if (key_exists($current_pattern, $visited_patterns)) {
                    $control_character = $visited_patterns[$current_pattern];
                } else {
                    $control_character = $control_characters[$visited_control_characters];
                    $visited_patterns[$current_pattern] = $control_character;
                    $visited_control_characters++;
                }

                $ret_str = substr_replace($ret_str, $control_character, $i + $more_positions, $pattern_length);
                $more_positions += (strlen($control_character) - mb_strlen($current_pattern));
            }
        }

        return $ret_str;
    }
}
