<?php if (!defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			{@link http://eventespresso.com/support/terms-conditions/}   * see Plugin Licensing *
 * @ link					{@link http://www.eventespresso.com}
 * @ since		 		4.0
 *
 * ------------------------------------------------------------------------
 *
 * EE_Payment_Method class
 * Shoudl be parent of all paymetn method classes
 *
 * @package			Event Espresso
 * @subpackage		includes/classes/EE_Checkin.class.php
 * @author			Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class EE_Payment_Method extends EE_Base_Class{
	
	/** ID @var PMD_ID*/ 
	protected $_PMD_ID = NULL;
	/** @var PMD_type string pointing to which PMT_Base child that holds all the payment-processing funcitonality of 
	 * the particular payment method. Eg Paypal_Standard, which indicates the type object is EE_PMT_Paypal_Standard */ 
	protected $_PMD_type = NULL;
	/** Name @var PMD_name - a name used for external (frontend) display only*/ 
	protected $_PMD_name = NULL;
	/** Description @var PMD_desc description for external (frontend) description*/ 
	protected $_PMD_desc = NULL;
	/** Name @var PMD_admin_name - a name used for interal (admin) display only*/ 
	protected $_PMD_admin_name = NULL;
	/** Description @var PMD_admin_desc description used for internal (admin) display only*/ 
	protected $_PMD_admin_desc = NULL;
	/** Slug @var PMD_slug*/ 
	protected $_PMD_slug = NULL;
	/** Order @var PMD_order*/ 
	protected $_PMD_order = NULL;
	/** Surcharge Price @var PRC_ID*/ 
	protected $_PRC_ID = NULL;
	/** Debug Mode On? @var PMD_debug_model*/ 
	protected $_PMD_debug_mode = NULL;
	/** Logging On? @var PMD_logging*/ 
	protected $_PMD_logging = NULL;
	/** User ID @var PMD_wp_user_id*/ 
	protected $_PMD_wp_user_id = NULL;
	/** Open by Default? @var PMD_open_by_default*/ 
	protected $_PMD_open_by_default = NULL;
	/** Button URL @var PMD_button_url*/ 
	protected $_PMD_button_url = NULL;
	/** Preferred Currency @var PMD_preferred_currency*/ 
	protected $_PMD_preferred_currency = NULL;
	/** @var $_PMD_scope
	 */
	protected $_PMD_scope = NULL;
	
	/**
	 * Surcharge for using this payment method
	 * @var EE_Price
	 */
	protected $_Price = NULL;
	/**
	 * ALl events that allow the use of this gateway
	 * @var EE_Event[]
	 */
	protected $_Event = array();
	/**
	 * Payments made using this gateway
	 * @var EE_Payment[]
	 */
	protected $_Payment = array();
	/**
	 * Currenices which can be use dby htis paymetn method
	 * @var EE_Currency[]
	 */
	protected $_Currency = array();
	/**
	 * Payment Method type object, which has all the info about this type of payment method,
	 * including functions for processing payments, to get settings forms, etc.
	 * @var EEPM_Base
	 */
	protected $_type_obj = NULL;


	/**
	 * 
	 * @param type $props_n_values
	 * @param type $timezone
	 * @return EE_Payment_Method
	 */
	public static function new_instance( $props_n_values = array()) {
		$classname = __CLASS__;
		$has_object = parent::_check_for_object( $props_n_values, $classname );
		return $has_object ? $has_object : new self( $props_n_values, FALSE );
	}

	
	public static function new_instance_from_db ( $props_n_values = array()) {
		return new self( $props_n_values, TRUE );
	}
	/**
	 * Checks if there is a payment method class of the given 'PMD_type', and if so returns the classname.
	 * Otherwise returns a normal EE_Payment_Method
	 * @param array $props_n_values where 'PMD_type' is a gateway name like 'Paypal_Standard','Invoice',etc (basically
	 * the classname minus 'EEPM_')
	 * @return string
	 */
	private static function _payment_method_type($props_n_values){
		EE_Registry::instance()->load_lib('Payment_Method_Manager');
		$type_string = isset($props_n_values['PMD_type']) ? $props_n_values['PMD_type'] : NULL;
		if(EE_Payment_Method_Manager::instance()->payment_method_exists($type_string)){
			return 'EEPM_'.$type_string;
		}else{
			return __CLASS__;
		}
	}

	/**
	 * Gets whether this payment method can be used anywhere at all (ie frontend cart, admin, etc)
	 * @return boolean
	 */
	function active() {
		return array_intersect(array_keys(EEM_Payment_Method::instance()->scopes()),$this->scope());
	}
	/**
	 * Sets this PM as active by making it usable wihtin the CART scope. Offline gateways
	 * are also usable from the admin-scope as well. DOES NOT SAVE it
	 */
	function set_active(){
		$default_scopes = array(EEM_Payment_Method::scope_cart);
		if($this->type_obj() &&
			$this->type_obj()->payment_occurs() == EE_PMT_Base::offline){
			$default_scopes[] = EEM_Payment_Method::scope_admin;
		}
		$this->set_scope($default_scopes);
	}
	
	/**
	 * Makes this paymetn method apply to NO scopes at all. DOES NOT SAVE it.
	 * @return boolean
	 */
	function deactivate(){
		return $this->set_scope(array());
	}

	/**
	 * Gets button_url
	 * @return string
	 */
	function button_url() {
		return $this->get('PMD_button_url');
	}

	/**
	 * Sets button_url
	 * @param string $button_url
	 * @return boolean
	 */
	function set_button_url($button_url) {
		return $this->set('PMD_button_url', $button_url);
	}
	/**
	 * Gets debug_mode
	 * @return boolean
	 */
	function debug_mode() {
		return $this->get('PMD_debug_mode');
	}

	/**
	 * Sets debug_mode
	 * @param boolean $debug_mode
	 * @return boolean
	 */
	function set_debug_mode($debug_mode) {
		return $this->set('PMD_debug_mode', $debug_mode);
	}
	/**
	 * Gets description
	 * @return string
	 */
	function description() {
		return $this->get('PMD_desc');
	}

	/**
	 * Sets description
	 * @param string $description
	 * @return boolean
	 */
	function set_description($description) {
		return $this->set('PMD_desc', $description);
	}
	/**
	 * Gets logging
	 * @return boolean
	 */
	function logging() {
		return $this->get('PMD_logging');
	}

	/**
	 * Sets logging
	 * @param boolean $logging
	 * @return boolean
	 */
	function set_logging($logging) {
		return $this->set('PMD_logging', $logging);
	}
	/**
	 * Gets name
	 * @return string
	 */
	function name() {
		return $this->get('PMD_name');
	}

	/**
	 * Sets name
	 * @param string $name
	 * @return boolean
	 */
	function set_name($name) {
		return $this->set('PMD_name', $name);
	}
	/**
	 * Gets open_by_default
	 * @return boolean
	 */
	function open_by_default() {
		return $this->get('PMD_open_by_default');
	}

	/**
	 * Sets open_by_default
	 * @param boolean $open_by_default
	 * @return boolean
	 */
	function set_open_by_default($open_by_default) {
		return $this->set('PMD_open_by_default', $open_by_default);
	}
	/**
	 * Gets order
	 * @return int
	 */
	function order() {
		return $this->get('PMD_order');
	}

	/**
	 * Sets order
	 * @param int $order
	 * @return boolean
	 */
	function set_order($order) {
		return $this->set('PMD_order', $order);
	}
	/**
	 * Gets preferred_currency
	 * @return string
	 */
	function preferred_currency() {
		return $this->get('PMD_preferred_currency');
	}

	/**
	 * Sets preferred_currency
	 * @param string $preferred_currency
	 * @return boolean
	 */
	function set_preferred_currency($preferred_currency) {
		return $this->set('PMD_preferred_currency', $preferred_currency);
	}
	/**
	 * Gets slug
	 * @return string
	 */
	function slug() {
		return $this->get('PMD_slug');
	}

	/**
	 * Sets slug
	 * @param string $slug
	 * @return boolean
	 */
	function set_slug($slug) {
		return $this->set('PMD_slug', $slug);
	}
	/**
	 * Gets type
	 * @return string
	 */
	function type() {
		return $this->get('PMD_type');
	}

	/**
	 * Sets type
	 * @param string $type
	 * @return boolean
	 */
	function set_type($type) {
		return $this->set('PMD_type', $type);
	}
	/**
	 * Gets wp_user_id
	 * @return int
	 */
	function wp_user_id() {
		return $this->get('PMD_wp_user_id');
	}

	/**
	 * Sets wp_user_id
	 * @param int $wp_user_id
	 * @return boolean
	 */
	function set_wp_user_id($wp_user_id) {
		return $this->set('PMD_wp_user_id', $wp_user_id);
	}
	
	/**
	 * Gets admin_name
	 * @return string
	 */
	function admin_name() {
		return $this->get('PMD_admin_name');
	}

	/**
	 * Sets admin_name
	 * @param string $admin_name
	 * @return boolean
	 */
	function set_admin_name($admin_name) {
		return $this->set('PMD_admin_name', $admin_name);
	}
	/**
	 * Gets admin_desc
	 * @return string
	 */
	function admin_desc() {
		return $this->get('PMD_admin_desc');
	}

	/**
	 * Sets admin_desc
	 * @param string $admin_desc
	 * @return boolean
	 */
	function set_admin_desc($admin_desc) {
		return $this->set('PMD_admin_desc', $admin_desc);
	}
	/**
	 * Gets scope
	 * @return array
	 */
	function scope() {
		return $this->get('PMD_scope');
	}

	/**
	 * Sets scope
	 * @param array $scope
	 * @return boolean
	 */
	function set_scope($scope) {
		return $this->set('PMD_scope', $scope);
	}

	
	/**
	 * Gets the payment method type for this payment method instance
	 * @return EE_PMT_Base
	 * @throws EE_Error
	 */
	public function type_obj(){
		if( ! $this->_type_obj ) {
			EE_Registry::instance()->load_lib( 'Payment_Method_Manager' );
			if ( EE_Payment_Method_Manager::instance()->payment_method_exists( $this->type() )) {
				$class_name = EE_Payment_Method_Manager::instance()->payment_method_class_from_type( $this->type() );
				if ( ! class_exists( $class_name )) {
					throw new EE_Error(sprintf(__("There is no payment method type of class '%s', did you deactivate an EE addon?", "event_espresso"),$class_name));
				}
				$r = new ReflectionClass( $class_name );
				$this->_type_obj = $r->newInstanceArgs( array( $this ));
			} else {
				throw new EE_Error( sprintf( __( 'A payment method of type "%s" does not exist', 'event_espresso' ), $this->type() ));
			}
		}
		return $this->_type_obj;
	}	
	
	/**
	 * Returns a simple arrya of key-value pairs combining the payment method's fields (without the 'PMD_' prefix) 
	 * and the extra meta. Mostly used for passing off ot gateways.	 * 
	 * @return array
	 */
	public function settings_array(){
		$fields = $this->model_field_array();
		$extra_metas = $this->all_extra_meta_array();
		//remove the model's prefix from the fields
		$combined_settings_array = array();
		foreach($fields as $key => $value){
			if(strpos($key, 'PMD_')===0){
				$key_sans_model_prefix = str_replace('PMD_', '', $key);
				$combined_settings_array [$key_sans_model_prefix] = $value;
			}
		}
		$combined_settings_array = array_merge($extra_metas,$combined_settings_array);
		return $combined_settings_array;
	}
	
	/**
	 * Gets the HTML for displaying the payment method on a page.
	 * @param string $url
	 * @param string $css_class
	 * @return string of HTML for displaying the button
	 */
	public function button_html( $url = '', $css_class = '' ){
		$payment_occurs = $this->type_obj()->payment_occurs();
		return '
		 <div id="' . $this->slug() . '-payment-option-dv" class="'. $payment_occurs .'-payment-gateway reg-page-payment-option-dv' . $css_class . '">
			<a id="payment-gateway-button-' . $this->slug() . '" class="reg-page-payment-option-lnk" rel="' . $this->slug() . '" href="' . $url . '" >
				<img src="' . $this->button_url() . '" alt="Pay using ' . $this->get_pretty('PMD_name','form_input') . '" />
			</a>
		</div>
';
	}
	/**
	 * Gets all the currenices which are an option for this payment method
	 * (as defined by the gateway and the currently active currencies)
	 * @return EE_Currency[]
	 */
	public function get_all_usable_currencies(){
		return EEM_Currency::instance()->get_all_currencies_usable_by($this->type_obj());
	}
}