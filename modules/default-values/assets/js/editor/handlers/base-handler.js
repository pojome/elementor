export default class BaseHandler {
	appendSettingsForSave( settings, container ) {
		return settings;
	}

	appendSettingsForRecreate( element, container, newDefaultValues ) {
		return element;
	}

	afterSaved( type, settings ) {
		//
	}

	registerHooks() {
		//
	}
}
