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
 * states.inc.php
 *
 * Hutan game states description
 *
 */

require_once "modules/php/constants.inc.php";

$machinestates = [
  // The initial state. Please do not modify.
  ST_GAME_SETUP => [
    'name' => 'gameSetup',
    'description' => '',
    'type' => 'manager',
    'action' => 'stGameSetup',
    'transitions' => ['' => ST_PREPARE_MARKET],
  ],

  ST_PREPARE_MARKET => [
    'name' => 'prepareMarket',
    'description' => clienttranslate('Preparing market for the next round'),
    'type' => 'game',
    'action' => 'stPrepareMarket',
    // 'transitions' => ['' => ST_PHASE_ONE_CHOOSE_FLOWER_CARD],
    'transitions' => ['' => ST_TURN],
  ],

  ST_TURN => [
    'name' => 'turn',
    'description' => clienttranslate('${actplayer} must choose a Flower card and place the corresponding Flowers'),
    'descriptionmyturn' => clienttranslate('${you} must choose a Flower card and place the corresponding Flowers'),
    'type' => 'activeplayer',
    'args' => 'argsTurn',
    'possibleactions' => ['actTakeTurn'],
    'transitions' => ['' => ST_END_OF_TURN_CLEANUP],
  ],


  // ST_PHASE_ONE_CHOOSE_FLOWER_CARD => [
  //   'name' => 'chooseFlowerCard',
  //   'description' => clienttranslate('${actplayer} must choose a Flower card'),
  //   'descriptionmyturn' => clienttranslate('${you} must choose a Flower card'),
  //   'type' => 'activeplayer',
  //   'args' => 'argsChooseFlowerCard',
  //   'possibleactions' => ['actChooseFlowerCard'],
  //   'transitions' => [
  //     ST_PHASE_ONE_PLACE_FLOWERS => ST_PHASE_ONE_PLACE_FLOWERS,
  //     ST_PHASE_ONE_CHOOSE_FLOWER_COLOR => ST_PHASE_ONE_CHOOSE_FLOWER_COLOR
  //   ],
  // ],

  // ST_PHASE_ONE_CHOOSE_FLOWER_COLOR => [
  //   'name' => 'chooseFlowerColor',
  //   'description' => clienttranslate('${actplayer} must choose a color of the flower before placing it'),
  //   'descriptionmyturn' => clienttranslate('${you} must choose a color of the flower before placing it'),
  //   'type' => 'activeplayer',
  //   'args' => 'argsChooseFlowerColor',
  //   'possibleactions' => ['actChooseFlowerColor'],
  //   'transitions' => ['' => ST_PHASE_ONE_PLACE_FLOWERS],
  // ],

  // ST_PHASE_ONE_PLACE_FLOWERS => [
  //   'name' => 'placeFlowers',
  //   'description' => clienttranslate('${actplayer} must place flowers from the chosen card'),
  //   'descriptionmyturn' => clienttranslate('${you} must place flowers from the chosen card'),
  //   'type' => 'activeplayer',
  //   'args' => 'argsPlaceFlowers',
  //   'possibleactions' => ['actPlaceFlowers'],
  //   'transitions' => [
  //     '' => ST_END_OF_TURN_CLEANUP,
  //   ],
  // ],

  ST_END_OF_TURN_CLEANUP => [
    'name' => 'prepareMarket',
    'description' => clienttranslate('Cleaning up at the end of turn'),
    'type' => 'game',
    'action' => 'stEndOfTurnCleanup',
  ],

  // Final state.
  // Please do not modify (and do not overload action/args methods).
  ST_END_GAME => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd',
  ],
];
