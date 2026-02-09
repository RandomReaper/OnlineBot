<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class RegisterCommand extends UserCommand
{
    protected $name = 'register';
    protected $description = 'register host';
    protected $usage = '/register uid hostname';
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
                'text'    => "*error:* no parameters, should be `/register uid hostname`",
                'parse_mode' => 'Markdown'
            ]);
        }
        $uid = $params[0];
        if (!$bot->is_valid_uuid($uid))
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* invalid uid, must be a valid uuid",
                'parse_mode' => 'Markdown'
            ]);
        }
        $hostname = 'unnamed';
        if (count($params) > 1)
        {
            $hostname = $params[1];
        }

        if (!preg_match('/^(?=.{1,253}$)([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $hostname))
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* invalid hostname, must be a valid unix hostname (alphanumeric, hyphens, dots)",
                'parse_mode' => 'Markdown'
            ]);
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

        $success = $bot->register($id_user, $id_server, $hostname);

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
            'text'    => "*success:* host _{$hostname}_ (`$uid`) registered",
            'parse_mode' => 'Markdown'
        ]);
    }
}
