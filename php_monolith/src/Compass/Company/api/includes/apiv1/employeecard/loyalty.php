<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * работа с вовлеченностью сотрудника
 */
class Apiv1_EmployeeCard_Loyalty extends \BaseFrame\Controller\Api {

	protected const _MAX_GET_LOYALTY_LIST = 50;

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
	 * добавляем вовлеченность для пользователя
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
	 * @long
	 */
	public function add():array {

		$user_id               = $this->post(\Formatter::TYPE_INT, "user_id");
		$reaction_value        = $this->post(\Formatter::TYPE_INT, "reaction_value");
		$sport_value           = $this->post(\Formatter::TYPE_INT, "sport_value");
		$department_life_value = $this->post(\Formatter::TYPE_INT, "department_life_value");
		$comment_text          = $this->post(\Formatter::TYPE_STRING, "comment_text");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::LOYALTY_ADD);

		// проверяем параметры на корректность
		self::_throwIfIncorrectCommentText($comment_text);
		self::_throwIfIncorrectValueParams($reaction_value, $sport_value, $department_life_value);

		// получаем инфу по пользователю, выбрасываем ParamException, если пользователь не найден
		// проверяем, если пользователь заблокирован, то возвращаем ошибку
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// парсим эмоджи
		$comment_text = Type_Api_Filter::replaceEmojiWithShortName($comment_text);

		// фильтруем текст
		try {
			$comment_text = Type_Api_Filter::prepareText($comment_text, Type_User_Card_Loyalty::MAX_LOYALTY_COMMENT_LENGTH);
		} catch (cs_Text_IsTooLong) {
			return $this->error(540, "text is too long");
		}

		// проверяем очищенный текст - возможно он стал пустым
		if (strlen($comment_text) == 0) {
			return $this->error(937, "text after filter empty");
		}

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// добавляем запись вовлеченности
		$loyalty_obj            = Type_User_Card_Loyalty::add($user_id, $this->user_id, $reaction_value, $sport_value, $department_life_value, $comment_text);
		$avg_value              = Type_User_Card_Loyalty::getAvgValue($loyalty_obj);
		$value_by_category_type = Type_User_Card_Loyalty::getValueGroupedByType($loyalty_obj);

		// парсим ссылки с тексте
		Gateway_Socket_Conversation::getLinkListFromText($comment_text, $user_id, $this->user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_LOYALTY, $loyalty_obj->loyalty_id);

		// обновляем общее значение вовлеченности и количество
		$card_dynamic_obj = Type_User_Card_Loyalty::updateDynamicDataIfAdd($user_id, $sport_value, $reaction_value, $department_life_value);

		// получаем dynamic данные вовлеченности пользователя
		$loyalty_count        = Type_User_Card_DynamicData::getLoyaltyCount($card_dynamic_obj->data);
		$loyalty_value_data   = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($card_dynamic_obj->data);
		$loyalty_dynamic_data = Type_User_Card_Loyalty::getDynamicData($loyalty_value_data, $loyalty_count);

