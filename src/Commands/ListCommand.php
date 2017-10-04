<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class ListCommand extends UserCommand
{
    protected $name = 'List';                      // Your command's name
    protected $description = 'List servers'; // Your command description
    protected $usage = '/list';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        global $pdo;
        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $text = "";
        foreach($pdo->query('SELECT * FROM ob_servers') as $row) {
            $text .= $row['id'].' '.$row['name'] . "\n";
        }
                
        $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text'    => $text
        ];
        

        return Request::sendMessage($data);        // Send message!
    }
}