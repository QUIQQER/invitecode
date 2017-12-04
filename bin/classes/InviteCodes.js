/**
 * InviteCodes Handler
 *
 * @module package/quiqqer/invitecode/bin/classes/InviteCodes
 * @author www.pcsg.de (Patrick Müller)
 *
 * @require Ajax
 */
define('package/quiqqer/invitecode/bin/classes/InviteCodes', [

    'Ajax'

], function (QUIAjax) {
    "use strict";

    var pkg = 'quiqqer/invitecode';

    return new Class({

        Type: 'package/quiqqer/invitecode/bin/classes/InviteCodes',

        /**
         * Create new InviteCode
         *
         * @param {Object} Attributes
         * @return {Promise}
         */
        create: function (Attributes) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_invitecode_ajax_create', resolve, {
                    'package' : pkg,
                    attributes: JSON.encode(Attributes),
                    onError   : reject
                });
            });
        },

        /**
         * Delete Invite Codes
         *
         * @param {Array} ids
         * @return {Promise}
         */
        delete: function (ids) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_invitecode_ajax_delete', resolve, {
                    'package': pkg,
                    ids      : JSON.encode(ids),
                    onError  : reject
                });
            });
        },

        /**
         * Get list of all InviteCodes
         *
         * @param {Object} SearchParams
         * @return {Promise}
         */
        getList: function (SearchParams) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_invitecode_ajax_getList', resolve, {
                    'package'   : pkg,
                    searchParams: JSON.encode(SearchParams),
                    onError     : reject
                });
            });
        },

        /**
         * Send InviteCodes via mail
         *
         * @param {Array} ids
         * @param {Boolean} resend
         * @return {Promise}
         */
        sendMail: function (ids, resend) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_invitecode_ajax_sendMail', resolve, {
                    'package': pkg,
                    ids      : JSON.encode(ids),
                    resend   : resend ? 1 : 0,
                    onError  : reject
                });
            });
        }
    });
});
