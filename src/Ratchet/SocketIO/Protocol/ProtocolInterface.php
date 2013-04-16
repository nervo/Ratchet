<?php

namespace Ratchet\SocketIO\Protocol;

use Ratchet\MessageComponentInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * A standard interface for interacting with the various protocol of Socket.IO
 */
interface ProtocolInterface extends MessageComponentInterface
{
    /**
     * Although the version has a name associated with it the integer returned is the proper identification
     * @return int
     */
    public function getVersion();
    
    /**
     * Given a request, determine if this protocol should handle the protocol
     * @param  \Guzzle\Http\Message\RequestInterface $request
     * @return bool
     * @throws \UnderflowException                   If the protocol thinks the headers are still fragmented
     */
    public function isRequestProtocol(RequestInterface $request);
}
