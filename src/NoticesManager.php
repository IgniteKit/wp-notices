<?php

namespace IgniteKit\WP\Notices;

/**
 * Class NoticesManager
 * @package IgniteKit\WP\Notices
 */
class NoticesManager implements NoticesInterface {

	/**
	 * The instance prefix
	 * Must be unique and short.
	 * @var string
	 */
	private $prefix;

	/**
	 * The queue of notices
	 * @var array
	 */
	private $notices;

	/**
	 * NoticesManager constructor.
	 *
	 * @param $prefix
	 */
	public function __construct( $prefix ) {
		$this->prefix  = $prefix;
		$this->notices = array();
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_footer', array( &$this, 'admin_footer' ), PHP_INT_MAX - 5000 );
		add_action( 'wp_ajax_' . $this->get_dismiss_action(), array( &$this, 'handle_ajax' ) );
	}

	/**
	 * Handles ajax dismissal
	 */
	public function handle_ajax() {

		if ( ! check_ajax_referer( $this->get_nonce_key(), false, false ) ) {
			wp_send_json_error();
			exit;
		}

		$id     = filter_input( INPUT_GET, 'notice_id', FILTER_SANITIZE_STRING );
		$notice = $this->get_notice_by_id( $id );
		if ( ! is_null( $notice ) ) {
			$notice->dismiss();
			wp_send_json_success();
			exit;
		}
		wp_send_json_error();
		exit;
	}

	/**
	 * Enqueue the admin scripts
	 */
	public function admin_footer() {
		?>
        <script>
            /**
             * Admin code for dismissing notifications.
             *
             */
            (function ($) {
                $(document).on('click', '.dg-notice-<?php echo $this->prefix; ?> .notice-dismiss', function (e) {
                    e.preventDefault();
                    var $notice = $(this).parent('.is-dismissible');
                    var dismiss_url = $notice.attr('data-dismiss-url');
                    if (dismiss_url) {
                        $.ajax({
                            url: dismiss_url,
                            cache: false,
                            type: 'GET',
                            success: function (response) {
                                if (response.success) {
                                    $notice.remove();
                                }
                            },
                            error: function () {
                                console.warn('Error dismissing notice.');
                            }
                        })
                    }
                });
            })(jQuery);
        </script>
		<?php
	}

	/**
	 * Show admin notices
	 */
	public function admin_notices() {
		foreach ( $this->notices as $notice ) {
			$notice->print();
		}
	}

	/**
	 * Add error notice. Displayed with red border.
	 *
	 * @param string $key - unique identifier
	 * @param string $message - html of the notice
	 * @param string|int $expiry - Specifes how much time the notice stays disabled.
	 *
	 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
	 *
	 * @return Notice
	 */
	public function add_error( $key, $message, $expiry = self::DISMISS_FOREVER ) {
		return $this->add_notice( $key, 'error', $message, $expiry );
	}

	/**
	 * Add warning notice. Displayed with orange border.
	 *
	 * @param string $key - unique identifier
	 * @param string $message - html of the notice
	 * @param string|int $expiry - Specifes how much time the notice stays disabled.
	 *
	 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
	 *
	 * @return Notice
	 */
	public function add_warning( $key, $message, $expiry = self::DISMISS_FOREVER ) {
		return $this->add_notice( $key, 'warning', $message, $expiry );
	}

	/**
	 * Add success notice. Displayed with greeen border.
	 *
	 * @param $key
	 * @param $message - html of the notice
	 * @param string|int $expiry - Specifes how much time the notice stays disabled.
	 *
	 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
	 *
	 * @return Notice
	 */
	public function add_success( $key, $message, $expiry = self::DISMISS_FOREVER ) {
		return $this->add_notice( $key, 'success', $message, $expiry );
	}

	/**
	 * Add info notice. Displayed with blue border.
	 *
	 * @param string $key - unique identifier
	 * @param string $message - html of the notice
	 * @param string|int $expiry - Specifes how much time the notice stays disabled.
	 *
	 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
	 *
	 * @return Notice
	 */
	public function add_info( $key, $message, $expiry = self::DISMISS_FOREVER ) {
		return $this->add_notice( $key, 'info', $message, $expiry );
	}

	/**
	 * Add custom notice. Displayed with gray border.
	 *
	 * @param string $key - unique identifier
	 * @param string $message - html of the notice
	 * @param string|int $expiry - Specifes how much time the notice stays disabled.
	 *
	 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
	 *
	 * @return Notice
	 */
	public function add_custom( $key, $message, $expiry = self::DISMISS_FOREVER ) {
		return $this->add_notice( $key, 'custom', $message, $expiry );
	}

	/**
	 * Add notice
	 *
	 * @param $type
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return Notice
	 */
	private function add_notice( $key, $type, $message, $expiry ) {
		$notice                       = new Notice( $key, $type, $message, $expiry, $this->get_dismiss_url(), $this->prefix );
		$this->notices[ $notice->id ] = $notice;

		return $this->notices[ $notice->id ];
	}

	/**
	 * The dismiss url
	 * @return string
	 */
	private function get_dismiss_url() {

		$params = array(
			'action' => $this->get_dismiss_action(),
		);

		return add_query_arg( $params, admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * The dismiss action
	 * @return string
	 */
	private function get_dismiss_action() {
		return sprintf( '%s_notice_dismiss', $this->prefix );
	}

	/**
	 * The nonce key
	 * @return string
	 */
	private function get_nonce_key() {
		return sprintf( '%s_nonce', $this->prefix );
	}

	/**
	 * Return the notice object
	 *
	 * @param $id
	 *
	 * @return Notice|null
	 */
	public function get_notice_by_id( $id ) {

		return isset( $this->notices[ $id ] ) ? $this->notices[ $id ] : null;
	}

	/**
	 * Return notice by key and type
	 *
	 * @param $key
	 * @param $type
	 *
	 * @return Notice|null
	 */
	public function get_notice( $key, $type ) {
		$id = Notice::generate_id( $key, $type );

		return $this->get_notice_by_id( $id );
	}
}
