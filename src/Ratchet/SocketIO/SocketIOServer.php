<?php

namespace Ratchet\SocketIO;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\SocketIO\Protocol;
use Ratchet\SocketIO\Http;
use Ratchet\SocketIO\Message;

class SocketIOServer implements MessageComponentInterface
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
     * This class just makes it 1 step easier to use Topic objects in WAMP
     * If you're looking at the source code, look in the __construct of this
     *  class and use that to make your application instead of using this
     */
    public function __construct(SocketIOServerInterface $server, array $options = array())
    {
        // Request parser
        $this->httpRequestParser = new Http\RequestParser();

        // Message proxy
        $messageProxy = new Message\MessageProxy($server);
        
        // Protocol manager
        $this->protocolManager = new Protocol\ProtocolManager();
        $this->protocolManager
            ->enableProtocol(
                new Protocol\Version1\Protocol(
                    $messageProxy,
                    $options
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $connection->socketIO = new \StdClass();
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        if (!isset($connection->socketIO->protocol)) {
            try {
                if (null === ($request = $this->httpRequestParser->onMessage($connection, $message))) {
                    return;
                }
            } catch (\OverflowException $oe) {
                return $this->close($connection, 413);
            }

            $connection->socketIO->request = $request;
        
            // Get protocol
            try {
                $connection->socketIO->protocol = $this->protocolManager->getRequestProtocol(
                    $connection->socketIO->request
                );
                $connection->socketIO->protocol->onOpen($connection);
            } catch (\InvalidArgumentException $e) {
                return $this->close($connection);
            }
        }
        
        // Transmit message to protocol
        $connection->socketIO->protocol->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        if (isset($connection->socketIO->protocol)) {
            $connection->socketIO->protocol->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        if (isset($connection->socketIO->protocol)) {
            $connection->socketIO->protocol->onError($connection, $e);
        }
    }

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
