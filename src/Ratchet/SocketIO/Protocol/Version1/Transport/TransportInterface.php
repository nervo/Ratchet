<?php

namespace Ratchet\SocketIO\Protocol\Version1\Transport;

use Ratchet\MessageInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * A standard interface for interacting with the various transport of the Socket.IO protocol
 */
interface TransportInterface extends MessageInterface
{
    /**
     * Transport id
     * 
     * @return string
     */
    public function getId();
    
    public function isRequestTransport(RequestInterface $request);
}
