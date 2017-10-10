<?php
error_reporting(E_ALL);

require_once (__DIR__ . '/../src/bot.php');

$bot = new PimOnlineBot(false);

/*
 * When called from the command line, emulate a HTTP GET/POST
 */
if (! isset($_SERVER["HTTP_HOST"]) && $argc > 1) {
    parse_str($argv[1], $_GET);
    parse_str($argv[1], $_POST);
}

if (isset($_POST['uid']))
{
    /*
     * uid is set -> update from a server
     */
    $bot->online($_POST['uid']);
}
else if (isset($_POST['cron']))
{
    /*
     * Manual update, generally from cron, but at this time can be forced
     * through HTTP
     */
    $bot->udpate_db();
}
else
{
    /*
     * if uid is set, this is an online bot request
     */
    $bot->bot();
}

/*
 * Say hello
 */
$c = $bot->server_count();
echo "server_count = $c\n";

