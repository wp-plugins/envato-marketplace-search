<?php
/**
 * Plugin Name: Envato Marketplace Search
 * Plugin URI: http://wordpress.org/extend/plugins/envato-marketplace-search/
 * Description: Retrieves items from Envato Marketplace's using the search API and displays the results as an unordered lists of linked 80px thumbnails.
 * Version: 1.0.0
 * Author: Derek Herman
 * Author URI: http://valendesigns.com
 * License: GPLv2
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Get the marketplace search results
 *
 * @uses wp_parse_args()
 * @uses extract()
 * @uses trim()
 * @uses get_search_query()
 * @uses is_search()
 * @uses str_replace()
 * @uses get_json_data()
 * @uses json_decode()
 * @uses get_option()
 *
 * @since 1.0.0
 * @access public
 *
 * @param array $args The argument array.
 *    - @param integer $limit The returned results limit (max is 50): default 10
 *    - @param string $site The marketplace site (e.g. activeden, audiojungle)
 *    - @param string $type The item type (e.g. site-templates, music, graphics) 
 *        - For a full list of types, look at the search select box values on the particular marketplace
 *    - @param string $query The search query: default is get_search_query()
 *    - @param string $referral Your marketplace referral ID (e.g. valendesigns)
 *    - @param bool $search For use on pages other than search results: default false
 *    - @param bool $cache Cache results in the database (recommended when $query is set manually): default false
 *    - @param bool $echo Echo or return output: default true
 * @return null|string The output, if echo is set to false.
 */
function envato_marketplace_search( $args = '' )
{
  // default arguments
  $defaults = array(
		'limit'     => 10, 
		'site'      => '', 
		'type'      => '',
		'query'     => trim( get_search_query() ),
		'referral'  => '',
		'search'    => true,
		'cache'     => false,
		'echo'      => true
	);
  
  // parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );
	
	// declare each item in $args as its own variable
	extract( $args, EXTR_SKIP );
	
  // missing query OR search == true and not search page, return false
  if ( !$query || ( $search && !is_search() ) )
    return;
  
  // cache is true
  if ( $cache ) 
  {
    // key separator
    $sep = '_';
    
    // build cache key from variables
    $key = ( $site ) ? $site.$sep : '';
    $key = ( $type ) ? $key.$type.$sep : $key;
    $key = str_replace( ' ', $sep, $key.$query );

    // just to keep the code below cleaner
    $cache_key = "ems_{$key}";
    $transient = "_transient_{$cache_key}";
    $transient_timeout = "_transient_timeout_{$cache_key}";
    
    // set original return before we destroy it.
    if ( get_option( $transient_timeout ) < time() )
      $old_return = get_option( $transient );
      
    // cached result
    if ( false !== $return = get_transient( $cache_key ) )
    {
      // return
      if ( !$echo )
        return $return;
      
      // echo
      echo $return;
      
      // exit, cached returned to screen
      return;
    }
	}
	
	// either cache is false 
	// or there are no cached results
	
	// set empty return variable
  $return = '';
  
  // build search expresssion
  $query = str_replace( ' ', '|', $query );
  
  // build API url
  $json_url = "http://marketplace.envato.com/api/edge/search:{$site},{$type},{$query}.json";
  
  // get API JSON results
  $json_contents = get_json_contents( $json_url ); 
  
  // if get_json_data() returns data
  if ( $json_contents ) 
  {
    // decode json data
    $json_data = json_decode( $json_contents, true );
    
    // set count to zero
    $count = 0;
    
    // loop through results
    foreach( $json_data['search'] as $item ) 
    {
      // file type not item, continue and preserve loop count
      if ( $item['type'] != 'item' )
        continue;
      
      // stop adding results to the content if count is >= limit
      if ( $count >= $limit )
        continue;

      // set variables
      $url    = $item['url'];
      $image  = $item['item_info']['thumbnail'];
      $title  = $item['item_info']['item'];
      $ref    = ( $referral ) ? '?ref='.$referral : '';
      
      // set return data 
      $return .= "<li><a href='{$url}{$ref}' rel='nofollow external'><img src='{$image}' alt='{$title}' height='80' width='80' /></a></li>";
      
      // increment count total
      $count++;
    }
    
    // wrap results in a UL
    if ( $return )
      $return = '<ul id="envato-marketplace-search">'.$return.'</ul>';
    
    // set cache if required
    if ( $cache )
      set_transient( $cache_key , $return, 3600 );
  }
  // cache the old result for 5 minutes if old_return data exist
  else if ( $cache && $old_return )
  {
    $return = $old_return;
    set_transient( $cache_key , $return, 300 );
  }
  
  if ( !$echo )
		return $return;
  
  echo $return;
}

/**
 * Get the contents of a remote url with a curl fallback
 *
 * @uses file_get_contents()
 * @uses get_json_data_via_curl()
 *
 * @since 1.0.0
 * @access public
 *
 * @param string $address The remote address of the JSON file
 * @return null|string Returns the contents of the file 
 */
function get_json_contents( $address )
{
  // no addredd return false
  if ( !$address )
    return;
  
  // grab the file contents 
  $data = @file_get_contents( $address );
  
  // no data use curl
  if ( $data === false )
    $data = get_json_contents_via_curl( $address );
  
  // return data
  if ( $data )
    return $data;
  
  return false;
}

/**
 * Get the contents of a remote usinf curl
 *
 * @uses curl_init()
 * @uses curl_setopt()
 * @uses curl_exec()
 * @uses curl_getinfo()
 * @uses curl_close()
 *
 * @since 1.0.0
 * @access public
 *
 * @param string $address The remote address of the JSON file
 * @return null|string Returns the contents of the file 
 */
function get_json_contents_via_curl( $address )
{
  // no addredd return false
  if ( !$address )
    return;
  
  // grab the file contents
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $address);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  $data = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  
  // HTTP is 200 (success) return data
  if ( $info['http_code'] == 200 ) 
    return $data;
  
  return false;
}