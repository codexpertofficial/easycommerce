import React, {useState} from 'react'
import { Slot } from '@wordpress/components';

const SlotField = ({name, item = '', fillProps = ''}) => {
    const [slotValue, setSlotValue] = useState(null);
    
    const handleChange = (value) => {
        setSlotValue(value);
    };

    return (
        <Slot 
            name={name}
            fillProps={{ item: item, value: slotValue, handleChange: handleChange, ...fillProps }}
        />
    )
}

export default SlotField