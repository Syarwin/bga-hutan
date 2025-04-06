<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Helpers\Utils;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Meeples;
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
    $currentFlowerColor = $this->getFlowerColor($player, $flowerCardId);
    return [
      'flowerCardId' => $flowerCardId,
      'flowerCardCounter' => $player->getFlowerCardCounter(),
      'flowerColor' => Utils::colorToClass($currentFlowerColor),
      'availableCoordinates' => $this->getAvailableCoords($player, $currentFlowerColor),
    ];
  }

  private function getFlowerColor(Player $player, int $flowerCardId)
  {
    $cardFlowers = FlowerCards::get($flowerCardId)->getFlowers();
    if (in_array(FLOWER_JOKER, $cardFlowers)) {
      return $player->getJokerColor();
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
    $player->setJokerColor(Utils::classToColor($colorClass));
    $this->gamestate->nextState('');
  }

  public function actPlaceFlower(int $x, int $y): void
  {
    $player = Players::getCurrent();
    $flowerCardId = $player->getFlowerCardId();
    $currentFlowerColor = $this->getFlowerColor($player, $flowerCardId);
    if (!in_array(['x' => $x, 'y' => $y], $this->getAvailableCoords($player, $currentFlowerColor))) {
      throw new \BgaVisibleSystemException(
        "You cannot place a flower at coordinates $x, $y. If you see this - please report as a bug"
      );
    }
    $isTree = !$player->board()->isEmpty($x, $y);
    $currentFlowerColor = $isTree ? TREE : $currentFlowerColor;
    $flowerOrTree = Meeples::placeFlower($player->getId(), $x, $y, $currentFlowerColor);
    $isTree ? Notifications::treePlaced($player, $flowerOrTree) : Notifications::flowerPlaced($player, $flowerOrTree);

    $nextFlowerCount = $player->getFlowerCardCounter() + 1;
    $flowerCardFlowers = FlowerCards::getSingle($flowerCardId)->getFlowers();
    if (count($flowerCardFlowers) > $nextFlowerCount) {
      $player->setFlowerCardCounter($nextFlowerCount);
      $this->gamestate->nextState(ST_PHASE_ONE_PLACE_FLOWERS);
    } else {
      // TODO: Phase 3 should be here
      // $this->gamestate->nextState(ST_PHASE_THREE_...);
      $this->gamestate->nextState(ST_END_OF_TURN_CLEANUP);
    }
  }

  private function getAvailableCoords(Player $player, string $currentFlowerColor)
  {
    $board = $player->board();
    $waterSpaces = $board->getWaterSpaces();

    $availableCoords = [];
    for ($x = 0; $x < 6; $x++) {
      for ($y = 0; $y < 6; $y++) {
        $empty = $board->isEmpty($x, $y);
        $itemsAtCell = $board->getItemsAt($x, $y);
        $justOneFlower = count($itemsAtCell) === 1;
        $justOneMatchingFlower = false;
        if ($justOneFlower) {
          /** @var Flower $flowerAtCell */
          $flowerAtCell = $itemsAtCell[0];
          $justOneMatchingFlower = $flowerAtCell->getType() === $currentFlowerColor;
        }
        $coords = ['x' => $x, 'y' => $y];
        if ((!in_array($coords, $waterSpaces) && $empty) || $justOneMatchingFlower) {
          $availableCoords[] = $coords;
        }
      }
    }
    return $availableCoords;
  }
}
