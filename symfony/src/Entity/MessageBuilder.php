<?php

namespace Chs\Messages\Entity;

use Chs\Messages\Entity\Faker\Chs;
use Faker\Factory;
use Faker\Generator;

class MessageBuilder {

    protected Generator $faker;

    protected function __construct() {
        $this->faker = Factory::create();
        $this->faker->addProvider(new Chs($this->faker));
    }

    public static function create($muni_limit = null): Message {
        $self = new self;
        return new Message(
            0,
            $self->faker->municipalityCode($muni_limit),
            $self->faker->bs(),
            $self->faker->system(),
        );
    }

    /**
     * @param int $count
     * @return array [Message]
     */
    public static function createMany(int $count, $muni_limit = null) {
        if(1 > $count) throw new \InvalidArgumentException('Must request at least 1');
        $results = [];
        foreach (range(1, $count) as $_) $results[] = self::create($muni_limit);
        return $results;
    }
}