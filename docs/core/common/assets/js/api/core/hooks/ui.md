## API - `$e.hooks.ui`
*  **Description**: `$e.hooks.ui` API is a manager of _UI_ hooks that allows you to create custom **logic** 
that runs *before/after/catch* the command without affecting the *elementor* data model and history.
The __hooks__ are attached to `$e.commands`, and each _event_ is being fired when a command is running.
Mainly used for UI/View manipulation.
*  **Location**: *core/common/assets/js/api/core/hooks/ui.js*
*  **Parent**: [`{HooksBase}`](#HooksBase)
*  **Methods**: Please look at parent: `{HooksBase}` for all the methods.
* ***Important***: All hooks should be created by extending [`{( $e.modules.hookUI )}`](#e-modules-hooks-ui) located at: `core/common/assets/js/api/modules/hooks/ui/`.
	
	| Class                           | Description                                                                                            
	|---------------------------------|--------------------------------------------------------------------------------
	| `$e.modules.hookUI.Base`        | Naked base for creating custom UI hooks.                   
	| `$e.modules.hookUI.After`       | Used to create a hook, runs after command being executed.
	| `$e.modules.hookUI.Before`      | Used to create a hook, runs before command being executed.
	| `$e.modules.hookUI.Catch`       | Used to create a hook, runs when command failed.
 
 * **Examples**:
   * Built in hooks:  *`assets/dev/js/editor/document/hooks/ui`*
   * Register UI hook that runs **_after_** command runs:
   * Important: Available to run in the console but depends on [$e.components example#1](../components.md#examples).
        ```javascript
        // Example of UI hook, fired after the command runs and change ( CSS Class ) of all div elements.
        
        class CustomUIHook extends $e.modules.hookUI.After {
           getCommand() {
               // Command to listen.
               return 'custom-component/example';
           }
        
           getId() {
               // Unique id for the hook.
               return 'custom-component-example-ui-hook';
           }
        
           getConditions( args ) {
               // Conditions for the hook to be applied.
               if ( args.toggleClass ) {
                   return true;
               }
        
               return false;
           }
        
           /*
            * The actual hook logic.
            */
           apply( args, result ) {
               console.log( 'My hook custom logic', 'args: ', args, 'result: ', result );
        
               // Add 'custom-component' class for all div elements.
               document.querySelectorAll( 'div' ).forEach( 
                 ( element ) => element.classList.add( 'custom-component' ) 
               );
           }
        }
        
        // Add new hook to `$e.hooks.ui`;
        const myHook = new CustomUIHook();
        
        // Output new hook.
        console.log( myHook );
        
        // Output all ui hooks after.
        console.log( $e.hooks.ui.getAll().after );
        
        // Test the hook.
        result = $e.run( 'custom-component/example', {
           toggleClass: true,
        } );
        
        // Output command run result.
        console.log( 'e-hooks-ui-eg-1-result:', result );
        ```

   * Register UI hook that runs **_before_** command runs
        ```javascript
        // Example of event that toggle the section HTML class.
        class CreateSectionIsFull extends $e.modules.hookUI.Before {
            getCommand() {
                return 'document/elements/create';
            }
        
            getId() {
                return 'create-section-is-full';
            }
        
            getConditions( args ) {
                const { containers = [ args.container ] } = args;
        
                return containers.some( ( /* Container */ container ) =>
                    'section' === container.model.get( 'elType' )
                );
            }
        
            apply( args ) {
                const { containers = [ args.container ] } = args;
        
                containers.forEach( ( /* Container */ container ) => {
                    if ( 'section' === container.model.get( 'elType' ) ) {
                        container.view.toggleSectionIsFull();
                    }
                } );
            }
        }
        ```
> **Note:** further information about [`{HookBase}`](#)**class**.
>  
### [Back](../hooks.md) 
