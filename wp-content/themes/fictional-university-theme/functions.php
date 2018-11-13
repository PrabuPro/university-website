<?php 

        require get_theme_file_path('/inc/like-route.php');
        require get_theme_file_path('/inc/search-route.php');

        function university_custom_rest() {
            register_rest_field('post', 'authorName', array(
                'get_callback' => function() {return get_the_author();}
            ));

            register_rest_field('note', 'userNoteCount', array(
                'get_callback' => function() {return count_user_posts(get_current_user_id(), 'note');}
            ));
        }

        add_action('rest_api_init', 'university_custom_rest');


        function pageBanner($args = NULL){ 
            
            if(!$args['title']){ 
                $args['title'] = get_the_title();
            }

            if(!$args['subtitle']){ 
                $args['subtitle'] = get_field('page_banner_subtitle');
            }

            if(!$args['photo']){
                if(get_field('page_banner_background_image')) {
                    $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
                } else {
                    $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
                }
            }

            ?>

            <div class="page-banner">
                <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
                <div class="page-banner__content container container--narrow">
                    <h1 class="page-banner__title">
                        <?php echo $args['title']; ?>
                    </h1>
                    <div class="page-banner__intro">
                        <p><?php echo $args['subtitle']; ?></p>
                    </div>
                </div>
            </div>
        <?php }

        function university_files() {
        //1st argument - name of the js file
        //2nd argument - location of the file. We wordpress function to get the location of the function
        //3rd argument - dependancies for this js files
        //4th argument - version numner (any number)
        //5th argument - load the js file right after closing bracket, true or false.
        wp_enqueue_script('googleMap','//maps.googleapis.com/maps/api/js?key=AIzaSyCecGglgjruPO9nCrwoqXtYb_ZK5WYDQII', NULL,'1.0',true);
        wp_enqueue_script('main_university_js',get_theme_file_uri('/js/scripts-bundled.js'), NULL,microtime(),true);


        //wp_enqueue_style() need 2 arguments
        //1st argument - nick name for our style sheet (any name)
        //2nd argument - location points towards file
        //Since we are referring to the default style sheet we don't have to point out exact location.
        //we can call wordpress function to call the style.css which is 'get_stylesheet_uri();'

        //************************************************//
        
        //FWL bug fixing
        //add NULL,microtime() to fix not loadin CSS file issue
        wp_enqueue_style('university_main_style', get_stylesheet_uri(),NULL,microtime());
        wp_enqueue_style('font_awesome','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('costom_googl_fonts','https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
        
        //can pass any value to the javascript file using below function
        //it takes 3 arguments
        //  1-javascript file name. This is the script we have enqueued('script-bundled.js'). in another words Target file. 
        //  2-variable name which contain all the object we passing.
        //  3-data we are passing. we should pass data through array.
        wp_localize_script('main_university_js', 'universityData', array(
            'root_url' => get_site_url(),
            'nonce' => wp_create_nonce('wp_rest')
        ));
        }; 

        //add_action() need 2 arguments
        //1st arguments - load css of js files
        //2nd arguments - name of the funtion we want to run
        //2nd one is a function and we are not going to put '()' after the fuction name. Reason is
        //we are just saying to wordpress to find this function and run it is at required time.
        //we are just giving the name of the function. 
        add_action('wp_enqueue_scripts','university_files');
        
        
        function university_features(){
            add_theme_support('title-tag');
            add_theme_support('post-thumbnails');
            add_image_size('professorLandscape', 400, 260, true);
            add_image_size('professorPotrait', 480, 650, true);
            add_image_size('pageBanner', 1000, 350, true);
            
        }

        add_action('after_setup_theme','university_features');

        function university_adjust_queries ($query) {

            if(!is_admin() AND is_post_type_archive('campus') AND $query->is_main_query()){
                $query->set('posts_per_page', -1);
            }

            if(!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()){
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');
                $query->set('posts_per_page',-1);
            }


            if (!is_admin() AND is_post_type_archive('event') AND $query->is_main_query()){
                $today = date('Ymd');
                $query->set('meta_key', 'event_date');
                $query->set('order_by', 'mata_value_num');
                $query->set('order', 'ASC');
                $query->set('meta_query', array(
                            array(
                            'key'=> 'event_date',
                            'compare' => '>=',
                            'value' => $today,
                            'type' => 'numeric'
                            )
                            ));
            }
        }

        add_action('pre_get_posts', 'university_adjust_queries');



        function universityMapKey($api){
            $api['key'] = 'AIzaSyCecGglgjruPO9nCrwoqXtYb_ZK5WYDQII';
            return $api;
        }

        add_filter('acf/fields/google_map/api', 'universityMapKey'); 


     //Redirect subscriber account to the homepage
     
     add_action('admin_init', 'redirectSubsToFrontEnd');

     function redirectSubsToFrontEnd() {
        $ourCurrentUser = wp_get_current_user();

        if (count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber') {
            wp_redirect(site_url('/'));
            exit;
        } 
     }

     add_action('wp_loaded', 'noSubsAdminBar');

     function noSubsAdminBar() {
        $ourCurrentUser = wp_get_current_user();

        if (count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber') {
            show_admin_bar(false);
            
        } 
     }

     //Customize login screen
     add_filter('login_headerurl', 'ourHeaderUrl' );

     function ourHeaderUrl() {
         return esc_url(site_url('/'));
     }

     add_action('login_enqueue_scripts', 'outLoginCSS');

     function outLoginCSS() {
        wp_enqueue_style('university_main_style', get_stylesheet_uri());
        wp_enqueue_style('costom_googl_fonts','https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
         
     }
    
    add_filter('login_headertitle', 'ourLoginTitle' );

    function ourLoginTitle() {
        return get_bloginfo('name');
    }

    //below filter send a second argument to our function which contain id of the posts
    //in order to access the secound parameter we need to give below parameters in to filter arguments
    //3rd paramiter - priority
    //4th paramiter - number of argument in the function
    add_filter('wp_insert_post_data', 'makeNotePrivate', 10 , 2); 

    function makeNotePrivate($data, $postarr) {
        
        
        //We need to check the post type since filter we are using will check every post 
        //methode we are passing.
        if($data['post_type'] == 'note') {

            //count notes
            //check wheather we are considering a new post. new post have id and update post doesnt have.
            //second parameter have the id of the new post
            if(count_user_posts(get_current_user_id(), 'note') > 4 AND !$postarr['ID']) {
                die("You have reached your note limits");
            }

            //sanitize note posts
            $data['post_content'] = sanitize_textarea_field($data['post_content']); 
            $data['post_title'] = sanitize_text_field($data['post_title']); 
        }

        //make new notes post_status as private
        if($data['post_type'] == 'note' AND $data['post_status'] != 'trash' ) {
            $data['post_status'] = "private";
        }

        return $data;
    }


?>