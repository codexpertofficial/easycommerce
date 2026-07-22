import { __ } from '@wordpress/i18n';

/**
 * Thin fetch wrappers around the EasyCommerce connectivity endpoints.
 *
 * Each endpoint uses wp_send_json_* on the server, so the parsed body is
 * `{ success: boolean, data: {...} }`. These helpers normalise that into a
 * `{ ok, data, message }` shape for the screens.
 */

const base = () => (window.EASYCOMMERCE && window.EASYCOMMERCE.rest_base) || "";
const nonce = () => (window.EASYCOMMERCE && window.EASYCOMMERCE.nonce) || "";

async function post(path, body) {
    try {
        const res = await fetch(`${base()}/connectivity/${path}`, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": nonce(),
            },
            body: JSON.stringify(body),
        });

        let json = {};
        try {
            json = await res.json();
        } catch (e) {
            json = {};
        }

        const data = json && json.data ? json.data : {};

        return {
            ok: !!(json && json.success),
            status: res.status,
            data,
            message: data.message || "",
        };
    } catch (e) {
        return {
            ok: false,
            status: 0,
            data: {},
            message: __("An error occurred. Please try again.", 'easycommerce'),
        };
    }
}

export const authApi = {
    login: (payload) => post("login", payload),
    register: (payload) => post("registration", payload),
    resetRequest: (payload) => post("reset-password", payload),
    resetConfirm: (payload) => post("reset-password-confirm", payload),
};
