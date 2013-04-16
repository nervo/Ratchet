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

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $message = new ConnectMessage();
        
        $connection->send((string) $message);
        
        $this->server->onOpen($connection);
    }
    
    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->server->onClose($connection);
    }
    
    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        $this->server->onError($connection, $e);
    }
    
    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        $message = Message::parse($message);
        
        if ($message) {
            $message->handleServerConnection(
                $this->server,
                $connection
            );
        }
    }
}
