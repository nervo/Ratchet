<?php

namespace Ratchet\SocketIO\Protocol;

use Ratchet\SocketIO;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;
use React\EventLoop;
use Psr\Log;

class Version1 extends Protocol
{
    /**
     * Options
     *
     * @var \Ratchet\SocketIO\Protocol\Version1\Options
     */
    protected $options;

    /**
     * Http request parser
     *
     * @var \Ratchet\SocketIO\Http\HttpRequestParser
     */
    protected $httpRequestParser;

    /**
     * Session ids
     *
     * @var array
     */
    protected $sessionIds = array();

    /**
     * Constructor
     *
     * @param SocketIO\SocketIOServerInterface $server
     * @param EventLoop\LoopInterface          $loop
     * @param SocketIO\SocketIOOptions         $options
     * @param Log\LoggerInterface              $logger
     */
    public function __construct(
        SocketIO\SocketIOServerInterface $server,
        EventLoop\LoopInterface $loop,
        SocketIO\SocketIOOptions $options,
        Log\LoggerInterface $logger = null
    ) {
        parent::__construct($server, $loop, $logger);

        // Options
        $this->options = new Version1\Options(
            $options->getProtocolOptions($this->getVersion())
        );

        // Http Request parser
        $this->httpRequestParser = new SocketIO\Http\RequestParser();

        // Server proxy
        $serverProxy = new Version1\ServerProxy(
            $this->server,
            $this->loop,
            $this->options,
            $this->logger
        );

        // Transports
        $this
            ->addTransport(
                new SocketIO\Transport\WebSocket(
                    $serverProxy,
                    $this->logger
                )
            )->enableTransportTypes(
                $this->options->getTransports()
            );

        // Log
        if ($this->logger) {
            $this->logger->info('Initialize protocol version ' . $this->getVersion(), $this->options->getAll());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isHttpRequestProtocol(RequestInterface $httpRequest)
    {
        // Get http request url path
        $path = $httpRequest->getUrl(true)->getPath();

        // Get resource
        $resource = $this->options->getResource();

        // Path begin by resource ?
        if (0 !== strpos($path, $resource)) {
            return false;
        }

        // Remove resource from path
        $path = str_replace(
            $resource,
            '',
            $path
        );

        $segments = explode('/', trim($path, '/'));

        if ($this->getVersion() === (int) $segments[0]) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Protocol version ' . $this->getVersion() . ' onOpen');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug(
                'Protocol version ' . $this->getVersion() . ' onMessage',
                array(utf8_encode($message))
            );
        }

        if (!isset($connection->socketIOConnection)) {
                    // Get http request
            try {
                $httpRequest = $this->httpRequestParser->onMessage($connection, $message);
            } catch (\OverflowException $oe) {
                return $this->close($connection, 413);
            }

            if (!$httpRequest) {
                return;
            }

            // Get session id
            $sessionId = $this->getHttpRequestSessionId($httpRequest);

            // No session id means handshake required
            if (!$sessionId) {
                // Handshake
                try {
                    $sessionId = $this->handshake(
                        $httpRequest,
                        $connection
                    );
                    // Store session id
                    $this->sessionIds[] = $sessionId;
                } catch (\InvalidArgumentException $e) {
                    $this->close($connection);
                }

                return;
            } else {
                // Get transport server
                try {
                    $transport = $this->getHttpRequestTransport(
                        $httpRequest
                    );
                } catch (\InvalidArgumentException $e) {
                    $this->close($connection);

                    return;
                }

                if (in_array($sessionId, $this->sessionIds)) {
                    // Set socket.io connection
                    $connection->socketIOConnection = new Version1\Connection(
                        $this,
                        $transport,
                        $sessionId
                    );

                    // Un-store session id
                    unset($this->sessionIds[$sessionId]);

                    // OnOpen transport
                    $transport->onOpen($connection);
                    $transport->onMessage($connection, $message);
                }
            }
        } else {

            // Transmit message to transport
            $connection->socketIOConnection->getTransport()->onMessage($connection, $message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Protocol version ' . $this->getVersion() . ' onClose');
        }

        if (isset($connection->socketIOConnection)) {
            $connection->socketIOConnection->getTransport()->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug(
                'Protocol version ' . $this->getVersion() . ' onError',
                array(get_class($e), $e->getMessage())
            );
        }

        if (isset($connection->socketIOConnection)) {
            $connection->socketIOConnection->getTransport()->onError($connection, $e);
        }
    }

    /**
     * Get http request session id
     *
     * @param  RequestInterface $httpRequest
     * @return null|string
     */
    protected function getHttpRequestSessionId(RequestInterface $httpRequest)
    {
        // Get http request url path
        $path = $httpRequest->getUrl(true)->getPath();

        // Remove resource from path
        $path = str_replace(
            $this->options->getResource(),
            '',
            $path
        );

        $segments = explode('/', trim($path, '/'));

        if (2 < count($segments)) {
            return (string) $segments[2];
        }

        return null;
    }

    /**
     * Get http request transport
     *
     * @param  RequestInterface                      $httpRequest
     * @return SocketIO\Transport\TransportInterface
     * @throws \InvalidArgumentException
     */
    protected function getHttpRequestTransport(RequestInterface $httpRequest)
    {
        // Get http request url path
        $path = $httpRequest->getUrl(true)->getPath();

        // Remove resource from path
        $path = str_replace(
            $this->options->getResource(),
            '',
            $path
        );

        $segments = explode('/', trim($path, '/'));

        if (1 > count($segments)) {
            throw new \InvalidArgumentException('Transport server not found');
        }

        $transportType = (string) $segments[1];

        foreach ($this->transports as $transport) {
            if ($transport->isType($transportType)) {
                return $transport;
            }
        }

        throw new \InvalidArgumentException('Transport server not found');
    }

    /**
     * Handshake
     *
     * @param  RequestInterface          $httpRequest
     * @param  ConnectionInterface       $connection
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function handshake(RequestInterface $httpRequest, ConnectionInterface $connection)
    {
        // Must be http get method
        if ('get' !== strtolower($httpRequest->getMethod())) {
            throw new \InvalidArgumentException('Not http get method');
        }

        // Must be http version 1.1 or greater
        if (1.1 > (double) $httpRequest->getProtocolVersion()) {
            throw new \InvalidArgumentException('Not http version 1.1 or greater');
        }

        // Generate session id
        $sessionId = uniqid('', true);

        // Response
        $response = new SocketIO\Http\Response($connection);
        $response->writeHead(
            200,
            array(
                'Content-Type'                     => 'text/plain',
                'access-control-allow-origin'      => (string) $httpRequest->getHeader('origin'),
                'Access-Control-Allow-Credentials' => 'true'
            )
        );
        $response->write(
            implode(
                ':',
                array(
                    $sessionId,
                    $this->options->areHeartbeatsEnabled() ? $this->options->getHeartbeatTimeout() : '',
                    $this->options->getCloseTimeout(),
                    implode(',', $this->getEnabledTransportTypes())
                )
            )
        );
        $response->end();
        $response->close();

        return $sessionId;
    }

    /**
     * Close a connection with an HTTP response
     * @param  \Ratchet\ConnectionInterface $connection
     * @param  int                          $code       HTTP status code
     * @return void
     */
    protected function close(ConnectionInterface $connection, $code = 400)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Server close', array($code));
        }

        $response = new SocketIO\Http\Response($connection);

        $response
            ->writeHead($code)
            ->end();
    }
}
