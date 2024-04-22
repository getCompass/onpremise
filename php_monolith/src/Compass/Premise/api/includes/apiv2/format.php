<?php

namespace Compass\Premise;

use BaseFrame\Domain\User\Avatar;
use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Extra;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\User\Main;

/**
 * Класс для форматирования сущностей под формат API (для api/v2)
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго перед отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv2_Format {

	/**
	 * Приводим к формату данные о диалоге с пользователем
	 *
	 * @throws ParseFatalException
	 */
	public static function permissions(int $user_id, int $permissions):array {

		return [
			"premise_user_id"         => (int) $user_id,
			"premise_permission_list" => (array) Domain_User_Entity_Permissions::formatToOutput($permissions),
		];
	}

	/**
	 * форматируем список компаний пользователя
	 */
	public static function userCompanyList(array $company_list):array {

		$user_company_list = [];
		foreach ($company_list as $company) {
			$user_company_list[] = self::formatUserCompany($company);
		}

		return $user_company_list;
	}

	/**
	 * форматируем компании пользователя
	 *
	 * @param Struct_User_Company $company
	 *
	 * @return array
	 */
	public static function formatUserCompany(Struct_User_Company $company):array {

		return [
			"premise_space_id" => (int) $company->company_id,
			"name"             => (string) $company->name,
			"avatar_color_id"  => (int) $company->avatar_color_id,
			"creator_user_id"  => (int) $company->created_by_user_id,
			"member_count"     => (int) $company->member_count,
			"guest_count"      => (int) $company->guest_count,
			"avatar_file_key"  => (string) isEmptyString($company->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($company->avatar_file_map),
		];
	}

	/**
	 * получаем дополнительные данные для компании
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _getUserCompanyData(Struct_User_Company $company):array {

		return [];
	}

	/**
	 * Приводим к формату пользователей.
	 *
	 * @param Struct_Db_PivotUser_User[] $premise_user_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function premiseUserList(array $premise_user_list):array {

		$formatted_premise_user_list = [];

		foreach ($premise_user_list as $user) {

			$avatar_color_id = Type_User_Main::getAvatarColorId($user->extra);
			$avatar_color_id = $avatar_color_id === 0 ? Avatar::getColorByUserId($user->user_id) : $avatar_color_id;

			$formatted_user = [
				"user_id"              => (int) $user->user_id,
				"full_name"            => (string) $user->full_name,
				"full_name_updated_at" => (int) $user->full_name_updated_at,
				"type"                 => (string) Member::getUserOutputType((Main::getUserType($user->npc_type))),
				"extra"                => (object) [],
				"avatar"               => (object) [
					"color"    => (string) Avatar::getColorOutput($avatar_color_id),
					"file_key" => (string) ($user->avatar_file_map !== "" ? Type_Pack_File::doEncrypt($user->avatar_file_map) : ""),
				],
			];

			$formatted_premise_user_list[] = (object) $formatted_user;
		}

		return $formatted_premise_user_list;
	}
}
