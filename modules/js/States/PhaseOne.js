define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('hutan.phaseOne', null, {
    constructor() {
      this._notifications.push(['flowerCardChosen', 1]);
    },

    onEnteringStateChooseFlowerCard(args) {
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

    notif_flowerCardChosen(n) {
      debug('Notif: flowerCardChosen ', n);
    },
  });
});
