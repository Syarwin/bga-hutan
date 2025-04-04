<?php

namespace Bga\Games\Hutan;

use Bga\Games\Hutan\Managers\Players;

trait DebugTrait
{
  public function testZones()
  {
    $player = Players::getCurrent();
    var_dump($player->board()->getZones());
  }
}
