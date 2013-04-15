<?php

namespace Ratchet\SocketIO\Message;

use Ratchet\MessageComponentInterface;
use Ratchet\SocketIO\SocketIOServerInterface;
use Ratchet\ConnectionInterface;

class MessageProxy implements MessageComponentInterface
{
    const MESSAGE_TYPE_CONNECT = 1;
    
    /**
     * Server
     * 
     * @var \Ratchet\SocketIO\SocketIOServerInterface
     */
    protected $server;
    
    /**
     *  Constructor
     * 
     * @param \Ratchet\SocketIO\SocketIOServerInterface $server
     */
    public function __construct(SocketIOServerInterface $server)
    {
        // Server
        $this->server = $server;
    }
    
    protected function formatMessage($type)
    {
        return $type . '::';
    }

    public function onOpen(ConnectionInterface $connection)
    {
        var_dump('Message\MessageProxy::onOpen');
        
        $connection->send(
            $this->formatMessage(
                self::MESSAGE_TYPE_CONNECT
            )
        );
        
        $this->server->onOpen($connection);
    }
    
    public function onClose(ConnectionInterface $connection)
    {
        var_dump('Message\MessageProxy::onClose');
        
        $this->server->onClose($connection);
    }
    
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        var_dump('Message\MessageProxy::onError');
        
        $this->server->onError($connection);
    }
    
    public function onMessage(ConnectionInterface $connection, $message)
    {
        var_dump('Message\MessageProxy::onMessage');
    }
}
