<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

// define the database table names used in the project
define('TABLE_ADDRESS_BOOK', 'address_book');
define('TABLE_ADDRESS_FORMAT', 'address_format');
define('TABLE_BANNERS', 'banners');
define('TABLE_BANNERS_HISTORY', 'banners_history');
define('TABLE_CATEGORIES', 'categories');
define('TABLE_CATEGORIES_DESCRIPTION', 'categories_description');
define('TABLE_CONFIGURATION', 'configuration');
define('TABLE_CONFIGURATION_GROUP', 'configuration_group');
define('TABLE_COUNTER', 'counter');
define('TABLE_COUNTER_HISTORY', 'counter_history');
define('TABLE_COUNTRIES', 'countries');
define('TABLE_CURRENCIES', 'currencies');
define('TABLE_CUSTOMERS', 'customers');
define('TABLE_CUSTOMERS_CREDIT_HISTORY', 'customers_credit_history');
define('TABLE_CUSTOMERS_BASKET', 'customers_basket');
define('TABLE_CUSTOMERS_BASKET_ATTRIBUTES', 'customers_basket_attributes');
define('TABLE_CUSTOMERS_INFO', 'customers_info');
define('TABLE_LANGUAGES', 'languages');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_MANUFACTURERS_INFO', 'manufacturers_info');
define('TABLE_META_TAGS', 'meta_tags');
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDERS_PRODUCTS', 'orders_products');
define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
define('TABLE_ORDERS_STATUS', 'orders_status');
define('TABLE_ORDERS_STATUS_HISTORY', 'orders_status_history');
define('TABLE_ORDERS_HISTORY', 'orders_history');
define('TABLE_ORDERS_TOTAL', 'orders_total');
define('TABLE_PRODUCTS', 'products');
define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
define('TABLE_PRODUCTS_DESCRIPTION', 'products_description');
define('TABLE_PRODUCTS_NOTIFICATIONS', 'products_notifications');
define('TABLE_PRODUCTS_OPTIONS', 'products_options');
define('TABLE_PRODUCTS_OPTIONS_VALUES', 'products_options_values');
define('TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS', 'products_options_values_to_products_options');
define('TABLE_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
define('TABLE_REVIEWS', 'reviews');
define('TABLE_REVIEWS_DESCRIPTION', 'reviews_description');
define('TABLE_SESSIONS', 'sessions');
define('TABLE_SESSIONS_ADMIN', 'sessions_admin');
define('TABLE_SPECIALS', 'specials');
define('TABLE_TAX_CLASS', 'tax_class');
define('TABLE_TAX_RATES', 'tax_rates');
define('TABLE_GEO_ZONES', 'geo_zones');
define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
define('TABLE_TAX_ZONES', 'tax_zones');
define('TABLE_ZONES_TO_TAX_ZONES', 'zones_to_tax_zones');
define('TABLE_WHOS_ONLINE', 'whos_online');
define('TABLE_ZONES', 'zones');
define('TABLE_PAYPALIPN_TXN', 'paypalipn_txn'); // PAYPALIPN

// Added for Xsell Products Mod
define('TABLE_PRODUCTS_XSELL', 'products_xsell');

// Lango Added for template and infobox mod
define('TABLE_INFOBOX_CONFIGURATION', 'infobox_configuration');
define('TABLE_TEMPLATE', 'template');

// Lango Added for Salemaker mod
define('TABLE_SALEMAKER_SALES', 'salemaker_sales');

// Lango Added for Featured Products
define('TABLE_FEATURED', 'featured');

// Lango Added for Wishlist
define('TABLE_WISHLIST', 'customers_wishlist');
define('TABLE_WISHLIST_ATTRIBUTES', 'customers_wishlist_attributes');

define('TABLE_INFORMATION', 'information');
define('TABLE_CATS_PRODUCTS_XSELL', 'cats_products_xsell');
define('TABLE_PRODUCTS_UPSELL', 'products_upsell');
define('TABLE_CATEGORIES_UPSELL', 'categories_upsell');
define('TABLE_CATS_PRODUCTS_UPSELL', 'cats_products_upsell');

define('TABLE_PRODUCTS_PRICES', 'products_prices');
define('TABLE_PRODUCTS_ATTRIBUTES_PRICES', 'products_attributes_prices');
define('TABLE_SPECIALS_PRICES', 'specials_prices');
define('TABLE_INVENTORY', 'inventory');

define('TABLE_PRODUCTS_STOCK_INDICATION','products_stock_indication');
define('TABLE_PRODUCTS_STOCK_INDICATION_TEXT','products_stock_indication_text');

define('TABLE_PROPERTIES_CATEGORIES', 'properties_categories');
define('TABLE_PROPERTIES_CATEGORIES_DESCRIPTION', 'properties_categories_description');
define('TABLE_PROPERTIES_TO_PROPERTIES_CATEGORIES', 'properties_to_properties_categories');
define('TABLE_PROPERTIES', 'properties');
define('TABLE_PROPERTIES_DESCRIPTION', 'properties_description');
define('TABLE_PROPERTIES_TO_PRODUCTS', 'properties_to_products');

define('TABLE_EP_PROFILES', 'ep_profiles');
define('TABLE_EP_DIRECTORIES', 'ep_directories');
define('TABLE_EP_CUSTOM_PROVIDERS', 'ep_custom_providers');
define('TABLE_EP_JOB', 'ep_job');
define('TABLE_EP_LOG_MESSAGES', 'ep_log_messages');

define('TABLE_GROUPS', 'groups');
//  define('TABLE_PRODUCTS_GROUPS_PRICES', 'products_groups_prices');
//  define('TABLE_PRODUCTS_ATTRIBUTES_GROUPS_PRICES', 'products_attributes_groups_prices');
//  define('TABLE_SPECIALS_GROUPS_PRICES', 'specials_groups_prices');
define('TABLE_SEARCH_ENGINES', 'search_engines');
define('TABLE_SEARCH_WORDS', 'search_words');

define('TABLE_SUBSCRIBERS', 'subscribers');
define('TABLE_SETS_PRODUCTS', 'sets_products');
define('TABLE_GIVE_AWAY_PRODUCTS', 'give_away_products');
define('TABLE_GIFT_WRAP_PRODUCTS', 'gift_wrap_products');
define('TABLE_VIRTUAL_GIFT_CARD_BASKET', 'virtual_gift_card_basket');
define('TABLE_VIRTUAL_GIFT_CARD_PRICES', 'virtual_gift_card_prices');


define('TABLE_DESIGN_BOXES', 'design_boxes');
define('TABLE_DESIGN_BOXES_TMP', 'design_boxes_tmp');
define('TABLE_DESIGN_BOXES_SETTINGS', 'design_boxes_settings');
define('TABLE_DESIGN_BOXES_SETTINGS_TMP', 'design_boxes_settings_tmp');

define('TABLE_ORDERS_STATUS_GROUPS', 'orders_status_groups');

define('TABLE_MENUS', 'menus');
define('TABLE_MENU_ITEMS', 'menu_items');
define('TABLE_MENU_TITLES', 'menu_titles');


define('TABLE_PRODUCTS_IMAGES', 'products_images');
define('TABLE_PRODUCTS_IMAGES_DESCRIPTION', 'products_images_description');
define('TABLE_IMAGE_TYPES', 'image_types');
define('TABLE_IMAGE_CACHE_KEYS','image_cache_keys');
define('TABLE_PRODUCTS_IMAGES_ATTRIBUTES', 'products_images_attributes');
define('TABLE_BANNERS_LANGUAGES', 'banners_languages');
define('TABLE_BANNERS_NEW', 'banners_new');
define('TABLE_BANNERS_TO_PLATFORM', 'banners_to_platform');
define('TABLE_INVENTORY_PRICES', 'inventory_prices');
define('TABLE_PRODUCTS_IMAGES_INVENTORY', 'products_images_inventory');

define('TABLE_PRODUCTS_NOTIFY', 'products_notify');

define('TABLE_EMAIL_TEMPLATES', 'email_templates');
define('TABLE_EMAIL_TEMPLATES_TEXTS', 'email_templates_texts');

define('TABLE_THEMES', 'themes');
define('TABLE_THEMES_SETTINGS', 'themes_settings');
define('TABLE_THEMES_SETTINGS_TMP', 'themes_settings_tmp');
define('TABLE_THEMES_STYLES', 'themes_styles');
define('TABLE_THEMES_STYLES_TMP', 'themes_styles_tmp');
define('TABLE_THEMES_STEPS', 'themes_steps');

define('TABLE_TRANSLATION', 'translation');

define('TABLE_PROPERTIES_VALUES', 'properties_values');
define('TABLE_PROPERTIES_UNITS', 'properties_units');
define('TABLE_FILTERS', 'filters');

define('TABLE_GOOGLE_SETTINGS', 'google_settings');

define('TABLE_PLATFORMS', 'platforms');
define('TABLE_PLATFORMS_TO_THEMES', 'platforms_to_themes');
define('TABLE_PLATFORMS_OPEN_HOURS', 'platforms_open_hours');
define('TABLE_PLATFORMS_ADDRESS_BOOK', 'platforms_address_book');
define('TABLE_PLATFORMS_CATEGORIES', 'platforms_categories');
define('TABLE_PLATFORMS_PRODUCTS', 'platforms_products');
define('TABLE_PLATFORMS_CONFIGURATION', 'platforms_configuration');
define('TABLE_PLATFORMS_CUT_OFF_TIMES', 'platforms_cut_off_times');
define('TABLE_PLATFORMS_WATERMARK', 'platforms_watermark');

define('TABLE_ACCESS_LEVELS', 'access_levels');
define('TABLE_ACCESS_CONTROL_LIST', 'access_control_list');

define('TABLE_LANGUAGES_FORMATS', 'languages_formats');
define('TABLE_PLATFORM_FORMATS', 'platforms_formats');

define('TABLE_VIRTUAL_GIFT_CARD_PRICES', 'virtual_gift_card_prices');

define('TABLE_DOCUMENT_TYPES', 'document_types');
define('TABLE_PRODUCTS_DOCUMENTS', 'products_documents');
define('TABLE_PRODUCTS_DOCUMENTS_TITLES', 'products_documents_titles');

define('TABLE_VISIBILITY', 'visibility');
define('TABLE_VISIBILITY_AREA', 'visibility_area');

define('TABLE_PRODUCTS_VIDEOS', 'products_videos');

//Additional shipping module zonetable
define('TABLE_ZONE_TABLE', 'zone_table');
define('TABLE_ZONES_TO_SHIP_ZONES', 'zones_to_ship_zones');
define('TABLE_SHIP_ZONES', 'ship_zones');
define('TABLE_SHIP_OPTIONS', 'ship_options');

define('TABLE_EXACT_CRONS', 'exact_crons');

define('TABLE_SCART', 'scart');
define('TABLE_CUSTOMERS_ERRORS', 'customers_errors');
define('TABLE_GA', 'ga');

define('TABLE_ADDITIONAL_FIELDS', 'additional_fields');
define('TABLE_ADDITIONAL_FIELDS_DESCRIPTION', 'additional_fields_description');
define('TABLE_ADDITIONAL_FIELDS_GROUP', 'additional_fields_group');
define('TABLE_ADDITIONAL_FIELDS_GROUP_DESCRIPTION', 'additional_fields_group_description');
define('TABLE_CUSTOMERS_ADDITIONAL_FIELDS', 'customers_additional_fields');

define('TABLE_SUBSCRIPTION', 'subscription');
define('TABLE_SUBSCRIPTION_PRODUCTS', 'subscription_products');
define('TABLE_SUBSCRIPTION_STATUS_HISTORY', 'subscription_status_history');
define('TABLE_SUBSCRIPTION_TOTAL', 'subscription_total');

define('TABLE_STOCK_HISTORY', 'stock_history');

define('TABLE_PRODUCTS_STOCK_DELIVERY_TERMS', 'products_stock_delivery_terms');
define('TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT', 'products_stock_delivery_terms_text');

define('TABLE_SOCIALS', 'socials');

define('TABLE_PRODUCT_TO_TEMPLATE', 'product_to_template');
define('TABLE_PRODUCTS_TO_POSTS', 'products_to_posts');
define('TABLE_SEO_REDIRECT', 'seo_redirect');
define('TABLE_SEO_REDIRECTS_NAMED', 'seo_redirects_named');
define('TABLE_SOCIALS_ADDONS', 'social_addons');