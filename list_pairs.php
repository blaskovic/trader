<?php
//
// https://github.com/payward/kraken-api-client/blob/master/php/example.php
//

include_once 'functions.php'; 


if(isset($argv[1])) {
    $res = $kraken->QueryPublic('Ticker', array('pair' => $argv[1]));
    print_r($res);
} else {
    $res = $kraken->QueryPublic('AssetPairs');
    foreach($res['result'] as $ticker => $data) {
        echo $ticker ." - " . $data['altname']."\n";
    }
}
