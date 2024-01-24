<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс для работы со спринтами сотрудника
 */
class Apiv1_EmployeeCard_Sprint extends \BaseFrame\Controller\Api {

	/** @var int лимит для получения списка спринтов */
	protected const _MAX_GET_SPRINT_LIST = 50;

	/**
	 * поддерживаемые методы. регистр не имеет значение
	 */
	public const ALLOW_METHODS = [
		"add",
		"getList",
		"edit",
		"remove",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"add",
		"edit",
		"remove",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	// -------------------------------------------------------
	// ОБЩИЕ МЕТОДЫ
	// -------------------------------------------------------

	/**
	 * добавляем спринт пользователю
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function add():array {

		$user_id       = $this->post(\Formatter::TYPE_INT, "user_id");
		$header        = $this->post(\Formatter::TYPE_STRING, "header");
		$description   = $this->post(\Formatter::TYPE_STRING, "description");
		$end_at_string = $this->post(\Formatter::TYPE_STRING, "end_at_string");
		$is_success    = $this->post(\Formatter::TYPE_INT, "is_success");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SPRINT_ADD);

		// проверяем параметры на корректность
		self::_throwIfIncorrectText($header, $description);
		self::_throwIfIncorrectEndAtString($end_at_string);
		self::_throwIfIncorrectSuccess($is_success);

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// парсим эмоджи
		$header      = Type_Api_Filter::replaceEmojiWithShortName($header);
		$description = Type_Api_Filter::replaceEmojiWithShortName($description);

		// фильтруем заголовок, описание
		try {

			$header      = Type_Api_Filter::prepareText($header, Type_User_Card_Sprint::MAX_SPRINT_HEADER_LENGTH);
			$description = Type_Api_Filter::prepareText($description, Type_User_Card_Sprint::MAX_SPRINT_TEXT_LENGTH);
		} catch (cs_Text_IsTooLong) {
			return $this->error(540, "text is too long");
		}

		// проверяем очищенный текст - возможно он стал пустым
		if (strlen($header) == 0 || strlen($description) == 0) {
			return $this->error(937, "text after filter empty");
		}

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// добавляем спринт
		return $this->_addSprint($user_id, $end_at_string, $is_success, $header, $description);
	}

	/**
	 * добавляем спринт
	 *
	 * @param int    $user_id
	 * @param string $end_at_string
	 * @param int    $is_success
	 * @param string $header
	 * @param string $description
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected function _addSprint(int $user_id, string $end_at_string, int $is_success, string $header, string $description):array {

		// добавляем запись о спринте
		$end_at = strtotime($end_at_string);
		$sprint = Type_User_Card_Sprint::add($user_id, $this->user_id, $is_success, $end_at, $header, $description);

		// парсим ссылки с тексте
		Gateway_Socket_Conversation::getLinkListFromText(
			$description,
			$user_id,
			$this->user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_SPRINT,
			$sprint->sprint_id);

		// инкрементим значение спринтов
		$card_dynamic_obj = Type_User_Card_Sprint::incInDynamicData($user_id, true, $is_success == 1);

		return $this->ok([
			"sprint"          => (object) Apiv1_Format::sprint($sprint),
			"total_count"     => (int) Type_User_Card_DynamicData::getSprintTotalCount($card_dynamic_obj->data),
			"success_count"   => (int) Type_User_Card_DynamicData::getSprintSuccessCount($card_dynamic_obj->data),
			"success_percent" => (int) Type_User_Card_DynamicData::getSprintSuccessPercent($card_dynamic_obj->data),
		]);
	}

	/**
	 * получаем список спринтов
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getList():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", self::_MAX_GET_SPRINT_LIST);

		// проверяем параметры на корректность
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if ($offset < 0 || $limit < 0) {
			throw new ParamException("incorrect param");
		}

		// если запрашивают 0 записей
		if ($limit == 0) {

			return $this->ok([
				"sprint_list"     => (array) [],
				"has_next"        => (int) 0,
				"total_count"     => (int) 0,
				"success_count"   => (int) 0,
				"success_percent" => (int) 0,
			]);
		}

		// получаем количество спринтов выбранного пользователя (сколько всего было спринтов, сколько из них выполнено)
		$count_list = Type_User_Card_Sprint::getCountList($user_id);

		// ограничиваем указанное возвращаемое число до максимального
		$limit = limit($limit, 0, self::_MAX_GET_SPRINT_LIST);

		// получаем записи о спринтах пользователя
		$sprint_list = Type_User_Card_Sprint::getList($user_id, $offset, $limit);

		$formatted_sprint_list = [];
		$user_id_list          = [];
		foreach ($sprint_list as $v) {

			$user_id_list[]          = $v->creator_user_id;
			$formatted_sprint_list[] = Apiv1_Format::sprint($v);
		}

		$this->action->users($user_id_list);

		return $this->ok([
			"sprint_list"     => (array) $formatted_sprint_list,
			"has_next"        => (int) count($sprint_list) == $limit ? 1 : 0,
			"total_count"     => (int) $count_list["all_count"],
			"success_count"   => (int) $count_list["success_count"],
			"success_percent" => (int) $count_list["all_count"] > 0 ? floor(100 / $count_list["all_count"] * $count_list["success_count"]) : 0,
		]);
	}

	/**
	 * редактируем спринт пользователя
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public function edit():array {

		$user_id           = $this->post(\Formatter::TYPE_INT, "user_id");
		$sprint_id         = $this->post(\Formatter::TYPE_INT, "sprint_id");
		$new_header        = $this->post(\Formatter::TYPE_STRING, "header");
		$new_description   = $this->post(\Formatter::TYPE_STRING, "description");
		$new_end_at_string = $this->post(\Formatter::TYPE_STRING, "end_at_string");
		$is_new_success    = $this->post(\Formatter::TYPE_INT, "is_success");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SPRINT_EDIT);

		// выполняем проверки
		self::_throwIfIncorrectSprintId($sprint_id);
		self::_throwIfIncorrectText($new_header, $new_description);
		self::_throwIfIncorrectEndAtString($new_end_at_string);
		self::_throwIfIncorrectSuccess($is_new_success);

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// парсим эмоджи
		$new_header      = Type_Api_Filter::replaceEmojiWithShortName($new_header);
		$new_description = Type_Api_Filter::replaceEmojiWithShortName($new_description);

		// подготавливаем текстовые поля
		try {

			$new_header      = Type_Api_Filter::prepareText($new_header, Type_User_Card_Sprint::MAX_SPRINT_HEADER_LENGTH);
			$new_description = Type_Api_Filter::prepareText($new_description, Type_User_Card_Sprint::MAX_SPRINT_TEXT_LENGTH);
		} catch (cs_Text_IsTooLong) {
			return $this->error(540, "text is too long");
		}

		// проверяем очищенный текст - возможно он стал пустым
		if (strlen($new_header) == 0 || strlen($new_description) == 0) {
			return $this->error(937, "text after filter empty");
		}

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// достаем запись спринта из базы
		$sprint = Type_User_Card_Sprint::get($user_id, $sprint_id);

		// если записи спринта не нашлось или удалена, то выдаем ошибку
		if (is_null($sprint->sprint_id) || $sprint->is_deleted == 1) {
			return $this->error(931, "not found sprint of user");
		}

		// если наш пользователь НЕ создатель этого спринта, то выдаём ошибку
		if ($sprint->creator_user_id != $this->user_id) {
			return $this->error(932, "user is not creator of this sprint");
		}

		// редактируем спринт
		$new_end_at = strtotime($new_end_at_string);
		return $this->_editSprint($user_id, $sprint, $new_end_at, $new_header, $new_description, $is_new_success);
	}

	/**
	 * редактируем спринт
	 *
	 * @param int                           $user_id
	 * @param Struct_Domain_Usercard_Sprint $sprint
	 * @param int                           $new_end_at
	 * @param string                        $new_header
	 * @param string                        $new_description
	 * @param int                           $is_new_success
	 *
	 * @return array
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected function _editSprint(int $user_id, Struct_Domain_Usercard_Sprint $sprint, int $new_end_at, string $new_header, string $new_description, int $is_new_success):array {

		// чистим link_list по необходимости
		$sprint = $this->_clearLinkListIfNeeded($sprint, $user_id, $new_description);

		// редактируем спринт пользователя
		Type_User_Card_Sprint::edit($user_id, $sprint->sprint_id, $new_end_at, $new_header, $new_description, $is_new_success, $sprint->data);

		// инкремент/декремент количество спринтов при редактировании
		$card_dynamic_obj = $this->_incOrDecSprintCountOnEdit($user_id, $sprint, $is_new_success);

		// актуализируем отредактированный спринт
		$sprint->header_text      = $new_header;
		$sprint->description_text = $new_description;
		$sprint->end_at           = $new_end_at;
		$sprint->is_success       = $is_new_success;

		return $this->ok([
			"sprint"          => (object) Apiv1_Format::sprint($sprint),
			"total_count"     => (int) Type_User_Card_DynamicData::getSprintTotalCount($card_dynamic_obj->data),
			"success_count"   => (int) Type_User_Card_DynamicData::getSprintSuccessCount($card_dynamic_obj->data),
			"success_percent" => (int) Type_User_Card_DynamicData::getSprintSuccessPercent($card_dynamic_obj->data),
		]);
	}

	/**
	 * инкремент/декремент количество спринтов при редактировании
	 *
	 * @param int                           $user_id
	 * @param Struct_Domain_Usercard_Sprint $sprint
	 * @param int                           $is_new_success
	 *
	 * @return Struct_Domain_Usercard_Dynamic
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected function _incOrDecSprintCountOnEdit(int $user_id, Struct_Domain_Usercard_Sprint $sprint, int $is_new_success):Struct_Domain_Usercard_Dynamic {

		// если успешность спринта НЕ изменилась, то просто возвращаем dynamic-данные
		if ($is_new_success == $sprint->is_success) {

			return Type_User_Card_DynamicData::get($user_id);
		}

		// если спринт изменил флаг на успешный, то инкрементим количество успешных спринтов
		if ($is_new_success == 1) {
			return Type_User_Card_Sprint::incInDynamicData($user_id, false, true);
		}

		// иначе декрементим количество успешных спринтов
		return Type_User_Card_Sprint::decInDynamicData($user_id, false, true);
	}

	/**
	 * добавляем список ссылок если он есть
	 *
	 * @param Struct_Domain_Usercard_Sprint $sprint
	 * @param int                           $user_id
	 * @param string                        $new_description
	 *
	 * @return Struct_Domain_Usercard_Sprint
	 * @throws \returnException
	 */
	protected function _clearLinkListIfNeeded(Struct_Domain_Usercard_Sprint $sprint, int $user_id, string $new_description):Struct_Domain_Usercard_Sprint {

		// парсим ссылки с тексте
		$link_list = Gateway_Socket_Conversation::getLinkListFromText($new_description, $user_id, $this->user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_SPRINT, $sprint->sprint_id);

		if (count($link_list) < 1) {
			$sprint->data = Type_User_Card_Sprint::setLinkList($sprint->data, []);
		}

		return $sprint;
	}

	/**
	 * удаляем спринт пользователя
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function remove():array {

		$user_id   = $this->post(\Formatter::TYPE_INT, "user_id");
		$sprint_id = $this->post(\Formatter::TYPE_INT, "sprint_id");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SPRINT_REMOVE);

		// проверяем параметры на корректность
		self::_throwIfIncorrectSprintId($sprint_id);

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// достаем спринт из базы
		$sprint = Type_User_Card_Sprint::get($user_id, $sprint_id);

		// если запись спринта не нашлась или уже удалена, то возвращаем просто ok
		if (is_null($sprint->sprint_id) || $sprint->is_deleted == 1) {

			// получаем dynamic данные спринтов пользователя
			$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);

			return $this->ok([
				"total_count"     => (int) Type_User_Card_DynamicData::getSprintTotalCount($card_dynamic_obj->data),
				"success_count"   => (int) Type_User_Card_DynamicData::getSprintSuccessCount($card_dynamic_obj->data),
				"success_percent" => (int) Type_User_Card_DynamicData::getSprintSuccessPercent($card_dynamic_obj->data),
			]);
		}

		// помечаем спринт удаленным
		Type_User_Card_Sprint::delete($user_id, $sprint_id);

		// декрементим значение спринтов
		$card_dynamic_obj = Type_User_Card_Sprint::decInDynamicData($user_id, true, $sprint->is_success == 1);

		return $this->ok([
			"total_count"     => (int) Type_User_Card_DynamicData::getSprintTotalCount($card_dynamic_obj->data),
			"success_count"   => (int) Type_User_Card_DynamicData::getSprintSuccessCount($card_dynamic_obj->data),
			"success_percent" => (int) Type_User_Card_DynamicData::getSprintSuccessPercent($card_dynamic_obj->data),
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * проверяем на корректность переданный end_at_string
	 *
	 * @param string $end_at_string
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectEndAtString(string $end_at_string):void {

		if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $end_at_string, $matches)) {

			$year  = $matches[1];
			$month = $matches[2];
			$day   = $matches[3];

			if (!checkdate($month, $day, $year)) {
				throw new ParamException("incorrect end_at_string");
			}

			return;
		}

		throw new ParamException("incorrect end_at_string");
	}

	/**
	 * проверяем на корректность переданный is_success параметр
	 *
	 * @param int $is_success
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectSuccess(int $is_success):void {

		if ($is_success !== 0 && $is_success !== 1) {
			throw new ParamException("incorrect is_success");
		}
	}

	/**
	 * проверяем на корректность переданный sprint_id
	 *
	 * @param int $sprint_id
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectSprintId(int $sprint_id):void {

		if ($sprint_id < 1) {
			throw new ParamException("incorrect param sprint_id");
		}
	}

	/**
	 * проверяем на корректность переданный text
	 *
	 * @param string $header
	 * @param string $description
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectText(string $header, string $description):void {

		if (mb_strlen($header) < 1) {
			throw new ParamException("incorrect param header");
		}

		if (mb_strlen($description) < 1) {
			throw new ParamException("incorrect param description");
		}
	}

	/**
	 * получить информацию о пользователе, но в случае некорректных данных — возвращать экзепшн
	 *
	 * @param int $user_id
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _tryGetUserInfoAndThrowIfIncorrectUserId(int $user_id):\CompassApp\Domain\Member\Struct\Main {

		if ($user_id < 1) {
			throw new ParamException("incorrect param user_id");
		}

		// получаем информацию о пользователе
		try {
			$user_info = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("dont found user in company cache");
		}

		// если это бот
		if (Type_User_Main::isBot($user_info->npc_type)) {
			throw new ParamException("you can't do this action on bot-user");
		}

		return $user_info;
	}
}