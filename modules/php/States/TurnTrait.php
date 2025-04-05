<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Globals;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;

trait TurnTrait
{
  public function stPrepareMarket()
  {
    Globals::incTurn();
    FlowerCards::moveDeckToBoard(Globals::getTurn());
    Players::resetCounters();
    $this->gamestate->nextState('');
  }
}
