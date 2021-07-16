<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class UnregisterCommand extends UserCommand
{
    protected $name = 'unregister';
    protected $description = 'unregister host';
    protected $usage = '/unregister uid';
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
                'text'    => "*error:* no parameters, should be `/unregister uid`",
                'parse_mode' => 'Markdown'
            ]);
        }
        $uid = $params[0];

        $id_server = $bot->id_server($uid);

        if (!$id_server)
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* host '$uid' not found",
                'parse_mode' => 'Markdown'
            ]);
        }

        $success = $bot->unregister($id_user, $id_server);

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
            'text'    => "*success:* host`$uid` unregistered",
            'parse_mode' => 'Markdown'
        ]);
    }
}
