<?php

namespace Ratchet\SocketIo;

/**
 * SocketIo server interface
 */
interface SocketIoServerInterface
{
    /**
     * On connection
     *
     * @param SocketIoConnectionInterface $connection
     */
    public function onConnection(SocketIoConnectionInterface $connection);

    /**
     * On
     *
     * @param SocketIoConnectionInterface $connection
     * @param string                      $event
     * @param array                       $args
     */
    public function on(SocketIoConnectionInterface $connection, $event, array $args = null);

    /**
     * On disconnect
     *
     * @param SocketIoConnectionInterface $connection
     */
    public function onDisconnect(SocketIoConnectionInterface $connection);

    /**
     * On error
     *
     * @param SocketIoConnectionInterface $connection
     * @param \Exception                  $e
     */
    public function onError(SocketIoConnectionInterface $connection, \Exception $e);
}
