<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс для работы с респектами для сотрудника
 */
class Apiv1_EmployeeCard_Respect extends \BaseFrame\Controller\Api {

	protected const _MAX_GET_RESPECT_LIST = 50;

	/**
	 * поддерживаемые методы. регистр не имеет значение
	 */
	public const ALLOW_METHODS = [
		"add",
		"getList",
		"edit",
		"remove",
		"getGaveMonthList",
		"getGaveRespectList",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"add",
		"edit",
		"remove",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => [
			"getList",
			"edit",
			"remove",
			"getGaveMonthList",
			"getGaveRespectList",
		],
	];

	// -------------------------------------------------------
	// ОБЩИЕ МЕТОДЫ
	// -------------------------------------------------------

	/**
	 * добавляем новый респект сотруднику
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public function add():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$text    = $this->post(\Formatter::TYPE_STRING, "text", false);

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::RESPECT_ADD);

		// проверяем параметры на корректность
		self::_throwIfIncorrectCommentText($text);
		self::_throwIfUserCreateHimself($user_id, $this->user_id);

		// получаем инфу по пользователю, выбрасываем ParamException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$receiver_user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($receiver_user_info->role)) {
			return $this->error(532, "this user left company");
		}

		// если пользователь удалил аккаунт
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($receiver_user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// проверяем роли отправителя/получателя спасибо
		$this->_assertSenderReceiverRole($receiver_user_info);

		if ($text !== false) {

			// парсим эмоджи
			$text = Type_Api_Filter::replaceEmojiWithShortName($text);

			// фильтруем текст
			try {
				$text = Type_Api_Filter::prepareText($text, Type_User_Card_Respect::MAX_RESPECT_COMMENT_LENGTH);
			} catch (cs_Text_IsTooLong) {
				return $this->error(540, "text is too long");
			}

			// проверяем очищенный текст - возможно он стал пустым
			if (mb_strlen($text) < 1) {
				return $this->error(937, "text after filter empty");
			}
		}

		// добавляем респект
		try {
			$respect = Domain_EmployeeCard_Action_Respect_Add::do($this->user_id, $user_id, $text === false ? "" : $text);
		} catch (Domain_EmployeeCard_Exception_Respect_NotConversationMember) {
			return $this->error(2106005);
		}

		// достаем количество респектов пользователя из dynamic-данных пользователя получателя
		$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);
		$respect_count    = Type_User_Card_DynamicData::getRespectCount($card_dynamic_obj->data);

		// по умолчанию диалог, куда добавили респект — пуст
		$conversation_map = "";
		$message_map      = Type_User_Card_Respect::getMessageMap($respect->data);
		if (mb_strlen($message_map) != 0) {
			$conversation_map = Type_Pack_Message_Conversation::getConversationMap($message_map);
		}

		return $this->ok([
			"respect"          => (object) Apiv1_Format::respect($respect),
			"respect_count"    => (int) $respect_count,
			"conversation_map" => (string) $conversation_map,
		]);
	}

	/**
	 * Проверяем роли отправителя/получателя спасибо
	 *
	 * @throws \BaseFrame\Exception\Request\CaseException
	 */
	protected function _assertSenderReceiverRole(\CompassApp\Domain\Member\Struct\Main $receiver_user_info):void {

		// получатель спасибо – гость
		$is_receiver_guest = Member::ROLE_GUEST === $receiver_user_info->role;

		// если текущий пользователь гость
		if ($this->role === Member::ROLE_GUEST) {

			// если гость пытается выдать респект гостю
			if ($is_receiver_guest) {
				throw new \BaseFrame\Exception\Request\CaseException(2106008, "Sender and receiver have guest access. Action is not allowed");
			}

			// иначе такая ошибка
			throw new \BaseFrame\Exception\Request\CaseException(2106009, "User have guest access. Action is not allowed");
		}

		// текущий пользователь НЕ гость, но получатель гость
		if ($is_receiver_guest) {
			throw new \BaseFrame\Exception\Request\CaseException(2106010, "User have guest access. Action is not allowed");
		}
	}

