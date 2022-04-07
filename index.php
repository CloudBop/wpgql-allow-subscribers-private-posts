<?php 

  // $userLoggedIn = is_user_logged_in();
  
  // graphql_debug([
  //     "graphql_connection_should_execute"=>"invoved", 
  //     "should_execute"=> $should_execute,
  //     "resolver"=> $resolver,
  //     "resolverClass"=> get_class($resolver),
  //     "userLoggedIn"=> $userLoggedIn
  //     "current_user"=> $current_user,
  //     // "current_user"=> $current_user->caps['custom-role']
  //   ]);
  // return $should_execute;
  // if(!$userLoggedIn){
  // } else {

  //   if($connection_resolver instanceof \WPGraphQL\Data\Connection\PostObjectConnectionResolver
  //   // && $current_user
  //   ) {
  //     //
  //     graphql_debug([
  //       "graphql_connection_should_execute"=>"invoved", 
  //       "should_execute"=> $should_execute,
  //       "resolver"=> $resolver,
  //       "resolverClass"=> get_class($resolver),
  //       "current_user"=> $current_user,
  //       // "current_user"=> $current_user->caps['custom-role']
  //     ]);

  //     return true;
  //   } else {
  //     return $should_execute;
  //   }
  // }


/** 
 * Iintriguing 
 * wpgql 
 * hooks  */

// - be wary of this
// add_filter('graphql_data_is_private', function($is_private, $modelName, $data){
//   graphql_debug( [ 
//     'filter' => "graphql_data_is_private",
//     'is_private' => $is_private,
//     'modelName' => $modelName,
//     'data' => $data
//   ]);
//   return false;
// }, 10, 5);


// //
// add_filter('graphql_restricted_data_cap', function( $restricted_cap
// // , $model_name, $data, $visibility, $owner, $current_user 
// ){
//   graphql_debug( [ 
//     'filter' => "graphql_restricted_data_cap",
//     'restriction' => $restricted_cap
//   ]);
//   return $restricted_cap;
// });

//
// add_filter( 'graphql_connection_should_execute', function ($should_execute, $resolver){
//   $shud2 = true;
//   graphql_debug( [
//     'should execute'=>$shud2,
//     'resolver'=>$resolver
//     ] );
//   return $shud2;
// } );

//
// add_filter( 'graphql_data_is_private', function (
//   $is_private // , $data,  $visibility, $owner, $current_user 
//   ){

//   $isUser = is_user_logged_in();
//   graphql_debug( [ 'is_private' => $is_private ]);

//   return $is_private;
// });

// add_filter( 'graphql_post_object_connection_query_args',function(  $query_args
// , $source
// , $args
// , $context
// , $info
// ) {
//   wp_send_json([$query_args, $source
// , $args
// , $context
// , $info]);
//   return $query_args;
// });


// add_filter( 'graphql_post_object_connection_query_args',  function($query_args
// // $source, $args, $context, $info
// ){
//   graphql_debug( [ 
//     'filter' => "graphql_post_object_connection_query_args",
//     'query_args' => $query_args,
//     // 'source' => $source,
//     // 'args' => $args,
//     // 'context' => $context,
//     // 'info' => $info
//   ]);
//   return $query_args;
// });
// add_filter( 'graphql_connection_query_args', function($stuff// , AbstractConnectionResolver $resolver
// ){
//   $userLoggedIn = is_user_logged_in();
//   graphql_debug( [ 
//     'filter' => "graphql_connection_query_args",
//     'stuff' => $stuff,
//     'userLoggedIn'=>$userLoggedIn
//     // 'source' => $source,
//     // 'args' => $args,
//     // 'context' => $context,
//     // 'info' => $info
//   ]);
//   return $userLoggedIn || $should_execute;
// });