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

  public function stEndOfTurnCleanup()
  {
    $player = Players::getActive();
    FlowerCards::move($player->getFlowerCardId(), LOCATION_DISCARD);
    // Do we need a notification about card being discarded? It will disappear from the UI anyway
    $this->activeNextPlayer();
    $this->gamestate->nextState('');
  }
}
