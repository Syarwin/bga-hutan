<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Globals;
use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;
use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\Games\Hutan\Helpers\Utils;
use Bga\Games\Hutan\Managers\Meeples;
use Bga\Games\Hutan\Models\Player;

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
      $this->gamestate->jumpToState(ST_PREPARE_MARKET);
    } else {
      $this->activeNextPlayer();
      $this->gamestate->jumpToState(ST_TURN);
    }
  }


  ////////////////////////////////////////////////////////////////
  //  ____  _             _                   _   _             
  // / ___|(_)_ __   __ _| | ___    __ _  ___| |_(_) ___  _ __  
  // \___ \| | '_ \ / _` | |/ _ \  / _` |/ __| __| |/ _ \| '_ \ 
  //  ___) | | | | | (_| | |  __/ | (_| | (__| |_| | (_) | | | |
  // |____/|_|_| |_|\__, |_|\___|  \__,_|\___|\__|_|\___/|_| |_|
  //                |___/                                       
  ////////////////////////////////////////////////////////////////

  public function argsTurn()
  {
    $cards = FlowerCards::getInLocation(LOCATION_TABLE);
    $playableCards = [];
    foreach (Players::getAll() as $pId => $player) {
      $playableCards[$pId] = $cards->filter(fn($card) => $player->canPlayCard($card))->getIds();
    }

    return [
      'cards' => $playableCards,
      'pangolin' => Globals::getPangolinLocation(),
    ];
  }

  public function actTakeTurn(#[JsonParam] array $turn): void
  {
    $player = Players::getCurrent();

    // Choose card
    $cardId = (int) $turn['cardId'];
    $player->setFlowerCardId($cardId);  // TODO: remove ?
    $cardFlowers = $this->getFlowersColors($player, $cardId);
    if ($cardId === 0) {
      Globals::setPangolinLocation($player->getId());
    }
    Notifications::flowerCardChosen($player, $cardId);

    // Choose color if needed
    if (count($cardFlowers) == 1) {
      $player->setJokerColor($turn['colors'][0]); // TODO: remove ?
      $cardFlowers = $turn['colors'];
    }

    // Place flowers
    $flowers = [];
    foreach ($turn['flowers'] as $i => $flower) {
      $flower['color'] = $cardFlowers[$i];
      $flowers[] = $flower;
    }
    $this->verifyParams($flowers, $cardFlowers);

    foreach ($flowers as $flower) {
      $x = $flower['x'];
      $y = $flower['y'];
      $isTree = !$player->board()->isEmpty($x, $y);
      $flowerType = $isTree ? TREE : $flower['color'];
      $flowerOrTree = Meeples::place($player->getId(), $x, $y, $flowerType);
      $isTree ? Notifications::treePlaced($player, $flowerOrTree) : Notifications::flowerPlaced($player, $flowerOrTree);
    }

    $this->gamestate->nextState('');
  }

  private function getFlowersColors(Player $player, int $flowerCardId)
  {
    $cardFlowers = $flowerCardId === 0 ? null : FlowerCards::getSingle($flowerCardId)->getFlowers();
    if ($flowerCardId === 0 || in_array(FLOWER_JOKER, $cardFlowers)) {
      return [$player->getJokerColor()];
    } else {
      return $cardFlowers;
    }
  }
}
