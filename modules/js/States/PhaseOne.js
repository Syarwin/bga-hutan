define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('hutan.phaseOne', null, {
    constructor() {
      // this._notifications.push(['cardChosen', 1]);
    },

    onEnteringStateChooseFlowerCard(args) {
      this.placeFlowerCards(args.cards);
    },


    placeFlowerCards(cards) {
      let cardIds = cards.map((card) => {
        if (!$(`flower-card-${card.id}`)) {
          this.addFlowerCard(card);
        }

        let o = $(`flower-card-${card.id}`);
        if (!o) return null;

        let container = this.getFlowerCardContainer(card);
        if (o.parentNode !== $(container)) {
          dojo.place(o, container);
        }

        return card.id;
      });
    },
  });
});
