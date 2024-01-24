<?php

namespace Compass\Pivot;

/**
 * класс описывает действие по актуализации данных в сделке
 */
class Domain_Bitrix_Action_OnUserChangeData {

	// смена статуса "Владелец пространства", ставим 1 если создал хотя бы 1 пространство
	public const CHANGED_SPACE_OWN_STATUS = "has_own_space";

	// смена Имя Фамилия
	public const CHANGED_FULL_NAME = "full_name";

	// смена номера телефона
	public const CHANGED_PHONE_NUMBER = "phone_number";

	// смена типа регистрации (первичный, вторичный)
	public const CHANGED_REG_TYPE = "reg_type";

	// смена source_id
	public const CHANGED_SOURCE_ID = "source_id";

	// смена utm-метки
	public const CHANGED_UTM_TAG = "utm_tag";

	/**
	 * Список свойств, изменение которых аффектит сущность контакта в битриксе
	 */
	protected const _FIELD_LIST_AFFECT_CONTACT = [
		self::CHANGED_FULL_NAME,
		self::CHANGED_PHONE_NUMBER,
	];

	/**
	 * Список свойств, изменение которых аффектит сущность сделки в битриксе
	 */
	protected const _FIELD_LIST_AFFECT_DEAL = [
		self::CHANGED_SPACE_OWN_STATUS,
		self::CHANGED_REG_TYPE,
		self::CHANGED_SOURCE_ID,
		self::CHANGED_UTM_TAG,
	];

	/**
	 * выполняем действие
	 */
	public static function do(int $user_id, array $changed_data):void {

		// проверяем, имеется ли в битриксе информация об этом пользователе
		try {
			$bitrix_user_entity_rel = Domain_Bitrix_Entity_UserRel::get($user_id);
		} catch (Domain_Bitrix_Exception_UserRelNotFound) {

			// пользователя нет, значит ничего не делаем
			return;
		}

		try {

			// обновляем сущность контакта, если есть изменения
			self::_updateContactIfHasChanges($bitrix_user_entity_rel, $changed_data);

			// обновляем сущность сделки, если есть изменения
			self::_updateDealIfHasChanges($bitrix_user_entity_rel, $changed_data);
		} catch (\BaseFrame\Exception\GatewayException $e) {
			throw new Domain_Bitrix_Exception_FailedApiRequest($e->getMessage());
		}
	}

	/**
	 * обновляем сущность контакта, если есть изменения
	 */
	protected static function _updateContactIfHasChanges(Struct_Db_PivotBusiness_BitrixUserEntityRel $bitrix_user_entity_rel, array $changed_data):void {

		// сюда сложим все поля, которые будем обновлять в сущности контакта
		// все указываем в формате, который ожидает битрикс
		$bitrix_update_request_fields = [];

		// пробегаемся по каждому изменению
		foreach ($changed_data as $changed_field => $new_value) {

			// если измененное поле не аффектит сущность контакта, то пропускаем его
			if (!in_array($changed_field, self::_FIELD_LIST_AFFECT_CONTACT)) {
				continue;
			}

			// иначе вызываем нужную функцию для форматирования параметров запроса на обновление
			$bitrix_update_request_fields = match ($changed_field) {

				self::CHANGED_FULL_NAME    => self::_onChangeFullName($new_value, $bitrix_update_request_fields),
				self::CHANGED_PHONE_NUMBER => self::_onChangePhoneNumber($new_value, $bitrix_update_request_fields),
				default                    => throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected behaviour"),
			};
		}

		// если нет ничего к обновлению, то ничего не делаем
		if (count($bitrix_update_request_fields) < 1) {
			return;
		}

		// иначе обновляем:
		// получаем айтем сущности контакта
		$contact_entity_item = Domain_Bitrix_Entity_UserRel::getEntityItemByType($bitrix_user_entity_rel, Domain_Bitrix_Entity_UserRel_Contact::ENTITY_TYPE);

		// если не нашли, то завершаем
		if (is_null($contact_entity_item)) {
			return;
		}

		// получаем ID контакта в битриксе, который закреплен за пользователем
		$contact_id = Domain_Bitrix_Entity_UserRel_Contact::getContactID($contact_entity_item);

		// делаем запрос на обновление
		$bitrix_client = new Gateway_Api_Bitrix(BITRIX_AUTHORIZED_ENDPOINT_URL);
		$bitrix_client->crmContactUpdate($contact_id, $bitrix_update_request_fields);
	}

