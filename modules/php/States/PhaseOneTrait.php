<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Notifications;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Managers\Players;

trait PhaseOneTrait
{
  public function argsChooseFlowerCard()
  {
    return ['cards' => FlowerCards::getInLocation(LOCATION_TABLE)->toArray()];
  }

  public function actChooseFlowerCard(int $id): void
  {
    $player = Players::getCurrent();
    $player->setFlowerCardId($id);
    Notifications::flowerCardChosen($player, $id);
    if (in_array(FLOWER_JOKER, FlowerCards::get($id)->getFlowers())) {
      $this->gamestate->nextState(ST_PHASE_ONE_CHOOSE_FLOWER_COLOR);
    } else {
      $this->gamestate->nextState(ST_PHASE_ONE_PLACE_FLOWERS);
    }
  }
}
