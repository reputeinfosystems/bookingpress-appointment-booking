<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('BookingPress_Global_Options') ) {
    class BookingPress_Global_Options Extends BookingPress_Core
    {
        function __construct()
        {
        }
        
        /**
         * All global options for BookingPress
         *
         * @return void
         */
        function bookingpress_global_options()
        {   
            global $BookingPress,$wpdb,$tbl_bookingpress_settings,$tbl_bookingpress_customize_settings;
            
            $bookingpress_site_current_language = $this->bookingpress_get_site_current_language();
            
            if ($bookingpress_site_current_language == 'ru' ) {
                $bookingpress_site_current_language = 'ruRU';
            } elseif ($bookingpress_site_current_language == 'zh-cn' ) {
                $bookingpress_site_current_language = 'zhCN';
            } elseif ($bookingpress_site_current_language == 'pt-br' ) {
                $bookingpress_site_current_language = 'ptBr';
            } elseif ($bookingpress_site_current_language == 'sv' ) {
                $bookingpress_site_current_language = 'se';
            } else if( 'tr' == $bookingpress_site_current_language ){
                $bookingpress_site_current_language = 'trTR';
            } else if( 'nl-be' == $bookingpress_site_current_language ){
                $bookingpress_site_current_language = 'nlBE';
            }elseif ($bookingpress_site_current_language == 'pt' ) {
                $bookingpress_site_current_language = 'ptPT';
            }elseif ($bookingpress_site_current_language == 'et' ) {
                $bookingpress_site_current_language = 'et';
            }elseif ($bookingpress_site_current_language == 'nb_NO' ) {
                $bookingpress_site_current_language = 'no';
            }elseif ($bookingpress_site_current_language == 'lv' ) {
                $bookingpress_site_current_language = 'lv';
            }elseif ($bookingpress_site_current_language == 'az' ) {
                $bookingpress_site_current_language = 'az';
            }elseif ($bookingpress_site_current_language == 'fi' ) {
                $bookingpress_site_current_language = 'fi';
            }elseif ($bookingpress_site_current_language == 'gl_ES' ) {
                $bookingpress_site_current_language = 'gl';
            }elseif ($bookingpress_site_current_language == 'he_IL' ) {
                $bookingpress_site_current_language = 'he';
            }elseif ($bookingpress_site_current_language == 'fr_CA' ) {
                $bookingpress_site_current_language = 'fr_CA';
            }
            

            global $bookingpress_settings_table_exists, $bookingpress_customize_settings_table_exists;

            if( 1 != $bookingpress_settings_table_exists ){
                $check_table = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema=%s AND table_name=%s",DB_NAME,$tbl_bookingpress_settings));
                if( 1 == $check_table ){
                    $bookingpress_settings_table_exists = 1;
                }
            }

            if( 1 != $bookingpress_customize_settings_table_exists ){
                $check_customize_table = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema=%s AND table_name=%s", DB_NAME, $tbl_bookingpress_customize_settings) );
                if( 1 == $check_customize_table ){
                    $bookingpress_customize_settings_table_exists = 1;
                }
            }

            $default_date_format = 'F j, Y';
            $default_time_format = 'g:i a';
            $bpa_converted_time_format = 'hh:mm';
            
            $bpa_general_settings_data = array();
            if($bookingpress_settings_table_exists == 1) {

                // Get all the general settings
                $bpa_cached_general_settings = wp_cache_get( 'bookingpress_all_general_settings' );
                if( false === $bpa_cached_general_settings ){
                    $bookingpress_all_general_settings = $wpdb->get_results( "SELECT setting_name,setting_value,setting_type FROM {$tbl_bookingpress_settings} ORDER BY setting_type ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.
                    wp_cache_set( 'bookingpress_all_general_settings', $bookingpress_all_general_settings ); 
                } else {
                    $bookingpress_all_general_settings = $bpa_cached_general_settings;
                }

                if( !empty( $bookingpress_all_general_settings ) ){
                    foreach( $bookingpress_all_general_settings as $cs_key => $cs_value ){
                        $general_type = $cs_value->setting_type;
                        if( empty( $bpa_general_settings_data[ $general_type ] ) ){
                            $bpa_general_settings_data[ $general_type ] = array();
                        }

                        $bpa_general_settings_data[ $general_type ][ $cs_value->setting_name ] = $cs_value->setting_value;
                    }
                }

                $default_date_format = $bpa_general_settings_data['general_setting']['default_date_format']; 
                $default_date_format = !empty($default_date_format) ? $default_date_format : 'F j, Y';
  			
                $default_time_format = $bpa_general_settings_data['general_setting']['default_time_format'];

                /** @Deprecated condition and Should be removed in or after version 1.0.56 */
                if( 'bookingpress-wp-inherit-time-format' == $default_time_format && $BookingPress->bpa_is_pro_active() && version_compare( $BookingPress->bpa_pro_plugin_version(), '2.0', '<') ){
                    $default_time_format = '';
                }
                
                $default_time_format = !empty($default_time_format) ? $default_time_format : 'g:i a';
                

                if ($default_time_format == 'g:i a' ) {
                    $bpa_converted_time_format = 'hh:mm a';
                } elseif ($default_time_format == 'H:i') {
                    $bpa_converted_time_format = 'HH:mm';
                } elseif ($default_time_format == 'bookingpress-wp-inherit-time-format'){
                    $default_time_format = get_option('time_format'); 
                }
            }

            $global_data = array(
                'name'                         => 'Appointment Booking',
                'debug'                        => true,
                'locale'                       => $bookingpress_site_current_language,
                'wp_default_date_format'       => $default_date_format,
                'wp_default_time_format'       => $default_time_format,
                'bpa_time_format_for_timeslot' => $bpa_converted_time_format,
                'start_of_week'                => get_option('start_of_week'),
                'pagination'                   => wp_json_encode(array( 10, 20, 50, 100, 200, 300, 400, 500 )),
                'customer_placeholders'        => wp_json_encode(
                    array(
                        array(
                            'value' => '%customer_email%',
                            'name'  => '%customer_email%',
                        ),
                        array(
                            'value' => '%customer_first_name%',
                            'name'  => '%customer_first_name%',
                        ),
                        array(
                            'value' => '%customer_full_name%',
                            'name'  => '%customer_full_name%',
                        ),
                        array(
                            'value' => '%customer_last_name%',
                            'name'  => '%customer_last_name%',
                        ),
                        array(
                            'value' => '%customer_note%',
                            'name'  => '%customer_note%',
                        ),
                        array(
                            'value' => '%customer_phone%',
                            'name'  => '%customer_phone%',
                        ),
                        array(
                            'value' => '%customer_cancel_appointment_link%',
                            'name'  => '%customer_cancel_appointment_link%',
                        ),
                    )
                ),
                'service_placeholders'         => wp_json_encode(
                    array(
                        array(
                            'value' => '%service_name%',
                            'name'  => '%service_name%',
                        ),
                        array(
                            'value' => '%service_amount%',
                            'name'  => '%service_amount%',
                        ),
                        array(
                            'value' => '%service_duration%',
                            'name'  => '%service_duration%',
                        ),
                        array(
                            'value' => '%service_description%',
                            'name'  => '%service_description%',
                        ),
                        array(
                            'value' => '%category_name%',
                            'name'  => '%category_name%',
                        ),
                    )
                ),
                'company_placeholders'         => wp_json_encode(
                    array(
                        array(
                            'value' => '%company_address%',
                            'name'  => '%company_address%',
                        ),
                        array(
                            'value' => '%company_name%',
                            'name'  => '%company_name%',
                        ),
                        array(
                            'value' => '%company_phone%',
                            'name'  => '%company_phone%',
                        ),
                        array(
                            'value' => '%company_website%',
                            'name'  => '%company_website%',
                        ),
                    )
                ),
                'appointment_placeholders'     => wp_json_encode(
                    array(
                        array(
                            'value' => '%appointment_date%',
                            'name'  => '%appointment_date%',
                        ),
                        array(
                            'value' => '%appointment_time%',
                            'name'  => '%appointment_time%',
                        ),
                        array(
                            'value' => '%booking_id%',
                            'name'  => '%booking_id%',
                        ),
                        array(
                            'value' => '%payment_method%',
                            'name'  => '%payment_method%',
                        ),
                        array(
                            'value' => '%share_appointment_url%',
                            'name'  => '%share_appointment_url%',
                        ),
                    )
                ),
                'country_lists'                => '[{"code":"ad","name":"Andorra"},{"code":"ae","name":"United Arab Emirates"},{"code":"af","name":"Afghanistan"},{"code":"ag","name":"Antigua & Barbuda"},{"code":"ai","name":"Anguilla"},{"code":"al","name":"Albania"},{"code":"am","name":"Armenia"},{"code":"ao","name":"Angola"},{"code":"aq","name":"Antarctica"},{"code":"ar","name":"Argentina"},{"code":"as","name":"American Samoa"},{"code":"at","name":"Austria"},{"code":"au","name":"Australia"},{"code":"aw","name":"Aruba"},{"code":"ax","name":"Aland Islands"},{"code":"az","name":"Azerbaijan"},{"code":"ba","name":"Bosnia & Herzegovina"},{"code":"bb","name":"Barbados"},{"code":"bd","name":"Bangladesh"},{"code":"be","name":"Belgium"},{"code":"bf","name":"Burkina Faso"},{"code":"bg","name":"Bulgaria"},{"code":"bh","name":"Bahrain"},{"code":"bi","name":"Burundi"},{"code":"bj","name":"Benin"},{"code":"bm","name":"Bermuda"},{"code":"bn","name":"Brunei"},{"code":"bo","name":"Bolivia"},{"code":"br","name":"Brazil"},{"code":"bs","name":"Bahamas"},{"code":"bt","name":"Bhutan"},{"code":"bv","name":"Bouvet Island"},{"code":"bw","name":"Botswana"},{"code":"by","name":"Belarus"},{"code":"bz","name":"Belize"},{"code":"ca","name":"Canada"},{"code":"cc","name":"Cocos (Keeling) Islands"},{"code":"cd","name":"Congo - Kinshasa"},{"code":"cf","name":"Central African Republic"},{"code":"cg","name":"Congo - Brazzaville"},{"code":"ch","name":"Switzerland"},{"code":"ci","name":"Cote D\'Ivoire (Ivory Coast)"},{"code":"ck","name":"Cook Islands"},{"code":"cl","name":"Chile"},{"code":"cm","name":"Cameroon"},{"code":"cn","name":"China"},{"code":"co","name":"Colombia"},{"code":"cr","name":"Costa Rica"},{"code":"cu","name":"Cuba"},{"code":"cv","name":"Cape Verde"},{"code":"cx","name":"Christmas Island"},{"code":"cy","name":"Cyprus"},{"code":"cz","name":"Czechia"},{"code":"de","name":"Germany"},{"code":"dj","name":"Djibouti"},{"code":"dk","name":"Denmark"},{"code":"dm","name":"Dominica"},{"code":"do","name":"Dominican Republic"},{"code":"dz","name":"Algeria"},{"code":"ec","name":"Ecuador"},{"code":"ee","name":"Estonia"},{"code":"eg","name":"Egypt"},{"code":"eh","name":"Western Sahara"},{"code":"er","name":"Eritrea"},{"code":"es","name":"Spain"},{"code":"et","name":"Ethiopia"},{"code":"fi","name":"Finland"},{"code":"fj","name":"Fiji"},{"code":"fk","name":"Falkland Islands"},{"code":"fm","name":"Micronesia"},{"code":"fo","name":"Faroe Islands"},{"code":"fr","name":"France"},{"code":"ga","name":"Gabon"},{"code":"gb","name":"United Kingdom"},{"code":"gd","name":"Grenada"},{"code":"ge","name":"Georgia"},{"code":"gf","name":"French Guiana"},{"code":"gh","name":"Ghana"},{"code":"gi","name":"Gibraltar"},{"code":"gl","name":"Greenland"},{"code":"gm","name":"Gambia"},{"code":"gn","name":"Guinea"},{"code":"gp","name":"Guadeloupe"},{"code":"gq","name":"Equatorial Guinea"},{"code":"gr","name":"Greece"},{"code":"gs","name":"South Georgia & South Sandwich Islands"},{"code":"gt","name":"Guatemala"},{"code":"gu","name":"Guam"},{"code":"gw","name":"Guinea-Bissau"},{"code":"gy","name":"Guyana"},{"code":"hk","name":"Hong Kong"},{"code":"hm","name":"Heard & McDonald Islands"},{"code":"hn","name":"Honduras"},{"code":"hr","name":"Croatia"},{"code":"ht","name":"Haiti"},{"code":"hu","name":"Hungary"},{"code":"id","name":"Indonesia"},{"code":"ie","name":"Ireland"},{"code":"il","name":"Israel"},{"code":"in","name":"India"},{"code":"io","name":"British Indian Ocean Territory"},{"code":"iq","name":"Iraq"},{"code":"ir","name":"Iran"},{"code":"is","name":"Iceland"},{"code":"it","name":"Italy"},{"code":"jm","name":"Jamaica"},{"code":"jo","name":"Jordan"},{"code":"jp","name":"Japan"},{"code":"ke","name":"Kenya"},{"code":"kg","name":"Kyrgyzstan"},{"code":"kh","name":"Cambodia"},{"code":"ki","name":"Kiribati"},{"code":"km","name":"Comoros"},{"code":"kn","name":"St. Kitts & Nevis"},{"code":"kp","name":"North Korea"},{"code":"kr","name":"South Korea"},{"code":"kw","name":"Kuwait"},{"code":"ky","name":"Cayman Islands"},{"code":"kz","name":"Kazakhstan"},{"code":"la","name":"Laos"},{"code":"lb","name":"Lebanon"},{"code":"lc","name":"St. Lucia"},{"code":"li","name":"Liechtenstein"},{"code":"lk","name":"Sri Lanka"},{"code":"lr","name":"Liberia"},{"code":"ls","name":"Lesotho"},{"code":"lt","name":"Lithuania"},{"code":"lu","name":"Luxembourg"},{"code":"lv","name":"Latvia"},{"code":"ly","name":"Libya"},{"code":"ma","name":"Morocco"},{"code":"mc","name":"Monaco"},{"code":"md","name":"Moldova"},{"code":"me","name":"Montenegro"},{"code":"mg","name":"Madagascar"},{"code":"mh","name":"Marshall Islands"},{"code":"mk","name":"Macedonia"},{"code":"ml","name":"Mali"},{"code":"mm","name":"Myanmar (Burma)"},{"code":"mn","name":"Mongolia"},{"code":"mn","name":"Mongolian Tugrik"},{"code":"mo","name":"Macau"},{"code":"mp","name":"Northern Mariana Islands"},{"code":"mq","name":"Martinique"},{"code":"mr","name":"Mauritania"},{"code":"ms","name":"Montserrat"},{"code":"mt","name":"Malta"},{"code":"mu","name":"Mauritius"},{"code":"mv","name":"Maldives"},{"code":"mw","name":"Malawi"},{"code":"mx","name":"Mexico"},{"code":"my","name":"Malaysia"},{"code":"mz","name":"Mozambique"},{"code":"na","name":"Namibia"},{"code":"nc","name":"New Caledonia"},{"code":"ne","name":"Niger"},{"code":"nf","name":"Norfolk Island"},{"code":"ng","name":"Nigeria"},{"code":"ni","name":"Nicaragua"},{"code":"nl","name":"Netherlands"},{"code":"no","name":"Norway"},{"code":"np","name":"Nepal"},{"code":"nr","name":"Nauru"},{"code":"nu","name":"Niue"},{"code":"nz","name":"New Zealand"},{"code":"om","name":"Oman"},{"code":"pa","name":"Panama"},{"code":"pe","name":"Peru"},{"code":"pf","name":"French Polynesia"},{"code":"pg","name":"Papua New Guinea"},{"code":"ph","name":"Philippines"},{"code":"pk","name":"Pakistan"},{"code":"pl","name":"Poland"},{"code":"pm","name":"St. Pierre & Miquelon"},{"code":"pn","name":"Pitcairn"},{"code":"pr","name":"Puerto Rico"},{"code":"ps","name":"Palestinian Territories"},{"code":"pt","name":"Portugal"},{"code":"pw","name":"Palau"},{"code":"py","name":"Paraguay"},{"code":"qa","name":"Qatar"},{"code":"re","name":"Reunion"},{"code":"ro","name":"Romania"},{"code":"ru","name":"Russia"},{"code":"rw","name":"Rwanda"},{"code":"rs","name":"Serbia"},{"code":"sa","name":"Saudi Arabia"},{"code":"sb","name":"Solomon Islands"},{"code":"sc","name":"Seychelles"},{"code":"sd","name":"Sudan"},{"code":"se","name":"Sweden"},{"code":"sg","name":"Singapore"},{"code":"sh","name":"St. Helena"},{"code":"si","name":"Slovenia"},{"code":"sj","name":"Svalbard & Jan Mayen"},{"code":"sk","name":"Slovakia"},{"code":"sl","name":"Sierra Leone"},{"code":"sm","name":"San Marino"},{"code":"sn","name":"Senegal"},{"code":"so","name":"Somalia"},{"code":"sr","name":"Suriname"},{"code":"ss","name":"South Sudan"},{"code":"st","name":"Sao Tome and Principe"},{"code":"sv","name":"El Salvador"},{"code":"sy","name":"Syria"},{"code":"sz","name":"Swaziland"},{"code":"tc","name":"Turks & Caicos Islands"},{"code":"td","name":"Chad"},{"code":"tf","name":"French Southern Territories"},{"code":"tg","name":"Togo"},{"code":"th","name":"Thailand"},{"code":"tj","name":"Tajikistan"},{"code":"tk","name":"Tokelau"},{"code":"tl","name":"Timor-Leste"},{"code":"tm","name":"Turkmenistan"},{"code":"tn","name":"Tunisia"},{"code":"to","name":"Tonga"},{"code":"tr","name":"Turkey"},{"code":"tt","name":"Trinidad & Tobago"},{"code":"tv","name":"Tuvalu"},{"code":"tw","name":"Taiwan"},{"code":"tz","name":"Tanzania"},{"code":"ua","name":"Ukraine"},{"code":"ug","name":"Uganda"},{"code":"um","name":"U.S. Outlying Islands"},{"code":"us","name":"United States"},{"code":"uy","name":"Uruguay"},{"code":"uz","name":"Uzbekistan"},{"code":"va","name":"Vatican City"},{"code":"vc","name":"St. Vincent & Grenadines"},{"code":"ve","name":"Venezuela"},{"code":"vg","name":"British Virgin Islands"},{"code":"vi","name":"U.S. Virgin Islands"},{"code":"vn","name":"Vietnam"},{"code":"vu","name":"Vanuatu"},{"code":"wf","name":"Wallis & Futuna"},{"code":"ws","name":"Samoa"},{"code":"ye","name":"Yemen"},{"code":"yt","name":"Mayotte"},{"code":"za","name":"South Africa"},{"code":"zm","name":"Zambia"},{"code":"zw","name":"Zimbabwe"}]',
                'countries_json_details'       => '[{"symbol":"$","name":"US Dollar","symbol_native":"$","code":"USD","iso":"us"},{"symbol":"€","name":"Euro","symbol_native":"€","code":"EUR","iso":"eu"},{"symbol":"£","name":"British Pound Sterling","symbol_native":"£","code":"GBP","iso":"gb"},{"symbol":"$","name":"Canadian Dollar","symbol_native":"$","code":"CAD","iso":"ca"},{"symbol":"XPF","name":"CFP Franc","symbol_native":"FCFP","code":"XPF","iso":"fr"},{"symbol":"CHF","name":"Swiss Franc","symbol_native":"CHF","code":"CHF","iso":"ch"},{"symbol":"₽","name":"Russian Ruble","symbol_native":"руб.","code":"RUB","iso":"ru"},{"symbol":"¥","name":"Japanese Yen","symbol_native":"￥","code":"JPY","iso":"jp"},{"symbol":"؋","name":"Afghan Afghani","symbol_native":"؋","code":"AFN","iso":"af"},{"symbol":"L","name":"Albanian Lek","symbol_native":"Lek","code":"ALL","iso":"al"},{"symbol":"د.ج","name":"Algerian Dinar","symbol_native":"د.ج.","code":"DZD","iso":"dz"},{"symbol":"$","name":"Argentine Peso","symbol_native":"$","code":"ARS","iso":"ar"},{"symbol":"AMD","name":"Armenian Dram","symbol_native":"դր.","code":"AMD","iso":"am"},{"symbol":"$","name":"Australian Dollar","symbol_native":"$","code":"AUD","iso":"au"},{"symbol":"AZN","name":"Azerbaijani Manat","symbol_native":"ман.","code":"AZN","iso":"az"},{"symbol":".د.ب","name":"Bahraini Dinar","symbol_native":"د.ب.","code":"BHD","iso":"bh"},{"symbol":"৳","name":"Bangladeshi Taka","symbol_native":"৳","code":"BDT","iso":"bd"},{"symbol":"Br","name":"Belarusian Ruble","symbol_native":"BYR","code":"BYR","iso":"by"},{"symbol":"$","name":"Belize Dollar","symbol_native":"$","code":"BZD","iso":"bz"},{"symbol":"Bs.","name":"Bolivian Boliviano","symbol_native":"Bs","code":"BOB","iso":"bo"},{"symbol":"KM","name":"Bosnia-Herzegovina Convertible Mark","symbol_native":"KM","code":"BAM","iso":"ba"},{"symbol":"P","name":"Botswanan Pula","symbol_native":"P","code":"BWP","iso":"bw"},{"symbol":"R$","name":"Brazilian Real","symbol_native":"R$","code":"BRL","iso":"br"},{"symbol":"$","name":"Brunei Dollar","symbol_native":"$","code":"BND","iso":"bn"},{"symbol":"лв.","name":"Bulgarian Lev","symbol_native":"лв.","code":"BGN","iso":"bg"},{"symbol":"Fr","name":"Burundian Franc","symbol_native":"FBu","code":"BIF","iso":"bi"},{"symbol":"៛","name":"Cambodian Riel","symbol_native":"៛","code":"KHR","iso":"kh"},{"symbol":"$","name":"Cape Verdean Escudo","symbol_native":"CV$","code":"CVE","iso":"cv"},{"symbol":"$","name":"Chilean Peso","symbol_native":"$","code":"CLP","iso":"cl"},{"symbol":"¥","name":"Chinese Yuan","symbol_native":"CN¥","code":"CNY","iso":"cn"},{"symbol":"$","name":"Colombian Peso","symbol_native":"$","code":"COP","iso":"co"},{"symbol":"Fr","name":"Comorian Franc","symbol_native":"FC","code":"KMF","iso":"km"},{"symbol":"Fr","name":"Congolese Franc","symbol_native":"FrCD","code":"CDF","iso":"cd"},{"symbol":"₡","name":"Costa Rican Colón","symbol_native":"₡","code":"CRC","iso":"cr"},{"symbol":"kn","name":"Croatian Kuna","symbol_native":"kn","code":"HRK","iso":"hr"},{"symbol":"Kč","name":"Czech Republic Koruna","symbol_native":"Kč","code":"CZK","iso":"cz"},{"symbol":"FCFA","name":"Central African Franc","symbol_native":"FCFA","code":"XAF","iso":"FCFA"},{"symbol":"Dkk","name":"Danish Krone","symbol_native":"kr","code":"DKK","iso":"dk"},{"symbol":"Fr","name":"Djiboutian Franc","symbol_native":"Fdj","code":"DJF","iso":"dj"},{"symbol":"RD$","name":"Dominican Peso","symbol_native":"RD$","code":"DOP","iso":"do"},{"symbol":"EGP","name":"Egyptian Pound","symbol_native":"ج.م.","code":"EGP","iso":"eg"},{"symbol":"Nfk","name":"Eritrean Nakfa","symbol_native":"Nfk","code":"ERN","iso":"er"},{"symbol":"Ekr","name":"Estonian Kroon","symbol_native":"kr","code":"EEK","iso":"ee"},{"symbol":"Br","name":"Ethiopian Birr","symbol_native":"Br","code":"ETB","iso":"et"},{"symbol":"$","name":"East Caribbean Dollar","symbol_native":"$","code":"XCD","iso":"ag"},{"symbol":"₾","name":"Georgian Lari","symbol_native":"GEL","code":"GEL","iso":"ge"},{"symbol":"₵","name":"Ghanaian Cedi","symbol_native":"GH₵","code":"GHS","iso":"gh"},{"symbol":"Q","name":"Guatemalan Quetzal","symbol_native":"Q","code":"GTQ","iso":"gt"},{"symbol":"Fr","name":"Guinean Franc","symbol_native":"FG","code":"GNF","iso":"gn"},{"symbol":"L","name":"Honduran Lempira","symbol_native":"L","code":"HNL","iso":"hn"},{"symbol":"$","name":"Hong Kong Dollar","symbol_native":"$","code":"HKD","iso":"hk"},{"symbol":"Ft","name":"Hungarian Forint","symbol_native":"Ft","code":"HUF","iso":"hu"},{"symbol":"kr.","name":"Icelandic Króna","symbol_native":"kr","code":"ISK","iso":"is"},{"symbol":"₹","name":"Indian Rupee","symbol_native":"টকা","code":"INR","iso":"in"},{"symbol":"Rp","name":"Indonesian Rupiah","symbol_native":"Rp","code":"IDR","iso":"id"},{"symbol":"﷼","name":"Iranian Rial","symbol_native":"﷼","code":"IRR","iso":"ir"},{"symbol":"د.ع","name":"Iraqi Dinar","symbol_native":"د.ع.","code":"IQD","iso":"iq"},{"symbol":"₪","name":"Israeli New Sheqel","symbol_native":"₪","code":"ILS","iso":"il"},{"symbol":"$","name":"Jamaican Dollar","symbol_native":"$","code":"JMD","iso":"jm"},{"symbol":"د.ا","name":"Jordanian Dinar","symbol_native":"د.أ.","code":"JOD","iso":"jo"},{"symbol":"₸","name":"Kazakhstani Tenge","symbol_native":"тңг.","code":"KZT","iso":"kz"},{"symbol":"KSh","name":"Kenyan Shilling","symbol_native":"Ksh","code":"KES","iso":"ke"},{"symbol":"د.ك","name":"Kuwaiti Dinar","symbol_native":"د.ك.","code":"KWD","iso":"kw"},{"symbol":"Ls","name":"Latvian Lats","symbol_native":"Ls","code":"LVL","iso":"lv"},{"symbol":"ل.ل","name":"Lebanese Pound","symbol_native":"ل.ل.","code":"LBP","iso":"lb"},{"symbol":"ل.د","name":"Libyan Dinar","symbol_native":"د.ل.","code":"LYD","iso":"ly"},{"symbol":"Lt","name":"Lithuanian Litas","symbol_native":"Lt","code":"LTL","iso":"lt"},{"symbol":"P","name":"Macanese Pataca","symbol_native":"MOP$","code":"MOP","iso":"mo"},{"symbol":"ден","name":"Macedonian Denar","symbol_native":"MKD","code":"MKD","iso":"mk"},{"symbol":"₮","name":"Mongolian Tugrik","symbol_native":"MNT","code":"MNT","iso":"mn"},{"symbol":"Ar","name":"Malagasy Ariary","symbol_native":"MGA","code":"MGA","iso":"mg"},{"symbol":"RM","name":"Malaysian Ringgit","symbol_native":"RM","code":"MYR","iso":"my"},{"symbol":"₨","name":"Mauritian Rupee","symbol_native":"MURs","code":"MUR","iso":"mu"},{"symbol":"$","name":"Mexican Peso","symbol_native":"$","code":"MXN","iso":"mx"},{"symbol":"MDL","name":"Moldovan Leu","symbol_native":"MDL","code":"MDL","iso":"md"},{"symbol":"د.م.","name":"Moroccan Dirham","symbol_native":"د.م.","code":"MAD","iso":"ma"},{"symbol":"MT","name":"Mozambican Metical","symbol_native":"MTn","code":"MZN","iso":"mz"},{"symbol":"Ks","name":"Myanma Kyat","symbol_native":"K","code":"MMK","iso":"mm"},{"symbol":"N$","name":"Namibian Dollar","symbol_native":"N$","code":"NAD","iso":"na"},{"symbol":"₨","name":"Nepalese Rupee","symbol_native":"नेरू","code":"NPR","iso":"np"},{"symbol":"NT$","name":"New Taiwan Dollar","symbol_native":"NT$","code":"TWD","iso":"tw"},{"symbol":"$","name":"New Zealand Dollar","symbol_native":"$","code":"NZD","iso":"nz"},{"symbol":"C$","name":"Nicaraguan Córdoba","symbol_native":"C$","code":"NIO","iso":"ni"},{"symbol":"₦","name":"Nigerian Naira","symbol_native":"₦","code":"NGN","iso":"ng"},{"symbol":"kr","name":"Norwegian Krone","symbol_native":"kr","code":"NOK","iso":"no"},{"symbol":"ر.ع.","name":"Omani Rial","symbol_native":"ر.ع.","code":"OMR","iso":"om"},{"symbol":"₨","name":"Pakistani Rupee","symbol_native":"₨","code":"PKR","iso":"pk"},{"symbol":"B\/.","name":"Panamanian Balboa","symbol_native":"B\/.","code":"PAB","iso":"pa"},{"symbol":"₲","name":"Paraguayan Guarani","symbol_native":"₲","code":"PYG","iso":"py"},{"symbol":"S\/","name":"Peruvian Nuevo Sol","symbol_native":"S\/.","code":"PEN","iso":"pe"},{"symbol":"₱","name":"Philippine Peso","symbol_native":"₱","code":"PHP","iso":"ph"},{"symbol":"zł","name":"Polish Zloty","symbol_native":"zł","code":"PLN","iso":"pl"},{"symbol":"ر.ق","name":"Qatari Rial","symbol_native":"ر.ق.","code":"QAR","iso":"qa"},{"symbol":"lei","name":"Romanian Leu","symbol_native":"RON","code":"RON","iso":"ro"},{"symbol":"Fr","name":"Rwandan Franc","symbol_native":"FR","code":"RWF","iso":"rw"},{"symbol":"⃁","name":"Saudi Riyal","symbol_native":"⃁","code":"SAR","iso":"sa"},{"symbol":"рсд","name":"Serbian Dinar","symbol_native":"дин.","code":"RSD","iso":"rs"},{"symbol":"$","name":"Singapore Dollar","symbol_native":"$","code":"SGD","iso":"sg"},{"symbol":"Sh","name":"Somali Shilling","symbol_native":"Ssh","code":"SOS","iso":"so"},{"symbol":"R","name":"South African Rand","symbol_native":"R","code":"ZAR","iso":"za"},{"symbol":"₩","name":"South Korean Won","symbol_native":"₩","code":"KRW","iso":"kr"},{"symbol":"₭","name":"Lao kip","symbol_native":"₭","code":"LAK","iso":"la"},{"symbol":"රු","name":"Sri Lankan Rupee","symbol_native":"SL Re","code":"LKR","iso":"lk"},{"symbol":"ج.س.","name":"Sudanese Pound","symbol_native":"SDG","code":"SDG","iso":"sd"},{"symbol":"kr","name":"Swedish Krona","symbol_native":"kr","code":"SEK","iso":"se"},{"symbol":"ل.س","name":"Syrian Pound","symbol_native":"ل.س.","code":"SYP","iso":"sy"},{"symbol":"Rs","name":"Seychellois Rupee","symbol_native":"SR","code":"SCR","iso":"SCR"},{"symbol":"Sh","name":"Tanzanian Shilling","symbol_native":"TSh","code":"TZS","iso":"tz"},{"symbol":"฿","name":"Thai Baht","symbol_native":"฿","code":"THB","iso":"th"},{"symbol":"T$","name":"Tongan Paʻanga","symbol_native":"T$","code":"TOP","iso":"to"},{"symbol":"$","name":"Trinidad and Tobago Dollar","symbol_native":"$","code":"TTD","iso":"tt"},{"symbol":"د.ت","name":"Tunisian Dinar","symbol_native":"د.ت.","code":"TND","iso":"tn"},{"symbol":"₺","name":"Turkish Lira","symbol_native":"TL","code":"TRY","iso":"tr"},{"symbol":"UGX","name":"Ugandan Shilling","symbol_native":"USh","code":"UGX","iso":"ug"},{"symbol":"₴","name":"Ukrainian Hryvnia","symbol_native":"₴","code":"UAH","iso":"ua"},{"symbol":"د.إ","name":"United Arab Emirates Dirham","symbol_native":"د.إ.","code":"AED","iso":"ae"},{"symbol":"$","name":"Uruguayan Peso","symbol_native":"$","code":"UYU","iso":"uy"},{"symbol":"UZS","name":"Uzbekistan Som","symbol_native":"UZS","code":"UZS","iso":"uz"},{"symbol":"Bs.S.","name":"Venezuelan Bolívar","symbol_native":"Bs.S.","code":"VES","iso":"ve"},{"symbol":"₫","name":"Vietnamese Dong","symbol_native":"₫","code":"VND","iso":"vn"},{"symbol":"﷼","name":"Yemeni Rial","symbol_native":"ر.ي.","code":"YER","iso":"ye"},{"symbol":"ZK","name":"Zambian Kwacha","symbol_native":"ZK","code":"ZMK","iso":"zm"},{"symbol":"$","name":"Cayman Islands dollar","symbol_native":"$","code":"KYD","iso":"ky"},{"symbol":"K","name":"Papua New Guinean Kina","symbol_native":"K","code":"PGK","iso":"pg"},{"symbol": "CFA","name": "West African CFA Franc","symbol_native": "CFA","code": "XOF","iso": "gh"}]',
                'timepicker_options'           => wp_json_encode(
                    array(
                        'start' => '00:00',
                        'step'  => '00:15',
                        'end'   => '24:00',
                    )
                ),
                'work_hours_days'              => wp_json_encode(
                    array(
                        array(
                            'day_key'           => 'monday',
                            'day_name'          => 'Monday',
                            'day_services_data' => array(),
                        ),
                        array(
                            'day_key'           => 'tuesday',
                            'day_name'          => 'Tuesday',
                            'day_services_data' => array(),
                        ),
                        array(
                            'day_key'           => 'wednesday',
                            'day_name'          => 'Wednesday',
                            'day_services_data' => array(),
                        ),
                        array(
                            'day_key'           => 'thursday',
                            'day_name'          => 'Thursday',
                            'day_services_data' => array(),
                        ),
                        array(
                            'day_key'           => 'friday',
                            'day_name'          => 'Friday',
                            'day_services_data' => array(),
                        ),
                        array(
                            'day_key'           => 'saturday',
                            'day_name'          => 'Saturday',
                            'day_services_data' => array(),
                        ),
                        array(
                            'day_key'           => 'sunday',
                            'day_name'          => 'Sunday',
                            'day_services_data' => array(),
                        ),
                    )
                ),
                'allowed_html' => wp_json_encode(
                    array(
                        'a' => array_merge(
                            $this->bookingpress_global_attributes(),
                            array(
                                'href' => array(),
                                'rel' => array(),
                                'target' => array(),
                            )
                        ),
                        'b' => $this->bookingpress_global_attributes(),
                        'br' => $this->bookingpress_global_attributes(),
                        'center' => array(),
                        'dd' => $this->bookingpress_global_attributes(),
                        'dl' => $this->bookingpress_global_attributes(),
                        'dt' => $this->bookingpress_global_attributes(),
                        'div' => $this->bookingpress_global_attributes(),
                        'font' => array_merge(
                            $this->bookingpress_global_attributes(),
                            array(
                                'color' => array(),
                                'face' => array(),
                                'size' => array()
                            )
                        ),
                        'h1' => $this->bookingpress_global_attributes(),
                        'h2' => $this->bookingpress_global_attributes(),
                        'h3' => $this->bookingpress_global_attributes(),
                        'h4' => $this->bookingpress_global_attributes(),
                        'h5' => $this->bookingpress_global_attributes(),
                        'h6' => $this->bookingpress_global_attributes(),
                        'hr' => $this->bookingpress_global_attributes(),
                        'i' => $this->bookingpress_global_attributes(),
                        'img' => array_merge(
                            $this->bookingpress_global_attributes(),
                            array(
                                'alt' => array(),
                                'height' => array(),
                                'src' => array(),
                                'width' => array()
                            )
                        ),
                        'label' => array_merge(
                            $this->bookingpress_global_attributes(),
                            array(
                                'for' => array(),
                            )
                        ),
                        'line' => array(                        
                            'x1'       => array(),
                            'y1'       => array(),
                            'x2' => array(),
                            'y2' => array(),
                            'stroke' => array(),
                            'stroke-width' => array(),
                            'stroke-linecap' => array(),
                        ),
                        'li' => $this->bookingpress_global_attributes(),
                        'ol' => $this->bookingpress_global_attributes(),
                        'optgroup' => $this->bookingpress_global_attributes(),
                        'p' => $this->bookingpress_global_attributes(),
                        'span' => $this->bookingpress_global_attributes(),
                        'strong' => $this->bookingpress_global_attributes(),
                        'sub' => $this->bookingpress_global_attributes(),
                        'sup' => $this->bookingpress_global_attributes(),
                        'svg'        => array(
                            'id'      => array(),
                            'height'  => array(),
                            'width'   => array(),
                            'x'       => array(),
                            'y'       => array(),
                            'xmlns' => array(),
                            'class' => array(),
                            'fill' => array(),
                            'viewBox' => array(),
                        ),  
                        'path'       => array(
                            'id'        => array(),
                            'd'         => array(),
                            'fill'      => array(),
                            'fill-rule' => array(),
                            'clip-rule' => array(),
                            'class'     => array(),
                            'stroke'    => array(),
                            'stroke-width'   => array(),
                            'stroke-opacity' => array(),
                            'stroke-linecap' => array(),
                        ),   
                        'table' => $this->bookingpress_global_attributes(),
                        'tbody' => $this->bookingpress_global_attributes(),
                        'thead' => $this->bookingpress_global_attributes(),
                        'tfooter' => $this->bookingpress_global_attributes(),
                        'th' => array_merge(
                            $this->bookingpress_global_attributes(),
                            array(
                                'colspan' => array(),
                                'headers' => array(),
                                'rowspan' => array(),
                                'scope' => array()
                            )
                        ),
                        'td' => array_merge(
                            $this->bookingpress_global_attributes(),
                            array(
                                'colspan' => array(),
                                'headers' => array(),
                                'rowspan' => array()
                            )
                        ),
                        'tr' => $this->bookingpress_global_attributes(),
                        'u' => $this->bookingpress_global_attributes(),
                        'ul' => $this->bookingpress_global_attributes(),
                    )
                ),
                'allowed_basic_html_tag' => wp_json_encode(array(
                    'span'  => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                        'title' => array(),
                    ),   
                    'div'  => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                        'title' => array(),
                    ),
                    'label' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                        'for'   => array(),
                     ),
                    'ul' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                     ),
                    'ol'  => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'li'  => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'strong' => array(),
                        'style'=> array(
                        'type' => array(),
                    ),
                    'b' => array(),
                    'i' => array(),
                    'p' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'a' => array(
                        'title'  => array(),
                        'href'   => array(),
                        'target' => array(),
                        'class'  => array(),
                        'id'     => array(),
                        'style'  => array(),
                    ),    
                    'h1'  => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'h2' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'h3' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'h4' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'h5' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'h6' => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),
                    'br' => array(),
                    'hr'  => array(
                        'class' => array(),
                        'id'    => array(),
                        'style' => array(),
                    ),                   
                )),
                'payment_status' => array(
                    array(
                        'value' => '1',
                        'text'  => esc_html__('Paid', 'bookingpress-appointment-booking'),
                    ),
                    array(
                        'value' => '2',
                        'text'  => esc_html__('Pending', 'bookingpress-appointment-booking'),
                    ),
                ),
                'appointment_status' => array(
                    array(
                        'value' => '1',
                        'text'  => esc_html__('Approved', 'bookingpress-appointment-booking'),
                    ),
                    array(
                        'value' => '2',
                        'text'  => esc_html__('Pending', 'bookingpress-appointment-booking'),
                    ),
                    array(
                        'value' => '3',
                        'text'  => esc_html__('Cancelled', 'bookingpress-appointment-booking'),
                    ),
                    array(
                        'value' => '4',
                        'text'  => esc_html__('Rejected', 'bookingpress-appointment-booking'),
                    ),
                ),
                'bookingpress_timezone_offset' => $this->bookingpress_get_utc_offset_of_city(),
                'bookingpress_inherit_from_wordpress_arr' => wp_json_encode(
                    array(
                        'G\hi' => 'HH\\\h mm',
                        'G:i' => 'HH:mm',
                        'G \h i \m\i\n' => 'HH \\\h mm \\\min',
                        'a g:i' => 'a hh:mm',
                        'g:ik a' => 'h:mm\\\k  a',
                        'G:i T' => 'HH:mm  UTC',
                        'G:i น.' => 'HH:mm  น.',
                        'ag:i' => 'a hh:mm',
                        'g:i a'=>'hh:mm a',
                        'g:i A'=>'hh:mm A',
                        'H:i'=>'HH:mm',
                        'h:i'=>'hh:mm',
                        'G:i A'=>'HH:mm A',
                        'H:i A'=>'HH:mm A',
                        'h:i a'=>'hh:mm a',
                        'g:i' => 'hh:mm',
                    )
                )
            );

            // Get all the customize settings
            /* global $tbl_bookingpress_customize_settings; */
            $bpa_customize_settings_data = array();
            if( 1 == $bookingpress_customize_settings_table_exists ){
   
                $bpa_cached_customize_settings = wp_cache_get( 'bookingpress_all_customize_settings' );
                if( false === $bpa_cached_customize_settings ){
                    $bookingpress_all_customize_settings = $wpdb->get_results( "SELECT bookingpress_setting_name,bookingpress_setting_value,bookingpress_setting_type FROM {$tbl_bookingpress_customize_settings} ORDER BY bookingpress_setting_type ASC" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally.
                    wp_cache_set( 'bookingpress_all_customize_settings', $bookingpress_all_customize_settings );
                } else {
                    $bookingpress_all_customize_settings = $bpa_cached_customize_settings;
                }

                if( !empty( $bookingpress_all_customize_settings ) ){
                    foreach( $bookingpress_all_customize_settings as $cs_key => $cs_value ){
                        $customize_type = $cs_value->bookingpress_setting_type;
                        if( empty( $bpa_customize_settings_data[ $customize_type ] ) ){
                            $bpa_customize_settings_data[ $customize_type ] = array();
                        }

                        $bpa_customize_settings_data[ $customize_type ][ $cs_value->bookingpress_setting_name ] = $cs_value->bookingpress_setting_value;
                    }
                }
            }

            $global_data['customize_settings'] = $bpa_customize_settings_data;

            $global_data['general_settings'] = $bpa_general_settings_data;
            
            $global_data = apply_filters('bookingpress_add_global_option_data', $global_data);

            /** @Deprecated and Should be removed in or after version 1.0.56 */
            if( 'bookingpress-wp-inherit-time-format' == $global_data['wp_default_time_format'] && $BookingPress->bpa_is_pro_active() && version_compare( $BookingPress->bpa_pro_plugin_version(), '2.0', '<') ){
                $global_data['wp_default_time_format'] = 'g:i a';
            }

            return $global_data;
        }

        function bookingpress_global_attributes(){
            return array(
                'class' => array(),
                'id' => array(),
                'title' => array(),
                'style' => array(),
            );
        }

        /**
         * Get timezone offset value
         *
         * @return void
         */
        function bookingpress_get_site_timezone_offset(){
            $bookingpress_options = $this->bookingpress_global_options();
            $bookingpress_timezone_offset = $bookingpress_options['bookingpress_timezone_offset'];
            $wp_timezone_string = wp_timezone_string();

            if( 'UTC' == $wp_timezone_string ){
				$wp_timezone_string = '+00:00';
			} else if( array_key_exists( $wp_timezone_string, $bookingpress_timezone_offset ) ){
				$wp_timezone_string = $bookingpress_timezone_offset[ $wp_timezone_string ];
			}

			return $wp_timezone_string;
        }
        
        /**
         * Get UTC offset of specific city
         *
         * @return void
         */
        function bookingpress_get_utc_offset_of_city(){
			$time_offset = array(
				'Africa/Abidjan' => '+00:00',
				'Africa/Accra' => '+00:00',
				'Africa/Addis_Abada' => '+03:00',
				'Africa/Algiers' => '+01:00',
				'Africa/Asmara' => '+03:00',
				'Africa/Bamako' => '+00:00',
				'Africa/Bangui' => '+01:00',
				'Africa/Banjul' => '+00:00',
				'Africa/Bissau' => '+00:00',
				'Africa/Blantyre' => '+02:00',
				'Africa/Brazzaville' => '+01:00',
				'Africa/Bujumbura' => '+02:00',
				'Africa/Cairo' => '+02:00',
				'Africa/Casablanca' => '+01:00',
				'Africa/Ceuta' => '+02:00',
				'Africa/Conakry' => '+00:00',
				'Africa/Dakar' => '+00:00',
				'Africa/Dar_es_Salaam ' => '+03:00',
				'Africa/Djibouti' => '+03:00',
				'Africa/Douala' => '+01:00',
				'Africa/El_Aaiun ' => '+01:00',
				'Africa/Freetown' => '+00:00',
				'Africa/Gaborone' => '+02:00',
				'Africa/Harare' => '+02:00',
				'Africa/Johannesburg' => '+02:00',
				'Africa/Juba' => '+02:00',
				'Africa/Kampala' => '+03:00',
				'Africa/Khartoum' => '+02:00',
				'Africa/Kigali' => '+02:00',
				'Africa/Kinshasa' => '+01:00',
				'Africa/Lagos' => '+01:00',
				'Africa/Libreville' => '+01:00',
				'Africa/Lome' => '+00:00',
				'Africa/Luanda' => '+01:00',
				'Africa/Lubumbashi' => '+02:00',
				'Africa/Lusaka' => '+02:00',
				'Africa/Malabo' => '+01:00',
				'Africa/Maputo' => '+02:00',
				'Africa/Maseru' => '+02:00',
				'Africa/Mbabane' => '+02:00',
				'Africa/Mogadishu' => '+03:00',
				'Africa/Monrovia' => '+00:00',
				'Africa/Nairobi' => '+03:00',
				'Africa/Ndjamena' => '+01:00',
				'Africa/Niamey' => '+01:00',
				'Africa/Nouakchott' => '+00:00',
				'Africa/Ouagadougou' => '+00:00',
				'Africa/Porto-Novo' => '+01:00',
				'Africa/Sao_Tome' => '+00:00',
				'Africa/Tripoli' => '+02:00',
				'Africa/Tunis' => '+01:00',
				'Africa/Windhoek' => '+02:00',
				'America/Adak' => '-09:00',
				'America/Anchorage' => '-08:00',
				'America/Anguilla' => '-04:00',
				'America/Antigua' => '-04:00',
				'America/Araguaina' => '-03:00',
				'America/Argentina/Buenos_Aires' => '-03:00',
				'America/Argentina/Catamarca' => '-03:00',
				'America/Argentina/Cordoba' => '-03:00',
				'America/Argentina/Jujuy' => '-03:00',
				'America/Argentina/La_Rioja' => '-03:00',
				'America/Argentina/Mendoza' => '-03:00',
				'America/Argentina/Rio_Gallegos' => '-03:00',
				'America/Argentina/Salta' => '-03:00',
				'America/Argentina/San_Juan' => '-03:00',
				'America/Argentina/San_Luis' => '-03:00',
				'America/Argentina/Tucuman' => '-03:00',
				'America/Argentina/Ushuaia' => '-04:00',
				'America/Aruba' => '-04:00',
				'America/Asuncion' => '-04:00',
				'America/Atikokan' => '-05:00',
				'America/Bahia' => '-03:00',
				'America/Bahia_Banderas' => '-05:00',
				'America/Barbados' => '-04:00',
				'America/Belem' => '-03:00',
				'America/Belize' => '-06:00',
				'America/Blanc-Sablon' => '-04:00',
				'America/Boa_Vista' => '-04:00',
				'America/Bogota' => '-05:00',
				'America/Boise' => '-06:00',
				'America/Cambridge_Bay' => '-06:00',
				'America/Campo_Grande' => '-04:00',
				'America/Cancun' => '-05:00',
				'America/Caracas' => '-04:00',
				'America/Cayenne' => '-03:00',
				'America/Cayman' => '-05:00',
				'America/Chicago' => '-05:00',
				'America/Chihuahua' => '-06:00',
				'America/Costa_Rica' => '-07:00',
				'America/Cuiaba' => '-04:00',
				'America/Curacao' => '-04:00',
				'America/Danmarkshavn' => '+00:00',
				'America/Dawson' => '-07:00',
				'America/Dawson_Creek' => '-07:00',
				'America/Denver' => '-06:00',
				'America/Detroit' => '-04:00',
				'America/Dominica' => '-04:00',
				'America/Edmonton' => '-06:00',
				'America/Eirunepe' => '-05:00',
				'America/El_Salvador' => '-06:00',
				'America/Fortaleza' => '-03:00',
				'America/Fort_Nelson' => '-07:00',
				'America/Glace_Bay' => '-03:00',
				'America/Goose_Bay' => '-03:00',
				'America/Grand_Turk' => '-04:00',
				'America/Grenada' => '-04:00',
				'America/Guadeloupe' => '-04:00',
				'America/Guatemala' => '-06:00',
				'America/Guayaquil' => '-05:00',
				'America/Guyana' => '-04:00',
				'America/Halifax' => '-03:00',
				'America/Havana' => '-04:00',
				'America/Hermosillo' => '-07:00',
				'America/Indiana/Indianapolis' => '-04:00',
				'America/Indiana/Knox' => '-05:00',
				'America/Indiana/Marengo' => '-04:00',
				'America/Indiana/Petersburg' => '-04:00',
				'America/Indiana/Tell_City' => '-05:00',
				'America/Indiana/Vevay' => '-04:00',
				'America/Indiana/Vincennes' => '-04:00',
				'America/Indiana/Winamac' => '-04:00',
				'America/Inuvik' => '-06:00',
				'America/Iqaluit' => '-04:00',
				'America/Jamaica' => '-05:00',
				'America/Juneau' => '-08:00',
				'America/Kentucky/Louisville' => '-04:00',
				'America/Kentucky/Monticello' => '-04:00',
				'America/Kralendijk' => '-04:00',
				'America/La_Paz' => '-04:00',
				'America/Lima' => '-05:00',
				'America/Los_Angeles' => '-07:00',
				'America/Lower_Princes' => '-04:00',
				'America/Maceio' => '-03:00',
				'America/Managua' => '-06:00',
				'America/Manaus' => '-04:00',
				'America/Marigot' => '-04:00',
				'America/Martinique' => '-04:00',
				'America/Matamoros' => '-05:00',
				'America/Mazatlan' => '-06:00',
				'America/Menominee' => '-05:00',
				'America/Merida' => '-05:00',
				'America/Metlakatla' => '-08:00',
				'America/Mexico_City' => '-05:00',
				'America/Miquelon' => '-02:00',
				'America/Moncton' => '-03:00',
				'America/Monterrey' => '-05:00',
				'America/Montevideo' => '-03:00',
				'America/Montserrat' => '-04:00',
				'America/Nassau' => '-04:00',
				'America/New_York' => '-04:00',
				'America/Nipigon' => '-04:00',
				'America/Nome' => '-08:00',
				'America/Noronha' => '-02:00',
				'America/North_Dakota/Beulah' => '-05:00',
				'America/North_Dakota/Center' => '-05:00',
				'America/North_Dakota/New_Salem' => '-05:00',
				'America/Nuuk' => '-02:00',
				'America/Ojinaga' => '-06:00',
				'America/Panama' => '-05:00',
				'America/Pangnirtung' => '-04:00',
				'America/Paramaribo' => '-03:00',
				'America/Phoenix' => '-07:00',
				'America/Port-au-Prince' => '-04:00',
				'America/Port_of_Spain' => '-04:00',
				'America/Porto_Velho' => '-04:00',
				'America/Puerto_Rico' => '-04:00',
				'America/Punta_Arenas' => '-03:00',
				'America/Rainy_River' => '-05:00',
				'America/Rankin_Inlet' => '-05:00',
				'America/Recife' => '-03:00',
				'America/Regina' => '-06:00',
				'America/Resolute' => '-05:00',
				'America/Rio_Branco' => '-05:00',
				'America/Santarem' => '-03:00',
				'America/Santiago' => '-04:00',
				'America/Santo_Domingo' => '-04:00',
				'America/Sao_Paulo' => '-03:00',
				'America/Scoresbysund' => '+00:00',
				'America/Sitka' => '-08:00',
				'America/St_Barthelemy' => '-04:00',
				'America/St_Johns' => '-03:30',
				'America/St_Kitts' => '-04:00',
				'America/St_Lucia' => '-04:00',
				'America/St_Thomas' => '-04:00',
				'America/St_Vincent' => '-04:00',
				'America/Swift_Current' => '-06:00',
				'America/Tegucigalpa' => '-06:00',
				'America/Thule' => '-03:00',
				'America/Thunder_Bay' => '-04:00',
				'America/Tijuana' => '-07:00',
				'America/Toronto' => '-04:00',
				'America/Tortola' => '-04:00',
				'America/Vancouver' => '-07:00',
				'America/Whitehorse' => '-07:00',
				'America/Winnipeg' => '-05:00',
				'America/Yakutat' => '-08:00',
				'America/Yellowknife' => '-06:00',
				'Antarctica/Casey' => '+11:00',
				'Antarctica/Davis' => '+07:00',
				'Antarctica/DumontDUrville' => '+10:00',
				'Antarctica/Macquarie' => '+10:00',
				'Antarctica/Mawson' => '+05:00',
				'Antarctica/McMurdo' => '+12:00',
				'Antarctica/Palmer' => '-03:00',
				'Antarctica/Rothera' => '-03:00',
				'Antarctica/Syowa' => '+03:00',
				'Antarctica/Troll' => '+02:00',
				'Antarctica/Vostok' => '+06:00',
				'Arctic/Longyearbyen' => '+01:00',
				'Asia/Aden' => '+03:00',
				'Asia/Almaty' => '+06:00',
				'Asia/Amman' => '+03:00',
				'Asia/Anadyr' => '+12:00',
				'Asia/Aqtau' => '+05:00',
				'Asia/Aqtobe' => '+05:00',
				'Asia/Ashgabat' => '+05:00',
				'Asia/Atyrau' => '+05:00',
				'Asia/Baghdad' => '+03:00',
				'Asia/Bahrain' => '+03:00',
				'Asia/Baku' => '+04:00',
				'Asia/Bangkok' => '+07:00',
				'Asia/Barnaul' => '+07:00',
				'Asia/Beirut' => '+03:00',
				'Asia/Bishkek' => '+06:00',
				'Asia/Brunei' => '+08:00',
				'Asia/Chita' => '+09:00',
				'Asia/Choibalsan' => '+08:00',
				'Asia/Colombo' => '+05:30',
				'Asia/Damascus' => '+03:00',
				'Asia/Dhaka' => '+06:00',
				'Asia/Dili' => '+09:00',
				'Asia/Dubai' => '+04:00',
				'Asia/Dushanbe' => '+05:00',
				'Asia/Famagusta' => '+03:00',
				'Asia/Gaza' => '+03:00',
				'Asia/Hebron' => '+03:00',
				'Asia/Ho_Chi_Minh' => '+07:00',
				'Asia/Hong_Kong' => '+08:00',
				'Asia/Hovd' => '+07:00',
				'Asia/Irkutsk' => '+08:00',
				'Asia/Jakarta' => '+07:00',
				'Asia/Jayapura' => '+09:00',
				'Asia/Jerusalem' => '+03:00',
				'Asia/Kabul' => '+04:30',
				'Asia/Kamchatka' => '+12:00',
				'Asia/Karachi' => '+05:00',
				'Asia/Kathmandu' => '+05:45',
				'Asia/Khandyga' => '+09:00',
				'Asia/Kolkata' => '+05:30',
				'Asia/Calcutta' => '+05:30',
				'Asia/Krasnoyarsk' => '+07:00',
				'Asia/Kuala_Lumpur' => '+08:00',
				'Asia/Kuching' => '+08:00',
				'Asia/Kuwait' => '+03:00',
				'Asia/Macau' => '+08:00',
				'Asia/Magadan' => '+11:00',
				'Asia/Makassar' => '+08:00',
				'Asia/Manila' => '+08:00',
				'Asia/Muscat' => '+04:00',
				'Asia/Nicosia' => '+03:00',
				'Asia/Novokuznetsk' => '+07:00',
				'Asia/Novosibirsk' => '+07:00',
				'Asia/Omsk' => '+06:00',
				'Asia/Oral' => '+05:00',
				'Asia/Phnom_Penh' => '+07:00',
				'Asia/Pontianak' => '+07:00',
				'Asia/Pyongyang' => '+08:30',
				'Asia/Qatar' => '+03:00',
				'Asia/Qostanay' => '+06:00',
				'Asia/Qyzylorda' => '+05:00',
				'Asia/Riyadh' => '+03:00',
				'Asia/Sakhalin' => '+11:00',
				'Asia/Samarkand' => '+05:00',
				'Asia/Seoul' => '+09:00',
				'Asia/Shanghai' => '+08:00',
				'Asia/Singapore' => '+08:00',
				'Asia/Srednekolymsk' => '+11:00',
				'Asia/Taipei' => '+08:00',
				'Asia/Tashkent' => '+05:00',
				'Asia/Tbilisi' => '+04:00',
				'Asia/Tehran' => '+03:30',
				'Asia/Thimphu' => '+06:00',
				'Asia/Tokyo' => '+09:00',
				'Asia/Tomsk' => '+07:00',
				'Asia/Ulaanbaatar' => '+08:00',
				'Asia/Urumqi' => '+06:00',
				'Asia/Ust-Nera' => '+10:00',
				'Asia/Vientiane' => '+07:00',
				'Asia/Vladivostok' => '+10:00',
				'Asia/Yakutsk' => '+09:00',
				'Asia/Yangon' => '+06:30',
				'Asia/Yekaterinburg' => '+05:00',
				'Asia/Yerevan' => '+04:00',
				'Atlantic/Azores' => '+00:00',
				'Atlantic/Bermuda' => '-03:00',
				'Atlantic/Canary' => '+01:00',
				'Atlantic/Cape_Verde' => '-01:00',
				'Atlantic/Faroe' => '+01:00',
				'Atlantic/Madeira' => '+01:00',
				'Atlantic/Reykjavik' => '+00:00',
				'Atlantic/South_Georgia' => '-02:00',
				'Atlantic/Stanley' => '-03:00',
				'Atlantic/St_Helena' => '+00:00',
				'Australia/Adelaide' => '+09:30',
				'Australia/Brisbane' => '+10:00',
				'Australia/Broken_Hill' => '+10:30',
				'Australia/Darwin' => '+09:30',
				'Australia/Eucla' => '+08:45',
				'Australia/Hobart' => '+10:00',
				'Australia/Lindeman' => '+10:00',
				'Australia/Lord_Howe' => '+10:30',
				'Australia/Melbourne' => '+10:00',
				'Australia/Perth' => '+08:00',
				'Australia/Sydney' => '+10:00',
				'Europe/Amsterdam' => '+02:00',
				'Europe/Andorra' => '+01:00',
				'Europe/Astrakhan' => '+04:00',
				'Europe/Athens' => '+03:00',
				'Europe/Belgrade' => '+02:00',
				'Europe/Berlin' => '+02:00',
				'Europe/Bratislava' => '+01:00',
				'Europe/Brussels' => '+02:00',
				'Europe/Bucharest' => '+03:00',
				'Europe/Budapest' => '+02:00',
				'Europe/Busingen' => '+02:00',
				'Europe/Chisinau' => '+03:00',
				'Europe/Copenhagen' => '+02:00',
				'Europe/Dublin' => '+01:00',
				'Europe/Gibraltar' => '+01:00',
				'Europe/Guernsey' => '+01:00',
				'Europe/Helsinki' => '+03:00',
				'Europe/Isle_of_Man' => '+01:00',
				'Europe/Istanbul' => '+03:00',
				'Europe/Jersey' => '+01:00',
				'Europe/Kaliningrad' => '+02:00',
				'Europe/Kiev' => '+02:00',
				'Europe/Kirov' => '+03:00',
				'Europe/Lisbon' => '+01:00',
				'Europe/Ljubljana' => '+02:00',
				'Europe/London' => '+01:00',
				'Europe/Luxembourg' => '+01:00',
				'Europe/Madrid' => '+02:00',
				'Europe/Malta' => '+02:00',
				'Europe/Mariehamn' => '+03:00',
				'Europe/Minsk' => '+03:00',
				'Europe/Monaco' => '+02:00',
				'Europe/Moscow' => '+03:00',
				'Europe/Oslo' => '+02:00',
				'Europe/Paris' => '+02:00',
				'Europe/Podgorica' => '+02:00',
				'Europe/Prague' => '+02:00',
				'Europe/Riga' => '+03:00',
				'Europe/Rome' => '+02:00',
				'Europe/Samara' => '+04:00',
				'Europe/San_Marino' => '+01:00',
				'Europe/Sarajevo' => '+02:00',
				'Europe/Saratov' => '+04:00',
				'Europe/Simferopol' => '+03:00',
				'Europe/Skopje' => '+02:00',
				'Europe/Sofia' => '+03:00',
				'Europe/Stockholm' => '+02:00',
				'Europe/Tallinn' => '+03:00',
				'Europe/Tirane' => '+02:00',
				'Europe/Ulyanovsk' => '+04:00',
				'Europe/Uzhgorod' => '+03:00',
				'Europe/Vaduz' => '+02:00',
				'Europe/Vatican' => '+02:00',
				'Europe/Vienna' => '+02:00',
				'Europe/Vilnius' => '+03:00',
				'Europe/Volgograd' => '+03:00',
				'Europe/Warsaw' => '+01:00',
				'Europe/Zagreb' => '+02:00',
				'Europe/Zaporozhye' => '+03:00',
				'Europe/Zurich' => '+02:00',
				'Indian/Antananarivo' => '+03:00',
				'Indian/Chagos' => '+06:00',
				'Indian/Christmas' => '+07:00',
				'Indian/Cocos' => '+06:30',
				'Indian/Comoro' => '+03:00',
				'Indian/Kerguelen' => '+05:00',
				'Indian/Mahe' => '+04:00',
				'Indian/Maldives' => '+05:00',
				'Indian/Mauritius' => '+04:00',
				'Indian/Mayotte' => '+03:00',
				'Indian/Reunion' => '+04:00',
				'Pacific/Apia' => '+13:00',
				'Pacific/Auckland' => '+12:00',
				'Pacific/Bougainville' => '+11:00',
				'Pacific/Chatham' => '+13:45',
				'Pacific/Chuuk' => '+10:00',
				'Pacific/Easter' => '-06:00',
				'Pacific/Efate' => '+11:00',
				'Pacific/Enderbury' => '+13:00',
				'Pacific/Fakaofo' => '+13:00',
				'Pacific/Fiji' => '+12:00',
				'Pacific/Funafuti' => '+12:00',
				'Pacific/Galapagos' => '-06:00',
				'Pacific/Gambier' => '-09:00',
				'Pacific/Guadalcanal' => '+11:00',
				'Pacific/Guam' => '+10:00',
				'Pacific/Honolulu' => '-10:00',
				'Pacific/Kiritimati' => '+14:00',
				'Pacific/Kosrae' => '+11:00',
				'Pacific/Kwajalein' => '+12:00',
				'Pacific/Majuro' => '+12:00',
				'Pacific/Marquesas' => '-09:30',
				'Pacific/Midway' => '-11:00',
				'Pacific/Nauru' => '+12:00',
				'Pacific/Niue' => '-11:00',
				'Pacific/Norfolk' => '+11:00',
				'Pacific/Noumea' => '+11:00',
				'Pacific/Pago_Pago' => '-11:00',
				'Pacific/Palau' => '+09:00',
				'Pacific/Pitcairn' => '-08:00',
				'Pacific/Pohnpei' => '+11:00',
				'Pacific/Port_Moresby' => '+10:00',
				'Pacific/Rarotonga' => '-10:00',
				'Pacific/Saipan' => '+10:00',
				'Pacific/Tahiti' => '-10:00',
				'Pacific/Tarawa' => '+12:00',
				'Pacific/Tongatapu' => '+13:00',
				'Pacific/Wake' => '+12:00',
				'Pacific/Wallis' => '+12:00',
                'Etc/GMT+12' => '-12:00',
                'Etc/GMT+11' => '-11:00',
                'Etc/GMT+9' => '-09:00',
                'Etc/GMT+8' => '-08:00',
                'Etc/GMT+2' => '-02:00',
                'Etc/GMT-12' => '+12:00',
                'Etc/GMT-13' => '+13:00'
			);

			return apply_filters( 'bookingpress_modify_default_timezone_offsets_for_city', $time_offset);
		}
        
        /**
         * Function for return BookingPress Default Fonts
         *
         * @return void
         */
        function bookingpress_get_default_fonts()
        {
            return array(
            'Arial',
            'Helvetica',
            'sans-serif',
            'Lucida Grande',
            'Lucida Sans Unicode',
            'Tahoma',
            'Times New Roman',
            'Courier New',
            'Verdana',
            'Geneva',
            'Courier',
            'Monospace',
            'Times',
            'Open Sans Semibold',
            'Open Sans Bold',
            );
        }
        
        /**
         * Function for return all supported google fonts
         *
         * @return void
         */
        function bookingpress_get_google_fonts()
        {
            return array(
            'ABeeZee',
            'Abel',
            'Abhaya Libre',
            'Abril Fatface',
            'Aclonica',
            'Acme',
            'Actor',
            'Adamina',
            'Advent Pro',
            'Aguafina Script',
            'Akronim',
            'Aladin',
            'Aldrich',
            'Alef',
            'Alegreya',
            'Alegreya SC',
            'Alegreya Sans',
            'Alegreya Sans SC',
            'Alex Brush',
            'Alfa Slab One',
            'Alice',
            'Alike',
            'Alike Angular',
            'Allan',
            'Allerta',
            'Allerta Stencil',
            'Allura',
            'Almendra',
            'Almendra Display',
            'Almendra SC',
            'Amarante',
            'Amaranth',
            'Amatic SC',
            'Amethysta',
            'Amiko',
            'Amiri',
            'Amita',
            'Anaheim',
            'Andada',
            'Andika',
            'Angkor',
            'Annie Use Your Telescope',
            'Anonymous Pro',
            'Antic',
            'Antic Didone',
            'Antic Slab',
            'Anton',
            'Arapey',
            'Arbutus',
            'Arbutus Slab',
            'Architects Daughter',
            'Archivo',
            'Archivo Black',
            'Archivo Narrow',
            'Aref Ruqaa',
            'Arima Madurai',
            'Arimo',
            'Arizonia',
            'Armata',
            'Arsenal',
            'Artifika',
            'Arvo',
            'Arya',
            'Asap',
            'Asap Condensed',
            'Asar',
            'Asset',
            'Assistant',
            'Astloch',
            'Asul',
            'Athiti',
            'Atma',
            'Atomic Age',
            'Aubrey',
            'Audiowide',
            'Autour One',
            'Average',
            'Average Sans',
            'Averia Gruesa Libre',
            'Averia Libre',
            'Averia Sans Libre',
            'Averia Serif Libre',
            'Bad Script',
            'Bahiana',
            'Bai Jamjuree',
            'Baloo',
            'Baloo Bhai',
            'Baloo Bhaijaan',
            'Baloo Bhaina',
            'Baloo Chettan',
            'Baloo Da',
            'Baloo Paaji',
            'Baloo Tamma',
            'Baloo Tammudu',
            'Baloo Thambi',
            'Balthazar',
            'Bangers',
            'Barlow',
            'Barlow Condensed',
            'Barlow Semi Condensed',
            'Barrio',
            'Basic',
            'Battambang',
            'Baumans',
            'Bayon',
            'Belgrano',
            'Bellefair',
            'Belleza',
            'BenchNine',
            'Bentham',
            'Berkshire Swash',
            'Bevan',
            'Bigelow Rules',
            'Bigshot One',
            'Bilbo',
            'Bilbo Swash Caps',
            'BioRhyme',
            'BioRhyme Expanded',
            'Biryani',
            'Bitter',
            'Black And White Picture',
            'Black Han Sans',
            'Black Ops One',
            'Bokor',
            'Bonbon',
            'Boogaloo',
            'Bowlby One',
            'Bowlby One SC',
            'Brawler',
            'Bree Serif',
            'Bubblegum Sans',
            'Bubbler One',
            'Buda',
            'Buenard',
            'Bungee',
            'Bungee Hairline',
            'Bungee Inline',
            'Bungee Outline',
            'Bungee Shade',
            'Butcherman',
            'Butterfly Kids',
            'Cabin',
            'Cabin Condensed',
            'Cabin Sketch',
            'Caesar Dressing',
            'Cagliostro',
            'Cairo',
            'Calligraffitti',
            'Cambay',
            'Cambo',
            'Candal',
            'Cantarell',
            'Cantata One',
            'Cantora One',
            'Capriola',
            'Cardo',
            'Carme',
            'Carrois Gothic',
            'Carrois Gothic SC',
            'Carter One',
            'Catamaran',
            'Caudex',
            'Caveat',
            'Caveat Brush',
            'Cedarville Cursive',
            'Ceviche One',
            'Chakra Petch',
            'Changa',
            'Changa One',
            'Chango',
            'Charmonman',
            'Chathura',
            'Chau Philomene One',
            'Chela One',
            'Chelsea Market',
            'Chenla',
            'Cherry Cream Soda',
            'Cherry Swash',
            'Chewy',
            'Chicle',
            'Chivo',
            'Chonburi',
            'Cinzel',
            'Cinzel Decorative',
            'Clicker Script',
            'Coda',
            'Coda Caption',
            'Codystar',
            'Coiny',
            'Combo',
            'Comfortaa',
            'Coming Soon',
            'Concert One',
            'Condiment',
            'Content',
            'Contrail One',
            'Convergence',
            'Cookie',
            'Copse',
            'Corben',
            'Cormorant',
            'Cormorant Garamond',
            'Cormorant Infant',
            'Cormorant SC',
            'Cormorant Unicase',
            'Cormorant Upright',
            'Courgette',
            'Cousine',
            'Coustard',
            'Covered By Your Grace',
            'Crafty Girls',
            'Creepster',
            'Crete Round',
            'Crimson Text',
            'Croissant One',
            'Crushed',
            'Cuprum',
            'Cute Font',
            'Cutive',
            'Cutive Mono',
            'Damion',
            'Dancing Script',
            'Dangrek',
            'David Libre',
            'Dawning of a New Day',
            'Days One',
            'Dekko',
            'Delius',
            'Delius Swash Caps',
            'Delius Unicase',
            'Della Respira',
            'Denk One',
            'Devonshire',
            'Dhurjati',
            'Didact Gothic',
            'Diplomata',
            'Diplomata SC',
            'Do Hyeon',
            'Dokdo',
            'Domine',
            'Donegal One',
            'Doppio One',
            'Dorsa',
            'Dosis',
            'Dr Sugiyama',
            'Duru Sans',
            'Dynalight',
            'EB Garamond',
            'Eagle Lake',
            'East Sea Dokdo',
            'Eater',
            'Economica',
            'Eczar',
            'El Messiri',
            'Electrolize',
            'Elsie',
            'Elsie Swash Caps',
            'Emblema One',
            'Emilys Candy',
            'Encode Sans',
            'Encode Sans Condensed',
            'Encode Sans Expanded',
            'Encode Sans Semi Condensed',
            'Encode Sans Semi Expanded',
            'Engagement',
            'Englebert',
            'Enriqueta',
            'Erica One',
            'Esteban',
            'Euphoria Script',
            'Ewert',
            'Exo',
            'Exo 2',
            'Expletus Sans',
            'Fahkwang',
            'Fanwood Text',
            'Farsan',
            'Fascinate',
            'Fascinate Inline',
            'Faster One',
            'Fasthand',
            'Fauna One',
            'Faustina',
            'Federant',
            'Federo',
            'Felipa',
            'Fenix',
            'Finger Paint',
            'Fira Mono',
            'Fira Sans',
            'Fira Sans Condensed',
            'Fira Sans Extra Condensed',
            'Fjalla One',
            'Fjord One',
            'Flamenco',
            'Flavors',
            'Fondamento',
            'Fontdiner Swanky',
            'Forum',
            'Francois One',
            'Frank Ruhl Libre',
            'Freckle Face',
            'Fredericka the Great',
            'Fredoka One',
            'Freehand',
            'Fresca',
            'Frijole',
            'Fruktur',
            'Fugaz One',
            'GFS Didot',
            'GFS Neohellenic',
            'Gabriela',
            'Gaegu',
            'Gafata',
            'Galada',
            'Galdeano',
            'Galindo',
            'Gamja Flower',
            'Gentium Basic',
            'Gentium Book Basic',
            'Geo',
            'Geostar',
            'Geostar Fill',
            'Germania One',
            'Gidugu',
            'Gilda Display',
            'Give You Glory',
            'Glass Antiqua',
            'Glegoo',
            'Gloria Hallelujah',
            'Goblin One',
            'Gochi Hand',
            'Gorditas',
            'Gothic A1',
            'Goudy Bookletter 1911',
            'Graduate',
            'Grand Hotel',
            'Gravitas One',
            'Great Vibes',
            'Griffy',
            'Gruppo',
            'Gudea',
            'Gugi',
            'Gurajada',
            'Habibi',
            'Halant',
            'Hammersmith One',
            'Hanalei',
            'Hanalei Fill',
            'Handlee',
            'Hanuman',
            'Happy Monkey',
            'Harmattan',
            'Headland One',
            'Heebo',
            'Henny Penny',
            'Herr Von Muellerhoff',
            'Hi Melody',
            'Hind',
            'Hind Guntur',
            'Hind Madurai',
            'Hind Siliguri',
            'Hind Vadodara',
            'Holtwood One SC',
            'Homemade Apple',
            'Homenaje',
            'IBM Plex Mono',
            'IBM Plex Sans',
            'IBM Plex Sans Condensed',
            'IBM Plex Serif',
            'IM Fell DW Pica',
            'IM Fell DW Pica SC',
            'IM Fell Double Pica',
            'IM Fell Double Pica SC',
            'IM Fell English',
            'IM Fell English SC',
            'IM Fell French Canon',
            'IM Fell French Canon SC',
            'IM Fell Great Primer',
            'IM Fell Great Primer SC',
            'Iceberg',
            'Iceland',
            'Imprima',
            'Inconsolata',
            'Inder',
            'Indie Flower',
            'Inika',
            'Inknut Antiqua',
            'Irish Grover',
            'Istok Web',
            'Italiana',
            'Italianno',
            'Itim',
            'Jacques Francois',
            'Jacques Francois Shadow',
            'Jaldi',
            'Jim Nightshade',
            'Jockey One',
            'Jolly Lodger',
            'Jomhuria',
            'Josefin Sans',
            'Josefin Slab',
            'Joti One',
            'Jua',
            'Judson',
            'Julee',
            'Julius Sans One',
            'Junge',
            'Jura',
            'Just Another Hand',
            'Just Me Again Down Here',
            'K2D',
            'Kadwa',
            'Kalam',
            'Kameron',
            'Kanit',
            'Kantumruy',
            'Karla',
            'Karma',
            'Katibeh',
            'Kaushan Script',
            'Kavivanar',
            'Kavoon',
            'Kdam Thmor',
            'Keania One',
            'Kelly Slab',
            'Kenia',
            'Khand',
            'Khmer',
            'Khula',
            'Kirang Haerang',
            'Kite One',
            'Knewave',
            'KoHo',
            'Kodchasan',
            'Kosugi',
            'Kosugi Maru',
            'Kotta One',
            'Koulen',
            'Kranky',
            'Kreon',
            'Kristi',
            'Krona One',
            'Krub',
            'Kumar One',
            'Kumar One Outline',
            'Kurale',
            'La Belle Aurore',
            'Laila',
            'Lakki Reddy',
            'Lalezar',
            'Lancelot',
            'Lateef',
            'Lato',
            'League Script',
            'Leckerli One',
            'Ledger',
            'Lekton',
            'Lemon',
            'Lemonada',
            'Life Savers',
            'Lilita One',
            'Lily Script One',
            'Limelight',
            'Linden Hill',
            'Lobster',
            'Lobster Two',
            'Londrina Outline',
            'Londrina Shadow',
            'Londrina Sketch',
            'Londrina Solid',
            'Lora',
            'Love Ya Like A Sister',
            'Loved by the King',
            'Lovers Quarrel',
            'Luckiest Guy',
            'Lusitana',
            'Lustria',
            'M PLUS 1p',
            'M PLUS Rounded 1c',
            'Macondo',
            'Macondo Swash Caps',
            'Mada',
            'Magra',
            'Maiden Orange',
            'Maitree',
            'Mako',
            'Mali',
            'Mallanna',
            'Mandali',
            'Manuale',
            'Marcellus',
            'Marcellus SC',
            'Marck Script',
            'Margarine',
            'Markazi Text',
            'Marko One',
            'Marmelad',
            'Martel',
            'Martel Sans',
            'Marvel',
            'Mate',
            'Mate SC',
            'Maven Pro',
            'McLaren',
            'Meddon',
            'MedievalSharp',
            'Medula One',
            'Meera Inimai',
            'Megrim',
            'Meie Script',
            'Merienda',
            'Merienda One',
            'Merriweather',
            'Merriweather Sans',
            'Metal',
            'Metal Mania',
            'Metamorphous',
            'Metrophobic',
            'Michroma',
            'Milonga',
            'Miltonian',
            'Miltonian Tattoo',
            'Mina',
            'Miniver',
            'Miriam Libre',
            'Mirza',
            'Miss Fajardose',
            'Mitr',
            'Modak',
            'Modern Antiqua',
            'Mogra',
            'Molengo',
            'Molle',
            'Monda',
            'Monofett',
            'Monoton',
            'Monsieur La Doulaise',
            'Montaga',
            'Montez',
            'Montserrat',
            'Montserrat Alternates',
            'Montserrat Subrayada',
            'Moul',
            'Moulpali',
            'Mountains of Christmas',
            'Mouse Memoirs',
            'Mr Bedfort',
            'Mr Dafoe',
            'Mr De Haviland',
            'Mrs Saint Delafield',
            'Mrs Sheppards',
            'Mukta',
            'Mukta Mahee',
            'Mukta Malar',
            'Mukta Vaani',
            'Muli',
            'Mystery Quest',
            'Manrope',
            'NTR',
            'Nanum Brush Script',
            'Nanum Gothic',
            'Nanum Gothic Coding',
            'Nanum Myeongjo',
            'Nanum Pen Script',
            'Neucha',
            'Neuton',
            'New Rocker',
            'News Cycle',
            'Niconne',
            'Niramit',
            'Nixie One',
            'Nobile',
            'Nokora',
            'Norican',
            'Nosifer',
            'Notable',
            'Nothing You Could Do',
            'Noticia Text',
            'Noto Sans',
            'Noto Sans JP',
            'Noto Sans KR',
            'Noto Serif',
            'Noto Serif JP',
            'Noto Serif KR',
            'Nova Cut',
            'Nova Flat',
            'Nova Mono',
            'Nova Oval',
            'Nova Round',
            'Nova Script',
            'Nova Slim',
            'Nova Square',
            'Numans',
            'Nunito',
            'Nunito Sans',
            'Odor Mean Chey',
            'Offside',
            'Old Standard TT',
            'Oldenburg',
            'Oleo Script',
            'Oleo Script Swash Caps',
            'Open Sans',
            'Open Sans Condensed',
            'Oranienbaum',
            'Orbitron',
            'Oregano',
            'Orienta',
            'Original Surfer',
            'Oswald',
            'Over the Rainbow',
            'Overlock',
            'Overlock SC',
            'Overpass',
            'Overpass Mono',
            'Ovo',
            'Oxygen',
            'Oxygen Mono',
            'PT Mono',
            'PT Sans',
            'PT Sans Caption',
            'PT Sans Narrow',
            'PT Serif',
            'PT Serif Caption',
            'Pacifico',
            'Padauk',
            'Palanquin',
            'Palanquin Dark',
            'Pangolin',
            'Paprika',
            'Parisienne',
            'Passero One',
            'Passion One',
            'Pathway Gothic One',
            'Patrick Hand',
            'Patrick Hand SC',
            'Pattaya',
            'Patua One',
            'Pavanam',
            'Paytone One',
            'Peddana',
            'Peralta',
            'Permanent Marker',
            'Petit Formal Script',
            'Petrona',
            'Philosopher',
            'Piedra',
            'Pinyon Script',
            'Pirata One',
            'Plaster',
            'Play',
            'Playball',
            'Playfair Display',
            'Playfair Display SC',
            'Podkova',
            'Poiret One',
            'Poller One',
            'Poly',
            'Pompiere',
            'Pontano Sans',
            'Poor Story',
            'Poppins',
            'Port Lligat Sans',
            'Port Lligat Slab',
            'Pragati Narrow',
            'Prata',
            'Preahvihear',
            'Press Start 2P',
            'Pridi',
            'Princess Sofia',
            'Prociono',
            'Prompt',
            'Prosto One',
            'Proza Libre',
            'Puritan',
            'Purple Purse',
            'Quando',
            'Quantico',
            'Quattrocento',
            'Quattrocento Sans',
            'Questrial',
            'Quicksand',
            'Quintessential',
            'Qwigley',
            'Racing Sans One',
            'Radley',
            'Rajdhani',
            'Rakkas',
            'Raleway',
            'Raleway Dots',
            'Ramabhadra',
            'Ramaraja',
            'Rambla',
            'Rammetto One',
            'Ranchers',
            'Rancho',
            'Ranga',
            'Rasa',
            'Rationale',
            'Ravi Prakash',
            'Redressed',
            'Reem Kufi',
            'Reenie Beanie',
            'Revalia',
            'Rhodium Libre',
            'Ribeye',
            'Ribeye Marrow',
            'Righteous',
            'Risque',
            'Roboto',
            'Roboto Condensed',
            'Roboto Mono',
            'Roboto Slab',
            'Rochester',
            'Rock Salt',
            'Rokkitt',
            'Romanesco',
            'Ropa Sans',
            'Rosario',
            'Rosarivo',
            'Rouge Script',
            'Rozha One',
            'Rubik',
            'Rubik Mono One',
            'Ruda',
            'Rufina',
            'Ruge Boogie',
            'Ruluko',
            'Rum Raisin',
            'Ruslan Display',
            'Russo One',
            'Ruthie',
            'Rye',
            'Sacramento',
            'Sahitya',
            'Sail',
            'Saira',
            'Saira Condensed',
            'Saira Extra Condensed',
            'Saira Semi Condensed',
            'Salsa',
            'Sanchez',
            'Sancreek',
            'Sansita',
            'Sarala',
            'Sarina',
            'Sarpanch',
            'Satisfy',
            'Sawarabi Gothic',
            'Sawarabi Mincho',
            'Scada',
            'Scheherazade',
            'Schoolbell',
            'Scope One',
            'Seaweed Script',
            'Secular One',
            'Sedgwick Ave',
            'Sedgwick Ave Display',
            'Sevillana',
            'Seymour One',
            'Shadows Into Light',
            'Shadows Into Light Two',
            'Shanti',
            'Share',
            'Share Tech',
            'Share Tech Mono',
            'Shojumaru',
            'Short Stack',
            'Shrikhand',
            'Siemreap',
            'Sigmar One',
            'Signika',
            'Signika Negative',
            'Simonetta',
            'Sintony',
            'Sirin Stencil',
            'Six Caps',
            'Skranji',
            'Slabo 13px',
            'Slabo 27px',
            'Slackey',
            'Smokum',
            'Smythe',
            'Sniglet',
            'Snippet',
            'Snowburst One',
            'Sofadi One',
            'Sofia',
            'Song Myung',
            'Sonsie One',
            'Sorts Mill Goudy',
            'Source Code Pro',
            'Source Sans Pro',
            'Source Serif Pro',
            'Space Mono',
            'Special Elite',
            'Spectral',
            'Spectral SC',
            'Spicy Rice',
            'Spinnaker',
            'Spirax',
            'Squada One',
            'Sree Krushnadevaraya',
            'Sriracha',
            'Srisakdi',
            'Stalemate',
            'Stalinist One',
            'Stardos Stencil',
            'Stint Ultra Condensed',
            'Stint Ultra Expanded',
            'Stoke',
            'Strait',
            'Stylish',
            'Sue Ellen Francisco',
            'Suez One',
            'Sumana',
            'Sunflower',
            'Sunshiney',
            'Supermercado One',
            'Sura',
            'Suranna',
            'Suravaram',
            'Suwannaphum',
            'Swanky and Moo Moo',
            'Syncopate',
            'Tajawal',
            'Tangerine',
            'Taprom',
            'Tauri',
            'Taviraj',
            'Teko',
            'Telex',
            'Tenali Ramakrishna',
            'Tenor Sans',
            'Text Me One',
            'The Girl Next Door',
            'Tienne',
            'Tillana',
            'Timmana',
            'Tinos',
            'Titan One',
            'Titillium Web',
            'Trade Winds',
            'Trirong',
            'Trocchi',
            'Trochut',
            'Trykker',
            'Tulpen One',
            'Ubuntu',
            'Ubuntu Condensed',
            'Ubuntu Mono',
            'Ultra',
            'Uncial Antiqua',
            'Underdog',
            'Unica One',
            'UnifrakturCook',
            'UnifrakturMaguntia',
            'Unkempt',
            'Unlock',
            'Unna',
            'VT323',
            'Vampiro One',
            'Varela',
            'Varela Round',
            'Vast Shadow',
            'Vesper Libre',
            'Vibur',
            'Vidaloka',
            'Viga',
            'Voces',
            'Volkhov',
            'Vollkorn',
            'Vollkorn SC',
            'Voltaire',
            'Waiting for the Sunrise',
            'Wallpoet',
            'Walter Turncoat',
            'Warnes',
            'Wellfleet',
            'Wendy One',
            'Wire One',
            'Work Sans',
            'Yanone Kaffeesatz',
            'Yantramanav',
            'Yatra One',
            'Yellowtail',
            'Yeon Sung',
            'Yeseva One',
            'Yesteryear',
            'Yrsa',
            'Zeyada',
            'Zilla Slab',
            'Zilla Slab Highlight',
            );
        }
        
        /**
         * Get website current language
         *
         * @return void
         */
        function bookingpress_get_site_current_language()
        {
            $bookingpress_site_current_language = get_locale();
            
            if ($bookingpress_site_current_language == 'ru_RU' ) {
                $bookingpress_site_current_language = 'ru';
            } elseif ($bookingpress_site_current_language == 'bs_BA' ) {
                $bookingpress_site_current_language = 'ba';// bosnia
            } elseif ($bookingpress_site_current_language == 'vi' ) {
                $bookingpress_site_current_language = 'vn';// Vietnamese
            } elseif ($bookingpress_site_current_language == 'sw' ) {
                $bookingpress_site_current_language = 'se';// Swedish
            } elseif ($bookingpress_site_current_language == 'ar' ) {
                $bookingpress_site_current_language = 'ar'; // arabic
            } elseif ($bookingpress_site_current_language == 'bg_BG' ) {
                $bookingpress_site_current_language = 'bg'; // Bulgeria
            } elseif ($bookingpress_site_current_language == 'ca' ) {
                $bookingpress_site_current_language = 'ca'; // Canada
            } elseif ($bookingpress_site_current_language == 'da_DK' ) {
                $bookingpress_site_current_language = 'da'; // Denmark
            } elseif ($bookingpress_site_current_language == 'de_DE' || $bookingpress_site_current_language == 'de_CH_informal' || $bookingpress_site_current_language == 'de_AT' || $bookingpress_site_current_language == 'de_CH' || $bookingpress_site_current_language == 'de_DE_formal' ) {
                $bookingpress_site_current_language = 'de'; // Germany
            } elseif ($bookingpress_site_current_language == 'el' ) {
                $bookingpress_site_current_language = 'el'; // Greece
            } elseif ($bookingpress_site_current_language == 'es_ES' ) {
                $bookingpress_site_current_language = 'es'; // Spain
            } elseif ($bookingpress_site_current_language == 'fr_FR' ) {
                $bookingpress_site_current_language = 'fr'; // France
            } elseif ($bookingpress_site_current_language == 'fr_CA' ) {
                $bookingpress_site_current_language = 'fr_CA'; // Franch canada
            } 
            elseif ($bookingpress_site_current_language == 'hr' ) {
                $bookingpress_site_current_language = 'hr'; // Croatia
            } elseif ($bookingpress_site_current_language == 'hu_HU' ) {
                $bookingpress_site_current_language = 'hu'; // Hungary
            } elseif ($bookingpress_site_current_language == 'id_ID' ) {
                $bookingpress_site_current_language = 'id'; // Indonesia
            } elseif ($bookingpress_site_current_language == 'is_IS' ) {
                $bookingpress_site_current_language = 'is'; // Iceland
            } elseif ($bookingpress_site_current_language == 'it_IT' ) {
                $bookingpress_site_current_language = 'it'; // Italy
            } elseif ($bookingpress_site_current_language == 'ja' ) {
                $bookingpress_site_current_language = 'ja'; // Japan
            } elseif ($bookingpress_site_current_language == 'ko_KR' ) {
                $bookingpress_site_current_language = 'ko'; // Korean
            } elseif ($bookingpress_site_current_language == 'lt_LT' ) {
                $bookingpress_site_current_language = 'lt'; // Lithunian
            } elseif ($bookingpress_site_current_language == 'mn' ) {
                $bookingpress_site_current_language = 'mn'; // Mongolia
            } elseif ($bookingpress_site_current_language == 'nl_NL' ) {
                $bookingpress_site_current_language = 'nl'; // Netherlands
            } elseif ($bookingpress_site_current_language == 'pl_PL' ) {
                $bookingpress_site_current_language = 'pl'; // Poland
            } elseif ($bookingpress_site_current_language == 'pt_BR' ) {
                $bookingpress_site_current_language = 'pt-br'; // Portuguese
            } elseif ($bookingpress_site_current_language == 'ro_RO' ) {
                $bookingpress_site_current_language = 'ro'; // Romania
            } elseif ($bookingpress_site_current_language == 'sk_SK' ) {
                $bookingpress_site_current_language = 'sk'; // Slovakia
            } elseif ($bookingpress_site_current_language == 'sl_SI' ) {
                $bookingpress_site_current_language = 'sl'; // Slovenia
            } elseif ($bookingpress_site_current_language == 'sq' ) {
                $bookingpress_site_current_language = 'sq'; // Albanian
            } elseif ($bookingpress_site_current_language == 'sr_RS' ) {
                $bookingpress_site_current_language = 'sr'; // Suriname
            } elseif ($bookingpress_site_current_language == 'sv_SE' ) {
                $bookingpress_site_current_language = 'sv'; // El Salvador
            } elseif ($bookingpress_site_current_language == 'tr_TR' ) {
                $bookingpress_site_current_language = 'tr'; // Turkey
            } elseif ($bookingpress_site_current_language == 'uk' ) {
                $bookingpress_site_current_language = 'uk'; // Ukrain
            } elseif ($bookingpress_site_current_language == 'vi' ) {
                $bookingpress_site_current_language = 'vi'; // Virgin Islands (U.S.)
            } elseif ($bookingpress_site_current_language == 'zh_CN' ) {
                $bookingpress_site_current_language = 'zh-cn'; // Chinese
            } elseif ($bookingpress_site_current_language == 'ka_GE' ) {
                $bookingpress_site_current_language = 'ka'; // Georgian
            } elseif ($bookingpress_site_current_language == 'nl_BE'){
                $bookingpress_site_current_language = 'nl-be';
            } elseif ($bookingpress_site_current_language == 'cs_CZ'){
                $bookingpress_site_current_language = 'cs';
            }elseif ($bookingpress_site_current_language == 'pt_PT'){
                $bookingpress_site_current_language = 'pt';
            } elseif ($bookingpress_site_current_language == 'et'){
                $bookingpress_site_current_language = 'et';
            }elseif ($bookingpress_site_current_language == 'nb_NO'){
                $bookingpress_site_current_language = 'no'; //Norwegian
            }elseif ($bookingpress_site_current_language == 'lv'){
                $bookingpress_site_current_language = 'lv'; //Latvian
            }elseif ($bookingpress_site_current_language == 'az'){
                $bookingpress_site_current_language = 'az'; //Azerbijani
            }elseif ($bookingpress_site_current_language == 'fi'){
                $bookingpress_site_current_language = 'fi'; //Finnish
            }elseif ($bookingpress_site_current_language == 'gl_ES' ) {
                $bookingpress_site_current_language = 'gl';
            }elseif ($bookingpress_site_current_language == 'he_IL' ) {
                $bookingpress_site_current_language = 'he';//Hebrew
            }else {
                $bookingpress_site_current_language = 'en';
            }

            return $bookingpress_site_current_language;
        }
    }
}
global $bookingpress_global_options, $bookingpress_notification_duration;
$bookingpress_global_options        = new BookingPress_Global_Options();
$bookingpress_notification_duration = '1500';