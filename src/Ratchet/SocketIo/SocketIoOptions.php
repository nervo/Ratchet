<?php

namespace Ratchet\SocketIo;

/**
 * SocketIo options
 */
class SocketIoOptions
{
    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'protocols' => array(
            1 => array()
        )
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
     * Get protocol versions
     *
     * @return array
     */
    public function getProtocolVersions()
    {
        return array_keys((array) $this->options['protocols']);
    }

    /**
     * Get protocol options
     *
     * @param int $version
     *
     * @return array
     */
    public function getProtocolOptions($version)
    {
        return array_key_exists((int) $version, $this->options['protocols']) ?
            (array) $this->options['protocols']
            : array();
    }
}
