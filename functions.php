<?php
date_default_timezone_set('Europe/Prague');
require_once 'KrakenAPIClient.php'; 

function get_config()
{
    $conf_raw = file_get_contents(dirname(__FILE__).'/config.yaml');
    $config = yaml_parse($conf_raw);
    return $config;
}

function result_array($q)
{
    $out = array();
    while ($line = mysql_fetch_array($q, MYSQL_ASSOC)) {
        $out[] = $line;
    }
    return $out;
}

function kraken_is_error($res)
{
    if(count($res['error']) == 0) return FALSE;
    return TRUE;
}

function get_rows($q)
{
    return result_array($q);
}

function percent_between($a, $b)
{
    if($a == 0) return 0;

    return 100 * (($b)-($a))/($a);
}

function nf($a)
{
    return number_format($a, 3, ',', ' ');
}

function minimum_label($diff)
{
    if($diff < 2) return 'success';
    if($diff >= 20) return 'danger';
    return 'default';
}

function get_actual_price($ticker)
{
    $q = mysql_query("SELECT price FROM status WHERE ticker='$ticker' ORDER BY date DESC LIMIT 1");
    $r = get_rows($q);
    return $r[0]['price'];
}

// Initialize everything
$config = get_config();
$kraken = new Payward\KrakenAPI($config['kraken_key'], $config['kraken_secret'], 'https://api.kraken.com', 0, TRUE);


