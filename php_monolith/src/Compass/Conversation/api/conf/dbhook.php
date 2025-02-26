<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

$CONFIG["DBHOOK"] = [];

if (DATABASE_ENCRYPTION_MODE === "none") {

	return $CONFIG;
}

if (DATABASE_ENCRYPTION_MODE === "read_write") {

	foreach (range(1, 12) as $shard_id) {

		$CONFIG["DBHOOK"][] = new \BaseFrame\Database\Hook(
			"company_conversation",
			"message_block_$shard_id",
			"data",
			\BaseFrame\Database\Hook\Action::WRITE,
			\BaseFrame\Crypt\Utils\CallbackProvider::encodeAsJSON(CrypterProvider::get("database_crypt_key")),
		);
	}

	$CONFIG["DBHOOK"][] = new \BaseFrame\Database\Hook(
		"company_conversation",
		"user_left_menu",
		"last_message",
		\BaseFrame\Database\Hook\Action::WRITE,
		\BaseFrame\Crypt\Utils\CallbackProvider::encodeAsJSON(CrypterProvider::get("database_crypt_key")),
	);
}

if (DATABASE_ENCRYPTION_MODE === "read" || DATABASE_ENCRYPTION_MODE === "read_write") {

	foreach (range(1, 12) as $shard_id) {
		$CONFIG["DBHOOK"][] = new \BaseFrame\Database\Hook(
			"company_conversation",
			"message_block_$shard_id",
			"data",
			\BaseFrame\Database\Hook\Action::READ,
			\BaseFrame\Crypt\Utils\CallbackProvider::decodeFromJSON(CrypterProvider::get("database_crypt_key")),
		);
	}

	$CONFIG["DBHOOK"][] = new \BaseFrame\Database\Hook(
		"company_conversation",
		"user_left_menu",
		"last_message",
		\BaseFrame\Database\Hook\Action::READ,
		\BaseFrame\Crypt\Utils\CallbackProvider::decodeFromJSON(CrypterProvider::get("database_crypt_key")),
	);

	return $CONFIG;
}

throw new ReturnFatalException("passed incorrect database encryption mode");