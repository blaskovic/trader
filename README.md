# trader
autotrader and help scripts, mostly for Kraken

## Config file
Copy `config.sample.yaml` to `config.yaml` and set kraken API key and secret properly.

## Portfolio
Quick check of portfolio.
```
$ php portfolio.php 
ZEUR    0.0000x | 1.00 | 0.00
XXBT    0.0349717370x | 4809.80 | 168.21
XXRP    500.00000000x | 0.22 | 109.56
XETH    0.8695152500x | 279.03 | 242.62
Total: 520.39
```

## List available pairs
You can use this to search for available pairs on Kraken and their alt name.
```
$ php list_pairs.php
BCHEUR - BCHEUR
BCHUSD - BCHUSD
BCHXBT - BCHXBT
DASHEUR - DASHEUR
...
```

or to get information about a pair:
```
$ php list_pairs.php XDGXBT
Array
(
    [error] => Array
        (
        )

    [result] => Array
        (
            [XXDGXXBT] => Array
                (
                    [a] => Array
                        (
                            [0] => 0.000000200
                            [1] => 73655508
                            [2] => 73655508.000
                        )

                    [b] => Array
                        (   
                            [0] => 0.000000190
                            [1] => 19470397
                            [2] => 19470397.000
                        )

                    [c] => Array
                        (   
                            [0] => 0.000000190
                            [1] => 84000.00000000
                        )
...
```

## Quick auto trading
This script starts buying at bid price and then selling on price + profit.

Usage:
```
php quick_trade.php PAIR AMOUNT PROFIT
```
Example:
```
php quick_trade.php ETHEUR 0.4 1.022
```

Trades are paired (uniques) as `PAIR_AMOUNT`, so you don't need database. Just run the same pair and amount in while cycle or crontab.

## Contribution
Please feel free to fork and send pull request. Just add new script with your very own autotrade heuristic, grab credentials from yaml config and extend this readme so users known how to use it.
