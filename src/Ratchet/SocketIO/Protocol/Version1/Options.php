<?php

namespace Ratchet\SocketIO\Protocol\Version1;

/**
 * Protocol version 1 options
 */
class Options
{
    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'resource'           => '/socket.io',
        'transports'         => array('websocket', 'htmlfile', 'xhr-polling', 'jsonp-polling'),
        'heartbeats'         => true,
        'heartbeat_timeout'  => 60,
        'heartbeat_interval' => 25,
        'close_timeout'      => 60
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        // Options
        $this->options = array_merge(
            $this->options,
            $options
        );
    }

    /**
     * Get all
     *
     * @return array
     */
    public function getAll()
    {
        return $this->options;
    }

    /**
     * Get resource
     *
     * @return string
     */
    public function getResource()
    {
        return (string) $this->options['resource'];
    }

    /**
     * Get transports
     *
     * @return array
     */
    public function getTransports()
    {
        return (array) $this->options['transports'];
    }

    /**
     * Are heartbeats enabled ?
     *
     * @return bool
     */
    public function areHeartbeatsEnabled()
    {
        return (bool) $this->options['heartbeats'];
    }

    /**
     * Get heartbeat timeout
     *
     * @return int
     */
    public function getHeartbeatTimeout()
    {
        return (int) $this->options['heartbeat_timeout'];
    }

    /**
     * Get heartbeat interval
     *
     * @return int
     */
    public function getHeartbeatInterval()
    {
        return (int) $this->options['heartbeat_interval'];
    }

    /**
     * Get close timeout
     *
     * @return int
     */
    public function getCloseTimeout()
    {
        return (int) $this->options['close_timeout'];
    }
}
