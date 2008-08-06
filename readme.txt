=== Sidebars ===
Contributors: DanielNexterous
Tags: sidebars, control, widgets
Requires at least: 2.5
Tested up to: 2.5.1
Stable tag: 1.1

Create and apply sidebars to certain pages / posts.

== Description ==

Create sidebars through an admin interface and apply widgets. Using conditions, apply the sidebars to certain pages / posts depending on what you select.

== Installation ==

1. Upload the entire folder of the plugin ('sidebars' folder) to your plugins directory ('/wp-content/plugins/' folder). 
1. Activate the plugin through the 'Plugins' menu in Wordpress.
1. A new item on the menu will appeared that says 'Sidebars'. 
1. Also, in your template files, location the function `dynamic_sidebar` and replace it with `go_sidebar`. The dynamic_sidebar function could be anywhere but it will mostly likely be in the sidebar.php file.

== Frequently Asked Questions ==

= In what order do the conditions apply? =

1. The ID of the post / page
1. The parent of the post / page
1. The category of the post / page
1. Get the default sidebar

= Do you need any other template tags besides replacing dynamic_sidebar with go_sidebar? =

No. As long as you replace `dynamic_sidebar` with `go_sidebar` it will work fine.

== Screenshots ==

1. The manage sidebars panel where you can delete or edit a sidebar.
2. The add sidebars panel where you can add a new sidebar.

== Versions ==

1. Version 1.0 - Initial Release
1. Version 1.1 - Passed slug to dynamic_sidebar at end of control mechanism (lost in original changes)