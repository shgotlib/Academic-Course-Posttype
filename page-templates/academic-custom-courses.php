<?php
/*
 * Template Name: Technion custom courses
 */

get_header();

$lang = ICL_LANGUAGE_CODE;
$page_id = get_the_ID();
$page_title = get_the_title();
$courses = array();

$template_prefix = "course_template_";
$post_prefix = "cmb_";

$years_to_show = get_post_meta( $page_id, $template_prefix.'course_years', true );
$semesters_to_show = get_post_meta( $page_id, $template_prefix.'course_semesters', true );
$types_to_show = get_post_meta( $page_id, $template_prefix.'course_types', true );

$meta_query = array('relation' => 'AND');

if($years_to_show != '') {
    $meta_query[] = array(
        'key'       => $post_prefix.'course_year',
        'value'     => $years_to_show,
        'compare'   => '='
    );
}

if($semesters_to_show != '') {
    $meta_query[] = array(
        'key'       => $post_prefix.'course_semester',
        'value'     => sprintf(':"%s";', $semesters_to_show),
        'compare'   => 'LIKE'
    );
}

if($types_to_show != '') {
    $meta_query[] = array(
        'key'       => $post_prefix.'type_course',
        'value'     => sprintf(':"%s";', $types_to_show),
        'compare'   => 'LIKE'
    );
}

$args = array(
    'post_type' => 'course',
    'posts_per_page' => -1,
    'meta_query'    => $meta_query
);

$courses_query = new WP_Query( $args );

if($courses_query->have_posts()) {
    while($courses_query->have_posts()) {
        $courses_query->the_post();
        $courses[] = CoursePostType::add_course_from_query(get_the_ID());
    }
} else {
    echo __('No courses found', 'technion');
    get_footer();
    exit();
}
wp_register_script( 'test-courses', plugin_dir_url( dirname(__FILE__)).'/inc/js/table-courses.js', array('course_vue'), false, true );
wp_localize_script( 'test-courses', 'Courses', array(
    'courses' => json_encode($courses),
    'courseYears' => json_encode(CoursePostType::get_years_of_courses()),
) );
wp_enqueue_script( 'test-courses' );
?>

<div class="container">
	<div class="row">
        <?php get_sidebar(); ?>
		<section class="content-area col-sm-9" id="main_content_container">
			<header class="entry-header">
				<h1 class="entry-title"><?php echo $page_title; ?></h1>
			</header><!-- .entry-header -->

			<div id="primary" class="content-area">
				<main id="main" class="site-main" role="main">
                    <div id="courses-list-app">
                        <input id="free-search" type="text" v-model="freeSearch" value="" placeholder="<?php _e('Free search', 'technion'); ?>">
                        <table id="courses-table" role="grid" aria-live="polite" aria-relevant="additions">
                            <tr>
								<th><?php _e('Course number', 'technion'); ?></th>
								<th><?php _e('Course name', 'technion'); ?></th>
								<th><?php _e('Lecturer', 'technion'); ?></th>
								<th><?php _e('Degree', 'technion'); ?></th>
								<th><?php _e('Semester', 'technion'); ?></th>
                                <th><?php _e('Year', 'technion'); ?></th>
								<th><?php _e('Credits', 'technion'); ?></th>
								<th><?php _e('Type', 'technion'); ?></th>
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
                                <template v-if="!course.active"><span class="course-not-active label label-warning"><?php _e('Not active', 'technion'); ?></span></template>
                            </tr> 
                             
                        </table>
                        <?php if (!isset($_GET['archive'])) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'archive', '', get_post_type_archive_link('course') ) ); ?>"><?php _e('Archive', 'technion'); ?></a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( get_post_type_archive_link('course') ); ?>"><?php _e('Active Courses', 'technion'); ?></a>
                        <?php endif; ?>
                    </div>
                </main>
            </div><!-- primary -->
        </section>
    </div><!-- row -->
</div><!-- container -->

<?php



get_footer();