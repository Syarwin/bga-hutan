<?php

namespace Bga\Games\Hutan\Managers;

use Bga\Games\Hutan\Core\Globals;
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
    return self::getInLocation(LOCATION_TABLE)->ui();
  }

  public static function setupNewGame()
  {
    shuffle(self::$allCards);

    $isSolo = Globals::isSolo();
    $cardsInDeck = $isSolo ? 3 : (Players::count() * 2) - 1;
    // 18 or 9 stacks / decks
    $amountOfDecks = $isSolo ? 18 : 9;
    for ($i = 1; $i <= $amountOfDecks; $i++) {
      $values = [];
      for ($k = 0; $k < $cardsInDeck; $k++) {
        $card = array_pop(self::$allCards);
        $values[] = [
          'flower_a' => $card[0],
          'flower_b' => $card[1] ?? null,
          'flower_c' => $card[2] ?? null
        ];
      }
      self::create($values, LOCATION_DECK . $i);
    }
  }

  public static function moveDeckToBoard(int $deckNumber): Collection
  {
    return self::moveAllInLocation(LOCATION_DECK . $deckNumber, LOCATION_TABLE);
  }

  public static function get(int $id, bool $raiseExceptionIfNotEnough = true): FlowerCard
  {
    return parent::get($id, $raiseExceptionIfNotEnough);
  }
}
