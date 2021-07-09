## API - `$e.hooks.data`
*  **Description**: `$e.hooks.data` API is a manager of _DATA_ hooks that allows you to create custom **data manipulation** 
of *elementor* data model, and create dependencies. The _hooks_ are attached
to  `$e.commands`, and each **hook** is being fired _before/after/catch_ a command, that's being executed by `$e.run()`
*  **Location**: *core/common/assets/js/api/core/hooks/data.js*
*  **Parent**: [`{HooksBase}`](#HooksBase)
*  **Methods**: Please look at parent: `{HooksBase}` for all the methods.
* ***Important***: All hooks should be created by extending [`{( $e.modules.hookData )}`](#e-modules-hooks-data) located at: `core/common/assets/js/api/modules/hooks/data/`.
	
	| Class                             | Description                                                                                            
	|-----------------------------------|--------------------------------------------------------------------------------
	| `$e.modules.hookData.Base`        | Naked base for creating custom data hooks.                   
	| `$e.modules.hookData.After`       | Used to create a hook, runs after command being executed.
	| `$e.modules.hookData.Dependency`  | Used to create a hook, runs before command being executed and used as dependency.
	| `$e.modules.hookData.Catch`       | Used to create a hook, runs when command failed.

*  **Examples**:
   * Built in hooks:  *`assets/dev/js/editor/document/hooks/data`*
   * Register data hook that runs **_after_** command runs:
        ```javascript
        // Example of data hook, fired after the command runs.
        // Important: Available to run in the console but depends on $e.components example#1.
        class CustomDataHook extends $e.modules.hookData.After {
            getCommand() {
                // Command to hook.
                return 'custom-component/example';
            }
        
            getId() {
                // Unique id for the hook.
                return 'custom-component-example-data-hook';
            }

            // An optional yet recommended function, used for optimization. If the container type is known in advance -
            // you can pass it here.
            //
            //getContainerType() {
            //    return 'container_type'; // e.g. section
            //}
        
            /* Optional function, the conditions for hook to be run. */
            getConditions( args ) {
                return 'value' === args.property;
            }
        
            /*
             * The actual hook logic.
             */
            apply( args, result ) {
                console.log( 'My hook custom logic', 'args: ', args, 'containers: ', result );
            }
        }
        
        // Add new hook to `$e.hooks.data`;
        const myHook = new CustomDataHook();
        
        // Output new hook.
        console.log( myHook );
        
        // Output all data hooks after.
        console.log( $e.hooks.data.getAll().after );
        
        // Test the hook.
        result = $e.run( 'custom-component/example', {
            property: 'value', // The conditions for the hook to be run.
        } );
        
        // Output command run result.
        console.log( 'e-hooks-data-eg-1-result:', result );
        ```

    * Register hook **dependency** that applies before the command runs
    * **Note**: Dependency is a command-breaking hook - you must return a boolean value. (return `false` to break or `true` to continue the execution).
		```javascript
		// Example of hook that block column creation, if it reach maximum columns count.
		class SectionColumnsLimit extends $e.modules.hookData.Dependency {
		   getCommand() {
		      return 'document/elements/create';
		   }

		   getId() {
		      return 'section-columns-limit';
		   }

		   getContainerType() {
		      return 'section';
		   }

		   /* NOTE: This is a Dependency hook and its breakable, when apply returns false */
		   apply( args ) {
		      const { containers = [ args.container ] } = args;
  
		      // If one of the targets have maximum columns reached break the command.
		      return ! containers.some( ( /**Container*/ container ) => {
		         return container.view.isCollectionFilled();
		      } );
		   }
		}
		```
> **Note:** further information about [`{HookBase}`](#)**class**.
  
### [Back](../hooks.md) 
