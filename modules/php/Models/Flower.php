<?php

namespace Bga\Games\Hutan\Models;

use Bga\Games\Hutan\Helpers\DB_Model;
use Bga\Games\Hutan\Helpers\Utils;

class Flower extends DB_Model
{
  protected string $table = 'flowers';
  protected string $primary = 'id';
  protected array $attributes = [
    'id' => 'id',
    'pId' => 'player_id',
    'x' => 'x',
    'y' => 'y',
    'color' => 'color',
  ];
  protected int $id;
  protected int $pId;
  protected array $coordinates;
  protected string $color;

  public function __construct(array $row)
  {
    parent::__construct($row);
    $this->coordinates = ['x' => $row['x'], 'y' => $row['y']];
  }

  public function isBelongToPlayer(int $playerId): bool
  {
    return $this->pId === $playerId;
  }

  public function getCoordinates(): array
  {
    return $this->coordinates;
  }

  public function getColor()
  {
    return $this->color;
  }

  public function jsonSerialize(): array
  {
    return ['id' => $this->id, 'coordinates' => $this->coordinates, 'color' => Utils::colorToClass($this->color)];
  }
}