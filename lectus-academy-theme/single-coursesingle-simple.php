<?php
/**
 * Simple Template for displaying single course
 *
 * @package LectusAcademy
 */

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        ?>

        <main id="primary" class="site-main single-course-page">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <div class="course-content">
                    <?php the_content(); ?>
                </div>
                
                <div class="course-meta">
                    <p>Author: <?php the_author(); ?></p>
                    <p>Date: <?php the_date(); ?></p>
                </div>
            </div>
        </main>

    <?php
    endwhile;
endif;

get_footer();
?>