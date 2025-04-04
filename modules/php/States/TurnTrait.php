<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Globals;
use Bga\Games\Hutan\Managers\FlowerCards;

trait TurnTrait
{
  public function stPrepareMarket()
  {
    Globals::incTurn();
    FlowerCards::moveDeckToBoard(Globals::getTurn());
    $this->gamestate->nextState('');
  }
}
