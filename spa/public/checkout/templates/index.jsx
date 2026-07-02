import React from "react";

// Templates
import TemplateOne from "./TemplateOne";
import TemplateTwo from "./TemplateTwo";
import TemplateThree from "./TemplateThree";

const Templates = ({ activeTemplate }) => {
    // create a funtion to return specific template
    const getTemplate = (template) => {
        switch (template) {
            case "template-1":
                return <TemplateOne />;
            case "template-2":
                return <TemplateTwo />;
            case "template-3":
                return <TemplateThree />;
            default:
                return null;
        }
    };

    return getTemplate(activeTemplate);
};

export default Templates;
