<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс для работы с достижениями сотрудника
 */
class Apiv1_EmployeeCard_Achievement extends \BaseFrame\Controller\Api {

	protected const _MAX_GET_ACHIEVEMENT_LIST = 50;

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
	 * добавление достижения пользователю
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function add():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$header  = $this->post(\Formatter::TYPE_STRING, "header");
		$comment = $this->post(\Formatter::TYPE_STRING, "comment");

		// проверяем параметры на корректность
		if ($user_id < 1 || mb_strlen($header) < 1 || mb_strlen($comment) < 1) {
			throw new ParamException("get incorrect params");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::ACHIEVEMENT_ADD);

		// получаем инфу по пользователю, бросаем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован/удалил аккаунт, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// парсим эмоджи заголовка и текста достижения
		$header  = Type_Api_Filter::replaceEmojiWithShortName($header);
		$comment = Type_Api_Filter::replaceEmojiWithShortName($comment);

		// фильтруем заголовок и текст достижения
		try {

			$header  = Type_Api_Filter::prepareText($header, Type_User_Card_Achievement::MAX_ACHIEVEMENT_HEADER_LENGTH);
			$comment = Type_Api_Filter::prepareText($comment, Type_User_Card_Achievement::MAX_ACHIEVEMENT_COMMENT_LENGTH);
		} catch (cs_Text_IsTooLong) {
			return $this->error(540, "text is too long");
		}

		// проверяем очищенный текст - возможно он стал пустым
		if (strlen($header) == 0 || strlen($comment) == 0) {
			return $this->error(937, "text after filter empty");
		}

		// получаем редакторов карточки пользователя
		$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editor_id_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// добавляем достижение
		[$achievement, $achievement_count] = Domain_EmployeeCard_Action_Achievement_Add::do(
			$this->user_id, $user_id, $header, $comment
		);

