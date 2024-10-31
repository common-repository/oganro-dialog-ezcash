<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Dialog Ezcash Bank IPG
Plugin URI: ezcashipg.oganro.net
Description: Dialog Ezcash from Oganro (Pvt)Ltd.
Version: 1.1
Author: Oganro
Author URI: www.oganro.com
*/

add_action('plugins_loaded', 'woocommerce_ezcash_gateway', 0);

function woocommerce_ezcash_gateway(){
  if(!class_exists('WC_Payment_Gateway')) return;

  class WC_Ezcash extends WC_Payment_Gateway{
    public function __construct(){
	  $plugin_dir = plugin_dir_url(__FILE__);
      $this->id = 'EzcashIPG';	  
	  $this->icon = apply_filters('woocommerce_Paysecure_icon', ''.$plugin_dir.'ezcash.png');
      $this->medthod_title = 'EzcashIPG';
      $this->has_fields = false;
 
      $this->init_form_fields();
      $this->init_settings(); 
	  
      $this->title 			= $this-> settings['title'];
      $this->description 	= $this-> settings['description'];
      
      $this->merchant_id 	= $this-> settings['merchant_id'];	  
      $this->amount 		= $this-> settings['amount'];
      $this->redirect_url 	= $this-> settings['redirect_url'];
      $this->return_url 	= $this-> settings['return_url'];      
	  	  
	  $this->sucess_responce_code	= $this-> settings['sucess_responce_code'];	  
	  $this->responce_url_sucess	= $this-> settings['responce_url_sucess'];
	  $this->responce_url_fail		= $this-> settings['responce_url_fail'];	  	  
	  $this->checkout_msg			= $this-> settings['checkout_msg'];	  
	   
      $this->msg['message'] 	= "";
      $this->msg['class'] 		= "";
 
      add_action('init', array(&$this, 'check_EzcashIPG_response'));	  
	  	  
		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
        	add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array( &$this, 'process_admin_options' ) );
		} else {
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
        }
      add_action('woocommerce_receipt_EzcashIPG', array(&$this, 'receipt_page'));
	 
   }
	
    function init_form_fields(){
 
       $this -> form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'ogn'),
                    'type' => 'checkbox',
                    'label' => __('Enable Ezcash IPG Module.', 'ognro'),
                    'default' => 'no'),
					
                'title' => array(
                    'title' => __('Title:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'ognro'),
                    'default' => __('Ezcash IPG', 'ognro')),
				
				'description' => array(
                    'title' => __('Description:', 'ognro'),
                    'type'=> 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'ognro'),
                    'default' => __('Ezcash IPG', 'ognro')),	
					
				'merchant_id' => array(
                    'title' => __('PG Merchant Id:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('Unique ID for the merchant acc, given by bank.', 'ognro'),
                    'default' => __('', 'ognro')),
				
				'amount' => array(
                    'title' => __('Amount:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('', 'ognro'),
                    'default' => __('', 'ognro')),
				
				'redirect_url' => array(
                    'title' => __('Redirect URL:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('', 'ognro'),
                    'default' => __('initiatePaymentCapture#sale', 'ognro')),
				
                'return_url' => array(
                    'title' => __('Return URL:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('', 'ognro'),
                    'default' => __('', 'ognro')),
									
				'sucess_responce_code' => array(
                    'title' => __('Sucess responce code :', 'ognro'),
                    'type'=> 'text',
                    'description' => __('2 - Transaction Passed', 'ognro'),
                    'default' => __('2', 'ognro')),	  
								
				'checkout_msg' => array(
                    'title' => __('Checkout Message:', 'ognro'),
                    'type'=> 'textarea',
                    'description' => __('Message display when checkout'),
                    'default' => __('Thank you for your order, please click the button below to pay with the secured Ezcash Bank payment gateway.', 'ognro')),		
					
				'responce_url_sucess' => array(
                    'title' => __('Sucess redirect URL :', 'ognro'),
                    'type'=> 'text',
                    'description' => __('After payment is sucess redirecting to this page.'),
                    'default' => __('http://your-site.com/thank-you-page/', 'ognro')),
				
				'responce_url_fail' => array(
                    'title' => __('Fail redirect URL :', 'ognro'),
                    'type'=> 'text',
                    'description' => __('After payment if there is an error redirecting to this page.', 'ognro'),
                    'default' => __('http://your-site.com/error-page/', 'ognro'))	
            );
    }
 
	public function admin_options(){
    	echo '<h3>'.__('Dialog Ezcash payment online', 'ognro').'</h3>';
        echo '<p>'.__('<a target="_blank" href="http://www.oganro.com/">Oganro</a> is a fresh and dynamic web design and custom software development company with offices based in East London, Essex, Brisbane (Queensland, Australia) and in Colombo (Sri Lanka).').'</p>';
        echo'<a href="http://www.oganro.com/wordpress-plug-in-support" target="_blank"><img class="wpimage" alt="payment gateway" src="../wp-content/plugins/sampath-bank-ipg/plug-inimg.jpg" width="100%"></a>';
        echo '<table class="form-table">';        
        $this->generate_settings_html();
        echo '</table>'; 
    }
	

    function payment_fields(){
        if($this -> description) echo wpautop(wptexturize($this -> description));
    }

    function receipt_page($order){        		
		global $woocommerce;
        $order_details = new WC_Order($order);
        
        echo $this->generate_ipg_form($order);		
		echo '<br>'.$this->checkout_msg.'</b>';        
    }
    	
    public function generate_ipg_form($order_id){
 
        global $wpdb;
        global $woocommerce;
        
        $order          = new WC_Order($order_id);
		$productinfo    = "Order $order_id";        		
		$curr_symbole 	= get_woocommerce_currency();		
		
        $ogn_ez_sensitiveData   = $this->merchant_id.'|'.$order_id.'|'.($order -> order_total).'|'.$this->return_url; // query string

$ogn_ez_publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCW8KV72IMdhEuuEks4FXTiLU2o
bIpTNIpqhjgiUhtjW4Si8cKLoT7RThyOvUadsgYWejLg2i0BVz+QC6F7pilEfaVS
L/UgGNeNd/m5o/VoX9+caAIyu/n8gBL5JX6asxhjH3FtvCRkT+AgtTY1Kpjb1Btp
1m3mtqHh6+fsIlpH/wIDAQAB
-----END PUBLIC KEY-----
EOD;

$ogn_ez_encrypted = '';
if (!openssl_public_encrypt($ogn_ez_sensitiveData, $ogn_ez_encrypted, $ogn_ez_publicKey))
die('Failed to encrypt data');
$ogn_ez_invoice = base64_encode($ogn_ez_encrypted); // encoded encrypted query string    
        
				
		
        $form_args = array(
		  'merchantInvoice' => $ogn_ez_invoice
		);
		  
        $form_args_array = array();
        foreach($form_args as $key => $value){
          $form_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
        }
        return '<p>'.$percentage_msg.'</p>
		<p>Total amount will be <b>'.$curr_symbole.' '.number_format(($order->order_total)).'</b></p>
		<form action="'.$this -> redirect_url.'" method="post" id="merchantInvoice">
            ' . implode('', $form_args_array) . '
            <input type="submit" class="button-alt" id="submit_ipg_payment_form" value="'.__('Pay with Dialog Ezcash', 'ognro').'" /> 
			<a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'ognro').'</a>            
            </form>'; 
    }
    
    function process_payment($order_id){
        $order = new WC_Order($order_id);
        return array('result' => 'success', 'redirect' => add_query_arg('order',           
		   $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
        );
    }
 

function check_EzcashIPG_response(){				
        global $wpdb;
        global $woocommerce;
        
		if(isset($_POST['merchantReciept'])){
            $decrypted = '';
            $encrypted = $_POST['merchantReciept'];
$privateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJuIUgSzNuWm3US8
0brZr/5cMSPue9f0IwUrEhka1gLlC4uQon6QjQem4TWQ8anoMKYwfYgRnCGQsbrT
KwOApwTA4Bt6dg9jKXlIE6rXqqO6g2C/uD+G2p+W4k0ZI1isuqqjjkup5ZPkNaeW
R9/961Qx3CyrWDk6n0OkzDJ6UNzLAgMBAAECgYEAh+/dv73jfVUaj7l4lZct+2MY
kA8grt7yvNGoP8j0xBLsxE7ltzkgClARBoBot9f4rUg0b3j0vWF59ZAbSDRpxJ2U
BfWEtlXWvN1V051KnKaOqE8TOkGK0PVWcc6P0JhPrbmOu9hhAN3dMu+jd7ABFKgC
4b8EIlHA8bl8po8gwAECQQDliMBTAzzyhB55FMW/pVGq9TBo2oXQsyNOjEO+rZNJ
zIwJzFrFhvuvFj7/7FekDAKmWgqpuOIk0NSYfHCR54FLAkEArXc7pdPgn386ikOc
Nn3Eils1WuP5+evoZw01he4NSZ1uXNkoNTAk8OmPJPz3PrtB6l3DUh1U/DEZjIiI
7z5igQJAFXvFNH/bFn/TMlYFZDie+jdUvpulZrE9nr52IMSyQngIq2obHN3TdMHK
R73hPhN5tAQ9d0E8uWFqZJNRHfbjHQJASY7pNV3Ov/QE0ALxqE3W3VDmJD/OjkOS
jriUPNIAwnnHBgp0OXHMCHkSYX4AHpLr1cWjARw9IKB1lBmF7+YFgQJAFqUgYj11
ioyuSf/CSotPIC7YyNEnr+TK2Ym0N/EWzqNXoOCDxDTgoWLQxM3Nfr65tWtV2097
BjCbFfbui/IyUw==
-----END PRIVATE KEY-----
EOD;
$encrypted = base64_decode($encrypted); // decode the encrypted query string
if(!openssl_private_decrypt($encrypted, $decrypted, $privateKey))


$ogn_ezcash_dec_arr = explode("|",$decrypted);

$str = $decrypted;
$ogn_ezcash_dec_arr = (explode('|',$str));

global $wpdb;

    
    $order_id = $_POST['merchant_reference_no'];
			
			if($order_id != ''){				
				$order 	= new WC_Order($order_id);
								
				$status = $ogn_ezcash_dec_arr[1];
				if($this->sucess_responce_code == $status){
						
				$table_name = $wpdb->prefix . 'ogn_ezcash_ipg';	
                $wpdb->update( 
                $table_name, 
                array(    					
                	'status' => $ogn_ezcash_dec_arr[1],					
                	'transaction_msg' => $ogn_ezcash_dec_arr[2],                	
                	'message_data' => $decrypted	
                    ), 
                	array( 'transaction_id' => $ogn_ezcash_dec_arr[0] ));
									
                    $order->add_order_note('Ezcash payment successful<br/>Unnique Id from Sampath IPG: '.$_POST['transaction_id']);
                    $order->add_order_note($this->msg['message']);
                    $woocommerce->cart->empty_cart();
					
					$mailer = $woocommerce->mailer();

					$admin_email = get_option( 'admin_email', '' );

$message = $mailer->wrap_message(__( 'Order confirmed','woocommerce'),sprintf(__('Order '.$_POST["transaction_id"].' has been confirmed', 'woocommerce' ), $order->get_order_number(), $posted['reason_code']));	
$mailer->send( $admin_email, sprintf( __( 'Payment for order %s confirmed', 'woocommerce' ), $order->get_order_number() ), $message );					
					
					
$message = $mailer->wrap_message(__( 'Order confirmed','woocommerce'),sprintf(__('Order '.$_POST["transaction_id"].' has been confirmed', 'woocommerce' ), $order->get_order_number(), $posted['reason_code']));	
$mailer->send( $order->billing_email, sprintf( __( 'Payment for order %s confirmed', 'woocommerce' ), $order->get_order_number() ), $message );

					$order->payment_complete();
					wp_redirect( $this->responce_url_sucess, 200 ); exit;
					
				}else{					
					global $wpdb;
                    
                    $order->update_status('failed');
                    $order->add_order_note('Failed - Code'.$_POST['pgErrorCode']);
                    $order->add_order_note($this->msg['message']);
							
					$table_name = $wpdb->prefix . 'ogn_ezcash_ipg';	
					$wpdb->update( 
					$table_name, 
					array(    					
                	'status' => $ogn_ezcash_dec_arr[1],					
                	'transaction_msg' => $ogn_ezcash_dec_arr[2],                	
                	'message_data' => $decrypted	
                    ), 
                	array( 'transaction_id' => $ogn_ezcash_dec_arr[0] ));
					
					wp_redirect( $this->responce_url_fail, 200 ); exit;
				}				 
			}
			
		}
    }
    
    function get_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
            $prefix = '';            
            if ($indent) {
                $has_parent = $page->post_parent;
                while($has_parent) {
                    $prefix .=  ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }            
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }
}


if(isset($_POST['transaction_type_code']) && isset($_POST['status']) && isset($_POST['merchant_reference_no'])){
	$WC = new WC_Ezcash();
}

   
   function woocommerce_add_ezcash_gateway($methods) {
       $methods[] = 'WC_Ezcash';
       return $methods;
   }
	 	
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_ezcash_gateway' );
}

	global $jal_db_version;
	$jal_db_version = '1.0';
	
	function jal_install_ezcash() {		
		global $wpdb;
		global $jal_db_version;
	
		$table_name = $wpdb->prefix . 'ezcash_ipg';
		$charset_collate = '';
	
		if ( ! empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
	
		if ( ! empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}
	
		$sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        transaction_id text NOT NULL,        		
		status text NOT NULL,
        transaction_msg text NOT NULL,
        amount VARCHAR(20) NOT NULL,        
        message_data text NOT NULL,
        message_date text NOT NULL,
        transaction_email text NOT NULL,
        UNIQUE KEY id (id)
				) $charset_collate;";
				
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		add_option( 'jal_db_version', $jal_db_version );
	}
	
	function jal_install_data_ezcash() {
		global $wpdb;
		
		$welcome_name = 'Ezcash IPG';
		$welcome_text = 'Congratulations, you just completed the installation!';
		
		$table_name = $wpdb->prefix . 'ezcash_ipg';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ), 
				'name' => $welcome_name, 
				'text' => $welcome_text, 
			) 
		);
	}
	
	register_activation_hook( __FILE__, 'jal_install_ezcash' );
	register_activation_hook( __FILE__, 'jal_install_data_ezcash' );