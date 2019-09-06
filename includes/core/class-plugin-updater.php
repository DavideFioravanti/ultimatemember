<?php
namespace um\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'um\core\Plugin_Updater' ) ) {


	/**
	 * Class Plugin_Updater
	 * @package um\core
	 */
	class Plugin_Updater {


		/**
		 * Plugin_Updater constructor.
		 */
		function __construct() {
			//create cron event
			if ( ! wp_next_scheduled( 'um_check_extensions_licenses' ) ) {
				wp_schedule_event( time() + ( 24*60*60 ), 'daily', 'um_check_extensions_licenses' );
			}

			register_deactivation_hook( um_plugin, array( &$this, 'um_plugin_updater_deactivation_hook' ) );

			//cron request to UM()->store_url;
			add_action( 'um_check_extensions_licenses', array( &$this, 'um_checklicenses' ) );

			//update plugin info
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );

			//plugin information info
			add_filter( 'plugins_api', array( &$this, 'plugin_information' ), 9999, 3 );
		}


		/**
		 * Get all paid UM extensions
		 *
		 * @return array
		 */
		function get_active_plugins() {
			$paid_extensions = array(
				'um-bbpress/um-bbpress.php'                             => array(
					'key'   => 'bbpress',
					'title' => 'bbPress',
				),
				'um-followers/um-followers.php'                         => array(
					'key'   => 'followers',
					'title' => 'Followers',
				),
				'um-friends/um-friends.php'                             => array(
					'key'   => 'friends',
					'title' => 'Friends',
				),
				'um-groups/um-groups.php'                               => array(
					'key'   => 'groups',
					'title' => 'Groups',
				),
				'um-instagram/um-instagram.php'                         => array(
					'key'   => 'instagram',
					'title' => 'Instagram',
				),
				'um-mailchimp/um-mailchimp.php'                         => array(
					'key'   => 'mailchimp',
					'title' => 'MailChimp',
				),
				'um-messaging/um-messaging.php'                         => array(
					'key'   => 'messaging',
					'title' => 'Private Messages',
				),
				'um-mycred/um-mycred.php'                               => array(
					'key'   => 'mycred',
					'title' => 'myCRED',
				),
				'um-notices/um-notices.php'                             => array(
					'key'   => 'notices',
					'title' => 'Notices',
				),
				'um-notifications/um-notifications.php'                 => array(
					'key'   => 'notifications',
					'title' => 'Real-time Notifications',
				),
				'um-profile-completeness/um-profile-completeness.php'   => array(
					'key'   => 'profile_completeness',
					'title' => 'Profile Completeness',
				),
				'um-reviews/um-reviews.php'                             => array(
					'key'   => 'reviews',
					'title' => 'User Reviews',
				),
				'um-social-activity/um-social-activity.php'             => array(
					'key'   => 'activity',
					'title' => 'Social Activity',
				),
				'um-social-login/um-social-login.php'                   => array(
					'key'   => 'social_login',
					'title' => 'Social Login',
				),
				'um-user-tags/um-user-tags.php'                         => array(
					'key'   => 'user_tags',
					'title' => 'User Tags',
				),
				'um-verified-users/um-verified-users.php'               => array(
					'key'   => 'verified_users',
					'title' => 'Verified Users',
				),
				'um-woocommerce/um-woocommerce.php'                     => array(
					'key'   => 'woocommerce',
					'title' => 'WooCommerce',
				),
				'um-user-photos/um-user-photos.php'                     => array(
					'key'   => 'user_photos',
					'title' => 'User Photos',
				),
				'um-private-content/um-private-content.php'             => array(
					'key'   => 'private_content',
					'title' => 'Private Content',
				),
				'um-user-bookmarks/um-user-bookmarks.php'               => array(
					'key'   => 'user_bookmarks',
					'title' => 'User Bookmarks',
				),
				'um-unsplash/um-unsplash.php'                           => array(
					'key'   => 'unsplash',
					'title' => 'Unsplash',
				),
				'um-user-notes/um-user-notes.php'                       => array(
					'key'   => 'user_notes',
					'title' => 'User Notes',
				),
				'um-frontend-posting/um-frontend-posting.php'           => array(
					'key'   => 'frontend_posting',
					'title' => 'Frontend Posting',
				),
				'um-filesharing/um-filesharing.php'                     => array(
					'key'   => 'filesharing',
					'title' => 'File Sharing',
				),
				'um-user-location/um-user-location.php'                     => array(
					'key'   => 'user-location',
					'title' => 'User Location',
				),
			);

			$active_um_plugins = array();
			if ( is_multisite() ) {
				// Per site activated
				$sites = get_sites();

				$sitewide_plugins = get_site_option( 'active_sitewide_plugins' );
				$sitewide_plugins = array_keys( $sitewide_plugins );

				foreach ( $sites as $site ) {
					switch_to_blog( $site->blog_id );

					$the_plugs = get_option( 'active_plugins' );
					$the_plugs = array_merge( $the_plugs, $sitewide_plugins );

					foreach ( $the_plugs as $key => $value ) {

						if ( in_array( $value, array_keys( $paid_extensions ) ) ) {
							$license = UM()->options()->get( "um_{$paid_extensions[ $value ]['key']}_license_key" );

							if ( empty( $license ) ) {
								continue;
							}

							$active_um_plugins[ $value ] = $paid_extensions[ $value ];
							$active_um_plugins[ $value ]['license'] = $license;
						}
					}

					restore_current_blog();
				}

			} else {
				$the_plugs = get_option( 'active_plugins' );
				foreach ( $the_plugs as $key => $value ) {

					if ( in_array( $value, array_keys( $paid_extensions ) ) ) {
						$license = UM()->options()->get( "um_{$paid_extensions[ $value ]['key']}_license_key" );

						if ( empty( $license ) ) {
							continue;
						}

						$active_um_plugins[ $value ] = $paid_extensions[ $value ];
						$active_um_plugins[ $value ]['license'] = $license;
					}
				}
			}

			return $active_um_plugins;
		}


