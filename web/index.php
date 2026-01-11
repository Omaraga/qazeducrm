<?php
// Read from environment variables with fallback to development defaults
$debug = getenv('YII_DEBUG');
$env = getenv('YII_ENV');

defined('YII_DEBUG') or define('YII_DEBUG', $debug !== false ? filter_var($debug, FILTER_VALIDATE_BOOLEAN) : true);
defined('YII_ENV') or define('YII_ENV', $env !== false ? $env : 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