	/**
	 * бросаем исключение если пользователь хочет создать запись самому себе
	 *
	 * @param int $user_id
	 * @param int $creator_user_id
	 *
	 * @throws paramException
	 */
	protected static function _throwIfUserCreateHimself(int $user_id, int $creator_user_id):void {

		if ($user_id == $creator_user_id) {
			throw new ParamException("user create himself");
		}
	}

	/**
	 * получить список респектов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getList():array {

		$user_id         = $this->post(\Formatter::TYPE_INT, "user_id");
		$last_respect_id = $this->post(\Formatter::TYPE_INT, "last_respect_id", 0);
		$limit           = $this->post(\Formatter::TYPE_INT, "limit", self::_MAX_GET_RESPECT_LIST);

		// проверяем параметры на корректность
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if ($last_respect_id < 0 || $limit < 0) {
			throw new ParamException("incorrect param");
		}

		// если запрашивают 0 записей
		if ($limit == 0) {

			return $this->ok([
				"respect_list"  => (array) [],
				"has_next"      => (int) 0,
				"respect_count" => (int) 0,
			]);
		}

		// ограничиваем указанное возвращаемое число до максимального
		$limit = limit($limit, 0, self::_MAX_GET_RESPECT_LIST);

		// получаем список респектов
		$respect_list = Type_User_Card_Respect::getList($user_id, $last_respect_id, $limit + 1);

		// формируем ответ
		$formatted_respect_list = [];
		$user_id_list           = [];

		$has_next = 0;

		// если записей элементов больше чем лимит
		if (count($respect_list) > $limit) {

			$has_next = 1;
			array_pop($respect_list);
		}

		foreach ($respect_list as $v) {

			$user_id_list[]           = $v->creator_user_id;
			$formatted_respect_list[] = Apiv1_Format::respect($v);
		}

		// добавляем создателей полученных респектов в action users
		$this->action->users($user_id_list);

		// получаем dynamic данные спринтов пользователя
		$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);
		$respect_count    = Type_User_Card_DynamicData::getRespectCount($card_dynamic_obj->data);

		return $this->ok([
			"respect_list"  => (array) $formatted_respect_list,
			"has_next"      => (int) $has_next,
			"respect_count" => (int) $respect_count,
		]);
	}

	/**
	 * редактируем респект пользователю
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function edit():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$respect_id = $this->post(\Formatter::TYPE_INT, "respect_id");
		$new_text   = $this->post(\Formatter::TYPE_STRING, "text", false);

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::RESPECT_EDIT);

		// проверяем параметры на корректность
		self::_throwIfIncorrectRespectId($respect_id);
		self::_throwIfIncorrectCommentText($new_text);

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}

		// если пользователь удалил аккаунт
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		if ($new_text !== false) {

			// парсим эмоджи
			$new_text = Type_Api_Filter::replaceEmojiWithShortName($new_text);

			// фильтруем текст
			try {
				$new_text = Type_Api_Filter::prepareText($new_text, Type_User_Card_Respect::MAX_RESPECT_COMMENT_LENGTH);
			} catch (cs_Text_IsTooLong) {
				return $this->error(540, "text is too long");
			}

			// проверяем очищенный текст - возможно он стал пустым
			if (strlen($new_text) == 0) {
				return $this->error(937, "text after filter empty");
			}
		}

		// достаем респект из базы
		$respect = Type_User_Card_Respect::get($user_id, $respect_id);

		// если запись респекта удалена, то выдаем ошибку
		if ($respect->is_deleted == 1) {
			return $this->error(931, "not found respect of user");
		}

		// если пользователь не создатель респекта
		if ($respect->creator_user_id != $this->user_id) {
			return $this->error(932, "user is not creator user");
		}

		// получаем список редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// отдаем ошибку если время на удаление истекло и создатель респекта не является редактором пользователя
		if (!Type_User_Card_Respect::isTimeAllowToEdit($respect) &&
			!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(933, "respect edited timed out");
		}

		// редактируем респект
		return $this->_editRespect($respect, $user_id, $new_text === false ? "" : $new_text);
	}

	/**
	 * редактируем респект
	 *
	 * @param Struct_Domain_Usercard_Respect $respect
	 * @param int                            $user_id
	 * @param string                         $new_text
	 *
	 * @return array
	 * @throws \parseException|\returnException
	 */
	protected function _editRespect(Struct_Domain_Usercard_Respect $respect, int $user_id, string $new_text):array {

		// если за респектом закреплено сообщение, то редактируем текст также и для сообщения в группе Респекты
		$message_map = Type_User_Card_Respect::getMessageMap($respect->data);
		if (mb_strlen($message_map) > 0) {

			try {
				Gateway_Socket_Conversation::editRespectText($this->user_id, $message_map, $new_text);
			} catch (Domain_EmployeeCard_Exception_Respect_NotConversationMember) {
				return $this->error(2106005);
			} catch (\Exception|\Error) {
				// ничего не выдаем
			}
		}

		// парсим ссылки с тексте
		$link_list = Gateway_Socket_Conversation::getLinkListFromText($new_text, $user_id, $this->user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_RESPECT, $respect->respect_id);

		if (count($link_list) < 1) {
			$respect->data = Type_User_Card_Respect::setLinkList($respect->data, []);
		}

		// редактируем респект пользователя
		Type_User_Card_Respect::edit($user_id, $respect->respect_id, $new_text, $respect->data);

		// актуализируем отредактированный респект
		$respect->respect_text = $new_text;

		// получаем dynamic данные пользователя
		$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);
		$respect_count    = Type_User_Card_DynamicData::getRespectCount($card_dynamic_obj->data);

