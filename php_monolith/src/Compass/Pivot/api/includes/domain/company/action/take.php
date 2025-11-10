<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\System\Locale;

/**
 * Занимает горячую компанию для пользователя.
 * Инициирует дефолтные действия в ней и добавляет создателя.
 */
class Domain_Company_Action_Take
{
	protected const _HIBERNATION_IMMUNITY_TILL = DAY7;

	/**
	 * Создает новую компанию с казанными параметрами.
	 *
	 *
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_NoFreeCompanyFound
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws \queryException
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => Struct_Db_PivotCompany_Company::class, 1 => Struct_Db_PivotUser_Company::class])]
	public static function do(int $created_by_user_id, int $avatar_color_id, string $name, string $client_company_id, string $avatar_file_map, bool $is_need_create_intercom_conversation): array
	{

		// получаем свободную компанию для дальнейшей передачи пользователю
		$company_init_registry = static::_getVacant();

		/** начало транзакции для списка компаний */
		try {

			// получаем саму горячую компанию,
			// чтобы передать ее пользователю и убрать из списка свободных
			$company_to_take = Gateway_Db_PivotCompany_CompanyList::getOne($company_init_registry->company_id);
		} catch (\cs_RowIsEmpty) {

			// какой-то рассинхрон данных в базе, есть свободная, но нет фактической
			throw new cs_NoFreeCompanyFound("there are no free companies");
		}

		if (!Domain_Company_Entity_Company::isAllowedToTake($company_to_take)) {

			// ситуация, когда компания уже каким-то образом занялась ранее
			throw new ReturnFatalException("can't take company — the company is already taken");
		}

		// это не баг partner_id пользователя равняется user_id пользователя
		$invited_by_partner_id = $created_by_user_id;

		// вносим в компанию данные о том, кто и как ее занимает
		$company = static::_fillPivot($company_to_take, $created_by_user_id, $invited_by_partner_id, $avatar_color_id, $name, $client_company_id, $avatar_file_map);

