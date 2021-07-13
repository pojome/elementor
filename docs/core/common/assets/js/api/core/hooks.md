## API - `$e.hooks`
*  **Description**: `$e.hooks` api is a manager of `$e.hooks.ui` & `$e.hooks.data`, allow you to create custom hooks.
the hooks attached to $e.commands and each hook fired _after/before_ running a command, that runs by $e.run().
*  **Location**: *core/common/assets/js/api/core/hooks.js*
*  **Methods**:

	| Method                               | Params                                                                                          |  Returns              | Description                                              
	|--------------------------------------|-------------------------------------------------------------------------------------------------|-----------------------|-----------------------------------------------------------
	| `$e.hooks.activate()`                |                                                                                                 |                       | Activate all hooks. 
	| `$e.hooks.deactivate()`              |                                                                                                 |                       | Deactivate all hooks. 
	| `$e.hooks.getAll()`                  |                                                                                                 | `{Array}`             | Receive all loaded hooks. 
	| `$e.hooks.register()`                | `{String}` *type*, `{String}` *event*, `{HookBase}` *instance*                                  | `{Object}` *callback* | Register a hook. 
	| `$e.hooks.run()`                     | `{String}` *type*, `{String}` *event*, `{String}` *command*, `{Object}` *args*, `{*}` *result*  | `{Boolean}`           | Run a hook. 
	| `$e.hooks.registerDataAfter()`       | `{HookBase}` *instance*                                                                         | `{Object}` *callback* | Register data hook that runs after the command runs.  
	| `$e.hooks.registerDataCatch()`       | `{HookBase}` *instance*                                                                         | `{Object}` *callback* | Register data hook that runs when the command fails.  
	| `$e.hooks.registerDataDependency()`  | `{HookBase}` *instance*                                                                         | `{Object}` *callback* | Register data hook that runs before the command runs as dependency.  
	| `$e.hooks.registerUIAfter()`         | `{HookBase}` *instance*                                                                         | `{Object}` *callback* | Register UI hook that runs after the commands run.  
	| `$e.hooks.registerUICatch()`         | `{HookBase}` *instance*                                                                         | `{Object}` *callback* | Register UI hook that runs when the command fails.  
	| `$e.hooks.registerUIBefore()`        | `{HookBase}` *instance*                                                                         | `{Object}` *callback* | Register UI hook that runs before the command.  
	| `$e.hooks.runDataAfter()`            | `{String}` *command*, `{Object}` *args*, `{*}` *result*                                         | `{Boolean}`           | Run a data hook that runs after the command.
	| `$e.hooks.runDataCatch()`            | `{String}` *command*, `{Object}` *args*, `{*}` *result*                                         | `{Boolean}`           | Run a data hook that runs when the command fails.
	| `$e.hooks.runDataDependency()`       | `{String}` *command*, `{Object}` *args*, `{*}` *result*                                         | `{Boolean}`           | Run a data hook that runs before the command as dependency.
	| `$e.hooks.runUIAfter()`              | `{String}` *command*, `{Object}` *args*, `{*}` *result*                                         | `{Boolean}`           | Run a UI hook that runs after the commands run.
	| `$e.hooks.runUICatch()`              | `{String}` *command*, `{Object}` *args*, `{*}` *result*                                         | `{Boolean}`           | Run a UI hook that runs when the command fails.
	| `$e.hooks.runUIBefore()`             | `{String}` *command*, `{Object}` *args*, `{*}` *result*                                         | `{Boolean}`           | Run a UI hook that runs before the command.

