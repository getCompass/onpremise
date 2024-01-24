<?php

namespace Compass\Speaker;

/*
 * родительский класс для всех подсущностей для ноды janus-gateway
 */

/**
 * @property Type_Janus_Node $node
 */
class Type_Janus_Default {

	protected $node;

	// @mixed
	function __construct($janus) {

		$this->node = $janus;
	}
}
