<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Globals;
use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;

trait TurnTrait
{
  public function stPrepareMarket()
  {
    Globals::incTurn();
    FlowerCards::moveDeckToBoard(Globals::getTurn());
    $pangolinHolder = Globals::getPangolinLocation();
    Globals::setPangolinPlayedThisTurn(false);
    $this->gamestate->changeActivePlayer($pangolinHolder);
    $this->gamestate->nextState('');
  }

  public function stEndOfTurnCleanup()
  {
    $player = Players::getActive();
    Players::resetCounters();
    $flowerCardId = $player->getFlowerCardId();
    if (!Globals::isPangolinPlayedThisTurn() && $flowerCardId === 0) {
      Globals::setPangolinPlayedThisTurn(true);
    } else {
      FlowerCards::move($flowerCardId, LOCATION_DISCARD);
    }
    if (Globals::getPangolinLocation() === $player->getId() && !Globals::isPangolinPlayedThisTurn()) {
      Globals::setPangolinLocation(LOCATION_TABLE);
      Notifications::pangolinMovedToMarket($player);
    }
    // Do we need a notification about a flower card being discarded? It will disappear from the UI anyway
    $flowerCardsLeft = FlowerCards::getInLocation(LOCATION_TABLE);
    if ($flowerCardsLeft->count() === 0 && Globals::getPangolinLocation() !== LOCATION_TABLE) {
      // End of round
      $this->gamestate->nextState(ST_PREPARE_MARKET);
    } else {
      $this->activeNextPlayer();
      $this->gamestate->nextState(ST_PHASE_ONE_CHOOSE_FLOWER_CARD);
    }
  }
}
