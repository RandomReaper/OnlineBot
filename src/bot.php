<?php
error_reporting(E_ALL);

// Load composer
require (__DIR__ . '/../vendor/autoload.php');
require_once (__DIR__ . '/../config/config.php');

function online($uid)
{
    $pdo = get_db();
    
    $sql = "SELECT * FROM `ob_online` where uid=:uid order by id DESC limit 1";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':uid', $uid);
    $statement->execute();
    $row = $statement->fetch();
    
    $telegram = telegram();
    $inserted = false;
    
    if ($statement->rowCount() == 0)
    {
        $text = "found a new server : $uid";
        if (name != '')
        {
            $text .= " (*$name*)";
        }
        else
        {
            $text .= " (*unnamed*)";
        }
        
        $past = 0;
        $sql = "INSERT INTO `ob_online` (`uid`, `now`, `past`, `alarm`) VALUES (:uid, :now, :past, :alarm)";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':uid', $uid);
        $statement->bindValue(':now', time());
        $statement->bindValue(':past', $past);
        $statement->bindValue(':alarm', 0);
        
        // Execute the statement and insert our values.
        $inserted = $statement->execute();
    }
    else
    {
        $id = $row['id'];
        $past = $row['now'];
        $alarm = $row['alarm'];
        
        $sql = "UPDATE `ob_online` SET `now` = :now, `past` = :past, `alarm` = :alarm WHERE `id` = :id";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':now', time());
        $statement->bindValue(':past', $past);
        $statement->bindValue(':alarm', 0);
        $inserted = $statement->execute();
        
        if ($alarm)
        {
            $users = users($id);
            while ($r = $users->fetch())
            {
                $chat_id = $r['id_user'];
                Longman\TelegramBot\Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text'    => "*info:* server '$uid' is *online*",
                    'parse_mode' => 'Markdown'
                ]);
            }
        }
    }

    // Because PDOStatement::execute returns a TRUE or FALSE value,
    // we can easily check to see if our insert was successful.
    if (! $inserted) {
        return $inserted;
    }
}

function udpate_db()
{
    $time = time();
    echo "update_db time : $time\n";
    $telegram = telegram();
    $pdo = get_db();
    $sql = "SELECT * FROM `ob_online` WHERE alarm = 0 and (now - past) * 1.2 < (:time - now)";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':time', time());
    $statement->execute();
    while ($row = $statement->fetch())
    {
        $id = $row['id'];
        $uid = $row['uid'];
        $sql = "UPDATE `ob_online` SET `alarm` = :alarm WHERE `id` = :id";
        $s = $pdo->prepare($sql);
        $s->bindValue(':id', $id);
        $s->bindValue(':alarm', time());
        $s->execute();
        
        $users = users($id);
        while ($r = $users->fetch())
        {
            $chat_id = $r['id_user'];

            Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* server '$uid' is *offline*",
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}

function id_server($uid)
{
    $pdo = get_db();
    
    $sql = "SELECT * FROM `ob_online` where uid=:uid order by id DESC limit 1";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':uid', $uid);
    $statement->execute();
    $row = $statement->fetch();
    
    if ($statement->rowCount() == 0)
    {
        return false;
    }
    
    return $row['id'];
}

function users($id_server)
{
    $pdo = get_db();
    
    $sql = "SELECT * FROM `ob_servers_users` where id_server=:id_server order by id";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':id_server', $id_server);
    $statement->execute();
    return $statement;
}

function register($id_user, $id_server)
{
    $pdo = get_db();
    
    $sql = "SELECT * FROM `ob_servers_users` where id_user=:id_user AND id_server=:id_server order by id DESC limit 1";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':id_user', $id_user);
    $statement->bindValue(':id_server', $id_server);
    $statement->execute();
    $row = $statement->fetch();
    
    if ($statement->rowCount() != 0)
    {
        return true;
    }
    else
    {
        $sql = "INSERT INTO `ob_servers_users` (`id_user`, `id_server`) VALUES (:id_user, :id_server)";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':id_user', $id_user);
        $statement->bindValue(':id_server', $id_server);
        
        // Execute the statement and insert our values.
        return $statement->execute();
    }
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
    global $pdo;
    
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
    try {
        // Create Telegram API object
        $telegram = telegram();
        
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

function server_count()
{
    $pdo = get_db();
    
    $sql = "SELECT COUNT(*) as server_count FROM `ob_online`";
    $statement = $pdo->prepare($sql);
    $statement->execute();
    $row = $statement->fetch();
    
    return $row['server_count'];
}

