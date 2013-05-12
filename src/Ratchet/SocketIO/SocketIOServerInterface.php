<?php

namespace Ratchet\SocketIO;

/**
 * SocketIO server interface
 */
interface SocketIOServerInterface
{
    /**
     * On connection
     *
     * @param SocketIOConnectionInterface $connection
     */
    public function onConnection(SocketIOConnectionInterface $connection);

    /**
     * On
     *
     * @param SocketIOConnectionInterface $connection
     * @param string                      $event
     * @param array                       $args
     */
    public function on(SocketIOConnectionInterface $connection, $event, array $args = null);

    /**
     * On disconnect
     *
     * @param SocketIOConnectionInterface $connection
     */
    public function onDisconnect(SocketIOConnectionInterface $connection);

    /**
     * On error
     *
     * @param SocketIOConnectionInterface $connection
     * @param \Exception                  $e
     */
    public function onError(SocketIOConnectionInterface $connection, \Exception $e);
}
