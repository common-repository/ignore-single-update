const Toast = Swal.mixin({
    customClass: {'container': 'ispu-ignore-confirmation ' + IGSPU.settings.theme.value + '-theme'},
});
let igspu;

(function ($) {
    igspu = {
        ajax_request: function (duration, link) {
            $.ajax({
                type: "post",
                dataType: "json",
                url: ajaxurl,
                success: function (response) {
                    if (response.success === true) {
                        igspu.counter_update(IGSPU.screen_id, duration);
                        igspu.remove_plugin_from_list(IGSPU.screen_id, link);
                        igspu.modal.confirmation(duration);
                    } else {
                        igspu.modal.error();
                    }
                },
                data: {
                    action: "igspu_ignore_plugin_update",
                    nonce: IGSPU.nonce,
                    plugin: link.data("plugin"),
                    name: link.data("name"),
                    version: link.data("version"),
                    duration: duration
                }
            })
        },
        modal:{
            ignore:function (swalOptions, link) {
                if (IGSPU.settings.ignore_popup === false) {
                    Toast.fire(swalOptions).then((result) => {
                        if (result.isConfirmed) {
                            let duration = result.value;
                            if (!duration) {
                                duration = IGSPU.settings.days;
                            }
                            igspu.ajax_request(duration, link);
                        }
                    })
                } else {
                    igspu.ajax_request(IGSPU.settings.days, link);
                }
            },
            confirmation:function (duration) {
                let swalOptions = {
                    toast: true,
                    position: 'center',
                    showConfirmButton: false,
                    timer: 2000,
                    icon: 'success'
                };
                if (duration === 'Forever') {
                    swalOptions.title = IGSPU.text.IgnoredForeverToast;
                } else if (parseInt(duration, 10) > 1) {
                    swalOptions.title=IGSPU.text.IgnoredToastPlural.replace("%s",parseInt(duration, 10));
                } else if (parseInt(duration, 10) === 0) {
                    swalOptions.title = IGSPU.text.IgnoredUntilNextVersionToast;
                } else {
                    swalOptions.title = IGSPU.text.IgnoredToastSingle;
                }
                Toast.fire(swalOptions);
            },
            error:function () {
                Toast.fire({
                    toast: true,
                    position: 'center',
                    showConfirmButton: false,
                    showCloseButton: true,
                    icon: 'error',
                    title: IGSPU.text.ErrorToast
                })
            }
        },
        remove_plugin_from_list: function (screenID, link) {
            if (screenID === IGSPU.screen_ids.plugins) {
                link.remove();
                $("#" + link.data("slug") + "-update .update-message").remove();
            } else {
                $(link).closest("tr").remove();
            }
        },
        counter_update: function (screenID, duration) {
            igspu_update_common_counters(-1);
            if (screenID === IGSPU.screen_ids.update_core) {
                let pluginCountEl = $("h2 .count")[0],
                    pluginCount = parseInt(pluginCountEl.innerText.match(/\d+/)[0], 10);
                if (pluginCount === 1) {
                    $('form[name="upgrade-plugins"]').remove();
                    let pluginHeading = pluginCountEl.closest("h2");
                    pluginHeading.closest("h2").innerText = IGSPU.text.Plugins;
                    pluginHeading.nextElementSibling.innerText = IGSPU.text.PluginsUpToDate;
                } else {
                    pluginCountEl.innerText = '(' + (pluginCount - 1) + ')';
                }
            } else {
                let updatesEl = $(".subsubsub .upgrade .count")[0],
                    updatesCount = parseInt(updatesEl.innerText.match(/\d+/)[0], 10);
                if (updatesCount === 1) {
                    $(".subsubsub .upgrade").remove();
                } else {
                    updatesEl.innerText = '(' + (updatesCount - 1) + ')';
                }
            }
            let notice = $(".ispu-notice");
            if (!notice.length) {
                return;
            }
            if (duration !== 'Forever' && $(".ispu-notice.notice-error").length) {
                return;
            }
            let newCounter = parseInt(notice.data('count'), 10) + 1;
            notice.data('count', newCounter);
            $(".ispu-counter").text(newCounter);
        },
        
        get_relative_position:function (element, textNode) {
            let elementRect = element[0].getBoundingClientRect(),
                range = document.createRange();
            range.selectNode(textNode)
            let textRect = range.getBoundingClientRect();
            return textRect.left - elementRect.left;
        }
    }
    if (IGSPU.screen_id === IGSPU.screen_ids.update_core) {
        $("#update-plugins-table .plugins tr").each(function () {
            let $this = $(this),
                pluginfile = $this.find(".check-column input").attr('value'),
                pattern = /\d+(\.\d+)*/,
                version = $this.find(".plugin-title a").text().match(pattern)[0];
            
            if (!IGSPU.settings.patch_versions || (IGSPU.settings.patch_versions && IGSPU.updateTypes[pluginfile] !== '3')) {
                let lineCount = $this.find("p br").length,
                    name = $this.find("strong:first").text(),
                    link = '<a class="ignore-version" ';
                if (lineCount === 2) {
                    let el = $this.find(".plugin-title p"),
                        textNodes = el.contents().filter(function () {
                            return this.nodeType === Node.TEXT_NODE;
                        });
                    let left = igspu.get_relative_position(el, textNodes[textNodes.length - 1]);
                    link += 'style="position:relative;left:' + left + 'px" ';
                }
                link += 'href="javascript:void(0)" data-plugin="' + pluginfile + '" data-version="' + version + '" data-name="' + name + '">' + IGSPU.text.Ignore + '</a>';
                $this.find(".plugin-title p").append("<br>" + link);
            }
            if (IGSPU.expired_text[pluginfile] && IGSPU.ignored_updates[pluginfile] !== undefined && version === IGSPU.ignored_updates[pluginfile].ignored_version) {
                $this.find("a.open-plugin-details-modal").after(" <span style=\"color:green;font-weight:bold\">(" + IGSPU.expired_text[pluginfile] + ")</span>");
            }
        })
    }
    

    $(document).on('click', '.ispu-notice .notice-dismiss', function () {
        let notice = $(this).closest('.ispu-notice'),
            type = notice.data('notice'),
            days = notice.data('noticedays');
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: 'dismissed_notice_handler',
                type: type,
                days: days,
                nonce: IGSPU.nonce
            }
        });
    });
    $(document).on("click", ".ignore-version", function () {
        Object.entries(IGSPU.text).forEach(([key, value]) => {
            if (typeof value === 'object') {
                let textKey = key;
                Object.entries(value).forEach(([key, value]) => {
                    IGSPU.text[textKey][key] = value.replace('&#039;', '\'');
                })
            } else {
                IGSPU.text[key] = value.replace('&#039;', '\'');
            }
        })
        let link = $(this),
            swalOptions = {
                titleText: link.data("name"),
                html: IGSPU.text.ConfirmDays.replace("%s",link.data("version")) + '<br>0=' + IGSPU.text.UntilNext,
                input: 'text',
                inputPlaceholder: IGSPU.settings.days,
                confirmButtonText: IGSPU.text.OKButton,
                cancelButtonText: IGSPU.text.CancelButton,
                showCancelButton: true,
                inputValidator: (value) => {
                    if (isNaN(value)) {
                        return IGSPU.text.ConfirmError
                    }
                }
            };
        if (IGSPU.settings.permanent) {
            swalOptions.footer = '<span class="ignore-all-versions">' + IGSPU.text.IgnoreAllVersions + '</span>';
        }
        igspu.modal.ignore(swalOptions, link);

        $(document).on("click", ".ignore-all-versions", function () {
            
            Toast.fire({
                    titleText: IGSPU.text.AreYouSure,
                    text: IGSPU.text.ConfirmIgnoreAllVersions,
                    confirmButtonText: IGSPU.text.OKButton,
                    cancelButtonText: IGSPU.text.CancelButton,
                    showCancelButton: true
                }
            ).then((result) => {
                if (result.isConfirmed) {
                    igspu.ajax_request('Forever', link);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    igspu.modal.ignore(swalOptions, link);
                }
            })
        });
    });
})(jQuery);