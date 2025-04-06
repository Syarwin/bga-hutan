<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Helpers\CachedDB_Manager;
use Bga\Games\Hutan\Models\Flower;

class Flowers extends CachedDB_Manager
{
  protected static string $table = 'flowers';
  protected static string $primary = 'id';

  protected static function cast($row)
  {
    return new Flower($row);
  }

  public static function placeFlower(int $pId, int $x, int $y, string $color)
  {
    self::DB()->insert(['player_id' => $pId, 'x' => $x, 'y' => $y, 'color' => $color]);
    $id = self::DB()->select(['id'])->orderBy('id', 'DESC')->limit(1)->get(true, false, false)['id'];
    return self::get((int)$id);
  }

  public static function getByPlayer(int $pId)
  {
    self::invalidate();
    return self::getAll()->filter(function (Flower $flower) use ($pId) {
      return $flower->isBelongToPlayer($pId);
    });
  }

  public static function getUiData(int $pId): array
  {
    return self::getByPlayer($pId)->ui();
  }
}
