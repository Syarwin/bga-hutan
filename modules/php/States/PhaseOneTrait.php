<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Managers\FlowerCards;

trait PhaseOneTrait
{
  public function argsChooseFlowerCard()
  {
    return ['cards' => FlowerCards::getInLocation(LOCATION_TABLE)->toArray()];
  }
}
