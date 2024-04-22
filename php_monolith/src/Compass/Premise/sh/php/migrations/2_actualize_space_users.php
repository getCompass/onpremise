<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Member;
use Exception;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для миграции - актуализации связей между пользователями и пространствами
 */
class Migrations_2ActualizeSpaceUsers {

	protected const _LIMIT = 10000; // лимит для получения записей из базы за один запрос

	/**
	 * Запускаем работу скрипта
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	public function run():void {

		// создаём связь между пользователями и пространством
		self::_createSpaceList();

		// получаем список всех пользователей в premise_users
		$offset                = 0;
		$all_premise_user_list = [];
		do {

			$premise_user_list     = Gateway_Db_PremiseUser_UserList::getAll(self::_LIMIT, $offset);
			$all_premise_user_list = array_merge($all_premise_user_list, $premise_user_list);

			$offset += self::_LIMIT;
		} while (count($premise_user_list) == self::_LIMIT);

		// обновляем space_status пользователя
		/** @var Struct_Db_PremiseUser_User $premise_user */
		foreach ($all_premise_user_list as $premise_user) {

			// получаем все пространства пользователя
			$premise_space_list = Gateway_Db_PremiseUser_SpaceList::getByUser($premise_user->user_id, self::_LIMIT);

			// определяем статус пользователя в пространствах
			$space_status = self::_getSpaceStatus($premise_user, $premise_space_list);

			// обновляем статус пользователя в пространствах
			$set = [
				"space_status" => $space_status,
				"updated_at"   => time(),
			];
			Gateway_Db_PremiseUser_UserList::set($premise_user->user_id, $set);
		}
	}

	/**
	 * создаём связь между пользователями и пространством
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	protected static function _createSpaceList():void {

		// получаем список всех активных пространств
		$company_list = Gateway_Db_PivotCompany_CompanyList::getByStatus(Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE);

		// для каждого пространства
		foreach ($company_list as $company) {

			// получаем инфу по участникам пространства
			$member_list = Gateway_Socket_Company::getMemberList(
				$company->company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra)
			);

			// готовим массив для вставки связи пользователя и пространства
			$user_space_list = [];
			foreach ($member_list as $member) {

				// если пользователь уже уволен из пространства
				if (Member::isDisabledProfile($member["role"])) {
					continue;
				}

				$user_space_list[] = new Struct_Db_PremiseUser_Space(
					$member["user_id"], $company->company_id, $member["role"], $member["permissions"], $member["created_at"], 0, []
				);
			}
			Gateway_Db_PremiseUser_SpaceList::insertList($user_space_list);
		}
	}

	/**
	 * Получить статус пользователя в пространствах.
	 *
	 * @param Struct_Db_PremiseUser_User $premise_user
	 * @param array                      $premise_space_list
	 *
	 * @return int
	 */
	protected static function _getSpaceStatus(Struct_Db_PremiseUser_User $premise_user, array $premise_space_list):int {

		// если у пользователя нет пространств - помечаем статус у пользователя "не состоит ни в одном пространстве"
		if (count($premise_space_list) < 1) {
			return Domain_Premise_Entity_Space::NOT_EXIST_SPACE_STATUS;
		}

		// если это бот - обновляем статус на "уникальный бот"
		/** @noinspection PhpFullyQualifiedNameUsageInspection */
		if (\CompassApp\Domain\User\Main::isBot($premise_user->npc_type_alias)) {
			return Domain_Premise_Entity_Space::UNIQUE_BOT_SPACE_STATUS;
		}

		// если хоть в одном пространстве пользователя тот числится как участник
		// то помечаем его как "уникальный участник"
		foreach ($premise_space_list as $premise_space) {

			if ($premise_space->role_alias == Member::ROLE_MEMBER) {
				return Domain_Premise_Entity_Space::UNIQUE_MEMBER_SPACE_STATUS;
			}
		}

		// иначе считаем, что пользователь является "уникальным гостем"
		return Domain_Premise_Entity_Space::UNIQUE_GUEST_SPACE_STATUS;
	}
}

try {
	(new Migrations_2ActualizeSpaceUsers())->run();
} catch (Exception $e) {

	console($e->getMessage());
	console($e->getTraceAsString());
	console(redText("Не смогли актуализировать связь между пользователями и пространствами"));
	exit(1);
}
