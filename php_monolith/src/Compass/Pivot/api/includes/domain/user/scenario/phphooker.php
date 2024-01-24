<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;
use PhoneCarrierLookup\Exception\CarrierNotFound;

/**
 * Класс обработки сценариев задач phphooker
 */
class Domain_User_Scenario_Phphooker {

	/**
	 * задача срабатывает при достижении expires_at auth_story – попытки залогиниться/зарегистрироваться в приложении
	 */
	public static function onAuthStoryExpire(string $auth_map):void {

		// получаем запись с действием
		$auth_story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// если попытка увенчалась успехом, то завершаем задачу – ничего делать не нужно
		if ($auth_story->getStoryData()->auth->is_success == 1) {
			return;
		}

		// получаем сотового оператора по номеру телефона, на который отправляли смс
		try {
			$phone_number_operator = \PhoneCarrierLookup\Main::getCarrierName($auth_story->getPhoneNumber());
		} catch (CarrierNotFound|\PhoneCarrierLookup\Exception\UnsupportedPhoneNumber) {

			// если не удалось получить, то оставляем пустым
			$phone_number_operator = "";
		}

		// отправляем в аналитику, что попытка не увенчалась успехом
		Type_Sms_Analytics_Story::onExpired(
			$auth_story->getUserId(),
			Type_Sms_Analytics_Story::STORY_TYPE_AUTH,
			$auth_map,
			$auth_story->getExpiresAt(),
			$auth_story->getSmsId(),
			$auth_story->getPhoneNumber(),
			$phone_number_operator
		);
	}

	/**
	 * задача срабатывает при достижении expires_at phone_change_story – попытки сменить номер телефона
	 */
	public static function onPhoneChangeStoryExpire(int $user_id, string $auth_map):void {

		// получаем запись с действием
		$phone_change_story = Domain_User_Entity_ChangePhone_Story::getByMap($user_id, $auth_map);

		// если попытка увенчалась успехом, то завершаем задачу – ничего делать не нужно
		if ($phone_change_story->getStoryData()->status == Domain_User_Entity_ChangePhone_Story::STATUS_SUCCESS) {
			return;
		}

		// получаем номер телефона & sms_id, на котором остановилось flow
		$last_sms_story = self::_getLastSmsStory($phone_change_story->getStoryMap());

		// получаем сотового оператора по номеру телефона, на который отправляли смс
		try {
			$phone_number_operator = \PhoneCarrierLookup\Main::getCarrierName($last_sms_story->phone_number);
		} catch (CarrierNotFound|\PhoneCarrierLookup\Exception\UnsupportedPhoneNumber) {

			// если не удалось получить, то оставляем пустым
			$phone_number_operator = "";
		}

		// отправляем в аналитику, что попытка не увенчалась успехом
		Type_Sms_Analytics_Story::onExpired($user_id, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_CHANGE, $auth_map,
			$phone_change_story->getExpiresAt(), $last_sms_story->sms_id, $last_sms_story->phone_number, $phone_number_operator);
	}

	/**
	 * получаем последнюю запись из phone_change_via_sms_story
	 *
	 * @return Struct_Db_PivotPhone_PhoneChangeViaSmsStory
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _getLastSmsStory(string $phone_change_story_map):Struct_Db_PivotPhone_PhoneChangeViaSmsStory {

		$list = Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::getByStory($phone_change_story_map);

		usort($list, function(Struct_Db_PivotPhone_PhoneChangeViaSmsStory $a, Struct_Db_PivotPhone_PhoneChangeViaSmsStory $b) {

			return $a->stage <=> $b->stage;
		});

		return $list[count($list) - 1];
	}

	/**
	 * задача срабатывает при достижении expires_at two_fa_story – двух-факторного подтверждения действия
	 */
	public static function onTwoFaStoryExpire(string $two_fa_map):void {

		try {
			$two_fa = Gateway_Db_PivotAuth_TwoFaList::getOne($two_fa_map);
		} catch (\cs_RowIsEmpty) {

			// если это тестовый сервер, то просто завершаем задачу
			if (isTestServer()) {
				return;
			}

			// если не получили записи из таблицы 2fa_list - что-то пошло не так
			throw new cs_WrongTwoFaKey("not found this 2fa_map in 2fa_list");
		}

		try {
			$two_fa_phone = Gateway_Db_PivotAuth_TwoFaPhoneList::getOne($two_fa_map);
		} catch (\cs_RowIsEmpty) {

			// тут вполне реальный кейс, когда смс не отправилось и запись здесь не создалась
			return;
		}

		$two_fa_story = new Domain_User_Entity_TwoFa_Story($two_fa, $two_fa_phone);

		// если попытка увенчалась успехом, то завершаем задачу – ничего делать не нужно
		if ($two_fa_story->getTwoFaInfo()->is_success == 1) {
			return;
		}

		// получаем сотового оператора по номеру телефона, на который отправляли смс
		try {
			$phone_number_operator = \PhoneCarrierLookup\Main::getCarrierName($two_fa_story->getPhoneInfo()->phone_number);
		} catch (CarrierNotFound|\PhoneCarrierLookup\Exception\UnsupportedPhoneNumber) {

			// если не удалось получить, то оставляем пустым
			$phone_number_operator = "";
		}

		// отправляем в аналитику, что попытка не увенчалась успехом
		Type_Sms_Analytics_Story::onExpired($two_fa_story->getTwoFaInfo()->user_id, Type_Sms_Analytics_Story::STORY_TYPE_TWO_FA, $two_fa_map,
			$two_fa_story->getTwoFaInfo()->expires_at, $two_fa_story->getPhoneInfo()->sms_id, $two_fa_story->getPhoneInfo()->phone_number, $phone_number_operator);
	}

