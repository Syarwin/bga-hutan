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

  function onlyUnique(value, index, array) {
    return array.indexOf(value) === index;
  }

  return declare('hutan.turn', null, {
    constructor() {
      this._notifications.push('flowerCardChosen');
      this._notifications.push('flowerPlaced');
      this._notifications.push('treePlaced');
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
          let colors = $(`flower-card-${cardId}`).dataset.type;
          let data = { cardId, colors, flowers: {} };

          // Joker card => select the color first
          if (colors.length == 1) {
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
        let o = this.addMeeple({ id: `tmp-${i}`, type: args.colors[i] }, this.getCell(cell));
        o.classList.add('tmp');
      });
    },

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
      if (Object.values(remainingColors).filter(onlyUnique).length == 1) {
        let i = Object.keys(remainingColors)[0];
        callback(i, false)();
      }
    },

    onEnteringStatePlaceFlower(args) {
      this.highlightOngoingMoves(args);

      let cells = this.getFlowerValidPosition(args.colors[args.i], args.flowers);
      cells.forEach((cell) => {
        this.onClick(this.getCell(cell), () => {
          args.flowers[args.i] = cell;
          let isFinished = Object.values(args.flowers).length == args.colors.length;
          if (isFinished) {
            this.clientState('confirmTurn', _('Please confirm your turn'), args);
          } else {
            this.clientState('placeFlowers', _('You must place the flowers on your board'), args);
          }
        });
      });
    },

    notif_flowerPlaced(n) {
      debug('Notif: flowerPlaced', n);
    },

    notif_treePlaced(n) {
      debug('Notif: treePlaced', n);
    },

    /////////////////////////////////////////////////////////
    // Confirm the whole turn
    /////////////////////////////////////////////////////////
    onEnteringStateConfirmTurn(args) {
      this.highlightOngoingMoves(args);
      this.addPrimaryActionButton('btnConfirm', _('Confirm'), () =>
        this.bgaPerformAction('actTakeTurn', { turn: JSON.stringify(args) })
      );
    },

    // onEnteringStateChooseFlowerCard(args) {
    //   this.destroyAll('.hutan-flower-card');
    //   const cards = this.placeFlowerCards(args.cards);
    //   if (this.isCurrentPlayerActive()) {
    //     this.makeAllSelectableAndClickable(cards, (card) => {
    //       const id = this.extractId(card, 'flower-card');
    //       this.bgaPerformAction('actChooseFlowerCard', { id: id });
    //     });
    //     if (this.gamedatas.pangolin === LOCATION_TABLE) {
    //       this.addPrimaryActionButton('pangolin', `Take Pangolin`, () => {
    //         this.bgaPerformAction('actChooseFlowerCard', { id: 0 });
    //       });
    //     }
    //   }
    // },

    // onEnteringStateChooseFlowerColor(args) {
    //   args.colors.forEach((color) => {
    //     this.addPrimaryActionButton(color, this.tplFlowerIcon(color, true), (element) => {
    //       this.bgaPerformAction('actChooseFlowerColor', { colorClass: color });
    //     });
    //   });
    // },

    // onEnteringStatePlaceFlowers(args) {
    //   if (this.isCurrentPlayerActive()) {
    //     const flowersColors = args.flowersClasses;
    //     const flowersElements = flowersColors.map((flower) => {
    //       return this.tplFlowerIcon(flower, true);
    //     });

    //     // *** All this block should be replaced with the client logic. Here are all possible correct and incorrect placements
    //     const x = 0;
    //     const y = 2;
    //     if (flowersColors.length === 1) {
    //       this.addPrimaryActionButton('one', `${flowersElements[0]} -> ${x},${y}`, () => {
    //         const flowerObject = this.getFlowerObject(flowersColors[0], x, y);
    //         this.bgaPerformAction('actPlaceFlowers', { flowers: JSON.stringify([flowerObject]) });
    //       });
    //       this.addPrimaryActionButton('incorr', `Incorrect amount`, () => {
    //         const flowerObject = this.getFlowerObject(flowersColors[0], x, y);
    //         const fakeObject = this.getFlowerObject(flowersColors[0], 0, 1);
    //         this.bgaPerformAction('actPlaceFlowers', { flowers: JSON.stringify([flowerObject, fakeObject]) });
    //       });
    //     }

    //     if (flowersColors.length > 1) {
    //       this.addPrimaryActionButton('incorrectcolor', `Incorrect color`, () => {
    //         const incorrectColor = flowersColors[0] === 'icon-flower-red' ? 'icon-flower-blue' : 'icon-flower-red';
    //         const flowers = [this.getFlowerObject(incorrectColor, x, y), this.getFlowerObject(flowersColors[1], x + 1, y)];
    //         if (flowersColors.length > 2) {
    //           flowers.push(this.getFlowerObject(flowersColors[2], x + 2, y));
    //         }
    //         this.bgaPerformAction('actPlaceFlowers', { flowers: JSON.stringify(flowers) });
    //       });
    //       this.addPrimaryActionButton('onenotadjacent', `One not adjacent`, () => {
    //         const flowers = [this.getFlowerObject(flowersColors[0], x, y), this.getFlowerObject(flowersColors[1], x + 3, y)];
    //         if (flowersColors.length > 2) {
    //           flowers.push(this.getFlowerObject(flowersColors[2], x + 2, y));
    //         }
    //         this.bgaPerformAction('actPlaceFlowers', { flowers: JSON.stringify(flowers) });
    //       });
    //       if (flowersColors[0] === flowersColors[1]) {
    //         this.addPrimaryActionButton('two-same', `Two same to same coords`, () => {
    //           const flowers = [this.getFlowerObject(flowersColors[0], x, y), this.getFlowerObject(flowersColors[1], x, y)];
    //           this.bgaPerformAction('actPlaceFlowers', { flowers: JSON.stringify(flowers) });
    //         });
    //       }
    //       this.addPrimaryActionButton('Allcorrect', `All correct`, () => {
    //         const flowerObject0 = this.getFlowerObject(flowersColors[0], x, y);
    //         const flowerObject1 = this.getFlowerObject(flowersColors[1], x + 1, y);
    //         const flowers = [flowerObject0, flowerObject1];
    //         if (flowersColors.length > 2) {
    //           flowers.push(this.getFlowerObject(flowersColors[2], x + 2, y));
    //         }
    //         this.bgaPerformAction('actPlaceFlowers', { flowers: JSON.stringify(flowers) });
    //       });
    //     }
    //     // *** End of block
    //   }
    // },

    // getFlowerObject(color, x, y) {
    //   return { color: color, x: x, y: y };
    // },

    // notif_flowerCardChosen(n) {
    //   debug('Notif: flowerCardChosen', n);
    //   if (n.args.flowerCardId === 0) {
    //     this.gamedatas.pangolin = n.active_player;
    //   }
    // },

    // notif_flowerPlaced(n) {
    //   debug('Notif: flowerPlaced', n);
    // },

    // notif_treePlaced(n) {
    //   debug('Notif: treePlaced', n);
    // },
  });
});
