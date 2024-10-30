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
 * Handles the visibility and setup with the WordPress Settings API.
 */
class Settings {
	/**
	 * The default timeout value if none is set.
	 *
	 * @var integer
	 */
	protected $default_timeout;

	/**
	 * The default Discord message.
	 *
	 * @var string
	 */
	protected $default_message;

	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		$this->default_timeout = 60;
		$this->default_message = 'New entry or updates made to **{{post_title}}**.';
	}

	/**
	 * Intialises the options page.
	 */
	public function options_page():void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Discord Integration', 'rt-post-messenger' ); ?></h1>
			<form id='wpss-conf' action='options.php' method='post'>
			<?php
			settings_fields( 'wpupdatediscordbot' );
			do_settings_sections( 'wpupdatediscordbot' );
			submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Registers the 'Mail' setting underneath 'Settings' in the admin GUI.
	 */
	public function add_admin_menu():void {
		add_options_page(
			__( 'Discord', 'rt-post-messenger' ),
			__( 'Discord', 'rt-post-messenger' ),
			'manage_options',
			'plummeted16ftthroughanannouncerstable',
			array( &$this, 'options_page' )
		);
	}

	/**
	 * Initialises the settings implementation.
	 */
	public function settings_init():void {
		register_setting( 'wpupdatediscordbot', 'wpupdatediscordbot_hookurl' );
		register_setting(
			'wpupdatediscordbot',
			'wpupdatediscordbot_timeout',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
				'default'           => $this->default_timeout,
			)
		);
		register_setting(
			'wpupdatediscordbot',
			'wpupdatediscordbot_message',
			array(
				'sanitize_callback' => 'esc_html',
				'default'           => $this->default_message,
			)
		);

		add_settings_section(
			'wpupdatediscordbot_section',
			__( 'Discord Settings', 'rt-post-messenger' ),
			function () {
				esc_html_e( 'Configure WordPress to communicate with your Discord.', 'rt-post-messenger' );
			},
			'wpupdatediscordbot'
		);

		add_settings_field(
			'wpupdatediscordbot_hookurl',
			__( 'Hook URL', 'rt-post-messenger' ),
			array( &$this, 'render_setting_hook' ),
			'wpupdatediscordbot',
			'wpupdatediscordbot_section'
		);

		add_settings_field(
			'wpupdatediscordbot_timeout',
			__( 'Post Timeout', 'rt-post-messenger' ),
			array( &$this, 'render_setting_timeout' ),
			'wpupdatediscordbot',
			'wpupdatediscordbot_section'
		);

		add_settings_field(
			'wpupdatediscordbot_message',
			__( 'Post Message', 'rt-post-messenger' ),
			array( &$this, 'render_setting_message' ),
			'wpupdatediscordbot',
			'wpupdatediscordbot_section'
		);
	}

	/**
	 * Writes the hook input box to the page.
	 */
	public function render_setting_hook():void {
		$opt = get_option( 'wpupdatediscordbot_hookurl' );
		?>
		<input class='regular-text ltr' type='text' name='wpupdatediscordbot_hookurl' value='<?php echo esc_attr( $opt ); ?>' placeholder='https://discord.com/api/webhooks/blahblah...'>
		<p class='description'><?php esc_html_e( 'The hook URL can be found in Discord at Server Settings > Integrations > Webhooks.', 'rt-post-messenger' ); ?></p>
		<?php
	}

	/**
	 * Writes the timeout input box to the page.
	 */
	public function render_setting_timeout():void {
		$opt = get_option( 'wpupdatediscordbot_timeout' );
		?>
		<input class='ltr' type='number' name='wpupdatediscordbot_timeout' value='<?php echo intval( $opt ); ?>'> <?php esc_html_e( 'seconds', 'rt-post-messenger' ); ?>
		<p class='description'><?php esc_html_e( 'If a post/page is published during this timeframe, no Discord post will happen', 'rt-post-messenger' ); ?></p>
		<?php
	}

	/**
	 * Writes the message input box to the page.
	 */
	public function render_setting_message():void {
		$opt = get_option( 'wpupdatediscordbot_message' );
		?>
		<textarea class="large-text" name="wpupdatediscordbot_message"><?php echo esc_attr( $opt ); ?></textarea>
		<p class='description'><?php esc_html_e( 'Accepted values are:', 'rt-post-messenger' ); ?> <strong>post_id</strong>, <strong>post_title</strong>, <strong>post_author</strong>, <strong>post_date</strong>, <strong>post_modified</strong>, <strong>post_content</strong>, <strong>post_excerpt</strong>.</p>
		<?php
	}
}
