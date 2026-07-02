const subMenuList = [
    {
        id: "orders",
        menus: ['orders', 'transactions', 'downloads']
    }, 
    {
        id: "profile",
        menus: ['profile', 'address', 'password']
    }
];
const sideMenuIdentifier = "#easycommerce_dashboard_render .easycommerce-sidebar-submenu";

jQuery(function ($) {
    $(document).ready( function () {
        const hash = window.location.hash;

        if (hash) {
            const id = hash.replace("#", "");
            const singleOrderRegex = /orders\/(\d+)/;

            if (id === "orders" || id.match(singleOrderRegex)) {
                $(`${sideMenuIdentifier} #easycommerce-submenu-orders`).slideDown();
            } else if (subMenuList.find(item => item.menus.includes(id))) {
                $(`${sideMenuIdentifier} #easycommerce-submenu-${subMenuList.find(item => item.menus.includes(id)).id}`).slideDown();
            }
        }
    });

    // add an event listner to hash change
    $(window).on("hashchange", function () {
        const hash = window.location.hash;

        if (hash) {
            const id = hash.replace("#", "");
            const singleOrderRegex = /orders\/(\d+)/;

            if (id === "orders" || id.match(singleOrderRegex)) {
                $(`${sideMenuIdentifier} #easycommerce-submenu-orders`).slideDown();
            }
        }
    });

    $( `${sideMenuIdentifier} .easycommerce-sidebar-btn` ).on("click", function () {
        $(this).next(".easycommerce-submenu-wrapper").slideToggle();
    });
});
