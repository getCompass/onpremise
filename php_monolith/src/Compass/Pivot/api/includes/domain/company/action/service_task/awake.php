<?php

namespace Compass\Pivot;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Разбудить компанию
 */
class Domain_Company_Action_ServiceTask_Awake implements Domain_Company_Action_ServiceTask_Main {

	protected const _PUBLISH_ANNOUNCEMENT_TIME = 3;		// через какое то время опубликовать анонс
	protected const _HIBERNATION_IMMUNITY_TILL = DAY1;	// сколько компания не засыпает
	protected const _AWAKE_TIMEOUT             = 10;      // время сколько ждем пробуждения компании

	/**
	 * Разбудить компанию
	 *
	 * @param Struct_Db_PivotCompany_Company                $company_row
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 * @param \BaseFrame\System\Log                         $log
	 * @param array                                         $data
	 *
	 * @return \BaseFrame\System\Log
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = []):\BaseFrame\System\Log {

		$hibernate_at = $company_row->updated_at;

		if (!isTestServer() && !isStageServer()) {
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_SERVICE, "Пробую пробудить компанию {$company_row->company_id}");
		}

		try {
			$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_SERVICE, "Не удалось пробудить компанию {$company_row->company_id}");
			throw new Domain_Company_Exception_ConfigNotExist("there is no domino assigned to company");
		}

		if ($company_row->status !== Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED || !$company_registry->is_hibernated) {

			$log_text = "Компания {$company_row->company_id} не была в гибернации";
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_SERVICE, $log_text);
			return $log->addText($log_text);
		}

		// запускаем компанию и синхронизируем конфиг
		[$company_row] = Domain_Domino_Action_StartCompany::run($domino_registry_row, $company_row);

		[$user_need_sync, $user_info_need_sync, $user_info_update_list] = self::_getUserIdNeedSync($company_row, $hibernate_at);
		$respect_file = Gateway_Db_PivotSystem_DefaultFileList::get("respect_conversation_avatar");

		Gateway_Socket_Company::awake(
			$company_row->company_id,
			time() + self::_HIBERNATION_IMMUNITY_TILL,
			time(),
			$company_row->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company_row->extra),
			$user_need_sync,
			$user_info_need_sync,
			self::_getRemindBotInfo(),
			self::_getSupportBotInfo(),
			$respect_file->file_key,
			$user_info_update_list
		);

		Gateway_Bus_SenderBalancer::companyAwoke($company_row->company_id, Domain_Company_Action_GetUserIdList::do($company_row));
		Type_Phphooker_Main::onCountCompany(time());

		// добавляем в обзерв
		Gateway_Db_PivotCompany_CompanyTierObserve::insert(
			$company_row->company_id,
			$domino_registry_row->tier,
			0,
			Domain_Company_Entity_Tier::initExtra()
		);

		if (!isTestServer() && !isStageServer()) {
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_SERVICE, "Успешно пробудил компанию {$company_row->company_id}");
		}

		return self::_writeLog($company_row->company_id, $company_row->domino_id, $log);
	}

	/**
	 * Записать логи
	 *
	 * @param int                   $company_id
	 * @param string                $domino_id
	 * @param \BaseFrame\System\Log $log
	 *
	 * @return \BaseFrame\System\Log
	 */
	protected static function _writeLog(int $company_id, string $domino_id, \BaseFrame\System\Log $log):\BaseFrame\System\Log {

		Type_System_Analytic::save($company_id, $domino_id, Type_System_Analytic::TYPE_POST_AWAKE);

		return $log->addText("В компании $company_id убран анонс о гибернации компании");
	}

	/**
	 * Получим пользователей которых нужно синхроинизировать в компании и на пивоте
	 *
	 * @param Struct_Db_PivotCompany_Company $company_row
	 * @param int                            $hibernate_at
	 *
	 * @long большая структура цикла
	 * @return array
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_UserNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _getUserIdNeedSync(Struct_Db_PivotCompany_Company $company_row, int $hibernate_at):array {

		// получим пользователей в компании
		$user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($company_row->company_id);

		// получаем пользователей, находящихся в лобби
		$lobby_user_rel_list = Gateway_Db_PivotData_CompanyJoinLinkUserRel::getByCompanyIdAndStatus(
			$company_row->company_id, Domain_Company_Entity_JoinLink_UserRel::JOIN_LINK_REL_POSTMODERATION
		);
		$lobby_user_id_list  = array_column($lobby_user_rel_list, "user_id");

		$all_user_id_list  = array_merge($user_id_list, $lobby_user_id_list);
		$user_company_list = Gateway_Bus_PivotCache::getUserListInfo($all_user_id_list);

		$user_need_sync        = [];
		$user_info_need_sync   = [];
		$user_info_update_list = [];

		/** @var Struct_Db_PivotUser_User $user */
		foreach ($user_company_list as $user_info) {

			if (!Type_User_Main::isDisabledProfile($user_info->extra)) {

				// если обновилась информация у активного пользователя во время сна компании
				if ($user_info->updated_at >= $hibernate_at) {

					$user_info = Struct_User_Info::createStruct($user_info);

					$user_info_update_list[$user_info->user_id] = [
						"full_name"       => $user_info->full_name,
						"avatar_file_key" => isEmptyString($user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($user_info->avatar_file_map),
						"avatar_color_id" => $user_info->avatar_color_id,
					];
				}

				continue;
			}

			// если пользователь удалил аккаунт - надо синхронизироваться с компанией
			$user_need_sync[] = $user_info->user_id;

			// для пользователей из лобби собираем информацию по их имени и аватарке
			if (in_array($user_info->user_id, $lobby_user_id_list)) {

				$user_info = Struct_User_Info::createStruct($user_info);

				$user_info_need_sync[$user_info->user_id] = [
					"full_name"       => $user_info->full_name,
					"avatar_file_key" => isEmptyString($user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($user_info->avatar_file_map),
					"avatar_color_id" => $user_info->avatar_color_id,
					"disabled_at"     => $user_info->disabled_at,
				];
			}
		}

		return [$user_need_sync, $user_info_need_sync, $user_info_update_list];
	}

	/**
	 * получаем данные по боту Напоминаний
	 *
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	#[ArrayShape(["user_id" => "int", "full_name" => "string", "avatar_file_key" => "string", "npc_type" => "int"])]
	protected static function _getRemindBotInfo():array {

		$bot_info = Gateway_Bus_PivotCache::getUserInfo(REMIND_BOT_USER_ID);

		return [
			"user_id"         => $bot_info->user_id,
			"full_name"       => $bot_info->full_name,
			"avatar_file_key" => mb_strlen($bot_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($bot_info->avatar_file_map) : "",
			"npc_type"        => $bot_info->npc_type,
		];
	}

	/**
	 * Получаем данные по боту Службы поддержки
	 *
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	#[ArrayShape(["user_id" => "int", "full_name" => "string", "avatar_file_key" => "string", "npc_type" => "int"])]
	protected static function _getSupportBotInfo():array {

		$bot_info = Gateway_Bus_PivotCache::getUserInfo(SUPPORT_BOT_USER_ID);

		return [
			"user_id"         => $bot_info->user_id,
			"full_name"       => $bot_info->full_name,
			"avatar_file_key" => mb_strlen($bot_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($bot_info->avatar_file_map) : "",
			"npc_type"        => $bot_info->npc_type,
		];
	}
}