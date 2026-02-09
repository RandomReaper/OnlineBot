<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;

class BroadcastCommand extends UserCommand
{
    protected $name = 'broadcast';
    protected $description = 'Broadcast message to all users';
    protected $usage = '/broadcast <message>';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $text = trim($message->getText(true));

        if (!in_array($user_id, $bot->admins_id())) {
            return Request::sendMessage([
                'chat_id'    => $chat_id,
                'text'       => '*Error:* You do not have permission to execute this command.',
                'parse_mode' => 'Markdown'
            ]);
        }

        if ($text === '') {
            return Request::sendMessage([
                'chat_id'    => $chat_id,
                'text'       => '*Error*: Message body is empty. Usage: `/broadcast <message>`',
                'parse_mode' => 'Markdown'
            ]);
        }

        $results = DB::getPdo()->query('SELECT id FROM user')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($results as $target_id) {
            Request::sendMessage([
                'chat_id' => $target_id,
                'text'    => "*Important message*\n\n" . $text,
                'parse_mode' => 'Markdown'
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => 'Broadcast sent to ' . count($results) . ' users.'
        ]);
    }
}
