<?php

namespace Ratchet\SocketIO;

use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * SocketIO server interface
 */
interface SocketIOServerInterface extends ComponentInterface
{
    /**
     * On message
     * 
     * @param \Ratchet\ConnectionInterface $connection
     * @param string $message
     */
    public function onMessage(ConnectionInterface $connection, $message);

    /**
     * On event
     * 
     * @param \Ratchet\ConnectionInterface $connection
     * @param string     $name
     * @param array|null $args
     */
    public function onEvent(ConnectionInterface $connection, $name, array $args = null);
}
