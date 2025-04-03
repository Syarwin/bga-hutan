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
    shuffle(self::$allCards);

    $cardsInDeck = (Players::count() * 2) + 1;
    // 9 decks
    for ($i = 1; $i <= 9; $i++) {
      $values = [];
      for ($k = 0; $k < $cardsInDeck; $k++) {
        $card = array_pop(self::$allCards);
        $values[] = [
          'flower_a' => $card[0],
          'flower_b' => $card[1] ?? null,
          'flower_c' => $card[2] ?? null
        ];
      }
      self::create($values, 'deck' . $i);
    }
  }
}
