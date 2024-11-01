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
    $connection_resolver instanceof \WPGraphQL\Data\Connection\PostObjectConnectionResolver 
    //
    && isset($query_args["graphql_args"]["where"])
  ) {
      if( //ensure the wp query is querying post_status->private
        $query_args["graphql_args"]["where"]["status"]==="private" 
        // only logged in users, 
        && is_user_logged_in()
        // && ccould specify, wp_get_current_user()->caps['subscriber']
        ){
        // update the WP_Query args
        $query_args['post_status'] = ["private"];
    };
  };
  return $query_args;
}, 10, 2 );

/** Filter the Post Model to make all private posts queryable to subscribers (and other logged in users.)
 * "WPGraphQL has a Model Layer that centralizes the logic to determine if any given object, or fields of the object, should be allowed to be seen by the user requesting data"
 * see - https://www.wpgraphql.com/2020/12/11/allowing-wpgraphql-to-show-unpublished-authors-in-user-queries/
 */
add_filter( 'graphql_object_visibility', function( $visibility, $model_name, $data, $owner, $current_user ) {
  // only apply our adjustments to the PostObject Model  beware! potential edgecases for CPT.
  if ( 'PostObject' === $model_name ) {
    // 
    if( $current_user->caps['subscriber'] && $data->post_status === "private" && ($data->post_type==="fh_intro3ph" || $data->post_type==="post")) {
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
  if ($resolver instanceof \WPGraphQL\Data\Connection\PostObjectConnectionResolver && is_user_logged_in()){
    $current_user = wp_get_current_user();
    // allow subscribers to execute reading 'private' posts
    if($current_user->caps['subscriber'] 
    // null coalescing operator to stop PHP Warning:  Undefined array key "subscriber"
    ?? null){
      return true;
      // (beware! this could/would overule other post_type resolver behaviour where logged_in && 'subscriber' conditionals are met)
    }
  } 
  return $should_execute;
  // bugfix - defaults ($priroity number, $args 1)  
}, 10, 2);

/** NEW FEATURE, ADD CAPABILITY TO READ PRIVATE POST, PAGE, CPT */
/**
 * Run an action after the additional data has been updated. This is a great spot to hook into to
 * update additional data related to users, such as setting relationships, updating additional usermeta,
 * or sending emails to Kevin... whatever you need to do with the userObject.
 *
 * @param int $user_id The ID of the user being mutated
 * @param array $input The input for the mutation
 * @param string $mutation_name The name of the mutation (ex: create, update, delete)
 * @param AppContext $context The AppContext passed down the resolve tree
 * @param ResolveInfo $info The ResolveInfo passed down the Resolve Tree
 */

add_action( 
  // wpgaphql hook
  'graphql_user_object_mutation_update_additional_data', 
  //
  'graphql_register_extra_capability_on_user_mutation', 
  10, 
  5 
);

function graphql_register_extra_capability_on_user_mutation( $user_id, $input, $mutation_name, $context, $info ) {

  //
  // use $input['aim'] to set the capability key for the user
  //

  // $arrayData = array(
  //   'gqldebug'=> 'gqldebug-'.__FUNCTION__,
  //   'user_id'=> $user_id,
  //   "input"=>$input,
  //   'mutation_name'=> $mutation_name,
  //   'context'=> $context,
  //   // 'info'=> $info, /
  //   "isset"=> $input['id']
  // );
  // graphql_debug($arrayData);
  // TODO - check current user context is authentication to this function...

  //
  // Consider other input sanitization if necessary and validation such as which
  // user role/capability should be able to insert this value, etc.
  if("graphql_register_extra_capability_on_user_mutation"===__FUNCTION__){

    //
    if ( isset( $input['id']) && isset($input['aim']) ) {
      
      // $authUser =  wp_get_current_user(); --> If using gatsby cloud this could happen via a single 'special' wordpress user account. Another layer of protection.
      // id and username
      // graphql_debug($authUser);
      // graphql_debug("I FIRED, LETS GET THE WP USER");
      //
      $user = new WP_User($user_id);
      //
      //
      switch ($input['aim']) {
          //
          case "ab48dc": // set in gsby-cloud webhook
          // unless this string key is known then it is very difficult to steal/configure the access/auth
            if(!isset($user->caps['read_private_portfolio'])){
              $user->add_cap("read_private_portfolio");
            }
            break;

          // the example below is used from the /profile page, a hacky way to add/remove a capability, for testing
          //
          case "football":
              if(isset($user->caps['read_private_portfolio'])){
                $user->remove_cap("read_private_portfolio");
              } else {
                 $user->add_cap("read_private_portfolio");
              }
            break;
          default:
            break;
      }

      // if(
      //   isset($user->caps['read_private_portfolio'])
      // ) {
      //   $user->remove_cap("read_private_portfolio");
      // } else{        
      // }
      
      // Will return false if the previous value is the same as $new_value.
      // ...careful here, wpUser data can be input
      // $updated = update_user_meta( $user_id, 'hobbies', $input['hobbies'] );
    } else {
      // graphql_debug("I FIRED, VALIDATION FAILED SOMETHING IS WRONG WITH THE INPUT ");
    }
  }
  // graphql_debug(["customhookname"=>$input ]); //and wp_send_json()
  // wp_send_json($arrayData);
}

/***
// $current_user = wp_get_current_user();    
// echo "Username :".$current_user->user_login;
// echo "Username :".$current_user->ID;
// echo "Username :".$current_user->user_pass;
// echo "Username :".$current_user->user_nicename;
// echo "Username :".$current_user->user_email;
// echo "Username :".$current_user->user_url;
// echo "Username :".$current_user->user_registered;
// echo "Username :".$current_user->user_activation_key;
// echo "Username :".$current_user->user_status;
// echo "Username :".$current_user->display_name;
*/



//
// Move this into the plugin that allows subscribers access extra content
// 
function create_custom_portfolio_post_type(){
  register_post_type('portfolio',
    array(
      'labels' => array(
      'name' => __('Portfolio'),
      'singular_name' => __('Portfolio'),
      ),
    'public' => true,
    'show_in_admin_bar' => true,
    'show_in_graphql' => true,
    'graphql_single_name' => 'portfolio',
    'graphql_plural_name' => 'portfolios',
    'rewrite' => array('slug' => 'featured', 'with_front' => false)
    // 'show_in_rest' => true
    )
  );
  
  // enable thumbnail 
  add_post_type_support('portfolio', array('thumbnail', 'excerpt'));

  register_post_type('fh_intro3ph',
    array(
      'labels' => array(
      'name' => __('Intro_3ph'),
      'singular_name' => __('Intro_3ph'),
      ),
    'public' => true,
    'show_in_admin_bar' => true,
    'show_in_graphql' => true,
    'graphql_single_name' => 'fh_3ph',
    'graphql_plural_name' => 'fh_3phs',
    'rewrite' => array('slug' => 'intro-to-three-part-harmony', 'with_front' => false),
    // 'show_in_rest' => true -< needed for gutenb support
    'show_in_rest' => true
    )
  );
  
  // enable thumbnail 
  add_post_type_support('fh_intro3ph', array('thumbnail', 'excerpt'));
}
// on theme init
add_action('init', 'create_custom_portfolio_post_type');
//
// Move this into the plugin that allows subscribers access extra content
// 
// function create_an_introduction_to_three_part_harmony_post_type(){
//   register_post_type('an_introduction_to_three_part_harmony',
//     array(
//       'labels' => array(
//       'name' => __('An_introduction_to_three_part_harmony'),
//       'singular_name' => __('An_introduction_to_three_part_harmony'),
//       ),
//     'public' => true,
//     'show_in_admin_bar' => true,
//     'show_in_graphql' => true,
//     'graphql_single_name' => 'an_introduction_to_three_part_harmony',
//     'graphql_plural_name' => 'an_introduction_to_three_part_harmonys',
//     'rewrite' => array('slug' => 'introduction_to_three_part_harmony_post_type', 'with_front' => false)
//     // 'show_in_rest' => true
//     )
//   );
  
//   // enable thumbnail 
//   add_post_type_support('an_introduction_to_three_part_harmony_post_type', array('thumbnail', 'excerpt'));
// }
// // on theme init
// add_action('init', 'create_an_introduction_to_three_part_harmony_post_type');