	/**
	 * обновляем сущность сделки, если есть изменения
	 */
	protected static function _updateDealIfHasChanges(Struct_Db_PivotBusiness_BitrixUserEntityRel $bitrix_user_entity_rel, array $changed_data):void {

		// сюда сложим все поля, которые будем обновлять в сущности сделки
		// все указываем в формате, который ожидает битрикс
		$bitrix_update_request_fields = [];

		// пробегаемся по каждому изменению
		foreach ($changed_data as $changed_field => $new_value) {

			// если измененное поле не аффектит сущность сделки, то пропускаем его
			if (!in_array($changed_field, self::_FIELD_LIST_AFFECT_DEAL)) {
				continue;
			}

			// иначе вызываем нужную функцию для форматирования параметров запроса на обновление
			$bitrix_update_request_fields = match ($changed_field) {
				self::CHANGED_SPACE_OWN_STATUS => self::_onChangeSpaceOwnStatus($new_value, $bitrix_update_request_fields),
				self::CHANGED_REG_TYPE         => self::_onChangeRegType($new_value, $bitrix_update_request_fields),
				self::CHANGED_SOURCE_ID        => self::_onChangeSourceID($new_value, $bitrix_update_request_fields),
				self::CHANGED_UTM_TAG          => self::_onChangeUtmTag($new_value, $bitrix_update_request_fields),
				default                        => throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected behaviour"),
			};
		}

		// если нет ничего к обновлению, то ничего не делаем
		if (count($bitrix_update_request_fields) < 1) {
			return;
		}

		// иначе обновляем:
		// получаем айтем сущности сделки
		$deal_entity_item = Domain_Bitrix_Entity_UserRel::getEntityItemByType($bitrix_user_entity_rel, Domain_Bitrix_Entity_UserRel_Deal::ENTITY_TYPE);

		// если не нашли, то выходим
		if (is_null($deal_entity_item)) {
			return;
		}

		// получаем ID сделки в битриксе, который закреплен за пользователем
		$deal_id = Domain_Bitrix_Entity_UserRel_Deal::getDealID($deal_entity_item);

		// делаем запрос на обновление
		$bitrix_client = new Gateway_Api_Bitrix(BITRIX_AUTHORIZED_ENDPOINT_URL);
		$bitrix_client->crmDealUpdate($deal_id, $bitrix_update_request_fields);
	}

	/**
	 * При смене флага "Является владельцем пространства"
	 *
	 * @return array
	 */
	protected static function _onChangeSpaceOwnStatus(mixed $new_value, array $bitrix_update_request_fields):array {

		// обновляем нужное поле в битриксе
		$bitrix_update_request_fields[BITRIX_DEAL_USER_FIELD_NAME__HAS_OWN_SPACE] = intval($new_value);

		return $bitrix_update_request_fields;
	}

	/**
	 * При смене Имя Фамилия
	 *
	 * @return array
	 */
	protected static function _onChangeFullName(string $new_value, array $bitrix_update_request_fields):array {

		// обновляем нужное поле в битриксе
		$bitrix_update_request_fields["NAME"] = $new_value;

		return $bitrix_update_request_fields;
	}

	/**
	 * При смене номера телефона
	 *
	 * @return array
	 */
	protected static function _onChangePhoneNumber(string $new_value, array $bitrix_update_request_fields):array {

		// обновляем нужное поле в битриксе
		$bitrix_update_request_fields["PHONE"] = [["VALUE" => $new_value, "VALUE_TYPE" => "WORK"]];

		return $bitrix_update_request_fields;
	}

	/**
	 * При смене типа регистрации
	 *
	 * @return array
	 */
	protected static function _onChangeRegType(string $new_value, array $bitrix_update_request_fields):array {

		// обновляем нужное поле в битриксе
		$bitrix_update_request_fields[BITRIX_DEAL_USER_FIELD_NAME__REG_TYPE] = $new_value;

		return $bitrix_update_request_fields;
	}

	/**
	 * При смене source_id
	 *
	 * @return array
	 */
	protected static function _onChangeSourceID(string $new_value, array $bitrix_update_request_fields):array {

		// обновляем нужное поле в битриксе
		$bitrix_update_request_fields[BITRIX_DEAL_USER_FIELD_NAME__SOURCE_ID] = trim($new_value);
		$bitrix_update_request_fields["UTM_SOURCE"]                           = trim($new_value);

		return $bitrix_update_request_fields;
	}

	/**
	 * При смене utm_tag
	 *
	 * @return array
	 */
	protected static function _onChangeUtmTag(string $new_value, array $bitrix_update_request_fields):array {

		// обновляем нужное поле в битриксе
		$bitrix_update_request_fields["UTM_CAMPAIGN"] = trim($new_value);

		return $bitrix_update_request_fields;
	}

}