<?php
/**
 * Sends post and page interactions to a designated bot user webhook.
 *
 * @package rt-post-messenger
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace rtmessenger;

/**
 * Handles control inputs found on Gutenberg & Classic Editor.
 */
class Metabox {
	/**
	 * Adds a box in editor view to enable custom settings.
	 *
	 * @return void Adds meta boxes into WP.
	 */
	public function form_setup():void {
		add_meta_box(
			'wordcordsettings',
			__( 'Post to Discord', 'rt-post-messenger' ),
			function( $post ) {
				?>
				<input type="hidden" name="wordcord_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wordcord_nonce' ) ); ?>">
				<div>
					<input type="checkbox" name="wordcord_postit" value="1" checked />
					<label for="wordcord_postit"><?php esc_html_e( 'Post to Discord', 'rt-post-messenger' ); ?></label>
				</div>
				<?php
			},
			array( 'post', 'page' ),
			'side',
			'low'
		);
	}
}
