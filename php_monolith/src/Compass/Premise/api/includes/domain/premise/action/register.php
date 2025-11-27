<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Server\ServerProvider;
use Random\RandomException;

/**
 * Регистрация onpremise сервера
 */
class Domain_Premise_Action_Register
{
	// длина секретного ключа
	// обязательно 32 рандомных байта
	protected const _SECRET_KEY_LENGTH = 32;

	/**
	 * Выполняем
	 *
	 * @throws Domain_Premise_Exception_ServerAlreadyRegistered
	 * @throws Domain_Premise_Exception_ServerCountExceeded
	 * @throws Domain_Premise_Exception_ServerIsNotAvailable
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(string | false $yc_identity_document = false, string | false $yc_identity_document_base64_signature = false): string
	{

		try {

			$secret_key = self::_getSecretKey($yc_identity_document, $yc_identity_document_base64_signature);
			Domain_Config_Entity_Main::set(Domain_Config_Entity_Main::SECRET_KEY, [
				"secret_key" => $secret_key,
			]);

		} catch (Gateway_Premise_Exception_ServerAlreadyRegistered) {
			throw new Domain_Premise_Exception_ServerAlreadyRegistered("server already registered");
		} catch (Gateway_Premise_Exception_ServerIsNotAvailable) {
			throw new Domain_Premise_Exception_ServerIsNotAvailable("server is not available");
		} catch (Gateway_Premise_Exception_ServerCountExceeded) {
			throw new Domain_Premise_Exception_ServerCountExceeded("server count exceeded");
		}

		// отправляем всем ws, что сервер активирован
		Gateway_Bus_SenderBalancer::serverActivated();

		return $secret_key;
	}

	/**
	 * Получаем секретный ключ
	 *
	 * @throws Gateway_Premise_Exception_ServerAlreadyRegistered
	 * @throws Gateway_Premise_Exception_ServerCountExceeded
	 * @throws Gateway_Premise_Exception_ServerIsNotAvailable
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RandomException
	 */
	protected static function _getSecretKey(string | false $yc_identity_document = false, string | false $yc_identity_document_base64_signature = false): string
	{
		if (ServerProvider::isLocalLicense()) {
			return base64_encode(random_bytes(self::_SECRET_KEY_LENGTH));
		}

		return Gateway_Premise_License::register(PUBLIC_ENTRYPOINT_PREMISE, SERVER_UID, $yc_identity_document, $yc_identity_document_base64_signature);
	}
}
