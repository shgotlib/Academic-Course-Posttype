<?php
get_header();
get_sidebar();
$prefix = "cmb_";
?>

<div class="col-sm-9 col-xs-12">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
			
			<?php while ( have_posts() ) : the_post();
				$course = array();
                $course['id'] = get_the_ID();
                $course['url'] = get_permalink();
                $course['name'] = get_the_title();
                $course['number'] = get_post_meta( $course['id'], $prefix.'course_number', true );
                $course['credit'] = get_post_meta( $course['id'], $prefix.'course_credit', true );
                $course['year'] = get_post_meta( $course['id'], $prefix.'course_year', true );
                $course['semester'] = get_post_meta( $course['id'], $prefix.'course_semester', true );
                if ($course['semester'] == "") $course['semester'] = array("-");
                $course['active'] = get_post_meta( $course['id'], $prefix.'course_active', true ) ? true : false;
                $course['enrichment_course'] = get_post_meta( $course['id'], $prefix.'enrichment_course', true ) ? "V" : "";
                $course['lecturer_name'] = get_post_meta( $course['id'], $prefix.'course_lecturer_name', true );
                $course['type'] = get_post_meta( $course['id'], $prefix.'type_course', true );
                $course['lecturer_link'] = get_post_meta( $course['id'], $prefix.'course_lecturer_link', true );
                $course['syllabus'] = get_post_meta( $course['id'], $prefix.'course_syllabus', true );
                $course['file'] = get_post_meta( $course['id'], $prefix.'course_file', true );
                ?>
                <header class="entry-header">
                    <div class='field field-title'>
                        <h1>
                            <?php echo $course['name']; ?>
                            <span class="course-num">
                                <small><?php echo $course['number']; ?></small>
                            </span>
                        </h1>
                    </div>
                </header>
                <?php if (!$course['active']) : ?>
                    <p class="alert alert-warning"><?php _e('Course is inactive right now', 'academic'); ?></p>
                <?php endif; ?>
                <div class="entry-content">
                    <div class="">
                        <div class="course-main-details row">
                            <ul class="single-course-semester col-sm-4 col-xs-12">
                                <h3><?php _e('The course will be taught in semesters', 'academic'); ?>:</h3>
                                <?php foreach ($course['semester'] as $semester) : ?>
                                    <li><?php echo $semester; ?></li>    
                                <?php endforeach; ?>
                            </ul>
                            <div class="col-sm-4 col-xs-12">
                                <?php if($course['lecturer_name']) : ?>
                                    <h3><?php _e('Lecturer', 'academic'); ?>:</h3>
                                    <a href="<?php echo $course['lecturer_link'] ?>"><?php echo $course['lecturer_name']; ?></a>
                                <?php endif; ?>
                            </div>
                             <div class="col-sm-4 col-xs-12">
                                <?php if($course['type']) :  ?>
                                    <h3><?php _e('Course', 'academic'); ?>:</h3>
                                    <a href="<?php echo $course['type_course'] ?>"><?php echo $course['type'][0]; ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="syllabus">
                            <?php echo $course['syllabus']; ?>
                            <br>
                            <?php if ($course['file']) : ?>
                                <a href="<?php echo $course['file']; ?>"><?php _e('Download course file', 'academic'); ?><span class="glyphicon glyphicon-file"></span></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
				<?php the_post_navigation(array('prev_text' => __("Previous post - ").'%title', 'next_text' => __("Next post - ").'%title',)); ?>
			<?php endwhile; // End of the loop. ?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!--col-md-8 col-xs-12 -->

<?php
get_footer();