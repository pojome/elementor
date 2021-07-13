## API --  `$e.extras.hashCommands`
HashCommands API, provides a simple and convenient way to run command, routes from a browser url hash (#).

*  **Description**: `$e.extras.hashCommands` API is a manager of hash commands.

*  **Location**: */core/common/assets/js/core/extras/hash-commands.js*

*  **Methods**:

    | Method                               | Params                                                                                | Returns                                                | Description                                                                         |
    |--------------------------------------|---------------------------------------------------------------------------------------|--------------------------------------------------------|-------------------------------------------------------------------------------------|
    | `$e.commands.get()`                  | `{string}` *hash*, __default__: `hash = location.hash`                                | `{Array.<HashCommand>}` *commands extracted from hash* | Get API requests that comes from hash ( eg #e:run ).
    | `$e.commands.run()`                  | `{Array.<HashCommand>}` *commands*, __default__: `commands = this.commands`           |                                                        | Run API requests that comes from hash ( eg #e:run ).
    | `$e.commands.runOnce()`              |                                                                                       |                                                        | Do same as `run` but clear `this.commands` before leaving.
    
 
* ##### How to use?
    Currently, the `dispatchersList` supports only two dispatchers:
    * `e:run` - To run a command.
    * `e:route`- To run a route.
    * Example:
        * Assuming you want open _Site Settings_ you can add the hash below to do it:
            * `http://localhost/wp-admin/post.php?post=1&action=elementor#e:run:panel/global/open`
                * Will run command `panel/global/open` which will open __Site Settings__.

* ##### What is `dispatchersList`?
    Currently, this is how it looks:
    ```javascript
    dispatchersList = {
        'e:run': {
            runner: $e.run,
            isSafe: ( command ) => $e.commands.getCommandClass( command )?.getInfo().isSafe,
        },

        'e:route': {
            runner: $e.route,
            isSafe: () => true,
        },
    };
    ```
  * What does its means? let part it:
    * `e:run`: dispatcher name, and format used in hash to activate it.
    * `runner`: the actual function that will be used to run the command.
    * `isSafe`: Is that command safe to run?

* ##### Why `isSafe` exists?
    * There are commands that can trigger or inject 3rd logic and can be vulnerable.

* ##### Which commands are runnable via hash-commands mechanism?
    * Currently, all routes are safe, and commands which include `isSafe() => true`  at their `getInfo` function.
* ##### How to mark a command to be safe?
    ```javascript
    import CommandBase from 'elementor-api/modules/command-base';
    
    export class SafeCommand extends CommandBase {
        static getInfo() {
            return {
                isSafe: true,
            };
        }
    }
    ```
* ##### How to chain hash commands (run multiple commands)?  
    * Simple using and (&) character, example:
    `http://localhost/wp-admin/post.php?post=1&action=elementor#e:run:panel/global/open&e:route:panel/history/revisions`
