<?php

namespace Ratchet\SocketIo\Protocol\Version1\Message;

use Ratchet\SocketIo;
use Ratchet\ConnectionInterface;

/**
 * Message message handler
 */
class MessageMessageHandler extends MessageHandler
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
        return 3;
    }
}
