<?php
error_reporting(E_ALL);

// Load composer
require (__DIR__ . '/../vendor/autoload.php');
require_once (__DIR__ . '/../config/config.php');

class PimOnlineBot
{
    /**
     * Database pdo
     * @var PDO
     */
    private $pdo;

    /**
     * total telegram interface
     * @var Longman\TelegramBot\Telegram telegram object
     */
    private $telegram;

    /**
     * Should we use the telegram hook or rest api
     * @var bool
     */
    private $isHook;

    /**
     * When cron is not available or too slow (hourly cron on certain host).
     * The offline checks will be done on each access, can be CPU intensive.
     * @var bool
     */
    private $doWithoutCron;

    /**
     * Base url, used for the help
     * @var string
     */
    private $base_url;

    /**
     * Minimum update interval in second
     * @var int
     */
    private $min_interval;


    /**
     * Init a PimOnlineBot
     */
    public function __construct($isHook)
    {
        global $mysql_credentials;
        global $bot_api_key;
        global $bot_username;
        global $min_interval;
        global $doWithoutCron;
        global $base_url;

        $this->pdo = $this->init_db($mysql_credentials);
        $this->telegram = $this->init_telegram($this->pdo, $bot_api_key, $bot_username);
        $this->isHook = $isHook;
        $this->doWithoutCron = $doWithoutCron;
        $this->base_url = $base_url;
        $this->min_interval = $min_interval;
    }

    public function base_url()
    {
        return $this->base_url;
    }

    public function telegram()
    {
        return $this->telegram;
    }

    public function pdo()
    {
        return $this->pdo;
    }

    private function is_valid_uuid($uuid)
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }

    private function init_db($mysql_credentials)
    {
        $dsn = 'mysql:host=' . $mysql_credentials['host'] . ';dbname=' . $mysql_credentials['database'];
        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . 'utf8mb4'
        ];
        return new PDO($dsn, $mysql_credentials['user'], $mysql_credentials['password'], $options);
    }

    private function init_telegram($pdo, $bot_api_key, $bot_username)
    {
        $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

        $commands_paths = [
            __DIR__ . '/Commands'
        ];
        $telegram->addCommandsPaths($commands_paths);

        $telegram->enableExternalMySql($pdo);

        // Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . "/{$bot_username}_error.log");
        // Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/{$bot_username}_debug.log");
        // Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/{$bot_username}_update.log");
        return $telegram;
    }

    public function online($uid)
    {
        if (!$this->is_valid_uuid($uid))
        {
            return 1;
        }

        try {
            $this->pdo->beginTransaction();

            $pdo = $this->pdo();
            $sql = "SELECT * FROM `ob_online` WHERE uid=:uid ORDER BY id DESC LIMIT 1 FOR UPDATE";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':uid', $uid);
            $statement->execute();
            $row = $statement->fetch();

            $telegram = $this->telegram();
            $inserted = false;

            if ($statement->rowCount() == 0)
            {
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
                $currentTime = time();

                if (!$alarm && ($currentTime - $past) < $min_interval) {
                    $this->pdo->rollBack();
                    return 2;
                }

                $sql = "UPDATE `ob_online` SET `now` = :now, `past` = :past, `alarm` = :alarm WHERE `id` = :id";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':id', $id);
                $statement->bindValue(':now', time());
                $statement->bindValue(':past', $past);
                $statement->bindValue(':alarm', 0);
                $inserted = $statement->execute();

                if ($alarm)
                {
                    $users = $this->users($id);
                    while ($r = $users->fetch())
                    {
                        $chat_id = $r['id_user'];
                        $name = $r['name'];

                        Longman\TelegramBot\Request::sendMessage([
                            'chat_id' => $chat_id,
                            'text'    => "*info:* Host _{$name}_ is *online*",
                            'parse_mode' => 'Markdown'
                        ]);
                    }
                }
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
        }

        if ($this->doWithoutCron)
        {
            $this->udpate_db();
        }

        return 0;
    }

    public function udpate_db()
    {
        try {
            $this->pdo->beginTransaction();

            $pdo = $this->pdo();
            $time = time();
            $sql = "SELECT * FROM `ob_online` WHERE (alarm = 0) AND (((now - past) * 2.4) < (:time - now)) FOR UPDATE";
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

                $users = $this->users($id);
                while ($r = $users->fetch())
                {
                    $chat_id = $r['id_user'];
                    $name = $r['name'];

                    Longman\TelegramBot\Request::sendMessage([
                        'chat_id' => $chat_id,
                        'text'    => "*error:* Host _{$name}_ is *offline*",
                        'parse_mode' => 'Markdown'
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }
    }

    public function id_server($uid)
    {
        $pdo = $this->pdo();
        $sql = "SELECT * FROM `ob_online` WHERE uid=:uid ORDER BY id DESC LIMIT 1";
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

    public function server_count_for_user($id_user)
    {
        $pdo = $this->pdo();
        $sql = "SELECT count(*) as nr FROM `ob_servers_users` WHERE id_user=:id_user ORDER BY id";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':id_user', $id_user);
        $statement->execute();
        return $statement->fetch()['nr'];
    }

    private function users($id_server)
    {
        $pdo = $this->pdo();
        $sql = "SELECT * FROM `ob_servers_users` WHERE id_server=:id_server ORDER BY id";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':id_server', $id_server);
        $statement->execute();
        return $statement;
    }

    public function register($id_user, $id_server, $name)
    {
        try {
            $this->pdo->beginTransaction();

            $pdo = $this->pdo();
            $sql = "SELECT * FROM `ob_servers_users` WHERE id_user=:id_user AND id_server=:id_server ORDER BY id DESC LIMIT 1 FOR UPDATE";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':id_user', $id_user);
            $statement->bindValue(':id_server', $id_server);
            $statement->execute();
            $row = $statement->fetch();

            if ($statement->rowCount() != 0)
            {
                $this->pdo->rollBack();
                return true;
            }
            else
            {
                $sql = "INSERT INTO `ob_servers_users` (`id_user`, `id_server`, `name`) VALUES (:id_user, :id_server, :name)";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':id_user', $id_user);
                $statement->bindValue(':id_server', $id_server);
                $statement->bindValue(':name', $name);

                // Execute the statement and insert our values.
                $res = $statement->execute();
                $this->pdo->commit();
                return $res;
            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function unregister($id_user, $id_server)
    {
        try {
            $this->pdo->beginTransaction();

            $pdo = $this->pdo();
            $sql = "SELECT * FROM `ob_servers_users` WHERE id_user=:id_user AND id_server=:id_server ORDER BY id DESC LIMIT 1 FOR UPDATE";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':id_user', $id_user);
            $statement->bindValue(':id_server', $id_server);
            $statement->execute();
            $row = $statement->fetch();

            if ($statement->rowCount() != 1)
            {
                $this->pdo->rollBack();
                return false;
            }
            else
            {
                $id = $row['id'];
                $sql = "DELETE FROM `ob_servers_users` WHERE id=:id";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':id', $id);

                $res = $statement->execute();
                $this->pdo->commit();
                return $res;
            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function server_count()
    {
        $pdo = $this->pdo();
        $sql = "SELECT COUNT(*) as server_count FROM `ob_online`";
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch();

        return $row['server_count'];
    }

    public function bot()
    {
        try {
            $telegram = $this->telegram();
            // Handle telegram webhook request
            if ($this->isHook) {
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

        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            // Silence is golden!
            // log telegram errors
            echo $e->getMessage();
        }

        if ($this->doWithoutCron)
        {
            $this->udpate_db();
        }
    }
}