		/**
		 * Remove CRON events on deactivation hook
		 */
		function um_plugin_updater_deactivation_hook() {
			wp_clear_scheduled_hook( 'um_check_extensions_licenses' );
		}


		/**
		 * Check license function
		 */
		function um_checklicenses() {
			$exts = $this->get_active_plugins();

			if ( 0 == count( $exts ) ) {
				return;
			}

			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$api_params = array(
				'edd_action' => 'check_licenses',
				'author'     => 'Ultimate Member',
				'url'        => home_url(),
			);

			$api_params['active_extensions'] = array();

			foreach ( $exts as $slug => $data ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug );

				$api_params['active_extensions'][ $slug ] = array(
					'slug'      => $slug,
					'license'   => $data['license'],
					'item_name' => $data['title'],
					'version'   => $plugin_data['Version']
				);
			}

			$request = wp_remote_post(
				UM()->store_url,
				array(
					'timeout'   => UM()->request_timeout,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
			}

			$request = ( $request ) ? maybe_unserialize( $request ) : false;

			if ( $request ) {
				foreach ( $exts as $slug => $data ) {
					if ( ! empty( $request->$slug->license_check ) ) {
						update_option( "{$data['key']}_edd_answer", $request->$slug->license_check );
					}

					if ( ! empty( $request->$slug->get_version_check ) ) {

						$request->$slug->get_version_check = json_decode( $request->$slug->get_version_check );

						if ( ! empty( $request->$slug->get_version_check->package ) ) {
							$request->$slug->get_version_check->package = $this->extend_download_url( $request->$slug->get_version_check->package, $slug, $data );
						}

						if ( ! empty( $request->$slug->get_version_check->download_link ) ) {
							$request->$slug->get_version_check->download_link = $this->extend_download_url( $request->$slug->get_version_check->download_link, $slug, $data );
						}

						if ( isset( $request->$slug->get_version_check->sections ) ) {
							$request->$slug->get_version_check->sections = maybe_unserialize( $request->$slug->get_version_check->sections );
							$request->$slug->get_version_check->sections = (array) $request->$slug->get_version_check->sections;
						} else {
							$request->$slug->get_version_check = new \WP_Error( 'plugins_api_failed',
								sprintf(
								/* translators: %s: support forums URL */
									__( 'An unexpected error occurred. Something may be wrong with %s or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
									UM()->store_url,
									__( 'https://wordpress.org/support/' )
								),
								wp_remote_retrieve_body( $request->$slug->get_version_check )
							);
						}

						if ( isset( $request->$slug->get_version_check->banners ) ) {
							$request->$slug->get_version_check->banners = maybe_unserialize( $request->$slug->get_version_check->banners );
						}

						if ( isset( $request->$slug->get_version_check->icons ) ) {
							$request->$slug->get_version_check->icons = maybe_unserialize( $request->$slug->get_version_check->icons );
						}

						if ( ! empty( $request->$slug->get_version_check->sections ) ) {
							foreach( $request->$slug->get_version_check->sections as $key => $section ) {
								$request->$slug->get_version_check->$key = (array) $section;
							}
						}

						$this->set_version_info_cache( $slug, $request->$slug->get_version_check );
					}
				}
			}

			return;
		}


		/**
		 * Check for Updates by request to the marketplace
		 * and modify the update array.
		 *
		 * @param array $_transient_data plugin update array build by WordPress.
		 * @return \stdClass modified plugin update array.
		 */
		function check_update( $_transient_data ) {
			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new \stdClass;
			}

			if ( 'plugins.php' == $pagenow && is_multisite() ) {
				return $_transient_data;
			}

			$exts = $this->get_active_plugins();

			foreach ( $exts as $slug => $data ) {
				//if response for current product isn't empty check for override
				if ( ! empty( $_transient_data->response ) && ! empty( $_transient_data->response[ $slug ] ) && $_transient_data->last_checked > time() - DAY_IN_SECONDS ) {
					continue;
				}

				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug );

				$version_info = $this->get_cached_version_info( $slug );
				if ( false === $version_info ) {
					$version_info = $this->single_request( 'plugin_latest_version', array(
						'slug'      => $slug,
						'license'   => $data['license'],
						'item_name' => $data['title'],
						'version'   => $plugin_data['Version']
					) );

					$this->set_version_info_cache( $slug, $version_info );
				}

				if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->new_version ) ) {
					//show update version block if new version > then current
					if ( version_compare( $plugin_data['Version'], $version_info->new_version, '<' ) ) {
						$_transient_data->response[ $slug ] = $version_info;
						$_transient_data->response[ $slug ]->plugin = $slug;
					}

					$_transient_data->last_checked      = time();
					$_transient_data->checked[ $slug ]  = $plugin_data['Version'];

				}
			}

			return $_transient_data;
		}



		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @uses get_bloginfo()
		 * @uses wp_remote_post()
		 * @uses is_wp_error()
		 *
		 * @param string  $_action The requested action.
		 * @param array   $_data   Parameters for the API action.
		 * @return false|object
		 */
		private function single_request( $_action, $_data ) {
			$api_params = array(
				'edd_action' => 'get_version',
				'author'     => 'Ultimate Member',
				'url'        => home_url(),
				'beta'       => false,
			);

			$api_params = array_merge( $api_params, $_data );

			$request    = wp_remote_post(
				UM()->store_url,
				array(
					'timeout'   => UM()->request_timeout,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
			}

			if ( $request && isset( $request->sections ) ) {
				$request->sections = maybe_unserialize( $request->sections );
				$request->sections = (array) $request->sections;
			} else {
				$request = false;
			}

			if ( $request && isset( $request->banners ) ) {
				$request->banners = maybe_unserialize( $request->banners );
			}

			if ( $request && isset( $request->icons ) ) {
				$request->icons = maybe_unserialize( $request->icons );
			}

			if( ! empty( $request->sections ) ) {
				foreach ( $request->sections as $key => $section ) {
					$request->$key = (array) $section;
				}
			}

			if ( ! empty( $request->package ) ) {
				$request->package = $this->extend_download_url( $request->package, $_data['slug'], $_data );
			}

			if ( ! empty( $request->download_link ) ) {
				$request->download_link = $this->extend_download_url( $request->download_link, $_data['slug'], $_data );
			}

			return $request;
		}


		/**
		 * Updates information on the "View version x.x details" popup with custom data.
		 *
		 * @param mixed   $_data
		 * @param string  $_action
		 * @param object  $_args
		 * @return object $_data
		 */
		function plugin_information( $_data, $_action = '', $_args = null ) {
			//by default $data = false (from Wordpress)
			if ( $_action != 'plugin_information' ) {
				return $_data;
			}

			$exts = $this->get_active_plugins();
			foreach ( $exts as $slug => $data ) {
				if ( isset( $_args->slug ) && $_args->slug == $slug ) {
					$api_request_transient = $this->get_cached_version_info( $slug );

					if ( false === $api_request_transient ) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug );

						$api_request_transient = $this->single_request( 'plugin_latest_version', array(
							'slug'      => $slug,
							'license'   => $data['license'],
							'item_name' => $data['title'],
							'version'   => $plugin_data['Version']
						) );
						$this->set_version_info_cache( $slug, $api_request_transient );
					}
					break;
				}
			}

			//If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
			if ( isset( $api_request_transient ) ) {
				$_data = $api_request_transient;
			}

			return $_data;
		}


		/**
		 * Disable SSL verification in order to prevent download update failures
		 *
		 * @param array   $args
		 * @param string  $url
		 * @return array $array
		 */
		function http_request_args( $args, $url ) {
			// If it is an https request and we are performing a package download, disable ssl verification
			if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'action=package_download' ) ) {
				$args['sslverify'] = false;
			}
			return $args;
		}


		/**
		 * Download extension URL
		 *
		 * @param $download_url
		 * @param $slug
		 * @param $data
		 *
		 * @return string
		 */
		function extend_download_url( $download_url, $slug, $data ) {

			$url = get_site_url( get_current_blog_id() );
			$domain  = strtolower( urlencode( rtrim( $url, '/' ) ) );

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug );

			$api_params = array(
				'action'        => 'get_last_version',
				'license'       => ! empty( $data['license'] ) ? $data['license'] : '',
				'item_name'     => str_replace( 'Ultimate Member - ', '', $plugin_data['Name'] ),
				'blog_id'       => get_current_blog_id(),
				'site_url'      => urlencode( $url ),
				'domain'        => urlencode( $domain ),
				'slug'          => urlencode( $slug ),
			);

			$download_url = add_query_arg( $api_params, $download_url );

			return $download_url;
		}


		/**
		 * @param $slug
		 *
		 * @return bool|string
		 */
		function get_cache_key( $slug ) {
			$exts = $this->get_active_plugins();

			if ( empty( $exts[ $slug ] ) ) {
				return false;
			}

			return 'edd_sl_' . md5( serialize( $slug . $exts[ $slug ]['license'] ) );
		}


		/**
		 * @param $slug
		 *
		 * @return array|bool|mixed|object
		 */
		function get_cached_version_info( $slug ) {
			$cache_key = $this->get_cache_key( $slug );
			if ( empty( $cache_key ) ) {
				return false;
			}

			$cache = get_option( $cache_key );
			if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {
				return false; // Cache is expired
			}

			// We need to turn the icons into an array, thanks to WP Core forcing these into an object at some point.
			$cache['value'] = json_decode( $cache['value'] );
			if ( ! empty( $cache['value']->icons ) ) {
				$cache['value']->icons = (array) $cache['value']->icons;
			}
			if ( ! empty( $cache['value']->sections ) ) {
				$cache['value']->sections = (array) $cache['value']->sections;
			}
			if ( ! empty( $cache['value']->banners ) ) {
				$cache['value']->banners = (array) $cache['value']->banners;
			}

			return $cache['value'];
		}


		/**
		 * @param $slug
		 * @param string $value
		 */
		function set_version_info_cache( $slug, $value = '' ) {
			$cache_key = $this->get_cache_key( $slug );
			if ( empty( $cache_key ) ) {
				return;
			}

			$data = array(
				'timeout' => strtotime( '+6 hours', time() ),
				'value'   => json_encode( $value )
			);

			update_option( $cache_key, $data, 'no' );
		}
	}

}