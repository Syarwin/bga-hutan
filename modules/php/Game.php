<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Hutan implementation : © Timothée (Tisaac) Pecatte <tim.pecatte@gmail.com>, Pavel Kulagin (KuWizard) <kuzwiz@mail.ru>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * hutan.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

namespace Bga\Games\Hutan;

use Bga\Games\Hutan\Core\Globals;
use Bga\Games\Hutan\Managers\Players;
use Bga\Games\Hutan\States\TurnTrait;
use Bga\Games\Hutan\Core\Stats;

require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

class Game extends \Table
{
  use TurnTrait;

  public static $instance = null;

  function __construct()
  {
    parent::__construct();
    self::$instance = $this;
    self::initGameStateLabels([]);
  }

  public static function get()
  {
    return self::$instance;
  }

  protected function getGameName()
  {
    return 'hutan';
  }

  /*
   * setupNewGame:
   */
  protected function setupNewGame($players, $options = [])
  {
    Stats::setupNewGame();
    Players::setupNewGame($players, $options);
    Globals::setupNewGame($players, $options);
    $this->activeNextPlayer();
  }

  /*
   * getAllDatas:
   */
  public function getAllDatas(): array
  {
    return [
      'boards' => Globals::getBoards(),
      'players' => Players::getUiData(),
    ];
  }

  /*
   * getGameProgression:
   */
  function getGameProgression()
  {
    return 51;
  }

  ///////////////////////////
  //// DEBUG FUNCTIONS //////
  ///////////////////////////

  ////////////////////////////////////
  ////////////   Zombie   ////////////
  ////////////////////////////////////
  /*
   * zombieTurn:
   *   This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
   *   You can do whatever you want in order to make sure the turn of this player ends appropriately
   */
  public function zombieTurn($state, $active_player): void
  {
    switch ($state['name']) {
        // TODO
    }
  }

  /////////////////////////////////////
  //////////   DB upgrade   ///////////
  /////////////////////////////////////
  // You don't have to care about this until your game has been published on BGA.
  // Once your game is on BGA, this method is called everytime the system detects a game running with your old Database scheme.
  // In this case, if you change your Database scheme, you just have to apply the needed changes in order to
  //   update the game database and allow the game to continue to run with your new version.
  /////////////////////////////////////
  /*
   * upgradeTableDb
   *  - int $from_version : current version of this game database, in numerical form.
   *      For example, if the game was running with a release of your game named "140430-1345", $from_version is equal to 1404301345
   */
  public function upgradeTableDb($from_version)
  {
    //        if ($from_version <= 2412211311) {
    //            $this->updateDBTableCustom();
    //        }
  }

  function updateDBTableCustom()
  {
    // This method is used as a workaround to update DB after some new fields appeared
  }

  /////////////////////////////////////////////////////////////
  // Exposing protected methods, please use at your own risk //
  /////////////////////////////////////////////////////////////

  // Exposing protected method getCurrentPlayerId
  public static function getCurrentPId()
  {
    return self::get()->getCurrentPlayerId();
  }

  // Exposing protected method translation
  public static function translate($text)
  {
    return self::_($text);
  }

  public static function a()
  {
    // Method to debug something. Just type "a()" in the table chat
    //        var_dump(Stack::get());
  }
}
