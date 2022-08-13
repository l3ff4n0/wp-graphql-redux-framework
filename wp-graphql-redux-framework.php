<?php

/**
 * Plugin Name: WP GraphQL Redux Framework
 * Plugin URI:
 * Description: A plugin to add Redux Framework to WP GraphQL.
 * Author: Stefano Frasson Pianizzola
 * Author URI: https://www.stefanofp.com
 * Text Domain: wp-graphql-redux
 * Version: 0.0.1-alpha
*/

if (!defined('ABSPATH')) {
    exit();
}

add_action( 'plugins_loaded', function() {

	if ( ! class_exists( 'Redux' ) ) {
		return;
	}

	add_action( 'redux/loaded', function( $redux ) {
    

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
					
					$field_type        = 'String';
					$field_description = isset( $setting_field['desc'] ) ? $setting_field['desc'] : '';

					$field_name = isset( $setting_field['id'] ) ? \WPGraphQL\Utils\Utils::format_field_name( $setting_field['id'] ) : \WPGraphQL\Utils\Utils::format_field_name( $setting_field['title'] );


					if ( isset($setting_field['show_in_graphql']) && $setting_field['show_in_graphql'] === true){
						$fields[ $field_name ] = [
							'type'        => $field_type,
							'description' => $field_description,
							'resolve'     => function() use ( $opt_name, $redux, $setting_field ) {
								return Redux::get_option( $opt_name, $setting_field['id'], 'goo' );
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

	} );

} );