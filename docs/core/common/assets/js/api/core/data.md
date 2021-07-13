## API --  `$e.data`
The new data API (since 3.0.0), provides a simple and convenient way to communicate with REST API & cache.

The full list of data commands, ( including custom & 3rd party commands ) are available via: `$e.data.getAll();`

*  **Description**: `$e.data` API is a manager of _data_ _commands_, allow you to create custom _commands_ that runs by `$e.data.get()`, `$e.data.create()`, `$e.data.delete()`, `$e.data.update()` - depends on what you want to do with the data, each component have its own unique data commands, and all the data commands managed by this **API**.
`$e.data` is inherent from `$e.commands` it was created in order to have the same abilities/options as `$e.commands` but unlike it, each command will represent a final endpoint, and by running it will give the option to *access/manipulate* the [Restful backend]() data or local cache.
*  **Location**: */core/common/assets/js/components/data.js*
*  **Parent**: `$e.commands`
* **Available commands:** for further information about **all** data commands, please visit [`{$e.data.getAll()}`](#)**method**.
*  **Methods** : 

    | Method                               | Params                                                                                 | Returns                            | Description                                                                         |
    |--------------------------------------|----------------------------------------------------------------------------------------|------------------------------------|-------------------------------------------------------------------------------------|
    | `$e.data.getHTTPMethod()`            | `{DataTypes}` *type*                                                                   | `{string}`                         | Returns HTTP Method by type.
    | `$e.data.getAllowedMethods()`        | `{DataTypes}` *type*                                                                   | `{([string]⎮boolean)}`             | Returns allowed HTTP methods by type.
    | `$e.data.commandToEndpoint()`        | `{String}` *command*, `{Object}` *args*, `{(String⎮null)}` *format*                    | `{string}` *endpoint*              | Convert command to endpoint.
    | `$e.data.commandExtractArgs()`       | `{String}` *command*, `{Object}` *args*                                                | `{ExtractedCommand}` *command*     | If the command have query convert it to args.
    | `$e.data.validateRequestData()`      | `{RequestData}` *requestData*, `{boolean}` *requireArgsData*                           |                                    | Validate request data requirements.
    | `$e.data.prepareHeaders()`           | `{RequestData}` *requestData*                                                          | `{Object}` *params*                | Prepare the headers for each request.
    | `$e.data.prepareEndpoint()`          | `{String}` *endpoint*                                                                  | `{String}` *endpoint*              | This method response for building a final endpoint.
    | `$e.data.fetch()`                    | `{RequestData}` *requestData* , `{window.fetch}` *fetchAPI*                            | `{Promise<Response>}`              | Each run, the command will `fetch()` which will access the server or cache.
    | `$e.data.getCache()`                 | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *args*                | `{Object}`                         | Get cache.
    | `$e.data.setCache()`                 | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *query*, `{*}` *data* |                                    | Update cache.
    | `$e.data.updateCache()`              | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *query*, `{*}` *data* |                                    | The difference between `setCache` and `updateCache` is update will only modify exist values. and 'setCache' will create or update.
    | `$e.data.deleteCache()`              | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *query*               |                                    | Delete cache.
    | `$e.data.registerFormat()`           | `{String}` *command* , `{String}` *format*                                             |                                    | Register's format for each command.
    | `$e.data.create()`                   | `{String}` *command* , `{*)` *data*, `{Object}` *query*, `{Object}` *options*          | `{*}` Result                       | Run a command, that will be translated as endpoint for creating new data.
    | `$e.data.delete()`                   | `{String}` *command* , `{Object}` *query*, `{Object}` *options*                        | `{*}` Result                       | Run a command, that will be translated as endpoint for deleting data.
    | `$e.data.get()`                      | `{String}` *command* , `{Object}` *query*, `{Object}` *options*                        | `{*}` Result                       | Run a command, that will be translated as endpoint for getting data.
    | `$e.data.update()`                   | `{String}` *command* , `{*)` *data*, `{Object}` *query*, `{Object}` *options*          | `{*}` Result                       | Run a command, that will be translated as endpoint for updating data.
    | `$e.data.options()`                  | `{String}` *command* , `{Object}` *query*, `{Object}` *options*                        | `{*}` Result                       | Run a command, that will be translated as endpoint for requesting options/information about specific endpoint.

> **Note:** for more information please. please visit: [`{$e.commands}`](commands.md)
## Why `$e.data` exist?
* It will be priceless to lose such hooks, which can be easily manged by existing mechanism (`$e.commands`).

## How to use?
* Create a [Component](components.md) And add a endpoint to it:
  
    ```javascript
    class Component extends $e.modules.ComponentBase {
      getNamespace() {
        return 'documents';
      }
    
      defaultData() {
          return {
            'command-name': 'endpoint-format',
            'index': 'documents/{id}',
            'save': 'documents/{id}/save',
          }
      }
    }
    ```
    
    | Calling to | Will reach |
    |------------|------------|
    | `$e.data.get( 'documents/index' )` | `wp-json/elementor/{current_version/documents` | 
    | `$e.data.get( 'documents/index', { id: 1 } )` | `wp-json/elementor/{current_version/documents/1` | 
    | `$e.data.update( 'documents/save', { id: 1 } )` | `wp-json/elementor/{current_version/documents/1/save` | 

* What is refresh options and how to use cache?
  * `$e.data.get( 'documents/index', { id: 1 }, { refresh: true } );`
    - Refresh option will give you fresh data from backend and will update the cache with the new result.
  * How to manipulate the cache?
    - There few methods available.
    
        | Method                               | Params                                                                                 | Returns                            | Description                                                                         |
        |--------------------------------------|----------------------------------------------------------------------------------------|------------------------------------|-------------------------------------------------------------------------------------|
        | `$e.data.setCache()`                 | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *query*, `{*}` *data* |                                    | Update cache.
        | `$e.data.updateCache()`              | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *query*, `{*}` *data* |                                    | The difference between `setCache` and `updateCache` is update will only modify exist values. and 'setCache' will create or update.
        | `$e.data.deleteCache()`              | `{ComponentBase}` *component* , `{String}` *command*, `{Object}` *query*               |                                    | Delete cache.
        | `$e.data.create()`                   | `{String}` *command* , `{*)` *data*, `{Object}` *query*, `{Object}` *options*          | `{*}` Result                       | Run a command, that will be translated as endpoint for creating new data.
        | `$e.data.get()`                      | `{String}` *command* , `{Object}` *query*, `{Object}` *options*                        | `{*}` Result                       | Run a command, that will be translated as endpoint for getting data.
        
        * `$e.data.setCache`
           * Will override the cache in any given command.
        * `$e.data.updateCache()`
            * Will modify exist data in the command cache.
        * `$e.data.deleteCache`
            * Will delete a specific node at any level. 
        * `$e.data.create`
            * Will send a remote POST to the server and save the result in the cache.
        * `$e.data.get()`
            * Will send a remote GET to the server, the result will be set to command cache.

    - Those methods not available for caching at the moment.
    
        | Method                               | Params                                                                                 | Returns                            | Description                                                                         |
        |--------------------------------------|----------------------------------------------------------------------------------------|------------------------------------|-------------------------------------------------------------------------------------|
        | `$e.data.delete()`                   | `{String}` *command* , `{Object}` *query*, `{Object}` *options*                        | `{*}` Result                       | Run a command, that will be translated as endpoint for deleting data.
        | `$e.data.update()`                   | `{String}` *command* , `{*)` *data*, `{Object}` *query*, `{Object}` *options*          | `{*}` Result                       | Run a command, that will be translated as endpoint for updating data.

        
## Guidelines, conventions & file's structure
  * Please visit [`$e.commands`](commands.md#guidelines-conventions--files-structure)    

### **Note:** further information about [`{$e.modules.CommandData}`](../modules/command-data.full.md)**class**.

### [Back](../index.md) 
