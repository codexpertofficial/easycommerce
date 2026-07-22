// ExampleQuestions.jsx
import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

const ExampleQuestions = ({ onSelect }) => {
  const examples = [
      __( 'What is today\'s total revenue?', 'easycommerce' ),
      __( 'How many orders were placed this week?', 'easycommerce' ),
      __( 'Which product is performing best?', 'easycommerce' ),
      __( 'What is my average order value this month?', 'easycommerce' ),
      __( 'How many new vs returning customers this week?', 'easycommerce' ),
      __( 'Which products are low in stock?', 'easycommerce' ),
  ];

  const [activeIndex, setActiveIndex] = useState(null);

  const handleClick = (question, index) => {
    setActiveIndex(index);
    onSelect(question, () => setActiveIndex(null));
  };

  return (
    <div className='pb-[80px] pt-[26px] px-[56px]'>
      <h3 className="text-xl font-inter font-semibold text-ec-title pb-[14px]">{ __( 'Examples', 'easycommerce' ) }</h3>
      <ol className="space-y-2">
        {examples.map((example, index) => (
          <li key={index}>
            <button
              className={`text-left text-[14px] text-[#5031cfd6]`}
              onClick={() => handleClick(example, index)}
            >
              {index + 1}. {example}
            </button>
          </li>
        ))}
      </ol>
    </div>
  );
};

export default ExampleQuestions;
