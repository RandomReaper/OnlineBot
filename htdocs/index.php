<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/../src/bot.php');

$pdo = get_db();
foreach($pdo->query('SELECT * FROM ob_servers') as $row) {
    echo $row['id'].' '.$row['name'] . "\n";
}
