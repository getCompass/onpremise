<?php
// GENERATED CODE -- DO NOT EDIT!

namespace EventGrpc;

/**
 * сервис, который описывает все метод go_event
 */
class eventClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \EventGrpc\EventSetEventTrapRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function EventSetEventTrap(\EventGrpc\EventSetEventTrapRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/eventGrpc.event/EventSetEventTrap',
        $argument,
        ['\EventGrpc\EventSetEventTrapResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \EventGrpc\EventWaitEventTrapRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function EventWaitEventTrap(\EventGrpc\EventWaitEventTrapRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/eventGrpc.event/EventWaitEventTrap',
        $argument,
        ['\EventGrpc\EventWaitEventTrapResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \EventGrpc\TaskPushRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskPush(\EventGrpc\TaskPushRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/eventGrpc.event/TaskPush',
        $argument,
        ['\EventGrpc\TaskPushResponseStruct', 'decode'],
        $metadata, $options);
    }

}
