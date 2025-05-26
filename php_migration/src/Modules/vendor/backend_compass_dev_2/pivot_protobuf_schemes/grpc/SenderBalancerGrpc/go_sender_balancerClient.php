<?php
// GENERATED CODE -- DO NOT EDIT!

namespace SenderBalancerGrpc;

/**
 * сервис, который описывает все метод go_sender_balancer
 */
class go_sender_balancerClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \SenderBalancerGrpc\SenderBalancerSetTokenRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderBalancerSetToken(\SenderBalancerGrpc\SenderBalancerSetTokenRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SenderBalancerSetToken',
        $argument,
        ['\SenderBalancerGrpc\SenderBalancerSetTokenResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SenderBalancerSendEventRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderBalancerSendEvent(\SenderBalancerGrpc\SenderBalancerSendEventRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SenderBalancerSendEvent',
        $argument,
        ['\SenderBalancerGrpc\SenderBalancerSendEventResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SenderBalancerBroadcastEventRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderBalancerBroadcastEvent(\SenderBalancerGrpc\SenderBalancerBroadcastEventRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SenderBalancerBroadcastEvent',
        $argument,
        ['\SenderBalancerGrpc\SenderBalancerBroadcastEventResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SenderBalancerGetOnlineConnectionsByUserIdRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderBalancerGetOnlineConnectionsByUserId(\SenderBalancerGrpc\SenderBalancerGetOnlineConnectionsByUserIdRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SenderBalancerGetOnlineConnectionsByUserId',
        $argument,
        ['\SenderBalancerGrpc\SenderBalancerGetOnlineConnectionsByUserIdResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SenderBalancerCloseConnectionsByUserIdRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderBalancerCloseConnectionsByUserId(\SenderBalancerGrpc\SenderBalancerCloseConnectionsByUserIdRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SenderBalancerCloseConnectionsByUserId',
        $argument,
        ['\SenderBalancerGrpc\SenderBalancerCloseConnectionsByUserIdResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SenderBalancerGetOnlineUsersRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderBalancerGetOnlineUsers(\SenderBalancerGrpc\SenderBalancerGetOnlineUsersRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SenderBalancerGetOnlineUsers',
        $argument,
        ['\SenderBalancerGrpc\SenderBalancerGetOnlineUsersResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SystemStatusRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemStatus(\SenderBalancerGrpc\SystemStatusRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SystemStatus',
        $argument,
        ['\SenderBalancerGrpc\SystemStatusResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SystemTraceGoroutineRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceGoroutine(\SenderBalancerGrpc\SystemTraceGoroutineRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SystemTraceGoroutine',
        $argument,
        ['\SenderBalancerGrpc\SystemTraceGoroutineResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SystemTraceMemoryRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceMemory(\SenderBalancerGrpc\SystemTraceMemoryRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SystemTraceMemory',
        $argument,
        ['\SenderBalancerGrpc\SystemTraceMemoryResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SystemCpuProfileRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCpuProfile(\SenderBalancerGrpc\SystemCpuProfileRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SystemCpuProfile',
        $argument,
        ['\SenderBalancerGrpc\SystemCpuProfileResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SystemReloadConfigRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadConfig(\SenderBalancerGrpc\SystemReloadConfigRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SystemReloadConfig',
        $argument,
        ['\SenderBalancerGrpc\SystemReloadConfigResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \SenderBalancerGrpc\SystemReloadShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadSharding(\SenderBalancerGrpc\SystemReloadShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/senderBalancerGrpc.go_sender_balancer/SystemReloadSharding',
        $argument,
        ['\SenderBalancerGrpc\SystemReloadShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

}