	/**
	 * Задача отправки аналитики по пользователю
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public static function onSendAccountStatusLog(int $user_id, int $action):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// получаем сущность пользователя
		try {

			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			return;
		}

		// этот ивент шлем пока только в партнерку
		if ($action == Type_User_Analytics::FIRST_PROFILE_SET) {

			// получаем информацию по аттрибуции
			[$source_type, $source_extra] = Domain_User_Entity_Attribution::getUserSourceData($user_id);

			// отправляем в партнерку событие о регистрации пользователя
			Domain_Partner_Entity_Event_UserRegistered::create($user_id, $source_type, $source_extra, $user_info->created_at);
			return;
		}

		// получаем список активных компаний пользователя
		$user_company_list    = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
		$user_company_id_list = array_column($user_company_list, "company_id");

		// получаем ссылку по которой пользователь вступил
		try {

			$link_row  = Gateway_Db_PivotData_CompanyJoinLinkUserRel::getByUserId($user_id);
			$join_link = $link_row->join_link_uniq ?? "";
		} catch (\cs_RowIsEmpty) {
			$join_link = "";
		}

		$account_status = Type_User_Main::isDisabledProfile($user_info->extra) ? 0 : 1;
		[$space_id_creator_list, $space_id_admin_list] = self::_getSpaceIdListWhereCreator($user_id, $user_company_id_list);

		// пока неоткуда брать
		$space_id_payed_list = [];

		// получаем имя страны пользователя
		try {

			$phone_number_obj = Domain_User_Scenario_Api::getPhoneNumberInfo($user_id);
			$country_name     = \BaseFrame\Conf\Country::get($phone_number_obj->countryCode())->getLocalizedName();
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber|cs_UserPhoneSecurityNotFound|\BaseFrame\Exception\Domain\CountryNotFound) {
			$country_name = "";
		}

		// отправляем ивент в коллектор
		Type_User_Analytics::send($user_id, $action, $account_status, time(), $user_info->created_at, $user_info->last_active_day_start_at, 0,
			$join_link, $country_name, $user_company_id_list, $space_id_admin_list, $space_id_creator_list, $space_id_payed_list);
	}

	/**
	 * Получаем список компаний, где пользователь создатель и админ
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _getSpaceIdListWhereCreator(int $user_id, array $user_company_id_list):array {

		$company_info_list = Gateway_Db_PivotCompany_CompanyList::getList($user_company_id_list);

		$space_id_creator_list = [];
		$space_id_admin_list   = [];

		foreach ($company_info_list as $company) {

			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);
			try {

				if (Gateway_Socket_Company::checkIsOwner($user_id, $company->company_id, $company->domino_id, $private_key)) {
					$space_id_admin_list[] = $company->company_id;
				}
			} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
				// ничего, компания спит
			}

			if ($company->created_by_user_id == $user_id) {
				$space_id_creator_list[] = $company->company_id;
			}
		}

		return [$space_id_creator_list, $space_id_admin_list];
	}

	/**
	 * Задача отправки аналитики по пространству
	 */
	public static function onSendSpaceStatusLog(int $company_id, int $action):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// отправляем ивент в коллектор
		Type_Space_Analytics::send($action, $company_id);
	}
}