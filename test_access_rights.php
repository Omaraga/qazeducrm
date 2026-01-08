<?php
/**
 * Тест прав доступа - запускать в браузере
 * http://localhost/qazeducrm/web/test_access_rights.php
 */

// Инициализация Yii приложения
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config))->run();

// Код ниже не выполнится, т.к. run() вызывает exit
// Вместо этого используем обычный PHP скрипт
