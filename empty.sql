CREATE TABLE `ob_online` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `now` int(11) NOT NULL,
 `past` int(11) NOT NULL,
 `alarm` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ob_servers_users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_server` int(11) NOT NULL,
 `id_user` int(11) NOT NULL,
 `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
