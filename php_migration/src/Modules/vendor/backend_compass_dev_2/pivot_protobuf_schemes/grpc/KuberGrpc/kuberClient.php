<?php
// GENERATED CODE -- DO NOT EDIT!

namespace KuberGrpc;

/**
 * сервис, который описывает все метод go_kuber
 */
class kuberClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \KuberGrpc\CompanyAddRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CompanyAdd(\KuberGrpc\CompanyAddRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/CompanyAdd',
        $argument,
        ['\KuberGrpc\CompanyAddResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\CompanyGetListRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CompanyGetList(\KuberGrpc\CompanyGetListRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/CompanyGetList',
        $argument,
        ['\KuberGrpc\CompanyGetListResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\CompanyDeleteRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CompanyDelete(\KuberGrpc\CompanyDeleteRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/CompanyDelete',
        $argument,
        ['\KuberGrpc\CompanyDeleteResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\CompanyUpdateRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CompanyUpdate(\KuberGrpc\CompanyUpdateRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/CompanyUpdate',
        $argument,
        ['\KuberGrpc\CompanyUpdateResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemStatusRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemStatus(\KuberGrpc\SystemStatusRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemStatus',
        $argument,
        ['\KuberGrpc\SystemStatusResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemTraceGoroutineRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceGoroutine(\KuberGrpc\SystemTraceGoroutineRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemTraceGoroutine',
        $argument,
        ['\KuberGrpc\SystemTraceGoroutineResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemTraceMemoryRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemTraceMemory(\KuberGrpc\SystemTraceMemoryRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemTraceMemory',
        $argument,
        ['\KuberGrpc\SystemTraceMemoryResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemCpuProfileRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCpuProfile(\KuberGrpc\SystemCpuProfileRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemCpuProfile',
        $argument,
        ['\KuberGrpc\SystemCpuProfileResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemReloadConfigRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadConfig(\KuberGrpc\SystemReloadConfigRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemReloadConfig',
        $argument,
        ['\KuberGrpc\SystemReloadConfigResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemReloadShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemReloadSharding(\KuberGrpc\SystemReloadShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemReloadSharding',
        $argument,
        ['\KuberGrpc\SystemReloadShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \KuberGrpc\SystemCheckShardingRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SystemCheckSharding(\KuberGrpc\SystemCheckShardingRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/kuberGrpc.kuber/SystemCheckSharding',
        $argument,
        ['\KuberGrpc\SystemCheckShardingResponseStruct', 'decode'],
        $metadata, $options);
    }

}
