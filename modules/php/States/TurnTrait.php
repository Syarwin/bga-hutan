<?php

namespace Bga\Games\Hutan\States;

use Bga\Games\Hutan\Core\Globals;

trait TurnTrait
{
  public function stStartTurn()
  {
    Globals::incTurn();
  }
}
