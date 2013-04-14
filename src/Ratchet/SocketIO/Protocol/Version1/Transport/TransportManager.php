<?php

namespace Ratchet\SocketIO\Protocol\Version1\Transport;

use Guzzle\Http\Message\RequestInterface;

/**
 * Manage the various transports of the Socket.IO protocol
 * This accepts interfaces of transports to enable/disable
 */
class TransportManager
{
    /**
     * Storage of each transport enabled
     * 
     * @var array
     */
    protected $transports = array();


    public function getRequestTransport(RequestInterface $request)
    {
        foreach ($this->transports as $transport) {
            if ($transport->isRequestTransport($request)) {
                return $transport;
            }
        }

        throw new \InvalidArgumentException('Protocol not found');
    }
    
    /**
     * Enable support for a specific transport of the Socket.IO protocol
     * 
     * @param \Ratchet\SocketIO\Transport\TransportInterface $transport
     * 
     * @return \Ratchet\SocketIO\TransportManager
     */
    public function enableTransport(TransportInterface $transport)
    {
        $this->transports[$transport->getId()] = $transport;

        return $this;
    }

    /**
     * Get a string of transport ids supported (comma delimited)
     * 
     * @return string
     */
    public function getTransportIds()
    {
        return array_keys($this->transports);
    }
}
