<?php

namespace Ratchet\SocketIo\Protocol\Version1\Message;

use Ratchet\SocketIo;
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
     * @param \Ratchet\SocketIo\Protocol\Version1\Message\Message
     */
    public function onMessage(
        ConnectionInterface $connection,
        SocketIo\Protocol\Version1\Message\Message $message
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
     * @param \Ratchet\SocketIo\Protocol\Version1\Message\Message $message
     *
     * @return bool
     */
    public function isMessageType(Message $message)
    {
        return $message->getType() === $this->getType();
    }
}
