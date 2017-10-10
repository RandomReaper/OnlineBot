<?php
error_reporting(E_ALL);

require_once (__DIR__ . '/../src/bot.php');

if (! isset($_SERVER["HTTP_HOST"]) && $argc > 1) {
    parse_str($argv[1], $_GET);
    parse_str($argv[1], $_POST);
}

if (isset($_POST['uid'])) {
    // looks like an online server
    $ret = online($_POST['uid']);
} else if (isset($_POST['cron']))
{
    udpate_db();
}

$server_count = server_count();

echo "server_count = $server_count\n";

