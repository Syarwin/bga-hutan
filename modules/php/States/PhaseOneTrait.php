<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Helpers\Utils;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;
use Bga\Games\Hutan\Models\Player;

trait PhaseOneTrait
{
  public function argsChooseFlowerCard()
  {
    return ['cards' => FlowerCards::getInLocation(LOCATION_TABLE)->toArray()];
  }

  public function argsChooseFlowerColor()
  {
    return [
      'colors' => array_map(function ($color) {
        return Utils::colorToClass($color);
      }, ALL_COLORS),
      'flowerCardId' => Players::getCurrent()->getFlowerCardId(),
    ];
  }

  public function argsPlaceFlowers()
  {
    $player = Players::getCurrent();
    $flowerCardId = $player->getFlowerCardId();
    return [
      'flowerCardId' => $flowerCardId,
      'flowerCardCounter' => $player->getFlowerCardCounter(),
      'flowerColor' => $this->getFlowerColor($player, $flowerCardId),
    ];
  }

  private function getFlowerColor(Player $player, int $flowerCardId)
  {
    $cardFlowers = FlowerCards::get($flowerCardId)->getFlowers();
    if (in_array(FLOWER_JOKER, $cardFlowers)) {
      return $player->getFlowerCardColor();
    } else {
      return $cardFlowers[$player->getFlowerCardCounter()];
    }
  }

  public function actChooseFlowerCard(int $id): void
  {
    $player = Players::getCurrent();
    $player->setFlowerCardId($id);
    $player->setFlowerCardCounter(0);
    Notifications::flowerCardChosen($player, $id);
    if (in_array(FLOWER_JOKER, FlowerCards::get($id)->getFlowers())) {
      $this->gamestate->nextState(ST_PHASE_ONE_CHOOSE_FLOWER_COLOR);
    } else {
      $this->gamestate->nextState(ST_PHASE_ONE_PLACE_FLOWERS);
    }
  }

  public function actChooseFlowerColor(string $colorClass): void
  {
    $player = Players::getCurrent();
    $player->setFlowerCardColor(Utils::classToColor($colorClass));
    $this->gamestate->nextState('');
  }
}
