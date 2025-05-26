<?php
// GENERATED CODE -- DO NOT EDIT!

namespace PusherGrpc;

/**
 * сервис, который описывает все метод go_pusher
 */
class pusherClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \PusherGrpc\PusherUpdateBadgeRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function PusherUpdateBadge(\PusherGrpc\PusherUpdateBadgeRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/PusherUpdateBadge',
        $argument,
        ['\PusherGrpc\PusherUpdateBadgeResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\PusherSendTestPushRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function PusherSendTestPush(\PusherGrpc\PusherSendTestPushRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/PusherSendTestPush',
        $argument,
        ['\PusherGrpc\PusherSendTestPushResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemStatusRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemStatus(\PusherGrpc\SystemStatusRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemStatus',
        $argument,
        ['\PusherGrpc\SystemStatusResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemTraceGoroutineRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceGoroutine(\PusherGrpc\SystemTraceGoroutineRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemTraceGoroutine',
        $argument,
        ['\PusherGrpc\SystemTraceGoroutineResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemTraceMemoryRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceMemory(\PusherGrpc\SystemTraceMemoryRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemTraceMemory',
        $argument,
        ['\PusherGrpc\SystemTraceMemoryResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemCpuProfileRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCpuProfile(\PusherGrpc\SystemCpuProfileRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemCpuProfile',
        $argument,
        ['\PusherGrpc\SystemCpuProfileResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemReloadConfigRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadConfig(\PusherGrpc\SystemReloadConfigRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemReloadConfig',
        $argument,
        ['\PusherGrpc\SystemReloadConfigResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemReloadShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadSharding(\PusherGrpc\SystemReloadShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemReloadSharding',
        $argument,
        ['\PusherGrpc\SystemReloadShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PusherGrpc\SystemCheckShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCheckSharding(\PusherGrpc\SystemCheckShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pusherGrpc.pusher/SystemCheckSharding',
        $argument,
        ['\PusherGrpc\SystemCheckShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

}
