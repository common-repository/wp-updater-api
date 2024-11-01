=== Plugin Name ===
Contributors: sideshowcoder
Tags: updates, xml-rpc
Requires at least: 3.5.0
Tested up to: 3.5.1
Stable tag: 1.1

Provide information about available updates for Wordpress Core as well as
plugins via XML-RPC.

== Description ==

All methods expect an generated API key as parameter, see usage for more information

    method                     result
    getCoreVersion             'version' => $wp_version
    getCoreUpdatesAvailable    'installed': version, 'current': version
    getPluginUpdatesAvailable  [{'plugin': name, 'installed': version, 'current': version}]

== Installation ==

1. Upload the plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.1 =
- Work with API keys instead of username and password

= 1.0 =
- Initial release


== Usage ==
generate an api key via the Wordpress admin interface for the plugin and
call like any other XML-RPC, ie from ruby do

    require 'xmlrpc/client'

    connection = XMLRPC::Client.new('wordpress.dev', '/xmlrpc.php')

    api_key = 'U3sMgZPcClgFkvR486dIQZ6cDynhHYlk'
    p connection.call('getCoreVersion', api_key)
    p connection.call('getCoreUpdatesAvailable', api_key)
    p connection.call('getPluginUpdatesAvailable', api_key)

