-- Use the SQL script below to fully remove all changes of Xonu_Directdebit from the database of Magento.
-- After executing this script you will not be able to restore any customer data saved by this extension!

ALTER TABLE `sales_flat_order_payment`
	DROP COLUMN `sepa_mandate_id`,
	DROP COLUMN `sepa_bic`,
	DROP COLUMN `sepa_iban`,
	DROP COLUMN `sepa_holder`;

ALTER TABLE `sales_flat_quote_payment`
	DROP COLUMN `sepa_mandate_id`,
	DROP COLUMN `sepa_bic`,
	DROP COLUMN `sepa_iban`,
	DROP COLUMN `sepa_holder`;

DROP TABLE
	`xonu_directdebit_export`,
	`xonu_directdebit_history`,
	`xonu_directdebit_mandate`;

DELETE FROM `core_resource` WHERE `code`=
	'xonu_directdebit_setup';

