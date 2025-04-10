define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const COLORS_FULL_TYPE = {
    b: 'flower-blue',
    y: 'flower-yellow',
    r: 'flower-red',
    w: 'flower-white',
    g: 'flower-grey',
    j: 'flower-joker',
  };

  const ZONES_SCORING = {
    2: [2, 3],
    3: [4, 6],
    4: [6, 9],
    5: [8, 12],
  };

  return declare('hutan.htmltemplates', null, {
    centralAreaHtml() {
      return `
  <div id="hutan-main-container">
        <div id="flower-cards-container"></div>
  </div>
    
  <svg style="display:none" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-question" role="img" xmlns="http://www.w3.org/2000/svg">
    <symbol id="help-marker-svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="white" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="1"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g>
    </symbol>
  </svg>`;
    },

    tplFlowerCard(card, tooltip = false) {
      let uid = 'flower-card-' + card.id;
      let type = card.flowers.join('');

      return `<div id="${uid}" class='hutan-flower-card' data-id='${card.id}' data-type='${type}'>
          <div class='hutan-flower-card-wrapper'></div>
        </div>`;
    },

    tplMeeple(meeple) {
      let type = meeple.type;
      if (COLORS_FULL_TYPE[type] !== undefined) {
        type = COLORS_FULL_TYPE[type];
      }

      return `<div class="hutan-meeple hutan-icon icon-${type}" id="meeple-${meeple.id}" data-id="${meeple.id}" data-type="${type}"></div>`;
    },

    tplInfoPanel(animals) {
      let animalsReserves = '';
      animals.forEach((type) => {
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

    tplFlowerIcon(type, isButton = false) {
      const buttonClass = isButton ? ' button-icon' : '';
      return `<div class="hutan-icon ${type}${buttonClass} status-bar-icon"></div>`;
    },

    tplPlayerBoard(player) {
      let boards = this.gamedatas.board.ids;
      let grid = '';

      // Quadrant
      for (let i = 0; i < 4; i++) {
        let board = boards[i];
        grid += `<div class='board-quadrant' data-quadrant='${i}' data-board='${board[0]}' data-orientation='${board[1]}'></div>`;
      }

      // Zone indicators
      Object.entries(this.gamedatas.board.zones).forEach(([zoneId, zone]) => {
        // Compute where to place the shape
        let minX = 6,
          minY = 6,
          maxY = 0,
          maxX = 0;
        zone.cells.forEach((cell) => {
          minX = Math.min(minX, cell.x);
          minY = Math.min(minY, cell.y);
          maxX = Math.max(maxX, cell.x);
          maxY = Math.max(maxY, cell.y);
        });
        let midY = (minY + maxY) / 2;
        let column = '';
        if ((minY + maxY) % 2 == 1) {
          column = `${midY + 0.5} / span 2`;
        } else {
          column = `${midY + 1} / span 1`;
        }

        // Find info about that zone
        let scoring = ZONES_SCORING[zone.cells.length];
        let shape = '';
        switch (zone.cells.length) {
          case 2:
            shape = 'zone-2';
            break;

          case 3:
            shape = minX != maxX && minY != maxY ? 'zone-3L' : 'zone-3I';
            break;

          case 4:
            if (maxX == minX + 1 && maxY == minY + 1) shape = 'zone-4O';
            // TODO
            break;

          default:
            shape = 'zone-4S';
        }

        grid += `<div class='zone-infos-wrapper' style='grid-row-start:${minX + 1}; grid-column:${column}'>
          <div class='zone-infos'>
            <div class='zone-size-scoring'>
              <span id='zone-${player.id}-${zoneId}'>${scoring[0]}</span>${this.formatIcon(shape, null, false)}
            </div>
            <div id='zone-animal-${player.id}-${zoneId}' class='zone-animal-scoring'>${scoring[1]}</div>
          </div>
        </div>`;
      });

      // Cells
      for (let x = 0; x < 6; x++) {
        for (let y = 0; y < 6; y++) {
          grid += `<div class='board-cell' id='cell-${player.id}-${x}-${y}' style='grid-row-start:${x + 1}; grid-column-start:${y + 1}'></div>`;
        }
      }

      return `<div class='hutan-player-board-resizable' id='player-board-resizable-${player.id}'>
            <div class='hutan-player-board' id='player-board-${player.id}'>
                <div class='hutan-board-player-name' style="color:#${player.color}">
                    ${player.name}
                </div>
                <div class='hutan-board-grid'>
                    ${grid}
                </div>
            </div>
          </div>`;
    },

    tplPlayerPanel(player) {
      return `<div class='player-info'>
        <div class="hutan-pangolin-holder" id="pangolin-${player.id}"></div>
      </div>`;
    },
  });
});
