<?php

namespace Compass\Pivot;

/**
 * Класс для создания инвайта приглашения в компанию
 */
class Domain_Company_Action_JoinLink_Create {

	/**
	 * Делаем
	 *
	 * @throws \queryException
	 */
	public static function do(int $company_id, int $status_alias):string {

		do {

			// генерим новую ссылку
			$join_link_uniq = self::_generateJoinUniq();

			try {

				Gateway_Db_PivotData_CompanyJoinLinkRel::insert($join_link_uniq, $company_id, $status_alias);
				return $join_link_uniq;
			} catch (cs_RowDuplication) {
				// пробуем снова
			}
		} while (true);
	}

	/**
	 * генерируем соль
	 *
	 * @throws \Exception
	 */
	protected static function _generateJoinUniq():string {

		$allowed_alphabet = Domain_Company_Entity_JoinLink_Main::ALLOWED_ALPHABET_FOR_JOIN_LINK_UNIQ;

		$length = mb_strlen($allowed_alphabet) - 1;

		$salt = "";
		for ($j = 0; $j < 8; $j++) {
			$salt .= $allowed_alphabet[random_int(0, $length)];
		}

		return $salt;
	}
}