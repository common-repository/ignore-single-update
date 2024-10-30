(function ($) {
    $(document).on("click", ".ispu-table-button", function (e) {
        e.preventDefault();
        let plugin = $(this).data("plugin"),
            type = $(this).data("type");
        $(".refreshingtable").addClass("refreshing");
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajaxurl,
            success: function (response) {
                if (response.success === true) {
                    igspu_refresh_table(false);
                }
            },
            data: {
                action: "igspu_unignore_plugin_update",
                nonce: IGSPU.nonce,
                plugin: plugin,
                type: type
            }
        })
    });

    $(".ispu-settings .dashicons").on("click keypress", function () {
        let $this = $(this),
            option = $this.data('option'),
            desc = $this.data('desc');
        igspu_fire_settings_popup(option, desc);
    });

    $(".ispu-settings-nav a").on("click", function (e) {
        e.preventDefault();
        igspu_switch_tab($(this).data("tab"));
    });

    function igspu_switch_tab(tab) {
        let tabCount = 4;
        tab = parseInt(tab, 10);
        for (let i = 1; i <= tabCount; i++) {
            if (i !== tab) {
                $("#ispu-switch-" + i).removeClass("nav-tab-active").blur();
                $("#ispu-tab-" + i).hide();
            }
        }
        let tabNames = {'1': '', '2': 'settings', '3': 'info', '4': 'plans'};
        if (tab===4){
            $(".upgrade-box .text-center").hide();
        }else{
            $(".upgrade-box .text-center").show();
        }
        $("#ispu-switch-" + tab).addClass("nav-tab-active").focus();
        $("#ispu-tab-" + tab).show();
        if (typeof (history.pushState) != "undefined") {
            let url = new URL(location);
            if (tab !== 1) {
                url.searchParams.set('tab', tabNames[tab]);
            } else {
                url.searchParams.delete('tab');
            }
            history.pushState({}, "", url);
        }
    }

    window.addEventListener("popstate", function (e) {
        window.location.reload();
    });

    $(".ispu-switch input").on("change", function () {
        let value = false,
            $this = $(this),
            optionName = $this.attr("name"),
            shows = $this.data("shows");
        if (this.checked) {
            value = true;
        }
        if (shows) {
            igspu_switch_setting_visibility(optionName, shows, value)
        }

        
        igspu_update_setting(optionName, value);
    });

    $(".ispu-currency-switch input").on("change", function () {
        purchase_currency = $('input[name="currency-toggle"]:checked').val();
        if (purchase_currency === 'usd') {
            $(".currency-symbol").html('$');
        } else {
            $(".currency-symbol").html('€');
        }
    });

    

    function igspu_switch_setting_visibility(originalOptionName, targetOptionNames,value)
    {
        targetOptionNames = targetOptionNames.split(",");
        targetOptionNames.forEach((targetOptionName) => {
            if (value === true || parseInt(value,10)>0) {
                $(".hidden-" + targetOptionName).fadeIn();
            } else {
                $(".hidden-" + targetOptionName).fadeOut();
            }
        })
    }

    $(".ispu-select").on('focus', function () {
        let select = $(this);
        select.data('previous', select.val());
    }).on("change", function () {
        let select = $(this),
            optionName = select.attr("name"),
            value,
            shows = select.data("shows");
            if (optionName.includes("[]")){
                optionName=optionName.slice(0, -2);
                value=$("#" + optionName).val();
            }else {
                value = $(".ispu-select[name=" + optionName + "] :selected").val();
            }
        if (shows) {
            igspu_switch_setting_visibility(optionName,shows,value)
        }
        if (optionName === 'theme') {
            IGSPU.settings.theme.value = value;
            $("#sweetalert-css").attr("href", IGSPU.settings.theme.path + value + '.css');
        }
        if (optionName !== 'notices' || value !== 'disabled') {
            
            igspu_update_setting(optionName, value);
            return;
        }
        swalOptions = {
            titleText: IGSPU.text.AreYouSure,
            text: IGSPU.text.ConfirmDisableNotices,
            customClass: {'container': IGSPU.settings.theme.value + '-theme'},
            confirmButtonText: IGSPU.text.DisableButton,
            cancelButtonText: IGSPU.text.CancelButton,
            showCancelButton: true,
        }
        if (select.data('previous') !== 'critical') {
            swalOptions.showDenyButton = true;
            swalOptions.denyButtonText = IGSPU.text.DenyButton;
            swalOptions.focusDeny = true;
            swalOptions.denyButtonText = IGSPU.text.DenyButton;
            swalOptions.text += ' ' + IGSPU.text.HowAboutCritical
        }
        Swal.fire(swalOptions).then((result) => {
            if (result.isConfirmed) {
                select.data('previous', value);
                igspu_update_setting(optionName, value);
                return;
            }
            if (result.isDenied) {
                if (select.data('previous') !== 'critical') {
                    select.data('previous', value);
                    value = 'critical';
                    igspu_update_setting(optionName, value)
                }
                select.val('critical');
                return;
            }
            select.val(select.data('previous'))
        })
    });

    $(document).on("click", ".ispu-save-button", function (e) {
        e.preventDefault();
        let option = $(this).data("option"),
            value = $("input[name=" + option + "]").val();
        igspu_update_setting(option, value)
    })

    $(document).on("click", ".ispu-upgrade", function (e) {
        e.preventDefault();
        igspu_switch_tab(4);
    })

    $(".show-refund-policy").on("click", function (e) {
        e.preventDefault();
        Swal.fire({
            customClass: {'container': 'refund-policy ' + IGSPU.settings.theme.value + '-theme'},
            title: $(".refund-heading").clone(),
            html: $(".refund-content").clone()
        })
    })

    $(".swal-image").on("click", function (e) {
        e.preventDefault();
        let width = $(this).data("width"),
            height = $(this).data("height"),
            ratio = width / height;
        if (width + 50 > window.innerWidth) {
            width = window.innerWidth - 50;
            height = width / ratio;
        }
        Swal.fire({
            imageUrl: $(this).attr("href"),
            customClass: {'container': 'ispu-swal-screenshots ' + IGSPU.settings.theme.value + '-theme'},
            imageWidth: width + 'px',
            imageHeight: height + 'px'
        })
    })


    function igspu_fire_settings_popup(option, desc) {
        SwalOptions = {
            toast: true,
            position: 'center',
            customClass: {'container': IGSPU.settings.theme.value + '-theme'},
            showConfirmButton: true,
            icon: 'info'
        };
        if (option==='autopilot') {
            SwalOptions['html'] = IGSPU.settings[option].html;
            SwalOptions['customClass']['container'] += ' ispu-html-instructions ispu-html-' + option;
        } else {
            SwalOptions['title'] = desc;
        }
        Swal.fire(SwalOptions);
    }


    function igspu_update_setting(optionName, value, toast = true) {
        Swal.showLoading();
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajaxurl,
            success: function (response) {
                if (response.success === true) {
                    if (Array.isArray(optionName)) {
                        optionName.every(option => {
                            if (IGSPU.settings[option].tableRefresh === true) {
                                igspu_refresh_table(response.data);
                                return false;
                            }
                        })
                    } else if (IGSPU.settings[optionName].tableRefresh === true) {
                        igspu_refresh_table(response.data);
                    }
                    if (toast) {
                        Swal.fire({
                            toast: true,
                            customClass: {'container': IGSPU.settings.theme.value + '-theme'},
                            position: 'center',
                            showConfirmButton: false,
                            timer: 2000,
                            icon: 'success',
                            title: IGSPU.text.SettingsUpdateSuccess
                        });
                    }
                }
            },
            data: {
                action: "igspu_update_settings",
                nonce: IGSPU.nonce,
                setting: optionName,
                value: value
            }
        })
    }

    function igspu_refresh_table(forceRefreshValues) {
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajaxurl,
            success: function (response) {
                if (response.success === true) {
                    $(".ispu-refreshable").replaceWith(response.data);
                }
            },
            data: {
                action: "igspu_refresh_table",
                nonce: IGSPU.nonce,
                force_refresh: forceRefreshValues
            }
        })
    }
})(jQuery);