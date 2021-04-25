export default class Editor {
	/**
	 * Editor status.
	 *
	 * @type {'open'|'closed'}
	 */
	status = 'closed';

	/**
	 * Is document still saving?.
	 *
	 * @type {boolean}
	 */
	isSaving = false;

	/**
	 * Is document changed?.
	 *
	 * @type {boolean}
	 */
	isChanged = false;

	/**
	 * Is document changed during save?.
	 *
	 * @type {boolean}
	 */
	isChangedDuringSave = false;

	/**
	 * Is document saved?
	 *
	 * @type {boolean}
	 */
	isSaved = true;

	/**
	 * Last save history id.
	 *
	 * @type {number}
	 */
	lastSaveHistoryId = 0;
}
