<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы company_conversation
 */
class Gateway_Db_CompanyConversation_Main {

	protected const _DB_KEY = "company_conversation";

	// метод для старта транзакции
	public static function beginTransaction():bool {

		return static::_connect(static::_getDbKey())->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commitTransaction():void {

		if (!static::_connect(static::_getDbKey())->commit()) {

			static::_connect(static::_getDbKey())->rollback();
			throw new ReturnFatalException("Transaction commit failed in " . __METHOD__);
		}
	}

	// метод для отката транзакции
	public static function rollback():bool {

		$result = static::_connect(static::_getDbKey())->rollback();

		if (!$result) {
			throw new ReturnFatalException("Transaction rollback failed in " . __METHOD__);
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем ключ в sharding
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}

	/**
	 * Создает подключение к базе данных
	 */
	protected static function _connect(string $sharding_key):\myPDObasic {

		return ShardingGateway::database($sharding_key);
	}
}