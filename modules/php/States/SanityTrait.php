<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Managers\Players;
use Bga\Games\Hutan\Models\Meeple;
use Bga\Games\Hutan\Models\Player;

trait SanityTrait
{
  /**
   * @throws \BgaVisibleSystemException
   */
  public function verifyTurnParams(array $flowers, array $cardFlowers)
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

    $coords = array_map(fn($flower) => $flower['x'] . "," . $flower['y'], $flowers);
    if (count($coords) !== count(array_unique($coords))) {
      throw new \BgaVisibleSystemException("You cannot place two flowers at the same position in a single turn");
    }

    // Each flower should have either x+-1 from one another or y+-1
    if (count($flowers) > 1) {
      foreach ($flowers as $flower) {
        $x = $flower['x'];
        $y = $flower['y'];
        $adjacentFlowers = array_filter($flowers, function ($flower) use ($x, $y) {
          return abs($flower['x'] - $x) + abs($flower['y'] - $y) === 1;
        });
        if (count($adjacentFlowers) === 0) {
          throw new \BgaVisibleSystemException(
            "You cannot place flowers which are not adjacent orthogonally to any other flower from this card"
          );
        }
      }
    }

    $playerBoard = Players::getCurrent()->board();
    foreach ($flowers as $flower) {
      $x = $flower['x'];
      $y = $flower['y'];
      $itemsAtCoords = $playerBoard->getItemsAt($x, $y);
      if (count($itemsAtCoords) > 1) {
        throw new \BgaVisibleSystemException(
          "You cannot place a flower at $x, $y, it's already fully occupied"
        );
      } else if (count($itemsAtCoords) === 1) {
        /** @var Meeple $flowerAtCoords */
        $flowerAtCoords = $itemsAtCoords[0];
        if ($flowerAtCoords->getUiData()['type'] !== $flower['color']) {
          throw new \BgaVisibleSystemException(
            "You cannot place a flower at $x, $y because their colors don't match"
          );
        }
      }
    }

    if ($playerBoard->getAmountOfMeeples() > 0) {
      $foundAdjacent = false;
      foreach ($flowers as $flower) {
        $x = $flower['x'];
        $y = $flower['y'];
        foreach ([[$x, $y], [$x - 1, $y], [$x + 1, $y], [$x, $y - 1], [$x, $y + 1]] as [$adjacentX, $adjacentY]) {
          $itemsAtCoords = $playerBoard->getItemsAt($adjacentX, $adjacentY);
          if (!is_null($itemsAtCoords) && count($itemsAtCoords) > 0) {
            $foundAdjacent = true;
            break;
          }
        }
      }
      if (!$foundAdjacent) {
        throw new \BgaVisibleSystemException(
          "New flowers must be adjacent to, or on top of, Flowers you already placed"
        );
      }
    }
  }

  /**
   * @throws \BgaVisibleSystemException
   */
  public function verifyAnimalParams(int $requestedZone, Player $player, array $finishedZonesIdsBeforePlacing): void
  {
    $zonesIdsFinishedThisTurn = array_filter(
      array_keys($player->board()->getFinishedZones()),
      function ($zoneId) use ($finishedZonesIdsBeforePlacing) {
        return !in_array($zoneId, $finishedZonesIdsBeforePlacing);
      }
    );

    if (!in_array($requestedZone, array_keys($player->board()->getFinishedZones()))) {
      throw new \BgaVisibleSystemException(
        "Unable to place an animal: Requested animal zone is not finished yet"
      );
    }
    if (!in_array($requestedZone, $zonesIdsFinishedThisTurn)) {
      throw new \BgaVisibleSystemException(
        "Unable to place an animal: Requested animal zone was finished before this turn"
      );
    }
  }
}
