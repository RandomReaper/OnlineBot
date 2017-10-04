<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/../src/bot.php');

if (!isset($_SERVER["HTTP_HOST"]) && $argc > 1) {
    parse_str($argv[1], $_GET);
    parse_str($argv[1], $_POST);
}

if(isset($_POST['uid']))
{
    $name='';
    if (isset($_POST['name']))
    {
        $name=$_POST['name'];
    }
    
    // looks like an online server
    $ret = online($_POST['uid'], $name);
} 

$pdo = get_db();
foreach($pdo->query('SELECT * FROM ob_servers') as $row) {
    echo $row['id'].' '.$row['name'] . "\n";
}
