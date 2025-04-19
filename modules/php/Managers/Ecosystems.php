<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Helpers\Utils;
use Bga\Games\Hutan\Models\Meeple;
use Bga\Games\Hutan\Models\Player;

class Ecosystems
{
  /**
   * @throws \BgaVisibleSystemException
   */
  public static function getScoresForAllEcosystems(Player $player): array
  {
    $cardsInPlay = [6, 7, 8, 9, 10]; // TODO: Get from Globals
    $ecosystems = [];
    foreach ($cardsInPlay as $cardId) {
      $ecosystems[$cardId] = static::getScoresForEcosystem($player, $cardId);
    }
    return $ecosystems;
  }

  /**
   * @throws \BgaVisibleSystemException
   */
  private static function getScoresForEcosystem(Player $player, int $cardId): int
  {
    $scores = 0;
    switch ($cardId) {
      case 1:
        return static::getScoresCompletedArea($player, FLOWER_RED);
      case 2:
        return static::getScoresCompletedArea($player, FLOWER_YELLOW);
      case 3:
        return static::getScoresCompletedArea($player, FLOWER_BLUE);
      case 4:
        return static::getScoresCompletedArea($player, FLOWER_GREY);
      case 5:
        return static::getScoresCompletedArea($player, FLOWER_WHITE);
      case 6:
        return static::getScoresTreesOnTopOf($player, FLOWER_RED);
      case 7:
        return static::getScoresTreesOnTopOf($player, FLOWER_YELLOW);
      case 8:
        return static::getScoresTreesOnTopOf($player, FLOWER_BLUE);
      case 9:
        return static::getScoresTreesOnTopOf($player, FLOWER_GREY);
      case 10:
        return static::getScoresTreesOnTopOf($player, FLOWER_WHITE);
      case 11:
        $trees = $player->getTrees();
        /** @var Meeple $tree */
        foreach ($trees as $tree) {
          if (in_array($tree->getCoords(), static::getOuterEdges())) {
            $scores += 2;
          }
        }
        return $scores;
      case 12:
        return 0; // TODO
      default:
        throw new \BgaVisibleSystemException("Unknown Ecosystem card $cardId");
    }
  }

  /**
   * @throws \BgaVisibleSystemException
   */
  private static function getScoresCompletedArea(Player $player, string $flowerColor): int
  {
    $result = 0;
    $completedAreas = $player->board()->getCompletedAreas();
    foreach ($completedAreas as $area) {
      $flowerColors = Utils::getFlowerColorsForZone($player, $area);
      $uniqueColors = array_unique($flowerColors);
      if (count($uniqueColors) === 1 && $uniqueColors[0] === $flowerColor) {
        $result += 3;
      }
    }
    return $result;
  }

  /**
   * @throws \BgaVisibleSystemException
   */
  private static function getScoresTreesOnTopOf(Player $player, string $flowerColor)
  {
    $result = 0;
    $trees = $player->getTrees();
    if ($trees->count() > 0) {
      foreach ($trees as $tree) {
        $treeCoords = $tree->getCoords();
        $x = $treeCoords['x'];
        $y = $treeCoords['y'];
        $itemsAtCoords = $player->board()->getItemsAt($x, $y);
        $flowersAtCoords = array_filter($itemsAtCoords, function ($meeple) {
          return in_array($meeple->getType(), ALL_COLORS);
        });
        if (count($flowersAtCoords) > 1) {
          throw new \BgaVisibleSystemException("Ecosystems: found more than 1 flower at coordinates $x, $y");
        } else if (count($flowersAtCoords) === 0) {
          throw new \BgaVisibleSystemException("Ecosystems: No flowers found under a tree at coordinates $x, $y");
        } else {
          if ($itemsAtCoords[0]->getType() === $flowerColor) {
            $result += 2;
          }
        }
      }
    }
    return $result;
  }

  private static function getOuterEdges(): array
  {
    $edges = [];
    for ($x = 0; $x < 6; $x++) {
      for ($y = 0; $y < 6; $y++) {
        if ($x === 0 || $x === 5 || $y === 0 || $y === 5) {
          $edges[] = ['x' => $x, 'y' => $y];
        }
      }
    }
    return $edges;
  }
}
