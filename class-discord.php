<?php
/**
 * Sends post and page interactions to a designated bot user webhook.
 *
 * @package rt-post-messenger
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace rtmessenger;

use WP_Post;

/**
 * Everything Discord API related.
 */
class Discord {
	/**
	 * Discord Webhook URL.
	 *
	 * @var string
	 */
	protected $webhook_url;

	/**
	 * Time allowed between updates, in seconds.
	 *
	 * @var int
	 */
	protected $timer;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->webhook_url = get_option( 'wpupdatediscordbot_hookurl' );
		$this->timer       = get_option( 'wpupdatediscordbot_timeout', 60 );
		$this->message     = get_option( 'wpupdatediscordbot_message', 'New entry or updates made to **{{post_title}}**.' );
	}

	/**
	 * Brings in the WordPress post/page object for hook usage.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function publish_handler( int $post_id, WP_Post $post ):void {
		if ( isset( $_REQUEST['wordcord_nonce'], $_REQUEST['wordcord_postit'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['wordcord_nonce'] ), 'wordcord_nonce' ) && '1' === $_REQUEST['wordcord_postit'] ) {
			$this->update_discord( $this->rewrite_variables( $post, $this->message ) . "\n" . get_permalink( $post_id ) );
		}
	}

	/**
	 * Sends an update to the specified Discord bot.
	 *
	 * @param string $message The message to send to Discord.
	 * @return bool Success status.
	 */
	public function update_discord( string $message ):bool {
		if ( empty( $this->webhook_url ) ) {
			return false;
		}

		if ( ! $this->timer_check() ) {
			return false;
		}

		$response = wp_remote_post(
			$this->webhook_url,
			array(
				'body' => array(
					'content' => $message,
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$this->timer_store();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks the time with the stored timer, and gives a boolean response if a minute passed since last update.
	 *
	 * @return bool True if the timer check succeeds, false if not.
	 */
	private function timer_check():bool {
		$lu_time = get_option( 'wpupdatediscordbot_lastupdate', 0 ) + $this->timer;

		if ( time() > $lu_time ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Stores the current time.
	 *
	 * @return bool Success.
	 */
	private function timer_store():bool {
		update_option( 'wpupdatediscordbot_lastupdate', time() );

		return true;
	}

	/**
	 * Replaces each reference string with the post value.
	 *
	 * @param WP_Post $post    The post object being used.
	 * @param string  $message The message to be sent to Discord.
	 * @return string Message with placeholders replaced with variable content.
	 */
	private function rewrite_variables( WP_Post $post, string $message ):string {
		$const = array(
			'post_id'       => $post->ID,
			'post_title'    => $post->post_title,
			'post_author'   => get_the_author_meta( 'display_name', $post->post_author ),
			'post_date'     => $post->post_date,
			'post_modified' => $post->post_modified,
			'post_content'  => $post->post_content,
			'post_excerpt'  => $post->post_excerpt,
		);

		foreach ( $const as $look => $value ) {
			$message = str_replace( '{{' . $look . '}}', $value, $message );
		}

		return $message;
	}
}
