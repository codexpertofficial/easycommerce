import { Fancybox } from '@fancyapps/ui/dist/fancybox/';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

export default function useFancybox() {
	const openFancybox = (src) => {
		Fancybox.show([{ src, type: 'image' }]);
	};

	return openFancybox;
}
