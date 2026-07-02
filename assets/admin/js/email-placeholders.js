( function () {
    const { __ } = window.wp.i18n;

    tinymce.PluginManager.add('easycommerce-email-placeholders', function (editor, url) {
        // Add a button to the TinyMCE toolbar
        editor.addButton( 'easycommerce_email_placeholders', {
            text: false,
            icon: 'icon dashicons-email-alt',
            onclick: function () {
                // Open a dialog with Select2 placeholder selector
                editor.windowManager.open({
                    title: __( 'Insert Email Placeholder', 'easycommerce' ),
                    body: [
                        {
                            type: 'container',
                            html: `
                                <div style="padding: 10px;">
                                    <label for="placeholder-select" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                        ${ __('Select a placeholder:', 'easycommerce') }
                                    </label>
                                    <select id="placeholder-select" style="width: 100%; padding: 8px;"></select>
                                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                                        ${ __('Select a placeholder to automatically insert it into your email template. These will be replaced with actual values when emails are sent.', 'easycommerce') }
                                    </p>
                                </div>
                            `
                        }
                    ],
                    buttons: [
                        {
                            text: __( 'Close', 'easycommerce' ),
                            onclick: function () {
                                editor.windowManager.close();
                            }
                        }
                    ],
                    onOpen: function () {
                        // Initialize Select2 when dialog opens
                        setTimeout(() => {
                            const selectElement = jQuery('#placeholder-select');

                            // Check if AJAX object is available
                            if ( typeof window.EASYCOMMERCE === 'undefined' ) {
                                return;
                            }

                            // Initialize Select2 with AJAX data source
                            selectElement.select2({
                                placeholder: __( 'Search for a placeholder...', 'easycommerce' ),
                                allowClear: true,
                                width: '100%',
                                ajax: {
                                    url: window.EASYCOMMERCE.rest_base + '/email-placeholders',
                                    dataType: 'json',
                                    delay: 250,
                                    headers: {
                                        'X-WP-Nonce': window.EASYCOMMERCE.nonce
                                    },
                                    data: function (params) {
                                        return {
                                            search: params.term || ''
                                        };
                                    },
                                    processResults: function (response) {
                                        // Handle the new response format from Rest trait
                                        if (response && response.success && Array.isArray(response.data)) {
                                            return {
                                                results: response.data
                                            };
                                        }
                                        // Handle error response from Rest trait
                                        else if (response && response.success === false) {
                                            console.error('TinyMCE Dropdown: API error', response.data || response);
                                            return {results: []};
                                        }
                                        // Fallback for direct array response (backward compatibility)
                                        else if (response && Array.isArray(response)) {
                                            return {
                                                results: response
                                            };
                                        }
                                        console.error('TinyMCE Dropdown: Failed to load placeholders', response);
                                        return {results: []};
                                    },
                                    cache: true,
                                },
                                minimumInputLength: 0,
                                templateResult: function (placeholder) {
                                    if (placeholder.loading) {
                                        return __( 'Loading...', 'easycommerce' );
                                    }

                                    // Handle category headers
                                    if (placeholder.disabled) {
                                        return jQuery(`
                                            <div style="font-weight: bold; color: #333; background-color: #f5f5f5; padding: 8px 4px; margin: 4px -12px; border-left: 4px solid #0073aa;">
                                                ${placeholder.text}
                                            </div>
                                        `);
                                    }

                                    if ( !placeholder.id ) {
                                        return placeholder.text;
                                    }

                                    const parts = placeholder.text.split(' - ');
                                    const placeholderName = parts[0];
                                    const description = parts[1] || '';

                                    return jQuery(`
                                        <div style="padding: 4px 0;">
                                            <strong style="color: #0073aa; font-family: Monaco, Consolas, 'Andale Mono', 'DejaVu Sans Mono', monospace; font-size: 13px;">
                                                ${placeholderName}
                                            </strong>
                                            ${description ? `<br><small style="color: #666; margin-left: 8px;">${description}</small>` : ''}
                                        </div>
                                    `);
                                },
                                templateSelection: function (placeholder) {
                                    // Show just the placeholder syntax when selected
                                    if (placeholder.id) {
                                        const parts = placeholder.text.split(' - ');
                                        return parts[0];
                                    }
                                    return placeholder.text;
                                }
                            });

                            // Insert placeholder immediately on selection
                            selectElement.on('select2:select', function (e) {
                                const selectedData = e.params.data;
                                if (selectedData && selectedData.id && !selectedData.disabled) {
                                    const parts = selectedData.text.split(' - ');
                                    const placeholderText = parts[0]; // Just the placeholder syntax
                                    editor.insertContent(placeholderText);
                                    editor.windowManager.close();
                                }
                            });

                            // Trigger search to load initial data
                            try {
                                selectElement.select2('open').select2('close');
                            } catch (e) {
                                console.warn('TinyMCE Dropdown: Could not trigger initial data load', e);
                            }
                        }, 100 );
                    }
                });
            }
        } );
    } );
} )();
