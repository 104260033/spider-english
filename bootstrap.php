<?php
/**
 * 导入自动载入
 */
include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/command.php';
$database = include __DIR__ . '/config/database.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();

$capsule->addConnection($database);
// 设置全局静态可访问
$capsule->setAsGlobal();
// 启动Eloquent
$capsule->bootEloquent();
/**
 * 启用guzzle
 */
$guzzle = new \GuzzleHttp\Client();