=== Plugin Name ===
Contributors: johnny5, FolioVision
Donate link: http://foliovision.com/donate/
Tags: form, contact, validate
Requires at least: 2.7
Tested up to: 3.4.1
Stable tag: trunk 

Generic form processor allowing forms to be painlessly processed and aggregated, with numerous options to validate data and perform custom commands

== Description ==

Filled In is  a generic form processing plugin that will validate and store data submitted through forms. You can use it for any kind of data input, from simple contact forms on a blog to full-blown questionnaires on a business site.

Features include:

* Customizable data filters and data processors
* Central data storage, with exports to CSV and XML
* Email reporting, with attachments and inline images
* AJAX support (forms always work in browsers without JavaScript)
* Built-in CAPTCHA support
* Built-in poMMo mailing list support
* Built-in file upload support
* Easy to build custom extensions

Filled In is available in:

* English
* Danish, thanks to Georg S. Adamsen
* Polish, thanks to Kasia
* Italian, thanks to Simone Righini
* French, thanks to Zesty

== Installation ==

The plugin is simple to install:

1. Download `filled-in.zip`
1. Unzip
1. Upload `redirection` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Configure the options from the `Manage/Filled In` page
1. Put your custom made extensions in to wp-content/plugins/filled-in-extensions/pre (and also post, result, filters)

You can find full details of installing a plugin on the [plugin installation page](http://urbangiraffe.com/articles/how-to-install-a-wordpress-plugin/).

== Documentation ==

Full documentation can be found on the [Filled In](http://urbangiraffe.com/plugins/filled-in/) page.

== Changelog ==

= 1.0 =
* Initial release

= 1.1 =
* AJAX re-dux

= 1.2 =
* Use Element.scrollTo instead of anchors, fix escaped quotes in display

= 1.3 =
* Reorganize admin menu, add email templates, fix bugs

= 1.4 =
* Bug fixes, improved admin interface

= 1.5 =
* Use correct table prefix and wpurl

= 1.6 =
* Reorganize files, seperating HTML from PHP.  Use swiftmailer, add email attachments
* Use new WP table names, remove field name collisions.
* New filters & processors

= 1.6.1 =
* Form list pagination, better reports

= 1.6.2 =
* Fixed a few bugs on report screen

= 1.6.3 =
* Fix CAPTCHA

= 1.6.4 =
* Add WP_Profile extension, allow to work with sniplets and other filters

= 1.6.5 =
* Fix pager bug, add 'top of page' option

= 1.6.6 =
* Fix minor bugs, add 'Is Password' filter to stop passwords being stored in the database

= 1.6.7 =
* Fix for WordPress 2.2

= 1.6.8 =
* Fix HTML email formatting, improve form replacement, stop duplicate filters, Italian translation

= 1.6.9 =
* Fix wpautop problem

= 1.6.10 =
* Added several new modules, added French & Italian translations

= 1.6.11 =
* Fix loop error

= 1.6.12 =
* WP 2.5 cleanup

= 1.6.13 =
* WP 2.7 fixes

= 1.7 =
* jQuery, nonces, WP 2.7 styling

= 1.7.1 =
* Fix #386

= 1.7.2 =
* WP 2.8 compatibility

= 1.7.3 =
* More 2.8 fixes

= 1.7.4 =
* Move into WP extend

= 1.7.5 =
* Add Polish translation

= 1.7.6 =
* Form submission log - delete function bugfix
* Form submission log - improved SQL (much faster with large number of submissions)
* Installation bugfix - tables are now created with the same charset as other present Wordpress tables

= 1.7.7 =
* Added indexing of filled_in_errors (`data_id`) which improves speed of the plugin when the database tables are big

= 1.8 =
* Any custom extensions now can be put into wp-content/plugins/filled-in-extensions to survive plugin upgrades
* New e-mail extension is using wp_mail

== Upgrade Notice ==

= 1.9 =

Move your custom extensions to wp-content/plugins/filled-in-extensions before upgrading!

The old e-mail extension is now deprecated. The new extension is available and it uses wp_mail. Use plugin like 'WP Mail SMTP' to configure SMTP for your wp_mail.