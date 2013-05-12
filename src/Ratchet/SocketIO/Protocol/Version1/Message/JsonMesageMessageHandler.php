<?php

namespace Ratchet\SocketIO\Protocol\Version1\Message;

use Ratchet\SocketIO;
use Ratchet\ConnectionInterface;

/**
 * Json Message message handler
 */
class JsonMessageMessageHandler extends MessageHandler
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
        return 4;
    }
}
