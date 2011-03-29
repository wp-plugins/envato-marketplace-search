=== Envato Marketplace Search ===
Contributors: valendesigns
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=accounts@valendesigns.com&item_name=Envato+Marketplace+Search
Tags: envato, api, marketplace, search, themeforest, codecanyon
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.0.0

Retrieves items from Envato Marketplace's using the search API and displays the results as an unordered lists of linked 80px thumbnails.

== Description ==

The **Envato Marketplace Search** plugin retrieves items from one or all Envato Marketplace's using the search API and displays the results as an unordered lists of linked 80px thumbnails. On your blogs search page add `<?php if ( function_exists( 'envato_marketplace_search' ) ) { envato_marketplace_search(); } ?>`. The function takes in 8 optional arguments ( limit, site, type, query, referral, search, cache, & echo ). Query is automatically built using the `get_search_query()` function but you could pass in your own query if you really felt the need. Search defaults to true and means that you want results only on search pages (advanced use for those who understand the application). Cache is for situations where you plan on setting the query manually and want to limit the API calls. Use cache with CAUTION, each query will be cached in your database if set to true (default is false). Below are examples of how you would use the plugin to return search results.

These two are equivalent to each other:
`envato_marketplace_search( 'limit=2&site=themeforest&type=site-template&referral=valendesigns' );`
`envato_marketplace_search( array( 'limit' => 2, 'site' => 'themeforest', 'type' => 'site-template', 'referral' => 'valendesigns' ) );`

== Installation ==

1. Upload the `envato-marketplace-search` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php if ( function_exists( 'envato_marketplace_search' ) ) { envato_marketplace_search(); } ?>` in your themes (search.php).

== Changelog ==

= 1.0.0 =
* Added plugin to svn