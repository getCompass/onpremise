<?php
// GENERATED CODE -- DO NOT EDIT!

namespace ActivityGrpc;

/**
 * сервис, который описывает все метод go_pivot_cache
 */
class activityClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \ActivityGrpc\SystemStatusRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemStatusResponseStruct
     */
    public function SystemStatus(\ActivityGrpc\SystemStatusRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemStatus',
        $argument,
        ['\ActivityGrpc\SystemStatusResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\SystemTraceGoroutineRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemTraceGoroutineResponseStruct
     */
    public function SystemTraceGoroutine(\ActivityGrpc\SystemTraceGoroutineRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemTraceGoroutine',
        $argument,
        ['\ActivityGrpc\SystemTraceGoroutineResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\SystemTraceMemoryRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemTraceMemoryResponseStruct
     */
    public function SystemTraceMemory(\ActivityGrpc\SystemTraceMemoryRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemTraceMemory',
        $argument,
        ['\ActivityGrpc\SystemTraceMemoryResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\SystemCpuProfileRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemCpuProfileResponseStruct
     */
    public function SystemCpuProfile(\ActivityGrpc\SystemCpuProfileRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemCpuProfile',
        $argument,
        ['\ActivityGrpc\SystemCpuProfileResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\SystemReloadConfigRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemReloadConfigResponseStruct
     */
    public function SystemReloadConfig(\ActivityGrpc\SystemReloadConfigRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemReloadConfig',
        $argument,
        ['\ActivityGrpc\SystemReloadConfigResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\SystemReloadShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemReloadShardingResponseStruct
     */
    public function SystemReloadSharding(\ActivityGrpc\SystemReloadShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemReloadSharding',
        $argument,
        ['\ActivityGrpc\SystemReloadShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\SystemCheckShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\SystemCheckShardingResponseStruct
     */
    public function SystemCheckSharding(\ActivityGrpc\SystemCheckShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/SystemCheckSharding',
        $argument,
        ['\ActivityGrpc\SystemCheckShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\UserGetActivityRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\UserGetActivityResponseStruct
     */
    public function UserGetActivity(\ActivityGrpc\UserGetActivityRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/UserGetActivity',
        $argument,
        ['\ActivityGrpc\UserGetActivityResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\UserGetActivityListRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\UserGetActivityListResponseStruct
     */
    public function UserGetActivityList(\ActivityGrpc\UserGetActivityListRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/UserGetActivityList',
        $argument,
        ['\ActivityGrpc\UserGetActivityListResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ActivityGrpc\UserResetCacheRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \ActivityGrpc\UserResetCacheResponseStruct
     */
    public function UserResetCache(\ActivityGrpc\UserResetCacheRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/activityGrpc.activity/UserResetCache',
        $argument,
        ['\ActivityGrpc\UserResetCacheResponseStruct', 'decode'],
        $metadata, $options);
    }

}
