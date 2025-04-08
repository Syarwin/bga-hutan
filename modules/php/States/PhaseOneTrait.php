<?php

namespace Bga\Games\Hutan\States;

use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Helpers\Utils;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Meeples;
use Bga\Games\Hutan\Managers\Players;
use Bga\Games\Hutan\Models\Meeple;
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

  public function argsPlaceFlowers()
  {
    $player = Players::getCurrent();
    if (Players::getActiveId() !== $player->getId()) {
      return [];
    }
    $flowerCardId = $player->getFlowerCardId();
    $flowerCard = FlowerCards::get($flowerCardId)->getUiData();
    $allFlowerColors = array_unique($this->getFlowersColors($player, $flowerCardId));
    $coords = [];
    foreach ($allFlowerColors as $color) {
      $coords[$color] = $this->getAvailableCoords($player, $color);
    }
    return [
      'flowerCard' => $flowerCard,
      'availableCoordinates' => $coords,
    ];
  }

  private function getFlowersColors(Player $player, int $flowerCardId)
  {
    $cardFlowers = FlowerCards::get($flowerCardId)->getFlowers();
    if (in_array(FLOWER_JOKER, $cardFlowers)) {
      return [$player->getJokerColor()];
    } else {
      return $cardFlowers;
    }
  }

  public function actChooseFlowerCard(int $id): void
  {
    $player = Players::getCurrent();
    $player->setFlowerCardId($id);
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

  /**
   * @throws \BgaVisibleSystemException
   */
  public function actPlaceFlowers(#[JsonParam] array $flowers): void
  {
    $player = Players::getCurrent();
    $flowerCardId = $player->getFlowerCardId();
    $flowers = array_map(function ($flower) {
      return [...$flower, 'color' => Utils::classToColor($flower['color'])];
    }, $flowers);
    $cardFlowers = $this->getFlowersColors($player, $flowerCardId);

    $this->verifyParams($flowers, $cardFlowers);

    // Actual placing
    foreach ($flowers as $flower) {
      $x = $flower['x'];
      $y = $flower['y'];
      $isTree = !$player->board()->isEmpty($x, $y);
      $flowerType = $isTree ? TREE : $flower['color'];
      $flowerOrTree = Meeples::place($player->getId(), $x, $y, $flowerType);
      $isTree ? Notifications::treePlaced($player, $flowerOrTree) : Notifications::flowerPlaced($player, $flowerOrTree);
    }

    // TODO: Phase 3 should be here
    // $this->gamestate->nextState(ST_PHASE_THREE_...);
    $this->gamestate->nextState('');
  }

  private function getAvailableCoords(Player $player, string $flowerColor)
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
          /** @var Meeple $flowerAtCell */
          $flowerAtCell = $itemsAtCell[0];
          $justOneMatchingFlower = $flowerAtCell->getType() === $flowerColor;
        }
        $coords = ['x' => $x, 'y' => $y];
        if ((!in_array($coords, $waterSpaces) && $empty) || $justOneMatchingFlower) {
          $availableCoords[] = $coords;
        }
      }
    }
    return $availableCoords;
  }

  /**
   * @throws \BgaVisibleSystemException
   */
  private function verifyParams(array $flowers, array $cardFlowers)
  {
    $flowersCount = count($flowers);
    $cardFlowersCount = count($cardFlowers);
    if ($flowersCount !== $cardFlowersCount) {
      throw new \BgaVisibleSystemException(
        "Incorrect amount of flowers: expected $cardFlowersCount, actual $flowersCount"
      );
    }

    foreach ($flowers as $i => $flower) {
      $color = $flower['color'];
      $cardFlowerColor = $cardFlowers[$i];
      if ($color !== $cardFlowers[$i]) {
        throw new \BgaVisibleSystemException(
          "Incorrect flowers received: expected $cardFlowerColor, actual $color at index $i"
        );
      }
    }

    $coords = array_map(function ($flower) {
      return "${flower['x']},${flower['y']}";
    }, $flowers);
    if (count($coords) !== count(array_unique($coords))) {
      throw new \BgaVisibleSystemException("You cannot place two flowers at the same position in a single turn");
    }

    // Each flower should have either x+-1 from one another or y+-1
    foreach ($flowers as $flower) {
      $x = $flower['x'];
      $y = $flower['y'];
      $otherFlowers = array_filter($flowers, function ($flower) use ($x, $y) {
        return $flower['x'] !== $x || $flower['y'] !== $y;
      });
      $adjacentFlowers = array_filter($otherFlowers, function ($flower) use ($x, $y) {
        return abs($flower['x'] - $x) + abs($flower['y'] - $y) === 1;
      });
      if (count($adjacentFlowers) === 0) {
        throw new \BgaVisibleSystemException(
          "You cannot place flowers which are not adjacent orthogonally to any other flower from this card"
        );
      }
    }
  }
}
