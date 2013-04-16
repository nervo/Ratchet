<?php

namespace Ratchet\SocketIO\Protocol\Version1\Transport\WebSocket;

use Ratchet\SocketIO\Protocol\Version1\Transport\TransportInterface;
use Ratchet\SocketIO\Message;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;
use Ratchet\WebSocket\WsServer;

/**
 * The WebSocket transport
 */
class Transport implements TransportInterface
{
    protected $wsServer;
    
    public function __construct(Message\MessageProxy $messageProxy)
    {
        $this->wsServer = new WsServer(
            $messageProxy
        );
    }
    
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
        if (!isset($connection->WebSocket)) {
            $this->wsServer->onOpen($connection);
        }
        
        $this->wsServer->onMessage($connection, $message);
    }
}
