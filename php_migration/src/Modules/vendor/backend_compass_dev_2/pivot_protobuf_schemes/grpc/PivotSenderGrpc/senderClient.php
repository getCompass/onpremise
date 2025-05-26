<?php
// GENERATED CODE -- DO NOT EDIT!

namespace PivotSenderGrpc;

/**
 * сервис, который описывает все метод go_sender
 */
class senderClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \PivotSenderGrpc\SenderSetTokenRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSetToken(\PivotSenderGrpc\SenderSetTokenRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSetToken',
        $argument,
        ['\PivotSenderGrpc\SenderSetTokenResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderSendEventRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSendEvent(\PivotSenderGrpc\SenderSendEventRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSendEvent',
        $argument,
        ['\PivotSenderGrpc\SenderSendEventResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderSendEventToAllRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSendEventToAll(\PivotSenderGrpc\SenderSendEventToAllRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSendEventToAll',
        $argument,
        ['\PivotSenderGrpc\SenderSendEventToAllResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderGetOnlineConnectionsByUserIdRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderGetOnlineConnectionsByUserId(\PivotSenderGrpc\SenderGetOnlineConnectionsByUserIdRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderGetOnlineConnectionsByUserId',
        $argument,
        ['\PivotSenderGrpc\SenderGetOnlineConnectionsByUserIdResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderCloseConnectionsByUserIdRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderCloseConnectionsByUserId(\PivotSenderGrpc\SenderCloseConnectionsByUserIdRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderCloseConnectionsByUserId',
        $argument,
        ['\PivotSenderGrpc\SenderCloseConnectionsByUserIdResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderAddTaskPushNotificationRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderAddTaskPushNotification(\PivotSenderGrpc\SenderAddTaskPushNotificationRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderAddTaskPushNotification',
        $argument,
        ['\PivotSenderGrpc\SenderAddTaskPushNotificationResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderGetOnlineUsersRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderGetOnlineUsers(\PivotSenderGrpc\SenderGetOnlineUsersRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderGetOnlineUsers',
        $argument,
        ['\PivotSenderGrpc\SenderGetOnlineUsersResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderAddUsersToThreadRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderAddUsersToThread(\PivotSenderGrpc\SenderAddUsersToThreadRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderAddUsersToThread',
        $argument,
        ['\PivotSenderGrpc\SenderAddUsersToThreadResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderSendTypingEventRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSendTypingEvent(\PivotSenderGrpc\SenderSendTypingEventRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSendTypingEvent',
        $argument,
        ['\PivotSenderGrpc\SenderSendTypingEventResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderSendThreadTypingEventRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSendThreadTypingEvent(\PivotSenderGrpc\SenderSendThreadTypingEventRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSendThreadTypingEvent',
        $argument,
        ['\PivotSenderGrpc\SenderSendThreadTypingEventResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderSendVoIPRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSendVoIP(\PivotSenderGrpc\SenderSendVoIPRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSendVoIP',
        $argument,
        ['\PivotSenderGrpc\SenderSendVoIPResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SenderSendIncomingCallRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SenderSendIncomingCall(\PivotSenderGrpc\SenderSendIncomingCallRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SenderSendIncomingCall',
        $argument,
        ['\PivotSenderGrpc\SenderSendIncomingCallResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SystemStatusRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemStatus(\PivotSenderGrpc\SystemStatusRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SystemStatus',
        $argument,
        ['\PivotSenderGrpc\SystemStatusResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SystemTraceGoroutineRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceGoroutine(\PivotSenderGrpc\SystemTraceGoroutineRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SystemTraceGoroutine',
        $argument,
        ['\PivotSenderGrpc\SystemTraceGoroutineResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SystemTraceMemoryRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceMemory(\PivotSenderGrpc\SystemTraceMemoryRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SystemTraceMemory',
        $argument,
        ['\PivotSenderGrpc\SystemTraceMemoryResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotSenderGrpc\SystemCpuProfileRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCpuProfile(\PivotSenderGrpc\SystemCpuProfileRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotSenderGrpc.sender/SystemCpuProfile',
        $argument,
        ['\PivotSenderGrpc\SystemCpuProfileResponseStruct', 'decode'],
        $metadata, $options);
    }

}