		return $this->ok([
			"achievement"       => (object) Apiv1_Format::achievement($achievement),
			"achievement_count" => (int) $achievement_count,
		]);
	}

	/**
	 * получаем список  достижений пользователя
	 *
	 * @return array
	 * @throws ParamException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getList():array {

		$user_id             = $this->post(\Formatter::TYPE_INT, "user_id");
		$last_achievement_id = $this->post(\Formatter::TYPE_INT, "last_achievement_id", 0);
		$limit               = $this->post(\Formatter::TYPE_INT, "limit", 50);

		// проверяем параметры на корректность
		if ($user_id < 1 || $last_achievement_id < 0 || $limit < 0) {
			throw new ParamException("incorrect param user_id");
		}

		// если запрашивают 0 записей
		if ($limit == 0) {

			return $this->ok([
				"achievement_list"  => (array) [],
				"achievement_count" => (int) 0,
				"has_next"          => (int) 0,
			]);
		}

		// ограничиваем указанное возвращаемое число до максимального
		$limit = limit($limit, 0, self::_MAX_GET_ACHIEVEMENT_LIST);

		// получаем список достижений пользователя
		$achievement_list = Type_User_Card_Achievement::getList($user_id, $last_achievement_id, $limit + 1);

		$formatted_achievement_list = [];
		$creator_user_id_list       = [];

		$has_next = 0;

		// если записей элементов больше чем лимит
		if (count($achievement_list) > $limit) {

			$has_next = 1;
			array_pop($achievement_list);
		}

		foreach ($achievement_list as $v) {

			$creator_user_id_list[]       = $v->creator_user_id;
			$formatted_achievement_list[] = Apiv1_Format::achievement($v);
		}

		// отдаем в action users идентификатор создателей достижений
		$this->action->users($creator_user_id_list);

		// получаем dynamic данные карточки сотрудника
		$card_dynamic_obj  = Type_User_Card_DynamicData::get($user_id);
		$achievement_count = Type_User_Card_DynamicData::getAchievementCount($card_dynamic_obj->data);

		return $this->ok([
			"achievement_list"  => (array) $formatted_achievement_list,
			"achievement_count" => (int) $achievement_count,
			"has_next"          => (int) $has_next,
		]);
	}

	/**
	 * редактируем достижение пользователя
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public function edit():array {

		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");
		$achievement_id = $this->post(\Formatter::TYPE_INT, "achievement_id");
		$new_header     = $this->post(\Formatter::TYPE_STRING, "header");
		$new_comment    = $this->post(\Formatter::TYPE_STRING, "comment");

		// проверяем параметры на корректность
		if ($user_id < 1 || $achievement_id < 1 || mb_strlen($new_header) < 1 || mb_strlen($new_comment) < 1) {
			throw new ParamException("get incorrect params");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::ACHIEVEMENT_EDIT);

		// парсим эмоджи заголовка и текста достижения
		$new_header  = Type_Api_Filter::replaceEmojiWithShortName($new_header);
		$new_comment = Type_Api_Filter::replaceEmojiWithShortName($new_comment);

		// фильтруем заголовок и текст достижения
		try {

			$new_header  = Type_Api_Filter::prepareText($new_header, Type_User_Card_Achievement::MAX_ACHIEVEMENT_HEADER_LENGTH);
			$new_comment = Type_Api_Filter::prepareText($new_comment, Type_User_Card_Achievement::MAX_ACHIEVEMENT_COMMENT_LENGTH);
		} catch (cs_Text_IsTooLong) {
			return $this->error(540, "text is too long");
		}

		// проверяем очищенный текст - возможно он стал пустым
		if (strlen($new_header) == 0 || strlen($new_comment) == 0) {
			return $this->error(937, "text after filter empty");
		}

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован/удалил аккаунт, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// получаем редакторов карточки пользователя
		$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editor_id_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// достаем достижение из базы
		try {
			$achievement = Type_User_Card_Achievement::get($user_id, $achievement_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("not found this achievement");
		}

		// если записи достижения не нашлась или удалена, то выдаем ошибку
		if (is_null($achievement->achievement_id) || $achievement->is_deleted == 1) {
			return $this->error(931, "not found achievement of user");
		}

		// если наш пользователь НЕ создатель этого достижения, то выдаём ошибку
		if ($achievement->creator_user_id != $this->user_id) {
			return $this->error(932, "user is not creator of this achievement");
		}

		return $this->_editAchievement($user_id, $achievement, $new_header, $new_comment);
	}

	/**
	 * редактируем достижение
	 *
	 * @param int                                $user_id
	 * @param Struct_Domain_Usercard_Achievement $achievement
	 * @param string                             $new_header
	 * @param string                             $new_comment
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _editAchievement(int $user_id, Struct_Domain_Usercard_Achievement $achievement, string $new_header, string $new_comment):array {

		// если за достижением закреплено сообщение, то редактируем текст также и для сообщения в группе Достижения
		$message_map = Type_User_Card_Achievement::getMessageMap($achievement->data);
		if (Type_User_Card_Achievement::isNeedMessageEdit($message_map, $achievement, $new_header, $new_comment)) {

			try {
				Gateway_Socket_Conversation::editAchievementText($this->user_id, $message_map, $new_header, $new_comment);
			} catch (\Exception|\Error) {
				// ничего не выдаем
			}
		}

		// парсим ссылки с тексте
		$link_list = Gateway_Socket_Conversation::getLinkListFromText($new_comment, $user_id, $this->user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_ACHIEVEMENT, $achievement->achievement_id);

		if (count($link_list) < 1) {
			$achievement->data = Type_User_Card_Achievement::setLinkList($achievement->data, []);
		}

		// редактируем достижение пользователя
		Type_User_Card_Achievement::edit($user_id, $achievement->achievement_id, $new_header, $new_comment, $achievement->data);

		// получаем dynamic данные достижений пользователя
		$card_dynamic_obj  = Type_User_Card_DynamicData::get($user_id);
		$achievement_count = Type_User_Card_DynamicData::getAchievementCount($card_dynamic_obj->data);

		// актуализируем данные редактируемого достижения
		$achievement->header_text      = $new_header;
		$achievement->description_text = $new_comment;

		return $this->ok([
			"achievement"       => (object) Apiv1_Format::achievement($achievement),
			"achievement_count" => (int) $achievement_count,
		]);
	}

	/**
	 * удаляем достиижение пользователя
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

		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");
		$achievement_id = $this->post(\Formatter::TYPE_INT, "achievement_id");

		if ($user_id < 1 || $achievement_id < 1) {
			throw new ParamException("incorrect param user_id or achievement_id");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::ACHIEVEMENT_REMOVE);

		// получаем инфу по пользователю, выбрасываем paramException, если пользователь не найден
		// проверяем, если пользователь заблокирован/ удалил аккаунт, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// получаем редакторов карточки пользователя
		$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editor_id_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// достаем достижение из базы
		try {
			$achievement = Type_User_Card_Achievement::get($user_id, $achievement_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("not found this achievement");
		}

		// если достижение не нашлось, то возвращаем просто ok
		if ($achievement->is_deleted == 1) {

			// получаем dynamic данные достижений пользователя
			$card_dynamic_obj  = Type_User_Card_DynamicData::get($user_id);
			$achievement_count = Type_User_Card_DynamicData::getAchievementCount($card_dynamic_obj->data);

			return $this->ok([
				"achievement_count" => (int) $achievement_count,
			]);
		}

		return $this->_tryRemove($user_id, $achievement);
	}

	/**
	 * удаляем достижение
	 *
	 * @param int                                $user_id
	 * @param Struct_Domain_Usercard_Achievement $achievement
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected function _tryRemove(int $user_id, Struct_Domain_Usercard_Achievement $achievement):array {

		// помечаем достижение удаленным
		Type_User_Card_Achievement::delete($user_id, $achievement->achievement_id);

		// декрементим значение достижений
		$card_dynamic_obj  = Type_User_Card_Achievement::decInDynamicData($user_id);
		$achievement_count = Type_User_Card_DynamicData::getAchievementCount($card_dynamic_obj->data);

		// если за достижением закреплено сообщение, то удаляем также и сообщение в группе Достижения
		$message_map = Type_User_Card_Achievement::getMessageMap($achievement->data);
		if (mb_strlen($message_map) > 0) {

			try {

				$conversation_config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::ACHIEVEMENT_CONVERSATION_KEY_NAME);
				$conversation_map    = $conversation_config["value"];
				Gateway_Socket_Conversation::tryDeleteMessageList([$message_map], $conversation_map, $this->user_id, true);
			} catch (\Exception|\Error) {
				// в случае ошибки ничего не выдаем
			}
		}

		return $this->ok([
			"achievement_count" => (int) $achievement_count,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

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