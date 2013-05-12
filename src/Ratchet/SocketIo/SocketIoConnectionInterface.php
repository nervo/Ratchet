<?php

namespace Ratchet\SocketIo;

use Ratchet\ConnectionInterface;

/**
 * SocketIo connection
 */
interface SocketIoConnectionInterface extends ConnectionInterface
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
     * @return \Ratchet\SocketIo\Protocol\ProtocolInterface
     */
    public function getProtocol();

    /**
     * Get transport
     *
     * @return \Ratchet\SocketIo\Transport\TransportInterface
     */
    public function getTransport();

    /**
     * Get session id
     *
     * @return string
     */
    public function getSessionId();
}
