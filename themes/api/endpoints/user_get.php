<?php 

function api_user_get($request){
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $user_image = get_avatar_data($user_id);

    if($user_id === null){
        $response = new WP_Error('error', 'Usuário não possui permissão', ['status' => 401]);
        return rest_ensure_response($response);
    }

    $response = [
        'id' => $user_id,
        'username' => $user->user_login,
        'name' => $user->display_name,
        'email' => $user->user_email,
        'image' => $user_image
    ];

    return rest_ensure_response($response);
}

function register_api_user_get(){
    register_rest_route('v1', '/user', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_get',
    ]);
}

add_action('rest_api_init', 'register_api_user_get')

?>