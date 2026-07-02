import { ColorPalette } from "@wordpress/components";
function Color({ attributes, attributeName, setAttributes, title }) {
    return (
        <div className="codesigner-color-component-wrap">
            <h3>{title}</h3>
            <ColorPalette
                value={attributes[attributeName]}
                onChange={(newVal) =>
                    setAttributes({ [attributeName]: newVal })
                }
                enableAlpha={true}
            />
        </div>
    );
}
export default Color;
