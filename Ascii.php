<?php

class Ascii{

    private $control_characters = ["\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07","\x08","\x09","\x0a","\x0b","\x0c","\x0d","\x0e","\x0f","\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17","\x18","\x19","\x1a","\x1b","\x1c","\x1d","\x1e","\x1f"];

    /**
    * @return Ascii characters from 0x00 to 0x1f
    */
    public function get_control_characters() : array
    {
        return Ascii::$control_characters;
    }

}

?>