		return $this->ok([
			"loyalty"            => (object) Apiv1_Format::loyalty($loyalty_obj, $avg_value, $value_by_category_type),
			"total_loyalty_data" => (object) $this->_makeOutputForTotalLoyaltyData($loyalty_dynamic_data),
		]);
	}

	/**
	 * получаем список с вовлеченностью пользователя
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
		$last_loyalty_id = $this->post(\Formatter::TYPE_INT, "last_loyalty_id", 0);
		$limit           = $this->post(\Formatter::TYPE_INT, "limit", self::_MAX_GET_LOYALTY_LIST);

		// проверяем параметры на корректность
		if ($last_loyalty_id < 0 || $limit < 0) {
			throw new ParamException("incorrect param");
		}
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// получаем информацию по всем записям
		$total_loyalty_data        = Type_User_Card_Loyalty::getTotalData($user_id);
		$output_total_loyalty_data = $this->_makeOutputForTotalLoyaltyData($total_loyalty_data);

		// если запрашивают 0 записей
		if ($limit == 0) {

			return $this->ok([
				"loyalty_list"       => (array) [],
				"total_loyalty_data" => (object) $output_total_loyalty_data,
				"has_next"           => (int) 0,
			]);
		}

		// ограничиваем указанное возвращаемое число до максимального
		$limit = limit($limit, 0, self::_MAX_GET_LOYALTY_LIST);

		// получаем записи вовлеченности
		$loyalty_obj_list = Type_User_Card_Loyalty::getAllLoyalty($user_id, $last_loyalty_id, $limit + 1);

		// формируем ответ
		$output_loyalty_list = [];
		$user_id_list        = [];

		$has_next = 0;

		// если записей элементов больше чем лимит
		if (count($loyalty_obj_list) > $limit) {

			$has_next = 1;
			array_pop($loyalty_obj_list);
		}

		foreach ($loyalty_obj_list as $loyalty_obj) {

			$user_id_list[]         = $loyalty_obj->creator_user_id;
			$avg_value              = Type_User_Card_Loyalty::getAvgValue($loyalty_obj);
			$value_by_category_type = Type_User_Card_Loyalty::getValueGroupedByType($loyalty_obj);
			$link_list              = Type_User_Card_Loyalty::getLinklist($loyalty_obj);
			$output_loyalty_list[]  = Apiv1_Format::loyalty($loyalty_obj, $avg_value, $value_by_category_type, $link_list);
		}

		$this->action->users($user_id_list);

		return $this->ok([
			"loyalty_list"       => (array) $output_loyalty_list,
			"total_loyalty_data" => (object) $output_total_loyalty_data,
			"has_next"           => (int) $has_next,
		]);
	}

	/**
	 * редактируем вовлеченность пользователя
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
	public function edit():array {

		$user_id                   = $this->post(\Formatter::TYPE_INT, "user_id");
		$loyalty_id                = $this->post(\Formatter::TYPE_INT, "loyalty_id");
		$new_reaction_value        = $this->post(\Formatter::TYPE_INT, "reaction_value");
		$new_sport_value           = $this->post(\Formatter::TYPE_INT, "sport_value");
		$new_department_life_value = $this->post(\Formatter::TYPE_INT, "department_life_value");
		$new_comment               = $this->post(\Formatter::TYPE_STRING, "comment_text", "");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::LOYALTY_EDIT);

		// проверяем параметры на корректность
		self::_throwIfIncorrectLoyaltyId($loyalty_id);
		self::_throwIfIncorrectCommentText($new_comment);
		self::_throwIfIncorrectValueParams($new_reaction_value, $new_sport_value, $new_department_life_value);

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
		$new_comment = Type_Api_Filter::replaceEmojiWithShortName($new_comment);

		// фильтруем текст
		try {
			$new_comment = Type_Api_Filter::prepareText($new_comment, Type_User_Card_Loyalty::MAX_LOYALTY_COMMENT_LENGTH);
		} catch (cs_Text_IsTooLong) {
			return $this->error(540, "text is too long");
		}

		// проверяем очищенный текст - возможно он стал пустым
		if (strlen($new_comment) == 0) {
			return $this->error(937, "text after filter empty");
		}

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// достаем вовлеченность из базы
		$loyalty = Type_User_Card_Loyalty::get($user_id, $loyalty_id);

		// если вовлеченность не нашлась или удалена, то выдаем ошибку
		if (is_null($loyalty) || $loyalty->is_deleted == 1) {
			return $this->error(931, "not found loyalty of user");
		}

		// если наш пользователь НЕ создатель вовлеченности, то выдаём ошибку
		if ($loyalty->creator_user_id != $this->user_id) {
			return $this->error(932, "user is not creator of this loyalty");
		}

		// редактируем вовлеченность
		return $this->_editLoyalty($user_id, $loyalty, $new_comment, $new_reaction_value, $new_sport_value, $new_department_life_value);
	}

	/**
	 * редактирование вовлеченности
	 *
	 * @param int                            $user_id
	 * @param Struct_Domain_Usercard_Loyalty $loyalty
	 * @param string                         $new_comment
	 * @param int                            $new_reaction_value
	 * @param int                            $new_sport_value
	 * @param int                            $new_department_life_value
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected function _editLoyalty(int $user_id, Struct_Domain_Usercard_Loyalty $loyalty, string $new_comment, int $new_reaction_value, int $new_sport_value, int $new_department_life_value):array {

		// парсим ссылки с текста
		$link_list = Gateway_Socket_Conversation::getLinkListFromText($new_comment, $user_id, $this->user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_LOYALTY, $loyalty->loyalty_id);

		// редактируем вовлеченность пользователя
		[$new_avg_value, $new_value_by_category_type] = Type_User_Card_Loyalty::edit(
			$user_id, $loyalty->loyalty_id, $new_comment, $new_reaction_value, $new_sport_value, $new_department_life_value, $link_list
		);

		// получаем значения, собранные по категориям вовлеченности старой вовлеченности
		$value_by_category_type = Type_User_Card_Loyalty::getValueGroupedByType($loyalty);

		// актуализируем data вовлеченности новыми значениями
		$loyalty->data = Type_User_Card_Loyalty::setCategoryValueList($loyalty->data, $new_sport_value, $new_reaction_value, $new_department_life_value);

		// обновляем вовлеченность пользователя
		if ($new_reaction_value != $value_by_category_type[Type_User_Card_Loyalty::REACTION_VALUE_TYPE] ||
			$new_sport_value != $value_by_category_type[Type_User_Card_Loyalty::SPORT_VALUE_TYPE] ||
			$new_department_life_value != $value_by_category_type[Type_User_Card_Loyalty::DEPARTMENT_VALUE_TYPE]) {

			// получаем новые значения исходя из старых значений
			$card_dynamic_obj = Type_User_Card_Loyalty::updateDynamicDataIfEdit($user_id, $new_value_by_category_type, $value_by_category_type);
		} else {
			$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);
		}

		// актуализируем редактируемую вовлеченность
		$loyalty->comment_text = $new_comment;

		// получаем dynamic данные вовлеченности пользователя
		$loyalty_count        = Type_User_Card_DynamicData::getLoyaltyCount($card_dynamic_obj->data);
		$loyalty_value_data   = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($card_dynamic_obj->data);
		$loyalty_dynamic_data = Type_User_Card_Loyalty::getDynamicData($loyalty_value_data, $loyalty_count);

		return $this->ok([
			"loyalty"            => (object) Apiv1_Format::loyalty($loyalty, $new_avg_value, $new_value_by_category_type, $link_list),
			"total_loyalty_data" => (object) $this->_makeOutputForTotalLoyaltyData($loyalty_dynamic_data),
		]);
	}

	/**
	 * удаляем вовлеченность пользователя
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

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$loyalty_id = $this->post(\Formatter::TYPE_INT, "loyalty_id");

		// блокируем пользователя по превышенному числу вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::LOYALTY_REMOVE);

		self::_throwIfIncorrectLoyaltyId($loyalty_id);

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

		// достаем вовлеченность из базы
		$loyalty = Type_User_Card_Loyalty::get($user_id, $loyalty_id);

		// если записи вовлеченности не нашлось или удалена, то отдаем ok
		if (is_null($loyalty->loyalty_id) || $loyalty->is_deleted == 1) {

			$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);

			// получаем dynamic данные вовлеченнности пользователя
			$loyalty_count        = Type_User_Card_DynamicData::getLoyaltyCount($card_dynamic_obj->data);
			$loyalty_value_data   = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($card_dynamic_obj->data);
			$loyalty_dynamic_data = Type_User_Card_Loyalty::getDynamicData($loyalty_value_data, $loyalty_count);

			return $this->ok([
				"total_loyalty_data" => (object) $this->_makeOutputForTotalLoyaltyData($loyalty_dynamic_data),
			]);
		}

		// помечаем вовлеченность удаленной
		Type_User_Card_Loyalty::delete($user_id, $loyalty_id);
		$value_grouped_by_type = Type_User_Card_Loyalty::getValueGroupedByType($loyalty);

		// обновляем общее значение вовлеченности и количество в dynamic-данныех
		$card_dynamic_obj = Type_User_Card_Loyalty::updateDynamicDataIfRemove($user_id, $value_grouped_by_type);

		// получаем dynamic данные вовлеченности пользователя
		$loyalty_count        = Type_User_Card_DynamicData::getLoyaltyCount($card_dynamic_obj->data);
		$loyalty_value_data   = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($card_dynamic_obj->data);
		$loyalty_dynamic_data = Type_User_Card_Loyalty::getDynamicData($loyalty_value_data, $loyalty_count);

		return $this->ok([
			"total_loyalty_data" => (object) $this->_makeOutputForTotalLoyaltyData($loyalty_dynamic_data),
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * проверяем на корректность переданные value параметры
	 *
	 * @param int $reaction_value
	 * @param int $sport_value
	 * @param int $department_life_value
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectValueParams(int $reaction_value, int $sport_value, int $department_life_value):void {

		if ($reaction_value < 1 || $sport_value < 1 || $department_life_value < 1) {
			throw new ParamException("incorrect value params");
		}

		if ($reaction_value > 5 || $sport_value > 5 || $department_life_value > 5) {
			throw new ParamException("incorrect value params");
		}
	}

	/**
	 * проверяем на корректность переданный loyalty_id
	 *
	 * @param int $loyalty_id
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectLoyaltyId(int $loyalty_id):void {

		if ($loyalty_id < 1) {
			throw new ParamException("incorrect param loyalty_id");
		}
	}

	/**
	 * проверяем на корректность переданный comment
	 *
	 * @param string $comment_text
	 *
	 * @throws paramException
	 */
	protected static function _throwIfIncorrectCommentText(string $comment_text):void {

		if (mb_strlen($comment_text) < 1) {
			throw new ParamException("incorrect param comment_text");
		}
	}

	/**
	 * получить информацию о пользователе, но в случае некорректных данных — возвращать экзепшн
	 *
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

	/**
	 * формируем total_loyalty_data
	 *
	 * @param array $total_loyalty_data
	 *
	 * @return array
	 */
	protected function _makeOutputForTotalLoyaltyData(array $total_loyalty_data):array {

		return [
			"value"         => (int) $total_loyalty_data["value"],
			"category_list" => (array) [
				[
					"name"  => (string) "reaction",
					"value" => (int) $total_loyalty_data["reaction_value"],
				],
				[
					"name"  => (string) "sport",
					"value" => (int) $total_loyalty_data["sport_value"],
				],
				[
					"name"  => (string) "department_life",
					"value" => (int) $total_loyalty_data["department_life_value"],
				],
			],
		];
	}
}