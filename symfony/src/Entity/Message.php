<?php

namespace Chs\Messages\Entity;

class Message implements \JsonSerializable {

    protected $attempts;
    protected $body;
    protected $municipality;

    public function __construct($attempts, $municipality, $body){
        $this->attempts = $attempts;
        $this->municipality = $municipality;
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getAttempts() {
        return $this->attempts;
    }

    /**
     * @return mixed
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getMunicipality() {
        return $this->municipality;
    }


    public function jsonSerialize(): string {
        $result = json_encode([
            'attempts' => $this->attempts,
            'municipality' => $this->municipality,
            'body' => $this->body
        ]);
        if(false === $result) throw new \UnexpectedValueException('Class Contents Could not be Json Encoded');
        return $result;
    }
}