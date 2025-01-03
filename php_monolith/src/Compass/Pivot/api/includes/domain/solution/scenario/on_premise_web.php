<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Api-сценарии для работы с выделенными решениями.
 */
class Domain_Solution_Scenario_OnPremiseWeb {

	/**
	 * Возвращает текущий токен активный токен аутентификации, если его нет, генерирует новый.
	 */
	public static function generateAuthenticationToken(int $user_id, string|false $join_link_uniq = false):array {

		return Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, join_link_uniq: $join_link_uniq);
	}
}
