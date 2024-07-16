<?php

namespace Compass\Pivot;

/**
 * класс для работы с паролем пользователя
 * @package Compass\Pivot
 */
class Domain_User_Entity_Password {

	/** требования по длине пароля */
	protected const _MIN_LEN = 8;
	protected const _MAX_LEN = 40;

	/**
	 * @var string регулярное выражение с запрещенными символами пароля
	 *             запрещены: пробелы, эмодзи
	 */
	protected const _BANNED_CHARS_REGEX = "/[\s\x{1F100}-\x{1F1FF}\x{1F300}-\x{1F5FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u";

	/** @var string алгоритм хэширования пароля */
	protected const _PASSWORD_HASH_ALGORITHM = PASSWORD_BCRYPT;

	/**
	 * выбрасываем исключение, если некорректный пароль
	 *
	 * @throws Domain_User_Exception_Password_Incorrect
	 */
	public static function throwIfIncorrect(string $password):void {

		if (!static::isCorrect($password)) {
			throw new Domain_User_Exception_Password_Incorrect("incorrect password new");
		}
	}

	/**
	 * выбрасываем исключение, если некорректный переданный новый пароль
	 *
	 * @throws Domain_User_Exception_Password_IncorrectNew
	 */
	public static function throwIfIncorrectNew(string $password):void {

		if (!static::isCorrect($password)) {
			throw new Domain_User_Exception_Password_IncorrectNew("incorrect password new");
		}
	}

	/**
	 * проверяем, что пароль корректен
	 *
	 * @return bool
	 */
	public static function isCorrect(string $password):bool {

		// проверяем по длинне
		$len = mb_strlen($password);
		if ($len < static::_MIN_LEN || $len > static::_MAX_LEN) {
			return false;
		}

		// если пароль содержит запрещенные символы
		if (preg_match(self::_BANNED_CHARS_REGEX, $password)) {
			return false;
		}

		return true;
	}

	/**
	 * проверяем, что пароль совпадает с истинным
	 */
	public static function assertPassword(string $verify_password, Struct_Db_PivotMail_MailUniq $mail_uniq):void {

		if (!static::isEqual($verify_password, $mail_uniq->password_hash)) {
			throw new Domain_User_Exception_Password_Mismatch("password not equal");
		}
	}

	/**
	 * совпадают ли пароли
	 *
	 * @return bool
	 */
	public static function isEqual(string $password, string $password_hash):bool {

		return password_verify($password, $password_hash);
	}

	/**
	 * собираем хэш пароля
	 *
	 * @return string
	 */
	public static function makeHash(string $password):string {

		return password_hash($password, static::_PASSWORD_HASH_ALGORITHM);
	}
}