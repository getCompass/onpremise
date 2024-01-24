<?php

namespace Compass\Conversation;

// список доменов которые не будут парситься при любых условиях
$CONFIG["PREVIEW_EXCLUDE"] = [
];

// список доменов которые находятся в белом списке
$CONFIG["PREVIEW_WHITELIST"] = [
	"google.com",
	"yandex.ru",
	"wikipedia.org",
	"vk.com",
	"twitter.com",
	"yahoo.com",
	"rambler.com",
	"github.com",
	"bitbucket.com",
	"youtube.com",
	"youtu.be",
];

// список доменов для которых не отдаем favicon
$CONFIG["PREVIEW_NO_FAVICON_DOMAIN_LIST"] = [];

// список доменов которые находятся в черном списке
$CONFIG["PREVIEW_BLACKLIST"] = [
];

return $CONFIG;