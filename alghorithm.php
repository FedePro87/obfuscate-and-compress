<?php

$debug = getenv('DEBUG') ?? false;

if($debug){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

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

$data = "am生et lm生rem ipsu生m do生lor生 s生生t";
echo "Data given : $data \n";

check_data($data);

$obfuscated_data = Crypto::box_column_obfuscate($data);

echo "Obfuscated data : ";
var_dump($obfuscated_data);

echo("Word to compress: $obfuscated_data\n");

$time_start = microtime(true);
$compressed_data = Compression::ascii_controls_compression($obfuscated_data);
echo "Compressed data : ";
var_dump($compressed_data);

$time_end = microtime(true);

if($debug){
    $execution_time = $time_end - $time_start;
    echo 'Total Execution Time: '.$execution_time.' Mins';
}

?>