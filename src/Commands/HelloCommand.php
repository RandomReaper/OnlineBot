<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class HelloCommand extends UserCommand
{
    protected $name = 'help';                      // Your command's name
    protected $description = 'help'; // Your command description
    protected $usage = '/hello';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

           $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text'    => 'Hello ', // Set message to send
        ];

        return Request::sendMessage($data);        // Send message!
    }
}