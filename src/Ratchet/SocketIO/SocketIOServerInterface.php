<?php

namespace Ratchet\SocketIO;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * SocketIO server interface
 */
interface SocketIOServerInterface extends MessageComponentInterface
{
    /**
     * On event
     * 
     * @param \Ratchet\ConnectionInterface $connection
     * @param string     $name
     * @param array|null $args
     */
    public function onEvent(ConnectionInterface $connection, $name, array $args = null);
}
