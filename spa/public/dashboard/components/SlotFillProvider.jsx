import { SlotFillProvider } from '@wordpress/components';

const CustomSlotFillProvider = ({ children }) => {
  return <SlotFillProvider>{children}</SlotFillProvider>;
};

export default CustomSlotFillProvider;
