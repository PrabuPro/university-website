<?php 

    add_action('rest_api_init', 'universityRegisterSearch');

    /*

        This is the fuction we use for create custom REST API
        Reguler REST API URL is like this 

        http://fictional-university.local/wp-json/wp/v2/posts

        /wp/v2 - namspace and version 2
        /post  - route

        in registere_rest_route() we use 3 parameters
        1st para - namespace
        2nd para - route
        3rd para - array which handles method for customize REST API
            in 3rd para we use 2 'methods' and 'callback'
                'methods' => This tells what we want to do hitting this url which define in namespace and route we givve GET or POST. 
                             Here we use WP_REST_SERVER::READABLE which is constant variable substitute for GET
                'callback' => return json data. Here php will converted to json.


    */
    function universityRegisterSearch() {
        register_rest_route('university/v1', 'search', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'universitySearchResult'  
        )); 
    }

    
    function universitySearchResult($data) {
    
        //create a custom query
        //s is for search. Entered string will be search in here after sanitized
        $mainQuery = new WP_Query(array(
            'post_type' => array('post','page','professor', 'campus', 'event', 'program'),
            's' => sanitize_text_field($data['term']) 
        ));

        //create a array in order to put related data fron professor post type
        $results = array(
            'generalInfo' => array(),
            'professors' => array(),
            'programs' => array(),
            'events' => array(),
            'campuses' => array()
        );

        
        while($mainQuery->have_posts()){
            $mainQuery->the_post();


            //check each post type and put the result into the currenct array
            if(get_post_type() == 'post' OR get_post_type() == 'page'){
                array_push($results['generalInfo'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'postType' => get_post_type(),
                'authorName' => get_the_author()
                ));
            }
            if(get_post_type() == 'professor'){
                array_push($results['professors'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'image' => get_the_post_thumbnail_url(0,'professorLandscape')
                ));
            }

            //Here we are setting ID property into json so we can use it to get the professor related to ID
            if(get_post_type() == 'program'){

                array_push($results['programs'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'id' => get_the_id()
                ));

                //This is for Campuses. Since the relation direction is from program to campuses we need to get campuse details from here.
                $relatedCampuses = get_field('related_campus');

                if($relatedCampuses) {
                    foreach($relatedCampuses as $campus) {
                        array_push($results['campuses'], array(
                            'title' => get_the_title($campus),
                            'permalink' => get_the_permalink($campus)
                        ));
                    }
                }
            }
            if(get_post_type() == 'campus'){
                array_push($results['campuses'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink()
                ));
            }
            if(get_post_type() == 'event'){
                $eventDate = new DateTime(get_field('event_date'));
                $description = null;
                if(has_excerpt()) {
                    $description = get_the_excerpt();
                }else {
                    $description =  wp_trim_words(get_the_content(),18);
                    }

                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $eventDate -> format('M'),
                    'day' => $eventDate -> format('d'),
                    'description' => $description
                ));
            }
            
            
        }

        //Search Logic in related post types......
        //In order to display professors we we searching program type like Math or biology we need to folowing steps
        //First of all we need to notice that there is already a relationship between professors and programs through a custom field.
        //We can use that relationship in order to get professors
        //First we Need to get the program ID in to the result array.
        //Then we can need to do a custom query with filtering id with related professor.

        if($results['programs']) {

            //Relation should be 'OR' since we need to get all results
            $programMetaQuery = array('relation' => 'OR');
        
            
        //This is the filtering part
        foreach($results['programs'] as $item) {
            array_push($programMetaQuery,array(
                'key' => 'related_program' ,
                'compare' => 'LIKE',
                'value' => '"'.$item['id'].'"'
            ));
        }

        //This is a custom query we fiering up, filter part is with meta query and it was define in above
        //Since we may have some other programs alse in futer, we need query through a for each loop to get all of results. it is define in above
        $programRelationshipQuery = new WP_Query(array(
            'post_type' => array('professor','event'),
            'meta_query' => $programMetaQuery
        ));

       

        while($programRelationshipQuery->have_posts()){
            $programRelationshipQuery->the_post();

            if(get_post_type() == 'event'){
                $eventDate = new DateTime(get_field('event_date'));
                $description = null;
                if(has_excerpt()) {
                    $description = get_the_excerpt();
                }else {
                    $description =  wp_trim_words(get_the_content(),18);
                    }

                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $eventDate -> format('M'),
                    'day' => $eventDate -> format('d'),
                    'description' => $description
                ));
            }

            if(get_post_type() == 'professor'){
                array_push($results['professors'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'image' => get_the_post_thumbnail_url(0,'professorLandscape')
                ));
            }
        }

        //remove duplicate if the same string we search also mention in the description
        //duplicate happens since 2 queries excute in the this page.
        $results['professors'] = array_values(array_unique($results['professors'], SORT_REGULAR));
        $results['events'] = array_values(array_unique($results['events'], SORT_REGULAR));
            
        }

        

        return $results;
    }


?>