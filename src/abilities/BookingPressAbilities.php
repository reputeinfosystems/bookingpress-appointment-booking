<?php

namespace BookingPress\abilities;

if( !defined('ABSPATH')) exit;

use BookingPress\core\Permissions;

class BookingPressAbilities extends Base{

    public static function register_abilities(){

        add_action( 'wp_abilities_api_categories_init', [ __CLASS__, 'register_ability_categories_callback' ] );

        add_action( 'wp_abilities_api_init', [ __CLASS__, 'register_abilities_callback' ] );

    }

    public static function register_ability_categories_callback(){
        wp_register_ability_category(
            'bookingpress-read',
            [
                'label' => __( 'BookingPress Read', 'bookingpress-appointment-booking' ),
                'description' => __( 'Retrieve BookingPress Data', 'bookingpress-appointment-booking' )
            ]
        );

        wp_register_ability_category(
            'bookingpress-write',
            [
                'label' => __( 'BookingPress Write', 'bookingpress-appointment-booking' ),
                'description' => __( 'Modify BookingPress Data', 'bookingpress-appointment-booking' )
            ]
        );

        wp_register_ability_category(
            'bookingpress-delete',
            [
                'label' => __( 'BookingPress Delete', 'bookingpress-appointment-booking' ),
                'description' => __( 'Delete BookingPress Data', 'bookingpress-appointment-booking' )
            ]
        );
    }

