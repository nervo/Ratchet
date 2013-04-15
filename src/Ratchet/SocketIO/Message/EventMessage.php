<?php

namespace Ratchet\SocketIO\Message;

use Ratchet\SocketIO\SocketIOServerInterface;
use Ratchet\ConnectionInterface;

/**
 * Event message
 */
class EventMessage extends Message
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return self::TYPE_CONNECT;
    }
    
    /**
     * {@inheritdoc}
     */
    public function handleServerConnection(SocketIOServerInterface $server, ConnectionInterface $connection)
    {
        $data = $this->getData();
        
        if (isset($data['name'])) {
            $server->onEvent(
                $connection,
                $data['name'],
                (isset($data['args']) && is_array($data['args'])) ? $data['args'] : null
            );
        }
    }
}
