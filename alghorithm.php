<?php

require_once('./Crypto.php');
require_once('./Compression.php');

/**
* Check if data has expcected conditions
* @param string $data
* @throws Exception If is not a string
* @throws Exception If string has no chars
* @throws Exception If length is more then 1_000_000_000
*/
function check_data(string $data) : void
{

    //must be a string
    if(!is_string($data)){
        throw new Exception("Data provided must be a string");
    }

    //str_len is O(1) while mb_strlen is O(n), but we must assume that string has multibyte characters.  
    $data_length = mb_strlen($data);

    //must be more then 0 length
    if($data_length < 1) {
        throw new Exception("String provided must have at least one character");
    }

    if($data_length > 1_000_000_000) {
        throw new Exception("String provided is loo long and must be max 1_000_000_000(included)");
    }

}

$data = "am生et l生orem ipsu生m do生lor生 s生it";
// $data = "amet lorem ipsum dolor sit";

check_data($data);

$obfuscated_data = Crypto::box_column_obfuscate($data);

echo "Data given : $data \n";
echo "Obfuscated data : ";
var_dump($obfuscated_data);

$compressed_data = Compression::ascii_controls_compression($obfuscated_data);
echo "Compressed data : ";
var_dump($compressed_data);
    
?>