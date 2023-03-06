<?php

$args = [
    'post_type' => 'testimonial',
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'meta_query' => [
        [
            'key' => '_drclubs_testimonial_key',
            'value' => 's:8:"approved";i:1;',
            'compare' => 'LIKE'
        ]
    ]
];

$query = new WP_Query($args);

if ($query->have_posts()):
    $i = 1;
    echo '<div class="dc-slider--wrapper"> 
    <div class="dc-slider--container"> 
    <div class="dc-slider--view"> 
    <ul>';
    while ($query->have_posts()):$query->the_post();
        $data = get_post_meta(get_the_ID(), '_drclubs_testimonial_key', true);
        $name = isset ($data['name']) ? $data['name'] : '';
        echo '<li class="dc-slider--view__slides ' . ($i === 1 ? 'is-active' : '') . '"><p class="testimonial-quote">' . get_the_content() . '</p><p  class="testimonial-author">~ ' . $name . ' ~</p></li>';
        $i++;
    endwhile;
    echo '</ul></div>
    <div class="dc-slider--arrows">
        <span class="arrow dc-slider--arrows__left">&#x3c;</span>
        <span class="arrow dc-slider--arrows__right">&#x3e;</span>
        
        </div></div></div>';

endif;