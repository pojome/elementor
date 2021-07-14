import BackgroundSlideshow from '../background-slideshow';
import BackgroundVideo from '../background-video';
import HandlesPosition from './handles-position';
import StretchedSection from './stretched-section';
import Shapes from './shapes';

export default [
	StretchedSection, // Must run before BackgroundSlideshow to init the slideshow only after the stretch.
	BackgroundSlideshow,
	BackgroundVideo,
	HandlesPosition,
	Shapes,
];
