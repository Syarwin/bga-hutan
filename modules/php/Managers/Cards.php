<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Helpers\CachedDB_Manager;
use Bga\Games\Hutan\Models\FlowerCard;

include_once dirname(__FILE__) . "/../Materials/Cards.php";

class Cards extends CachedDB_Manager
{
  protected static string $table = 'cards';
  protected static string $primary = 'card_id';
  private static array $allCards = CARDS;

  protected static function cast($row)
  {
    return new FlowerCard($row);
  }

  public static function setupNewGame()
  {
    $query = self::DB()->multipleInsert([
      'flower_a',
      'flower_b',
      'flower_c',
    ]);
    shuffle(self::$allCards);
    foreach (self::$allCards as $flowerCardArray) {
      if (count($flowerCardArray) < 3) {
        $flowerCardArray = [$flowerCardArray[0], $flowerCardArray[1] ?? null, $flowerCardArray[2] ?? null];
      }
      $values[] = $flowerCardArray;
    }
    $query->values($values);
  }
}
