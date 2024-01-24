<?php

namespace Compass\Pivot;

/**
 * класс содержит socket-методы для работы с партнерами
 *
 * Class Domain_partner_Scenario_Socket
 */
class Domain_Partner_Scenario_Socket {

	/**
	 * Получаем ссылку на аватар пользователя
	 *
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws \busException
	 */
	public static function getUserAvatarFileLink(array $user_id_list):array {

		// получаем информацию о пользователе
		$user_info_list = Gateway_Bus_PivotCache::getUserListInfo($user_id_list);

		$file_map_list_by_user_id = [];
		foreach ($user_info_list as $user_info) {

			if (mb_strlen($user_info->avatar_file_map) > 0) {
				$file_map_list_by_user_id[$user_info->user_id] = $user_info->avatar_file_map;
			}
		}

		if (count($file_map_list_by_user_id) < 1) {
			return [];
		}

		// получаем файлы
		$file_list = Gateway_Socket_PivotFileBalancer::getFileList(array_values($file_map_list_by_user_id));

		// собираем ответ
		$output = [];
		foreach ($file_list as $file_item) {

			$user_id = array_search($file_item["file_map"], $file_map_list_by_user_id);
			unset($file_map_list_by_user_id[$user_id]);

			// формат ответа: "user_id" -> "avatar url"
			$output[$user_id] = $file_item["url"];
		}

		return $output;
	}

	/**
	 * Получаем список компаний пользователя
	 */
	public static function getUserCompanyList(array $user_id_list):array {

		// достаем компании пользователя
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getList($user_id_list);

		$company_id_list = [];
		foreach ($user_company_list as $user_company_item) {
			$company_id_list[] = $user_company_item->company_id;
		}

		// инфа по компании
		$assoc_company_list = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list, true);

		// собираем ответ
		$output = [];
		foreach ($user_company_list as $user_company_item) {

			$company = $assoc_company_list[$user_company_item->company_id];

			// является ли создателем
			$is_owner = $company->created_by_user_id == $user_company_item->user_id;

			// дружим все данные
			$output[$user_company_item->user_id]   = $output[$user_company_item->user_id] ?? [];
			$output[$user_company_item->user_id][] = [
				"company_id"   => $company->company_id,
				"name"         => $company->name,
				"status"       => $company->status,
				"role"         => $is_owner ? "owner" : "staff",
				"member_count" => Domain_Company_Entity_Company::getMemberCount($company->extra),
				"created_at"   => $company->created_at,
			];
		}

		return $output;
	}

	/**
	 * Получаем данные о компании
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getCompanyInfo(int $company_id):array {

		$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

		return [
			"company_id"  => $company_row->company_id,
			"url"         => $company_row->url,
			"domino_id"   => $company_row->domino_id,
			"private_key" => Domain_Company_Entity_Company::getPrivateKey($company_row->extra),
		];
	}

	/**
	 * Загружаем счет
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function uploadInvoice(string $file_path, string $mime_type, string $posted_filename):string {

		// загружаем файл
		$url = Gateway_Socket_PivotFileBalancer::getNodeForUpload(FILE_SOURCE_MESSAGE_DOCUMENT);
		return Gateway_Socket_FileNode::uploadInvoice($url, $file_path, $mime_type, $posted_filename, FILE_SOURCE_MESSAGE_DOCUMENT);
	}

	/**
	 * Был создан счет на оплату
	 */
	public static function onInvoiceCreated(int $company_id, int $created_by_user_id):void {

		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);
		Domain_User_Entity_Validator::assertValidUserId($created_by_user_id);

		$company     = Domain_Company_Entity_Company::get($company_id);
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		Gateway_Socket_Company::onInvoiceCreated($created_by_user_id, $company_id, $company->domino_id, $private_key);
	}

	/**
	 * Был оплачен счет
	 */
	public static function onInvoicePayed(int $company_id):void {

		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		$company     = Domain_Company_Entity_Company::get($company_id);
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		Gateway_Socket_Company::onInvoicePayed($company_id, $company->domino_id, $private_key);
	}

	/**
	 * Счет был отменен
	 */
	public static function onInvoiceCanceled(int $company_id, int $invoice_id):void {

		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		$company     = Domain_Company_Entity_Company::get($company_id);
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		Gateway_Socket_Company::onInvoiceCanceled($company_id, $invoice_id, $company->domino_id, $private_key);
	}

	/**
	 * Получаем файлы
	 */
	public static function getFileByKeyList(array $file_key_list):array {

		if (count($file_key_list) < 1) {
			throw new \paramException("empty file_key_list");
		}

		return Gateway_Socket_PivotFileBalancer::getFileByKeyList($file_key_list);
	}

	/**
	 * Получаем информацию о списке пользователей
	 *
	 * @param array $user_id_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException|cs_UserNotFound
	 * @long
	 */
	public static function getUserInfoList(array $user_id_list):array {

		$user_list = Gateway_Bus_PivotCache::getUserListInfo($user_id_list);

		// делаем запрос по файлам аватаров
		$user_list_with_avatar = [];
		foreach ($user_list as $user) {

			if (mb_strlen($user->avatar_file_map) > 0) {
				$user_list_with_avatar[] = $user;
			}
		}

		// если есть аватары
		$file_list = [];
		if (count($user_list_with_avatar) > 0) {

			$avatar_file_map_list = array_column($user_list_with_avatar, "avatar_file_map");
			$file_list            = Gateway_Socket_PivotFileBalancer::getFileList($avatar_file_map_list);
		}

		// собираем информацию по пользователям для ответа
		$user_info_list = [];
		foreach ($user_list as $user) {

			$avatar_url = "";
			if (mb_strlen($user->avatar_file_map) > 0) {

				foreach ($file_list as $file) {

					if ($user->avatar_file_map == $file["file_map"]) {
						$avatar_url = $file["url"];
					}
				}
			}

			$user_info_list[] = (object) Socket_Format::userInfo($user, $avatar_url);
		}

		return $user_info_list;
	}
}