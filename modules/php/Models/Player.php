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
  ];
  protected int $id;
  protected int $flowerCardId;

  protected Board $board;

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

  public function getFlowers()
  {
    return $this->getMeeples()->where('type', ALL_COLORS);
  }

  public function getTrees(): Collection
  {
    return $this->getMeeples()->where('type', TREE);
  }

  public function getAnimals(): Collection
  {
    return $this->getMeeples()->where('type', ANIMALS);
  }

  public function board(): Board
  {
    if (!isset($this->board)) {
      $this->board = new Board($this);
    }
    return $this->board;
  }

  public function canPlayCard(FlowerCard $card): bool
  {
    return $this->board()->canPlayCard($card);
  }

  public function updateScores(): array
  {
    $newScores = $this->getScores();
    $this->setScore($newScores['overall']);
    return $newScores;
  }

  public function getScores(): array
  {
    // Trees
    $trees = count($this->getTrees()) * 2;

    // Animals
    $animals = 0;

    // Completed areas
    $completedAreas = 0;

    // Unfinished & mixed areas
    $unfinished = 0;
    $mixed = 0;
    $unfinishedAndMixed = $unfinished + $mixed;

    $overall = $trees;
    return [
      'trees' => $trees,
      'animals' => $animals,
      'comletedAreas' => $completedAreas,
      'unfinishedAndMixed' => $unfinishedAndMixed,
      'overall' => $overall,
    ];
  }

  // public function getStat($name)
  // {
  //   $name = 'get' . Utils::ucfirst($name);
  //   return Stats::$name($this->id);
  // }
}
