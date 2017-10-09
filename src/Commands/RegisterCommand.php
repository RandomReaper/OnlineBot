<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class RegisterCommand extends UserCommand
{
    protected $name = 'Register';
    protected $description = 'register server';
    protected $usage = '/register uid';
    protected $version = '1.0.0';

    public function execute()
    {
        global $pdo;
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $uid = trim($message->getText(true));
        $id_user = $message->getFrom()->getId();
        
        $id_server = id_server($uid);

        if ($uid=='')
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* no parameter",
                'parse_mode' => 'Markdown'
            ]);
        }
          
        if (!$id_server)
        {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "*error:* server '$uid' not found",
                'parse_mode' => 'Markdown'
            ]);
        }
        
        register($id_user, $id_server);
                
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => "*success:* server '$uid' added",
            'parse_mode' => 'Markdown'
        ]);
    }
}