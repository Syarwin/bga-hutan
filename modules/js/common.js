define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('hutan.common', null, {
    constructor() {
      // this._notifications.push(['locationsDrawn', 1]);
    },

    extractId(element, prefix) {
      const unparsed = element.getAttribute('id').replace(`${prefix}-`, '');
      return isNaN(parseInt(unparsed)) ? unparsed : parseInt(unparsed);
    },
  });
});
