<?php

namespace Compass\Company;

/**
 * Класс для валидации данных премиума
 */
class Domain_Premium_Entity_Validator {

	/**
	 * проверяем параметры для получения статусов сотрудников
	 *
	 * @throws Domain_Premium_Exception_IncorrectParam
	 */
	public static function checkParamsForGetMemberStatusList(int $count, int $offset, string $sort_order, string $sort_field):void {

		// проверяем параметры для пагинации
		if ($count < 1 || $count > Domain_Premium_Action_PaymentRequest_GetMemberStatusList::LIMIT) {
			throw new Domain_Premium_Exception_IncorrectParam("incorrect param count");
		}

		if ($offset < 0) {
			throw new Domain_Premium_Exception_IncorrectParam("incorrect param offset");
		}

		// проверяем параметр типа для сортировки
		if (!in_array($sort_order, Domain_Premium_Action_PaymentRequest_GetMemberStatusList::ALLOW_SORT_ORDER_LIST)) {
			throw new Domain_Premium_Exception_IncorrectParam("incorrect param sort_order");
		}

		// проверяем параметр поля для сортировки
		if (!in_array($sort_field, Domain_Premium_Action_PaymentRequest_GetMemberStatusList::ALLOW_SORT_FIELD_LIST)) {
			throw new Domain_Premium_Exception_IncorrectParam("incorrect param sort_field");
		}
	}
}
