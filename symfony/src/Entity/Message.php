<?php

namespace Chs\Messages\Entity;

class Message implements \JsonSerializable {

    protected static int $_id = 0;

    protected int $attempts;
    protected string $system;
    protected string $body;
    protected string $municipality;
    protected int $id;
    protected \DateTimeInterface $date;

    public function __construct(int $attempts, string $municipality, string $body, string $system = ''){
        $this->id = self::$_id++;
        $this->system = $system;
        $this->attempts = $attempts;
        $this->municipality = $municipality;
        $this->body = $body;
        $this->date = new \DateTime();
    }

    /**
     * @return string
     */
    public function getSystem(): string {
        return $this->system;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAttempts(): int {
        return $this->attempts;
    }

    /**
     * @return string
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getMunicipality(): string {
        return $this->municipality;
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string {
        $result = json_encode([
            'id' => $this->id,
            'system' => $this->system,
            'attempts' => $this->attempts,
            'event_time' => $this->date->format(\DateTimeInterface::ISO8601),
            'municipality' => $this->municipality,
            'body' => $this->body
        ]);
        if(false === $result) throw new \UnexpectedValueException('Class Contents Could not be Json Encoded');
        return $result;
    }
}