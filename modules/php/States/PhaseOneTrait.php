<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Helpers\Utils;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Flowers;
use Bga\Games\Hutan\Managers\Players;
use Bga\Games\Hutan\Models\Flower;
use Bga\Games\Hutan\Models\Player;

trait PhaseOneTrait
{
  public function argsChooseFlowerCard()
  {
    return ['cards' => FlowerCards::getInLocation(LOCATION_TABLE)->toArray()];
  }

  public function argsChooseFlowerColor()
  {
    return [
      'colors' => array_map(function ($color) {
        return Utils::colorToClass($color);
      }, ALL_COLORS),
      'flowerCardId' => Players::getCurrent()->getFlowerCardId(),
    ];
  }

  public function argsPlaceFlower()
  {
    $player = Players::getCurrent();
    if (Players::getActiveId() !== $player->getId()) {
      return [];
    }
    $flowerCardId = $player->getFlowerCardId();
    return [
      'flowerCardId' => $flowerCardId,
      'flowerCardCounter' => $player->getFlowerCardCounter(),
      'flowerColor' => Utils::colorToClass($this->getFlowerColor($player, $flowerCardId)),
      'availableCoordinates' => $this->getAvailableCoords($player),
    ];
  }

  private function getFlowerColor(Player $player, int $flowerCardId)
  {
    $cardFlowers = FlowerCards::get($flowerCardId)->getFlowers();
    if (in_array(FLOWER_JOKER, $cardFlowers)) {
      return $player->getFlowerCardColor();
    } else {
      return $cardFlowers[$player->getFlowerCardCounter()];
    }
  }

  public function actChooseFlowerCard(int $id): void
  {
    $player = Players::getCurrent();
    $player->setFlowerCardId($id);
    $player->setFlowerCardCounter(0);
    Notifications::flowerCardChosen($player, $id);
    if (in_array(FLOWER_JOKER, FlowerCards::get($id)->getFlowers())) {
      $this->gamestate->nextState(ST_PHASE_ONE_CHOOSE_FLOWER_COLOR);
    } else {
      $this->gamestate->nextState(ST_PHASE_ONE_PLACE_FLOWERS);
    }
  }

  public function actChooseFlowerColor(string $colorClass): void
  {
    $player = Players::getCurrent();
    $player->setFlowerCardColor(Utils::classToColor($colorClass));
    $this->gamestate->nextState('');
  }

  public function actPlaceFlower(int $x, int $y): void
  {
    $player = Players::getCurrent();
    if (!in_array(['x' => $x, 'y' => $y], $this->getAvailableCoords($player))) {
      throw new \BgaVisibleSystemException(
        "You cannot place a flower at coordinates $x, $y. If you see this - please report as a bug"
      );
    }
    $color = $this->getFlowerColor($player, $player->getFlowerCardId());
    $flower = Flowers::placeFlower($player->getId(), $x, $y, $color);
    Notifications::flowerPlaced($player, $flower);

    $nextFlowerCount = $player->getFlowerCardCounter() + 1;
    $flowerCardFlowers = FlowerCards::get($player->getFlowerCardId())->getFlowers();
    if (count($flowerCardFlowers) > $nextFlowerCount) {
      $player->setFlowerCardCounter($nextFlowerCount);
      $this->gamestate->nextState(ST_PHASE_ONE_PLACE_FLOWERS);
      // TODO: Check if tree is growing
      // $this->gamestate->nextState(ST_PHASE_TWO_CHECK_FOR_GROWN_TREES);
    } else {
      // TODO: Phase 3 should be here
      // $this->gamestate->nextState(ST_PHASE_THREE_...);
      $this->gamestate->nextState(ST_END_OF_TURN_CLEANUP);
    }
  }

  private function getAvailableCoords(Player $player)
  {
    // TODO: add ability to place flowers on top of matching flowers to grow trees. Not possible at the moment
    $placedFlowersCoords = array_map(function (Flower $flower) {
      return $flower->getCoordinates();
    }, $player->getFlowers());

    $availableCoords = [];
    for ($x = 0; $x < 6; $x++) {
      for ($y = 0; $y < 6; $y++) {
        $coords = ['x' => $x, 'y' => $y];
        if (!in_array($coords, $placedFlowersCoords)) { // TODO: Add water as unavailable cell
          $availableCoords[] = $coords;
        }
      }
    }
    return $availableCoords;
  }
}
