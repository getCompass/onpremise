<?php

namespace Compass\Company;

/**
 * Класс действия для получения пользователей для запросов премиума
 */
class Domain_Premium_Action_PaymentRequest_GetMemberStatusList {

	public const LIMIT = 500; // лимит для получения пользователей

	public const SORT_ASC  = "asc";  // сортировка типа asc
	public const SORT_DESC = "desc"; // сортировка типа desc

	// доступные поля для сортировки при получении пользователей премиума
	public const ALLOW_SORT_FIELD_LIST = [
		"full_name",
		"company_joined_at",
		"premium_active_till",
	];

	// доступные типы для сортировки при получении пользователей премиума
	public const ALLOW_SORT_ORDER_LIST = [
		self::SORT_ASC,
		self::SORT_DESC,
	];

	/**
	 * получаем данные участников для Премиума в компании
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(string $sort_field, string $sort_order, int $count, int $offset):array {

		if (in_array($sort_field, ["full_name", "company_joined_at"])) {
			[$filtered_member_list, $premium_active_list, $deleted_user_id_list, $has_next] = self::_getStatusListSortAsMember($sort_field, $sort_order, $count, $offset);
		} else {
			[$filtered_member_list, $premium_active_list, $deleted_user_id_list, $has_next] = self::_getStatusListSortAsPremium($sort_order, $count, $offset);
		}

		$user_id_list = array_keys($filtered_member_list);

		// получаем данные по запросам на оплату от сотрудников
		$payment_request_list = Gateway_Db_CompanyData_PremiumPaymentRequestList::getList($user_id_list);

		// группируем запросы на оплату по user_id
		$payment_request_list_by_user_id = [];
		foreach ($payment_request_list as $payment_request) {
			$payment_request_list_by_user_id[$payment_request->requested_by_user_id] = $payment_request;
		}

		// приводим к формату
		$formatted_premium_member_status_list = Apiv2_Format::premiumMemberStatusList(
			$filtered_member_list, $premium_active_list, $payment_request_list_by_user_id, $deleted_user_id_list
		);

		return [$formatted_premium_member_status_list, $has_next];
	}

	/**
	 * получаем данные как для поля сортировки по участнику (имя, время вступления)
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _getStatusListSortAsMember(string $sort_field, string $sort_order, int $count, int $offset):array {

		// для получения правильной пагинации
		$temp_count = $count + 1;

		// получаем пользователей
		$member_list = Gateway_Db_CompanyData_MemberList::getListBySortField($sort_field, $sort_order, $temp_count, $offset);

		// получаем правильный has_next
		$has_next = count($member_list) > $count;

		// обрезаем список пользователей до актуального
		$member_list = array_slice($member_list, 0, $count);

		// фильтруем, убирая уволенных пользователей
		$filtered_member_list = [];
		$user_id_list         = [];
		foreach ($member_list as $member) {

			if (\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($member->role)) {
				continue;
			}

			if (!Type_User_Main::isHuman($member->npc_type)) {
				continue;
			}

			$filtered_member_list[$member->user_id] = $member;

			// собираем также список user_id пользователей
			$user_id_list[] = $member->user_id;
		}

		// отправляем запрос в php_pivot для получения времени премиума сотрудников
		[$premium_active_list, $deleted_user_id_list] = Gateway_Socket_Pivot::getPremiumActiveAtList($user_id_list);

		return [
			$filtered_member_list,
			$premium_active_list,
			$deleted_user_id_list,
			$has_next,
		];
	}

	/**
	 * получаем данные как для поля сортировки по премиуму
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _getStatusListSortAsPremium(string $sort_order, int $count, int $offset):array {

		// отправляем запрос в php_pivot для получения времени премиума сотрудников
		[$premium_active_list, $deleted_user_id_list] = Gateway_Socket_Pivot::getPremiumActiveTillListByOrder($sort_order);

		// получаем компанейскую информацию о полученных пользователях
		$user_id_list         = array_keys($premium_active_list);
		$user_id_list         = array_merge($user_id_list, $deleted_user_id_list);
		$filtered_member_list = Gateway_Bus_CompanyCache::getMemberList($user_id_list);

		// докидываем в список тех, у кого отсутствовал премиум
		foreach ($filtered_member_list as $member) {
			$premium_active_list[$member->user_id] = $premium_active_list[$member->user_id] ?? 0;
		}

		// сортируем по дате премиума и вступления в компанию
		$sorted_member_list = self::_sortByPremiumTillAndJoinedAt($filtered_member_list, $premium_active_list);

		// получаем правильный has_next
		$has_next = count($sorted_member_list) > $count + 1;

		// обрезаем список до актуального
		$sorted_member_list = array_slice($sorted_member_list, $offset, $count, true);

		return [
			$sorted_member_list,
			$premium_active_list,
			$deleted_user_id_list,
			$has_next,
		];
	}

	/**
	 * сортируем по дате премиума и вступления в компанию
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main[] $filtered_member_list
	 * @param array                                  $premium_active_list
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 */
	protected static function _sortByPremiumTillAndJoinedAt(array $filtered_member_list, array $premium_active_list):array {

		$premium_active_till_list = [];
		foreach ($premium_active_list as $user_id => $premium_active_till) {

			$member = $filtered_member_list[$user_id];

			// собираем пользователей и дату вступления, группируя по времени премиума
			$premium_active_till_list[$premium_active_till][$user_id] = $member->company_joined_at;
		}

		$sorted_member_list = [];
		foreach ($premium_active_till_list as $joined_at_by_user_id) {

			// сортируем по дате вступления
			arsort($joined_at_by_user_id);

			// получаем список пользователей
			foreach ($joined_at_by_user_id as $user_id => $_) {
				$sorted_member_list[$user_id] = $filtered_member_list[$user_id];
			}
		}

		return $sorted_member_list;
	}
}