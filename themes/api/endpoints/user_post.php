<?php 

function api_user_post($request){
    
    $username = sanitize_text_field($request['username']);
    $user_name = sanitize_text_field($request['user_name']);
    $email = sanitize_email($request['email']);
    $password = $request['password'];

    if(empty($username) || empty($email) || empty($password)){
        $response = new WP_Error('error', 'Dados Incompletos kkk', ['status' => 406]);
        return rest_ensure_response($response . $emai . $username . $password);
    };

    if(username_exists($username) || email_exists($email)){
        $response = new WP_Error('error', 'Usuário já existente', ['status' => 403]);
        return rest_ensure_response($response);
    };

    $response = wp_insert_user([
        'user_login' => $username,
        'user_email' => $email,
        'display_name' =>$user_name,
        'user_pass' => $password,
        'role' => $subscriber
    ]);

    return rest_ensure_response($response);
}

function register_api_user_post(){
    register_rest_route('v1', '/user', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_user_post',
    ]);
}

add_action('rest_api_init', 'register_api_user_post')

?>