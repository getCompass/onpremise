<?php

namespace Compass\Pivot;

/**
 * класс описывает действие по отправке информации о пользователе в битрикс после регистрации в приложении
 */
class Domain_Bitrix_Action_OnUserRegistered {

	/**
	 * выполняем действие
	 */
	public static function do(int $user_id, string|null $force_stage_id):void {

		// проверяем, быть может этого пользователя уже отправляли в битрикс
		try {

			Domain_Bitrix_Entity_UserRel::get($user_id);

			// если дошли сюда, то ливаем – битрикс уже знает о пользователе все
			return;
		} catch (Domain_Bitrix_Exception_UserRelNotFound) {
			// все окей, идем дальше
		}

		// получаем все необходимые данные для отправки в битрекс
		[$user_info, $user_security, $created_company_count] = self::_collectRequiredData($user_id);

		// отправляем данные в битрекс
		try {
			self::_sendDataToBitrix($user_info, $user_security, $created_company_count, $force_stage_id);
		} catch (\BaseFrame\Exception\GatewayException $e) {
			throw new Domain_Bitrix_Exception_FailedApiRequest($e->getMessage());
		}
	}

	/**
	 * Собираем все необходимые данные для отправки в битрекс
	 *
	 * @return array
	 */
	protected static function _collectRequiredData(int $user_id):array {

		// получаем информацию о пользователе
		$user_info = Gateway_Db_PivotUser_UserList::getOne($user_id);

		// получаем номер телефона пользователя
		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

		// получаем кол-во пространств созданных пользователем
		$company_list          = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
		$created_company_count = Domain_Company_Entity_Company::getCountCompanyCreatedByUserId($user_id, $company_list);

		return [$user_info, $user_security, $created_company_count];
	}

	/**
	 * Отправляем данные в Битрикс
	 *
	 * @long
	 */
	protected static function _sendDataToBitrix(Struct_Db_PivotUser_User $user_info, Struct_Db_PivotUser_UserSecurity $user_security, int $created_company_count, string|null $force_stage_id):void {

		// заводим объект для работы с bitrix
		$bitrix_client = new Gateway_Api_Bitrix(BITRIX_AUTHORIZED_ENDPOINT_URL);

		// создаем сущность контакта
		$contact = $bitrix_client->crmContactAdd([
			"NAME"  => $user_info->full_name,
			"PHONE" => [["VALUE" => $user_security->phone_number, "VALUE_TYPE" => "WORK"]],
		]);

		// определяем на какой этап добавим сделку
		// если при добавлении задачи зафорсили какой-то конкретный stage_id, то создадим сделку
		// на этом этапе
		$stage_id = !is_null($force_stage_id) ? $force_stage_id : BITRIX_USER_REGISTERED_STAGE_ID;

		// тестовая ли регистрация
		$is_test_registration = inHtml($user_info->full_name, BITRIX_TEST_USER_NAME);

		// создаем сущность сделки
		$deal = $bitrix_client->crmDealAdd([
			"TITLE"                                    => $is_test_registration ? "Compass тестовый-пользователь" : "Compass пользователь",
			"CONTACT_ID"                               => $contact["result"],
			"STAGE_ID"                                 => $is_test_registration ? BITRIX_TEST_USER_REGISTERED_STAGE_ID : $stage_id,
			"CATEGORY_ID"                              => $is_test_registration ? BITRIX_TEST_USER_REGISTERED_CATEGORY_ID : BITRIX_USER_REGISTERED_CATEGORY_ID,
			BITRIX_DEAL_USER_FIELD_NAME__USER_ID       => $user_info->user_id,
			BITRIX_DEAL_USER_FIELD_NAME__REG_DATETIME  => date(DATE_FORMAT_FULL_S, $user_info->created_at),
			BITRIX_DEAL_USER_FIELD_NAME__HAS_OWN_SPACE => $created_company_count > 0 ? 1 : 0,

			// тип регистрации, пока что пишем пустоту – на данном этапе это зачастую не узнать
			BITRIX_DEAL_USER_FIELD_NAME__REG_TYPE      => "",

			// source_id, пока что пишем пустоту – на данном этапе это зачастую не узнать
			BITRIX_DEAL_USER_FIELD_NAME__SOURCE_ID     => "",
			"UTM_SOURCE"                               => "",

			// utm-ссылку целиком, пока что пишем пустоту – на данном этапе это зачастую не узнать
			"UTM_CAMPAIGN"                             => "",
		]);

		// сохраняем всю информацию по сущностям
		$bitrix_entity_list = [
			Domain_Bitrix_Entity_UserRel_Contact::init($contact["result"]),
			Domain_Bitrix_Entity_UserRel_Deal::init($deal["result"]),
		];

		// сохраняем сущность в базу
		Domain_Bitrix_Entity_UserRel::create($user_info->user_id, $bitrix_entity_list);
	}

}