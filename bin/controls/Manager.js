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

    'Locale',
    'Ajax',
    'Mustache',

    'text!package/quiqqer/invitecode/bin/controls/Manager.html',
    'text!package/quiqqer/invitecode/bin/controls/Manager.Edit.html',
    'css!package/quiqqer/invitecode/bin/controls/Manager.css'

], function (QUI, QUIPanel, QUILoader, QUIPopup, QUIConfirm, QUIButton, QUISeparator,
             Grid, QUIControlUtils, QUIFormUtils,
             QUILocale, QUIAjax, Mustache, template, templateEdit) {
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
            '$addBundle',
            '$toggleActiveStatus',
            '$managePackages',
            '$deleteBundles',
            '$editBundle'
        ],

        options: {
            title: QUILocale.get(lg, 'controls.bundlemanager.title')
        },

        initialize: function (options) {
            this.parent(options);

            this.Loader      = new QUILoader();
            this.$User       = null;
            this.$Grid       = null;
            this.$GridParent = null;
            this.$FormParent = null;
            this.$Panel      = null;

            this.addEvents({
                onCreate : this.$onCreate,
                onRefresh: this.$onRefresh,
                onResize : this.$onResize
            });
        },

        /**
         * Event: onImport
         */
        $onCreate: function () {
            this.Loader.inject(this.$Elm);

            this.addButton({
                name     : 'add',
                text     : QUILocale.get(lg, 'controls.bundlemanager.tbl.btn.addbundle'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: this.$addBundle
                }
            });

            this.addButton(new QUISeparator());

            this.addButton({
                name     : 'packages',
                text     : QUILocale.get(lg, 'controls.bundlemanager.tbl.btn.managepackages'),
                textimage: 'fa fa-gift',
                events   : {
                    onClick: this.$managePackages
                }
            });

            this.addButton({
                name     : 'edit',
                text     : QUILocale.get(lg, 'controls.bundlemanager.tbl.btn.editbundle'),
                textimage: 'fa fa-edit',
                events   : {
                    onClick: this.$editBundle
                }
            });

            this.addButton(new QUISeparator());

            this.addButton({
                name     : 'delete',
                text     : QUILocale.get(lg, 'controls.bundlemanager.tbl.btn.removebundle'),
                textimage: 'fa fa-trash',
                events   : {
                    onClick: this.$deleteBundles
                }
            });

            this.$load();
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
         * Load license management
         */
        $load: function () {
            var self = this;

            this.setContent(Mustache.render(template));
            var Content = this.getContent();

            this.$GridParent = Content.getElement(
                '.quiqqer-license-bundlemanager-table'
            );

            this.$Grid = new Grid(this.$GridParent, {
                columnModel      : [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 50
                }, {
                    header   : QUILocale.get(lg, 'controls.bundlemanager.tbl.header.title'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 250
                }, {
                    header   : QUILocale.get(lg, 'controls.bundlemanager.tbl.header.description'),
                    dataIndex: 'description',
                    dataType : 'string',
                    width    : 400
                }, {
                    header   : QUILocale.get(lg, 'controls.bundlemanager.tbl.header.packagecount'),
                    dataIndex: 'packagecount',
                    dataType : 'number',
                    width    : 50
                }, {
                    header   : QUILocale.get(lg, 'controls.bundlemanager.tbl.header.updated'),
                    dataIndex: 'updated',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'controls.bundlemanager.tbl.header.created'),
                    dataIndex: 'created',
                    dataType : 'string',
                    width    : 200
                }],
                pagination       : true,
                serverSort       : true,
                selectable       : true,
                multipleSelection: true
            });

            this.$Grid.addEvents({
                onDblClick: function () {
                    self.$managePackages(
                        self.$Grid.getSelectedData()[0].id
                    );
                },
                onClick   : function () {
                    var selected = self.$Grid.getSelectedData().length;

                    self.getButtons('delete').enable();

                    if (selected === 1) {
                        self.getButtons('packages').enable();
                        self.getButtons('edit').enable();
                    } else {
                        self.getButtons('packages').disable();
                        self.getButtons('edit').disable();
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
            self.getButtons('packages').disable();
            self.getButtons('edit').disable();

            var GridParams = {
                sortOn : Grid.getAttribute('sortOn'),
                sortBy : Grid.getAttribute('sortBy'),
                perPage: Grid.getAttribute('perPage'),
                page   : Grid.getAttribute('page')
            };

            this.Loader.show();

            BundleHandler.getBundles(GridParams).then(function (ResultData) {
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
            var self = this;

            for (var i = 0, len = GridData.data.length; i < len; i++) {
                var Row = GridData.data[i];

                Row.created = Row.createdAt + ' (' + Row.createUser + ')';
                Row.updated = Row.editAt + ' (' + Row.editUser + ')';
            }

            this.$Grid.setData(GridData);
        },

        /**
         * Add new license
         */
        $addBundle: function () {
            var self = this;

            var FuncSubmit = function () {
                var Input = Popup.getContent()
                    .getElement(
                        '.quiqqer-license-bundlemanager-add-input'
                    );

                var title = Input.value.trim();

                if (title === '') {
                    Input.value = '';
                    Input.focus();
                    return;
                }

                Popup.Loader.show();

                BundleHandler.createBundle(title).then(function (PackageBundleData) {
                    if (!PackageBundleData) {
                        Popup.Loader.hide();
                        return;
                    }

                    self.refresh();
                    Popup.close();
                });
            };

            // open popup
            var Popup = new QUIPopup({
                icon       : 'fa fa-plus',
                title      : QUILocale.get(
                    lg, 'controls.bundlemanager.add.popup.title'
                ),
                maxHeight  : 200,
                maxWidth   : 450,
                events     : {
                    onOpen: function () {
                        var Input = Popup.getContent()
                            .getElement(
                                '.quiqqer-license-bundlemanager-add-input'
                            );

                        Input.addEvents({
                            keyup: function (event) {
                                if (event.code === 13) {
                                    FuncSubmit();
                                    Input.blur();
                                }
                            }
                        });

                        Input.focus();
                    }
                },
                closeButton: true,
                content    : '<label class="quiqqer-license-bundlemanager-add-label">' +
                '<span>' + QUILocale.get(lg, 'controls.bundlemanager.add.popup.info') + '</span>' +
                '<input type="text" class="quiqqer-license-bundlemanager-add-input"/>' +
                '</label>'
            });

            Popup.open();

            Popup.addButton(new QUIButton({
                text  : QUILocale.get(lg, 'controls.bundlemanager.add.popup.confirm.btn.text'),
                alt   : QUILocale.get(lg, 'controls.bundlemanager.add.popup.confirm.btn'),
                title : QUILocale.get(lg, 'controls.bundlemanager.add.popup.confirm.btn'),
                events: {
                    onClick: FuncSubmit
                }
            }));
        },

        /**
         * Edit bundle
         */
        $editBundle: function () {
            var self              = this;
            var PackageBundleData = this.$Grid.getSelectedData()[0];

            this.createSheet({
                title : QUILocale.get(lg, 'controls.bundlemanager.edit.title'),
                events: {
                    onShow : function (Sheet) {
                        var Content = Sheet.getContent();

                        var lgPrefix = 'controls.bundlemanager.edit.template.';

                        Content.set('html', Mustache.render(templateEdit, {
                            header          : QUILocale.get(lg, lgPrefix + 'header', {
                                title: PackageBundleData.title,
                                id   : PackageBundleData.id
                            }),
                            labelTitle      : QUILocale.get(lg, lgPrefix + 'labelTitle'),
                            labelDescription: QUILocale.get(lg, lgPrefix + 'labelDescription'),
                            title           : PackageBundleData.titles,
                            description     : PackageBundleData.descriptions
                        }));

                        Content.setStyle('padding', 20);

                        Sheet.addButton(
                            new QUIButton({
                                text     : QUILocale.get('quiqqer/system', 'save'),
                                textimage: 'fa fa-save',
                                events   : {
                                    onClick: function () {
                                        var Form = Content.getElement('form');

                                        self.Loader.show();

                                        BundleHandler.editBundle(
                                            PackageBundleData.id,
                                            QUIFormUtils.getFormData(Form)
                                        ).then(function () {
                                            self.Loader.hide();
                                            Sheet.destroy();
                                            self.refresh();
                                        });
                                    }
                                }
                            })
                        );

                        self.Loader.show();

                        QUI.parse(Content).then(function () {
                            self.Loader.hide();
                        });
                    },
                    onClose: function (Sheet) {
                        Sheet.destroy();
                    }
                }
            }).show();
        },

        /**
         * Manage packages for a license
         */
        $managePackages: function () {
            var self              = this;
            var PackageBundleData = self.$Grid.getSelectedData()[0];
            var BundlePackagesControl;

            // open popup
            var Popup = new QUIPopup({
                icon       : 'fa fa-gift',
                title      : QUILocale.get(
                    lg, 'controls.bundlemanager.managebundlepackages.popup.title', {
                        title: PackageBundleData.title
                    }
                ),
                maxHeight  : 800,
                maxWidth   : 600,
                events     : {
                    onOpen: function () {
                        BundlePackagesControl = new BundlePackages({
                            bundleId: PackageBundleData.id
                        }).inject(Popup.getContent());
                    }
                },
                closeButton: true
            });

            Popup.open();

            Popup.addButton(new QUIButton({
                text  : QUILocale.get(lg, 'controls.bundlemanager.managebundlepackages.popup.btn.text'),
                alt   : QUILocale.get(lg, 'controls.bundlemanager.managebundlepackages.popup.btn'),
                title : QUILocale.get(lg, 'controls.bundlemanager.managebundlepackages.popup.btn'),
                events: {
                    onClick: function () {
                        Popup.Loader.show();

                        BundleHandler.editBundle(PackageBundleData.id, {
                            packages: BundlePackagesControl.getPackageData()
                        }).then(function (PackageBundleData) {
                            if (!PackageBundleData) {
                                Popup.Loader.hide();
                                return;
                            }

                            Popup.close();
                            self.refresh();
                        });
                    }
                }
            }));
        },

        /**
         * Remove all selected licenses
         */
        $deleteBundles: function () {
            var self              = this;
            var deleteBundlesData = [];
            var deleteBundlesIds  = [];
            var rows              = this.$Grid.getSelectedData();

            for (var i = 0, len = rows.length; i < len; i++) {
                deleteBundlesData.push(
                    rows[i].title + ' (ID: #' + rows[i].id + ')'
                );

                deleteBundlesIds.push(rows[i].id);
            }

            // open popup
            var Popup = new QUIConfirm({
                'maxHeight': 300,
                'autoclose': true,

                'information': QUILocale.get(
                    lg,
                    'controls.bundlemanager.deletebundles.popup.info', {
                        bundles: deleteBundlesData.join('<br/>')
                    }
                ),
                'title'      : QUILocale.get(lg, 'controls.bundlemanager.deletebundles.popup.title'),
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

                        BundleHandler.deleteBundles(deleteBundlesIds).then(function (success) {
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
        }
    });
});
