<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class DebugCommand extends UserCommand
{
    protected $name = 'debug';
    protected $description = 'debug';
    protected $usage = '/debug';
    protected $version = '1.0.0';

    public function execute() : ServerResponse
    {
        global $bot;
        $pdo = $bot->pdo();

        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        $text = [];
        $text[] = "user\_id = *$user_id*";
        $text[] = "chat\_id = *$chat_id*";
        foreach($pdo->query('SELECT * FROM ob_servers') as $row) {
            $text[] = $row['id'].' '.$row['name'] . "\n";
        }
        foreach($pdo->query('SELECT * FROM ob_online') as $row) {
            $duration = $row['now'] - $row['past'];
            $text[] = $row['id'].' '.$row['uid'] .' '.$duration . "seconds\n";
        }

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => implode(PHP_EOL, $text),
            'parse_mode' => 'Markdown'
        ]);
    }
}
