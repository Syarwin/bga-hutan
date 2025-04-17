<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Globals;
use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;
use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\Games\Hutan\Models\Player;

trait TurnTrait
{
  public function stPrepareMarket()
  {
    $turn = Globals::incTurn();
    $cards = FlowerCards::moveDeckToBoard(Globals::getTurn());
    Notifications::newTurn($turn, $cards);

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

  /**
   * @throws \BgaVisibleSystemException
   */
  public function actTakeTurn(#[JsonParam] array $turn): void
  {
    $player = Players::getCurrent();

    // Choose card
    $cardId = (int)$turn['cardId'];
    $player->setFlowerCardId($cardId);  // We need that for EndOfTurnCleanup state
    if ($cardId === 0) {
      Globals::setPangolinLocation($player->getId());

      if (count($turn['colors']) > 1) {
        throw new \BgaVisibleSystemException(
          "More than one color is sent for Pangolin. That should not be possible"
        );
      }
    }
    $cardFlowers = $turn['colors'];
    Notifications::flowerCardChosen($player, $cardId);

    // Place flowers
    $flowers = [];
    foreach ($turn['flowers'] as $i => $flower) {
      $flower['color'] = $cardFlowers[$i];
      $flowers[] = $flower;
    }
    $this->verifyTurnParams($flowers, $cardFlowers);

    $finishedZonesIdsBeforePlacing = array_keys($player->board()->getFinishedZones());
    foreach ($flowers as $flower) {
      $meeple = $player->board()->addFlower($flower['x'], $flower['y'], $flower['color']);
      Notifications::meeplePlaced($player, $meeple);
    }

    // Animal
    if (isset($turn['animal'])) {
      $this->verifyAnimalParams($player, (int)$turn['animalZone'], $finishedZonesIdsBeforePlacing);

      $i = $turn['animal'];
      [$treeToRemove, $animal] = $player->board()->placeAnimal($flowers[$i]['x'], $flowers[$i]['y']);
      $player->board()->moveTreeToReserve($treeToRemove);
      Notifications::animalPlaced($player, $treeToRemove, $animal);
    }

    // Fertilize
    if (isset($turn['fertilized'])) {
      $this->verifyFertilizedParams($player, $animal, $turn['fertilized']);

      foreach ($turn['fertilized'] as $flower) {
        $meeple = $player->board()->addFlower($flower['x'], $flower['y'], $flower['color']);
        Notifications::meeplePlaced($player, $meeple, true);
      }
    }
    $newScore = $player->updateScores();
//  TODO:  Notifications::newScore($newScore);
    $this->gamestate->nextState('');
  }
}
