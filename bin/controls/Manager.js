/**
 * Manage Invite Codes
 *
 * @module package/quiqqer/invitecode/bin/controls/Manager
 * @author www.pcsg.de (Patrick Müller)
 */
define('package/quiqqer/invitecode/bin/controls/Manager', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/loader/Loader',
    'qui/controls/windows/Popup',
    'qui/controls/windows/Confirm',
    'qui/controls/buttons/Button',
    'qui/controls/buttons/Separator',

    'controls/grid/Grid',
    'utils/Controls',
    'qui/utils/Form',

    'package/quiqqer/invitecode/bin/InviteCodes',

    'Locale',
    'Ajax',
    'Mustache',

    'text!package/quiqqer/invitecode/bin/controls/Manager.html',
    'text!package/quiqqer/invitecode/bin/controls/Manager.Create.html',
    'css!package/quiqqer/invitecode/bin/controls/Manager.css'

], function (QUI, QUIPanel, QUILoader, QUIPopup, QUIConfirm, QUIButton, QUISeparator,
             Grid, QUIControlUtils, QUIFormUtils, InviteCodes,
             QUILocale, QUIAjax, Mustache, template, templateCreate) {
    "use strict";

    var lg = 'quiqqer/invitecode';

    return new Class({

        Extends: QUIPanel,
        Type   : 'package/quiqqer/invitecode/bin/controls/Manager',

        Binds: [
            '$onCreate',
            '$onResize',
            '$listRefresh',
            '$onRefresh',
            '$load',
            '$setGridData',
            '$create',
            '$toggleActiveStatus',
            '$managePackages',
            '$delete',
            '$editBundle',
            'refresh',
            '$openUserPanel'
        ],

        options: {
            title: QUILocale.get(lg, 'controls.manager.title')
        },

        initialize: function (options) {
            this.parent(options);

            this.Loader      = new QUILoader();
            this.$User       = null;
            this.$Grid       = null;
            this.$GridParent = null;
            this.$Panel      = null;

            this.addEvents({
                onCreate : this.$onCreate,
                onRefresh: this.$onRefresh,
                onResize : this.$onResize
            });
        },

        /**
         * Event: onCreate
         */
        $onCreate: function () {
            var self = this;

            this.Loader.inject(this.$Elm);

            this.addButton({
                name     : 'create',
                text     : QUILocale.get(lg, 'controls.manager.tbl.btn.create'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: function() {
                        self.$create();
                    }
                }
            });

            this.addButton({
                name     : 'quickcreate',
                text     : QUILocale.get(lg, 'controls.manager.tbl.btn.quickcreate'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: function() {
                        self.$create(true);
                    }
                }
            });

            this.addButton(new QUISeparator());

            this.addButton({
                name     : 'delete',
                text     : QUILocale.get(lg, 'controls.manager.tbl.btn.delete'),
                textimage: 'fa fa-trash',
                events   : {
                    onClick: this.$delete
                }
            });

            this.$load();
        },

        /**
         * Refresh data
         */
        refresh: function () {
            if (this.$Grid) {
                this.$Grid.refresh();
            }
        },

        /**
         * event: onResize
         */
        $onResize: function () {
            if (this.$GridParent && this.$Grid) {
                var size = this.$GridParent.getSize();

                this.$Grid.setHeight(size.y);
                this.$Grid.resize();
            }
        },

        /**
         * Load Grid
         */
        $load: function () {
            var self = this;

            this.setContent(Mustache.render(template));
            var Content = this.getContent();

            this.$GridParent = Content.getElement(
                '.quiqqer-invitecode-manager-table'
            );

            this.$Grid = new Grid(this.$GridParent, {
                columnModel      : [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 50
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.code'),
                    dataIndex: 'code',
                    dataType : 'string',
                    width    : 150
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.title'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.status'),
                    dataIndex: 'status',
                    dataType : 'node',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.email'),
                    dataIndex: 'email',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.user'),
                    dataIndex: 'user',
                    dataType : 'node',
                    width    : 200,
                    className: 'clickable'
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.validUntilDate'),
                    dataIndex: 'validUntilDate',
                    dataType : 'string',
                    width    : 150
                }, {
                    header   : QUILocale.get(lg, 'controls.manager.tbl.header.createDate'),
                    dataIndex: 'createDate',
                    dataType : 'string',
                    width    : 150
                }, {
                    dataIndex: 'userId',
                    dataType : 'number',
                    hidden   : true
                }],
                pagination       : true,
                serverSort       : true,
                selectable       : true,
                multipleSelection: true
            });

            this.$Grid.addEvents({
                onDblClick: function () {
                    // @todo
                    //self.$managePackages(
                    //    self.$Grid.getSelectedData()[0].id
                    //);
                },
                onClick   : function (event) {
                    var selected = self.$Grid.getSelectedData();

                    self.getButtons('delete').enable();

                    if (!event.cell.hasClass('clickable')) {
                        return;
                    }

                    var Row = selected[0];

                    if (Row.userId) {
                        self.Loader.show();

                        self.$openUserPanel(Row.userId).then(function () {
                            self.Loader.hide();
                        });
                    }
                },
                onRefresh : this.$listRefresh
            });

            this.resize();
            this.$Grid.refresh();
        },

        /**
         * Event: onRefresh
         */
        $onRefresh: function () {
            if (this.$Grid) {
                this.$Grid.refresh();
            }
        },

        /**
         * Refresh bundle list
         *
         * @param {Object} Grid
         */
        $listRefresh: function (Grid) {
            if (!this.$Grid) {
                return;
            }

            var self = this;

            self.getButtons('delete').disable();

            var GridParams = {
                sortOn : Grid.getAttribute('sortOn'),
                sortBy : Grid.getAttribute('sortBy'),
                perPage: Grid.getAttribute('perPage'),
                page   : Grid.getAttribute('page')
            };

            switch (GridParams.sortOn) {
                case 'status':
                    GridParams.sortOn = 'useDate';
                    break;

                case 'user':
                    GridParams.sortOn = 'userId';
                    break;
            }

            this.Loader.show();

            InviteCodes.getList(GridParams).then(function (ResultData) {
                self.Loader.hide();
                self.$setGridData(ResultData);
            });
        },

        /**
         * Set license data to grid
         *
         * @param {Object} GridData
         */
        $setGridData: function (GridData) {
            var textUnused    = QUILocale.get(lg, 'controls.manager.tbl.status.unused');
            var textUnlimited = QUILocale.get(lg, 'controls.manager.tbl.validUntil.unlimited');

            for (var i = 0, len = GridData.data.length; i < len; i++) {
                var Row = GridData.data[i];

                if (!Row.email) {
                    Row.email = '-';
                }

                var StatusElm = new Element('span', {
                    'class': 'quiqqer-invitecode-manager-tbl-status'
                });

                if (!Row.useDate) {
                    StatusElm.set('html', textUnused);
                    StatusElm.addClass('quiqqer-invitecode-manager-tbl-status-unused');
                } else {
                    StatusElm.set('html', QUILocale.get(lg, 'controls.manager.tbl.status.unused', {
                        useDate: Row.useDate
                    }));
                    StatusElm.addClass('quiqqer-invitecode-manager-tbl-status-used');
                }

                Row.status = StatusElm;

                if (!Row.validUntilDate) {
                    Row.validUntilDate = textUnlimited;
                }

                if (!Row.userId) {
                    Row.user = new Element('span', {html: '-'});
                } else {
                    Row.user = new Element('div', {
                        'class': 'quiqqer-invitecode-manager-tbl-user',
                        html   : Row.username
                    });
                }

                if (!Row.title) {
                    Row.title = '-';
                }
            }

            this.$Grid.setData(GridData);
        },

        /**
         * Create new InviteCode
         *
         * @param {Boolean} [quickCreate]
         */
        $create: function (quickCreate) {
            var self = this;

            quickCreate = quickCreate || false;

            if (quickCreate) {
                InviteCodes.create({}).then(function (inviteCodeId) {
                    if (!inviteCodeId) {
                        return;
                    }

                    self.refresh();
                });

                return;
            }

            var FuncSubmit = function () {
                var Content = Popup.getContent();
                var Form    = Content.getElement('form');

                Popup.Loader.show();

                InviteCodes.create(QUIFormUtils.getFormData(Form)).then(function (inviteCodeId) {
                    if (!inviteCodeId) {
                        Popup.Loader.hide();
                        return;
                    }

                    self.refresh();
                    Popup.close();
                });
            };

            // open popup
            var lgPrefix = 'controls.manager.create.template.';

            var Popup = new QUIPopup({
                icon       : 'fa fa-plus',
                title      : QUILocale.get(
                    lg, 'controls.manager.create.popup.title'
                ),
                maxHeight  : 375,
                maxWidth   : 450,
                events     : {
                    onOpen: function () {
                        var Content = Popup.getContent();
                        var Form    = Content.getElement('form');

                        Form.addEvent('submit', function (event) {
                            event.stop();
                            FuncSubmit();
                        });

                        var EmailInput       = Content.getElement('input[name="email"]');
                        var SendMailCheckbox = Content.getElement('input[name="sendmail"]');

                        EmailInput.addEvent('keyup', function (event) {
                            if (event.target.value.trim() === '') {
                                SendMailCheckbox.checked  = false;
                                SendMailCheckbox.disabled = true;

                                return;
                            }

                            SendMailCheckbox.disabled = false;
                        });

                        Content.getElement('input[name="title"]').focus();
                    }
                },
                closeButton: true,
                content    : Mustache.render(templateCreate, {
                    labelTitle   : QUILocale.get(lg, lgPrefix + 'labelTitle'),
                    labelEmail   : QUILocale.get(lg, lgPrefix + 'labelEmail'),
                    labelDate    : QUILocale.get(lg, lgPrefix + 'labelDate'),
                    labelSendMail: QUILocale.get(lg, lgPrefix + 'labelSendMail')
                })
            });

            Popup.open();

            Popup.addButton(new QUIButton({
                text  : QUILocale.get(lg, 'controls.manager.create.popup.btn.confirm_text'),
                alt   : QUILocale.get(lg, 'controls.manager.create.popup.btn.confirm'),
                title : QUILocale.get(lg, 'controls.manager.create.popup.btn.confirm'),
                events: {
                    onClick: FuncSubmit
                }
            }));
        },

        /**
         * Remove all selected licenses
         */
        $delete: function () {
            var self       = this;
            var deleteData = [];
            var deleteIds  = [];
            var rows       = this.$Grid.getSelectedData();

            for (var i = 0, len = rows.length; i < len; i++) {
                deleteData.push(
                    rows[i].title + ' (ID: #' + rows[i].id + ')'
                );

                deleteIds.push(rows[i].id);
            }

            // open popup
            var Popup = new QUIConfirm({
                'maxHeight': 300,
                'autoclose': true,

                'information': QUILocale.get(
                    lg,
                    'controls.manager.delete.popup.info', {
                        codes: deleteData.join('<br/>')
                    }
                ),
                'title'      : QUILocale.get(lg, 'controls.manager.delete.popup.title'),
                'texticon'   : 'fa fa-trash',
                'icon'       : 'fa fa-trash',

                cancel_button: {
                    text     : false,
                    textimage: 'icon-remove fa fa-remove'
                },
                ok_button    : {
                    text     : false,
                    textimage: 'icon-ok fa fa-check'
                },
                events       : {
                    onSubmit: function () {
                        Popup.Loader.show();

                        InviteCodes.delete(deleteIds).then(function (success) {
                            if (!success) {
                                Popup.Loader.hide();
                                return;
                            }

                            Popup.close();
                            self.refresh();
                        });
                    }
                }
            });

            Popup.open();
        },

        /**
         * Open user panel
         *
         * @param {Number} userId
         */
        $openUserPanel: function (userId) {
            return new Promise(function (resolve, reject) {
                require([
                    'controls/users/User',
                    'utils/Panels'
                ], function (UserPanel, PanelUtils) {
                    PanelUtils.openPanelInTasks(new UserPanel(userId)).then(resolve, reject);
                }.bind(this));
            });
        }
    });
});
