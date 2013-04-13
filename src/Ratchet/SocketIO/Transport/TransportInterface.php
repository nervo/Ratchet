<?php

namespace Ratchet\SocketIO\Transport;

use Ratchet\MessageInterface;

/**
 * A standard interface for interacting with the various transport of the Socket.IO protocol
 */
interface TransportInterface extends MessageInterface
{
    /**
     * Transport name
     * 
     * @return string
     */
    public function getName();
}
