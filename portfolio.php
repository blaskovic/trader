<?php
include 'functions.php';

$res = $kraken->QueryPrivate('Balance');
//print_r($res);
$balance = 0;
foreach($res['result'] as $ticker => $amount) {

    if($ticker != 'ZEUR') {
        $res = $kraken->QueryPublic('Ticker', array('pair' => $ticker.'ZEUR'));
        $pair = array_keys($res['result'])[0];
        $price = $res['result'][$pair]['a'][0];
    } else {
        $price = 1;
    }

    $total = $price * $amount;
    $balance += $total;

    echo $ticker ."\t" . $amount ."x | ". number_format($price, 2, '.', '') . " | " . number_format($total, 2, '.', '') .PHP_EOL;
}

echo 'Total: ' . number_format($balance, 2, '.', '').PHP_EOL;



#$res = $kraken->QueryPrivate('ClosedOrders', array('trades' => true));
#print_r($res);


