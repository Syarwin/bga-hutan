define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const LOCATION_TABLE = 'table';

  return declare('hutan.common', null, {
    constructor() {
      this._notifications.push('pangolinMovedToMarket');
    },

    extractId(element, prefix) {
      const unparsed = element.getAttribute('id').replace(`${prefix}-`, '');
      return isNaN(parseInt(unparsed)) ? unparsed : parseInt(unparsed);
    },

    notif_pangolinMovedToMarket(args) {
      debug('Notif: pangolinMovedToMarket', args);
      this.gamedatas.pangolin = LOCATION_TABLE;
    },
  });
});
