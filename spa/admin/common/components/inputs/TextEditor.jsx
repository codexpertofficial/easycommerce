import { useEffect, useRef } from "react";

function TextEditor({ content, onChange, handleMediaUpload }) {
    const editorRef = useRef(null);
    const editorId = "easycommerce-classic-editor"; // Unique ID for the textarea

    useEffect(() => {
        const textarea = editorRef.current;
        if (!textarea) return;

        window.tinymce.init({
            target: textarea,
            plugins: ["link", "lists", "textcolor", "charmap"],
            toolbar:
                "formatselect add_custom_wp_media | bold italic underline strikethrough blockquote | \
                 forecolor backcolor | link | alignleft aligncenter alignright alignjustify | \
                 bullist numlist",
            relative_urls: false,
            remove_script_host: false,
            document_base_url: window.location.origin + "/",
            setup: (editor) => {
                editor.addButton("add_custom_wp_media", {
                    text: "Add Media",
                    icon: "image",
                    onclick: () => handleMediaUpload(),
                });

                editor.on("Change", () => {
                    onChange(editor.getContent());
                });

                editor.on("init", () => {
                    editor.setContent(content);
                });
            },
        });

        return () => {
            window.tinymce.get(editorId)?.remove();
        };
    }, []); // Only initialize once

    useEffect(() => {
        // Update content only when it's different to avoid cursor issues
        const editor = window.tinymce.get(editorId);
        if (editor && editor.getContent() !== content) {
            editor.setContent(content);
        }
    }, [content]);

    return (
        <textarea id={editorId} ref={editorRef}></textarea>
    );
}

export default TextEditor;
