<?php

namespace Ratchet\SocketIO;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop;
use Guzzle\Http\Message\RequestInterface;
use Psr\Log;

/**
 * SocketIO server
 */
class SocketIOServer implements MessageComponentInterface
{
    /**
     * Options
     *
     * @var \Ratchet\SocketIO\SocketIOOptions
     */
    protected $options;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Http request parser
     *
     * @var \Ratchet\SocketIO\Http\HttpRequestParser
     */
    protected $httpRequestParser;

    /**
     * Protocols
     *
     * @var array
     */
    protected $protocols = array();

    /**
     * Constructor
     *
     * @param SocketIOServerInterface $server
     * @param EventLoop\LoopInterface $loop
     * @param SocketIOOptions         $options
     * @param Log\LoggerInterface     $logger
     */
    public function __construct(
        SocketIOServerInterface $server,
        EventLoop\LoopInterface $loop,
        SocketIOOptions $options = null,
        Log\LoggerInterface $logger = null
    ) {
        // Http Request parser
        $this->httpRequestParser = new Http\RequestParser();

        // Options
        $this->options = $options ? $options : new SocketIOOptions();

        // Logger
        $this->logger = $logger;

        // Log
        if ($this->logger) {
            $this->logger->info('Initialize server', $this->options->getAll());
        }

        // Protocols
        $this
            ->addProtocol(
                new Protocol\Version1(
                    $server,
                    $loop,
                    $this->options,
                    $this->logger
                )
            )->enableProtocolVersions(
                $this->options->getProtocolVersions()
            );
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Server onOpen');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Server onMessage', array(utf8_encode($message)));
        }

        //if (!$protocol) {
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

            // Get protocol
            try {
                $protocol = $this->getHttpRequestProtocol(
                    $httpRequest
                );
            } catch (\InvalidArgumentException $e) {
                return $this->close($connection);
            }

            // Log
            if ($this->logger) {
                $this->logger->debug('Server onMessage get protocol version', array($protocol->getVersion()));
            }

            // Open protocol
            $protocol->onOpen($connection);
            $protocol->onMessage($connection, $message);
        } else {

            // Transmit message to protocol
            $connection->socketIOConnection->getProtocol()->onMessage($connection, $message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Server onClose');
        }

        if (isset($connection->socketIOConnection)) {
            $connection->socketIOConnection->getProtocol()->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Server onError', array(get_class($e), $e->getMessage()));
        }

        if (isset($connection->socketIOConnection)) {
            $connection->socketIOConnection->getProtocol()->onError($connection->socketIOConnection, $e);
        }
    }

    /**
     * Close a connection with an HTTP response
     * @param \Ratchet\ConnectionInterface $connection
     * @param int                          $code       HTTP status code
     */
    protected function close(ConnectionInterface $connection, $code = 400)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Server close', array($code));
        }

        $response = new Http\Response($connection);

        $response
            ->writeHead($code)
            ->end();
    }

    /**
     * Get http request protocol server
     *
     * @param  RequestInterface           $httpRequest
     * @return Protocol\ProtocolInterface
     * @throws \InvalidArgumentException
     */
    protected function getHttpRequestProtocol(RequestInterface $httpRequest)
    {
        foreach ($this->protocols as $protocol) {
            if ($protocol->isEnabled() && $protocol->isHttpRequestProtocol($httpRequest)) {
                return $protocol;
            }
        }

        throw new \InvalidArgumentException('Protocol server not found');
    }

    /**
     * Add protocol
     *
     * @param Protocol\ProtocolInterface $protocol
     * @return $this
     */
    protected function addProtocol(Protocol\ProtocolInterface $protocol)
    {
        $this->protocols[$protocol->getVersion()] = $protocol;

        return $this;
    }

    /**
     * Enable protocol versions
     *
     * @param array $versions
     * @return $this
     */
    protected function enableProtocolVersions(array $versions = array())
    {
        foreach ($versions as $version) {
            foreach ($this->protocols as $protocol) {
                if ($protocol->isVersion($version)) {
                    $protocol->isEnabled(true);
                }
            }
        }

        return $this;
    }
}
