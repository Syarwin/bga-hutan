define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('hutan.phaseOne', null, {
    constructor() {
      this._notifications.push(['flowerCardChosen', 1]);
      this._notifications.push(['flowerPlaced', 1]);
      this._notifications.push(['treePlaced', 1]);
    },

    onEnteringStateChooseFlowerCard(args) {
      this.destroyAll('.hutan-flower-card');
      const cards = this.placeFlowerCards(args.cards);
      this.makeAllSelectableAndClickable(cards, (card) => {
        const id = this.extractId(card, 'flower-card');
        this.bgaPerformAction('actChooseFlowerCard', {id: id});
      })
    },


    placeFlowerCards(cards) {
      return cards.map((card) => {
        if (!$(`flower-card-${card.id}`)) {
          this.addFlowerCard(card);
        }

        let o = $(`flower-card-${card.id}`);
        if (!o) return null;

        let container = this.getFlowerCardContainer(card);
        if (o.parentNode !== $(container)) {
          dojo.place(o, container);
        }

        return o;
      });
    },

    onEnteringStateChooseFlowerColor(args) {
      args.colors.forEach((color) => {
        this.addPrimaryActionButton(color, this.tplFlowerIcon(color, true), (element) => {
          this.bgaPerformAction('actChooseFlowerColor', {colorClass: color});
        });
      })
    },

    onEnteringStatePlaceFlower(args) {
      if (this.isCurrentPlayerActive()) {
        const state = this.gamedatas.gamestate;
        const flowerIcon = this.tplFlowerIcon(args.flowerColor);
        ['description', 'descriptionmyturn'].forEach((description) => {
          state[description] = state[description].replace('{flower}', flowerIcon);
        });
        this.updatePageTitle(state);

        args.availableCoordinates.forEach(coordinates => {
          const cell = $(`cell-${this.player_id}-${coordinates.x}-${coordinates.y}`);
          this.addSelectableClass(cell);
          this.dojoConnect(cell, () => this.bgaPerformAction('actPlaceFlower', {x: coordinates.x, y: coordinates.y}));
        });
      }
    },

    notif_flowerCardChosen(n) {
      debug('Notif: flowerCardChosen', n);
    },

    notif_flowerPlaced(n) {
      debug('Notif: flowerPlaced', n);
    },

    notif_treePlaced(n) {
      debug('Notif: treePlaced', n);
    },
  });
});
