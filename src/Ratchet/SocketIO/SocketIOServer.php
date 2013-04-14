<?php

namespace Ratchet\SocketIO;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\SocketIO\Protocol;
use Ratchet\SocketIO\Http;


class SocketIOServer implements MessageComponentInterface
//, WsServerInterface
{
    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     * @var HttpRequestParser
     * @note May not expose this in the future, may do through facade methods
     */
    public $httpRequestParser;

    /**
     * Manage the various SpcketIO protocols to support
     * @var ProtocolManager
     * @note May not expose this in the future, may do through facade methods
     */
    public $protocolManager;
    
    /**
     * Decorated component
     * 
     * @var \Ratchet\MessageComponentInterface
     */
    protected $component;
    
    /**
     * Connections
     * 
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * This class just makes it 1 step easier to use Topic objects in WAMP
     * If you're looking at the source code, look in the __construct of this
     *  class and use that to make your application instead of using this
     */
    public function __construct(SocketIOServerInterface $component, array $options = array())
    {
        //$this->socketIOProtocol = new ServerProtocol(new TopicManager($app));
        
        // Request parser
        $this->httpRequestParser = new Http\RequestParser();
        
        // Protocol manager
        $this->protocolManager = new Protocol\ProtocolManager();
        $this->protocolManager
            ->enableProtocol(new Protocol\Version1\Protocol($options));
        
        $this->component = $component;
        
        // Connections
        $this->connections = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn)
    {
        var_dump('---> onOpen');

        $conn->socketIO = new \StdClass();
        //$conn->socketIO->handshaked = false;

        //var_dump('================================================');
        //var_dump($conn);
        //var_dump('================================================');
        //$this->socketIOProtocol->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        
        var_dump('---> onMessage');
        //var_dump(get_class($connection));
        //var_dump($message);
        //
        //$this->socketIOProtocol->onMessage($connection, $message);
        
        //if (true === $connection->socketIO->handshaked) {
            //var_dump('-> post handshaked');
            //return $connection->socketIO->protocol->onMessage($this->connections[$connection], $message);
        //}

        try {
            if (null === ($request = $this->httpRequestParser->onMessage($connection, $message))) {
                return;
            }
        } catch (\OverflowException $oe) {
            return $this->close($connection, 413);
        }

        /*
        if (!$this->protocolManager->isProtocolEnabled($request)) {
            return $this->close($connection);
        }
         * 
         */

        $connection->socketIO->request = $request;
        
        // Get protocol
        try {
            $connection->socketIO->protocol = $this->protocolManager->getRequestProtocol(
                $connection->socketIO->request
            );
        } catch (\InvalidArgumentException $e) {
            return $this->close($connection);
        }
        
        // Transmit message to protocol
        $connection->socketIO->protocol->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        var_dump('onClose');
        //$this->socketIOProtocol->onClose($conn);
        //var_dump($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        var_dump('---> onError');
        //var_dump($conn);
        var_dump(get_class($e));
        //$this->socketIOProtocol->onError($conn, $e);
    }

    /**
     * {@inheritdoc}
     */
    /*
    public function getSubProtocols()
    {
        return $this->socketIOProtocol->getSubProtocols();
    }
    */

    /**
     * Close a connection with an HTTP response
     * @param  \Ratchet\ConnectionInterface $connection
     * @param  int                          $code HTTP status code
     * @return void
     */
    protected function close(ConnectionInterface $connection, $code = 400)
    {
        $response = new Http\Response($connection);
        $response->writeHead($code);
        $response->end();
    }
}
