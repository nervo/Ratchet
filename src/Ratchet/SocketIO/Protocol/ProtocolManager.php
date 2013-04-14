<?php

namespace Ratchet\SocketIO\Protocol;

use Guzzle\Http\Message\RequestInterface;

/**
 * Manage the various protocol versions of Socket.IO
 * This accepts interfaces of protocol to enable
 */
class ProtocolManager
{
    /**
     * Storage of each protocol enabled
     * @var array
     */
    protected $protocols = array();

    /**
     * Get the protocol negotiator for the request, if supported
     * @param  \Guzzle\Http\Message\RequestInterface       $request
     * @throws \InvalidArgumentException
     * @return \Ratchet\WebSocket\Protocol\ProtocolInterface
     */
    public function getRequestProtocol(RequestInterface $request)
    {
        foreach ($this->protocols as $protocol) {
            if ($protocol->isRequestProtocol($request)) {
                return $protocol;
            }
        }

        throw new \InvalidArgumentException('Protocol not found');
    }

    /**
     * Enable support for a specific protocol of SocketIO
     * @param  \Ratchet\WebSocket\Protocol\ProtocolInterface $protocol
     * @return ProtocolManager
     */
    public function enableProtocol(ProtocolInterface $protocol)
    {
        $this->protocols[$protocol->getVersion()] = $protocol;

        return $this;
    }
}
