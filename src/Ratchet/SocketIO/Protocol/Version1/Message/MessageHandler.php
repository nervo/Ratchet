<?php

namespace Ratchet\SocketIO\Protocol\Version1\Message;

use Ratchet\SocketIO;
use Ratchet\ConnectionInterface;

/**
 * Message handler
 */
abstract class MessageHandler
{
    /**
     * Get type
     *
     * @return string
     */
    abstract public function getType();

    /**
     * On open
     *
     * @param \Ratchet\ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection)
    {
    }

    /**
     * On message
     *
     * @param \Ratchet\ConnectionInterface $connection
     * @param \Ratchet\SocketIO\Protocol\Version1\Message\Message
     */
    public function onMessage(
        ConnectionInterface $connection,
        SocketIO\Protocol\Version1\Message\Message $message
    ) {
    }

    /**
     * On close
     *
     * @param \Ratchet\ConnectionInterface $connection
     */
    public function onClose(ConnectionInterface $connection)
    {
    }

    /**
     * Is message type
     *
     * @param \Ratchet\SocketIO\Protocol\Version1\Message\Message $message
     *
     * @return bool
     */
    public function isMessageType(Message $message)
    {
        return $message->getType() === $this->getType();
    }
}
