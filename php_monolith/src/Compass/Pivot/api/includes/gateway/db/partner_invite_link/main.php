<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для базы данных partner_invite_link
 */
class Gateway_Db_PartnerInviteLink_Main {

	protected const _DB_KEY = "partner_invite_link";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод открывает транзакцию
	 *
	 * @return bool
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @throws ReturnFatalException
	 */
	public static function commitTransaction():void {

		if (!ShardingGateway::database(self::_getDbKey())->commit()) {

			ShardingGateway::database(self::_getDbKey())->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Метод откатывает транзакцию
	 *
	 * @return bool
	 */
	public static function rollback():bool {

		return ShardingGateway::database(self::_getDbKey())->rollback();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем шард ключ для базы данных
	 *
	 * @return string
	 */
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}