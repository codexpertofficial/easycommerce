const currentHash = window.location.hash;

(EASYCOMMERCE.admin.menus || []).forEach((menu) => {
    const slug = menu.slug;
    const subMenuItems = document.querySelectorAll(
        `#toplevel_page_${slug} .wp-submenu.wp-submenu-wrap > li`
    );

    if (!subMenuItems.length) return;

    const syncCurrentMenu = (hash) => {
        let matched = false;
        subMenuItems.forEach((li) => {
            li.classList.remove('current');
            const a = li.querySelector('a');
            if (a && a.hash && (hash.includes(a.hash) || hash === a.hash)) {
                li.classList.add('current');
                matched = true;
            }
        });
        // No hash match (empty/bare #) → highlight Dashboard (first item)
        if (!matched) {
            const first = [...subMenuItems].find(li => li.classList.contains('wp-first-item'));
            if (first) first.classList.add('current');
        }
    };

    subMenuItems.forEach(function (item) {
        item.addEventListener('click', function () {
            subMenuItems.forEach((li) => li.classList.remove('current'));
            item.classList.add('current');
        });
    });

    syncCurrentMenu(currentHash);
    window.addEventListener('hashchange', () => syncCurrentMenu(window.location.hash));

    // Add # to the first submenu item
    document.addEventListener('DOMContentLoaded', function () {
        const submenuWrapper = document.querySelector(`#toplevel_page_${slug} .wp-submenu`);
        if (submenuWrapper) {
            const firstItem = submenuWrapper.querySelector('li.wp-first-item a');
            if (firstItem && !firstItem.href.endsWith('#')) {
                firstItem.href += '#';
            }
        }
    });
});

//checkout templates @todo remove when settings page is in react
jQuery(document).ready(function($) {

    const { __ } = wp.i18n;

    var templateImages = {
        'template-1': EASYCOMMERCE.assets + 'admin/img/checkout-templates/template-1.png',
        'template-2': EASYCOMMERCE.assets + 'admin/img/checkout-templates/template-2.png',
        'template-3': EASYCOMMERCE.assets + 'admin/img/checkout-templates/template-3.png',
    };

    var $select = $('#easycommerce-field-checkout_template');
    var $wrapper = $('#easycommerce-field-wrapper-checkout_template');

    if ($('#easycommerce-checkout-template-preview').length === 0) {
        $wrapper.after('<img id="easycommerce-checkout-template-preview" alt="' + __( 'Template Preview', 'easycommerce' ) + '" src="" style="display:none;margin-top:10px;max-width:100%;border:1px solid #ddd;border-radius:8px;">');
    }

    var $img = $('#easycommerce-checkout-template-preview');
    var $button = $('#easycommerce-checkout-template-preview-button');

    function updateTemplatePreview() {
        var selectedValue = $select.val();
        var imgUrl = templateImages[selectedValue];
        if (imgUrl) {
            $img.attr('src', imgUrl);
        } else {
            $img.hide();
        }
    }

    updateTemplatePreview();
    $select.on('change', updateTemplatePreview);
    $button.on('click', function() {
        $img.toggle();
    });
});