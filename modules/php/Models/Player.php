<?php

namespace Bga\Games\Hutan\Models;

use Bga\Games\Hutan\Game;
use Bga\Games\Hutan\Helpers\Collection;
use Bga\Games\Hutan\Helpers\DB_Model;
use Bga\Games\Hutan\Managers\Flowers;
use Bga\Games\Hutan\Managers\Meeples;
use Bga\Games\Hutan\Managers\Players;

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
    'flowerCardId' => 'player_flower_card_id',
    'flowerCardCounter' => 'player_flower_card_counter',
    'flowerCardColor' => 'player_flower_color'
  ];
  protected int $id;
  protected int $flowerCardId;
  protected int $flowerCardCounter;
  protected string $flowerCardColor;

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['flowers'] = $this->getFlowers();
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

  public function getFlowerCardId(): int
  {
    return $this->flowerCardId;
  }

  public function setFlowerCardId(int $id): void
  {
    $this->flowerCardId = $id;
    Players::setFlowerCardId($this->id, $id);
  }

  public function getFlowerCardCounter(): int
  {
    return $this->flowerCardCounter;
  }

  public function setFlowerCardCounter(int $count): void
  {
    $this->flowerCardCounter = $count;
    Players::setFlowerCardCounter($this->id, $count);
  }

  public function getFlowerCardColor(): string
  {
    return $this->flowerCardColor;
  }

  public function setFlowerCardColor(string $color): void
  {
    $this->flowerCardColor = $color;
    Players::setFlowerCardColor($this->id, $color);
  }

  public function getFlowers()
  {
    return Flowers::getByPlayer($this->id)->toArray();
  }

  // public function getStat($name)
  // {
  //   $name = 'get' . Utils::ucfirst($name);
  //   return Stats::$name($this->id);
  // }
}
