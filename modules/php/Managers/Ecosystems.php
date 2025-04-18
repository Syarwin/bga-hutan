<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Models\Meeple;
use Bga\Games\Hutan\Models\Player;

class Ecosystems
{
  public static function getScoresForAllEcosystems(Player $player)
  {
    $cardsInPlay = [1, 11]; // TODO: Get from Globals
    $ecosystems = [];
    foreach ($cardsInPlay as $cardId) {
      $ecosystems[$cardId] = static::getScoresForEcosystem($player, $cardId);
    }
    return $ecosystems;
  }

  private static function getScoresForEcosystem(Player $player, int $cardId): int
  {
    $scores = 0;
    switch ($cardId) {
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
        return static::getScoresCardsOneToFive($player, $cardId);
      case 6:
      case 7:
      case 8:
      case 9:
      case 10:
        return static::getScoresCardsSixToTen($player, $cardId);
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

  private static function getScoresCardsOneToFive(Player $player, int $cardId)
  {
    return 0;
  }

  private static function getScoresCardsSixToTen(Player $player, int $cardId)
  {
    return 0;
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
