<?php

namespace Ratchet\SocketIO\Message;

/**
 * Connect message
 */
class ConnectMessage extends Message
{
    /**
     * Constructor
     * 
     * @param string     $endpoint
     * @param int        $id
     */
    public function __construct($endpoint = null, $id = null)
    {
        // Endpoint
        $this->endpoint = is_null($endpoint) ? null : (string) $endpoint;
        
        // Id
        $this->id = is_null($id) ? null : (int) $id;
    }
    
    /**
     * From array
     * 
     * @param array $array
     */
    public static function fromArray(array $array)
    {
        return new self(
            isset($array[2]) ? $array[2] : null,
            isset($array[1]) ? $array[1] : null
        );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return self::TYPE_CONNECT;
    }
}
