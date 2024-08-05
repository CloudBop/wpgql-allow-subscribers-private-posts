<?php 
/**
 * Plugin Name: WPGQL Allow Subscribers Access To Private Posts
 * Plugin URI: https://www.todo...
 * Version: 0.0.1-alpha
 * Author:
 * Author URI:
 * Description: This is an example plugin for extending WP-GraphQL wpgql-allow-subscribers-private-posts 
 * - don't forget! graphql_debug(["customhookname"=>"invoked"]); and wp_send_json()
 */
/**
 * WP_QUERY sets an query argument of 'post_status' => 'public' for the underlying wpgraphql WP_User_Query, To adjust this, we can customise the `graphql_connection_query_args
 */
add_filter( 'graphql_connection_query_args', function( $query_args, $connection_resolver ) {
  if ( // queries to target
    $connection_resolver instanceof \WPGraphQL\Data\Connection\PostObjectConnectionResolver ) {
    // conditionals that resolve to null get a deprecation warning
    if(isset($query_args["graphql_args"]) && isset($query_args["graphql_args"]["where"]["status"])){
        if( //ensure the wp query is querying post_status->private
          $query_args["graphql_args"]["where"]["status"]==="private" 
          // only logged in users, 
          && is_user_logged_in()
          // && ccould specify, wp_get_current_user()->caps['subscriber']
          ){
          // update the WP_Query args
          $query_args['post_status'] = ["private"];
      };
    }
  };
  return $query_args;
}, 10, 2 );

/** Filter the Post Model to make all private posts queryable to subscribers (and other logged in users.)
 * "WPGraphQL has a Model Layer that centralizes the logic to determine if any given object, or fields of the object, should be allowed to be seen by the user requesting data"
 * see - https://www.wpgraphql.com/2020/12/11/allowing-wpgraphql-to-show-unpublished-authors-in-user-queries/
 */
add_filter( 'graphql_object_visibility', function( $visibility, $model_name, $data, $owner, $current_user ) {
  // only apply our adjustments to the PostObject Model  beware! potential edgecases for CPT. lost of model_name === PostObject
  if ( 'PostObject' === $model_name ) {
    if( $current_user->caps['subscriber'] && $data->post_status === "private" && $data->post_type==="post") {
      $visibility = 'public';
    }
    //
    if(isset($current_user->caps['read_private_pages']) && $data->post_type==="page"){
      $visibility = 'public';  
    }
    // 
    if(isset($current_user->caps['read_private_portfolio']) && $data->post_type==="portfolio"){
      $visibility = 'public';  
    }
  }
  return $visibility;
}, 10, 5 );

/** Overrule default if user is subscriber **/
add_filter( 'graphql_connection_should_execute', function($should_execute, $resolver){
  //
  if ($resolver instanceof \WPGraphQL\Data\Connection\PostObjectConnectionResolver && is_user_logged_in()){
    $current_user = wp_get_current_user();
    // allow subscribers to execute reading 'private' posts
    if($current_user->caps['subscriber']){
      return true;
      // (beware! this could/would overule other post_type resolver behaviour where logged_in && 'subscriber' conditionals are met)
    }
  } 
  return $should_execute;
  // bugfix - defaults ($priroity number, $args 1)  
}, 10, 2);
