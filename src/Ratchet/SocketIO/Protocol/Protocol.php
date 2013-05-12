<?php

namespace Ratchet\SocketIO\Protocol;

use Ratchet\SocketIO;
use React\EventLoop;
use Psr\Log;

/**
 * Protocol
 */
abstract class Protocol implements ProtocolInterface
{
    /**
     * Server
     *
     * @var SocketIO\SocketIOServerInterface
     */
    protected $server;

    /**
     * Loop
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Enabled
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Transports
     *
     * @var array
     */
    protected $transports = array();

    /**
     * Constructor
     *
     * @param SocketIO\SocketIOServerInterface $server
     * @param EventLoop\LoopInterface          $loop
     * @param Log\LoggerInterface              $logger
     */
    public function __construct(
        SocketIO\SocketIOServerInterface $server,
        EventLoop\LoopInterface $loop,
        Log\LoggerInterface $logger = null
    ) {
        // Server
        $this->server = $server;

        // Loop
        $this->loop = $loop;

        // Logger
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isVersion($version)
    {
        return $version == $this->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($enabled = null)
    {
        if (is_null($enabled)) {
            return $this->enabled;
        }

        $this->enabled = (bool) $enabled;
    }

    /**
     * Add support for a specific transport of the Socket.IO protocol
     *
     * @param SocketIO\Transport\TransportInterface $transport
     * @return $this
     */
    protected function addTransport(SocketIO\Transport\TransportInterface $transport)
    {
        $this->transports[$transport->getType()] = $transport;

        return $this;
    }

    /**
     * Enable transport types
     *
     * @param array $types
     * @return $this
     */
    protected function enableTransportTypes(array $types = array())
    {
        foreach ($types as $type) {
            foreach ($this->transports as $transport) {
                if ($transport->isType($type)) {
                    $transport->isEnabled(true);
                }
            }
        }

        return $this;
    }

    /**
     * Get an array of enabled transport types
     *
     * @return string
     */
    protected function getEnabledTransportTypes()
    {
        $types = array();

        foreach ($this->transports as $transport) {
            if ($transport->isEnabled()) {
                $types[] = $transport->getType();
            }
        }

        return $types;
    }
}
