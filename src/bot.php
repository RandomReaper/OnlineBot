<?php
error_reporting(E_ALL);

// Load composer
require (__DIR__ . '/../vendor/autoload.php');
require_once (__DIR__ . '/../config/config.php');

function online($uid, $name)
{
    $pdo = get_db();
    
    $sql = "INSERT INTO `ob_online` (`uid`, `time`) VALUES (:uid, :time)";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':uid', $uid);
    $statement->bindValue(':time', time());
    
    // Execute the statement and insert our values.
    $inserted = $statement->execute();
    
    // Because PDOStatement::execute returns a TRUE or FALSE value,
    // we can easily check to see if our insert was successful.
    if (! $inserted) {
        return $inserted;
    }
    
    $sql = "SELECT * FROM `ob_online` where uid=:uid order by id DESC limit 2";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':uid', $uid);
    $statement->execute();
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    $telegram = telegram();
    
    if ($statement->rowCount() == 1) {
        
        Longman\TelegramBot\Request::sendToActiveChats('sendMessage', // Callback function to execute (see Request.php methods)
        [
            'text' => "found a new server : $uid"
        ], // Param to evaluate the request
        [
            'groups' => true,
            'supergroups' => true,
            'channels' => false,
            'users' => true
        ]);
    }
}

function udpate_db()
{
    
}

function get_db()
{
    global $mysql_credentials;
    
    $dsn = 'mysql:host=' . $mysql_credentials['host'] . ';dbname=' . $mysql_credentials['database'];
    $options = [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . 'utf8mb4'
    ];
    $pdo = new PDO($dsn, $mysql_credentials['user'], $mysql_credentials['password'], $options);
    
    return $pdo;
}

$pdo;

function telegram()
{
    global $bot_api_key;
    global $bot_username;
    
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    $commands_paths = [
        __DIR__ . '/Commands'
    ];
    $telegram->addCommandsPaths($commands_paths);

    // Enable MySQL
    $pdo = get_db();
    $telegram->enableExternalMySql($pdo);

    // Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . "/{$bot_username}_error.log");
    // Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/{$bot_username}_debug.log");
    // Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/{$bot_username}_update.log");
    return $telegram;
}

function bot($is_hook)
{
    global $pdo;
    
    try {
        // Create Telegram API object
        $telegram = telegram();
        
        $telegram->enableAdmin("28932656");
        

        

        
        // Handle telegram webhook request
        if ($is_hook) {
            $server_response = $telegram->handle();
        } else {
            $server_response = $telegram->handleGetUpdates();
            
            if ($server_response->isOk()) {
                $update_count = count($server_response->getResult());
                echo date('Y-m-d H:i:s', time()) . ' - Processed ' . $update_count . ' updates' . PHP_EOL;
            } else {
                echo date('Y-m-d H:i:s', time()) . ' - Failed to fetch updates' . PHP_EOL;
                echo $server_response->printError();
            }
        }
        /*
         * $results = Longman\TelegramBot\Request::sendToActiveChats(
         * 'sendMessage', // Callback function to execute (see Request.php methods)
         * ['text' => 'Hey! Check out the new features!!'], // Param to evaluate the request
         * [
         * 'groups' => true,
         * 'supergroups' => true,
         * 'channels' => false,
         * 'users' => true,
         * ]
         * );
         */
    } catch (Longman\TelegramBot\Exception\TelegramException $e) {
        // Silence is golden!
        // log telegram errors
        echo $e->getMessage();
    }
}

