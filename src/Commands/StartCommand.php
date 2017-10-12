<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * Start command
 */
class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     *
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        global $bot;
        $pdo = $bot->pdo();
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $id_user = $message->getFrom()->getId();
        $username = $message->getFrom()->getFirstName();
        
        $host_count = $bot->server_count_for_user($id_user);
        
        $text = [];
        if ($host_count)
        {
            $text[] = "Welcome back *{$username}*!";
        }
        else
        {
            $text[] = "Welcome *{$username}*!";
            $text[] = "I'm __{$bot->telegram()->getBotUsername()}__ and".
                        " I can alert you when one of your host is _down_.";
            $text[] = "Type `/help` if you want to know how it works.";
        }
        
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => implode(PHP_EOL, $text),
            'parse_mode' => 'Markdown'
        ]);

        return parent::execute();
    }
}
