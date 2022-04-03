<?php 
/**
 * Plugin Name: WPGQL Allow Subscribers Access To Private Posts
 * Plugin URI: https://www.todo...
 * Version: 0.0.1-alpha
 * Author:
 * Author URI:
 * Description: This is an example plugin for extending WP-GraphQL wpgql-allow-subscribers-private-posts -remember! graphql_debug() and wp_send_json()
 */
/** 
 * WP_QUERY sets an query argument of 'post_status' => 'public' for the underlying wpgraphql WP_User_Query, To adjust this, we can customise the `graphql_connection_query_args` WP_Query
 * eg: broadcast 'post_status'='private to non-authenticated requests.
 */
add_filter( 'graphql_connection_query_args', function( $query_args, $connection_resolver ) {
  if ( // queries to target
    $connection_resolver instanceof \WPGraphQL\Data\Connection\PostObjectConnectionResolver ) {
      if(//ensure the wp query is looking 4 private posts (the point of this hook) 
        $query_args["graphql_args"]["where"]["status"]==="private" 
        // only logged in users
        && is_user_logged_in()
        // update the WP_Query
        ){
        $query_args['post_status'] = ["private"];
    };
  }
  return $query_args;
}, 10, 2 );

/** Filter the Post Model to make all private posts accessible to subscribers (and other logged in users.)
 * WPGraphQL has a Model Layer that centralizes the logic to determine if any given object, or fields of the object, should be allowed to be seen by the user requesting data
 */
add_filter( 'graphql_object_visibility', function( $visibility, $model_name, $data, $owner, $current_user ) {
  // only apply our adjustments to the PostObject Model  
 if ( 'PostObject' === $model_name ) {
    // $visibility = is_user_logged_in() ? 'public':$visibility;
    if(is_user_logged_in() && $data->post_status === "private" && $data->post_type==="post"){
      $visibility = 'public';
    }
  }
  return $visibility;
}, 10, 5 );

//double check what is happening here.
// error caused by adding argument to filter
// , $resolver
add_filter( 'graphql_connection_should_execute', function(
  $should_execute
){
  // $current_user = wp_get_current_user();
  $userLoggedIn = is_user_logged_in();
  //  does it mean all logged in users will end calling this function on every bit of authenticated data (inc mutations) 
  if($userLoggedIn){
    return true; //$userLoggedIn || $should_execute;
  } else {
    return $should_execute;
  }
} );
/* $resolver error == {
{
  "debugMessage": "Too few arguments to function {closure}(), 1 passed in /Users/colinrowntree/Local Sites/colinrtech20210705/app/public/wp-includes/class-wp-hook.php on line 309 and exactly 2 expected",
  "message": "Internal server error",
  "extensions": {
      "category": "internal"
  },
  "locations": [
      {
          "line": 3,
          "column": 10
      }
  ],
  "path": [
      "posts"
  ],
  "trace": [...]
}
}*/