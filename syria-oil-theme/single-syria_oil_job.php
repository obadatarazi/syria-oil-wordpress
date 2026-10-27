<?php
/**
 * Single Job listing.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$meta = function_exists( 'syria_oil_get_job_meta' ) ? syria_oil_get_job_meta( get_the_ID() ) : array();
	$careers_page = get_page_by_path( 'careers' );
	$careers_url  = $careers_page ? get_permalink( $careers_page ) : home_url( '/' );
	?>
	<article <?php post_class( 'so-job-single' ); ?>>
		<header class="page-hero so-job-single__hero">
			<div class="container">
				<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'syria-oil' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'الرئيسية', 'syria-oil' ); ?></a>
					<span class="sep">/</span>
					<a href="<?php echo esc_url( $careers_url ); ?>"><?php esc_html_e( 'فرص العمل', 'syria-oil' ); ?></a>
					<span class="sep">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>
				<?php if ( ! empty( $meta['entity'] ) ) : ?>
					<p class="so-job-single__entity"><?php echo esc_html( $meta['entity'] ); ?></p>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( empty( $meta['is_open'] ) ) : ?>
					<p class="so-job-badge so-job-badge--closed"><?php esc_html_e( 'هذه الفرصة غير مفتوحة حالياً', 'syria-oil' ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="container so-job-single__wrap">
			<ul class="so-job-meta">
				<?php if ( ! empty( $meta['positions'] ) ) : ?>
					<li><strong><?php esc_html_e( 'الشواغر', 'syria-oil' ); ?></strong> <?php echo (int) $meta['positions']; ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['type_label'] ) ) : ?>
					<li><strong><?php esc_html_e( 'النوع', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['type_label'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['mode_label'] ) ) : ?>
					<li><strong><?php esc_html_e( 'نمط العمل', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['mode_label'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['location'] ) ) : ?>
					<li><strong><?php esc_html_e( 'الموقع', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['location'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['department'] ) ) : ?>
					<li><strong><?php esc_html_e( 'القسم', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['department'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['experience'] ) ) : ?>
					<li><strong><?php esc_html_e( 'الخبرة', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['experience'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['salary'] ) ) : ?>
					<li><strong><?php esc_html_e( 'الراتب', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['salary'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $meta['deadline'] ) ) : ?>
					<li><strong><?php esc_html_e( 'آخر موعد', 'syria-oil' ); ?></strong> <?php echo esc_html( $meta['deadline'] ); ?></li>
				<?php endif; ?>
			</ul>

			<?php if ( ! empty( $meta['education'] ) ) : ?>
				<div class="so-job-single__block">
					<h2><?php esc_html_e( 'المؤهل العلمي', 'syria-oil' ); ?></h2>
					<p><?php echo nl2br( esc_html( $meta['education'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<div class="so-job-single__content entry-content">
				<?php the_content(); ?>
			</div>

			<?php if ( ! empty( $meta['is_open'] ) && function_exists( 'syria_oil_render_job_apply_form' ) ) : ?>
				<section class="so-job-single__apply" id="apply">
					<h2><?php esc_html_e( 'قدّم على هذه الفرصة', 'syria-oil' ); ?></h2>
					<?php syria_oil_render_job_apply_form( get_the_ID() ); ?>
				</section>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
