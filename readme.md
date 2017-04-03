# WAU Queue Library

## Use case
### worker.php
```php
<?php

require dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';
use WAUQueue\ApiRateLimiteStage;

$configs = require dirname(__FILE__) .'/ampq.php';
$configs['exchange_declare']  = 'texts';
$configs['queue_declare_bind' ] = 'texts';
$configs['exchange_params']['name'] = 'sms_urgent'; // Routing name

$worker = new WAUQueue\RabbitMQWorker($configs, array(
    new ApiRateLimiteStage()
));
$worker->listen($configs['queue'], $delay = 0, 128, 3, 0, [
    'binding_queue_route' => 'sms_urgent',
    'exchange'      => 'texts'
]);
```

### publish.php
```php
<?php
require_once dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';

use WAUQueue\Connectors\RabbitMQConnnector;


$configs = require dirname(__FILE__) .'/ampq.php';
$configs['exchange_declare']  = 'texts';
$configs['queue_declare_bind' ] = 'texts';
$configs['exchange_params']['name'] = 'sms_urgent'; // Routing name

$connect = new RabbitMQConnnector();
$channel = $connect->connect($configs);

$channel->push('sms', array( 'message' => 'Secound SMS') , $configs['queue'], [
    'priority' => 3
]);
```
