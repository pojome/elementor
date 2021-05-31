export default class ContainerHelper {
	static isAllAliveRecursive( container ) {
		if ( container.view?.isDestroyed ) {
			return false;
		}

		if ( container.children.length ) {
			for ( const childContainer of container.children ) {
				if ( ! ContainerHelper.isAllAliveRecursive( childContainer ) ) {
					return false;
				}
			}
		}

		return true;
	}
}
