<?php

namespace Bga\Games\Hutan\Models;

use Bga\Games\Hutan\Helpers\DB_Model;

class Meeple extends DB_Model
{
  protected string $table = 'meeples';
  protected string $primary = 'meeple_id';
  protected array $attributes = [
    'id' => 'meeple_id',
    'location' => 'meeple_location',
    'state' => 'meeple_state',
    'type' => ['type', 'str'],
    'pId' => ['player_id', 'int']
  ];
  protected int $id;
  protected string $location;
  protected int $state;
  protected string $type;
  protected ?int $pId;

  public function getId()
  {
    return $this->id;
  }
}
