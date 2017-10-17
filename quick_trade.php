<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
include 'functions.php';

$pair = $argv[1];
$amount = $argv[2];
$dry = FALSE;
$profit = 1.015;
if($argv[3] != '') $profit = $argv[3];
if($argv[4] != '') $dry = TRUE;

$res = $kraken->QueryPublic('AssetPairs');
if(kraken_is_error($res)) die($res['error'][0]);
foreach($res['result'] as $ticker => $data) {
    //echo $ticker ." - " . $data['altname']."\n";
    $pairs[$data['altname']] = $ticker;

    if($ticker == $pair OR $data['altname'] == $pair) {
        $pair = $ticker;
        $altname = $data['altname'];
        $decimals = $data['pair_decimals'];        
    }

}

$res = $kraken->QueryPublic('Ticker', array('pair' => $pair));
if(kraken_is_error($res)) die($res['error'][0]);

$ask = $res['result'][$pair]['a'][0];
$bid = $res['result'][$pair]['b'][0];

echo '[ ';
echo 'Pair: '. $pair.', ';
echo 'Altname: '. $altname.', ';
echo 'Decimals: '. $decimals.', ';
echo 'Amount: '.$amount.', ';
echo 'Ask: '.$ask.', ';
echo 'Bid: '.$bid.', ';
echo 'Profit: '.$profit.', ';

echo 'Percent diff: '.number_format(percent_between($bid, $ask), 2);
echo ' ]'.PHP_EOL;

$closed = $kraken->QueryPrivate('ClosedOrders', array('trades' => true));
if(kraken_is_error($closed)) die($closed['error'][0]);
foreach($closed['result']['closed'] as $txid => $data) {
    // our pair?
    if($data['descr']['pair'] != $altname OR $data['vol_exec'] != $amount or $data['status'] != 'closed') continue;

    echo '- Last trade '.$txid.': '.$data['descr']['type'].' '.$data['descr']['pair'].' price: '.$data['descr']['price'].' vol: '.$data['vol'].PHP_EOL;
    switch($data['descr']['type']) {
        case 'buy':
            //echo '- Already bought, we can sell it!'.PHP_EOL;
            $SELLIT = TRUE;
            $price = $data['price'];
            break;
        case 'sell':
            //echo '- Sold, we can buy it!'.PHP_EOL;
            $BUYIT = TRUE;
            break;
    }

    // Process just the last one
    break;
}


$open = $kraken->QueryPrivate('OpenOrders', array('trades' => true));
if(kraken_is_error($open)) die($open['error'][0]);

//print_r($open);

// Loop through opened positions
$buy = TRUE;
foreach($open['result']['open'] as $txid => $data) {
    if($data['descr']['pair'] != $altname OR $data['vol'] != $amount) continue;
    $NOOP = TRUE;
}

if($NOOP) {
    echo '- Position already opened. Exit now.'.PHP_EOL;
    exit();
}

if($SELLIT) {
    $sell_price = number_format($price * $profit, $decimals, '.', '');
    if($sell_price < $bid) $sell_price = $bid;
    echo '- Selling started for '.$sell_price.PHP_EOL;
    if($dry) die('Dry run, exiting'."\n");
    $res = $kraken->QueryPrivate('AddOrder', array(
		'pair' => $altname,
		'type' => 'sell', 
		'ordertype' => 'limit', 
		'price' => $sell_price, 
		'volume' => $amount,
	));
	print_r($res);
}
elseif($BUYIT) {
    $buyprice = $bid;
    echo '- Buying started for '.$buyprice.PHP_EOL;
    if($dry) die('Dry run, exiting'."\n");
    $res = $kraken->QueryPrivate('AddOrder', array(
		'pair' => $altname,
		'type' => 'buy', 
		'ordertype' => 'limit', 
		'price' => $buyprice, 
		'volume' => $amount,
	));
	print_r($res);

}


