<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class HelpCommand extends UserCommand
{

    protected $name = 'help';

    protected $description = 'help';

    protected $usage = '/help';

    protected $version = '1.0.0';

    public function execute()
    {
        $commands = array(
            "help" => "this message",
            "debug" => "will show debug info",
            "register uid" => "register one server",
        );
        
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = trim($message->getText(true));
        
        $debug_info = [];
     
        if (isset($commands[$text]))
        {
            $debug_info[] = "`/$text` : $commands[$text]";
        }
        else 
        {
            if (strlen($text)) {
                $debug_info[] = "you asked for `\help $text`, which is an unknown command";
            }
            $debug_info[] = "This is *PimOnlineBot*!";
            
            $debug_info[] = "Here is the command list:";
            
            foreach($commands as $text=>$value)
            {
                $debug_info[] = "`/$text` : $value";
            }
        }
           
        $data = [ // Set up the new message data
            'chat_id' => $chat_id, // Set Chat ID to send the message to
            'text' => implode(PHP_EOL, $debug_info),
            'parse_mode' => 'Markdown'
        ];
        
        return Request::sendMessage($data); // Send message!
    }
}