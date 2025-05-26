<?php
// GENERATED CODE -- DO NOT EDIT!

namespace PivotCacheGrpc;

/**
 * сервис, который описывает все метод go_pivot_cache
 */
class pivotCacheClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \PivotCacheGrpc\SessionGetInfoRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SessionGetInfo(\PivotCacheGrpc\SessionGetInfoRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SessionGetInfo',
        $argument,
        ['\PivotCacheGrpc\SessionGetInfoResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SessionDeleteByUserIdRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SessionDeleteByUserId(\PivotCacheGrpc\SessionDeleteByUserIdRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SessionDeleteByUserId',
        $argument,
        ['\PivotCacheGrpc\SessionDeleteByUserIdResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SessionDeleteBySessionUniqRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SessionDeleteBySessionUniq(\PivotCacheGrpc\SessionDeleteBySessionUniqRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SessionDeleteBySessionUniq',
        $argument,
        ['\PivotCacheGrpc\SessionDeleteBySessionUniqResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SessionDeleteUserInfoRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SessionDeleteUserInfo(\PivotCacheGrpc\SessionDeleteUserInfoRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SessionDeleteUserInfo',
        $argument,
        ['\PivotCacheGrpc\SessionDeleteUserInfoResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemStatusRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemStatus(\PivotCacheGrpc\SystemStatusRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemStatus',
        $argument,
        ['\PivotCacheGrpc\SystemStatusResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemTraceGoroutineRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceGoroutine(\PivotCacheGrpc\SystemTraceGoroutineRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemTraceGoroutine',
        $argument,
        ['\PivotCacheGrpc\SystemTraceGoroutineResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemTraceMemoryRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceMemory(\PivotCacheGrpc\SystemTraceMemoryRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemTraceMemory',
        $argument,
        ['\PivotCacheGrpc\SystemTraceMemoryResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemCpuProfileRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCpuProfile(\PivotCacheGrpc\SystemCpuProfileRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemCpuProfile',
        $argument,
        ['\PivotCacheGrpc\SystemCpuProfileResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemReloadConfigRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadConfig(\PivotCacheGrpc\SystemReloadConfigRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemReloadConfig',
        $argument,
        ['\PivotCacheGrpc\SystemReloadConfigResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemReloadShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadSharding(\PivotCacheGrpc\SystemReloadShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemReloadSharding',
        $argument,
        ['\PivotCacheGrpc\SystemReloadShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SystemCheckShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCheckSharding(\PivotCacheGrpc\SystemCheckShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SystemCheckSharding',
        $argument,
        ['\PivotCacheGrpc\SystemCheckShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\UsersGetInfoRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UserGetInfo(\PivotCacheGrpc\UsersGetInfoRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/UserGetInfo',
        $argument,
        ['\PivotCacheGrpc\UsersGetInfoResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\UsersGetInfoListRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UserGetInfoList(\PivotCacheGrpc\UsersGetInfoListRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/UserGetInfoList',
        $argument,
        ['\PivotCacheGrpc\UsersGetInfoListResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\UserResetCacheRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UserResetCache(\PivotCacheGrpc\UserResetCacheRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/UserResetCache',
        $argument,
        ['\PivotCacheGrpc\UserResetCacheResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \PivotCacheGrpc\SessionResetCacheRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SessionResetCache(\PivotCacheGrpc\SessionResetCacheRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/pivotCacheGrpc.pivotCache/SessionResetCache',
        $argument,
        ['\PivotCacheGrpc\SessionResetCacheResponseStruct', 'decode'],
        $metadata, $options);
    }

}
