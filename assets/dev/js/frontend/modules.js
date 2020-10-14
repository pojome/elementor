import Document from './document';
import StretchElement from './tools/stretch-element';
import BaseHandler from './handlers/base';
import SwiperBase from './handlers/base-swiper';

elementorModules.frontend = {
	Document: Document,
	tools: {
		StretchElement: StretchElement,
	},
	handlers: {
		Base: BaseHandler,
		SwiperBase: SwiperBase,
	},
};
