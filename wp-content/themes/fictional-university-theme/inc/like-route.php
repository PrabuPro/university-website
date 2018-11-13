<?php

 add_action('rest_api_init', 'universityLikeRouts');

 function universityLikeRouts() {
    register_rest_route('university/v1', 'manageLike', array(
        'methods' => 'POST', 
        'callback' => 'createLike' 
    ));

    register_rest_route('university/v1', 'manageLike', array(
        'methods' => 'DELETE',
        'callback' => 'deleteLike'
    )); 
 }

 //The post request sent from like.js hit the url which we created as custome REST API containing professor ID
 //grab $data which contain professro ID
 function createLike($data) {

    if(is_user_logged_in()){
        //sanitize and assign professor id to $professor variable
        $professor = sanitize_text_field($data['professorId']);


         $existQuery = new WP_Query(array(
                            'author' => get_current_user_id(),
                            'post_type' => 'like',
                            'meta_query' => array (
                                array(
                                    'key' => 'liked_professor_id',
                                    'compare' => '=',
                                    'value' => $professor
                                )
                            )
                        ));
        
        
        if($existQuery->found_posts == 0 AND get_post_type($professor) == 'professor' ){
            return wp_insert_post(array(
                'post_type' => 'like',
                'post_status' => 'publish',
                'post_title' => '2nd like post',
                //use to input professor ID in custom field
                'meta_input' => array(
                    'liked_professor_id' => $professor
                )
            ));

        } else {
            die('invalid professor id');
        }                

        //below function will return id of the post which we create

    } else {
        die("only logged in users can create a like!");
    }

}

function deleteLike($data) {
    $likeId = sanitize_text_field($data['like']);
    if(get_current_user_id() == get_post_field('post_author', $likeId) AND get_post_type($likeId) == 'like') {
        wp_delete_post($likeId, true);
        return 'Congrats, like deleted!';
    } else {
        die("you do not have permission to delete that");
    }
    
 }