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
        global $bot;
        $commands = array(
            "help" => "this message",
            "list" => "will list your own servers",
            "register uid name" => "register one server",
        );
        
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $min = rand(0,59);
        $base_url = $bot->base_url();
        $help_message =<<<EOT
Hello, my name is *PimOnlineBot*.
I can send you a message when a server is _offline_.

The server will tell _me_ it is online by doing a request at regular interval, for instance using a cron job like this one:
```
$min * * * * wget $base_url/index.php?uid=$uid -O /dev/null
```

The server will be identified by it's _uid_. This _uid_ must be *unique*, and I just generated `$uid` for your new server, but
`uuidgen` (the command line) can also be used.

Once your server has told _me_ that it is online, you can tell me that this server is yours, using the `/register` command.
Since _uid_ is not really human friendly, feel free to tell _me_ your server name.
```
/register $uid my-server-pretty-name
```

You can list your own servers using the `/list` command, it should say something like:

Server _ my-pretty-server-name _ (`$uid`) is *up*. Update interval : 3600 seconds, age : 150 seconds

EOT;

        
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
            $debug_info[] = $help_message;
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