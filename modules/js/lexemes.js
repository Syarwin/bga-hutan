define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
    return declare('hutan.lexemes', null, {
        getDeckLexeme() {
            return _('Deck');
        },

        getDiscardLexeme() {
            return _('Discard');
        },
    });
});
