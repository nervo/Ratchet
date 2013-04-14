<?php

namespace Ratchet\SocketIO\Protocol\Version1\Transport\WebSocket;

use Ratchet\SocketIO\Protocol\Version1\Transport\TransportInterface;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * The WebSocket transport
 */
class Transport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'websocket';
    }
    
    /**
     * {@inheritdoc}
     */
    public function isRequestTransport(RequestInterface $request)
    {
        $segments = $request->getUrl(true)->getPathSegments();

        if (3 > count($segments)) {
            return false;
        }

        if ('socket.io' == $segments[0] && $this->getId() === (string) $segments[2]) {
            return true;
        }
    }

    /**
     * @param \Ratchet\WebSocket\Version\RFC6455\Connection $from
     * @param string                                        $data
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        var_dump('---> WebSocket transport onMessage');
    }
}
