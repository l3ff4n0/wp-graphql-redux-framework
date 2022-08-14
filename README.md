![MasterHead](https://stefanofp.com/wp-content/uploads/2022/08/wpgraphql-redux-framework.jpg)
# GraphQL for Redux Framework plugin

Adds WPGraphQL support for [Redux Framework](https://redux.io/).

## System Requirements

* PHP 7.4+
* WordPress 6.0.1
* WPGraphQL 1.9.0
* Redux Framework 4.3.16

## Quick Install

1. Install & activate [WPGraphQL](https://www.wpgraphql.com/).
2. Install & activate [Redux Framework](https://redux.io/).
3. Download the zip of this repository, upload it to your WordPress install, and activate the plugin.

## Compatibility
- [x] Add notices for WordPress [WPGraphQL](https://wordpress.org/plugins/wp-graphql/) and [Redux Framework](https://redux.io/) plugins dependencies
- [x] [WPGatsby plugin](https://wordpress.org/plugins/wp-gatsby/)

## How to add the redux fields on our theme and expose them to GrahQL

To expose the fields on GraphQL you have to add only the new key **'show_in_graphql'** setted to **true** inside the array of your field or section.


You can also changed the **data string inside the $opt_name variable** and automatically will be fired on the Redux Hook save **('redux/options/{opt_name}/saved')**

You can see an example of how it works.

```
if (!class_exists( 'Redux' )) {
        return;
    }    

    Redux::disable_demo();

    $opt_name = 'data';

    $theme = wp_get_theme();

    $args = array(
        'display_name'         => $theme->get( 'Name' ),
        'display_version'      => $theme->get( 'Version' ),
        'menu_title'           => esc_html__( 'Theme options', 'gatsby-theme-redux' ),
        'customizer'           => false,
        'page_priority'        => '85',
    );

    Redux::setArgs( $opt_name, $args );

    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'General', 'gatsby-theme-redux' ),
        'id'     => 'general',
        'desc'   => esc_html__( 'General', 'gatsby-theme-redux' ),
        'icon'   => 'el el-home',
        'show_in_graphql' => true,
        'fields' => array(
            array(
                'id'       => 'logo',
                'type'     => 'media',
                'title'    => esc_html__( 'Examample Logo', 'gatsby-theme-redux' ),
                'desc'     => esc_html__( 'Upload the logo', 'gatsby-theme-redux' ),
                'show_in_graphql' => true,
            ),
            array(
                'id'       => 'example_field',
                'type'     => 'text',
                'title'    => esc_html__( 'Example field', 'gatsby-theme-redux' ),
                'desc'     => esc_html__( 'Write the Example field', 'gatsby-theme-redux' ),
                'show_in_graphql' => true,
            ),
            array(
                'id'       => 'example_phone_number',
                'type'     => 'text',
                'title'    => esc_html__( 'Example Phone number', 'gatsby-theme-redux' ),
                'desc'     => esc_html__( 'Write the example phone number', 'gatsby-theme-redux' ),
                'show_in_graphql' => true,
            ),
            array(
                'id'       => 'example_mobile_number',
                'type'     => 'text',
                'title'    => esc_html__( 'Example Mobile number', 'gatsby-theme-redux' ),
                'desc'     => esc_html__( 'Write the example mobile number', 'gatsby-theme-redux' ),
                'show_in_graphql' => true,
            ),
        )
    ) );
```