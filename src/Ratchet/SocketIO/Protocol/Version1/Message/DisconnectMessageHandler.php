<?php

namespace Ratchet\SocketIO\Protocol\Version1\Message;

use Ratchet\SocketIO;
use Ratchet\ConnectionInterface;

/**
 * Disconnect message handler
 */
class DisconnectMessageHandler extends MessageHandler
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
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->server->onDisconnect($connection->socketIOConnection);
    }
}
