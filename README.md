# OnlineBot
Telegram bot to watch online status of hosts.

## Talk to the bot
The bot is [here](https://telegram.me/PimOnlineBot).

## Setup for dev
1. clone
1. install tools
   ```bash
   sudo apt install php8.3-cli php8.3-mysql composer
   composer install
   cp config/config.php.txt config/config.php
   ```
1. setup `config/config.php`
```php
<?php
# The bot key and username, ask BotFather at https://telegram.me/BotFather
$bot_api_key    = 'your_key';
$bot_username   = 'OnlineBot';

# call "index.sh cron" every minute or set doWihoutCron to true
# this will be expensive
$doWithoutCron  = false;

# URL for the service (also used while generating help cron example)
$base_url       = '';

# Admins can be defined here
$admins_id      = [
  12345678,
]

# MySQL credentials
$mysql_credentials = [
    'host'     => 'localhost',
    'database' => 'your_db',
    'user'     => 'your_user',
    'password' => 'your_pwd',
];
```
1. Instal MySql + create database, then fill it with `./vendor/longman/telegram-bot/structure.sql` and `onlinebot.sql`
