<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/** стурктура уведомления о регистрации пользователя, отправляемого в модуль интеграции */
class Struct_Integration_Notifier_Response_OnUserRegistered {

	private function __construct(
		public array $action_list,
	) {
	}

	public static function build(array $action_list):self {

		foreach ($action_list as $action) {

			if (!isset($action["action"], $action["data"])) {
				throw new ParseFatalException("unexpected structure");
			}
		}

		return new self($action_list);
	}
}