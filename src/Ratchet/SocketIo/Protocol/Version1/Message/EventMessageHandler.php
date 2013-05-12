<?php

namespace Ratchet\SocketIo\Protocol\Version1\Message;

use Ratchet\SocketIo;
use Ratchet\ConnectionInterface;

/**
 * Event message handler
 */
class EventMessageHandler extends MessageHandler
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
        return 5;
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, SocketIo\Protocol\Version1\Message\Message $message)
    {
        $data = $message->getData();

        if ($data && isset($data['name'])) {
            $this->server->on(
                $connection->socketIoConnection,
                (string) $data['name'],
                isset($data['args']) ? (array) $data['args'] : null
            );
        }
    }
}
