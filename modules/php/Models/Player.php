<?php

namespace Bga\Games\Hutan\Models;

use Bga\Games\Hutan\Game;
use Bga\Games\Hutan\Helpers\Collection;
use Bga\Games\Hutan\Helpers\DB_Model;
use Bga\Games\Hutan\Managers\Meeples;

/*
 * Player: all utility functions concerning a player
 */

class Player extends DB_Model
{
  protected string $table = 'player';
  protected string $primary = 'player_id';
  protected array $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'zombie' => 'player_zombie',
  ];
  protected int $id;

  public function getUiData()
  {
    $data = parent::getUiData();
    return $data;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getPref(int $prefId)
  {
    return Game::get()->getGameUserPreference($this->id, $prefId);
  }

  public function getMeeples(): Collection
  {
    return Meeples::getFiltered($this->id);
  }

  protected Board $board;
  public function board(): Board
  {
    if (!isset($this->board)) {
      $this->board = new Board($this);
    }
    return $this->board;
  }

  // public function getStat($name)
  // {
  //   $name = 'get' . Utils::ucfirst($name);
  //   return Stats::$name($this->id);
  // }
}
