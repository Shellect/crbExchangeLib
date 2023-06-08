# crbExchangeLib
Package for currencies exchange
installation via composer:
```
composer require shellect/crb-exchange-lib
```

Test work:
1. Create simple \*.php file.
2. Add following code:
```
<?php
require_once(__DIR__ . '/vendor/autoload.php');
use PrivateCoolLib\Currency;
use PrivateCoolLib\ExchangedAmount;

$amount = new ExchangedAmount(Currency::USD, Currency::BYN, 100.0);
var_dump($amount->toDecimal());
```
3. Enjoy
