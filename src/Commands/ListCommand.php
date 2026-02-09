<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class ListCommand extends UserCommand
{
    protected $name = 'list';                      // Your command's name
    protected $description = 'List servers'; // Your command description
    protected $usage = '/list';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    public function execute() : ServerResponse
    {
        global $bot;
        $pdo = $bot->pdo();

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID
        $id_user = $message->getFrom()->getId();

        $text = [];
        $sql = "SELECT * FROM ob_online LEFT JOIN ob_servers_users ON ob_servers_users.id_server = ob_online.id WHERE ob_servers_users.id_user = :id_user";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':id_user', $id_user);
        $statement->execute();

        if ($statement->rowCount() == 0)
        {
            $text[] = "no registration";
        }
        else
        {
            while ($row = $statement->fetch())
            {
                $duration = $row['now'] - $row['past'];
                $last = time() - $row['now'];
                $uid = $row['uid'];
                $hostname = $row['name'];
                $alarm = $row['alarm'];
                if ($alarm == 0)
                {
                    $text[] = "Host _{$hostname}_ (`$uid`) is *up*. (update interval : $duration seconds, age : $last seconds).";
                }
                else
                {
                    $text[] = "Host _{$hostname}_ (`$uid`) is *down* (last update : $last seconds ago).";
                }
            }
        }

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => implode(PHP_EOL, $text),
            'parse_mode' => 'Markdown'
        ]);
    }
}
