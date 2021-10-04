<?php
error_reporting(E_ALL);

require_once (__DIR__ . '/../src/bot.php');

$bot = new PimOnlineBot(false);
$online = false;
/*
 * When called from the command line, emulate a HTTP GET/POST
 */
if (! isset($_SERVER["HTTP_HOST"]) && $argc > 1)
{
        parse_str($argv[1], $_GET);
        parse_str($argv[1], $_POST);
        parse_str($argv[1], $_REQUEST);
}

if (isset($_SERVER["HTTP_HOST"]))
{
    $online = true;
}

/*
 * uid is set -> update from a server
 */
if (isset($_REQUEST['uid']))
{
    /*
     * Disable varnish cache for updates
     */
    if (isset($_SERVER["HTTP_HOST"]))
    {
        header('Cache-Control: max-age=0');
    }

    $bot->online($_REQUEST['uid']);
}
else if (isset($_REQUEST['cron']))
{
    /*
     * Manual update, generally from cron, but at this time can be forced
     * through HTTP.
     */
    $bot->udpate_db();
}
else
{
    /*
     * Call that only when offline, the hooks *must* be used when online.
     */
    if (!$online)
    {
        $bot->bot();
    }
}

if (!$online)
{
    /*
     * Say hello
     */
    $c = $bot->server_count();
    echo "server_count = $c\n";
}

if ($online && !isset($_REQUEST['uid']))
{
$html =<<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>a.pignat.org</title>
<!-- Global site tag (gtag.js) - Google Analytics -->
<meta name="google-site-verification" content="JzxEzqkYg7kk74ZMjq4v-4NLSY6GboR2MyJNl8O4xOs" />
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119405595-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-119405595-1');
</script>
<style>
body
{
font-family:sans
}
</style>
</head>
<body>

<h1>Welcome to PimOnlineBot</h1>
<p>Receive a telegram when your host is offline.</p>
<h2>Telegram side</h2>
Talk to the <a href="https://telegram.me/PimOnlineBot">bot</a>, then ask for <b>/help</b>.
<h2>Host side</h2>
The current help is only on the bot side.
<h2>License and sources</h2>
© 2017 Marc Pignat, licensed under the <a href="https://www.gnu.org/licenses/agpl-3.0.en.html">AGPL v3</a>, sources on <a href="https://github.com/RandomReaper/OnlineBot">github</a>.
</body>
</html>
EOT;

    echo $html;
}

