<?php declare(strict_types = 1);

namespace Compass\Pivot;

use BaseFrame\Domain\User\Avatar;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер сокет методов для взаимодействия с
 * данными пользователя между pivot сервером и компаниями
 */
class Socket_User extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getInfoForVoip",
		"getUserCount",
		"getInfoForPartner",
		"getUserAvatarFileLinkList",
		"isAllowBatchPay",
		"getScreenTimeStat",
		"getUsersIntersectSpaces",
		"incConferenceMembershipRating",
		"validateSession",
	];

	/**
	 * Метод для получения данных пользователя для voip пуша
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws ParamException
	 * @throws cs_UserNotFound
	 */
	public function getInfoForVoip():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$user_info = Struct_User_Info::createStruct($user_info);

		return $this->ok([
			"full_name"       => (string) $user_info->full_name,
			"avatar_file_key" => (string) mb_strlen($user_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($user_info->avatar_file_map) : "",
			"avatar_color"    => (string) Avatar::getColorOutput($user_info->avatar_color_id),
		]);
	}

	/**
	 * Метод для возврата количества пользователей в компании
	 *
	 */
	public function getUserCount():array {

		$user_count = Gateway_Db_PivotUser_UserList::getUserCount();

		return $this->ok([
			"user_count" => (int) $user_count,
		]);
	}

	/**
	 * получаем данные пользователей для партнёрки
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getInfoForPartner():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		$partner_user_info_list = Domain_User_Scenario_Socket::getInfoForPartner($user_id_list);

		return $this->ok([
			"partner_user_info_list" => (array) $partner_user_info_list,
		]);
	}

	/**
	 * Получаем ссылку на аватар пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function getUserAvatarFileLinkList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		// получаем ссылку на все url аватара
		$avatar_link_list = Domain_User_Scenario_Socket::getUserAvatarFileLinkList($user_id_list);

		return $this->ok([
			"avatar_link_list" => (array) $avatar_link_list,
		]);
	}

	/**
	 * Проверяем, что пользователь может совершать оплату
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function isAllowBatchPay():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		if ($company_id < 1 || $user_id < 1) {
			throw new \ParamException("incorrect parameters");
		}

		try {
			$is_allow = Domain_User_Scenario_Socket::isAllowBatchPay($user_id, $company_id);
		} catch (cs_CompanyNotExist) {
			return $this->error(1408001, "not exist company");
		} catch (cs_CompanyIsHibernate) {
			return $this->error(1408002, "company is hibernated");
		}

		return $this->ok([
			"is_allow" => (bool) $is_allow,
		]);
	}

	/**
	 * Получаем статистику по экранному времени пользователя
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getScreenTimeStat():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$days_count = $this->post(\Formatter::TYPE_INT, "days_count");

		if ($user_id < 1 || $days_count > 60) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		$stat_list = Domain_User_Scenario_Socket::getScreenTimeStat($user_id, $days_count);

		return $this->ok([
			"stat_list" => (array) $stat_list,
		]);
	}

	/**
	 * Получаем список компаний в которых состоят как user_id_1, так и user_id_2
	 *
	 * @return array
	 */
	public function getUsersIntersectSpaces():array {

		$user_id_1 = $this->post(\Formatter::TYPE_INT, "user_id_1");
		$user_id_2 = $this->post(\Formatter::TYPE_INT, "user_id_2");

		$intersect_space_id_list = Domain_User_Scenario_Socket::getUsersIntersectSpaces($user_id_1, $user_id_2);

		return $this->ok([
			"intersect_space_id_list" => (array) $intersect_space_id_list,
		]);
	}

	/**
	 * Инкрементим статистику участия пользователя в конференции
	 *
	 * @return array
	 */
	public function incConferenceMembershipRating():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		Domain_User_Scenario_Socket::incConferenceMembershipRating($user_id, $space_id);

		return $this->ok();
	}

	/**
	 * метод для валидации pivot-сессии пользователя
	 *
	 * @return array
	 */
	public function validateSession():array {

		$pivot_session = $this->post(\Formatter::TYPE_STRING, "pivot_session");

		// проверяем, что сессия валидна
		try {
			$session_uniq = Domain_User_Scenario_Socket::validateSession($pivot_session);
		} catch (\cs_DecryptHasFailed|\cs_UnpackHasFailed) {
			throw new ParamException("incorrect pivot_session_key");
		}

		return $this->ok([
			"session_uniq" => (string) $session_uniq,
		]);
	}
}