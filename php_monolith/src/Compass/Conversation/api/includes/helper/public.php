<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * хелпер для публичных диалогов
 *
 * Class Helper_Public
 */
class Helper_Public {

	/**
	 * создать публичный диалог
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function create(int $user_id):array {

		return Type_Conversation_Public::create($user_id);
	}
}