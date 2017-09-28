<?php

get_header();

$courses = array();
$prefix = "cmb_";
$archive_mode = isset($_GET['archive']) ? true : false;
$non_archive_args = array();

if (!$archive_mode) {
    $non_archive_args = array(
        'meta_query' => array(
            array(
                'key' => $prefix.'course_active',
                'value' => 'on',
                'compare' => '=',
            )
        )
    );
}

$args = array_merge($wp_query->query_vars, $non_archive_args);
query_posts( $args );

if (have_posts()) {
    while (have_posts()) {
        the_post();
        $courses[] = CoursePostType::add_course_from_query(get_the_ID());
    }
} else {
    echo __('No courses found', 'academic');
    get_footer();
    exit();
}

wp_register_script( 'archive-courses', plugin_dir_url( dirname(__FILE__)).'/inc/js/table-courses.js', array('course_vue'), false, true );
wp_localize_script( 'archive-courses', 'Courses', array(
    'courses' => json_encode($courses),
    'courseYears' => json_encode(CoursePostType::get_years_of_courses()),
) );
wp_enqueue_script( 'archive-courses' );

?>

<div class="container">
	<div class="row">
        <?php get_sidebar(); ?>
		<section class="content-area col-sm-9" id="main_content_container">
			<header class="entry-header">
				<h1 class="entry-title"><?php echo post_type_archive_title( '', false ); ?></h1>
			</header><!-- .entry-header -->

			<div id="primary" class="content-area">
				<main id="main" class="site-main" role="main">
                    <div id="courses-list-app">
                        <input id="free-search" type="text" v-model="freeSearch" value="" placeholder="<?php _e('Free search', 'academic'); ?>">
                        <table id="courses-table" role="grid" aria-live="polite" aria-relevant="additions">
                            <tr>
								<th><?php _e('Course number', 'academic'); ?></th>
								<th><?php _e('Course name', 'academic'); ?></th>
								<th><?php _e('Lecturer', 'academic'); ?></th>
								<th>
                                    <?php _e('Degree', 'academic'); ?>
                                    <br>
                                    <select class="degree-filter" v-model="degreeFilter">
                                        <option value=""><?php _e('All','academic'); ?></option>
                                        <option value="BA">BA</option>
                                        <option value="MA">MA</option>
                                        <option value="Ph.D">Ph.D</option>
                                    </select>
                                </th>
								<th>
                                    <?php _e('Semester', 'academic'); ?>                                  
                                </th>
                                <th>
                                    <?php _e('Year', 'academic'); ?>
                                    <select class="year-filter" v-model="yearFilter">
                                        <option value=""><?php _e('All', 'academic'); ?></option>
                                        <option v-for="year in courseYears" v-bind:value="year">{{year}}</option>
                                    </select>
                                </th>
								<th><?php _e('Credits', 'academic'); ?></th>
								<th><?php _e('Type', 'academic'); ?></th>
							</tr>
                            <tr v-for="course in filteredCourses">
                                <td class="course-number"><a v-bind:href="course.url">{{ course.number }}</a></td>
                                <td class="course-name">{{ course.name }}</td>
                                <td class="course-lecturer"><a v-bind:href="course.lecturer_link">{{ course.lecturer_name }}</a></td>
                                <td class="course-degree">{{ course.degree }}</td>
                                <td class="course-semester">
                                    <span v-for="(semester, i) in course.semester">{{ semester }}{{i < course.semester.length - 1 ? "," : ""}} </span>
                                </td>
                                <td class="course-year">{{ course.year }}</td>
                                <td class="course-credit">{{ course.credit }}</td>
                                <td class="course-enrichment">{{ course.type[0] }}</td>
                                <template v-if="!course.active"><span class="course-not-active label label-warning"><?php _e('Not active', 'academic'); ?></span></template>
                            </tr> 
                             
                        </table>
                        <?php if (!isset($_GET['archive'])) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'archive', '', get_post_type_archive_link('course') ) ); ?>"><?php _e('Archive', 'academic'); ?></a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( get_post_type_archive_link('course') ); ?>"><?php _e('Active Courses', 'academic'); ?></a>
                        <?php endif; ?>
                    </div>
                </main>
            </div><!-- primary -->
        </section>
    </div><!-- row -->
</div><!-- container -->
<?php
get_footer();
?>