		// получаем домино и порт для генерации конфига
		$domino = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company->domino_id);
		$port   = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($domino->domino_id, $company->company_id);

		// инициализируем таблицу для поиска в пространстве
		Gateway_Bus_DatabaseController::initSearch($domino, $company->company_id);

		// выполняем всякие штуки в логике компании
		static::_fillWorld($company);

		$company->status = Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE;
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, ["status" => $company->status]);

		// перегенериваем конфиг для компании
		Domain_Domino_Action_Config_UpdateMysql::do($company, $domino, $port, true);

		// добавляем создателя в компанию
		// делаем это в самом конце, чтобы вдруг ему случайно не досталась битая компания
		[$user_company, $company] = static::_joinCreator($company, $is_need_create_intercom_conversation);

		//
		static::_afterTake($company->company_id, $created_by_user_id, $domino->tier);

		return [$company, $user_company];
	}

	/**
	 * Возвращает информацию о наличии свободной горячей компании.
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_NoFreeCompanyFound
	 */
	protected static function _getVacant(): Struct_Db_PivotCompanyService_CompanyInitRegistry
	{

		/** начало транзакции для списка свободных компаний */
		Gateway_Db_PivotCompanyService_Main::setReadCommittedIsolationLevelInTransaction();
		Gateway_Db_PivotCompanyService_Main::beginTransaction();

		// получаем горячую свободную компанию
		try {
			$company_init_registry = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getVacantForUpdate();
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// если вдруг горячих компаний не оказалось
			Gateway_Db_PivotCompanyService_Main::rollback();

			throw new cs_NoFreeCompanyFound("there are no free companies");
		}

		$company_init_registry->logs = Domain_Company_Entity_InitRegistry_Logs::addCompanyStartOccupationLog($company_init_registry->logs);
		$set                         = [
			"occupation_started_at" => time(),
			"logs"                  => $company_init_registry->logs,
			"is_vacant"             => 0,
		];
		Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company_init_registry->company_id, $set);
		Gateway_Db_PivotCompanyService_Main::commitTransaction();

		return $company_init_registry;
	}

	/**
	 * Заносит все данные по компании, которые привязаны к пивоту.
	 *
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _fillPivot(Struct_Db_PivotCompany_Company $company_to_take, int $created_by_user_id, int $creator_partner_id, int $avatar_color_id, string $name, string $client_company_id, string $avatar_file_map): Struct_Db_PivotCompany_Company
	{

		Gateway_Db_PivotCompany_CompanyList::set($company_to_take->company_id, [
			"created_by_user_id" => $created_by_user_id,
			"name"               => $name,
			"avatar_color_id"    => $avatar_color_id,
			"partner_id"         => $creator_partner_id,
			"created_at"         => time(),
			"avatar_file_map"    => $avatar_file_map,
			"extra"              => Domain_Company_Entity_Company::setClientCompanyId($company_to_take->extra, $client_company_id),
		]);

		return Gateway_Db_PivotCompany_CompanyList::getOne($company_to_take->company_id);
	}

	/**
	 * Заносит все данные по компании, которые привязаны к миру.
	 *
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 */
	protected static function _fillWorld(Struct_Db_PivotCompany_Company $company): Struct_Db_PivotCompany_Company
	{

		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		$bot_list              = Domain_Company_Entity_Company::getBotList();
		$default_file_key_list = Domain_Company_Entity_Company::getDefaultFileListStruct();

		$hibernation_immunity_till = time() + self::_HIBERNATION_IMMUNITY_TILL;
		if (isTestServer() && !isBackendTest()) {

			$hibernation_delayed_time  = defined("COMPANY_HIBERNATION_DELAYED_TIME") ? COMPANY_HIBERNATION_DELAYED_TIME : DAY_1;
			$hibernation_immunity_till = time() + $hibernation_delayed_time;
		}
		Gateway_Socket_Company::doActionsOnCreateCompany(
			$company->created_by_user_id,
			$company->name,
			$default_file_key_list,
			$bot_list,
			$company->company_id,
			$company->created_at,
			$company->domino_id,
			$private_key,
			$hibernation_immunity_till,
			isTestServer() && getHeader("HTTP_BASE_EMPLOYEE_CARD") != 1 && !isBackendTest(),
			Locale::getLocale()
		);

		return $company;
	}

	/**
	 * Добавляем создателя в компанию как участника.
	 *
	 *
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws \queryException
	 */
	protected static function _joinCreator(Struct_Db_PivotCompany_Company $company, bool $is_need_create_intercom_conversation): array
	{

		try {

			// добавляем создателя в компанию
			[$user_company, $company] = Domain_Company_Action_Member_AddCreator::do(
				$company->created_by_user_id,
				$company,
				Locale::getLocale(),
				$is_need_create_intercom_conversation
			);
		} catch (\Exception $e) {

			// случай если не получилось добавить создателя
			Type_System_Admin::log(
				"company_add_owner",
				"Не смогли добавить пользователя {$company->created_by_user_id} в компанию {$company->company_id}",
				true
			);

			// помечаем компанию как недоступную, чтобы никто не попал в нее
			Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
				"status"     => Domain_Company_Entity_Company::COMPANY_STATUS_INVALID,
				"updated_at" => time(),
			]);

			Gateway_Db_PivotCompanyService_Main::beginTransaction();

			// получаем горячую свободную компанию
			$company_init_registry = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getForUpdate($company->company_id);

			$company_init_registry->logs = Domain_Company_Entity_InitRegistry_Logs::addCompanyInvalidOccupationLog($company_init_registry->logs);
			$set                         = [
				"logs" => $company_init_registry->logs,
			];
			Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company_init_registry->company_id, $set);
			Gateway_Db_PivotCompanyService_Main::commitTransaction();

			$message = ":exclamation: Получили exception при добавлении создателя компании. Клиент получил 500";
			Gateway_Notice_Sender::sendGroup(LEGACY_NOTICE_PROVIDER_CHANNEL_KEY, $message);

			throw $e;
		}

		return [$user_company, $company];
	}

	/**
	 * выполняем после занятия компании
	 *
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @long
	 */
	protected static function _afterTake(int $company_id, int $occupant_user_id, int $current_domino_tier): void
	{

		Gateway_Db_PivotCompanyService_Main::beginTransaction();

		// получаем горячую свободную компанию
		$company_init_registry = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getForUpdate($company_id);

		$company_init_registry->logs = Domain_Company_Entity_InitRegistry_Logs::addCompanyFinishedOccupationLog($company_init_registry->logs);
		$set                         = [
			"occupation_finished_at" => time(),
			"occupant_user_id"       => $occupant_user_id,
			"logs"                   => $company_init_registry->logs,
		];
		Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company_init_registry->company_id, $set);
		Gateway_Db_PivotCompanyService_Main::commitTransaction();

		// добавляем в обзерв
		Gateway_Db_PivotCompany_CompanyTierObserve::insert(
			$company_id,
			$current_domino_tier,
			0,
			Domain_Company_Entity_Tier::initExtra()
		);
	}

}