<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Helpers\CachedPieces;
use Bga\Games\Hutan\Helpers\Collection;
use Bga\Games\Hutan\Models\FlowerCard;

include_once dirname(__FILE__) . "/../Materials/Cards.php";

class FlowerCards extends CachedPieces
{
  protected static string $table = 'flower_cards';
  protected static string $prefix = 'card_';
  protected static array $customFields = ['flower_a', 'flower_b', 'flower_c'];
  protected static null|Collection $datas = null;
  protected static bool $autoremovePrefix = false;
  protected static bool $autoIncrement = true;

  private static array $allCards = CARDS;

  protected static function cast($row)
  {
    return new FlowerCard($row);
  }

  public static function getUiData(): array
  {
    return self::getAll()->ui();
  }

  public static function setupNewGame()
  {
    $cards = [];
    shuffle(self::$allCards);
    foreach (self::$allCards as $flowerCardArray) {
      $cards[] = [
        'flower_a' => $flowerCardArray[0],
        'flower_b' => $flowerCardArray[1] ?? null,
        'flower_c' => $flowerCardArray[2] ?? null
      ];
    }

    self::create($cards, 'deck');
  }
}
