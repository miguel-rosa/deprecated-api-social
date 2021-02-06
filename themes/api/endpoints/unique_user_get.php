<?php 


function post_data($post){
    $post_meta = get_post_meta($post->ID);
    $src = wp_get_attachment_image_src($post_meta['img'][0], 'large')[0];
    $user = get_userdata($post->post_author);
    $total_comments = get_comments_number($post->ID);

    return [
        'id' => $post->ID,
        'src' => $src,
        'likes' => $post_meta['likes'][0],
        'total_comments' =>$total_comments
    ];
}

function api_unique_user_get($request){
    
    
    $username = $request['username'];
    $user = get_user_by('login', $username);
    
    $user_id = $user->ID;

    if($user_id === null){
        $response = new WP_Error('error', 'Usuário não encontrado', ['status' => 404]);
        return rest_ensure_response($user);
    }

    $user_image = get_avatar_data($user_id);

    $args = [
        'post_type' => 'post',
        'author' => $user_id,
        'posts_per_page' => 10
    ];

    $user_posts  = get_posts($args);
    $photos = [];
    
    if($user_posts){
        foreach($user_posts as $user_post){
            $photos[] = post_data($user_post);
        }
    }


    $response = [
        'id' => $user_id,
        'username' => $user->user_login,
        'name' => $user->display_name,
        'email' => $user->user_email,
        'image' => $user_image['url'],
        'posts' => $photos
    ];

    return rest_ensure_response($response);
}

function register_api_unique_user_get(){
    register_rest_route('v1', '/user/(?P<username>[a-z-Z0-9-]+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_unique_user_get',
    ]);
}

add_action('rest_api_init', 'register_api_unique_user_get')

?>