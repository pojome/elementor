# Elementor JS `$e`  API
## Introduction

**Elementor JS API** - is set of *API(s)*, *modules*, *components*, and *utils*, used to communicating with *elementor*.
 represented as `$e` - Global Variable.

# Overview
| API Name                                              | Access          | Description                    |
|-------------------------------------------------------|-----------------|--------------------------------|
| [Components](core/components.md)                      | `$e.components` | Components management.
| [Hooks](core/hooks.md)                                | `$e.hooks`      | Hooks   
| [Commands](core/commands.md)                          | `$e.commands`   | Commands management.
| [Data](core/data.md)                                  | `$e.data`       | Data ( Restful API ) Commands management.
| Routes                                                | `$e.routes`     | Routes management.   
| Shortcuts                                             | `$e.shortcuts`  | Shortcuts component.      
| DevTools                                              | `$e.devTools`   | External plugin for developers. 


# Aliases
| Alias           | Original                    | Description			    |
|-----------------|-----------------------------|---------------------------|
| `$e.run()`      | `$e.commands.run()`         | Run command.              |
| `$e.internal()` | `$e.commandsInternal.run()` | Run internal command.     |
| `$e.route()`    | `$e.routes.run()`           | Run route ( open route ). |

