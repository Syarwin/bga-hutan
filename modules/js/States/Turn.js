define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const LOCATION_TABLE = 'table';
  const COLORS_FULL_TYPE = {
    b: 'flower-blue',
    y: 'flower-yellow',
    r: 'flower-red',
    w: 'flower-white',
    g: 'flower-grey',
    j: 'flower-joker',
  };
  const COLOR_ANIMAL_MAP = {
    b: 'cassowary',
    y: 'tiger',
    r: 'orangutan',
    w: 'rhinoceros',
    g: 'hornbill',
  };

  function onlyUnique(value, index, array) {
    return array.indexOf(value) === index;
  }

  return declare('hutan.turn', null, {
    constructor() {
      this._notifications.push('newTurn');
      this._notifications.push('flowerCardChosen');
      this._notifications.push('meeplePlaced');
      this._notifications.push('animalPlaced');
      this._notifications.push('discardLeftoverFlowerCards');
    },

    async notif_newTurn(args) {
      debug('Notif: starting new turn', args);
      this.gamedatas.turn = args.turn;
      this.updateTurnNumber();

      await Promise.all(
        args.cards.map((card, i) => {
          this.addFlowerCard(card, this.getVisibleTitleContainer());
          return this.slide(`flower-card-${card.id}`, $('flower-cards-holder'), {
            delay: 100 * i,
            phantomEnd: true,
          });
        })
      );
    },

    /////////////////////////////////////////////////////////
    // Initial entry point => choose a card or the pangolin
    /////////////////////////////////////////////////////////
    onEnteringStateTurn(args) {
      if (this.isSpectator) return;
      // TODO: if inactive, force the player to it a button "plan my next move" first

      // Cards
      let cardIds = args.cards[this.player_id];
      cardIds.forEach((cardId) => {
        this.onClick(`flower-card-${cardId}`, () => {
          let colors = $(`flower-card-${cardId}`).dataset.type.split('');
          let data = { cardId, colors, flowers: {}, flowersOrder: [] };

          // Joker card => select the color first
          if (colors.length === 1) {
            this.clientState('chooseFlowerCardColor', _('What flower do you want to place?'), data);
          }
          // Standard case => go to place flower client state
          else {
            this.clientState('placeFlowers', _('You must place the flowers on your board'), data);
          }
        });
      });

      // Pangolin
      if (args.pangolin === LOCATION_TABLE) {
        let callbackPangolin = () => {
          let data = { cardId: 0, flowers: {} };
          this.clientState('chooseFlowerCardColor', _('Which flower do you want to place?'), data);
        };

        this.addPrimaryActionButton('pangolin', _('Take Pangolin'), callbackPangolin);
        this.onClick('meeple-pangolin', callbackPangolin);
      }
    },

    // Notif choose card
    async notif_flowerCardChosen(args) {
      debug('Notif: flowerCardChosen', args);
      // Pangolin token
      if (args.flowerCardId === 0) {
        this.gamedatas.pangolin = args.player_id;
        await this.slide('meeple-pangolin', $(`pangolin-${args.player_id}`));
      }
      // Flower card
      else {
        await this.slide(`flower-card-${args.flowerCardId}`, this.getVisibleTitleContainer(), { destroy: true });
      }
    },

    /////////////////////////////////////////////////////////
    // Display the ongoing choices (card, flowers, ...)
    /////////////////////////////////////////////////////////
    highlightOngoingMoves(args) {
      // TODO : improve client state to make it work like a stack
      this.addCancelStateBtn();

      // Highlight card //
      let cardId = args.cardId;
      // Pangolin
      if (cardId == 0) {
        $('meeple-pangolin').classList.add('selected');
      }
      // Normal flower card
      else {
        let oCard = $(`flower-card-${cardId}`);
        oCard.classList.add('selected');
      }

      // Place temporary flowers
      Object.entries(args.flowers).forEach(([i, cell]) => {
        let oCell = this.getCell(cell);
        let o;

        // Already something here ? => Place a tree instead
        if (oCell.querySelector('.hutan-meeple')) {
          // Animal placed?
          if (args.animal && args.animal == i) {
            o = this.addMeeple({ id: `tmp-${i}`, type: `animal-${COLOR_ANIMAL_MAP[args.colors[i]]}` }, oCell);
          } else {
            o = this.addMeeple({ id: `tmp-${i}`, type: `tree-${+i + 1}` }, oCell);
          }
        }
        // Otherwise, basic flow
        else {
          o = this.addMeeple({ id: `tmp-${i}`, type: args.colors[i] }, oCell);
        }

        o.classList.add('tmp');
      });

      // Place temporary flowers from fertilization
      if (args.fertilized) {
        Object.entries(args.fertilized).forEach(([i, cell]) => {
          let oCell = this.getCell(cell);
          let o;

          // Already something here ? => Place a tree instead
          if (oCell.querySelector('.hutan-meeple')) {
            o = this.addMeeple({ id: `tmp-fertilize-${i}`, type: `tree-${+i + 1}` }, oCell);
          }
          // Otherwise, basic flow
          else {
            o = this.addMeeple({ id: `tmp-fertilize-${i}`, type: cell.color }, oCell);
          }

          o.classList.add('tmp');
        });
      }

      this.updateZonesStatus(this.player_id);
    },

    /////////////////////////////////////////////////////////
    // Choose the color : for the pangolin and some cards
    /////////////////////////////////////////////////////////
    onEnteringStateChooseFlowerCardColor(args) {
      this.highlightOngoingMoves(args);

      Object.keys(COLORS_FULL_TYPE).forEach((type, i) => {
        // Ignore joker color
        if (type == 'j') return;

        let icon = this.formatIcon(COLORS_FULL_TYPE[type]);
        this.addSecondaryActionButton(`flower${i}`, icon, () => {
          args.colors = [type];
          args.i = 0;
          this.clientState('placeFlower', _('Where do you want to place that flower?') + icon, args);
        });
        $(`flower${i}`).classList.add('flowerBtn');
      });
    },

    /////////////////////////////////////////////////////////
    // Place the flowers
    /////////////////////////////////////////////////////////

    // Choose the flower you want to place
    onEnteringStatePlaceFlowers(args) {
      this.highlightOngoingMoves(args);

      // Callback once we picked the color we want to place
      let callback = (i, isPlaced) => {
        if (isPlaced) return () => {};
        else
          return () => {
            let icon = this.formatIcon(COLORS_FULL_TYPE[args.colors[i]]);
            args.i = i;
            this.clientState('placeFlower', _('Where do you want to place that flower?') + icon, args);
          };
      };

      let remainingColors = {};
      for (let i = 0; i < args.colors.length; i++) {
        let color = args.colors[i];
        let icon = this.formatIcon(COLORS_FULL_TYPE[color]);
        let isPlaced = args.flowers[i] !== undefined;
        this.addSecondaryActionButton(`flower${i}`, icon, callback(i, isPlaced));
        $(`flower${i}`).classList.add('flowerBtn');
        $(`flower${i}`).classList.toggle('placed', isPlaced);

        if (!isPlaced) remainingColors[i] = color;
      }

      // Auto select if only one color type left
      if (Object.values(remainingColors).filter(onlyUnique).length === 1) {
        let i = Object.keys(remainingColors)[0];
        callback(i, false)();
      }
    },

    // Place indivual flower
    onEnteringStatePlaceFlower(args) {
      this.highlightOngoingMoves(args);

      let cells = this.getFlowerValidPosition(args.colors[args.i], args.flowers);
      cells.forEach((cell) => {
        this.onClick(this.getCell(cell), () => {
          args.flowers[args.i] = cell;
          args.flowersOrder.push(args.i);
          let isFinished = Object.values(args.flowers).length === args.colors.length;
          if (isFinished) {
            this.clientState('placeAnimal', _('You may place an animal'), args);
          } else {
            this.clientState('placeFlowers', _('You must place the flowers on your board'), args);
          }
        });
      });
    },

    // Notif flower placed
    async notif_meeplePlaced(args) {
      debug('Notif: meeplePlaced', args);

      let meeple = args.meeple;
      let oMeeple = this.addMeeple(meeple, this.getVisibleTitleContainer());
      let cell = this.getCell(meeple, args.player_id);
      await this.slide(oMeeple, cell);
      if (args.player_id === this.player_id) {
        this._board[meeple.x][meeple.y].push(meeple);
        this._emptyBoard = false;
      }

      let tmpMeeple = cell.querySelector('.tmp');
      if (tmpMeeple) this.destroy(tmpMeeple);
      this.updateZonesStatus(args.player_id);
    },

    /////////////////////////////////////////////////////////
    // Animal
    /////////////////////////////////////////////////////////
    onEnteringStatePlaceAnimal(args) {
      this.highlightOngoingMoves(args);

      let zones = this.gamedatas.board.zones,
        cellsZone = this.gamedatas.board.cellsZone;

      // Try to find a complete zone
      let completeZones = {};
      Object.entries(args.flowers).forEach(([i, cell]) => {
        let zoneId = cellsZone[cell.x][cell.y];

        /// Check if the zone if full by checking how many meeples are there
        let isFullAndValid = true,
          color = null;
        zones[zoneId].cells.forEach((cell2) => {
          let oMeeples = this.getCell(cell2).childNodes;
          if (oMeeples.length < 2) isFullAndValid = false;
          if (oMeeples.length > 0) {
            let cellColor = oMeeples[0].getAttribute('data-type');
            if (color === null) color = cellColor;
            else if (color !== cellColor) isFullAndValid = false;
          }
        });

        if (isFullAndValid) {
          /// Any animal left of this type?
          let animalType = COLOR_ANIMAL_MAP[args.colors[i]];
          debug(`animal-reserve-animal-${animalType}-counter`);
          let counter = $(`animal-reserve-animal-${animalType}-counter`);
          if (parseInt(counter.innerHTML) > 0) {
            completeZones[i] = zoneId; // Store the index to replace the tree by the animal
          }
        }
      });

      // No full zone => auto skip to confirm
      if (Object.keys(completeZones).length === 0) {
        this.clientState('confirmTurn', _('Please confirm your turn'), args);
      }
      // Otherwise, let the user click on the cell
      else {
        Object.entries(completeZones).forEach(([i, zoneId]) => {
          let cell = args.flowers[i];
          this.onClick(this.getCell(cell), () => {
            args.animal = i;
            args.animalZone = zoneId;
            args.fertilized = {};
            this.clientState('fertilize', _('You may fertilize adjacent spaces'), args);
          });
        });

        this.addDangerActionButton('pass', _('Pass'), () => this.clientState('confirmTurn', _('Please confirm your turn'), args));
      }
    },

    async notif_animalPlaced(args) {
      debug('Notif: animal placed', args);

      let animal = args.animal;
      let cell = this.getCell(animal, args.player_id);
      await Promise.all([
        this.slide(`meeple-${animal.id}`, cell),
        this.slide(`meeple-${args.tree.id}`, this.getVisibleTitleContainer(), { destroy: true }),
      ]);
      if (this.player_id == args.player_id) {
        this._board[animal.x][animal.y].pop();
        this._board[animal.x][animal.y].push(animal);
      }

      let tmpMeeple = cell.querySelector('.tmp');
      if (tmpMeeple) this.destroy(tmpMeeple);
      this.updateZonesStatus(args.player_id);
    },

    /////////////////////////////////////////////////////////
    // Fertilize
    /////////////////////////////////////////////////////////
    onEnteringStateFertilize(args) {
      this.highlightOngoingMoves(args);

      // Cell where the animal was placed
      let cell = args.flowers[args.animal];
      let cells = { ...this.getFertizableCells(cell, args.flowers) };
      console.log(cells);

      // Remove the already fertilized one
      Object.keys(args.fertilized).forEach((i) => delete cells[i]);

      // Nothing else to fertilize => auto skip to confirm
      if (Object.keys(cells).length === 0) {
        this.clientState('confirmTurn', _('Please confirm your turn'), args);
      }
      // Otherwise, let the user click on the cell
      else {
        Object.entries(cells).forEach(([i, cell]) => {
          this.onClick(this.getCell(cell), () => {
            args.fertilizeIndex = i;
            args.fertilizeCell = cell;
            this.clientState('fertilizeChooseColor', _('Which flower do you want to place?'), args);
          });
        });

        this.addDangerActionButton('pass', _('Pass'), () => this.clientState('confirmTurn', _('Please confirm your turn'), args));
      }
    },

    /////////////////////////////////////////////////////////
    // Fertilize choose color
    /////////////////////////////////////////////////////////
    onEnteringStateFertilizeChooseColor(args) {
      this.highlightOngoingMoves(args);

      this.getCell(args.fertilizeCell).classList.add('selected');

      let callback = (color) => {
        args.fertilized[args.fertilizeIndex] = {
          x: args.fertilizeCell.x,
          y: args.fertilizeCell.y,
          color,
        };
        this.clientState('fertilize', _('You may fertilize adjacent spaces'), args);
      };

      // Only one color => auto select
      if (args.fertilizeCell.colors.length === 1) {
        callback(args.fertilizeCell.colors[0]);
      }
      // Otherwise, create buttons
      else {
        args.fertilizeCell.colors.forEach((type, i) => {
          let icon = this.formatIcon(COLORS_FULL_TYPE[type]);
          this.addSecondaryActionButton(`flower${i}`, icon, () => callback(type));
          $(`flower${i}`).classList.add('flowerBtn');
        });
      }
    },

    /////////////////////////////////////////////////////////
    // Confirm the whole turn
    /////////////////////////////////////////////////////////
    onEnteringStateConfirmTurn(args) {
      this.highlightOngoingMoves(args);

      delete args.fertilizeCell;
      delete args.fertilizeIndex;
      delete args.i;

      this.addPrimaryActionButton('btnConfirm', _('Confirm'), () =>
        this.bgaPerformAction('actTakeTurn', { turn: JSON.stringify(args) })
      );
    },

    async notif_discardLeftoverFlowerCards(args) {
      debug('Notif: Discard Leftover Flower Cards', args);

      let cards = [...$('flower-cards-container').querySelectorAll('.hutan-flower-card')];
      await Promise.all(
        cards.map((oCard, i) => this.slide(oCard, this.getVisibleTitleContainer(), { destroy: true, delay: 100 * i }))
      );
    },
  });
});
