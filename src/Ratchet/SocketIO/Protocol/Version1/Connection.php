<?php

namespace Ratchet\SocketIO\Protocol\Version1;

use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;

/**
 * {@inheritdoc}
 */
class Connection extends AbstractConnectionDecorator
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ConnectionInterface $conn)
    {
        parent::__construct($conn);

        $this->socketIO->sessionId = uniqid();
    }
    
    public function send($msg)
    {
        $this->getConnection()->send($msg);
        
        return $this;
    }

    public function close()
    {
        $this->getConnection()->close();
    }
}