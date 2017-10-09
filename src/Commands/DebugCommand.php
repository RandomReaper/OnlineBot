<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class DebugCommand extends UserCommand
{
    protected $name = 'debug';                      // Your command's name
    protected $description = 'debug'; // Your command description
    protected $usage = '/debug';                    // Usage of your command
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
        foreach($pdo->query('SELECT * FROM ob_online') as $row) {
            $duration = $row['now'] - $row['past'];
            $text .= $row['id'].' '.$row['uid'] .' '.$duration . "seconds\n";
        }
                
        $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text'    => $text
        ];

        return Request::sendMessage($data);        // Send message!
    }
}