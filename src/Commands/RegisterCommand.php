<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class RegisterCommand extends UserCommand
{
    protected $name = 'register';
    protected $description = 'register host';
    protected $usage = '/register uid name';
    protected $version = '1.0.0';

    public function execute() : ServerResponse
    {
        global $bot;
        $pdo = $bot->pdo();

        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $params = explode(" ", trim($message->getText(true)));
        $id_user = $message->getFrom()->getId();

        if (count($params) < 1 || empty($params[0]))
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* no parameters, should be `/register uid name`",
                'parse_mode' => 'Markdown'
            ]);
        }
        $uid = $params[0];
        $name = 'unnamed';
        if (count($params) > 1)
        {
            $name = $params[1];
        }

        $id_server = $bot->id_server($uid);

        if (!$id_server)
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* server '$uid' not found",
                'parse_mode' => 'Markdown'
            ]);
        }

        echo "here now\n";

        $success = $bot->register($id_user, $id_server, $name);

        echo "success=$success\n";

        if (!$success)
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* register host '$uid' failed",
                'parse_mode' => 'Markdown'
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => "*success:* host _{$name}_ (`$uid`) registered",
            'parse_mode' => 'Markdown'
        ]);
    }
}
