<?php

namespace Bga\Games\Hutan\Helpers;

use Bga\Games\Hutan\Game;

class UserException extends \BgaUserException
{
  public function __construct($str)
  {
    parent::__construct(Game::get()::translate($str));
  }
}
