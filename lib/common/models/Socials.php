<?php

namespace common\models;

use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;
use common\models\Customer;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\authclient\OAuth1;
use yii\authclient\OAuth2;
use yii\authclient\OpenId;

class Socials {
    private static $defined_modules = [
        [
            'name'  => 'google',
            'class' => 'yii\authclient\clients\Google',
            'site'  => 'https://console.developers.google.com',
        ],
        [
            'name'  => 'facebook',
            'class' => 'yii\authclient\clients\Facebook',
            'site'  => 'https://developers.facebook.com',
        ],
        [
            'name'  => 'twitter',
            'class' => 'yii\authclient\clients\Twitter',
            'site'  => 'https://apps.twitter.com',
        ],
        [
            'name'  => 'linkedin',
            'class' => 'yii\authclient\clients\LinkedIn',
            'site'  => 'https://www.linkedin.com/secure/developer',
        ],
        [
            'name'  => 'instagram',
            'class' => 'yii\authclient\clients\Instagram',
            'site'  => 'https://www.instagram.com/developer',
        ]
    ];
    
    private $client;
    
    const HASHCODE = 'kdk73mjdJjalkas-0!ksjsdl((232kaj';

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }    
    
    public static function getDefinedModules(){
        return self::$defined_modules;
    }
    
    public static function getSiteUrl($_module){
        $modules = ArrayHelper::map(self::$defined_modules, 'name', 'site');
        return $modules[$_module];
    }
    
    public static function loadComponents($platform_id, $default = ''){
        
        if (tep_not_null($default)){
            $_modules = tep_db_query("select * from " . TABLE_SOCIALS . " where platform_id = '{$platform_id}' and module = '" . tep_db_input($default) . "'");
        } else {
            $_modules = tep_db_query("select * from " . TABLE_SOCIALS . " where platform_id = '{$platform_id}' and active = 1");
        }
        if (tep_db_num_rows($_modules)){
            $clients = [];
            
            $_dm = \yii\helpers\ArrayHelper::map(self::$defined_modules, 'name', 'class');
            
            while($row = tep_db_fetch_array($_modules)){
                    $clients[$row['module']] = [ 
                        'class' => $_dm[$row['module']]
                    ];
                    if (in_array($row['module'], ['twitter'])){
                        $clients[$row['module']]['consumerKey'] = $row['client_id'];
                        $clients[$row['module']]['consumerSecret'] = $row['client_secret'];
                        $clients[$row['module']]['attributeParams'] = [ 'include_email' => 'true' ];
                    } else {
                        $clients[$row['module']]['clientId'] = $row['client_id'];
                        $clients[$row['module']]['clientSecret'] = $row['client_secret'];
                    }                    
            }
            
            if (count($clients)){
                if (is_object(Yii::$app->authClientCollection)){
                    Yii::$app->authClientCollection->clients = $clients;
                }                
            }
        }
    }
    
    public static function loadSocialAddons($platform_id){
        $_modules = tep_db_query("select * from " . TABLE_SOCIALS . " s left join " . TABLE_SOCIALS_ADDONS . " sa on sa.socials_id = s.socials_id where platform_id = '{$platform_id}'");
        if (tep_db_num_rows($_modules)){
            while($row = tep_db_fetch_array($_modules)){
                defined($row['configuration_key']) or define($row['configuration_key'], $row['configuration_value']);
            }
        }
    }
    
    public function handle(){
     global $customer_id, $customer_first_name, $cart, $wish_list, $messageStack;
        $attributes = $this->client->getUserAttributes();
        $attributes = $this->client->prepareAttributes($attributes);
        $email = tep_db_prepare_input(ArrayHelper::getValue($attributes, 'email'));
        $gender = tep_db_prepare_input(ArrayHelper::getValue($attributes, 'gender'));
        $firstname = tep_db_prepare_input(ArrayHelper::getValue($attributes, 'firstname'));
        $lastname = tep_db_prepare_input(ArrayHelper::getValue($attributes, 'lastname'));
        
        $customer = new Customer(Customer::LOGIN_SOCIALS);
        if (!$customer->loginCustomer($email, static::HASHCODE)){
            if (tep_not_null($email)){
                if (ENABLE_CUSTOMER_GROUP_CHOOSE == 'True') {
                    $group = 0; //ToDo, ask customer for group
                } else {
                    if (!defined("DEFAULT_USER_LOGIN_GROUP")) {
                        $group = 0;
                    } else {
                        $group = DEFAULT_USER_LOGIN_GROUP;
                    }
                }
                $login = true;
                if ($group != 0 && \common\helpers\Customer::check_customer_groups($group, 'new_approve')) {
                    $login = false;
                }
                $password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
                $sql_data_array = array(
                    'customers_email_address' => $email,
                    'customers_newsletter' => 0,
                    'platform_id' => \common\classes\platform::currentId(),
                    'groups_id' => $group,
                    'customers_status' => ($login ? 1 : 0),
                    'customers_password' => \common\helpers\Password::encrypt_password($password),
                );
                
                if (isset($gender) && !empty($gender)){
                    $sql_data_array['customers_gender'] = $gender;
                }
                
                if (isset($firstname) && !empty($firstname)){
                    $sql_data_array['customers_firstname'] = $firstname;
                }
                
                if (isset($lastname) && !empty($lastname)){
                    $sql_data_array['customers_lastname'] = $lastname;
                }

                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
                $customer_id = tep_db_insert_id();

                $country = (int)STORE_COUNTRY;
                $zone_id = (int)STORE_ZONE;

                $sql_data_array = array(
                    'customers_id' => $customer_id,
                    'entry_country_id' => $country,
                    'entry_zone_id' => $zone_id,
                ); 
                
                if (isset($gender) && !empty($gender)){
                    $sql_data_array['entry_gender'] = $gender;
                }
                
                if (isset($firstname) && !empty($firstname)){
                    $sql_data_array['entry_firstname'] = $firstname;
                }
                
                if (isset($lastname) && !empty($lastname)){
                    $sql_data_array['entry_lastname'] = $lastname;
                }            
                
                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
                $address_id = tep_db_insert_id();

                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int) $address_id . "' where customers_id = '" . (int) $customer_id . "'");

                tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $customer_id . "', '0', now())");

                if (SESSION_RECREATE == 'True') {
                    tep_session_recreate();
                }

                $customer_first_name = isset($firstname) ? $firstname : '';
                $customer_default_address_id = $address_id;
                $customer_country_id = $country;
                $customer_zone_id = $zone_id;
                if (!defined('DEFAULT_USER_LOGIN_GROUP')) {
                    define(DEFAULT_USER_LOGIN_GROUP, 0);
                }
                $customer_groups_id = DEFAULT_USER_LOGIN_GROUP;

                if ($login) {
                    tep_session_register('customer_id');
                    tep_session_register('customer_first_name');
                    tep_session_register('customer_default_address_id');
                    tep_session_register('customer_country_id');
                    tep_session_register('customer_zone_id');
                    tep_session_register('customer_groups_id');

                    $cart->restore_contents();
                    if (is_object($wish_list) && method_exists($wish_list, 'restore_contents')) {
                        $wish_list->restore_contents();
                    }
                }            
            } else {
                $messageStack->add_session('login', TEXT_INVALID_EMAIL, 'error');
                return Yii::$app->controller->redirect(['account/login']);
            }
        }
        
        global $cart;
        if($cart->count_contents()) {
          return Yii::$app->controller->redirect(['checkout/']);
        }
        
        return Yii::$app->controller->redirect(['account/']);
    }   
    
    public function test($socials_id, $paltform_id)
    {
        $url = '';
        $platform_config = new \common\classes\platform_config($paltform_id);
        $redirect = $platform_config->getCatalogBaseUrl(true) . 'account/auth';
        if ($this->client instanceof OAuth2) {
            $this->client->setReturnUrl($redirect);
            $url = $this->client->buildAuthUrl();
            //$response = Yii::$app->getResponse()->redirect($url);
        } elseif ($this->client instanceof OAuth1) {
            $this->client->setReturnUrl($redirect);
            $requestToken = $this->client->fetchRequestToken();
            $url = $this->client->buildAuthUrl($requestToken);
            //$response = Yii::$app->getResponse()->redirect($url);
        } elseif ($this->client instanceof OpenId) 
            $this->client->setReturnUrl($redirect);{
            $url = $this->client->buildAuthUrl();
            //$response = Yii::$app->getResponse()->redirect($url);            
        }
        
        $info =[];
        if (!empty($url)){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
        }
        
        $success = 1;
        if (!isset($info['http_code']) || $info['http_code'] != 200){
            $success = 0;            
        }        
        
        if ($socials_id){
            tep_db_query("update " . TABLE_SOCIALS . " set test_success = '" . (int)$success . "' where socials_id = '" . (int)$socials_id . "'");
            if (!$success){
                tep_db_query("update " . TABLE_SOCIALS . " set active = '0' where socials_id = '" . (int)$socials_id . "'");
            }
        }

        return (bool)$success;
    }    
    
}