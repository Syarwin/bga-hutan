<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Helpers\CachedPieces;
use Bga\Games\Hutan\Helpers\Collection;
use Bga\Games\Hutan\Models\Meeple;

class Meeples extends CachedPieces
{
  protected static string $table = 'meeples';
  protected static string $prefix = 'meeple_';
  protected static array $customFields = ['type', 'player_id', 'x', 'y'];
  protected static null|Collection $datas = null;
  protected static bool $autoremovePrefix = false;
  protected static bool $autoIncrement = true;

  protected static function cast($row)
  {
    return new Meeple($row);
  }

  public static function getUiData(): array
  {
    return self::getAll()->ui();
  }

  public static function setupNewGame()
  {
    $meeples = [];
    $n = Players::count() == 2 ? 2 : 3;
    foreach (ANIMALS as $type) {
      $meeples[] = ['type' => $type, 'nbr' => $n];
    }
    self::create($meeples, LOCATION_RESERVE);
  }

  public static function place(int $pId, int $x, int $y, string $color): Meeple
  {
    return self::singleCreate(['location' => 'board', 'type' => $color, 'player_id' => $pId, 'x' => $x, 'y' => $y]);
  }
}
