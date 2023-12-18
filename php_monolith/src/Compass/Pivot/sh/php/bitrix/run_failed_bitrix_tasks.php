<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для запуска всех проваленных задач по отправке информации в битрикс
 */
class Bitrix_RunFailedBitrixTasks {

	/**
	 * Запускаем скрипт
	 */
	public static function run():void {

		// получаем список всех проваленных задач
		$failed_task_list = Gateway_Db_PivotBusiness_BitrixUserInfoFailedTaskList::getAll();

		// если пусто, то завершаем
		$count = count($failed_task_list);
		if ($count < 1) {

			console("Проваленных задач нет");
			return;
		}

		console("Начинаем работу с проваленными задачами. Всего таких задач – $count ");

		// оставляем все уникальные user_id, чтобы не делать задачу > 1 раза для 1 пользователя
		$user_id_list = array_unique(array_column($failed_task_list, "user_id"));

		// бежим по каждому пользователю и смотрим что нужно сделать
		foreach ($user_id_list as $user_id) {

			self::doWork($user_id);

			console("Исправили задачу с пользователем $user_id");
		}

		// удаляем все проваленных задачи
		Gateway_Db_PivotBusiness_BitrixUserInfoFailedTaskList::deleteList(array_column($failed_task_list, "task_id"));

		console("Закончили");
	}

	/**
	 * Делаем работу с 1 пользователем
	 */
	public static function doWork(int $user_id):void {

		// если здесь будет true, то в результате функции ничего не заафектится, самое то для dry-run режима
		$is_dry_run = isDryRun();

		// проверяем, ранее отправляли ли мы инфу по этому пользователю в bitrix
		try {
			Domain_Bitrix_Entity_UserRel::get($user_id);
		} catch (Domain_Bitrix_Exception_UserRelNotFound) {

			// значит нужно отправить инфу по пользователю – эта задача и отвалилась в phphooker
			// поэтому запускаем ее снова
			!$is_dry_run && Type_Phphooker_Main::sendBitrixOnUserRegistered($user_id);

			return;
		}

		// собираем всю актуальную инфу, которая может понадобится в битриксе
		// и отправляем ее на актуализацию:

		// получаем кол-во компаний пользователя
		$created_company_count = self::_getCountOfUserCreatedCompany($user_id);

		// получаем инфу о пользователе
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		// получим номер телефона пользователя
		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

		// получим всю инфу по рекламной кампании, с которой пользователь пришел в приложение
		[$link, $source_id, $is_direct_reg] = Domain_User_Entity_Attribution::getUserCampaignRelData($user_id);

		// отправляем всю актуальную собранную информацию в битрикс
		!$is_dry_run && Type_Phphooker_Main::sendBitrixOnUserChangedInfo($user_id, [
			Domain_Bitrix_Action_OnUserChangeData::CHANGED_SPACE_OWN_STATUS => $created_company_count > 0 ? 1 : 0,
			Domain_Bitrix_Action_OnUserChangeData::CHANGED_FULL_NAME        => $user_info->full_name,
			Domain_Bitrix_Action_OnUserChangeData::CHANGED_PHONE_NUMBER     => $user_security->phone_number,
			Domain_Bitrix_Action_OnUserChangeData::CHANGED_REG_TYPE         => Domain_Bitrix_Entity_Main::convertIsDirectRegToBitrixValueFormat($is_direct_reg),
			Domain_Bitrix_Action_OnUserChangeData::CHANGED_SOURCE_ID        => $source_id,
			Domain_Bitrix_Action_OnUserChangeData::CHANGED_UTM_TAG          => $link,
		]);
	}

	/**
	 * Получаем кол-во созданных компаний пользователем
	 *
	 * @return int
	 * @throws cs_CompanyCreateExceededLimit
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _getCountOfUserCreatedCompany(int $user_id):int {

		// получаем все компании, где пользователь активный участник
		$company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);

		// проверям количество созданных компаний пользователем
		return Domain_Company_Entity_Company::getCountCompanyCreatedByUserId($user_id, $company_list);
	}
}

// если прислали аргумент --help
if (Type_Script_InputHelper::needShowUsage()) {

	console("Данный скрипт перезапустит все проваленные задачи по отправке информации в битрикс");
	console("Запустите скрипт без флага --help, чтобы начать");
	console("Скрипт поддерживает флаг --dry-run – в таком случае работа скрипта не проделает никаких write-операций");
	exit(0);
}

Bitrix_RunFailedBitrixTasks::run();