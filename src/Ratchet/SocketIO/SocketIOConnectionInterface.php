<?php

namespace Ratchet\SocketIO;

use Ratchet\ConnectionInterface;

/**
 * SocketIO connection
 */
interface SocketIOConnectionInterface extends ConnectionInterface
{
    /**
     * Is established
     *
     * @param  null|bool $established
     * @return null|bool
     */
    public function isEstablished($established = null);

    /**
     * Get protocol
     *
     * @return \Ratchet\SocketIO\Protocol\ProtocolInterface
     */
    public function getProtocol();

    /**
     * Get transport
     *
     * @return \Ratchet\SocketIO\Transport\TransportInterface
     */
    public function getTransport();

    /**
     * Get session id
     *
     * @return string
     */
    public function getSessionId();
}
