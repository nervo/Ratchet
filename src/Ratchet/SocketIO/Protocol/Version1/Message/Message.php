<?php

namespace Ratchet\SocketIO\Protocol\Version1\Message;

/**
 * Message
 */
class Message implements \Serializable
{
    /**
     * String segment separator
     */
    const STRING_SEGMENT_SEPARATOR = ':';

    /**
     * Type
     *
     * @var null|int
     */
    protected $type = null;

    /**
     * Id
     *
     * @var null|int
     */
    protected $id = null;

    /**
     * Endpoint
     *
     * @var null|string
     */
    protected $endpoint = null;

    /**
     * Data
     *
     * @var null|array
     */
    protected $data = null;

    /**
     * Set type
     *
     * @param  int                                                 $type
     * @return \Ratchet\SocketIO\Protocol\Version1\Message\Message
     */
    public function setType($type)
    {
        $this->type = (int) $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return null|int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get id
     *
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get endpoint
     *
     * @return null|string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get data
     *
     * @return null|array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Serialize
     *
     * @return string
     */
    public function serialize()
    {
        $segments = array(
            $this->getType(),
            $this->getId(),
            $this->getEndpoint()
        );

        $data = $this->getData();

        if ($data) {
            $segments[] = json_encode($data);
        }

        return implode(
            self::STRING_SEGMENT_SEPARATOR,
            $segments
        );
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
         return $this->serialize();
    }

    /**
     * Unserialize
     *
     * @param string $string
     *
     * @return \Ratchet\SocketIO\Protocol\Version1\Message\Message
     */
    public function unserialize($string)
    {
        $segments = explode(
            self::STRING_SEGMENT_SEPARATOR,
            (string) $string,
            4
        );

        $this->type     = (int) $segments[0];
        $this->id       = (int) $segments[1];
        $this->endpoint = (string) $segments[2];
        $this->data     = isset($segments[3]) ? json_decode($segments[3], true) : null;

        return $this;
    }
}
