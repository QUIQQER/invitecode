/**
 * Select available CodeGenerators
 *
 * @module package/quiqqer/invitecode/bin/controls/settings/CodeGeneratorSelect
 * @author www.pcsg.de (Patrick Müller)
 */
define('package/quiqqer/invitecode/bin/controls/settings/CodeGeneratorSelect', [

    'qui/controls/buttons/Select',
    'qui/controls/loader/Loader',

    'Locale',
    'Ajax',

    'css!package/quiqqer/invitecode/bin/controls/settings/CodeGeneratorSelect.css'

], function (QUISelect, QUILoader, QUILocale, QUIAjax) {
    "use strict";

    var lg = 'quiqqer/invitecode';

    return new Class({
        Extends: QUISelect,
        Type   : 'package/quiqqer/invitecode/bin/controls/settings/CodeGeneratorSelect',

        Binds: [
            '$onImport'
        ],

        options: {
            showIcons: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Input = null;
            this.Loader = new QUILoader();

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * Event: onImport
         */
        $onImport: function () {
            var self = this;

            this.$Input        = this.getElm();
            this.$Input.hidden = true;

            var Elm = this.create().inject(this.$Input, 'after');

            Elm.addClass('field-container-field');

            this.Loader.inject(Elm);
            this.Loader.show();

            this.$getCodeGenerators().then(function(codeGenerators) {
                self.Loader.hide();

                for (var i = 0, len = codeGenerators.length; i < len; i++) {
                    self.appendChild(
                        codeGenerators[i],
                        codeGenerators[i]
                    );
                }

                if (self.$Input.value !== '') {
                    self.setValue(self.$Input.value);
                }

                self.addEvent('onChange', function(value) {
                    console.log(value);
                    self.$Input.value = value;
                });
            });
        },

        /**
         * Get list of all CodeGenerators
         *
         * @return {Promise}
         */
        $getCodeGenerators: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_invitecode_ajax_settings_getCodeGenerators', resolve, {
                    'package': 'quiqqer/invitecode',
                    onError  : reject
                });
            });
        }
    });
});
