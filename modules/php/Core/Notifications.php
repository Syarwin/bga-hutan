<?php

namespace Bga\Games\Hutan\Core;

use Bga\Games\Hutan\Game;
use Bga\Games\Hutan\Managers\FlowerCards;
use Bga\Games\Hutan\Models\Meeple;
use Bga\Games\Hutan\Models\Player;

class Notifications
{
  public static function flowerCardChosen(Player $player, int $id)
  {
    $data = ['player' => $player, 'flowerCardId' => $id];
    if ($id === 0) {
      $msg = clienttranslate('${player_name} chooses the pangolin token from the market');
    } else {
      $msg = clienttranslate('${player_name} chooses a flower card (${colors_desc})');
      $data['colors'] = FlowerCards::getSingle($id)->getFlowers();
    }
    self::notifyAll('flowerCardChosen', $msg, $data);
  }

  public static function flowerPlaced(Player $player, Meeple $flower)
  {
    self::notifyAll('meeplePlaced', clienttranslate('${player_name} places a ${color_desc} on his board (${coords})'), [
      'player' => $player,
      'meeple' => $flower,
      'color' => $flower->getType(),
      'coords' => $flower->getNotifCoords()
    ]);
  }

  public static function treePlaced(Player $player, Meeple $tree)
  {
    self::notifyAll('meeplePlaced', clienttranslate('${player_name} places a tree on his board (${coords})'), [
      'player' => $player,
      'meeple' => $tree,
      'coords' => $tree->getNotifCoords()
    ]);
  }

  public static function pangolinMovedToMarket(Player $player)
  {
    $msg = clienttranslate('${player_name} places the pangolin token back to the market');
    self::notifyAll('pangolinMovedToMarket', $msg, ['player' => $player]);
  }

  ///////////////////////////////////////////////////////////////////////////////////
  //   ____                      _        __  __      _   _               _     
  //  / ___| ___ _ __   ___ _ __(_) ___  |  \/  | ___| |_| |__   ___   __| |___ 
  // | |  _ / _ \ '_ \ / _ \ '__| |/ __| | |\/| |/ _ \ __| '_ \ / _ \ / _` / __|
  // | |_| |  __/ | | |  __/ |  | | (__  | |  | |  __/ |_| | | | (_) | (_| \__ \
  //  \____|\___|_| |_|\___|_|  |_|\___| |_|  |_|\___|\__|_| |_|\___/ \__,_|___/
  ///////////////////////////////////////////////////////////////////////////////////

  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data, true);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    //    self::updateIfNeeded($data, $name, "private");
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  protected static function pnotify($player, $name, $msg, $data)
  {
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function refreshUI($pId, $datas)
  {
    // // Keep only the thing that matters
    $fDatas = [
      'players' => $datas['players'],
      'scribbles' => $datas['scribbles'],
      'constructionCards' => $datas['constructionCards'],
    ];

    self::notify($pId, 'refreshUI', '', [
      'datas' => $fDatas,
    ]);
  }

  public static function flush()
  {
    self::notifyAll('flush', '', []);
  }

  ///////////////////////////////////////////////////////////////
  //  _   _           _       _            _
  // | | | |_ __   __| | __ _| |_ ___     / \   _ __ __ _ ___
  // | | | | '_ \ / _` |/ _` | __/ _ \   / _ \ | '__/ _` / __|
  // | |_| | |_) | (_| | (_| | ||  __/  / ___ \| | | (_| \__ \
  //  \___/| .__/ \__,_|\__,_|\__\___| /_/   \_\_|  \__, |___/
  //       |_|                                      |___/
  ///////////////////////////////////////////////////////////////

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data, $public = false)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }
    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }
    if (isset($data['player3'])) {
      $data['player_name3'] = $data['player3']->getName();
      $data['player_id3'] = $data['player3']->getId();
      unset($data['player3']);
    }

    $colorNames = [
      FLOWER_BLUE => clienttranslate('blue flower'),
      FLOWER_YELLOW => clienttranslate('yellow flower'),
      FLOWER_RED => clienttranslate('red flower'),
      FLOWER_WHITE => clienttranslate('white flower'),
      FLOWER_GREY => clienttranslate('grey flower'),
      FLOWER_JOKER => clienttranslate('multicolored flower'),
    ];


    if (isset($data['color'])) {
      $data['color_desc'] = [
        'log' => '${color_icon}${color_name}',
        'args' => [
          'i18n' => ['color_name'],
          'color_name' => $colorNames[$data['color']],
          'color_type' => $data['color'],
          'color_icon' => '',
          'preserve' => ['color_type'],
        ],
      ];
    }

    foreach (['colors'] as $key) {
      if (isset($data[$key]) && !empty($data[$key])) {
        $args = [];
        $i = 0;
        foreach ($data[$key] as $type) {
          $args['i18n'][] = 'color_' . $i;
          $args['color_' . $i] = [
            'log' => '${color_icon}${color_name}',
            'args' => [
              'i18n' => ['color_name'],
              'color_name' => $colorNames[$type],
              'color_type' => $type,
              'color_icon' => '',
              'preserve' => ['color_type'],
            ],
          ];
          $i++;
        }
        $logs = [
          0 => '',
          1 => '${color_0}',
          2 => clienttranslate('${color_0} and ${color_1}'),
          3 => clienttranslate('${color_0}, ${color_1} and ${color_2}'),
        ];
        $data[$key . '_desc'] = [
          'log' => $logs[$i],
          'args' => $args,
        ];
        $data['i18n'][] = $key . '_desc';
      }
    }
  }
}
