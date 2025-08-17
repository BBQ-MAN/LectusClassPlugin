<?php
/**
 * The sidebar containing the main widget area
 *
 * @package LectusAcademy
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <?php dynamic_sidebar('sidebar-1'); ?>
    </div>
</aside>