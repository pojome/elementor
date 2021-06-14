# PHPUnit

A quote from "phpunit" website that describes the tool ([https://phpunit.de/](https://phpunit.de/)):

"PHPUnit is a programmer-oriented testing framework for PHP. It is an instance of the xUnit architecture for unit testing frameworks."
 
## Installation
 
**Note:** Make sure to install `composer` before jumping to this section (a guide to install `composer` locate next to this guide).
 
To make sure we can run `phpunit` we have to install few things:
1. Fresh WordPress installation just for the test (to make sure tests not affected by your current WordPress installation)
2. Fresh database (to make sure tests not affected by your current database)
3. `wp-testing-tools` - set of utils that helps us to test WordPress plugins and themes.
4. `phpunit` bin file.
 
There is a script that handles all those requirements
  
#### SVN

before running the install script you should check if there is `svn` in your machine.
 
Run: `which svn` if something like `/usr/local/bin/svn` or `/usr/bin/svn` was returned you should skip this step.

Installing `svn` in mac: `brew install svn` (if you don't have "brew" install it: [https://brew.sh/](https://brew.sh/))

Installing `svn` in Linux subsystem: `sudo apt-get install subversion`

#### Installation script

Run `composer run test:install`. it should prompt questions about your database credentials make sure to fill correct credentials.

That's all you can run `composer test` which will run the whole "Elementor" test suite, any argument that allowed to pass to `phpunit` command you can pass to `composer test` command, e.g: `composer test -- --filter Elementor_Test_Bootstrap`. just make sure to pass extra ` -- ` before your argument.
