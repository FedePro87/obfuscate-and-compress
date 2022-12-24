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
        $matches = RegexHelper::char_repetition_pattern($data);

        //key is pattern, value is how many times a pattern repeats.
        //is used to track previous patterns, and values in pattern is used to find where this pattern is positioned in $most_recurring_patterns
        $pattern_repeats = array();

        //Desc sort $matches, considering first more occurring patterns.
        for ($i = 0; $i < count($matches); $i++) {
            $pattern = $matches[$i][0];

            if (key_exists($pattern, $pattern_repeats)) {
                $previous_recurring_position = $pattern_repeats[$pattern];
                $pattern_repeats[$pattern] += 1;
                unset($pattern_repeats[$previous_recurring_position][$pattern]);
            } else {
                $pattern_repeats[$pattern] = 1;
            }
        }

        //Take first 31, then flatten.
        $pattern_repeats = array_slice($pattern_repeats, 0, 31);
        $more_recurring_patterns = array();
        array_walk_recursive($pattern_repeats, function ($repeats, $pattern) use (&$more_recurring_patterns) {
            $more_recurring_patterns[] = $pattern;
        });

        //Associate pattern with control character
        $control_characters = Ascii::get_excaped_control_characters();
        $recurring_patterns_length = count($more_recurring_patterns);
        $control_characters_association = array();
        $considering_control_character = 0;

        for ($i = 0; $i < $recurring_patterns_length; $i++) {
            $pattern = $more_recurring_patterns[$i];

            if (!key_exists($pattern, $control_characters_association)) {
                $control_characters_association[$pattern] = $control_characters[$considering_control_character];
                $considering_control_character++;
            }
        }

        $more_positions = 0;
        $pattern = "";
        $pattern_start = 0;
        $previous_char = "";

        //Traverse string, find repeated chars, pick control character and sobstitute in $data
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

            //If this char is different from previous, take care of pattern and substitute with control char.
            $char_length = strlen(trim($char));

            //Ricorda la fine!
            // $i === ($data_length - 1)

            //Can't have a pattern or a $previous_char at first letter or if pattern is empty or reached space.
            if ($i > 0 && $pattern_length > 0 && ($char !== $previous_char || $char_length < 1) || $i === ($data_length - 1)) {

                //If it's on last char, don't forget to add it to pattern if char is equal to previous.
                if ($i === ($data_length - 1) && $char === $previous_char) {
                    $pattern .= $char;
                    $pattern_length += 1;
                }

                //pattern is done. check if it's in $more_recurring_patterns and substitute. Ignore space.
                if (in_array($pattern, $more_recurring_patterns)) {
                    $control_character = $control_characters_association[$pattern];

                    //substitute starting with a offset of $pattern_start.
                    $control_character_length = strlen($control_character);
                    $ret_str = substr_replace($ret_str, $control_character, $pattern_start + $more_positions, $pattern_length);

                    $control_character_length = strlen($control_character);
                    $more_positions += ($control_character_length - $pattern_length);
                }

                $pattern = "";
                $previous_char = "";
            } else {
                //Ignore space
                if ($char_length > 0) {
                    $previous_char = $char;
                    $pattern .= $char;
                }
            }
        }

        return $ret_str;
    }
}
