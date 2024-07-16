<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** rest api запрос к jitsi ноде провалился */
class Domain_Jitsi_Exception_Node_RequestFailed extends DomainException {

	protected int    $_response_http_code;
	protected string $_response;
	protected string $_url;

	public function __construct(int $http_code, string $response, string $url, string $message = "request is failed") {

		$this->_response_http_code = $http_code;
		$this->_response  = $response;
		$this->_url       = $url;

		// логируем
		Type_System_Admin::log("jitsi_rest_api_request", [
			"http_code" => $http_code,
			"response"  => $response,
			"url"       => $url,
		]);

		parent::__construct($message);
	}

	public function getResponseHttpCode():int {

		return $this->_response_http_code;
	}

	public function getResponse():string {

		return $this->_response;
	}

	public function getUrl():string {

		return $this->_url;
	}
}