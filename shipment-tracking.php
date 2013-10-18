<?php
/*
	Plugin Name: WooCommerce Shipment Tracking
	Plugin URI: http://woothemes.com/woocommerce
	Description: Add tracking numbers to orders allowing customers to track their orders via a link. Supports many shipping providers, as well as custom ones if neccessary via a regular link.
	Version: 1.1.1
	Author: Mike Jolley
	Author URI: http://mikejolley.com

	Copyright: © 2009-2012 WooThemes.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );


//require_once('shipping-services-api-wsdl.wsdl');
/**
 * Plugin updates
 */
//woothemes_queue_update( plugin_basename( __FILE__ ), '1968e199038a8a001c9f9966fd06bf88', '18693' );

if ((!( is_woocommerce_active()) )) {

	/**
	 * WC_Shipment_Tracking class
	 */
	if ( ! class_exists( 'WC_Shipment_Tracking' ) ) {

		class WC_Shipment_Tracking {

			var $providers;

			/**
			 * Constructor
			 */
			function __construct() {
                       //     wp_enqueue_script('myscript', plugins_url('/shipment-tracking/assets/js/common.js'));
/*
				$this->providers = array(
					'Australia' => array(
						'Australia Post'
							=> 'http://auspost.com.au/track/track.html?id=%1$s',
					),
					'Brazil' => array(
						'Correios'
							=> 'http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=%1$s'
					),
					'Canada' => array(
						'Canada Post'
							=> 'http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=%1$s',
					),
					'India' => array(
						'DTDC'
							=> 'http://www.dtdc.in/dtdcTrack/Tracking/consignInfo.asp?strCnno=%1$s',
                                                'Aramex'
							=> 'http://www.aramex.com/express/track_results_multiple.aspx?ShipmentNumber=%1$s',
					),
					'Netherlands' => array(
						'PostNL'
							=> 'https://mijnpakket.postnl.nl/Claim?Barcode=%1$s&Postalcode=%2$s&Foreign=False&ShowAnonymousLayover=False&CustomerServiceClaim=False',
					),
					'South African' => array(
						'SAPO'
							=> 'http://tracking.postoffice.co.za/parcel.aspx?id=%1$s',
					),
					'Sweden' => array(
						'Posten AB'
							=> 'http://server.logistik.posten.se/servlet/PacTrack?xslURL=/xsl/pactrack/standard.xsl&/css/kolli.css&lang2=SE&kolliid=%1$s',
					),
					'United Kingdom' => array(
						'City Link'
							=> 'http://www.city-link.co.uk/dynamic/track.php?parcel_ref_num=%1$s',
						'DHL'
							=> 'http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=%1$s',
						'DPD'
							=> 'http://track.dpdnl.nl/?parcelnumber=%1$s',
						'ParcelForce'
							=> 'http://www.parcelforce.com/portal/pw/track?trackNumber=%1$s',
						'Royal Mail'
							=> 'http://track2.royalmail.com/portal/rm/track?trackNumber=%1$s',
						'TNT Express (consignment)'
							=> 'http://www.tnt.com/webtracker/tracking.do?requestType=GEN&searchType=CON&respLang=en&
respCountry=GENERIC&sourceID=1&sourceCountry=ww&cons=%1$s&navigation=1&g
enericSiteIdent=',
						'TNT Express (reference)'
							=> 'http://www.tnt.com/webtracker/tracking.do?requestType=GEN&searchType=REF&respLang=en&r
espCountry=GENERIC&sourceID=1&sourceCountry=ww&cons=%1$s&navigation=1&gen
ericSiteIdent=',
					),
					'United States' => array(
						'Fedex'
							=> 'http://www.fedex.com/Tracking?action=track&tracknumbers=%1$s',
						'OnTrac'
							=> 'http://www.ontrac.com/trackingdetail.asp?tracking=%1$s',
						'UPS'
							=> 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%1$s',
						'USPS'
							=> 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=%1$s',
					),
				);

     */                           			$this->providers = array(

					'India' => array(
						'DTDC'
							=> 'http://www.dtdc.in/dtdcTrack/Tracking/consignInfo.asp?strCnno=%1$s',
                                                'Aramex'
							=> 'http://www.aramex.com/express/track_results_multiple.aspx?ShipmentNumber=%1$s',
                                            
                                            'Self'
							=> 'self',
                                            'blue dart'
							=> 'http://www.bluedart.com/maintracking.html',
					),
				);

				add_action( 'admin_print_styles', array( &$this, 'admin_styles' ) );
				add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
				add_action( 'woocommerce_process_shop_order_meta', array( &$this, 'save_meta_box' ), 0, 2 );
				add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

				// View Order Page
				add_action( 'woocommerce_view_order', array( &$this, 'display_tracking_info' ) );
				add_action( 'woocommerce_email_before_order_table', array( &$this, 'email_display' ) );
                                add_action( 'woocommerce_view_order',  array( &$this,'my_action_javascript' ));
                                add_action( 'woocommerce_view_order',  array( &$this,'my_bluedart_javascript' ));
                                add_action('wp_ajax_my_action', array(&$this, 'my_action_callback'));
                                add_action('wp_ajax_bluedart_action', array(&$this, 'bluedart_bluedarts_callback'));
                               
                               // add_action('wp_ajax_my_action', 'my_action_callback');
			}

			/**
			 * Localisation
			 */
                    /*    
                        function my_action_javascript() {

                            '<script type="text/javascript" >
                            jQuery(document).ready(function() {
                                    alert("test");
                                    var data = {
                                            action: "my_action",
                                            whatever: 1234
                                    };

                                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                                    jQuery.post(ajaxurl, data, function(response) {
                                            alert("Got this from the server: " + response);
                                    });
                            });
                            </script>';
                        }
                      */ 
                        
function bluedart_bluedarts_callback() {
	global $woocommerce, $post,$wpdb;
     
	$theorder = new WC_Order( $_POST['oid'] );

        if($theorder->payment_method=='cod'){
             $myrows = $wpdb->get_row( "SELECT * FROM bluedart_cod where assigned='0'" );
              echo trim($myrows->CAWBNO);
      } else {
        $myrows = $wpdb->get_row( "SELECT * FROM bluedart_prepaid where assigned='0'" );
        echo trim($myrows->CAWBNO);    
          
      }
 
}                    

function my_action_callback() {
	global $woocommerce, $post,$wpdb;
            
		//$order = new WC_Order(get_the_ID());
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	//echo $post->ID;

	//if ( empty( $the_order ) || $the_order->id != $post->ID )
	$theorder = new WC_Order( $_POST['oid'] );
       // echo '<pre>';
       // print_r($theorder); exit;
        $dateSelected = $_POST['selecteddate'];
        $dates = explode('-',$dateSelected);
        $year = $dates[0];
        $month = $dates[1];
        $day = $dates[2];
       
        $newDates = mktime('0','0','0',$month,$day,$year);

        $totalAmt = $theorder->order_total;
        /*
         if($theorder->payment_method == 'cod'){
            $paymentService = 'CODS';
        } else {
            $paymentService = '';
            $totalAmt='';
        }
         * */
         
        
        if($theorder->payment_method == 'cod'){
            $paymentService = 'CODS';
            $totalAmt = $theorder->order_total;
            $productTYpe = "CDA";
        } else {
            $paymentService = '';
            $totalAmt = '';
            $productTYpe = "ONP";
        }
        
        
$cityRows = $wpdb->get_row( "SELECT * FROM aramex_master where pincode=$theorder->shipping_postcode" ); 
	//echo 'Service not available at the moment.'; 
//exit;
     //   echo plugins_url('shipment-tracking/shipping-services-api-wsdl.wsdl'); exit;
	$soapClient = new SoapClient(plugins_url('shipment-tracking/shipping-services-api-wsdl.wsdl'));
	//echo '<pre>';
	//print_r($soapClient->__getFunctions());
//exit;
	$params = array(
			'Shipments' => array(
				'Shipment' => array(
						'Shipper'	=> array(
										'Reference1' 	=> 'glam'.$_POST['oid'],
										'Reference2' 	=> '',
										'AccountNumber' => '50613983',
										'PartyAddress'	=> array(
											'Line1'					=> 'Room no-103, B-9, First Floor, Housing
Socity, South Extension Part-I',
											'Line2' 				=> '',
											'Line3' 				=> '',
											'City'					=> 'New Delhi South Ext-II',
											'StateOrProvinceCode'	=> '',
											'PostCode'				=> 110049,
											'CountryCode'			=> 'IN'
										),
										'Contact'		=> array(
											'Department'			=> '',
											'PersonName'			=> 'Gautam',
											'Title'					=> '',
											'CompanyName'			=> 'getglamr',
											'PhoneNumber1'			=> '5555555',
											'PhoneNumber1Ext'		=> '125',
											'PhoneNumber2'			=> '',
											'PhoneNumber2Ext'		=> '',
											'FaxNumber'				=> '',
											'CellPhone'				=> '07777777',
											'EmailAddress'			=> 'michael@aramex.com',
											'Type'					=> ''
										),
						),
			//$theorder->shipping_postcode	
                                    //$theorder->shipping_city
                                    // $theorder->shipping_state
                          
						'Consignee'	=> array(
										'Reference1'	=> 'Ref 333333',
										'Reference2'	=> 'Ref 444444',
										'AccountNumber' => '',
										'PartyAddress'	=> array(
											'Line1'					=> $theorder->shipping_address_1,
											'Line2'					=> $theorder->shipping_address_2,
											'Line3'					=> '',
											'City'					=> $cityRows->actual_city,
											'StateOrProvinceCode'	=> '',
											'PostCode'				=> $theorder->shipping_postcode ,
											'CountryCode'			=> 'IN'
										),
										
										'Contact'		=> array(
											'Department'			=> '',
											'PersonName'			=> $theorder->shipping_first_name,
											'Title'					=> '',
											'CompanyName'			=> $theorder->shipping_first_name,
											'PhoneNumber1'			=> $theorder->billing_phone,
											'PhoneNumber1Ext'		=> '',
											'PhoneNumber2'			=> '',
											'PhoneNumber2Ext'		=> '',
											'FaxNumber'				=> '',
											'CellPhone'				=> $theorder->billing_phone,
											'EmailAddress'			=> $theorder->billing_email,
											'Type'					=> ''
										),
						),
                                   
                                    /*
                                    						'Consignee'	=> array(
										'Reference1'	=> 'Ref 333333',
										'Reference2'	=> 'Ref 444444',
										'AccountNumber' => '',
										'PartyAddress'	=> array(
											'Line1'	=> '15 ABC St',
											'Line2'	=> '',
											'Line3'	=> '',
											'City'	=> 'Dubai',
											'StateOrProvinceCode'	=> '',
											'PostCode'		=> '',
											'CountryCode'		=> 'AE'
										),
										
										'Contact'		=> array(
											'Department'	=> '',
											'PersonName'	=> 'Mazen',
											'Title'		=> '',
											'CompanyName'	=> 'Aramex',
											'PhoneNumber1'	=> '6666666',
											'PhoneNumber1Ext'		=> '155',
											'PhoneNumber2'			=> '',
											'PhoneNumber2Ext'		=> '',
											'FaxNumber'				=> '',
											'CellPhone'				=> '9811554521',
											'EmailAddress'			=> 'mazen@aramex.com',
											'Type'					=> ''
										),
						),
                                     * 
                                     */						
						'ThirdParty' => array(
										'Reference1' 	=> '',
										'Reference2' 	=> '',
										'AccountNumber' => '',
										'PartyAddress'	=> array(
											'Line1'					=> '',
											'Line2'					=> '',
											'Line3'					=> '',
											'City'					=> '',
											'StateOrProvinceCode'	=> '',
											'PostCode'				=> '',
											'CountryCode'			=> ''
										),
										'Contact'		=> array(
											'Department'			=> '',
											'PersonName'			=> '',
											'Title'					=> '',
											'CompanyName'			=> '',
											'PhoneNumber1'			=> '',
											'PhoneNumber1Ext'		=> '',
											'PhoneNumber2'			=> '',
											'PhoneNumber2Ext'		=> '',
											'FaxNumber'				=> '',
											'CellPhone'				=> '',
											'EmailAddress'			=> '',
											'Type'					=> ''							
										),
						),
						
						'Reference1' 				=> 'glam'.$_POST['oid'],
						'Reference2' 				=> '',
						'Reference3' 				=> '',
						'ForeignHAWB'				=> '',
						'TransportType'				=> 0,
						'ShippingDateTime' 			=> $newDates,
						'DueDate'					=> $newDates,
						'PickupLocation'			=> 'Reception',
						'PickupGUID'				=> '',
						'Comments'					=> 'Shpt 0001',
						'AccountingInstrcutions' 	=> '',
						'OperationsInstructions'	=> '',
						
						'Details' => array(
										'Dimensions' => array(
											'Length'				=> 10,
											'Width'					=> 10,
											'Height'				=> 10,
											'Unit'					=> 'cm',
											
										),
										
										'ActualWeight' => array(
											'Value'					=> 0.5,
											'Unit'					=> 'Kg'
										),
										
										'ProductGroup' 			=> 'DOM',
										'ProductType'			=> $productTYpe,
										'PaymentType'			=> 'P',
										'PaymentOptions' 		=> '',
										'Services'			=> $paymentService,
										'NumberOfPieces'		=> 1,
										'DescriptionOfGoods' 	=> 'Shoes',
										'GoodsOriginCountry' 	=> 'IN',
										
										'CashOnDeliveryAmount' 	=> array(
											'Value'					=> $totalAmt,
											'CurrencyCode'			=> 'INR'
										),
										
										
										'CashAdditionalAmountDescription' => '',
										
										'CustomsValueAmount' => array(
											'Value'					=>  $theorder->order_total,
											'CurrencyCode'			=> 'INR'								
										),
										
										'Items' 				=> array(
											
										)
						),
				),
		),
		
			'ClientInfo'  			=> array(
										'AccountCountryCode'	=> 'IN',
										'AccountEntity'		 	=> 'DEL',
										'AccountNumber'		 	=> '50613983',
										'AccountPin'		 	=> '654654',
										'UserName'			 	=> 'gautamkhurana@getglamr.com',
										'Password'			 	=> '@getglamr',
										'Version'			 	=> 'v1.0'
									),

			'Transaction' 			=> array(
										'Reference1'			=> '001',
										'Reference2'			=> '', 
										'Reference3'			=> '', 
										'Reference4'			=> '', 
										'Reference5'			=> '',									
									),
			'LabelInfo'				=> array(
										'ReportID' 				=> 9201,
										'ReportType'			=> 'URL',
			),
	);
	
	$params['Shipments']['Shipment']['Details']['Items'][] = array(
		'PackageType' 	=> 'Box',
		'Quantity'		=> 1,
		'Weight'		=> array(
				'Value'		=> 0.5,
				'Unit'		=> 'Kg',		
		),
		'Comments'		=> 'Shoes',
		'Reference'		=> ''
	);
	
	//print_r($params['Shipments']->Shipments);
	
	try {
		$auth_call = $soapClient->CreateShipments($params);
	//echo '<pre>';
	//print_r($auth_call);     
                add_post_meta($_POST['oid'], 'invoice_url', $auth_call->Shipments->ProcessedShipment->ShipmentLabel->LabelURL, '');
                echo $auth_call->Shipments->ProcessedShipment->ID.'--'.$auth_call->Shipments->ProcessedShipment->ShipmentLabel->LabelURL;
		die();
	} catch (SoapFault $fault) {
		die('Error : ' . $fault->faultstring);
	}
}
                        
			function load_plugin_textdomain() {
				load_plugin_textdomain( 'wc_shipment_tracking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}

			function admin_styles() {
				wp_enqueue_style( 'shipment_tracking_styles', WPMU_PLUGIN_URL .'/shipment-tracking/assets/css/admin.css' );
			}

			/**
			 * Add the meta box for shipment info on the order page
			 *
			 * @access public
			 */
			function add_meta_box() {
				add_meta_box( 'woocommerce-shipment-tracking', __('Shipment Tracking', 'wc_shipment_tracking'), array( &$this, 'meta_box' ), 'shop_order', 'side', 'high');
			}

			/**
			 * Show the meta box for shipment info on the order page
			 *
			 * @access public
			 */
			function meta_box() {
				global $woocommerce, $post;
                                                          				woocommerce_wp_text_input( array(
					'id' 			=> 'date_shipped',
					'label' 		=> __('Date shipped:', 'wc_shipment_tracking'),
					'placeholder' 	=> 'YYYY-MM-DD',
					'description' 	=> '',
					'class'			=> 'date-picker-field',
					'value'			=> ( $date = get_post_meta( $post->ID, '_date_shipped', true ) ) ? date( 'Y-m-d', $date ) : ''
				) );
                                
                                $data = get_post_custom( $post->ID );
                               $codNonCod =  $this->getCodNonCod($data['_payment_method'][0],$data ['_shipping_postcode'][0]);
                               if(count($codNonCod)>0){
                                   $codText = implode(',',$codNonCod);
                               } else {
                                   $codText = "No Courier service available.";
                               }
                              //  echo '<pre>';
                              //  print_r($data);
                             //  echo $data ['_shipping_postcode'][0];
                              //     echo $data['_payment_method'][0]; 
				// Providers
                                $selected_link = get_post_meta( $post->ID, 'invoice_url', true );
                            echo '<input type="hidden" name="invid" id="invid" value="'.$selected_link.'">'; 
                             echo '<p class="tracking_provider_field"><label for="tracking_provider">' . __('Available Courier(s):', 'wc_shipment_tracking') . '</label>&nbsp;'.$codText.'<br/>';
				echo '<p class="form-field tracking_provider_field"><label for="tracking_provider">' . __('Provider:', 'wc_shipment_tracking') . '</label><br/><select id="tracking_provider" name="tracking_provider" class="chosen_select" style="width:100%;">';
                                       
				echo '<option value="">' . __('Custom Provider', 'wc_shipment_tracking') . '</option>';

				$selected_provider = get_post_meta( $post->ID, '_tracking_provider', true );
                           

				foreach ( $this->providers as $provider_group => $providers ) {

					echo '<optgroup label="' . $provider_group . '">';

					foreach ( $providers as $provider => $url ) {

						echo '<option value="' . sanitize_title( $provider ) . '" ' . selected( sanitize_title( $provider ), $selected_provider, true ) . '>' . $provider . '</option>';

					}

					echo '</optgroup>';

				}

				echo '</select> ';

				woocommerce_wp_text_input( array(
					'id' 			=> 'custom_tracking_provider',
					'label' 		=> __('Provider Name:', 'wc_shipment_tracking'),
					'placeholder' 	=> '',
					'description' 	=> '',
					'value'			=> get_post_meta( $post->ID, '_custom_tracking_provider', true )
				) );

          
				woocommerce_wp_text_input( array(
					'id' 			=> 'tracking_number',
					'label' 		=> __('Tracking number:', 'wc_shipment_tracking'),
					'placeholder' 	=> '',
					'description' 	=> '',
					'value'			=> get_post_meta( $post->ID, '_tracking_number', true )
				) );
                                

				woocommerce_wp_text_input( array(
					'id' 			=> 'custom_tracking_link',
					'label' 		=> __('Tracking link:', 'wc_shipment_tracking'),
					'placeholder' 	=> 'http://',
					'description' 	=> '',
					'value'			=> get_post_meta( $post->ID, '_custom_tracking_link', true )
				) );

                                echo '<img id="loadimg" style="display:none;" src="'.plugins_url("/shipment-tracking/assets/images/loading.gif").'" />';

				// Live preview
                           //     echo ;
                                if($selected_link ==''){
                                echo '<a id="ppid" style="display:none;" href="" target="_blank">' . __('Click to print packing slip.', 'wc_shipment_tracking') . '</a>';
                                } else {
                                  echo '<a id="ppid" style="display:block;" href="'.$selected_link.'" target="_blank">' . __('Click to print packing slip.', 'wc_shipment_tracking') . '</a>';    
                                }
                                echo '<p class="preview_tracking_link">' . __('Preview:', 'wc_shipment_tracking') . ' <a href="" target="_blank">' . __('Click here to track your shipment', 'wc_shipment_tracking') . '</a></p>';

				$provider_array = array();

				foreach ( $this->providers as $providers ) {
					foreach ( $providers as $provider => $format ) {
						$provider_array[sanitize_title( $provider )] = urlencode( $format );
					}
				}

				$woocommerce->add_inline_js("
					jQuery('p.custom_tracking_link_field, p.custom_tracking_provider_field').hide();

					jQuery('input#custom_tracking_link, input#tracking_number, #tracking_provider').change(function(){
                                        //alert('test2');

						var tracking = jQuery('input#tracking_number').val();
						var provider = jQuery('#tracking_provider').val();
						var providers = jQuery.parseJSON( '" . json_encode( $provider_array ) . "' );
                                                var dateSelected = jQuery('#date_shipped').val();    
                                                if(tracking ==''){        
                                                if(provider=='aramex'){
                                               jQuery('#loadimg').show();
                                                                                    var data = {
                                            action: 'my_action',
                                            whatever: 1234,
                                            oid:".$post->ID.",
                                            selecteddate:".dateSelected."    
                                    };

                                                   jQuery.post(ajaxurl, data, function(response) {
                                              //     alert(response);
                                                   var adResponse = response.split('--');
                                                   jQuery('#tracking_number').val(adResponse[0]);
                                                 jQuery('#ppid').show();
                                                 jQuery('#ppid').attr('href',adResponse[1]);
                                                 jQuery('#invid').val(adResponse[1]);
                                                   jQuery('#loadimg').hide();
                                    });
                                                }else if(provider=='blue-dart'){
                                       // alert('ureka');
                                                    jQuery('#loadimg').show();
                                                                            var data = {
                                    action: 'bluedart_action',
                                    whatever: 1234,
                                    oid:".$post->ID.",
                                    selecteddate:".dateSelected."    
                            };

                                           jQuery.post(ajaxurl, data, function(response) {
                                           response =   response.slice(0, -1);
                                        //  alert(response);
                                           var adResponse = response.split('--');
                                           jQuery('#tracking_number').val(adResponse[0]);
                                         jQuery('#ppid').show();
                                         jQuery('#ppid').attr('href',adResponse[1]);
                                         jQuery('#invid').val(adResponse[1]);
                                           jQuery('#loadimg').hide();
                            });  


                                        }}
                                                    
						var postcode = jQuery('#_shipping_postcode').val();

						if ( ! postcode )
							postcode = jQuery('#_billing_postcode').val();

						postcode = encodeURIComponent( postcode );

						var link = '';

						if ( providers[ provider ] ) {
							link = providers[provider];
							link = link.replace( '%251%24s', tracking );
							link = link.replace( '%252%24s', postcode );
							link = decodeURIComponent( link );

							jQuery('p.custom_tracking_link_field, p.custom_tracking_provider_field').hide();
						} else {
							jQuery('p.custom_tracking_link_field, p.custom_tracking_provider_field').show();

							link = jQuery('input#custom_tracking_link').val();
						}

						if ( link ) {
							jQuery('p.preview_tracking_link a').attr('href', link);
							jQuery('p.preview_tracking_link').show();
						} else {
							jQuery('p.preview_tracking_link').hide();
						}

					}).change();
				");
			}

			/**
			 * Order Downloads Save
			 *
			 * Function for processing and storing all order downloads.
			 */
			function save_meta_box( $post_id, $post ) {
                             global $wpdb;
				if ( isset( $_POST['tracking_number'] ) ) {

 $theorder = new WC_Order( $post_id );
					// Download data
					$tracking_provider        = woocommerce_clean( $_POST['tracking_provider'] );
					$custom_tracking_provider = woocommerce_clean( $_POST['custom_tracking_provider'] );
					$custom_tracking_link     = woocommerce_clean( $_POST['custom_tracking_link'] );
					$tracking_number          = woocommerce_clean( $_POST['tracking_number'] );
					$date_shipped             = woocommerce_clean( strtotime( $_POST['date_shipped'] ) );

					// Update order data
					update_post_meta( $post_id, '_tracking_provider', $tracking_provider );
					update_post_meta( $post_id, '_custom_tracking_provider', $custom_tracking_provider );
					update_post_meta( $post_id, '_tracking_number', $tracking_number );
					update_post_meta( $post_id, '_custom_tracking_link', $custom_tracking_link );
					update_post_meta( $post_id, '_date_shipped', $date_shipped );
                                        update_post_meta( $post_id, 'invoice_url', $_POST['invid'] );
                                        
                                        if($tracking_provider=='blue-dart'){
                                  //  echo "update bluedart_prepaid set `assigned`='1',`orderid`='".$post_id."' where `abw`='".trim($tracking_number)."'"; exit;
                                         if($theorder->payment_method=='cod'){
                                       // echo "update bluedart_cod set `assigned`='1',`orderid`='".$post_id."' where `abw`='".trim($tracking_number)."'"; exit;
                                        $wpdb->get_row( "update bluedart_cod set `assigned`='1',`orderid`='".$post_id."' where `CAWBNO`='".trim($tracking_number)."'") ;
                                       
                                       
                                             } else {
                                        $wpdb->get_row("update bluedart_prepaid set `assigned`='1',`orderid`='".$post_id."' where `CAWBNO`='".trim($tracking_number)."'") ;           
                                             }
                                }         
                                        
				}
			}

			/**
			 * Display Shipment info in the frontend (order view/tracking page).
			 *
			 * @access public
			 */
			function display_tracking_info( $order_id ) {

				$tracking_provider = get_post_meta( $order_id, '_tracking_provider', true );
				$tracking_number   = get_post_meta( $order_id, '_tracking_number', true );
				$date_shipped      = get_post_meta( $order_id, '_date_shipped', true );
				$postcode          = get_post_meta( $order_id, '_shipping_postcode', true );
                                $selected_link = get_post_meta( $order_id, 'invoice_url', true );
				if ( ! $postcode )
					$postcode		= get_post_meta( $order_id, '_billing_postcode', true );

				if ( ! $tracking_number )
					return;

				if ( $date_shipped )
					$date_shipped = ' ' . sprintf( __( 'on %s', 'wc_shipment_tracking' ), date_i18n( __( 'l jS F Y', 'wc_shipment_tracking'), $date_shipped ) );

				$tracking_link = '';

				if ( $tracking_provider ) {

					$link_format = '';

					foreach ( $this->providers as $providers ) {
						foreach ( $providers as $provider => $format ) {
							if ( sanitize_title( $provider ) == $tracking_provider ) {
								$link_format = $format;
								$tracking_provider = $provider;
								break;
							}
						}
						if ( $link_format ) break;
					}

					if ( $link_format )
						$tracking_link = sprintf( sprintf( '<a href="%s">' . __('Click here to track your shipment', 'wc_shipment_tracking') . '.</a>', $link_format ), $tracking_number, urlencode( $postcode ) );

					$tracking_provider = ' ' . __('via', 'wc_shipment_tracking') . ' <strong>' . $tracking_provider . '</strong>';

					echo wpautop( sprintf( __('Your order was shipped%s%s. Tracking number %s. %s', 'wc_shipment_tracking'), $date_shipped, $tracking_provider, $tracking_number, $tracking_link ) );

				} else {

					$custom_tracking_link     = get_post_meta( $order_id, '_custom_tracking_link', true );
					$custom_tracking_provider = get_post_meta( $order_id, '_custom_tracking_provider', true );

					if ( $custom_tracking_provider )
						$tracking_provider = ' ' . __('via', 'wc_shipment_tracking') . ' <strong>' . $custom_tracking_provider . '</strong>';
					else
						$tracking_provider = '';

					if ( $custom_tracking_link ) {
						$tracking_link = sprintf( '<a href="%s">' . __('Click here to track your shipment', 'wc_shipment_tracking') . '.</a>', $custom_tracking_link );
					} elseif ( strstr( $tracking_number, '<a' ) ) {
						$tracking_link = sprintf( '<a href="%s">%s.</a>', $tracking_number, $tracking_number );
					} else {
						$tracking_link = '';
					}

					echo wpautop( sprintf( __('Your order was shipped%s%s. Tracking number %s. %s', 'wc_shipment_tracking'), $date_shipped, $tracking_provider, $tracking_number, $tracking_link ) );
				}

			}

			/**
			 * Display shipment info in customer emails.
			 *
			 * @access public
			 * @return void
			 */
			function email_display( $order ) {
				$this->display_tracking_info( $order->id );
			}

                        function getCodNonCod($modeofpayment,$pincode){
                            global $wpdb;
                            switch($modeofpayment){

                                case 'cod':
                             	$aramax = $wpdb->get_var("SELECT count(*) as aramax FROM wp_cod_aramax WHERE pincode = '".$pincode."'");
	$dtdc = $wpdb->get_var("SELECT count(*) as dtdc FROM wp_cod_bluedart WHERE pincode = '".$pincode."'");
	 $quantium = $wpdb->get_var("SELECT count(*) as quantium FROM wp_cod_quantium WHERE pincode = '".$pincode."'");
	if($aramax > 0){
            $avialableCourier[] = 'Aramex';
        }

        if($quantium > 0){
          //  $avialableCourier[] = 'Quantum';
        }
        
                if($dtdc > 0){
            $avialableCourier[] = 'Bluedart';
        }
        
        /*
        if($aramax < 1 && $dtdc < 1 && $quantium < 1){
		echo 0;exit;
	}else{
		echo 1; exit;
	}
         *
         */
                                break;

                                default:
                                $aramax = $wpdb->get_var("SELECT count(*) as aramax FROM wp_cod_aramax WHERE pincode = '".$pincode."'");
	$dtdc = $wpdb->get_var("SELECT count(*) as dtdc FROM wp_cod_bluedart WHERE pincode = '".$pincode."'");
	 $quantium = $wpdb->get_var("SELECT count(*) as quantium FROM wp_cod_quantium WHERE pincode = '".$pincode."'");
	if($aramax > 0){
            $avialableCourier[] = 'Aramex';
        }

        if($quantium > 0){
         //   $avialableCourier[] = 'Quantum';
        }
            if($dtdc > 0){
            $avialableCourier[] = 'Bluedart';
        }
         
                                break;

                            }
                  return $avialableCourier; 

                        }


		}

	}

	/**
	 * Register this class globally
	 */
	$GLOBALS['WC_Shipment_Tracking'] = new WC_Shipment_Tracking();

}