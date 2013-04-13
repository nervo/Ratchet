<?php

namespace Ratchet\SocketIO;

use Ratchet\MessageComponentInterface;
//use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\SocketIO\Version;
use Ratchet\SocketIO\Transport;
use Guzzle\Http\Message\Response;

class SocketIOServer implements MessageComponentInterface
//, WsServerInterface
{
    /**
     * Options
     * 
     * @var array
     */
    protected $options = array();
    
    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     * @var HttpRequestParser
     * @note May not expose this in the future, may do through facade methods
     */
    public $reqParser;

    /**
     * Manage the various WebSocket versions to support
     * @var VersionManager
     * @note May not expose this in the future, may do through facade methods
     */
    public $versioner;
    
    /**
     * Manage the various Socket.IO transports to support
     * 
     * @var \Ratchet\SocketIO\TransportManager
     */
    public $transporter;

    /**
     * Decorated component
     * 
     * @var \Ratchet\MessageComponentInterface
     */
    protected $_decorating;
    
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
        // Options
        $this->options = $options;
        
        //$this->socketIOProtocol = new ServerProtocol(new TopicManager($app));
        
        // Request parser
        $this->reqParser = new HttpRequestParser();
        
        // Versioner
        $this->versioner = new VersionManager();
        $this->versioner
            ->enableVersion(new Version\Version1());
        
        // Transporter
        $this->transporter = new TransportManager();
        $this->transporter
            ->enableTransport(new Transport\WebSocket());

        $this->_decorating = $component;
        
        // Connections
        $this->connections = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn)
    {
        var_dump('---> onOpen');

        $conn->SocketIO = new \StdClass();
        $conn->SocketIO->established = false;

        //var_dump('================================================');
        //var_dump($conn);
        //var_dump('================================================');
        //$this->socketIOProtocol->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        
        var_dump('---> onMessage');
        //var_dump(get_class($from));
        //var_dump($msg);
        //$this->socketIOProtocol->onMessage($from, $msg);

        try {
            if (null === ($request = $this->reqParser->onMessage($from, $msg))) {
                return;
            }
        } catch (\OverflowException $oe) {
            return $this->close($from, 413);
        }

        if (!$this->versioner->isVersionEnabled($request)) {
            return $this->close($from);
        }

        //$from->SocketIO->request = $request;
        $from->SocketIO->version = $this->versioner->getVersion($request);

        //var_dump($from->SocketIO->version);
        //die;
        
        // Get upgraded connection ...
        //$upgraded = $from->SocketIO->version->upgradeConnection($from, $this->_decorating);
        $upgraded = $from->SocketIO->version->upgradeConnection($from);
        //var_dump($upgraded);
        //die;
        
        //var_dump($from->SocketIO->version);
        //die;


        // Handshake
        try {
            $from->SocketIO->version->handshake(
                $request,
                $upgraded,
                $this->options,
                $this->transporter
            );
        } catch (\UnderflowException $e) {
            return;
        }

        // Attach upgraded connection
        $this->connections->attach($from, $upgraded);
        
        //$upgraded->SocketIO->established = true;
        $from->SocketIO->established = true;

        //var_dump($response);
        
        /*
        
        $body = '51pazOKK5VhTDiZWne2B:60:60:websocket,htmlfile,xhr-polling,jsonp-polling';
        
        $response
            ->setHeader('Content-Type', 'text/plain')
            ->setHeader('Connection', 'keep-alive')
            ->setHeader('Transfer-Encoding', 'chunked')
            ->setHeader('access-control-allow-origin', (string) $request->getHeader('origin'))
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setBody(
                dechex(strlen($body)) .
                "\r\n" .
                $body .
                "\r\n" .
                "0\r\n\r\n"
            );
        
        $from->send((string) $response);
        
        //var_dump('ayé');
        //var_dump((string) $response);
        
        return $from->close();
        */
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
     * @param  \Ratchet\ConnectionInterface $conn
     * @param  int                          $code HTTP status code
     * @return void
     */
    protected function close(ConnectionInterface $conn, $code = 400)
    {
        /*
        $response = new Response($code, array(
            'X-Powered-By' => \Ratchet\VERSION
        ));
        */
        
        $response = new Response($code);

        $conn->send((string) $response);
        $conn->close();
    }
}
