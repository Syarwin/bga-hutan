<?php

namespace Bga\Games\Hutan\Models;

use Bga\Games\Hutan\Helpers\DB_Model;

class FlowerCard extends DB_Model
{
  protected string $table = 'flower_cards';
  protected string $primary = 'card_id';
  protected array $attributes = [
    'id' => 'card_id',
    'location' => 'card_location',
    'state' => 'card_state',
    'flower_a' => ['type', 'str'],
    'flower_b' => ['type', 'str'],
    'flower_c' => ['type', 'str'],
  ];
  protected int $id;
  protected string $location;
  protected int $state;
  protected array $flowers = [];

  public function __construct(array $row)
  {
    parent::__construct($row);
    foreach (['flower_a', 'flower_b', 'flower_c'] as $attribute) {
      if ($row[$attribute]) {
        $this->flowers[] = $row[$attribute];
      }
    }
  }

  public function getId()
  {
    return $this->id;
  }

  public function jsonSerialize(): array
  {
    return ['id' => $this->id, 'flowers' => $this->flowers];
  }
}