## Guidelines, conventions & file's structure 
  * Each hook is owned by a [component](../core/components.md#guidelines-conventions--files-structure).
  * Each [component](../core/components.md#guidelines-conventions--files-structure), can extend `defaultHooks` method which are used to import the hooks.
  * The hooks imported via built-in method called `importHooks`.
    * All the hooks should be exported in one index file:
        ```javascript
        // index.js
        export { FooterSaverRefreshMenu } from './ui/document/elements/settings/footer-saver-refresh-menu';
        export { UpdateButton } from './ui/document/save/set-is-modifed/update-button';
        export { BypassImport } from './data/document/elements/import/bypass-import';
        export { SaveExtras } from './data/document/save/save/save-extras';
        ```
        You can have as many indexes in every hierarchy under `component/hooks/what-ever-you-wish` as you wish to organize your code, the requirement is to have one index file,
        at `component/hooks/index.js` which exports all the hooks, take the **index.js** example above as a scenario:
        ```html class:"lineNo"
        1  📦 component
        2  │   📜 component.js
        3  │
        4  └───📂 hooks
        5  │   │   📜 index.js ( has all the hooks exported )
        6  │   │
        7  │   └───📂 ui
        8  │   │   └───📂 document
        9  │   │   │   └───📂 elements
        10 │   │   │   │   └───📂 settings
        11 │   │   │   │   │   │   📜 footer-saver-refresh-menu.js
        12 │   │   │   │   │   │   ...
        13 │   │   │   └───📂 save
        14 │   │   │   │   └───📂 set-is-modfifed
        15 │   │   │   │   │   │   📜 update-button.js
        16 │   │   │   │   │   │   ...
        17 │   │   📜 index.js ( has all ui hooks exported )
        18 │   │   ...
        19 │   └──📂 data
        20 │   │   └───📂 document
        21 │   │   │   └───📂 elements
        22 │   │   │   │   └───📂 import
        23 │   │   │   │   │   │   📜 bypass-import.js
        24 │   │   │   │   │   │   ...
        25 │   │   │   └───📂 save
        26 │   │   │   │   └───📂 save
        27 │   │   │   │   │   │   📜 save-extras.js
        28 │   │   │   │   │   │   ...
        29 │   │   📜 index.js ( has all data hooks exported )
        30 │   │   ...
        ```
        `component/hooks/index.js` file at line *5*:
        ```javascript
        export * from './ui/';
        export * from './data/';
        ```
        `component/hooks/ui/index.js` file at line *17*:
        ```javascript
        export { FooterSeverRefreshMenu } from './document/elements/settings/footer-saver-refresh-menu';
        export { UpdateButton } from './document/save/set-is-modifed/update-button';
        ```      
        `component/hooks/data/index.js` file at line *29*:
        ```javascript
        export { BypassImport } from './document/elements/import/bypass-import';
        export { SaveExtras } from './document/save/save/save-extras';
        ```
    * use `importHooks` example: `component/component.js` file at line *2*:
        ```javascript
        import * as hooks from './hooks/';

        export class Component extends $e.modules.ComponentBase {
            getNamespace() {
                return 'component-name';
            }

            defaultHooks() {
                return this.importHooks( hooks );
            }
        }
        ```
  * Hook conventions
    ```javascript class:"lineNo"
    1 // {FILE_PATH}/{FILE_NAME} - This is line should be deleted - just for the exmaple.
    2 import HookUIAfter from 'elementor-api/modules/hooks/{TYPE}/after';
    3  
    4 export class {FILE_NAME_CAMEL_CASE} extends HookUIAfter {
    5  	getCommand() {
    6  		return '{COMMAND}';
    7  	}
    8  
    9  	getId() {
    10		return '{FILE_NAME_WITHOUT_JS}--{COMMAND}';
    11	}
    12
    13	getContainerType() {
    14		return '{CONTAINER_TYPE}';
    15	}
    16
    17	getConditions( args ) {
    18		return args.settings && 'undefined' !== typeof args.settings.post_status;
    19	}
    20
    21	apply( args ) {
    22		const { footerSaver } = $e.components.get( 'document/save' );
    23
    24		footerSaver.setMenuItems( args.container.document );
    25
    26		footerSaver.refreshWpPreview();
    27	}
    28 }
    29
    30 export default {FILE_NAME_CAMEL_CASE};
    ```
    > Legend

    | Name                   | Format  - Description               | Value for example. 
    |------------------------|-------------------------------------|---------------------
    |`{TYPE}`                | `ui` or `data` depends on the hook  | `ui`
    |`{COMMAND}`             | which command to hook               | `document/elements/settings`
    |`{FILE_NAME}`           | kebab case, name is description oh what the hook does | `footer-saver-refresh-menu.js`
    |`{FILE_NAME_CAMEL_CASE}`| camel case, of `{FILE_NAME}`        | `FooterSaverRefreshMenu`
    |`{FILE_NAME_WITHOUT_JS}`| `{FILE_NAME}` without `.js`         | `footer-saver-refresh-menu`
    |`{FILE_PATH}`           | `{TYPE}/{COMMAND}/{FILE_NAME}`      | `ui/document/elements/settings/footer-saver-refresh-menu.js`
    |`{CONTAINER_TYPE}`      | optional, gain performance if container type is known in advance | `document`
    > Example
    ```javascript class:"lineNo"
    1 // ui/document/elements/settings/footer-saver-refresh-menu.js - This line should be deleted - just for the example.
    2 import HookUIAfter from 'elementor-api/modules/hooks/ui/after';
    3  
    4 export class FooterSaverRefreshMenu extends HookUIAfter {
    5  	getCommand() {
    6  		return 'document/elements/settings';
    7  	}
    8
    9  	getId() {
    10		return 'footer-save-refresh-menu--document/elements/settings';
    11	}
    12
    13	getContainerType() {
    14		return 'document';
    15	}
    16
    17	getConditions( args ) {
    18		return args.settings && 'undefined' !== typeof args.settings.post_status;
    19	}
    20
    21	apply( args ) {
    22		const { footerSaver } = $e.components.get( 'document/save' );
    23
    24		footerSaver.setMenuItems( args.container.document );
    25
    26		footerSaver.refreshWpPreview();
    27	}
    28 }
    29
    30 export default FooterSaverRefreshMenu; // Required - comment should be deleted.
    ```

###  **Note:** further information about how to use hooks, can be found at [`{$e.hooks.ui}`](hooks/ui.md) and [`{$e.hooks.data}`](hooks/data.md) accoridng to their type.

### [Back](../index.md) 
