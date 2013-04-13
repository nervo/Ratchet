<?php

namespace Ratchet\SocketIO\Version;

use Ratchet\ConnectionInterface;
use Ratchet\MessageInterface;
use Ratchet\SocketIO\Version\Version1\HandshakeVerifier;
use Ratchet\WebSocket\Version\RFC6455\Message;
use Ratchet\WebSocket\Version\RFC6455\Frame;

//use Ratchet\WebSocket\Version\RFC6455\Connection;
use Ratchet\SocketIO\Version\Version1\Connection;
use Ratchet\AbstractConnectionDecorator;

use Ratchet\WebSocket\Encoding\ValidatorInterface;
use Ratchet\WebSocket\Encoding\Validator;
use Guzzle\Http\Message\RequestInterface;
//use Ratchet\SocketIO\React\Http\Response;
use Ratchet\SocketIO\React\Http\Response;
use Ratchet\SocketIO\TransportManager;

/**
 * The latest version of the WebSocket protocol
 * @link http://tools.ietf.org/html/rfc6455
 * @todo Unicode: return mb_convert_encoding(pack("N",$u), mb_internal_encoding(), 'UCS-4BE');
 */
class Version1 implements VersionInterface
{
    //const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @var Version1\HandshakeVerifier
     */
    protected $_verifier;

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
    public function __construct()
    {
        $this->_verifier = new HandshakeVerifier;
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
    public function isVersion(RequestInterface $request)
    {
        $segments = $request->getUrl(true)->getPathSegments();

        if (2 > count($segments)) {
            return false;
        }

        if ('socket.io' == $segments[0] && $this->getVersionNumber() === (int) $segments[1]) {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionNumber()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function handshake(RequestInterface $request, AbstractConnectionDecorator $connection, array $options, TransportManager $transporter)
    {
        $options = array_merge(
            array(
                'heartbeat'         => true,
                'heartbeat_timeout' => 60,
                'close_timeout'     => 60
            ),
            $options
        );
        
        $response = new Response($connection);
        $response->writeHead(
            200,
            array(
                'Content-Type' => 'text/plain',
                'access-control-allow-origin' => (string) $request->getHeader('origin'),
                'Access-Control-Allow-Credentials' => 'true'
            )
        );
        $response->write(
            implode(
                ':',
                array(
                    $connection->SocketIO->sessionId,
                    ((bool) $options['heartbeat']) ? (int) $options['heartbeat_timeout'] : '',
                    (int) $options['close_timeout'],
                    $transporter->getSupportedTransportString()
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
    public function upgradeConnection(ConnectionInterface $conn)
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
     * @param \Ratchet\WebSocket\Version\RFC6455\Connection $from
     * @param string                                        $data
     */
    public function onMessage(ConnectionInterface $from, $data)
    {
        $overflow = '';

        if (!isset($from->WebSocket->message)) {
            $from->WebSocket->message = $this->newMessage();
        }

        // There is a frame fragment attached to the connection, add to it
        if (!isset($from->WebSocket->frame)) {
            $from->WebSocket->frame = $this->newFrame();
        }

        $from->WebSocket->frame->addBuffer($data);
        if ($from->WebSocket->frame->isCoalesced()) {
            $frame = $from->WebSocket->frame;

            if (false !== $frame->getRsv1() ||
                false !== $frame->getRsv2() ||
                false !== $frame->getRsv3()
            ) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            if (!$frame->isMasked()) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            $opcode = $frame->getOpcode();

            if ($opcode > 2) {
                if ($frame->getPayloadLength() > 125 || !$frame->isFinal()) {
                    return $from->close($frame::CLOSE_PROTOCOL);
                }

                switch ($opcode) {
                    case $frame::OP_CLOSE:
                        $closeCode = 0;

                        $bin = $frame->getPayload();

                        if (empty($bin)) {
                            return $from->close();
                        }

                        if (strlen($bin) >= 2) {
                            list($closeCode) = array_merge(unpack('n*', substr($bin, 0, 2)));
                        }

                        if (!$this->isValidCloseCode($closeCode)) {
                            return $from->close($frame::CLOSE_PROTOCOL);
                        }

                        if (!$this->validator->checkEncoding(substr($bin, 2), 'UTF-8')) {
                            return $from->close($frame::CLOSE_BAD_PAYLOAD);
                        }

                        return $from->close($frame);
                    break;
                    case $frame::OP_PING:
                        $from->send($this->newFrame($frame->getPayload(), true, $frame::OP_PONG));
                    break;
                    case $frame::OP_PONG:
                    break;
                    default:
                        return $from->close($frame::CLOSE_PROTOCOL);
                    break;
                }

                $overflow = $from->WebSocket->frame->extractOverflow();

                unset($from->WebSocket->frame, $frame, $opcode);

                if (strlen($overflow) > 0) {
                    $this->onMessage($from, $overflow);
                }

                return;
            }

            $overflow = $from->WebSocket->frame->extractOverflow();

            if ($frame::OP_CONTINUE == $frame->getOpcode() && 0 == count($from->WebSocket->message)) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            if (count($from->WebSocket->message) > 0 && $frame::OP_CONTINUE != $frame->getOpcode()) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            $from->WebSocket->message->addFrame($from->WebSocket->frame);
            unset($from->WebSocket->frame);
        }

        if ($from->WebSocket->message->isCoalesced()) {
            $parsed = $from->WebSocket->message->getPayload();
            unset($from->WebSocket->message);

            if (!$this->validator->checkEncoding($parsed, 'UTF-8')) {
                return $from->close(Frame::CLOSE_BAD_PAYLOAD);
            }

            $from->WebSocket->coalescedCallback->onMessage($from, $parsed);
        }

        if (strlen($overflow) > 0) {
            $this->onMessage($from, $overflow);
        }
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
