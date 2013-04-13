<?php

namespace Ratchet\SocketIO\Transport;

use Ratchet\ConnectionInterface;

/**
 * The WebSocket transport
 */
class WebSocket implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'websocket';
    }

    /**
     * @param \Ratchet\WebSocket\Version\RFC6455\Connection $from
     * @param string                                        $data
     */
    public function onMessage(ConnectionInterface $from, $data)
    {
        var_dump('---> WebSocket transport onMessage');
    }
}
