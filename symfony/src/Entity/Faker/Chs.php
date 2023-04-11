<?php

namespace Chs\Messages\Entity\Faker;

use Faker\Provider\Base;

class Chs extends Base {

    protected $municipalities = [
        'acton.ma.us',
        'billerica.ma.us',
        'boxborough.ma.us',
        'chelsea.ma.us',
        'clinton.ma.us',
        'heartland.ma.us',
        'marblehead.ma.us',
        'pembroke.ma.us',
        'stow.ma.us',
        'tewksbury.ma.us',
        'topsfield.ma.us',
        'truro.ma.us',
        'wayland.ma.us',
        'winthrop.ma.us',
        'oldsaybrook.ct.us',
        'providence.ri.us',
    ];

    public function municipalityCode(): string {
        return self::randomElement($this->municipalities);
    }

}