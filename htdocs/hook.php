<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/../src/bot.php');

$bot = new PimOnlineBot(true);
$bot->bot();