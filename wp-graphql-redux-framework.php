<?php
/**
 * Plugin Name: WP GraphQL Redux Framework
 * Plugin URI: https://github.com/l3ff4n0/wp-graphql-redux-framework
 * Description: A plugin to add Redux Framework to WP GraphQL.
 * Author: Stefano Frasson Pianizzola
 * Author URI: https://www.stefanofp.com
 * Text Domain: wp-graphql-redux
 * Version: 1.0.0-beta
 * Requires at least: 5.0
 * Tested up to: 6.0.1
 * Requires PHP: 7.0
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Checks if all the the required plugins are installed and activated.
 */
function graphql_redux_framework_dependencies(){
	
	if (!class_exists('Redux') && !class_exists('WPGraphQL')) {
		add_action('admin_notices', 'graphql_redux_framework_dependencies_notice');
		return false;
	}

	if (!class_exists('WPGraphQL')) {
		add_action('admin_notices', 'graphql_redux_framework_dependencies_notice');
		return false;
	}

	return true;
}

/**
 * Displays a notice if the required plugins are not installed and activated.
 */
function graphql_redux_framework_dependencies_notice(){ ?>
	<div class="notice notice-error">
		<p>
			<?php
				printf(
					__('The %s plugin requires the %s and %s plugins to be installed and activated.', 'wp-graphql-redux'),
					'<strong>Redux Framework</strong>',
					'<strong>WPGraphQL</strong>',
				);
			?>
		</p>
	</div>
<?php }

/**
 * Checks if the plugin is installed and activated.
 */
add_action( 'plugins_loaded', function() {

	graphql_redux_framework_dependencies();

	if ( ! class_exists( 'Redux' ) ) {
		return;
	}

	add_action( 'redux/loaded', function( $redux ) {

		$GLOBALS['opt_name'] = $redux->args['opt_name'];
    

		if ( ! isset( $redux->sections ) || empty( $redux->sections ) ) {
			return;
		}

		$opt_name = $redux->args['opt_name'];

		foreach ( $redux->sections as $section_id => $section ) {
		
			if ( isset( $section['show_in_graphql'] ) && $section['show_in_graphql'] === true) {
				$graphql_field_name = isset( $section['id'] ) ? \WPGraphQL\Utils\Utils::format_field_name( $section['id'] ) : \WPGraphQL\Utils\Utils::format_field_name( $section['title'] . '_Settings' );
				$graphql_type_name  = !empty($graphql_field_name) ? \WPGraphQL\Utils\Utils::format_type_name( $graphql_field_name ) : null;

				if ( empty( $graphql_type_name ) ) {
					return;
				}

				if ( !isset( $section['fields'] ) || empty($section['fields']) || ! is_array( $section['fields'] ) ) {
					return;
				}

				$fields = [];

				foreach ( $section['fields'] as $setting_field ) {

					$field_type	= isset( $setting_field['type'] ) ? $setting_field['type'] : null;
					$field_description = isset( $setting_field['desc'] ) ? $setting_field['desc'] : '';
					
					$type_field ='';

					if($field_type == 'media'){
						$type_field = 'MediaItem';
					}
					if($field_type == 'text'){
						$type_field = 'String';
					}
					
					$field_name = isset( $setting_field['id'] ) ? \WPGraphQL\Utils\Utils::format_field_name( $setting_field['id'] ) : \WPGraphQL\Utils\Utils::format_field_name( $setting_field['title'] );


					if ( isset($setting_field['show_in_graphql']) && $setting_field['show_in_graphql'] === true){

						$fields[ $field_name ] = [
							'type'        => $type_field,
							'description' => $field_description,
							'resolve'     => function( $source, $args, $context, $info ) use ( $opt_name, $redux, $setting_field, $type_field) {
								
								$value = Redux::getOption( $opt_name, $setting_field['id'], '' );
	
								switch ( $type_field ) {
									case 'MediaItem':
										return (!empty( $value['id'] )) ? $context->get_loader( 'post' )->load_deferred($value['id']) : null;
									default:
										return $value;
								}
	
							}
						];
					}
					
				}

				if ( empty( $fields ) ) {
					return;
				}

				register_graphql_object_type( $graphql_type_name, [
					'description' => ! empty( $section['desc'] ) ? $section['desc'] : sprintf( __( '%s Settings Section from Redux Framework', 'wp-graphql-redux' ) ),
					'fields'      => $fields
				] );

				register_graphql_field( 'RootQuery', $graphql_field_name, [
					'type'    => $graphql_field_name,
					'resolve' => function() use ( $section ) {
						return $section;
					}
				] );

			}
		}

		if(class_exists('WPGatsby')){
			class ReduxFrameWorkActionMonitor extends \WPGatsby\ActionMonitor\Monitors\Monitor {
		
			/**
			 * Initialize the custom tracker.
			 */
			public function init() {
				add_action( 'redux/options/'. $GLOBALS['opt_name'] .'/saved', [ $this, 'track_redux_save' ], 10, 2 );
			}

			/**
			 * Track a Redux save.
			 *
			 */
			public function track_redux_save() {
				$this->trigger_non_node_root_field_update(
					[
						'title' => __( 'Update Redux fields', 'WPGatsby'),
					]
				);
			}
		}
		
		add_filter( 'gatsby_action_monitors', function( array $monitors, \WPGatsby\ActionMonitor\ActionMonitor $action_monitor) {
			$monitors['ReduxFrameWorkActionMonitor'] = new ReduxFrameWorkActionMonitor( $action_monitor );
		
			return $monitors;
		}, 10, 2 );
		}

	} );

});