    public static function register_abilities_callback(){
        wp_register_ability(
            'bookingpress/list-service-categories',
            [
                'label' => __( 'List Service Categories', 'bookingpress-appointment-booking' ),
                'description' => __( 'Ability to list service categories of BookingPress', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ \BookingPress\api\ServicesRoutes::class, 'bookingpress_ability_list_category'],
                'permission_callback' => '__return_true',
                'input_schema' => self::bookingpress_default_pagination_input_schema( 'categories' ),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'categories' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'category_id' => [
                                        'type' => 'integer',
                                    ],
                                    'category_name' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ]
                        ]
                    ],
                    'required' => [ 'categories' ],
                ],
                'meta' => [
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'public' => true,
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ],
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/list-services',
            [
                'label' => __( 'List Services', 'bookingpress-appointment-booking' ),
                'description' => __( 'Ability to list services of BookingPress', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ \BookingPress\api\ServicesRoutes::class, 'bookingpress_ability_list_services'],
                'permission_callback' => '__return_true',
                /** Lets Pro and add-ons add their own filters. */
                'input_schema' => apply_filters( 'bookingpress_ability_list_services_input_schema', self::bookingpress_fetch_input_schema_for_services() ),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'items' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => [
                                        'type' => 'integer',
                                    ],
                                    'service_id' => [
                                        'type' => 'integer',
                                    ],
                                    'service_name' => [
                                        'type' => 'string',
                                    ],
                                    'service_category' => [
                                        'type' => 'string',
                                    ],
                                    'service_duration' => [
                                        'type' => 'string'
                                    ],
                                    'service_duration_val' => [
                                        'type' => 'string'
                                    ],
                                    'service_duration_unit' => [
                                        'type' => 'string'
                                    ],
                                    'service_price' => [
                                        'type' => 'string'
                                    ],
                                    'service_description' => [
                                        'type' => 'string'
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'required' => []
                ],
                'meta' => [
                    'public' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ],
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/get-service',
            [
                'label' => __( 'Get single service', 'bookingpress-appointment-booking' ),
                'description' => __( 'Get single service', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ \BookingPress\api\ServicesRoutes::class, 'bookingpress_ability_get_service'],
                'permission_callback' => '__return_true',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'service_id' => [
                            'type' => 'integer',
                        ]
                    ],
                    'required' => ['service_id']
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'service_id' => [
                            'type' => 'integer',
                        ],
                        'service_name' => [
                            'type' => 'string',
                        ],
                        'service_category' => [
                            'type' => 'string',
                        ],
                        'service_duration' => [
                            'type' => 'string',
                        ],
                        'service_price' => [
                            'type' => 'string',
                        ],
                        'service_description' => [
                            'type' => 'string',
                        ],
                    ],
                    'required' => [],
                ],
                'meta' => [
                    'public' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ],
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/list-appointments',
            [
                'label' => __( 'List appointments', 'bookingpress-appointment-booking' ),
                'description' => __( 'List appointments', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ __CLASS__, 'bookingpress_ability_list_appointments'],
                'permission_callback' => [ __CLASS__, 'bookingpress_verify_appointment_permission'],
                'input_schema' => [
                    'type' => 'object',
                    'default' => [
                        'start_date' => '',
                        'end_date' => '',
                        'status' => 0,
                        'service_name' => '',
                        'service_id' => 0,
                        'customer' => '',
                        'page' => 1,
                        'per_page' => 10,
                    ],
                    'properties' => [
                        'start_date' => [
                            'type' => 'string',
                        ],
                        'end_date' => [
                            'type' => 'string',
                        ],
                        'status' => [
                            'type' => 'integer',
                        ],
                        'service_name' => [
                            'type' => 'string',
                        ],
                        'service_id' => [
                            'type' => 'integer',
                        ],
                        'customer' => [
                            'type' => 'string',
                        ],
                        'page' => [
                            'type' => 'integer',
                            'minimum' => 1,
                        ],
                        'per_page' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 100,
                        ],
                    ],
                    'required' => [],
                ],
                'meta' => [
                    'public' => true,
                    'show_in_rest' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ]
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/get-appointment',
            [
                'label' => __( 'Get appointment', 'bookingpress-appointment-booking' ),
                'description' => __( 'Get appointment', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ __CLASS__, 'bookingpress_ability_get_appointment'],
                'permission_callback' => [ __CLASS__, 'bookingpress_verify_appointment_permission'],
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'appointment_id' => [
                            'type' => 'integer',
                        ]
                    ],
                    'required' => ['appointment_id']
                ],
                'meta' => [
                    'public' => true,
                    'show_in_rest' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ]
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/get-available-time-slots',
            [
                'label' => __( 'List available time slots', 'bookingpress-appointment-booking' ),
                'description' => __( 'List available time slots', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ \BookingPress\api\TimeRoutes::class, 'bookingpress_ability_list_available_time_slots'],
                'permission_callback' => '__return_true',
                'input_schema' => apply_filters( 'bookingpress_ability_time_slots_input_schema', [
                    'type' => 'object',
                    'properties' => [
                        'service_id' => [
                            'type' => 'integer',
                        ],
                        'date' => [
                            'type' => 'string',
                        ]
                    ],
                    'required' => ['service_id', 'date']
                ] ),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'available_time_slots' => [
                            'type' => 'array',
                            'description' => esc_html__('List of available appointment time slots.', 'bookingpress-appointment-booking'),
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'start_time' => [
                                        'type' => 'string',
                                        'description' => esc_html__('Start time in HH:mm format.', 'bookingpress-appointment-booking'),
                                    ],
                                    'end_time' => [
                                        'type' => 'string',
                                        'description' => esc_html__('End time in HH:mm format.', 'bookingpress-appointment-booking'),
                                    ],
                                    'total_capacity' => [
                                        'type' => 'integer',
                                        'description' => esc_html__('Total booking capacity for this time slot.', 'bookingpress-appointment-booking'),
                                    ],
                                    'available_capacity' => [
                                        'type' => 'integer',
                                        'description' => esc_html__('Remaining booking capacity for this time slot.', 'bookingpress-appointment-booking'),
                                    ],
                                    'formatted_start_time' => [
                                        'type' => 'string',
                                        'description' => esc_html__('Human-readable start time.', 'bookingpress-appointment-booking'),
                                    ],
                                    'formatted_end_time' => [
                                        'type' => 'string',
                                        'description' => esc_html__('Human-readable end time.', 'bookingpress-appointment-booking'),
                                    ],
                                ],
                                'required' => [
                                    'start_time',
                                    'end_time',
                                    'total_capacity',
                                    'available_capacity',
                                    'formatted_start_time',
                                    'formatted_end_time',
                                ],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                    'required' => [
                        'available_time_slots',
                    ],
                    'additionalProperties' => false,
                ],
                'meta' => [
                    'public' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ]
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/find-next-available-slot',
            [
                'label' => __( 'Find next available slot', 'bookingpress-appointment-booking' ),
                'description' => __( 'Find next available slot', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ \BookingPress\api\TimeRoutes::class, 'bookingpress_ability_find_next_available_slot'],
                'permission_callback' => '__return_true',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'service_id' => [
                            'type' => 'integer',
                        ],
                    ],
                    'required' => ['service_id']
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'available_timeslot' => [
                            'type' => 'object',
                            'properties' => [
                                'date' => [
                                    'type' => 'string',
                                    'format' => 'date'
                                ],
                                'start_time' => [
                                    'type' => 'string',
                                    'format' => 'time'
                                ],
                                'end_time' => [
                                    'type' => 'string',
                                    'format' => 'time'
                                ],
                                'total_capacity' => [
                                    'type' => 'integer'
                                ],
                                'available_capacity' => [
                                    'type' => 'integer'
                                ],
                                'formatted_start_time' => [
                                    'type' => 'string'
                                ],
                                'formatted_end_time' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                    ],
                    'required' => ['available_timeslot']
                ],
                'meta' => [
                    'public' => true,
                    'show_in_rest' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ]
                ]   
            ]
        );

        wp_register_ability(
            'bookingpress/get-price-quote',
            [
                'label' => __( 'Get price quote', 'bookingpress-appointment-booking' ),
                'description' => __( 'Get price quote', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-read',
                'execute_callback' => [ \BookingPress\api\AppointmentRoutes::class, 'bookingpress_ability_get_price_quote'],
                'permission_callback' => '__return_true',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'service_id' => [
                            'type' => 'integer',
                        ],
                    ],
                    'required' => ['service_id']
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'price_details' => [
                            'type' => 'object',
                            'properties' => [
                                'service_id' => [
                                    'type' => 'integer'
                                ],
                                'service_name' => [
                                    'type' => 'string'
                                ],
                                'price' => [
                                    'type' => 'number'
                                ],
                                'price_with_currency' => [
                                    'type' => 'string'
                                ],
                                'currency_code' => [
                                    'type' => 'string'
                                ],
                                'currency_symbol' => [
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ]
                ],
                'meta' => [
                    'public' => true,
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ]
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/cancel-appointment',
            [
                'label' => __( 'Cancel appointment', 'bookingpress-appointment-booking' ),
                'description' => __( 'Cancel appointment', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-delete',
                'execute_callback' => [ \BookingPress\api\AppointmentRoutes::class, 'bookingpress_ability_cancel_appointment'],
                'permission_callback' => [ __CLASS__, 'bookingpress_verify_appointment_permission' ],
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'appointment_id' => [
                            'type' => 'integer',
                        ],
                    ],
                    'required' => ['appointment_id']
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                        ],
                        'message' => [
                            'type' => 'string',
                        ]
                    ]
                ],
                'meta' => [
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'public' => true,
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => false,
                        'destructive' => true,
                        'idempotent' => true,
                    ]
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/create-appointment',
            [
                'label' => __( 'Create appointment', 'bookingpress-appointment-booking' ),
                'description' => __( 'Create appointment', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-write',
                'execute_callback' => [ \BookingPress\api\AppointmentRoutes::class, 'bookingpress_ability_create_appointment'],
                'permission_callback' => [ __CLASS__, 'bookingpress_verify_create_appointment_permission' ],
                'input_schema' => apply_filters( 'bookingpress_ability_create_appointment_input_schema', [
                    'type' => 'object',
                    'properties' => [
                        'service_id' => [
                            'type' => 'integer',
                        ],
                        'customer_id' => [
                            'type' => 'integer',
                        ],
                        'customer_email' => [
                            'type' => 'string',
                        ],
                        'booking_date' => [
                            'type' => 'string',
                        ],
                        'booking_time' => [
                            'type' => 'string',
                        ],
                    ],
                    'required' => ['service_id', 'booking_date', 'booking_time']
                ] ),
                'meta' => [
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'public' => true,
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => false,
                        'destructive' => false,
                        'idempotent' => false,
                    ]
                ]
            ]
        );

        wp_register_ability(
            'bookingpress/update-appointment-status',
            [
                'label' => __( 'Update appointment status', 'bookingpress-appointment-booking' ),
                'description' => __( 'Update appointment status', 'bookingpress-appointment-booking' ),
                'category' => 'bookingpress-write',
                'execute_callback' => [ \BookingPress\api\AppointmentRoutes::class, 'bookingpress_ability_update_appointment_status' ],
                'permission_callback' => [ __CLASS__, 'bookingpress_verify_update_appointment_status_permission' ],
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'appointment_id' => [
                            'type' => 'integer',
                        ],
                        'appointment_status' => [
                            'type' => 'integer',
                        ],
                    ],
                    'required' => ['appointment_id', 'appointment_status']
                ],
                'meta' => [
                    'mcp' => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                    'public' => true,
                    'show_in_rest' => true,
                    'annotations' => [
                        'readonly' => false,
                        'destructive' => false,
                        'idempotent' => false,
                    ]
                ]
            ]
        );
    }

    public static function bookingpress_ability_list_appointments( $input = [] ){

        global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_form_fields, $bookingpress_appointment;

        $appointment_scope = \BookingPress\abilities\BookingPressAbilities::bookingpress_appointment_scope();

        if( empty( $appointment_scope ) || ! is_user_logged_in() ) {
            return new \WP_Error(
                'invalid_scope',
                __( 'Invalid scope to get the appointment.', 'bookingpress-appointment-booking'),
                [ 'status' => 403 ]
            );
        }

        $where = ' WHERE 1 = 1';
        if( 'customer' == $appointment_scope ) {
            $where .= $wpdb->prepare( " AND ( bookingpress_customer_id IN ( SELECT bookingpress_customer_id FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = %d ) ) ", get_current_user_id() );
        }

        $start_date = $input['start_date'] ?? '';
        if( !empty( $start_date ) ) {
            $where .= $wpdb->prepare( " AND bookingpress_appointment_date >= %s", $start_date );
        }
        $end_date = $input['end_date'] ?? '';
        if( !empty( $end_date ) ) {
            $where .= $wpdb->prepare( " AND bookingpress_appointment_date <= %s", $end_date );
        }

        $status = $input['status'] ?? 0;
        if( !empty( $status ) ) {
            $where .= $wpdb->prepare( " AND bookingpress_appointment_status = %d", $status );
        }

        $service_name = $input['service_name'] ?? '';
        if( !empty( $service_name ) ) {
            $where .= $wpdb->prepare( " AND bookingpress_service_name = %s", $service_name );
        }

        $service_id = $input['service_id'] ?? 0;
        if( !empty( $service_id ) ) {
            $where .= $wpdb->prepare( " AND bookingpress_service_id = %d", $service_id );
        }

        $customer = !empty( $input['customer'] ) ? $wpdb->esc_like( $input['customer'] ) : '';
        if( !empty( $customer ) ) {
            $where .= $wpdb->prepare( " AND ( bookingpress_customer_name LIKE %s OR bookingpress_customer_firstname LIKE %s OR bookingpress_customer_lastname LIKE %s OR bookingpress_customer_email LIKE %s OR bookingpress_customer_id = %d )", "%{$customer}%", "%{$customer}%", "%{$customer}%", "%{$customer}%", intval( $customer ) );
        }

        $limit = '';
        $page = ! empty( $input['page'] ) ? absint( $input['page'] ) : 1;
        $input['per_page'] = ! empty( $input['per_page'] ) ? min( absint( $input['per_page'] ), 100 ) : 10;
        $offset = ( $page - 1 ) * $input['per_page'];
        $limit = $wpdb->prepare( " LIMIT %d OFFSET %d", $input['per_page'], $offset );

        $pagination_data = [];

        $query = "SELECT * FROM {$tbl_bookingpress_appointment_bookings} {$where} {$limit}";
        $all_appointment_details = $wpdb->get_results( $query, ARRAY_A );
        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$tbl_bookingpress_appointment_bookings} {$where}" );

        $pagination_data = [
            'total' => intval( $total_items ),
            'per_page' => intval( $input['per_page'] ),
            'current_page' => intval( $page ),
            'total_pages' => ceil( $total_items / $input['per_page'] ),
            'has_more' => ( $page * $input['per_page'] < $total_items )
        ];

        //error_log( print_r( $all_appointment_details, true ) );
        $items = [];
        if( !empty( $all_appointment_details ) ) {
            foreach( $all_appointment_details as $appointment ) {
                $items[] = [
                    'appointment_id' => $appointment['bookingpress_appointment_booking_id'],
                    'booking_id' => $appointment['bookingpress_booking_id'],
                    'customer' => ( !empty( $appointment['bookingpress_customer_name'] ) ? $appointment['bookingpress_customer_name'] : ( $appointment['bookingpress_customer_firstname'] . ' ' . $appointment['bookingpress_customer_lastname'] ) ),
                    'customer_id' => $appointment['bookingpress_customer_id'],
                    'customer_email' => $appointment['bookingpress_customer_email'],
                    'service' => $appointment['bookingpress_service_name'],
                    'bookingpress_appointment_status' => self::bookingpress_fetch_appointment_status_label_byid( $appointment['bookingpress_appointment_status'] ),
                    'appointment_date' => self::bookingpress_format_appointment_date( $appointment['bookingpress_appointment_date'] ?? '' ),
                    //'appointment_end_date' => self::bookingpress_format_appointment_date( $appointment['bookingpress_appointment_end_date'] ?? '' ),
                    'appointment_start_time' => self::bookingpress_format_appointment_time( $appointment['bookingpress_appointment_time'] ?? '' ),
                    'appointment_end_time' => self::bookingpress_format_appointment_time( $appointment['bookingpress_appointment_end_time'] ?? '' ),
                    'appointment_date_time' => self::bookingpress_format_appointment_date( $appointment['bookingpress_appointment_date'] ?? '' ) . ' ' . self::bookingpress_format_appointment_time( $appointment['bookingpress_appointment_time'] ?? '' ),
                    'appointment_duration' => self::bookingpress_format_appointment_duration( $appointment ) ?? ''
                ];
            }
        }

        return [
            'items' => $items,
            ...$pagination_data
        ];

    }

    public static function bookingpress_ability_get_appointment( $input = [] ){
        global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_form_fields, $bookingpress_appointment;

        $appointment_scope = \BookingPress\abilities\BookingPressAbilities::bookingpress_appointment_scope();

        if( empty( $appointment_scope ) || ! is_user_logged_in() ) {
            return new \WP_Error(
                'invalid_scope',
                __( 'Invalid scope to get the appointment.', 'bookingpress-appointment-booking'),
                [ 'status' => 403 ]
            );
        }

        $appointment_id = isset( $input['appointment_id'] ) ? intval( $input['appointment_id'] ) : 0;

        if( empty( $appointment_id ) ){
            return new \WP_Error(
                'invalid_appointment_id',
                __( 'Invalid appointment ID.', 'bookingpress-appointment-booking'),
                [ 'status' => 400 ]
            );
        }

        $where = $wpdb->prepare( " WHERE bookingpress_appointment_booking_id = %d", $appointment_id );

        if( 'customer' == $appointment_scope ) {
            $where .= $wpdb->prepare( " AND ( bookingpress_customer_id IN ( SELECT bookingpress_customer_id FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = %d ) ) ", get_current_user_id() );
        }

        $query = "SELECT * FROM {$tbl_bookingpress_appointment_bookings} {$where}";
        
        $appointment = $wpdb->get_row( $query, ARRAY_A );

        if( empty( $appointment ) ){
            return new \WP_Error(
                'appointment_not_found',
                __( 'Appointment not found.', 'bookingpress-appointment-booking'),
                [ 'status' => 404 ]
            );
        }

        $items = [
            'appointment_id' => $appointment['bookingpress_appointment_booking_id'],
            'booking_id' => $appointment['bookingpress_booking_id'],
            'customer' => ( !empty( $appointment['bookingpress_customer_name'] ) ? $appointment['bookingpress_customer_name'] : ( $appointment['bookingpress_customer_firstname'] . ' ' . $appointment['bookingpress_customer_lastname'] ) ),
            'customer_id' => $appointment['bookingpress_customer_id'],
            'customer_email' => $appointment['bookingpress_customer_email'],
            'service' => $appointment['bookingpress_service_name'],
            'bookingpress_appointment_status' => self::bookingpress_fetch_appointment_status_label_byid( $appointment['bookingpress_appointment_status'] ),
            'appointment_date' => self::bookingpress_format_appointment_date( $appointment['bookingpress_appointment_date'] ?? '' ),
            //'appointment_end_date' => self::bookingpress_format_appointment_date( $appointment['bookingpress_appointment_end_date'] ?? '' ),
            'appointment_start_time' => self::bookingpress_format_appointment_time( $appointment['bookingpress_appointment_time'] ?? '' ),
            'appointment_end_time' => self::bookingpress_format_appointment_time( $appointment['bookingpress_appointment_end_time'] ?? '' ),
            'appointment_date_time' => self::bookingpress_format_appointment_date( $appointment['bookingpress_appointment_date'] ?? '' ) . ' ' . self::bookingpress_format_appointment_time( $appointment['bookingpress_appointment_time'] ?? '' ),
            'appointment_duration' => self::bookingpress_format_appointment_duration( $appointment ) ?? ''
        ];
        return $items;
    }

    public static function bookingpress_verify_appointment_permission(){
        
        if( !is_user_logged_in() ){ return false; }

        $current_user_id = get_current_user_id();

        if( empty( $current_user_id ) ){ return false; }

        // Check if the current user is an administrator
        if( current_user_can( 'administrator' ) ){
            return true;
        }

        // Check if the current user has permission to retrieve appointments
        if( Permissions::user_can_do_action( 'retrieve_appointments') ){
            return true;
        }

        // Check if the current user is a BookingPress customer
        if( self::verify_customer_by_wp_id( $current_user_id ) ){
            return true;
        }
    
        return false;

    }

    /** Matches the capability that bookingpress_save_appointment_booking_func() checks before booking. */
    public static function bookingpress_verify_create_appointment_permission(){
        return is_user_logged_in() && Permissions::user_can_do_action( 'add_calendar_appointments' );
    }

    /** Matches the capability that bookingpress_change_upcoming_appointment_status() checks before updating. */
    public static function bookingpress_verify_update_appointment_status_permission(){
        return is_user_logged_in() && Permissions::user_can_do_action( 'update_upcoming_appointments' );
    }

    public static function bookingpress_appointment_scope(){

        $current_user = wp_get_current_user();

        if( current_user_can( 'administrator' ) ){
            return 'administrator';
        }

        if( Permissions::user_can_do_action( 'retrieve_appointments') ){
            return 'manager';
        }

        if( self::verify_customer_by_wp_id( get_current_user_id() ) ){
            return 'customer';
        }

        return false;

    }

}