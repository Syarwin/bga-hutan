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
  g_gamethemeurl + 'modules/js/States/PhaseOne.js',
], function (dojo, declare) {
  const ANIMAL_CASSOWARY = 'animal-cassowary';
  const ANIMAL_HORNBILL = 'animal-hornbill';
  const ANIMAL_ORANGUTAN = 'animal-orangutan';
  const ANIMAL_RHINOCEROS = 'animal-rhinoceros';
  const ANIMAL_TIGER = 'animal-tiger';
  const ANIMALS = [ANIMAL_CASSOWARY, ANIMAL_HORNBILL, ANIMAL_ORANGUTAN, ANIMAL_RHINOCEROS, ANIMAL_TIGER];

  return declare('bgagame.hutan', [customgame.game, hutan.playerboard, hutan.common, hutan.lexemes, hutan.phaseOne], {
    constructor() {
      // this.default_viewport = 'width=990';
    },

    setup(gamedatas) {
      debug('SETUP', gamedatas);
      this.setupCentralArea();

      this.setupPlayers();
      this.setupInfoPanel();
      this.setupMeeples();
      this.inherited(arguments);
    },

    setupCentralArea() {
      $('game_play_area').insertAdjacentHTML(
        'beforeend',
        `
  <div id="hutan-main-container">
        <div id="flower-cards-container"></div>
  </div>
    
  <svg style="display:none" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-question" role="img" xmlns="http://www.w3.org/2000/svg">
    <symbol id="help-marker-svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="white" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="1"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g>
    </symbol>
  </svg>`
      );
    },

    /////////////////////////////////
    //      ____              _
    //     / ___|__ _ _ __ __| |___
    //    | |   / _` | '__/ _` / __|
    //    | |__| (_| | | | (_| \__ \
    //     \____\__,_|_|  \__,_|___/
    /////////////////////////////////

    addFlowerCard(card, container = null) {
      if (container == null) {
        container = this.getFlowerCardContainer(card);
      }

      let o = this.place('tplFlowerCard', card, container);
      if (o !== undefined) {
        let tooltip = JSON.stringify(card);
        this.addCustomTooltip(o.id, tooltip, { midSize: false });
      }
    },

    getFlowerCardContainer(card) {
      return $('flower-cards-container');
    },

    tplFlowerCard(card, tooltip = false) {
      let uid = 'flower-card-' + card.id;
      let type = card.flowers.join('');

      return `<div id="${uid}" class='hutan-flower-card' data-id='${card.id}' data-type='${type}'>
          <div class='hutan-flower-card-wrapper'></div>
        </div>`;
    },

    //////////////////////////////////////////
    //  __  __                 _
    // |  \/  | ___  ___ _ __ | | ___  ___
    // | |\/| |/ _ \/ _ \ '_ \| |/ _ \/ __|
    // | |  | |  __/  __/ |_) | |  __/\__ \
    // |_|  |_|\___|\___| .__/|_|\___||___/
    //                  |_|
    //////////////////////////////////////////

    // This function is refreshUI compatible
    setupMeeples() {
      let meepleIds = this.gamedatas.meeples.map((meeple) => {
        if (!$(`meeple-${meeple.id}`)) {
          this.addMeeple(meeple);
        }

        let o = $(`meeple-${meeple.id}`);
        if (!o) return null;

        let container = this.getMeepleContainer(meeple);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
        }
        o.dataset.state = meeple.state;

        return meeple.id;
      });
      document.querySelectorAll('.hutan-meeple[id^="meeple-"]').forEach((oMeeple) => {
        if (!meepleIds.includes(parseInt(oMeeple.getAttribute('data-id')))) {
          this.destroy(oMeeple);
        }
      });
    },

    addMeeple(meeple, location = null) {
      if ($('meeple-' + meeple.id)) return;

      let o = this.place('tplMeeple', meeple, location == null ? this.getMeepleContainer(meeple) : location);
      let tooltipDesc = this.getMeepleTooltip(meeple);
      if (tooltipDesc != null) {
        this.addCustomTooltip(o.id, tooltipDesc.map((t) => this.formatString(t)).join('<br/>'));
      }

      return o;
    },

    getMeepleTooltip(meeple) {
      let type = meeple.type;
      return null;
    },

    tplMeeple(meeple) {
      return `<div class="hutan-meeple hutan-icon icon-${meeple.type}" id="meeple-${meeple.id}" data-id="${meeple.id}" data-type="${meeple.type}"></div>`;
    },

    getMeepleContainer(meeple) {
      // Reserve
      if (meeple.location == 'reserve') {
        return $(`animal-reserve-${meeple.type}`);
      }

      console.error('Trying to get container of a meeple', meeple);
      return 'game_play_area';
    },

    //////////////////////////////////////////////////////
    //  ___        __         ____                  _
    // |_ _|_ __  / _| ___   |  _ \ __ _ _ __   ___| |
    //  | || '_ \| |_ / _ \  | |_) / _` | '_ \ / _ \ |
    //  | || | | |  _| (_) | |  __/ (_| | | | |  __/ |
    // |___|_| |_|_|  \___/  |_|   \__,_|_| |_|\___|_|
    //////////////////////////////////////////////////////

    setupInfoPanel() {
      dojo.place(this.tplInfoPanel(), 'player_boards', 'first');

      // Mutators observers
      ANIMALS.forEach((type) => {
        const reserve = $(`animal-reserve-${type}`);
        let observer = new MutationObserver(() => {
          let n = reserve.childNodes.length;
          let counter = $(`animal-reserve-${type}-counter`);
          counter.innerHTML = n;
          counter.parentNode.dataset.n = n;
        });
        observer.observe(reserve, { childList: true });
      });

      let chk = $('help-mode-chk');
      dojo.connect(chk, 'onchange', () => this.toggleHelpMode(chk.checked));
      this.addTooltip('help-mode-switch', '', _('Toggle help/safe mode.'));
      this.updateTurnNumber();

      // this._settingsModal = new customgame.modal('showSettings', {
      //   class: 'brass_popin',
      //   closeIcon: 'fa-times',
      //   title: _('Settings'),
      //   closeAction: 'hide',
      //   verticalAlign: 'flex-start',
      //   contentsTpl: `<div id='brass-settings'>
      //      <div id='brass-settings-header'></div>
      //      <div id="settings-controls-container"></div>
      //    </div>`,
      // });
    },

    tplInfoPanel() {
      let animalsReserves = '';
      ANIMALS.forEach((type) => {
        animalsReserves += `<div class='animal-reserve-holder' data-n="0">
          <span id='animal-reserve-${type}-counter' class='animal-reserve-counter'>0</span>x
          <div id='animal-reserve-${type}' class='animal-reserve hutan-icon icon-${type}'></div>
        </div>`;
      });

      return `
   <div class='player-board' id="info-panel">
      <div class="info-panel-row" id="turn-counter-wrapper">
        ${_('Turn')} <span id="turn-number">1</span> / <span id="max-turns">8</span>
      </div>

     <div class="info-panel-row" id="player_config">
        <div id="help-mode-switch">
          <input type="checkbox" class="checkbox" id="help-mode-chk" />
          <label class="label" for="help-mode-chk">
            <div class="ball"></div>
          </label><svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
        </div>

        <div id="show-settings">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
            <g>
              <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
              <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
            </g>
          </svg>
        </div>
     </div>

      <div class="info-panel-row" id="animals-reserves">
        ${animalsReserves}
      </div>
   </div>
   `;
    },

    // updatePlayerOrdering() {
    //   this.inherited(arguments);
    //   dojo.place('player_board_config', 'player_boards', 'first');
    // },

    updateTurnNumber() {
      $('turn-number').innerHTML = this.gamedatas.turn;
      $('max-turns').innerHTML = this.getPlayers().length == 1 ? 18 : 9;
    },

    notif_newTurn(args) {
      this.gamedatas.turn = args.turn;
      this.updateTurnNumber();
      return this.wait(800);
    },
  });
});
