<?php

declare(strict_types=1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для сценариев api сущности пользователя
 */
class Domain_User_Scenario_Api
{
	/**
	 * Получить подпись для пользователя
	 */
	public static function getSignature(int $user_id): string
	{

		return Domain_User_Action_GetSignature::do($user_id);
	}

	/**
	 * Получить персональный код
	 *
	 * @throws Gateway_Premise_Exception_ServerNotFound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 */
	public static function getPersonalCode(int $user_id): string
	{

		if (ServerProvider::isLocalLicense()) {
			throw new ControllerMethodNotFoundException("method is not allowed");
		}

		try {
			$premise_user = Gateway_Db_PremiseUser_UserList::getOne($user_id);
			$user         = Gateway_Db_PivotUser_UserList::getOne($premise_user->user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("cant find user with active session");
		}

		$user_space_list = Gateway_Db_PremiseUser_SpaceList::getByUser($user_id);
		$space_id_list   = array_column($user_space_list, "space_id");

		// получаем список пространств
		$space_list = Gateway_Db_PivotCompany_CompanyList::getList($space_id_list, true);

		$formatted_space_list = [];

		// формируем объекты для каждой команды
		foreach ($user_space_list as $user_space) {

			if (!isset($space_list[$user_space->space_id])) {
				continue;
			}

			$formatted_space_list[] = Domain_PersonalCode_Action_PrepareSpace::do($user_space, $space_list[$user_space->space_id]);
		}

		// формируем данные для получения персонального кода
		$personal_code_data = new Struct_PersonalCode_Data($user->full_name, $formatted_space_list);

		return Gateway_Premise_License::getPersonalCode($user_id, $personal_code_data);
	}

	/**
	 * Получить количество гостей и участников на сервере
	 *
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 */
	public static function getCounters(int $user_id): array
	{

		$user = Gateway_Db_PremiseUser_UserList::getOne($user_id);

		// если у пользователя нет прав для получения счётчиков
		if ($user->has_premise_permissions == 0) {
			throw new Domain_User_Exception_UserHaveNotPermissions("user have not permissions");
		}

		return Domain_User_Action_GetCounters::do();
	}

	/**
	 * Установить права для указанного пользователя
	 *
	 * @throws Domain_User_Exception_IsDisabled
	 * @throws Domain_User_Exception_NotFound
	 * @throws Domain_User_Exception_SelfDisabledPermissions
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @long
	 */
	public static function setPermissions(int $user_id, int $set_user_id, array $premise_permissions): void
	{

		// приводим к формату полученный от клиента права для изменения
		[$enabled_permission_list, $disabled_permission_list] = Domain_User_Entity_Permissions::formatToList($premise_permissions);

		// если в итоге ничего не меняем, значит что-то некорректное пришло от клиента
		if (count($enabled_permission_list) == 0 && count($disabled_permission_list) == 0) {
			throw new ParamException("incorrect param premise_permissions");
		}

		// достаём пользователей
		$user_id_list = $user_id == $set_user_id ? [$user_id] : [$user_id, $set_user_id];
		$user_list    = Gateway_Db_PremiseUser_UserList::getList($user_id_list);

		if (!isset($user_list[$user_id])) {
			throw new ParseFatalException("did not find the user who must be");
		}

		// если у текущего пользователя нет прав для изменения прав
		$user = $user_list[$user_id];
		Domain_User_Entity_Permissions::assertIfNotAdministrator((bool) $user->has_premise_permissions, $user->premise_permissions);

		// если не нашли пользователя, которому меняем права, проверяем, что он реально существует
		if (!isset($user_list[$set_user_id])) {
			Domain_User_Action_AssertUserExists::do($set_user_id);
		}

		$set_user = $user_list[$set_user_id];

		// если пытаемся убрать у себя права администратора
		if (count($disabled_permission_list) > 0 && $user_id == $set_user_id &&
			in_array(Domain_User_Entity_Permissions::SERVER_ADMINISTRATOR, $disabled_permission_list)) {

			throw new Domain_User_Exception_SelfDisabledPermissions("try disabled permissions for self");
		}

		// обновляем права у пользователя
		$new_premise_permissions = Domain_User_Entity_Permissions::removePermissionListFromMask($set_user->premise_permissions, $disabled_permission_list);
		$new_premise_permissions = Domain_User_Entity_Permissions::addPermissionListToMask($new_premise_permissions, $enabled_permission_list);

		Domain_User_Action_UpdatePermissions::do($set_user_id, $new_premise_permissions);

		// если забрали права администратора и бухгалтера
		// отправляем запрос в php_license для отзыва токена аутентификации
		if ($new_premise_permissions == Domain_User_Entity_Permissions::DEFAULT && !ServerProvider::isLocalLicense()) {

			try {
				Gateway_Premise_License::disableAuthenticationToken($set_user_id);
			} catch (\Exception) {
			}
		}

		// отправляем ws пользователю, которому изменили права
		Gateway_Bus_SenderBalancer::permissionsChanged($set_user_id, $new_premise_permissions);
	}

	/**
	 * Получить информацию по пользователям
	 *
	 * @throws ParseFatalException
	 * @throws cs_IncorrectUserId
	 * @throws cs_WrongSignature
	 */
	public static function getInfoBatching(array $batch_premise_user_list, array $need_premise_user_list): array
	{

		Domain_User_Entity_Validator::assertNeedUserIdList($need_premise_user_list);

		// выбрасываем ошибку, если массив пользователей некорректен
		Domain_User_Entity_Validator::assertBatchUserList($batch_premise_user_list);

		// собираем пользователей для получения из базы
		$user_id_list = [];
		foreach ($batch_premise_user_list as $v) {
			$user_id_list = array_merge($user_id_list, $v["premise_user_list"]);
		}

		if (count($need_premise_user_list) > 0) {
			$user_id_list = array_intersect($need_premise_user_list, $user_id_list);
		}

		if (count($user_id_list) == 0) {
			return [];
		}

		// достаём пользователей
		$premise_user_list = Gateway_Db_PivotUser_UserList::getList($user_id_list);

		// приводим к формату
		return Apiv2_Format::premiseUserList($premise_user_list);
	}

	/**
	 * Получить id пользователей по поисковому запросу
	 *
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 * @throws \cs_RowIsEmpty
	 * @throws ParseFatalException
	 */
	public static function getIdList(int $user_id, string $query): array
	{

		// проверяем права пользователя
		$user = Gateway_Db_PremiseUser_UserList::getOne($user_id);
		Domain_User_Entity_Permissions::assertIfNotAdministrator((bool) $user->has_premise_permissions, $user->premise_permissions);

		// получаем пользователей
		$user_list = Domain_User_Action_GetByQuery::do($query);

		return array_column($user_list, "user_id");
	}

	/**
	 * Получить пользователей, сгруппированных по правам
	 *
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getGroupedByPermissionList(int $user_id, int $premise_space_id = 0): array
	{

		// проверяем права пользователя
		$current_user = Gateway_Db_PremiseUser_UserList::getOne($user_id);

		// если пытаются получить пользователей со всех команд, проверяем права
		if ($premise_space_id === 0) {
			Domain_User_Entity_Permissions::assertIfNotHavePermissions((bool) $current_user->has_premise_permissions);
		}

		// получаем пользователей
		$user_list = Gateway_Db_PremiseUser_UserList::getByNpcTypeAndHasPermissions(\CompassApp\Domain\User\Main::NPC_TYPE_HUMAN, true);

		if ($premise_space_id != 0) {
			$user_list = self::_filterBySpace($user_list, $premise_space_id);
		}

		// сортируем по времени вступления
		uasort($user_list, fn (Struct_Db_PremiseUser_User $a, Struct_Db_PremiseUser_User $b) => $b->created_at <=> $a->created_at);

		$premise_administrator_list = [];
		$premise_accountant_list    = [];

		// является ли вызывавший пользователь администратором
		$can_get_administrator_list = Domain_User_Entity_Permissions::hasPermission(
			$current_user->premise_permissions,
			Domain_User_Entity_Permissions::SERVER_ADMINISTRATOR
		);

		foreach ($user_list as $user) {

			$is_administrator = Domain_User_Entity_Permissions::hasPermission($user->premise_permissions, Domain_User_Entity_Permissions::SERVER_ADMINISTRATOR);

			// собираем, если текущий пользователь администратор
			// и получили из базы администратора
			if ($premise_space_id !== 0 || ($can_get_administrator_list && $is_administrator)) {
				$premise_administrator_list[] = $user->user_id;
			}

			// если получили из базы бухгалтера
			if (Domain_User_Entity_Permissions::hasPermission($user->premise_permissions, Domain_User_Entity_Permissions::ACCOUNTANT)) {
				$premise_accountant_list[] = $user->user_id;
			}
		}

		return [$premise_administrator_list, $premise_accountant_list];
	}

	/**
	 * Отфильтровать по команде
	 *
	 * @param Struct_Db_PremiseUser_User[] $user_list
	 *
	 * @return Struct_Db_PremiseUser_User[]
	 * @throws ParseFatalException
	 */
	protected static function _filterBySpace(array $user_list, int $premise_space_id): array
	{

		$output_user_list = [];
		$user_space_list  = Gateway_Db_PremiseUser_SpaceList::getByUserListAndSpace(array_keys($user_list), $premise_space_id);

		foreach ($user_space_list as $user_space) {

			if (isset($user_list[$user_space->user_id])) {
				$output_user_list[$user_space->user_id] = $user_list[$user_space->user_id];
			}
		}

		return $output_user_list;
	}
}
