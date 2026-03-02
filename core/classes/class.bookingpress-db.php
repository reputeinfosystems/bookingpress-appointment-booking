<?php

if( !defined( 'ABSPATH' ) ) exit;

if( !defined( 'BookingPressDb' ) ){

    class BookingPressDb{

        public static function bpa_fetch_records( $args = [] ){

            global $wpdb;

            static $cache = [];

            $key = md5( strtolower( preg_replace( '/\s+/', ' ', maybe_serialize( $args ) ) ) );

            if ( isset( $cache[$key] ) ) {
                return $cache[$key];
            }

            $defaults = [
                'columns'   => '*',
                'table'     => '',
                'where'     => 'WHERE 1=1',
                'type'      => OBJECT,
                'order_by'  => '',
                'is_single' => false
            ];

            $args = wp_parse_args( $args, $defaults );

            if( empty( $args['table'] ) ){
                return false;
            }

            $fetch_func = 'get_results';
            if( true === $args['is_single'] ){
                $fetch_func = 'get_row';
            }
            $query_string = trim( "SELECT {$args['columns']} FROM {$args['table']} {$args['where']} {$args['order_by']}" );
            $result = $wpdb->$fetch_func($query_string, $args['type']);
            
            if( is_null( $result ) ){
                $result = [];
            }

            $cache[ $key ] = $result;

            return $result;

        }

    }

}