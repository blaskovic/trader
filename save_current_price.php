<?php
//
// https://github.com/payward/kraken-api-client/blob/master/php/example.php
//

include_once 'functions.php'; 

$ticker = $argv[1];

$res = $kraken->QueryPublic('Ticker', array('pair' => $ticker));

$price = $res['result'][$ticker]['a'][0];

if(!is_numeric($price)) die('Not valid price, bad keypair?');

echo "--- [ $ticker is at $price ] ---\n";

$q = mysql_query("SELECT price FROM stock_prices WHERE stock = '$ticker' ORDER BY id DESC LIMIT 1");
$rows = result_array($q); 

if($rows[0]['price'] != $price) {
    mysql_query("INSERT INTO stock_prices SET stock='$ticker', price='".$res['result'][$ticker]['a'][0]."', date=NOW()") or die(mysql_error());
}

