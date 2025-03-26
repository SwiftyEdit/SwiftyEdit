<?php

/**
 * german tax informations:
 * ba_tax_id_number - Steueridentifikationsnummer
 * ba_tax_number - Steuernummer
 * ba_sales_tax_id_number - Umsatzsteuernummer-Identifikationsnummer (USt-IdNr)
 *
 */

$database = "user";
$table_name = "se_user";

$cols = array(
    "user_id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "user_class" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_nick" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_psw" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "user_psw_hash" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "user_groups" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "user_avatar" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_mail" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "user_url" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "user_tel" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "user_fax" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "user_registerdate" => "VARCHAR(25) NOT NULL DEFAULT ''",
    "user_verified" => "VARCHAR(25) NOT NULL DEFAULT ''",
    "user_verified_by_admin" => "VARCHAR(25) NOT NULL DEFAULT ''",
    "user_drm" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "user_acp_settings" => "LONGTEXT NOT NULL DEFAULT ''",
    "user_firstname" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_lastname" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_company" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_street" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_street_nbr" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "user_zip" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "user_city" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "user_activationkey" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "user_reset_psw" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "user_public_profile" => "VARCHAR(500) NOT NULL DEFAULT ''",
    "user_social_media" => "LONGTEXT NOT NULL DEFAULT ''",
    /* billing address data */
    "ba_firstname" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_lastname" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_company" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_tax_id_number" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_tax_number" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_sales_tax_id_number" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_country" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_state" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_street" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_street_nbr" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "ba_zip" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "ba_city" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "ba_mail" => "VARCHAR(100) NOT NULL DEFAULT ''",
    /* shipping / delivery address */
    "sa_firstname" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_lastname" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_company" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_country" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_state" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_street" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_street_nbr" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "sa_zip" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "sa_city" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "sa_mail" => "VARCHAR(100) NOT NULL DEFAULT ''"
);