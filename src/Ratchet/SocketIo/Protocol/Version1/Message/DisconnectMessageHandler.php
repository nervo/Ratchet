<?php

namespace Ratchet\SocketIo\Protocol\Version1\Message;

use Ratchet\SocketIo;
use Ratchet\ConnectionInterface;

/**
 * Disconnect message handler
 */
class DisconnectMessageHandler extends MessageHandler
{
    /**
     * Server
     *
     * @var \Ratchet\SocketIo\SocketIoServerInterface
     */
    protected $server;

    /**
     * Constructor
     *
     * @param \Ratchet\SocketIo\SocketIoServerInterface $server
     */
    public function __construct(SocketIo\SocketIoServerInterface $server)
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
        $this->server->onDisconnect($connection->socketIoConnection);
    }
}
