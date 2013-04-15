<?php

namespace Ratchet\SocketIO\Message;

/**
 * Connect message
 */
class ConnectMessage extends Message
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return self::TYPE_CONNECT;
    }
}
