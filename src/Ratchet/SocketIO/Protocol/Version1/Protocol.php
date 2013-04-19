<?php

namespace Ratchet\SocketIO\Protocol\Version1;

use Ratchet\SocketIO\Protocol\ProtocolInterface;
use Ratchet\SocketIO\Protocol\Version1\Transport;
use Ratchet\SocketIO\Protocol\Version1\Session;
use Ratchet\SocketIO\Protocol\Version1\HandshakeVerifier;
use Ratchet\SocketIO\Http;
use Ratchet\SocketIO\Message;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;
use React;

class Protocol implements ProtocolInterface
{
    /**
     * Options
     * 
     * @var array
     */
    protected $options;
    
    /**
     * @var Protocol1\HandshakeVerifier
     */
    protected $handshakeVerifier;
    
    /**
     * Manage the various Socket.IO transports to support
     * 
     * @var \Ratchet\SocketIO\TransportManager
     */
    public $transportManager;

    public function __construct(Message\MessageProxy $messageProxy, array $options = array())
    {
        // Options
        $this->options = array_merge(
            array(
                'heartbeats'         => true,
                'heartbeat_timeout'  => 60,
                'heartbeat_interval' => 25,
                'close_timeout'      => 60
            ),
            $options
        );

        // Session manager
        $this->sessionManager = new Session\SessionManager();
        
        // Transport manager
        $this->transportManager = new Transport\TransportManager();
        $this->transportManager
            ->enableTransport(
                new Transport\WebSocket\Transport(
                    $messageProxy
                )
            );
        
        // Handshake verifier
        $this->handshakeVerifier = new HandshakeVerifier();
        
        // Heartbeat loop
        if ((bool) $this->options['heartbeats']) {
            $loop = React\EventLoop\Factory::create();
            $loop->addPeriodicTimer(
                (int) $this->options['heartbeat_interval'],
                function(){
                    // @todo
                }
            );
            $loop->run();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRequestProtocol(RequestInterface $request)
    {
        $segments = $request->getUrl(true)->getPathSegments();

        if (2 > count($segments)) {
            return false;
        }

        if ('socket.io' == $segments[0] && $this->getVersion() === (int) $segments[1]) {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return 1;
    }

    protected function handshake(ConnectionInterface $connection, $sessionId)
    {
        // Get request
        $request = $connection->socketIO->request;
        
        // Verify handshake
        if (true !== $this->handshakeVerifier->verifyRequest($request)) {
            $this->close($connection);
        }
        
        // Response
        $response = new Http\Response($connection);
        $response->writeHead(
            200,
            array(
                'Content-Type'                     => 'text/plain',
                'access-control-allow-origin'      => (string) $request->getHeader('origin'),
                'Access-Control-Allow-Credentials' => 'true'
            )
        );
        $response->write(
            implode(
                ':',
                array(
                    $sessionId,
                    ((bool) $this->options['heartbeat']) ? (int) $this->options['heartbeat_timeout'] : '',
                    (int) $this->options['close_timeout'],
                    implode(',', $this->transportManager->getTransportIds())
                )
            )
        );
        $response->end();
    }
    
    /**
     * @param \Ratchet\WebSocket\Version\RFC6455\Connection $connection
     * @param string                                        $message
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        if (!isset($connection->socketIO->transport)) {
            // Get request
            $request = $connection->socketIO->request;
        
            // Get session id
            $sessionId = $this->sessionManager->getRequestSessionId($request);
        
            // No session id means handshake required
            If (!$sessionId) {
               $this->handshake(
                   $connection,
                   uniqid('', true)
               );
               return;
            } else {
                // Set session id
                $connection->socketIO->sessionId = $sessionId;
                // Get transport
                try {
                    $connection->socketIO->transport = $this->transportManager->getRequestTransport(
                        $request
                    );
                } catch (\InvalidArgumentException $e) {
                    return $this->close($connection);
                }
            }
        }
        
        // Transmit message to transport
        $connection->socketIO->transport->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        if (isset($connection->socketIO->transport)) {
            $connection->socketIO->transport->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        if (isset($connection->socketIO->transport)) {
            $connection->socketIO->transport->onError($connection, $e);
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
