const easycommerce_modal = (show = true) => {
    const modal = document.getElementById("easycommerce-modal");
    if (show) {
        modal.style.display = "";
    } else {
        modal.style.display = "none";
    }
};

const easycommerce_toast = (message = "", show = true) => {
    let toast = document.getElementById("easycommerce-toast");
    let content;

    if (!toast) {

        toast = document.createElement("div");
        toast.id = "easycommerce-toast";
        toast.style.cssText = `
            position: fixed;
            top: 120px;
            right: 20px;
            background-color: rgb(19 193 19);
            color: rgb(255, 255, 255);
            padding: 14px 20px;
            border-radius: 6px;
            border-bottom: 4px solid rgb(116 203 116);
            box-shadow: rgba(0, 0, 0, 0.15) 0px 4px 12px;
            font-family: sans-serif;
            font-size: 14px;
            z-index: 9999;
            min-width: 200px;
        `;

        content = document.createElement("div");
        content.className = "easycommerce-toast-message";
        toast.appendChild(content);

        document.body.appendChild(toast);
    } else {
        content = toast.querySelector(".easycommerce-toast-message");
    }

    if (content) content.textContent = message;
    toast.style.display = show ? "" : "none";

    // Auto-hide after 3s
    if (show) {
        setTimeout(() => {
            toast.style.display = "none";
        }, 1000);
    }
};

const easycommerce_error_toast = (message = "") => {
    if (!document.getElementById("ec-toast-kf")) {
        const styleElement = document.createElement("style");
        styleElement.id = "ec-toast-kf";
        styleElement.textContent = "@keyframes ecTIn{0%{opacity:0;transform:translateX(110%)}60%{transform:translateX(-8px)}80%{transform:translateX(4px)}100%{opacity:1;transform:translateX(0)}}@keyframes ecTBar{from{transform:scaleX(1)}to{transform:scaleX(0)}}";
        document.head.appendChild(styleElement);
    }

    let toastElement = document.getElementById("easycommerce-error-toast");
    if (!toastElement) {
        toastElement = document.createElement("div");
        toastElement.id = "easycommerce-error-toast";
        toastElement.style.cssText = "position:fixed;top:0;right:20px;z-index:99999;min-width:300px;max-width:400px;font-family:sans-serif;";
        document.body.appendChild(toastElement);
    }

    toastElement.innerHTML = `<div style="margin:30px 0 0;background:#e74c3c;color:#fff;font-size:16px;font-weight:500;line-height:26px;padding:14px 40px 18px 16px;border-radius:4px;box-shadow:0 1px 10px rgba(0,0,0,.1),0 2px 15px rgba(0,0,0,.05);position:relative;overflow:hidden;animation:ecTIn .45s cubic-bezier(.21,1.02,.73,1) forwards;">${message}<button onclick="document.getElementById('easycommerce-error-toast').style.display='none'" style="position:absolute;top:6px;right:8px;background:none;border:none;color:rgba(255,255,255,.8);font-size:18px;cursor:pointer;line-height:1;padding:2px 4px;">&#x2715;</button><div style="position:absolute;bottom:0;left:0;right:0;height:4px;background:rgba(255,255,255,.7);transform-origin:left;animation:ecTBar 4000ms linear forwards;"></div></div>`;

    toastElement.style.display = "";
    clearTimeout(toastElement._hideTimer);
    toastElement._hideTimer = setTimeout(() => { toastElement.style.display = "none"; }, 4000);
};

/**
 * Easycommerce new order shipping and billing address slide effect
 */
jQuery(function ($) {
    $(document).on("click", ".easycommerce-new-order-shipping", function () {
        $(".easycommerce-new-order-shipping-details").slideToggle();
    });
    $(document).on("click", ".easycommerce-new-order-billing", function () {
        $(".easycommerce-new-order-billing-details").slideToggle();
    });

    // Setting Screen Submenu scroll
    const submenu = $('#easycommerce-settings-submenus');
	if (submenu.length) {
		submenu.on('wheel', function(e) {
			const deltaY = e.originalEvent.deltaY;
			const deltaX = e.originalEvent.deltaX;
			if (deltaY !== 0 && deltaX === 0) {
				e.preventDefault();
				submenu.scrollLeft(submenu.scrollLeft() + deltaY);
			}
		});
	}
});
