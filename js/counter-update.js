function igspu_update_common_counters(step) {
    let commonSelectors = {
        "#wp-admin-bar-updates": {
            "location": "admin-bar",
            "hidable": "",
            "selector": ".ab-label",
            "behavior": "normal"
        },
        "#menu-plugins .wp-menu-name": {
            "location": "top-level-plugins-menu",
            "hidable": ".update-plugins",
            "selector": ".plugin-count",
            "behavior": "normal"
        },
        "#menu-dashboard .wp-submenu a[href='update-core.php']": {
            "location": "dashboard-menu",
            "hidable": ".update-plugins",
            "selector": ".update-count",
            "behavior": "normal"
        },
        "#menu-plugins .wp-submenu a[href='plugins.php?page=ignored-plugin-updates']": {
            "location": "plugin-menu-entry",
            "hidable": ".update-plugins",
            "selector": ".update-count",
            "behavior": "reverse"
        }
    };
    Object.entries(commonSelectors).forEach(([parent, data]) => {
        let fullSelector = parent + ' ' + data.hidable + ' ' + data.selector,
            currentCount = parseInt(jQuery(fullSelector).text(), 10),
            actualStep = step;
        if (data.behavior === 'reverse') {
            actualStep = -actualStep
        }
        if (data.location === "admin-bar" && isNaN(currentCount)) {
            igspu_create_admin_bar_element(actualStep);
            return;
        }
        if (isNaN(currentCount)) {
            jQuery(parent).append(' <span class="'+data.hidable.substring(1)+' count-1"><span class="'+data.selector.substring(1)+'">'+actualStep+'</span></span>');
            return;
        }
        let updatedCount = currentCount + actualStep;
        if (updatedCount === 0) {
            jQuery(parent + ' ' + data.hidable).hide();
        } else if (currentCount === 0 && updatedCount > 0) {
            jQuery(parent + ' ' + data.hidable).removeClass("count-0").addClass("count-1").show();
        }
        jQuery(fullSelector).text(updatedCount);
    })
}

function igspu_create_admin_bar_element(step) {
    let adminBarHTML = '<li id="wp-admin-bar-updates"><a class="ab-item" href="' + IGSPU.update_core_url + '"><span class="ab-icon" aria-hidden="true"></span><span class="ab-label" aria-hidden="true">' + step + '</span></a></li>'
    jQuery("#wp-admin-bar-site-name").after(adminBarHTML);
}