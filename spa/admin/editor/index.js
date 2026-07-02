import React from 'react';
import { createRoot } from 'react-dom/client';
import { applyFilters } from '@wordpress/hooks';
import EditorApp from './App.jsx';

/**
 * Filters the modal container ID.
 *
 * @since 1.0.0
 * @param {string} containerId The container ID.
 */
const containerId = applyFilters('easycommerce.editor.modal.container', 'ai-editor-modal-container');

// Create a container for our modal
const createModalContainer = () => {
    let container = document.getElementById(containerId);
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        document.body.appendChild(container);
    }
    return container;
};

// Initialize the app when DOM is ready
const initializeApp = () => {
    const container = createModalContainer();
    const root = createRoot(container);
    root.render(<EditorApp />);
};

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}