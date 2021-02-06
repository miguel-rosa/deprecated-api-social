<?php 


function comment_data($comment){
    return [
        'username' => $comment->comment_author,
        'comment' => $comment->comment_content
    ];
}

function photo_data($post){
    $post_meta = get_post_meta($post->ID);
    $src = wp_get_attachment_image_src($post_meta['img'][0], 'large')[0];
    $user = get_userdata($post->post_author);
    $user_id = $user->ID;
    $user_image = get_avatar_data($user_id);
    $total_comments = get_comments_number($post->ID);
    

    $comments = [];

    $get_comments = get_comments([
        'post_id' => $post->ID
     ]);

    foreach($get_comments as $comment){
        $comments[] = comment_data($comment);
    }
    
    
    return [
        'id' => $post->ID,
        'author' => array(
            'id' => $user_id,
            'username' => $user->user_login,
            'photo' => $user_image['url'],
        ),
        'image' => $src,
        'title' => $post->post_title,
        'date' => $post->post_date,
        'src' => $src,
        'liked' => array(12,1,285,54,678,11,7,48946,64),
        'comments' => $comments,
        'total_comments' => $total_comments
    ];
}


function api_photo_get($request){
    $post_id = $request['id'];
    $post = get_post($post_id);

    if(!isset($post) || empty($post_id)){
        $response = new WP_Error('error', 'Post não encontrado', ['status' => 404]);
        return rest_ensure_response($response);
    }

    $photo = photo_data($post);
    $photo['acessos'] = (int) $photo['acessos']+1;
    update_post_meta($post_id, 'acessos', $photo['acessos']);

    $comments = get_comments([
        'post_id' => $post_id,
        'order' => 'ASC'
    ]);

    $response = [
        'photo' => $photo,
        'comments' => $comments
    ];

    return rest_ensure_response($response);
}

function register_api_photo_get(){
    register_rest_route('v1', '/photo/(?P<id>[0-9]+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_photo_get',
    ]);
   
}

add_action('rest_api_init', 'register_api_photo_get');

function api_photos_get($request){
    
    $total = sanitize_text_field($request['total']) ?: 6;
    $page = sanitize_text_field($request['page']) ?: 1;
    $user= sanitize_text_field($request['user']) ?: 0;

    if(!is_numeric($user)){
        
        $user_object = get_user_by('login', $user);
        if(!$user_object){
            $response = new WP_Error('error', 'Usuário não encontrado', ['status' => 404]);
            return rest_ensure_response($response);
        }
        $user = $user_object->ID;
    }


    $args = [
        'post_type' => 'post',
        'author' => $user,
        'post_per_page' => $total,
        'paged' => $page
    ];

    $query = new WP_Query($args);
    $posts = $query->posts;

    $photos = [];
    
    if($posts){
        foreach($posts as $post){
            $photos[] =  photo_data($post);
        }
    }

    return rest_ensure_response($photos);
}

function register_api_photos_get(){

    register_rest_route('v1', '/photo', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_photos_get',
    ]);

}

add_action('rest_api_init', 'register_api_photos_get');

?>