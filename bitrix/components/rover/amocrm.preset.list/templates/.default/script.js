function AmoCrmPresetList(sourceTypes)
{
    return {
        sourceTypes: sourceTypes,
        lastPopup: null,
        popup : function (type) {

            if (this.sourceTypes[type] === undefined) return;
            var popup = this.getPopup(type);

            popup.show();
        },
        getPopupName: function (type) {
            return "rover-acrm__" + type;
        },
        getSelect: function (type) {

            var select = document.createElement("select"),
                optionsRaw, keys = [], keyO;

            select.setAttribute('id', 'form_values');

            if (this.sourceTypes[type] === undefined) return select;

            optionsRaw = this.sourceTypes[type];


            for (keyO in optionsRaw) {
                if (optionsRaw.hasOwnProperty(keyO)) {
                    keys.push(optionsRaw[keyO]);
                }
            }

            keys.sort().forEach(function(keyA){
                for (keyO in optionsRaw) {
                    if (!optionsRaw.hasOwnProperty(keyO)) continue;

                    if (optionsRaw[keyO] != keyA) continue;

                    select.options[select.options.length] = new Option(optionsRaw[keyO], keyO);
                    break;
                }
            });

            return select;
        },
        getPopup: function (type) {

            if (this.sourceTypes[type] === undefined) return;

            var popup;

            if (this.lastPopup !== null)
                this.lastPopup.close();

            popup = new BX.PopupWindow(
                this.getPopupName(type),
                null,
                {
                    content: this.getSelect(type),
                    closeIcon: {right: "20px", top: "10px" },
                    titleBar: {
                        content: BX.create("span", {
                            html: '<h2>' + BX.message['rover_acrm__' + type + '_title'] +'</h2>',
                            props: {className: 'access-title-bar'}
                        })
                    },
                    zIndex: 0,
                    offsetLeft: 0,
                    offsetTop: 0,
                    draggable: {restrict: false},
                    buttons: [
                        new BX.PopupWindowButton({
                            text: BX.message['rover_acrm__button_add'] ,
                            className: "popup-window-button-accept" ,
                            events: {click: function(){

                                var select = BX('form_values');

                                if (select.value > 0){
                                    window.location = 'rover-acrm__preset-update.php?source_type=' + type + '&source_id=' + select.value + '&lang=' + BX.message['rover_acrm__language_id'] ;
                                }

                                this.popupWindow.close();
                            }}
                        }),
                        new BX.PopupWindowButton({
                            text: BX.message['rover_acrm__button_close'] ,
                            className: "webform-button-link-cancel" ,
                            events: {click: function(){
                                this.popupWindow.close();
                            }}
                        })
                    ]
                });

            this.lastPopup = popup;

            return popup;
        }
    }
};