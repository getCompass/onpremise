<?php
// GENERATED CODE -- DO NOT EDIT!

namespace UserbotCacheGrpc;

/**
 * сервис, который описывает все метод go_userbot_cache
 */
class userbotCacheClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \UserbotCacheGrpc\UserbotGetOneRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UserbotGetOne(\UserbotCacheGrpc\UserbotGetOneRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/userbotCacheGrpc.userbotCache/UserbotGetOne',
        $argument,
        ['\UserbotCacheGrpc\UserbotGetOneResponseStruct', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \UserbotCacheGrpc\UserbotClearRequestStruct $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UserbotClear(\UserbotCacheGrpc\UserbotClearRequestStruct $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/userbotCacheGrpc.userbotCache/UserbotClear',
        $argument,
        ['\UserbotCacheGrpc\UserbotClearResponseStruct', 'decode'],
        $metadata, $options);
    }

}
