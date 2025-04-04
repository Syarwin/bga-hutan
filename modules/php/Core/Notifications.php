<?php

namespace Bga\Games\Hutan\Core;

use Bga\Games\Hutan\Game;
use Bga\Games\Hutan\Models\Player;

class Notifications
{
  public static function flowerCardChosen(Player $player, int $id)
  {
    // TODO: Parametrise according to card flowers
    // must be mapped to css classes .icon-flower-blue, .icon-flower-white, etc. Use Utils::colorToClass()
    $iconsText = '{icon-flower-blue}, {icon-flower-white}';
    $msg = str_replace('{icons}', $iconsText, clienttranslate('${player_name} chooses a card with {icons}'));
    self::notifyAll('flowerCardChosen', $msg, ['player' => $player, 'flowerCardId' => $id]);
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
    self::updateIfNeeded($data, $name, "private");
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
  }
}
