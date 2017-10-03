<?php

error_reporting(E_ALL & ~E_NOTICE);

// Load composer
require(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php'); 

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);
    
    // Handle telegram getUpdates request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}