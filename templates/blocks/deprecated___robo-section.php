<?php
/**
 * Title: Robo: Section
 * SupportsMultiple: true
 * SupportJSX: true
 * Category: formating
 * Mode: edit
 * Align: full
 * AlignText: center
 */

// Create class attribute allowing for custom "className" and "align" values.

$id = 'block_robo_section__' . $block['id'];

$classes = '';
if( !empty($block['className']) ) {
    $classes .= sprintf( ' %s', $block['className'] );
}
if( !empty($block['align']) ) {
    $classes .= sprintf( ' align%s', $block['align'] );
}

// Section block output (front-end only).
if( $is_preview ) {}

$background = 'background: %s%s;';
$background_img = ' url(%s) %s center center';
$section['background'] = sprintf(
    $background,                                                // The format
    get_field('bg_color') ?: 'transparent',                     // The color - `bg_color`
    get_field('bg_image') ? sprintf(
        $background_img,                                        // The image format
        get_field('bg_image'),                                  // The url of image - `bg_image`
        get_field('bg_image_repeat') ? "repeat" : "no-repeat"   // The repeat parameter for image background
    ) : ''
);

$section['margin_top'] = sprintf('margin-top: %spx;', get_field('section_margin_top') ?: 0);
$section['margin_bottom'] = sprintf('margin-bottom: %spx;', get_field('section_margin_bottom') ?: 0);
$section['padding_top'] = sprintf('padding-top: %spx;', get_field('section_padding_top') ?: 0);
$section['padding_bottom'] = sprintf('padding-bottom: %spx;', get_field('section_padding_bottom') ?: 0);

$align_columns = sprintf('align-items: %s;', get_field('align-items') ?: 'center');

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($classes);?> <?php echo esc_attr('block-' . $id); ?>" data-template="robo-section.php" >
    <InnerBlocks />
</div>

<style type="text/css">
	.block-<?php echo $id; ?>{
		display: block;
        min-height: 1rem;
        <?= $section['background']; ?>
        <?= $section['margin_top']; ?>
        <?= $section['margin_bottom']; ?>
        <?= $section['padding_top']; ?>
        <?= $section['padding_bottom']; ?>
	}
    .block-<?= $id; ?> .wp-block-columns {
        display: flex;
        max-width: 1280px;
        width: 100%;
        flex-flow: row nowrap;
        justify-content: center;
        margin: 0 auto;
        <?= $align_columns; ?>
    }
</style>