		return $this->ok([
			"respect"       => (object) Apiv1_Format::respect($respect),
			"respect_count" => (int) $respect_count,
		]);
	}

	/**
	 * удаляем респект пользователя
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function remove():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$respect_id = $this->post(\Formatter::TYPE_INT, "respect_id");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::RESPECT_REMOVE);

		// проверяем параметры на корректность
		self::_throwIfIncorrectRespectId($respect_id);

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// достаем респект из базы
		$respect = Type_User_Card_Respect::get($user_id, $respect_id);

		// если запись респекта удалена, то возвращаем просто ok
		if ($respect->is_deleted == 1) {

			$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);
			$respect_count    = Type_User_Card_DynamicData::getRespectCount($card_dynamic_obj->data);

			return $this->ok([
				"respect_count" => (int) $respect_count,
			]);
		}

		// получаем список редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// если пользователь не создатель респекта и не редактор
		if ($respect->creator_user_id != $this->user_id &&
			!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(932, "user is not creator or not administration or editor");
		}

		// отдаем ошибку если время на удаление истекло и создатель респекта не является редактором пользователя
		if (!Type_User_Card_Respect::isTimeAllowToDelete($respect) &&
			!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(934, "respect deleted timed out");
		}

		return $this->_tryRemove($respect, $user_id);
	}

	/**
	 * пробуем удалить респект
	 *
	 * @param Struct_Domain_Usercard_Respect $respect
	 * @param int                            $receiver_user_id
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	protected function _tryRemove(Struct_Domain_Usercard_Respect $respect, int $receiver_user_id):array {

		// если за респектом закреплено сообщение, то удаляем также и сообщение в группе Респекты
		$message_map = Type_User_Card_Respect::getMessageMap($respect->data);
		if (mb_strlen($message_map) > 0) {

			// получаем ключ группы Респекты
			$conversation_config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME);
			$conversation_map    = $conversation_config["value"];

			// пробуем удалить сообщение
			try {
				Gateway_Socket_Conversation::tryDeleteMessageList([$message_map], $conversation_map, $this->user_id, true);
			} catch (\cs_UserIsNotMember) {
				return $this->error(2106005);
			} catch (cs_Action_TimeIsOver) {
				return $this->error(934, "respect deleted timed out");
			} catch (\Exception|\Error) {
				// ничего не выдаем
			}
		}

		// получаем респект и проверям неудален ли он уже
		$respect = Type_User_Card_Respect::get($receiver_user_id, $respect->respect_id);

		// если респект не был еще удален
		if ($respect->is_deleted != 1) {

			// помечаем респект удаленным
			Type_User_Card_Respect::delete($receiver_user_id, $respect->respect_id);

			// декрементим количество набранных за месяц
			$month_start_at = monthStart($respect->created_at);
			Type_User_Card_MonthPlan::decUserValue($respect->creator_user_id, Type_User_Card_MonthPlan::MONTH_PLAN_RESPECT_TYPE, $month_start_at);

			// декрементим значение полученных респектов
			Type_User_Card_Respect::decInDynamicData($receiver_user_id);

			// декрементим рейтинг для того кто выдал респект
			Gateway_Bus_Company_Rating::decAfterDelete(Domain_Rating_Entity_Rating::RESPECT, $respect->creator_user_id, $respect->created_at);
		}

		// получаем dynamic данные полученных респектов пользователя
		$card_dynamic_obj = Type_User_Card_DynamicData::get($receiver_user_id);
		$respect_count    = Type_User_Card_DynamicData::getRespectCount($card_dynamic_obj->data);

		return $this->ok([
			"respect_count" => (int) $respect_count,
		]);
	}

	/**
	 * получаем список месяцев выданных респектов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getGaveMonthList():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$year    = $this->post(\Formatter::TYPE_INT, "year", date("Y"));

		// проверяем параметры на корректность
		if ($user_id < 1 || $year < 1) {
			throw new ParamException("incorrect params");
		}

		// проверяем что пользователь существует
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// получаем данные за год
		[$month_plan_list, $months_count] = Domain_EmployeeCard_Scenario_Api::getGaveMonthList(
			$user_id, $year, Type_User_Card_MonthPlan::MONTH_PLAN_RESPECT_TYPE
		);

		// собираем ответ для клиентов
		$output = [];
		foreach ($month_plan_list as $plan_obj) {
			$output[] = (object) Apiv1_Format::monthPlanDataItem($plan_obj);
		}

		// если количество месяцев за выбранный год больше чем месяцев после фильтрации,
		// значит имеются данные за следующий год
		$has_next = $months_count > count($month_plan_list);

		return $this->ok([
			"gave_month_data_list" => (array) $output,
			"has_next"             => (int) $has_next,
		]);
	}

	/**
	 * получаем список выданных респектов
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getGaveRespectList():array {

		$creator_user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$row_id          = $this->post(\Formatter::TYPE_INT, "row_id");
		$limit           = $this->post(\Formatter::TYPE_INT, "limit", self::_MAX_GET_RESPECT_LIST);
		$offset          = $this->post(\Formatter::TYPE_INT, "offset", 0);

		// проверяем параметры на корректность
		if ($creator_user_id < 1 || $row_id < 1 || $limit < 1 || $offset < 0) {
			throw new ParamException("incorrect params");
		}

		// проверяем что пользователь существует
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($creator_user_id);

		// получаем запись плана на месяц
		try {
			$month_plan_obj = Type_User_Card_MonthPlan::get($row_id);
		} catch (\cs_RowIsEmpty) {

			return $this->ok([
				"gave_respect_list" => (array) [],
				"has_next"          => (int) 0,
			]);
		}

		// достаем респекты, выданные за этот месяц
		$respect_list = Type_User_Card_Respect::getListByMonthAt($creator_user_id, $month_plan_obj->created_at, $limit + 1, $offset);

		// если записей элементов больше чем лимит
		$has_next = 0;
		if (count($respect_list) > $limit) {

			$has_next = 1;
			array_pop($respect_list);
		}

		// приводим к формату под клиентов
		$output    = [];
		$user_list = [];
		foreach ($respect_list as $v) {

			$output[]    = (object) Apiv1_Format::respect($v);
			$user_list[] = $v->user_id;
		}

		// добавляем пользователей в action users
		$this->action->users($user_list);

		return $this->ok([
			"gave_respect_list" => (array) $output,
			"has_next"          => (int) $has_next,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * проверяем на корректность переданный respect_id
	 *
	 * @param int $respect_id
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectRespectId(int $respect_id):void {

		if ($respect_id < 1) {
			throw new ParamException("incorrect param respect_id");
		}
	}

	/**
	 * проверяем на корректность переданный comment
	 *
	 * @param string|false $comment_text
	 *
	 * @throws ParamException
	 */
	protected static function _throwIfIncorrectCommentText(string|false $comment_text):void {

		if ($comment_text !== false && mb_strlen($comment_text) < 1) {
			throw new ParamException("incorrect param comment_text");
		}
	}

	/**
	 * получить информацию о пользователе, но в случае некорректных данных — возвращать экзепшн
	 *
	 * @param int $user_id
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \apiAccessException
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