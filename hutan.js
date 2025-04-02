/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Hutan implementation : © Timothée (Tisaac) Pecatte <tim.pecatte@gmail.com>, Pavel Kulagin (KuWizard) <kuzwiz@mail.ru>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * hutan.js
 *
 * Hutan user interface script
 *
 */
var isDebug = window.location.host === 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  'dojo',
  'dojo/_base/declare',
  'ebg/counter',
  g_gamethemeurl + 'modules/js/Core/game.js',
  g_gamethemeurl + 'modules/js/playerboard.js',
  g_gamethemeurl + 'modules/js/common.js',
  g_gamethemeurl + 'modules/js/lexemes.js',
], function (dojo, declare) {
  return declare('bgagame.hutan', [customgame.game, hutan.playerboard, hutan.common, hutan.lexemes], {
    constructor() {
      // this.default_viewport = 'width=990';
    },

    setup(gamedatas) {
      debug('SETUP', gamedatas);
      this.inherited(arguments);
    },
  });
});
