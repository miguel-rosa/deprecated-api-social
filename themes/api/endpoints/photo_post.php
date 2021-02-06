<?php 

function generateRandomString($length = 10) {
   $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $charactersLength = strlen($characters);
   $randomString = '';
   for ($i = 0; $i < $length; $i++) {
       $randomString .= $characters[rand(0, $charactersLength - 1)];
   }
   return $randomString;
}

function api_photo_post($request){
    $user = wp_get_current_user();
    $user_id = $user->ID;
    
    if($user_id === 0){
       $response = new WP_Error('error', 'Usuário não tem permissão', ['status' => 401]);
       return rest_ensure_response($response);
    }

    $legend = sanitize_text_field($request['legend']);

    $files = $request->get_file_params();

    if(empty($files) || empty($legend)){
       $response = new WP_Error('error', 'Dados Incompletos', ['status' => 422]);
       return rest_ensure_response($response);
    }

    require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
    require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
    require_once(ABSPATH . 'wp-admin' . '/includes/media.php');

    $response = [
       'post_author' => $user_id,
       'post_type' => 'post',
       'post_status' => 'publish',
       'post_title' => $legend,
       'post_content' => $legend,
       'files' => $files,
       'meta_input' => [
            'likes' => 0,   
       ]
       ];

    $post_id = wp_insert_post($response);   
    
    $photo_id = media_handle_upload('img', $post_id);
    update_post_meta($post_id, 'img', $photo_id);

    return rest_ensure_response($response);
}

function register_api_photo_post(){
    register_rest_route('v1', '/photo', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_photo_post',
    ]);
}

add_action('rest_api_init', 'register_api_photo_post')

?>