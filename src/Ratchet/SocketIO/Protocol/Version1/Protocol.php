<?php

namespace Ratchet\SocketIO\Protocol\Version1;

use Ratchet\SocketIO\Protocol\ProtocolInterface;
use Ratchet\SocketIO\Protocol\Version1\Transport;
use Ratchet\SocketIO\Protocol\Version1\Session;
use Ratchet\SocketIO\Protocol\Version1\Connection;
use Ratchet\SocketIO\Protocol\Version1\HandshakeVerifier;
use Ratchet\SocketIO\Http;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;

use Ratchet\MessageInterface;
use Ratchet\WebSocket\Version\RFC6455\Message;
use Ratchet\WebSocket\Version\RFC6455\Frame;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\WebSocket\Encoding\ValidatorInterface;
use Ratchet\WebSocket\Encoding\Validator;



/**
 * The latest version of the WebSocket protocol
 * @link http://tools.ietf.org/html/rfc6455
 * @todo Unicode: return mb_convert_encoding(pack("N",$u), mb_internal_encoding(), 'UCS-4BE');
 */
class Protocol implements ProtocolInterface
{
    //const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

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

    /**
     * A lookup of the valid close codes that can be sent in a frame
     * @var array
     */
    //private $closeCodes = array();

    /**
     * @var \Ratchet\WebSocket\Encoding\ValidatorInterface
     */
    //protected $validator;

    //public function __construct(ValidatorInterface $validator = null)
    public function __construct(array $options = array())
    {
        // Options
        $this->options = array_merge(
            array(
                'heartbeat'         => true,
                'heartbeat_timeout' => 60,
                'close_timeout'     => 60
            ),
            $options
        );

        // Session manager
        $this->sessionManager = new Session\SessionManager();
        
        // Transport manager
        $this->transportManager = new Transport\TransportManager();
        $this->transportManager
            ->enableTransport(new Transport\WebSocket\Transport());
        
        // Handshake verifier
        $this->handshakeVerifier = new HandshakeVerifier();
        /*
        $this->setCloseCodes();

        if (null === $validator) {
            $validator = new Validator;
        }

        $this->validator = $validator;
        */
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

    /**
     * {@inheritdoc}
     */
    protected function handshake(Connection $connection)
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
                    $connection->socketIO->sessionId,
                    ((bool) $this->options['heartbeat']) ? (int) $this->options['heartbeat_timeout'] : '',
                    (int) $this->options['close_timeout'],
                    implode(',', $this->transportManager->getTransportIds())
                )
            )
        );
        $response->end();
        
        return;
    }

    /**
     * @param  \Ratchet\ConnectionInterface                  $conn
     * @param  \Ratchet\MessageInterface                     $coalescedCallback
     * @return \Ratchet\WebSocket\Version\RFC6455\Connection
     */
    public function ___upgradeConnection(ConnectionInterface $conn)
    {
        $upgraded = new Connection($conn);

        /*
        if (!isset($upgraded->WebSocket)) {
            $upgraded->WebSocket = new \StdClass;
        }

        $upgraded->WebSocket->coalescedCallback = $coalescedCallback;
        */

        return $upgraded;
    }
    
    /**
     * @param \Ratchet\WebSocket\Version\RFC6455\Connection $connection
     * @param string                                        $message
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        // Get request
        $request = $connection->socketIO->request;
        
        // Get session id
        $sessionId = $this->sessionManager->getRequestSessionId($request);
        
        // No session id means handshake required
        If (!$sessionId) {
           $session = new Connection($connection);
           $this->handshake($session);
        } else {
            // Get transport
            try {
                $connection->socketIO->transport = $this->transportManager->getRequestTransport(
                    $request
                );
            } catch (\InvalidArgumentException $e) {
                return $this->close($connection);
            }
            
            // Transmit message to transport
            $connection->socketIO->transport->onMessage($connection, $message);
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

    /**
     * @return RFC6455\Message
     */
    public function ___newMessage()
    {
        return new Message;
    }

    /**
     * @param  string|null   $payload
     * @param  bool|null     $final
     * @param  int|null      $opcode
     * @return RFC6455\Frame
     */
    public function ___newFrame($payload = null, $final = null, $opcode = null)
    {
        return new Frame($payload, $final, $opcode);
    }

    /**
     * Used when doing the handshake to encode the key, verifying client/server are speaking the same language
     * @param  string $key
     * @return string
     * @internal
     */
    public function ___sign($key)
    {
        return base64_encode(sha1($key . static::GUID, true));
    }

    /**
     * Determine if a close code is valid
     * @param int|string
     * @return bool
     */
    public function ___isValidCloseCode($val)
    {
        if (array_key_exists($val, $this->closeCodes)) {
            return true;
        }

        if ($val >= 3000 && $val <= 4999) {
            return true;
        }

        return false;
    }

    /**
     * Creates a private lookup of valid, private close codes
     */
    protected function ___setCloseCodes()
    {
        $this->closeCodes[Frame::CLOSE_NORMAL]      = true;
        $this->closeCodes[Frame::CLOSE_GOING_AWAY]  = true;
        $this->closeCodes[Frame::CLOSE_PROTOCOL]    = true;
        $this->closeCodes[Frame::CLOSE_BAD_DATA]    = true;
        //$this->closeCodes[Frame::CLOSE_NO_STATUS]   = true;
        //$this->closeCodes[Frame::CLOSE_ABNORMAL]    = true;
        $this->closeCodes[Frame::CLOSE_BAD_PAYLOAD] = true;
        $this->closeCodes[Frame::CLOSE_POLICY]      = true;
        $this->closeCodes[Frame::CLOSE_TOO_BIG]     = true;
        $this->closeCodes[Frame::CLOSE_MAND_EXT]    = true;
        $this->closeCodes[Frame::CLOSE_SRV_ERR]     = true;
        //$this->closeCodes[Frame::CLOSE_TLS]         = true;
    }
}
