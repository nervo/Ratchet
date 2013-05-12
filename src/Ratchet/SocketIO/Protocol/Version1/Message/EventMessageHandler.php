<?php

namespace Ratchet\SocketIO\Protocol\Version1\Message;

use Ratchet\SocketIO;
use Ratchet\ConnectionInterface;

/**
 * Event message handler
 */
class EventMessageHandler extends MessageHandler
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
        return 5;
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, SocketIO\Protocol\Version1\Message\Message $message)
    {
        $data = $message->getData();

        if ($data && isset($data['name'])) {
            $this->server->on(
                $connection->socketIOConnection,
                (string) $data['name'],
                isset($data['args']) ? (array) $data['args'] : null
            );
        }
    }
}
