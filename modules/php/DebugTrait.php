<?php

namespace Bga\Games\Hutan;

use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;

trait DebugTrait
{
  public function testZones()
  {
    $player = Players::getCurrent();
    var_dump($player->board()->getZones());
  }

  public function tp()
  {
    $card = FlowerCards::getSingle(43);
    $player = Players::getCurrent();
    $board = $player->board();
    var_dump($board->canPlayCard($card));
  }
}
