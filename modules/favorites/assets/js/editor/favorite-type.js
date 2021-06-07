export default class FavoriteType {
	/**
	 * Get the name of the type.
	 *
	 * @return {string}
	 */
	getName() {}

	/**
	 * Create new user favorite to the type.
	 *
	 * @param slug
	 *
	 * @return {*}
	 */
	create( slug ) {}

	/**
	 * Delete existing favorite from the type.
	 *
	 * @param slug
	 *
	 * @return {*}
	 */
	delete( slug ) {}

	/**
	 * Toggle favorite of this type.
	 *
	 * @param slug
	 *
	 * @return {*}
	 */
	toggle( slug ) {}
}
