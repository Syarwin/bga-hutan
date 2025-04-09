define(['dojo', 'dojo/_base/declare', 'ebg/counter'], (dojo, declare) => {
  // Everything ralted to playerboards
  return declare('hutan.playerboard', null, {
    getPlayers() {
      return Object.values(this.gamedatas.players);
    },

    setupPlayers() {
      // Change No so that it fits the current player order view
      let currentNo = this.getPlayers().reduce((carry, player) => (player.id == this.player_id ? player.no : carry), 0);
      let nPlayers = Object.keys(this.gamedatas.players).length;
      this.forEachPlayer((player) => (player.order = (player.no + nPlayers - currentNo) % nPlayers));
      this.orderedPlayers = Object.values(this.gamedatas.players).sort((a, b) => a.order - b.order);

      // // Add player mat and player panel
      this.orderedPlayers.forEach((player, i) => {
        // Player board
        this.place('tplPlayerBoard', player, 'hutan-main-container');

        // Panels
        this.place('tplPlayerPanel', player, `overall_player_board_${player.id}`);
        $(`overall_player_board_${player.id}`).addEventListener('click', () => this.goToPlayerBoard(player.id));
      });

      // Init grid for clientside logic
      this._board = {};
      for (let x = 0; x < 6; x++) {
        this._board[x] = {};
        for (let y = 0; y < 6; y++) {
          this._board[x][y] = [];
        }
      }
    },

    getCell(cell, pId = null) {
      if (pId == null) pId = this.player_id;
      return $(`cell-${pId}-${cell.x}-${cell.y}`);
    },

    getFlowerValidPosition(color, previousFlowers) {
      let previousCells = Object.values(previousFlowers);
      let cells = [];
      for (let x = 0; x < 6; x++) {
        for (let y = 0; y < 6; y++) {
          // Board already full
          if (this._board[x][y].length == 2) continue;

          // If there is one flower here, check the color
          if (this._board[x][y].length == 1) {
            if (this._board[x][y][0] != color) continue;
          }

          // Check adjacency to other ongoing flowers
          if (previousCells.length > 0) {
            let isValid = false;
            previousCells.forEach((cell) => {
              if (Math.abs(cell.x - x) + Math.abs(cell.y - y) == 1) {
                isValid = true;
              }
            });

            if (!isValid) continue;
          }

          cells.push({ x, y });
        }
      }

      return cells;
    },

    onChangePlayerBoardsLayoutSetting(v) {
      if (v == 0) {
        this.goToPlayerBoard(this.orderedPlayers[0].id);
      } else {
        this._focusedPlayer = null;
      }
    },

    goToPlayerBoard(pId, evt = null) {
      if (evt) evt.stopPropagation();

      let v = this.settings.playerBoardsLayout;
      if (v == 0) {
        // Tabbed view
        this._focusedPlayer = pId;
        [...$('hutan-main-container').querySelectorAll('.hutan-player-board-resizable')].forEach((board) =>
          board.classList.toggle('active', board.id == `player-board-resizable-${pId}`)
        );
      } else if (v == 1) {
        // Multiple view
        this._focusedPlayer = null;
        window.scrollTo(0, $(`player-board-${pId}`).getBoundingClientRect()['top'] - 30);
      }
    },

    setupChangeBoardArrows(pId) {
      let leftArrow = $(`player-board-${pId}`).querySelector('.prev-player-board');
      if (leftArrow) leftArrow.addEventListener('click', () => this.switchPlayerBoard(-1));

      let rightArrow = $(`player-board-${pId}`).querySelector('.next-player-board');
      if (rightArrow) rightArrow.addEventListener('click', () => this.switchPlayerBoard(1));
    },

    getDeltaPlayer(pId, delta) {
      let playerOrder = this.orderedPlayers;
      let index = playerOrder.findIndex((elem) => elem.id == pId);
      if (index == -1) return -1;

      let n = playerOrder.length;
      return playerOrder[(((index + delta) % n) + n) % n].id;
    },

    switchPlayerBoard(delta) {
      let pId = this.getDeltaPlayer(this._focusedPlayer, delta);
      if (pId == -1) return;
      $(`player-board-${this._focusedPlayer}`).querySelector('.buildings-helper').classList.remove('open', 'closedAnim');
      this.goToPlayerBoard(pId);
    },
  });
});
