<?php

namespace Ratchet\SocketIO;

use Ratchet\SocketIO\Transport\TransportInterface;

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

    /**
     * Enable support for a specific transport of the Socket.IO protocol
     * 
     * @param \Ratchet\SocketIO\Transport\TransportInterface $transport
     * 
     * @return \Ratchet\SocketIO\TransportManager
     */
    public function enableTransport(TransportInterface $transport)
    {
        $this->transports[$transport->getName()] = $transport;

        return $this;
    }

    /**
     * Disable support for a specific Socket.IO protocol transport
     *
     * @param \Ratchet\SocketIO\Transport\TransportInterface $transport
     * 
     * @return \Ratchet\SocketIO\TransportManager
     */
    public function disableTransport(TransportInterface $transport)
    {
        $name = $transport->getName();
        
        if (array_key_exists($name, $this->transports)) {
            unset($this->transports[$name]);
        }
        
        return $this;
    }

    /**
     * Get a string of transport names supported (comma delimited)
     * 
     * @return string
     */
    public function getSupportedTransportString()
    {
        return implode(',', array_keys($this->transports));
    }
}
