<?php

namespace Bga\Games\Hutan\Models;

use Bga\Games\Hutan\Core\Globals;

class Board
{
  protected Player $player;
  protected array $cells;
  protected array $cellsZone;
  protected array $zones;

  public function __construct(Player $player)
  {
    $this->player = $player;

    // Compute zones according to boards
    $boads = Globals::getBoards();
    foreach ($boads as $k => [$boardId, $orientation]) {
      $board = BOARDS[$boardId];

      // For each of the 3x3 cell
      foreach ($board as $i => $row) {
        foreach ($row as $j => $zone) {
          // Rotate the cell depending on the orientation
          switch ($orientation) {
            case NW:
              $x = $i;
              $y = $j;
              break;

            case NE:
              $x = $j;
              $y = 2 - $i;
              break;

            case SE:
              $x = 2 - $i;
              $y = 2 - $j;
              break;

            case SW:
              $x = 2 - $j;
              $y = $i;
              break;
          }

          // Add delta depending on index $k
          if ($k == 1 || $k == 2) {
            $y += 3;
          }
          if ($k == 2 || $k == 3) {
            $x += 3;
          }

          // Unique zone id (each board has at most 4 zones)
          if ($zone != WATER) {
            $zoneId = 4 * $k + $zone;
            $this->zones[$zoneId]['cells'][] = ['x' => $x, 'y' => $y];
            $this->cellsZone[$x][$y] = $zoneId;
          } else {
            $this->cellsZone[$x][$y] = WATER;
          }
        }
      }
    }

    $this->refresh();
  }

  public function getZones(): array
  {
    return $this->zones;
  }

  public function refresh()
  {
    // Empty grid
    for ($x = 0; $x < 6; $x++) {
      for ($y = 0; $y < 6; $y++) {
        $this->cells[$x][$y] = [];
      }
    }

    // Add meeples
    foreach ($this->player->getMeeples() as $meeple) {
      $this->cells[$meeple->getX()][$meeple->getY()][] = $meeple;
    }
  }

  public function isEmpty(int $x, int $y): bool
  {
    return empty($this->cells[$x][$y]);
  }
}
