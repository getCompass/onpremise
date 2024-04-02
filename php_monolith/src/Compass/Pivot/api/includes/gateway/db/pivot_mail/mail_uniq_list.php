<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_mail.mail_uniq_list_{0-f}
 */
class Gateway_Db_PivotMail_MailUniqList extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_uniq_list";

	/**
	 * Добавляет новую запись в базу
	 */
	public static function insertIgnore(Struct_Db_PivotMail_MailUniq $mail_uniq):void {

		$insert = [
			"mail_hash"       => $mail_uniq->mail_hash,
			"user_id"         => $mail_uniq->user_id,
			"has_sso_account" => intval($mail_uniq->has_sso_account),
			"created_at"      => $mail_uniq->created_at,
			"updated_at"      => $mail_uniq->updated_at,
			"password_hash"   => $mail_uniq->password_hash,
		];
		ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey($mail_uniq->mail_hash), $insert);
	}

	/**
	 * Добавляет новую запись в базу
	 */
	public static function insertOrUpdate(Struct_Db_PivotMail_MailUniq $mail_uniq):void {

		$insert = [
			"mail_hash"       => $mail_uniq->mail_hash,
			"user_id"         => $mail_uniq->user_id,
			"has_sso_account" => intval($mail_uniq->has_sso_account),
			"created_at"      => $mail_uniq->created_at,
			"updated_at"      => $mail_uniq->updated_at,
			"password_hash"   => $mail_uniq->password_hash,
		];
		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_getTableKey($mail_uniq->mail_hash), $insert);
	}

	/**
	 * Метод для обновления записи по PK
	 *
	 * @throws \parseException
	 */
	public static function set(string $mail_hash, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailUniq::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}
		}

		// EXPLAIN: INDEX PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `mail_hash` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey($mail_hash), $set, $mail_hash, 1);
	}

	/**
	 * Метод для обновления записи по PK и user_id
	 *
	 * @throws \parseException
	 */
	public static function setByMailAndUserId(string $mail_hash, int $user_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailUniq::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}
		}

		// EXPLAIN: INDEX PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `mail_hash` = ?s AND `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey($mail_hash), $set, $mail_hash, $user_id, 1);
	}

	/**
	 * Метод для чтения записи по PK
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(string $mail_hash):Struct_Db_PivotMail_MailUniq {

		// EXPLAIN: INDEX PRIMARY
		$query = "SELECT * FROM `?p` WHERE `mail_hash` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey($mail_hash), $mail_hash, 1);

		if (!isset($row["mail_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("mail_uniq not found");
		}

		return Struct_Db_PivotMail_MailUniq::rowToStruct($row);
	}

	/**
	 * Метод для чтения записи по PK с блокировкой
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(string $mail_hash):Struct_Db_PivotMail_MailUniq {

		// EXPLAIN: INDEX PRIMARY
		$query = "SELECT * FROM `?p` WHERE `mail_hash` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey($mail_hash), $mail_hash, 1);

		if (!isset($row["mail_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("mail_uniq not found");
		}

		return Struct_Db_PivotMail_MailUniq::rowToStruct($row);
	}

	/**
	 * Метод для удаления записи
	 */
	public static function delete(string $mail_hash):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `mail_hash` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_getTableKey($mail_hash), $mail_hash, 1);
	}

	/**
	 * Возвращает шард таблицы по хэшу номера.
	 */
	protected static function _getTableKey(string $phone_hash):string {

		return self::_TABLE_KEY . "_" . substr($phone_hash, -1);
	}
}