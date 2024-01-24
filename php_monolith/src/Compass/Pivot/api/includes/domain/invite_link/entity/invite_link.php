<?php

namespace Compass\Pivot;

/**
 * Класс обобщающий сущность инвайт кода
 */
class Domain_InviteLink_Entity_InviteLink {

	public const DEFAULT_DISCOUNT = 20;

	protected const _VALID_DISCOUNT                         = [0, 5, 10, 15, 20];
	protected const _DEFAULT_HOUR_LIFE_INVITE_CODE          = 24;
	protected const _DEFAULT_TIME_LIFE_INVITE_CODE_IN_CACHE = HOUR5;
	protected const _DEFAULT_TIME_NEED_RECREATE_INVITE_CODE = HOUR5;

	/**
	 * Получить инвайт код из кеша
	 */
	public static function getInviteCodeFromCache(int $user_id, int $discount):Struct_Db_PartnerData_InviteCode {

		// проверяем наличия кода в кеше
		$cache              = Domain_InviteLink_Entity_Cache::init();
		$invite_code_object = $cache->get($user_id, $discount);

		if ($invite_code_object === false) {
			throw new Domain_InviteLink_Exception_InviteCodeNotFoundInCache();
		}

		return $invite_code_object;
	}

	/**
	 * Формируем ссылку приглашение
	 */
	public static function getInviteLink(string $invite_code):string {

		return PUBLIC_ENTRYPOINT_PIVOT . "/invite/" . $invite_code . "/";
	}

	/**
	 * Получаем сущность код
	 */
	public static function getInviteCode(string $invite_code):Struct_Db_PartnerInviteLink_InviteCodeMirror {

		try {
			$invite_code_row = Gateway_Db_PartnerInviteLink_InviteCodeListMirror::getOne($invite_code);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_InviteLink_Exception_InviteCodeNotExist();
		}

		return $invite_code_row;
	}

	/**
	 * Выкидываем исключение если срок жизни кода окончен
	 */
	public static function throwIfExpired(Struct_Db_PartnerData_InviteCode $invite_code_object):void {

		// если время действия кода истекло, отправляем ошибку
		if ($invite_code_object->expires_at < time()) {
			throw new Domain_InviteLink_Exception_InviteCodeExpired();
		}
	}

	/**
	 * Выкидываем исключение если код надо пересоздать
	 */
	public static function throwIfNeedRecreate(Struct_Db_PartnerData_InviteCode $invite_code_object):void {

		// если прошло более 5 часов с момента создания кода, то отдаем ошибку о том, что код надо пересоздать
		if ($invite_code_object->created_at + self::_DEFAULT_TIME_NEED_RECREATE_INVITE_CODE < time()) {
			throw new Domain_InviteLink_Exception_NeedRecreateInviteCode();
		}
	}

	/**
	 * Получить структуру инвайт кода
	 */
	public static function makeInviteCodeStruct(string $invite_code_hash, string $invite_code, int $partner_id, int $discount, int $can_reuse_after, int $expires_at, int $created_at, int $updated_at):Struct_Db_PartnerData_InviteCode {

		return new Struct_Db_PartnerData_InviteCode($invite_code_hash, $invite_code, $partner_id, $discount, $can_reuse_after, $expires_at, $created_at, $updated_at);
	}

	/**
	 * Возвращаем стандартное время жизни инвайт кода в кеше
	 */
	public static function getDefaultTimeLifeCodeInCache():int {

		return self::_DEFAULT_TIME_LIFE_INVITE_CODE_IN_CACHE;
	}

	/**
	 * Проверяем что скидка валидна
	 */
	public static function checkValidDiscount(int $discount):bool {

		return in_array($discount, self::_VALID_DISCOUNT);
	}

	/**
	 * Выкидываем исключение если передали не валидную ссылку
	 */
	public static function assertValidDiscount(int $discount):void {

		if (!self::checkValidDiscount($discount)) {
			throw new Domain_InviteLink_Exception_InviteCodeDiscountInvalid();
		}
	}
}