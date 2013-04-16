<?php

namespace Ratchet\SocketIO\Protocol\Version1\Transport;

use Ratchet\MessageComponentInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * A standard interface for interacting with the various transport of the Socket.IO protocol
 */
interface TransportInterface extends MessageComponentInterface
{
    /**
     * Transport id
     * 
     * @return string
     */
    public function getId();
    
    /**
     * Is request transport
     * 
     * @param \Guzzle\Http\Message\RequestInterface $request
     */
    public function isRequestTransport(RequestInterface $request);
}
