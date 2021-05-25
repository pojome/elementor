# <p><a href="https://elementor.com/?utm_source=github-repo&utm_medium=link&utm_campaign=readme"><img src="https://i.imgur.com/0Guj2pn.png?1" alt="Elementor Website Builder"></a></p>
### Welcome to the Elementor Beta - Developer Edition
**This plugin gives you first access to Elementor's newest features and improvements. 
Each Developer Edition release, similar to beta version releases, will contain experimental functionalities that developers will be able to use to get familiar with the next releases before they are published. 
We will use this plugin to validate new features and gain feedback before they are released.**
Visit the plugin page to download [Elementor Beta - Developer Edition](https://wordpress.org/plugins/elementor-beta)
## Changelog
**3.2.0-dev4 - 2021-02-22**
* Tweak: Changed devices switching UI to Responsive Top bar to allow future extra breakpoints
* Tweak: Added Gradient Background option to Button widget
* Tweak: Splitted `eicons` library into Editor and Frontend usages in Optimized Asset Load experiment
* Tweak: Made widgets panel resizing more responsive
* Tweak: Added sticky search box in the widgets panel
* Tweak: Removed deprecated `jQuery` functions for WP 5.7 compatibility
* Tweak: Added Dynamic capability to video link in Background video
* Tweak: Added `--force` CLI replace command to always return the number of replaces
* Tweak: Removed `elementor-image` & `elementor-text-editor` wrappers from DOM in Optimized DOM experiment
* Tweak: Added a Privacy Mode control for YouTube source in Video widget
* Tweak: Background Video changed `http` to `https` in the `youtube-nocookie`
* Tweak: Added a mixin for webkit-scrollbar design in Table of Contents widget CSS
* Fix: Landing page created via the Admin bar wasn't created with Canvas layout
* Fix: Youtube videos didn't work properly when Privacy mode is enabled and Optimized Asset Loading experiment is disabled 
* Fix: Column structure glitch when changing the section structure
* Fix: Error event will not always have an originalEvent (props [@enisdenjo](https://github.com/enisdenjo))
* Deprecated: `is_built_with_elementor` DB method

**3.2.0-dev2 - 2021-01-24**
* Tweak: Updated `eicons` library to v5.10.0
* Fix: Can't rearrange items between sections in Navigator since WordPress 5.6 update ([#12256](https://github.com/elementor/elementor/issues/12256))
* Fix: Editor Autoplay is not working consistently in Image Carousel widget
* Fix: IE11 bug in case of `min-height` mixed with `align-items: center`
* Fix: Wrong translation function caused errors in the Revisions panel
* Fix: UI glitch in the Template Library footer banner
* Fix: Widgets empty state is not visible in WordPress 5.6


**3.2.0-dev1 - 2021-01-07**
* Tweak: Add vertical alignment for tabs widget ([#11997](https://github.com/elementor/elementor/issues/11997))
* Tweak: Converted Elementor Landing Pages to Custom Post type
* Tweak: Added an option to choose Text element HTML tag in Divider widget ([#11499](https://github.com/elementor/elementor/issues/11499))
* Tweak: Added more Webpack optimization to improve performance
* Tweak: Improved browser detection mechanism
* Tweak: Updated Experiment status for Landing Pages and Accessibility Improvements
* Tweak: String changes in Delete Site Settings screen
* Fix: Clicking "Recalculate" button in System info throws a PHP error ([#13100](ttps://github.com/elementor/elementor/issues/13100))
* Fix: Duplicate items in responsive view of Tabs widget
* Fix: Unable to open Dynamic tag controls popover
* Fix: Columns Gap control is not working when Optimized DOM output mode is inactive
* Fix: JS source map files caused console warnings
* Fix: Some dynamic controls were not available
* Fix: Console error was thrown when activating Motion Effects with Legacy DOM mode
* Fix: Regenerate CSS is not working properly in large scale servers
* Fix: Template Library title sanitization to avoid security issues
* Fix: `libxml_disable_entity_loader` warning is thrown in PHP 8.0 instances

**3.1.0-dev5 - 2020-12-28**
* Tweak: Added compatibility to support Improved Asset Loading of Carousel and Slides widgets
* Fix: Improved Asset Loading experiment functionality is using an outdated name
* Fix: Landing Pages experiment glitches
* Fix: Wrong phrasing of Import template success message in Theme Builder
* Fix: Border color glitch in Theme Builder

**3.1.0-dev4 - 2020-12-21**
* Tweak: Added "Theme" option to Page Layout options in Page Settings to allow customization of Site Setting value
* Tweak: Added a confirmation message before deleing Default Kit to trash to avoid unintentional Site Settings deletion
* Fix: Named parameters used in Dynamic Tags causes PHP errors in PHP 8.0 ([#13269](https://github.com/elementor/elementor/issues/13269))
* Fix: "Edit with Elementor" menu does not expands in the top admin-bar menu in WordPress 5.6 ([#13256](https://github.com/elementor/elementor/issues/13256))
* Fix: Stretch Section causes horizontal scroll when the vertical scrollbar is visible in WordPress 5.6 ([#13260](https://github.com/elementor/elementor/issues/13260))
* Fix: Unable to save Templates in WordPress 5.6 ([#12273](https://github.com/elementor/elementor/issues/12273), [Topic](https://wordpress.org/support/topic/save-the-template-and-page-builder-loding/))
* Fix: Ninja Forms plugin conflict in WordPress 5.6 ([#13281](https://github.com/elementor/elementor/issues/13281), [Topic](https://wordpress.org/support/topic/elementor-ninja-forms-bug/), [Topic](https://wordpress.org/support/topic/elementor-bug-ninja-forms/))
* Fix: Pinterest social sharing is not working when displaying a large image in Lightbox
* Fix: Manage Global Colors and Fonts buttons is not leading to the correct screen
* Fix: Permission error when trying to update site description with WP-CLI
* Fix: Finder keyboard shortcut is not working
**3.1.0-dev3 - 2020-12-17**
* New: Elementor Experiments - Experience new features before they're officially released ([Developer Documentation](https://developers.elementor.com/elementor-experiments/))
* New: Compatibility Tag - Make sure your website plugins are compatible with Elementor ([Developer Documentation](https://developers.elementor.com/compatibility-tag/))
* Experiment: Landing Pages - Create beautiful landing pages in a streamlined workflow
* Experiment: Accessibility Improvements - Make Elementor widgets more accessible (may include markup changes) ([13191](https://github.com/elementor/elementor/issues/13191))
* Tweak: Improved performance by adding conditional load of JS assets in frontend ([#8572](https://github.com/elementor/elementor/issues/8572))
* Tweak: Improved performance by adding conditional load of Swiper components ([#8572](https://github.com/elementor/elementor/issues/8572))
* Tweak: Improved Tabs widget accessibility ([#11779](https://github.com/elementor/elementor/issues/11779), [#11561](https://github.com/elementor/elementor/issues/11561))
* Tweak: Added alignment options for Tabs widget ([#11997](https://github.com/elementor/elementor/issues/11997))
* Tweak: Updated Font Awesome icons library to v5.15.1 ([#12057](https://github.com/elementor/elementor/issues/12057))
* Tweak: Added "Custom" Columns Gap option in Section element ([#11978](https://github.com/elementor/elementor/issues/11978))
* Tweak: Added Border Radius support in Google Maps widget ([#11359](https://github.com/elementor/elementor/issues/11359))
* Tweak: Added dynamic capabilities to Video Background group control ([#10908](https://github.com/elementor/elementor/issues/10908))
* Tweak: Added dynamic capabilities to Tab Title control in Tabs widget ([#9710](https://github.com/elementor/elementor/issues/9710))
* Tweak: Added dynamic capabilities to Toggle Content control in Toggle widget ([#12405](https://github.com/elementor/elementor/issues/12405))
* Tweak: Added `em` unit to Border Radius control in Button widget ([#11561](https://github.com/elementor/elementor/issues/11561))
* Tweak: Introduced a new method for attaching a JS handler to an element ([Developer Documentation](https://developers.elementor.com/new-method-attaching-a-js-handler))
* Tweak: Refactored YouTube source to use YouTube API in Video widget
* Tweak: Added dynamic capabilities to Poster control in Video widget
* Tweak: Global dropdown controls are now displayed for all user roles
* Tweak: Improved browsers detection utility functionality
* Tweak: Added "Find an Expert" link to the Admin Dashboard Overview widget
* Tweak: Added the new Theme Builder as a Finder item
* Tweak: Added `+` icon in multi select control for better UX
* Tweak: Improved Select2 controls load
* Tweak: Added Elementor Beta (Developer Edition) promotion in WordPress dashboard
* Tweak: Migrated DOM Improvements to Elementor Experiments
* Tweak: Removed redundant extra padding in responsive controls
* Fix: Some keyboards layout cannot open the keyboard shortcuts dialog ([#6145](https://github.com/elementor/elementor/issues/6145))
* Fix: Steps Divider is not vertically aligned in Multi Step Form widget  ([#12569](https://github.com/elementor/elementor/issues/12569))
* Fix: Double click needed to play YouTube video when Poster image exists in Video widget
* Fix: Autoplay option doesn't work in mobile devices when YouTube source is defined in Video widget
* Fix: YouTube End Time doesn't work when Loop option is active in Video widget
* Fix: Incompatible variable names in Base Swiper class and Elementor Pro carousels
* Deprecated: See all deprecations to this version in our [Developers Deprecations Post](https://developers.elementor.com/v3-1-planned-deprecations/)
