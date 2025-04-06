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
  g_gamethemeurl + 'modules/js/tpls.js',
  g_gamethemeurl + 'modules/js/States/PhaseOne.js',
], function (dojo, declare) {
  const ANIMAL_CASSOWARY = 'animal-cassowary';
  const ANIMAL_HORNBILL = 'animal-hornbill';
  const ANIMAL_ORANGUTAN = 'animal-orangutan';
  const ANIMAL_RHINOCEROS = 'animal-rhinoceros';
  const ANIMAL_TIGER = 'animal-tiger';
  const ANIMALS = [ANIMAL_CASSOWARY, ANIMAL_HORNBILL, ANIMAL_ORANGUTAN, ANIMAL_RHINOCEROS, ANIMAL_TIGER];

  return declare(
    'bgagame.hutan',
    [customgame.game, hutan.playerboard, hutan.common, hutan.lexemes, hutan.phaseOne, hutan.htmltemplates],
    {
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
        $('game_play_area').insertAdjacentHTML('beforeend', this.centralAreaHtml());
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

      getMeepleContainer(meeple) {
        // Reserve
        if (meeple.location == 'reserve') {
          return $(`animal-reserve-${meeple.type}`);
        }
        // Board
        if (meeple.location == 'board') {
          return $(`cell-${meeple.pId}-${meeple.x}-${meeple.y}`);
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
        dojo.place(this.tplInfoPanel(ANIMALS), 'player_boards', 'first');

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
    }
  );
});
