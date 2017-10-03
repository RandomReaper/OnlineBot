#!/usr/bin/env php
<?php

error_reporting(E_ALL);

// Load composer
require(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    //$telegram->enableAdmin($bot_admin);
    
    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);
        
    // Handle telegram webhook request
    $server_response = $telegram->handleGetUpdates();
    if ($server_response->isOk()) {
        $update_count = count($server_response->getResult());
        echo date('Y-m-d H:i:s', time()) . ' - Processed ' . $update_count . ' updates';
    } else {
        echo date('Y-m-d H:i:s', time()) . ' - Failed to fetch updates' . PHP_EOL;
        echo $server_response->printError();
    }
    
    $results = Longman\TelegramBot\Request::sendToActiveChats(
        'sendMessage', // Callback function to execute (see Request.php methods)
        ['text' => 'Hey! Check out the new features!!'], // Param to evaluate the request
        [
            'groups'      => true,
            'supergroups' => true,
            'channels'    => false,
            'users'       => true,
        ]
        );
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    echo $e->getMessage();
}

