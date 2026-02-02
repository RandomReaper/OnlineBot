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

    $ret = $bot->online($_REQUEST['uid']);

    switch ($ret) {
      case 0:
        http_response_code(200);
      break;

      case 1:
        http_response_code(400);
        print("invalid uuid");
      break;

      case 2:
        http_response_code(400);
        print("too soon");
      break;

      default:
        http_response_code(400);
        print("unknown error");
      break;
    }
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
<title>online.oouu.ch</title>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6XS9YGH923"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-6XS9YGH923');
</script>
<link rel="stylesheet" id="silktide-consent-manager-css" href="silktide-consent-manager.css">
<script src="silktide-consent-manager.js"></script>
<script>
silktideCookieBannerManager.updateCookieBannerConfig({
  background: {
    showBackground: true
  },
  cookieIcon: {
    position: "bottomLeft"
  },
  cookieTypes: [
    {
      id: "necessary",
      name: "Necessary",
      description: "<p>These cookies are necessary for the website to function properly and cannot be switched off. They help with things like logging in and setting your privacy preferences.</p>",
      required: true,
      onAccept: function() {
        console.log('Add logic for the required Necessary here');
      }
    },
    {
      id: "analytics",
      name: "Analytics",
      description: "<p>These cookies help us improve the site by tracking which pages are most popular and how visitors move around the site.</p>",
      required: false,
      onAccept: function() {
        gtag('consent', 'update', {
          analytics_storage: 'granted',
        });
        dataLayer.push({
          'event': 'consent_accepted_analytics',
        });
      },
      onReject: function() {
        gtag('consent', 'update', {
          analytics_storage: 'denied',
        });
      }
    }
  ],
  text: {
    banner: {
      description: "<p>We use cookies on our site to enhance your user experience, provide personalized content, and analyze our traffic. <a href=\"https://your-website.com/cookie-policy\" target=\"_blank\">Cookie Policy.</a></p>",
      acceptAllButtonText: "Accept all",
      acceptAllButtonAccessibleLabel: "Accept all cookies",
      rejectNonEssentialButtonText: "Reject non-essential",
      rejectNonEssentialButtonAccessibleLabel: "Reject non-essential",
      preferencesButtonText: "Preferences",
      preferencesButtonAccessibleLabel: "Toggle preferences"
    },
    preferences: {
      title: "Customize your cookie preferences",
      description: "<p>We respect your right to privacy. You can choose not to allow some types of cookies. Your cookie preferences will apply across our website.</p>",
      creditLinkText: "Get this banner for free",
      creditLinkAccessibleLabel: "Get this banner for free"
    }
  }
});
</script>
<style>
body
{
font-family:sans
}
</style>
</head>
<body>

<h1>Welcome to $bot_username's</h1>
<p>Receive a telegram when your host is offline.</p>
<h2>Telegram side</h2>
Talk to the <a href="https://telegram.me/$bot_username">bot</a>, then ask for <b>/help</b>.
<h2>Host side</h2>
The current help is only on the bot side.
<h2>License and sources</h2>
© 2017-2026 Marc Pignat, licensed under the <a href="https://www.gnu.org/licenses/agpl-3.0.en.html">AGPL v3</a>, sources on <a href="https://github.com/RandomReaper/OnlineBot">github</a>.
</body>
</html>
EOT;

    echo $html;
}
