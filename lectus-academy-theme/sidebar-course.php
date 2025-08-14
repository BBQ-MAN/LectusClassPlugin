<?php
/**
 * The sidebar for course pages
 *
 * @package LectusAcademy
 */

if (!is_active_sidebar('course-sidebar')) {
    return;
}
?>

<aside id="secondary" class="widget-area course-sidebar">
    <?php dynamic_sidebar('course-sidebar'); ?>
</aside>