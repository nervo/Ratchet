<?php

namespace Ratchet\SocketIo\Protocol\Version1\Message;

use Ratchet\SocketIo;
use Ratchet\ConnectionInterface;
use React\EventLoop;

/**
 * Heartbeat message handler
 */
class HeartbeatMessageHandler extends MessageHandler
{
    /**
     * Connections
     *
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * Constructor
     *
     * @param \React\EventLoop\LoopInterface              $loop
     * @param \SplObjectStorage                           $connections
     * @param \Ratchet\SocketIo\Protocol\Version1\Options $options
     */
    public function __construct(
        EventLoop\LoopInterface $loop,
        \SplObjectStorage $connections,
        SocketIo\Protocol\Version1\Options $options
    ) {
        // Connections
        $this->connections = $connections;

        // Heartbeat loop
        $loop->addPeriodicTimer(
            $options->getHeartbeatInterval(),
            function () {
                // Create heartbeat message
                $message = (new Message())
                    ->setType($this->getType());
                // Loop on connections
                foreach ($this->connections as $connection) {
                    if ($connection->socketIoConnection->isEstablished()) {
                        $connection->send((string) $message);
                    }
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, SocketIo\Protocol\Version1\Message\Message $message)
    {
    }
}
