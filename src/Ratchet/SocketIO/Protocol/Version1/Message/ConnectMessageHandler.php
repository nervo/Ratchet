<?php

namespace Ratchet\SocketIO\Protocol\Version1\Message;

use Ratchet\SocketIO;
use Ratchet\ConnectionInterface;

/**
 * Connect message handler
 */
class ConnectMessageHandler extends MessageHandler
{
    /**
     * Server
     *
     * @var \Ratchet\SocketIO\SocketIOServerInterface
     */
    protected $server;

    /**
     * Constructor
     *
     * @param \Ratchet\SocketIO\SocketIOServerInterface $server
     */
    public function __construct(SocketIO\SocketIOServerInterface $server)
    {
        // Server
        $this->server = $server;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $message = (new Message())
            ->setType($this->getType());

        $connection->send((string) $message);

        $this->server->onConnection($connection->socketIOConnection);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->server->onDisconnect($connection->socketIOConnection);
    }
}
