<? include_once("application.php") ?>
<? 
if(isset($_GET['auto_update']) && !isset($_GET['force_update'])) {
} else {
	//if this isn't an auto_update, then the user needs to have admin privlidges to run this
	if(!isset($_SESSION['admin'])) header("Location: index.php");
	include_once("header.php");
	echo "<font class='standard12'>";
}
	$current_version = $defaultsarray['version'];

	if($current_version == "" && !isset($_GET['override_check'])) {
		die("Unable to determine the current version, please check manually");
	}
	
	if(isset($_GET['force_update'])) {
		$current_version = $_GET['force_update'];
	}

?>
<br>
<table width="95%" align='center' class='standard12'>
<tr>
	<td>
<b>Current Version: <?=$defaultsarray['version']?> 
<? if(isset($_GET['force_update'])) echo "<br><font color='red'>Force Update - From version $_GET[force_update]</font>"; ?></b><p>
<?

	if($current_version == 1.0) {
		
		if(!field_exists('load_handler','load_available')) {
			$sql = "
				alter table load_handler add load_available int default 0
			";
			simple_query($sql);
		}
		
		$current_version = 1.01;
		update_version($current_version);
	}
	
	if($current_version == 1.01) {
		if(!table_exists("load_handler_stops")) {
			$sql = "
				create table load_handler_stops (
					id int not null auto_increment, 
					load_handler_id int,
					trucks_log_id int,
					shipper_name varchar(255),
					shipper_address1 varchar(255),
					shipper_address2 varchar(255),
					shipper_city varchar(255),
					shipper_state varchar(255),
					shipper_zip varchar(255),
					shipper_eta datetime,
					shipper_pta datetime,
					dest_name varchar(255),
					dest_address1 varchar(255),
					dest_address2 varchar(255),
					dest_city varchar(255),
					dest_state varchar(255),
					dest_zip varchar(255),
					dest_eta datetime,
					dest_pta datetime,
					deleted int,
					linedate_added datetime,
					created_by_user_id int,
					linedate_pickup_eta datetime,
					linedate_pickup_pta datetime,
					linedate_dropoff_eta datetime,
					linedate_dropoff_pta datetime,
					primary key(id))
			";
			simple_query($sql);
		}
		/*
		if(!table_exists("trucks_log_stops")) {
			$sql = "
				create table trucks_log_stops (
					id int not null auto_increment, 
					trucks_log_id int,
					load_handler_stops_id int,
					primary key(id))
			";
			simple_query($sql);
		}
		*/
		
		if(!field_exists('load_handler','rate_unloading')) {
			$sql = "
				alter table load_handler add rate_unloading decimal(11,2) default 0,
					add rate_stepoff decimal(11,2) default 0,
					add rate_misc decimal(11,2) default 0,
					add rate_fuel_surcharge_per_mile decimal(11,3) default 0
			";
			simple_query($sql);
		}
		if(!field_exists('load_handler','rate_fuel_surcharge_total')) {
			$sql = "
				alter table load_handler add rate_fuel_surcharge_total decimal(11,2) default 0
			";
			simple_query($sql);
		}
		if(!field_exists('load_handler','rate_base')) {
			$sql = "
				alter table load_handler add rate_base decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler_stops','stop_type_id')) {
			$sql = "
				alter table load_handler_stops add stop_type_id int default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler_stops','linedate_completed')) {
			$sql = "
				alter table load_handler_stops add linedate_completed datetime
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler_stops','directions')) {
			$sql = "
				alter table load_handler_stops add directions text
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler_stops','linedate_completed')) {
			$sql = "
				alter table load_handler_stops add linedate_completed datetime
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler_stops','stop_phone')) {
			$sql = "
				alter table load_handler_stops add column stop_phone varchar(100)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler_stops','ignore_address')) {
			$sql = "
				alter table load_handler_stops add column ignore_address int default 0,
					add index ignore_address(ignore_address)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','rate_lumper')) {
			$sql = "
				alter table load_handler add rate_lumper decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','preplan')) {
			$sql = "
				alter table load_handler add column preplan int default 0,
					add column preplan_driver_id int default 0,
					add index preplan(preplan)
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks_log','daily_run_otr')) {
			$sql = "
				alter table trucks_log add column daily_run_otr decimal(11,2) default 0,
					add column daily_run_hourly decimal(11,2) default 0,
					add column loaded_miles_hourly int default 0,
					add column hours_worked decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','rate_fuel_surcharge')) {
			$sql = "
				alter table load_handler add column rate_fuel_surcharge decimal(11,3) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','days_run_otr')) {
			$sql = "
				alter table load_handler add column days_run_otr decimal(11,2) default 0,
									add column days_run_hourly decimal(11,2) default 0,
									add column loaded_miles_hourly decimal(11,2) default 0,
									add column hours_worked decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','actual_rate_fuel_surcharge')) {
			$sql = "
				alter table load_handler add column actual_rate_fuel_surcharge decimal(11,3) default 0,
									add column actual_bill_customer decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks','rental')) {
			$sql = "
				alter table trucks add column rental int default 0,
					add column count_billable int default 0
			";
			simple_query($sql);
		}
		
		if(!table_exists('load_handler_quote_var_exp')) {
			$sql = "
				CREATE TABLE  `load_handler_quote_var_exp` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `load_handler_id` int DEFAULT NULL,
				  `expense_type_id` int DEFAULT NULL,
				  `expense_amount` decimal(11,2) DEFAULT '0',
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
		}
		
		if(!table_exists('option_cat')) {
			$sql = "
				CREATE TABLE  `option_cat` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `cat_name` varchar(100) DEFAULT NULL,
				  `cat_desc` varchar(45) DEFAULT NULL,
				  `deleted` int(10) unsigned DEFAULT '0',
				  `locked` int(10) unsigned DEFAULT '0',
				  `blank_text` varchar(255) DEFAULT NULL,
				  `xlink` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
			
			$sql = "
				CREATE TABLE  `option_values` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `cat_id` int(10) unsigned DEFAULT NULL,
				  `fname` varchar(255) DEFAULT NULL,
				  `fvalue` varchar(255) DEFAULT NULL,
				  `deleted` int(10) unsigned DEFAULT '0',
				  `zorder` int(10) unsigned DEFAULT NULL,
				  `dummy_val` varchar(100) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  KEY `cat_id` (`cat_id`),
				  KEY `deleted` (`deleted`),
				  KEY `fname` (`fname`),
				  KEY `fvalue` (`fvalue`),
				  KEY `zorder` (`zorder`)
				)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					blank_text)
					
				values ('expense_type',
						'Expense Type',
						'Select Expense Type')
			";
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue)
					
				values 	($cat_id, 'layover', 'Layover'), 
						($cat_id, 'scales', 'Scales'),
						($cat_id, 'misc', 'Misc')
			";
			simple_query($sql);
		}
		
		if(!table_exists("dispatch_expenses")) {
			$sql = "
				CREATE TABLE `dispatch_expenses` (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `linedate_added` DATETIME,
				  `added_by_user_id` INTEGER UNSIGNED,
				  `dispatch_id` INTEGER UNSIGNED,
				  `expense_type_id` INTEGER UNSIGNED,
				  `expense_amount` DECIMAL(11,2),
				  `expense_desc` TEXT,
				  `deleted` INTEGER UNSIGNED DEFAULT 0,
				  PRIMARY KEY (`id`),
				  INDEX `linedate_added`(`linedate_added`),
				  INDEX `dispatch_id`(`dispatch_id`),
				  INDEX `deleted`(`deleted`),
				  INDEX `expense_type_id`(`expense_type_id`)
				)
			";
			simple_query($sql);
		}
		
		if(!default_exists('labor_per_mile_team')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('labor_per_mile_team',
					'0.50',
					'Labor per Mile (Team)',
					1,
					'Financial')
			";
			simple_query($sql);
		}

		if(!default_exists('billable_days_in_month')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('billable_days_in_month',
					'21',
					'Billable Days per Month',
					1,
					'Financial'),
					
					('average_mpg',
					'5.5',
					'Average MPG',
					1,
					'Financial'),
					
					('labor_per_mile',
					'0.47',
					'Labor Per Mile',
					1,
					'Financial'),
					
					('tractor_maint_per_mile',
					'0.07',
					'Tractor Maint per mile',
					1,
					'Financial'),
					
					('trailer_maint_per_mile',
					'0.03',
					'Trailer Maint per Mile',
					1,
					'Financial'),
					
					('labor_per_hour',
					'20',
					'Labor Per Hour',
					1,
					'Financial')
			";
			simple_query($sql);
			
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc)
					
				values ('fixed_expenses',
					'Fixed Expenses')					
			";
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					dummy_val)
					
				values 	($cat_id, 'Rent & Misc.','0','1'),
						($cat_id, 'Cargo Insurance', '0','0'),
						($cat_id, 'Liability/Phy Damage', '0','0'),
						($cat_id, 'Crime Insurance', '0','0'),
						($cat_id, 'Trailer Lease', '0','0'),
						($cat_id, 'Tractor Lease', '0','0')
			";
			simple_query($sql);
		}
		
		
		
		$current_version = 1.02;
		update_version($current_version);
	}
	
	if($current_version == 1.02) {
		if(!table_exists("trailers_dropped")) {
			$sql = "
				CREATE TABLE  `trailers_dropped` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `trailer_id` int(10) unsigned DEFAULT NULL,
				  `linedate_added` datetime DEFAULT NULL,
				  `linedate` datetime DEFAULT '0000-00-00 00:00:00',
				  `created_by_user_id` int(10) unsigned DEFAULT NULL,
				  `deleted` int(10) unsigned DEFAULT 0,
				  `location_city` varchar(255) DEFAULT NULL,
				  `location_state` varchar(50) DEFAULT NULL,
				  `location_zip` varchar(45) DEFAULT NULL,
				  `notes` text,
				  `drop_completed` int(10) unsigned DEFAULT '0',
				  `customer_id` int(10) unsigned DEFAULT '0',
				  PRIMARY KEY (`id`),
				  KEY `trailer_id` (`trailer_id`),
				  KEY `deleted` (`deleted`),
				  KEY `customer_id` (`customer_id`)
				)
			";
			simple_query($sql);
		}
		
		if(!get_option_cat_id('expense_type_lh')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('expense_type_lh',
					'Load Handler Expenses',
					0,
					'Select Expense Type')
			";
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'truck_order_not_used', 'Truck Order Not Used',0),
					  ($cat_id, 'lumper', 'Lumper',0),
					  ($cat_id, 'misc', 'Misc',0)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','actual_fuel_charge_per_mile')) {
			$sql = "
				alter table load_handler add actual_fuel_charge_per_mile decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('customers','address1')) {
			$sql = "
				alter table customers add column address1 varchar(255),
					add column address2 varchar(255),
					add column state varchar(100),
					add column city varchar(255)
			";
			simple_query($sql);
		}		
		
		if(!field_exists('customers','zip')) {
			$sql = "
				alter table customers add column zip varchar(50)
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks','leased_from')) {
			$sql = "
				alter table trucks add column leased_from varchar(255)
			";
			simple_query($sql);
		}				
		
		if(!table_exists('load_handler_actual_var_exp')) {
			$sql = "
				CREATE TABLE  `load_handler_actual_var_exp` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `load_handler_id` int DEFAULT NULL,
				  `expense_type_id` int DEFAULT NULL,
				  `expense_amount` decimal(11,2) DEFAULT '0',
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
		}
		
		if(!table_exists('equipment_history')) {
			$sql = "
				CREATE TABLE  `equipment_history` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `equipment_type_id` int DEFAULT 0,
				  `equipment_id` int DEFAULT 0,
				  `equipment_value` decimal(11,2) DEFAULT '0',
				  `linedate_added` datetime DEFAULT '0000-00-00',
				  `linedate_aquired` datetime DEFAULT '0000-00-00',
				  `linedate_returned` datetime DEFAULT '0000-00-00',
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
		}
		
		if(!field_exists('equipment_history','deleted')) {
			$sql = "
				alter table equipment_history add column deleted int default 0
			";
			simple_query($sql);
		}

		if(!field_exists('equipment_history','miles_pickup')) {
			$sql = "
				alter table equipment_history add column miles_pickup int default 0,
					add column miles_dropoff int default 0
			";
			simple_query($sql);
		}	
		
		if(!field_exists('drivers','dl_state')) {
			$sql = "
				alter table drivers add column dl_state varchar(100)
			";
			simple_query($sql);
		}	
		
		if(!table_exists("driver_expenses")) {
			$sql = "
				CREATE TABLE driver_expenses (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `driver_id` INTEGER UNSIGNED,
				  `linedate_added` DATETIME,
				  `created_by_user_id` INTEGER UNSIGNED,
				  `linedate` DATETIME,
				  `billable` INTEGER UNSIGNED DEFAULT 0,
				  `expense_type_id` INTEGER UNSIGNED,
				  `description` TEXT,
				  `amount` DECIMAL(11,2) DEFAULT 0,
				  `amount_billable` DECIMAL(11,2) DEFAULT 0,
				  `deleted` INTEGER UNSIGNED DEFAULT 0,
				  PRIMARY KEY (`id`),
				  INDEX `driver_id`(`driver_id`),
				  INDEX `deleted`(`deleted`),
				  INDEX `linedate`(`linedate`)
				)
			";
			simple_query($sql);
		}
		
		if(!table_exists('drivers_expenses')) {
			$sql = "
				CREATE TABLE `drivers_expenses` (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `driver_id` INTEGER UNSIGNED,
				  `created_by_user_id` INTEGER UNSIGNED,
				  `linedate_added` DATETIME,
				  `linedate` DATETIME,
				  `deleted` INTEGER UNSIGNED DEFAULT 0,
				  `desc_long` TEXT,
				  `expense_type_id` INTEGER UNSIGNED DEFAULT 0,
				  `billable` INTEGER UNSIGNED DEFAULT 0,
				  `amount` DECIMAL(11,2) DEFAULT 0,
				  `amount_billable` DECIMAL(11,2) DEFAULT 0,
				  PRIMARY KEY (`id`),
				  INDEX `drver_id`(`driver_id`),
				  INDEX `linedate`(`linedate`),
				  INDEX `deleted`(`deleted`),
				  INDEX `expense_type_id`(`expense_type_id`),
				  INDEX `billable`(`billable`)
				)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','load_number')) {
			$sql = "
				alter table load_handler add column load_number varchar(255)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','actual_total_cost')) {
			$sql = "
				alter table load_handler add column actual_total_cost decimal(11,3) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','actual_fuel_surcharge_per_mile')) {
			$sql = "
				alter table load_handler add column actual_fuel_surcharge_per_mile decimal(11,3) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('attachments','descriptor')) {
			$sql = "
				alter table attachments add column descriptor varchar(10)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','otr_daily_cost')) {
			$sql = "
				alter table load_handler add column otr_daily_cost decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks_log','otr_daily_cost')) {
			$sql = "
				alter table trucks_log add column otr_daily_cost decimal(11,2) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks_log','avg_mpg')) {
			$sql = "
				alter table trucks_log add column avg_mpg decimal(11,2) default 0,
					add column tractor_maint_per_mile decimal(11,4) default 0,
					add column trailer_maint_per_mile decimal(11,4) default 0,
					add column labor_per_hour decimal(11,4) default 0,
					add column labor_per_mile decimal(11,4) default 0,
					add column daily_cost decimal(11,4) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('drivers','pay_per_hour_team')) {
			$sql = "
				alter table drivers add column pay_per_hour_team decimal(11,2) default 0,
					add column pay_per_mile_team decimal(11,2) default 0,
					add column charged_per_hour_team decimal(11,2) default 0,
					add column charged_per_mile_team decimal(11,2) default 0
			";
			simple_query($sql);
		}

		if(!field_exists('trucks_log','cost')) {
			$sql = "
				alter table trucks_log add column cost decimal(11,3) default 0,
					add column profit decimal(11,3) default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks','vin')) {
			$sql = "alter table trucks add column vin varchar(100)";
			simple_query($sql);
			$sql = "alter table trailers add column vin varchar(100)";
			simple_query($sql);
			$sql = "alter table drivers add column dl_number varchar(100)";
			simple_query($sql);
		}
		
		if(!field_exists('trucks','license_plate_no')) {
			$sql = "alter table trucks add column license_plate_no varchar(100)";
			simple_query($sql);
			$sql = "alter table trailers add column license_plate_no varchar(100)";
			simple_query($sql);
		}
		
		if(!option_exists('expense_type_lh', 'stop_offs')) {
			$sql = "
				insert into option_values
					(cat_id, fname, fvalue)
					
				values ('".get_option_cat_id('expense_type_lh')."', 'stop_offs', 'Stop Off'),
					('".get_option_cat_id('expense_type_lh')."', 'base_rate', 'Base Rate')
			";
			simple_query($sql);
		}
		
		if(!get_option_cat_id('driver_expense_type')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('driver_expense_type',
					'Driver Expenses',
					0,
					'Select Driver Expense Type')
			";
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'misc', 'Misc',0)
			";
			simple_query($sql);
		}

		if(!get_option_cat_id('expense_type_insurance')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('expense_type_insurance',
					'Insurance Expenses',
					0,
					'Select Insurance Expense Type')
			";
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);

			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'primary_liability', 'Primary Liability (per truck) $',0),
					  ($cat_id, 'general_liability', 'General Liability (per truck) $',0),
					  ($cat_id, 'physical_damage_liability', 'Physical Damage Liability (percentage) %',0),
					  ($cat_id, 'cargo_liability', 'Cargo Liability (each) $',0)
			";
			simple_query($sql);
		}
		
		if(!field_exists('trucks_log','pcm_miles')) {
			$sql = "alter table trucks_log add column pcm_miles decimal(11,2) default 0";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column pcm_miles decimal(11,2) default 0";
			simple_query($sql);
		}
		
		if(!table_exists("trucks_odometer")) {
			$sql = "
				CREATE TABLE trucks_odometer (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `truck_id` INTEGER UNSIGNED,
				  `linedate_added` DATETIME,
				  `linedate` DATETIME,
				  `odometer` DECIMAL(11,2),
				  PRIMARY KEY (`id`),
				  INDEX `truck_id`(`truck_id`),
				  INDEX `linedate`(`linedate`)
				)
			";
			simple_query($sql);
		}
		
		if(!field_exists('load_handler','linedate_invoiced')) {
			$sql = "alter table load_handler add column linedate_invoiced datetime default '0000-00-00'";
			simple_query($sql);
		}
		
		if(!field_exists('trucks_log','manual_miles_flag')) {
			$sql = "alter table trucks_log add column manual_miles_flag int default 0";
			simple_query($sql);
		}		
		
		if(!table_exists("log_fuel_updates")) {
			$sql = "
				CREATE TABLE log_fuel_updates (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `linedate_added` DATETIME,
				  `fuel_surcharge` DECIMAL(11,3),
				  PRIMARY KEY (`id`),
				  INDEX `linedate`(`linedate_added`)
				)
			";
			simple_query($sql);
		}
		
		if(!table_exists('drivers_unavailable')) {
			$sql = "
				CREATE TABLE  `drivers_unavailable` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `driver_id` int DEFAULT 0,
				  `deleted` int DEFAULT 0,
				  `reason` text,
				  `linedate_added` datetime DEFAULT '0000-00-00',
				  `linedate_start` datetime DEFAULT '0000-00-00',
				  `linedate_end` datetime DEFAULT '0000-00-00',
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
		}
		
		if(!field_exists('drivers_unavailable','added_by')) {
			$sql = "alter table drivers_unavailable add column added_by int default 0";
			simple_query($sql);
		}
		
		if(!table_exists("quotes")) {
			$sql = "
				CREATE TABLE `quotes` (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `customer_id` INTEGER UNSIGNED,
				  `driver_id` INTEGER UNSIGNED,
				  `trailer_id` INTEGER UNSIGNED,
				  `truck_id` INTEGER UNSIGNED,
				  `linedate_added` DATETIME,
				  `created_by_user_id` INTEGER UNSIGNED,
				  `linedate` DATETIME,
				  `quote_name` varchar(255),
				  `quote_notes` text,
				  `deleted` INTEGER UNSIGNED DEFAULT 0,
				  `days_run_otr` DECIMAL(11,2) DEFAULT 0,
				  `days_run_hourly` DECIMAL(11,2) DEFAULT 0,
				  `miles_loaded` DECIMAL(11,2) DEFAULT 0,
				  `miles_pcm` DECIMAL(11,2) DEFAULT 0,
				  `miles_deadhead` DECIMAL(11,2) DEFAULT 0,
				  `miles_hourly` DECIMAL(11,2) DEFAULT 0,
				  `hours_worked` DECIMAL(11,2) DEFAULT 0,
				  `bill_customer` DECIMAL(11,2) DEFAULT 0,
				  `fuel_avg` DECIMAL(11,3) DEFAULT 0,
				  `daily_cost` DECIMAL(11,2) DEFAULT 0,
				  `average_mpg` DECIMAL(11,2) DEFAULT 0,
				  `labor_per_mile` DECIMAL(11,2) DEFAULT 0,
				  `labor_per_hour` DECIMAL(11,2) DEFAULT 0,
				  `maint_per_mile_tractor` DECIMAL(11,2) DEFAULT 0,
				  `maint_per_mile_trailer` DECIMAL(11,2) DEFAULT 0,
				  `total_cost` DECIMAL(11,2) DEFAULT 0,
				  `profit` DECIMAL(11,2) DEFAULT 0,
				  PRIMARY KEY (`id`)
				)
			";
			simple_query($sql);
			
			$sql = "
				CREATE TABLE `quotes_stops` (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `quote_id` INTEGER UNSIGNED,
				  `deleted` INTEGER UNSIGNED DEFAULT 0,
				  `stop_location` VARCHAR(255),
				  `stop_order_id` INTEGER UNSIGNED DEFAULT 0,
				  PRIMARY KEY (`id`),
				  INDEX `quote_id`(`quote_id`)
				)
			";
			simple_query($sql);
			
			$sql = "
				CREATE TABLE `quotes_expenses` (
				  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				  `quote_id` INTEGER UNSIGNED,
				  `expense_type_id` INTEGER UNSIGNED,
				  `amount` DECIMAL(11,2),
				  PRIMARY KEY (`id`),
				  INDEX `quote_id`(`quote_id`)
				)
			";
			simple_query($sql);
			
		}
		
		if(!field_exists('quotes','team_driver')) {
			$sql = "alter table quotes add column team_driver int default 0";
			simple_query($sql);
		}

		if(!field_exists('trucks','replacement')) {
			$sql = "alter table trucks add column replacement int default 0";
			simple_query($sql);
		}
		
		if(!field_exists('equipment_history','replacement')) {
			$sql = "alter table equipment_history 	add column replacement int default 0";
			simple_query($sql);
		}
		
		if(!field_exists('equipment_history','replacement_xref_id')) {
			$sql = "alter table equipment_history 	add column replacement_xref_id int default 0";
			simple_query($sql);
		}
		
		if(!field_exists('drivers','hide_unavailable')) {
			$sql = "alter table drivers 	add column hide_unavailable int default 0";
			simple_query($sql);
		}
		
		if(!field_exists('trucks_odometer','deleted')) {
			$sql = "alter table trucks_odometer add column deleted int default 0";
			simple_query($sql);
		}
		
		if(!verify_index('load_handler_stops','load_handler_id')) {
			$sql = "
				alter table load_handler_stops
					add index `load_handler_id`(`load_handler_id`),
					add index `trucks_log_id`(`trucks_log_id`),
					add index `linedate_pickup_eta`(`linedate_pickup_eta`),
					add index `deleted`(`deleted`)
			";
			simple_query($sql);
		}
		
		if(!table_exists("drivers_payroll")) {
			$sql = "
				CREATE TABLE `drivers_payroll` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `driver_id` int(11) DEFAULT 0,
				  `linedate_added` datetime DEFAULT '0000-00-00 00:00:00',
				  `added_by_user_id` int(11) DEFAULT 0,
				  `hours` decimal(11,2) DEFAULT 0,
				  `deleted` int DEFAULT 0,
				  `linedate` datetime DEFAULT '0000-00-00 00:00:00',
				  PRIMARY KEY (`id`)
				)
			";
			simple_query($sql);

			$sql = "
				alter table drivers_payroll
					add index `driver_id`(`driver_id`),
					add index `linedate`(`linedate`),
					add index `deleted`(`deleted`)
			";
			simple_query($sql);
		}
		
		if(!field_exists('trailers','no_insurance')) {
			$sql = "alter table trailers add column no_insurance int default 0";
			simple_query($sql);
		}
		
		$current_version = 1.03;
		update_version($current_version);
	}
	
	if($current_version == 1.03) {
		if(!field_exists('trucks_log','valid_trip_pack')) {
			$sql = "alter table trucks_log add column valid_trip_pack int default 0,
						add column user_id_verified_trip_pack int default 0";
			simple_query($sql);
		}
		
		if(!verify_index('load_handler','linedate_pickup_eta')) {
			$sql = "
				alter table load_handler
					add index `linedate_pickup_eta`(`linedate_pickup_eta`),
					add index `invoice_number`(`invoice_number`),
					add index `linedate_dropoff_eta`(`linedate_dropoff_eta`),
					add index `deleted`(`deleted`)
			";
			simple_query($sql);
		}
	
		if(!field_exists('drivers','hide_available')) {
			$sql = "alter table drivers add column hide_available int default 0";
			simple_query($sql);
		}
		
		
		$current_version = 1.04;
		update_version($current_version);
	}

	if($current_version == 1.04) {
		
		if(!field_exists('drivers','attached_truck_id')) {
			$sql = "
				alter table drivers add column attached_truck_id int default 0,
								add column attached_trailer_id int default 0
			";
			simple_query($sql);
		}		

		$current_version = 1.05;
		update_version($current_version);
	}
	
	if($current_version == 1.05) {
		
		if(!field_exists('customers','credit_hold')) {
			$sql = "
				alter table customers add column credit_hold int default 0
			";
			simple_query($sql);
		}	
		
		if(!verify_index("load_handler_actual_var_exp", "load_handler_id")) {
			$sql = "
				alter table load_handler_actual_var_exp add index load_handler_id(load_handler_id),
						add index expense_type_id(expense_type_id)
			";
			simple_query($sql);
			
			$sql = "
				alter table load_handler_quote_var_exp add index load_handler_id(load_handler_id),
						add index expense_type_id(expense_type_id)
			";
			simple_query($sql);
		}
		
		if(!verify_index("trucks_log", "linedate_pickup_eta")) {
			$sql = "
				alter table trucks_log add index linedate_pickup_eta(linedate_pickup_eta)
			";
			simple_query($sql);
		}
		
		$current_version = 1.06;
		update_version($current_version);
	}
	
	if($current_version == 1.06) {
					
		if(!field_exists("drivers_expenses", "taxable_payroll")) {
			$sql = "
				alter table drivers_expenses add column taxable_payroll int default 0
			";
			simple_query($sql);
			
			$sql = "
				alter table drivers change pay_per_mile pay_per_mile decimal(11,3) default '0.00',
					change charged_per_mile charged_per_mile decimal(11,3) default '0.00',
					change charged_per_mile_team charged_per_mile_team decimal(11,3) default '0.00',
					change pay_per_mile_team pay_per_mile_team decimal(11,3) default '0.00'
			";
			simple_query($sql);
		}
		
		if(!field_exists("drivers_expenses", "payroll")) {
			$sql = "
				alter table drivers_expenses add column payroll int default 0
			";
			simple_query($sql);
		}		
		
		if(!field_exists("drivers", "driver_has_load")) {
			$sql = "
				alter table drivers add column driver_has_load int default 0
			";
			simple_query($sql);
		}
		
		
		$current_version = 1.07;
		update_version($current_version);
	}
	
	if($current_version == 1.07) {
		
		if(!default_exists('sicap_integration')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('sicap_integration',
					'0',
					'SICAP Accounting Package Integration',
					1,
					'Financial')
			";
			simple_query($sql);
		}
		
		if(!field_exists("trucks", "sicap_coa_created")) {
			$sql = "
				alter table trucks add column sicap_coa_created int default 0
			";
			simple_query($sql);
			
			$sql = "
				alter table trailers add column sicap_coa_created int default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "sicap_id")) {
			$sql = "
				alter table customers add column sicap_id int default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler", "sicap_invoice_number")) {
			$sql = "
				alter table load_handler add column sicap_invoice_number varchar(255)
			";
			simple_query($sql);
		}	
		
		$current_version = 1.08;
		update_version($current_version);
	}	


	if($current_version == 1.08) {
		if(!field_exists("quotes", "load_taken")) {
			$sql = "
				alter table quotes add column load_taken int default 0
			";
			simple_query($sql);
		}	
		
		$current_version = 1.09;
		update_version($current_version);
	}	
	
	if($current_version == 1.09) {
		
		if(!field_exists("load_handler", "linedate_auto_created_reviewed")) {
			$sql = "
				alter table load_handler add column linedate_auto_created_reviewed datetime default '0000-00-00'
			";
			simple_query($sql);
		}	

		if(!field_exists("load_handler", "auto_created")) {
			$sql = "
				alter table load_handler add column auto_created int default 0
			";
			simple_query($sql);
		}

		if(!field_exists("load_handler", "linedate_edi_response_sent")) {
			$sql = "
				alter table load_handler add column linedate_edi_response_sent datetime default '0000-00-00'
			";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler", "linedate_edi_invoice_sent")) {
			$sql = "
				alter table load_handler add column linedate_edi_invoice_sent datetime default '0000-00-00'
			";
			simple_query($sql);
		}

		if(!field_exists("drivers", "available_notes")) {
			$sql = "
				alter table drivers add column available_notes text default null
			";
			simple_query($sql);
		}
		if(!field_exists("drivers", "linedate_available_notes")) {
			$sql = "
				alter table drivers add column linedate_available_notes datetime default '0000-00-00'
			";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "website")) {
			$sql = "
				alter table customers add column website varchar(255),
					add column billing_address1 varchar(255),
					add column billing_address2 varchar(255),
					add column billing_city varchar(255),
					add column billing_state varchar(255),
					add column billing_zip varchar(255),
					add column fax varchar(255)
			";
			simple_query($sql);
		}
		
		if(!default_exists('edi_scac_code')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('edi_scac_code',
					'',
					'EDI SCAC Code',
					1,
					'EDI')
			";
			simple_query($sql);
		}
		
		if(!default_exists('edi_company_name')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('edi_company_name',
					'',
					'EDI Company Name (Goes with the SCAC code)',
					1,
					'EDI'),
					
					('edi_fedex_path',
					'',
					'EDI Fedex Path',
					1,
					'EDI')
			";
			simple_query($sql);
		}

		if(!default_exists('edi_provider_name')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('edi_provider_name',
					'',
					'EDI Provider Name',
					1,
					'EDI'),
					
					('edi_contact',
					'',
					'EDI Contact',
					1,
					'EDI'),
					
					('edi_ftp_address',
					'',
					'EDI FTP Address',
					1,
					'EDI'),
					
					('edi_ftp_username',
					'',
					'EDI FTP Username',
					1,
					'EDI'),
					
					('edi_ftp_password',
					'',
					'EDI FTP Password',
					1,
					'EDI')
			";
			simple_query($sql);
		}
		
		$current_version = 1.1;
		update_version($current_version);
	}
	
	if($current_version == 1.1) {
		
		if(!get_option_cat_id('import_fields_customer')) {
			
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('import_fields_customer',
					'Import Fields (Customers)',
					0,
					'')
			";
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					dummy_val,
					deleted)
					
				values ($cat_id, 'name_company', 'Company', 'Company Name',0),
					  ($cat_id, 'contact_primary', 'Contact', 'Contact Name',0),
					  ($cat_id, 'contact_email', 'E-Mail', 'EMail',0),
					  ($cat_id, 'phone_work', 'Phone', 'Work Phone',0),
					  ($cat_id, 'address1', 'Address 1', '',0),
					  ($cat_id, 'address2', 'Address 2', '',0),
					  ($cat_id, 'city', 'City', '',0),
					  ($cat_id, 'state', 'State', '',0),
					  ($cat_id, 'zip', 'Zip', '',0),
					  ($cat_id, 'website', 'Website', '',0),
					  ($cat_id, 'billing_address1', 'Billing Address 1', '',0),
					  ($cat_id, 'billing_address2', 'Billing Address 2', '',0),
					  ($cat_id, 'billing_city', 'Billing city', '',0),
					  ($cat_id, 'billing_state', 'Billing State', '',0),
					  ($cat_id, 'billing_zip', 'Billing Zip', '',0),
					  ($cat_id, 'fax', 'Fax', 'Fax Number',0)
			";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "credit_limit")) {
			$sql = "
				alter table customers add column credit_limit decimal(11,2),
					add column phone2 varchar(100),
					add column linedate_customer_since datetime default '0000-00-00',
					add column linedate_added datetime
			";
			simple_query($sql);
			
			$cat_id = get_option_cat_id('import_fields_customer');
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					dummy_val,
					deleted)
					
				values ($cat_id, 'credit_limit', 'Credit Limit', '',0),
					  ($cat_id, 'linedate_customer_since', 'Customer Since Date', '',0),
					  ($cat_id, 'phone2', 'Phone 2', 'Telephone 2',0)
			";
			simple_query($sql);
		}		
		
		$cat_id = get_option_cat_id('import_fields_customer');
		$sql = "
			select id
			
			from option_values
			where cat_id = '".sql_friendly($cat_id)."'
				and fname = 'cts_id'
		";
		$data_check = simple_query($sql);
		if(!mysqli_num_rows($data_check)) {
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					dummy_val,
					deleted)
					
				values ($cat_id, 'cts_id', 'CTS Customer ID', '',0)
			";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "email_invoice")) {
			$sql = "
				alter table customers add column email_invoice int default 0
			";
			simple_query($sql);
		}		
		
		$current_version = 1.11;
		update_version($current_version);
	}
	
	if($current_version == 1.11) {
		if(!default_exists('fleetone_username')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('fleetone_username','','FTP Username',1,'Fleet One'),
					('fleetone_address','','FTP Server Address',1,'Fleet One'),
					('fleetone_password','','FTP Password',1,'Fleet One')
			";
			simple_query($sql);
		}
		
		$current_version = 1.12;
		update_version($current_version);
	}
	
	if($current_version == 1.12) {
		
		if(!field_exists("load_handler_stops", "odometer_reading")) {
			$sql = "
				alter table load_handler_stops add column odometer_reading int default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler", "predispatch_odometer")) {
			$sql = "
				alter table load_handler add column predispatch_odometer int default 0,
					add column predispatch_city varchar(255) default null,
					add column predispatch_state varchar(100) default null,
					add column predispatch_zip varchar(50) default null
			";
			simple_query($sql);
		}
		
		if(!field_exists("drivers", "linedate_driver_has_load")) {
			$sql = "
				alter table drivers add column linedate_driver_has_load datetime default '0000-00-00'
			";
			simple_query($sql);
			
			$sql = "
				update drivers
				set linedate_driver_has_load = now()
				where driver_has_load = 1
			";
			simple_query($sql);
		}		
		
		$current_version = 1.13;
		update_version($current_version);
	}
	
	
	if($current_version == 1.13) {
		if(!get_option_cat_id('maint_items')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('maint_items',
					'Maintenance Items',
					0,
					'Select Maintenance Item')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'misc', 'Misc',0)
			";
			simple_query($sql);
		}
		
		//truck or trailer equipment type
		if(!get_option_cat_id('equipment_type')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('equipment_type',
					'Equipment Type',
					0,
					'Select Equipment Type')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'truck', 'Truck',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'trailer', 'Trailer',0)
			";
			simple_query($sql);
		}
		
		//position x
		if(!get_option_cat_id('positions_x_axis')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('positions_x_axis',
					'Positions X',
					0,
					'Select Positions X')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'front', 'Front',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'middle', 'Middle',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'rear', 'Rear',0)
			";
			simple_query($sql);
		}
		//position y
		if(!get_option_cat_id('positions_y_axis')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('positions_y_axis',
					'Positions Y',
					0,
					'Select Positions Y')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'left', 'Left',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'center', 'Center',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'right', 'Right',0)
			";
			simple_query($sql);
		}
		//position z
		if(!get_option_cat_id('positions_z_axis')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('positions_z_axis',
					'Positions Z',
					0,
					'Select Positions Z')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'top', 'Top',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'center', 'Center',0)
			";
			simple_query($sql);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'bottom', 'Bottom',0)
			";
			simple_query($sql);
		}
		//position t
		if(!get_option_cat_id('positions_t_axis')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('positions_t_axis',
					'In or Out',
					0,
					'Select In or Out')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'inside', 'Interior',0)
			";
			simple_query($sql);
						
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'outside', 'Exterior',0)
			";
			simple_query($sql);
		}
		//request category
		if(!get_option_cat_id('request_category')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('request_category',
					'Request Category',
					0,
					'Select Request Category')
			"; 
			simple_query($sql);
			
			$cat_id = mysqli_insert_id($datasource);
			
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'engine_oil', 'Engine Oil',0)
			";
			simple_query($sql);
						
			$sql = "
				insert into option_values
					(cat_id,
					fname,
					fvalue,
					deleted)
					
				values ($cat_id, 'tires', 'Tires',0)
			";
			simple_query($sql);
		}
		
		//new maintenance request
		if(!table_exists("maint_requests")) {
			$sql = "
				create table maint_requests (
					id int not null auto_increment, 
					user_id int,
					linedate_added datetime,
					linedate_scheduled datetime,
					linedate_completed datetime,
					odometer_reading int,
					equip_type int,
					ref_id int,
					down_time_hours decimal(10,2),
					cost decimal(10,2),
					maint_desc text,
					recur_days int,
					recur_mileage int,
					recur_flag int,
					recur_ref int,	
					urgent int,
					active int,
					deleted int,					
					primary key(id))
			";
			simple_query($sql);
		}
		//line item for request
		if(!table_exists("maint_line_items")) {
			$sql = "
				create table maint_line_items (
					id int not null auto_increment, 
					ref_id int,
					cat_id int,
					lineitem_desc text,
					linedate_added datetime,
					quantity int,
					make varchar(100),
					model varchar(100),
					down_time_hours decimal(10,2),
					item_cost decimal(10,2),
					location_front int,
					location_left int,
					location_top int,
					location_inside int,
					active int,
					deleted int,	
					primary key(id))
			";
			simple_query($sql);
		}
		
		//add expires date to quotes
		if(!field_exists("quotes", "linedate_expires")) {
			$sql = "
				alter table quotes add column linedate_expires datetime default '0000-00-00 00:00:00'
			";
			simple_query($sql);
		}
		
		//add stop miles to quote_stops
		if(!field_exists("quotes_stops", "stop_miles")) {
			$sql = "
				alter table quotes_stops add column stop_miles int default 0
			";
			simple_query($sql);
		}
		
		//add stop miles to quote_stops
		if(!field_exists("quotes", "map_storage")) {
			$sql = "
				alter table quotes add column map_storage text default ''
			";
			simple_query($sql);
		}
		
		
		//add default email to the list
		if(!default_exists('company_email_address')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('company_email_address',
					'system@conardtransportation.com',
					'Company Email Address',
					1,
					'Company Information')
			";
			simple_query($sql);
		}
		
		//extended contact list for customers/etc...
		if(!table_exists("customer_contacts")) {
			$sql = "
				create table customer_contacts (
					id int not null auto_increment, 
					customer_id int,
					linedate_added datetime,
					contact_name varchar(100),
					address1 varchar(255),
					address2 varchar(255),
					city varchar(255),
					state varchar(100),
					zip varchar(50),
					email varchar(100),
					phone_home varchar(50),
					phone_work varchar(50),
					phone_cell varchar(50),
					phone_fax varchar(50),
					active int,
					deleted int,	
					primary key(id))
			";
			simple_query($sql);
		}
		
		//extended contact list for customers/etc...
		if(!table_exists("help_desk")) {
			$sql = "
				create table help_desk (
					id int not null auto_increment, 
					page_name varchar(255),
					field_name varchar(255),
					linedate_added datetime,
					quick_text varchar(255),
					help_text text,
					active int,
					deleted int,	
					primary key(id))
			";
			simple_query($sql);
		}
		
		//add flag to trucks for company owned trucks
		if(!field_exists("trucks", "company_owned")) {
			$sql = "
				alter table trucks add column company_owned int default 0
			";
			simple_query($sql);
		}
		//add flag to trailers for company owned trailers
		if(!field_exists("trailers", "company_owned")) {
			$sql = "
				alter table trailers add column company_owned int default 0
			";
			simple_query($sql);
		}
				
		//add flag to update surcharge, or prompt user to change it
		if(!field_exists("load_handler", "update_fuel_surcharge")) {
			$sql = "
				alter table load_handler add column update_fuel_surcharge date default '0000-00-00'
			";
			simple_query($sql);
		}
		
		//report entry for Has Completed Trip Pack
		if(!table_exists("trip_packs")) {
			$sql = "
				create table trip_packs (
					id int not null auto_increment, 
					load_id int,
					dispatch_id int,
					truck_id int,
					driver_id int,
					linedate_added datetime,
					deleted int,	
					primary key(id))
			";
			simple_query($sql);
		}
		
		
		//include info for driver, truck, trailer, cost, deductable, insurance, documents and bills, down-time, maint request, etc...dates
		//report entry for Has Completed Trip Pack
		if(!table_exists("accident_reports")) {
			$sql = "
				create table accident_reports (
					id int not null auto_increment, 
					truck_id int,
					trailer_id int,
					driver_id int,
					dispatch_id int,
					load_id int,
					linedate_added datetime,
					accident_date datetime,
					insurance_company varchar(255),
					claim_date datetime,
					insurance_claim int,
					insurance_covered int,										
					accident_desc text,
					accident_cost decimal(10,2),
					accident_deductable decimal(10,2),
					accident_downtime decimal(10,2),					
					injury_desc text,
					injury_cost decimal(10,2),
					injury_deductable decimal(10,2),
					injury_downtime decimal(10,2),					
					driver_desc text,
					driver_cost decimal(10,2),
					driver_deductable decimal(10,2),
					driver_downtime decimal(10,2),										
					maint_id int,
					reviewed int,					
					active int,
					deleted int,	
					primary key(id))
			";
			simple_query($sql);
		}
		//add flag to update surcharge, or prompt user to change it
		if(!field_exists("accident_reports", "completed_date")) {
			$sql = "
				alter table accident_reports add column completed_date date default '0000-00-00'
			";
			simple_query($sql);
		}
				
		if(!verify_index('load_handler','preplan_driver_id')) {
			$sql = "
				alter table load_handler
					add index `preplan_driver_id`(`preplan_driver_id`)
			";
			simple_query($sql);
		}
		
		//Added Dec 1, 2011
		//add flag to trucks for no insurance...also removes from active truck counts
		if(!field_exists("trucks", "no_insurance")) {
			$sql = "
				alter table trucks add column no_insurance int default 0
			";
			simple_query($sql);
		}
		//add flag to drivers for employer_id
		if(!field_exists("drivers", "employer_id")) {
			$sql = "
				alter table drivers add column employer_id int default 0
			";
			simple_query($sql);
		}
		//position z
		if(!get_option_cat_id('employer_list')) {
			$sql = "
				insert into option_cat
					(cat_name,
					cat_desc,
					deleted,
					blank_text)
					
				values ('employer_list',
					'Employers',
					0,
					'Select Employer')
			"; 
			simple_query($sql);
		}
		//removed old field employer...replaced by employer_id and option_cat/option_values section to streamline entries.
		if(field_exists("drivers", "employer")) {
			$sql = "
				alter table drivers drop column employer
			";
			simple_query($sql);
		}
		//add flag to mark master loads
		if(!field_exists("load_handler", "master_load")) {
			$sql = "
				alter table load_handler add column master_load int default 0
			";
			simple_query($sql);
		}
		//add flag to mark master loads
		if(!field_exists("load_handler", "dedicated_load")) {
			$sql = "
				alter table load_handler add column dedicated_load int default 0
			";
			simple_query($sql);
		}
		
		if(!table_exists("punch_clock")) {
			$sql = "
				create table punch_clock (
					id int not null auto_increment,
					linedate_added datetime,
					user_id int,
					ip_address varchar(50),
					clock_auto int,
					clock_mode int,
					clock_hrs decimal(7,2),	
					notes varchar(255),				
					primary key(id))
			";
			simple_query($sql);
		}
		
		//add flag to mark master loads
		if(!field_exists("notes", "deadline")) {
			$sql = "
				alter table notes add column deadline datetime default '0000-00-00 00:00:00'
			";
			simple_query($sql);
		}
		
		//add punch_clock settings to user...
		if(!field_exists("users","punch_clock")) {
			$sql = "alter table users add column punch_clock int default 0";
			simple_query($sql);
		}
		if(!default_exists('time_clock_auto_time')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('time_clock_auto_time','16:30:00','Time Clock Default Clockout Time',1,'Time Clock'),
					('time_clock_ip_restriction','','Time Clock IP Restriction',1,'Time Clock')
			";
			simple_query($sql);
		}
		
		//additional variables for comparison report...
		if(!default_exists('tires_per_mile')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('truck_accidents_per_mile','0.10','Accidents Per Mile',1,'Financial'),					
					('tires_per_mile','0.10','Tires Per Mile',1,'Financial'),
					('mileage_expense_per_mile','0.10','Mileage Expenses Per Mile',1,'Financial'),					
					('misc_expense_per_mile','0.10','Misc. Expenses Per Mile',1,'Financial')
					
			";
			/*
					('admin_expense_per_mile','0.10','Admin Expenses Per Mile',1,'Financial'),
					('insurance_per_mile','0.10','Insurance Per Mile',1,'Financial'),
					('truck_repairs_per_mile','0.10','Truck Repairs Per Mile',1,'Financial'),
					('trailer_repairs_per_mile','0.10','Trailer Repairs Per Mile',1,'Financial'),
					('trailer_accidents_per_mile','0.10','Trailer Accidents Per Mile',1,'Financial'),
					('tolls_per_mile','0.10','Tolls Per Mile',1,'Financial'),
					('weigh_ticket_per_mile','0.10','Weigh Tickets Per Mile',1,'Financial')
					
			*/
			simple_query($sql);
		}
		//add punch_clock settings to user...
		if(!field_exists("defaults","title_notes")) {
			$sql = "alter table defaults add column title_notes varchar(255) default ''";
			simple_query($sql);
		}
		
		//add chart_id for accounting purposes linking trucks to accounts for payroll reporting...
		if(!field_exists("dispatch_expenses","chart_id")) {
			$sql = "alter table dispatch_expenses add column chart_id int default 0";
			simple_query($sql);
		}
		//add chart_id for accounting purposes linking trucks to accounts for payroll reporting...
		if(!field_exists("drivers_expenses","chart_id")) {
			$sql = "alter table drivers_expenses add column chart_id int default 0";
			simple_query($sql);
		}
			
			
		$current_version = 1.14;
		update_version($current_version);
	}
	
	if($current_version == 1.14) {
		if(!field_exists("drivers","emergency_contact_phone")) {
			$sql = "alter table drivers add column emergency_contact_phone varchar(100),
								add column emergency_contact_name varchar(100)";
			simple_query($sql);
		}	
		
		//extended contact list for customers/etc...
		if(!table_exists("budget")) {
			$sql = "
				create table budget (
					id int not null auto_increment, 
					linedate_added datetime,
					linedate_start datetime,
					linedate_ended datetime,
					budget_name varchar(255),					
					active int,
					deleted int,	
					primary key(id))
			";
			simple_query($sql);
		}
		if(!table_exists("budget_items")) {
			$sql = "
				create table budget_items (
					id int not null auto_increment,
					budget_id int, 
					budget_cat int,
					per_mile decimal(10,3),
					per_truck decimal(10,3),
					per_trailer decimal(10,3),
					per_driver decimal(10,3),
					per_dispatch decimal(10,3),
					per_load decimal(10,3),
					flat_amount decimal(10,2),
					budget_amount decimal(10,3),
					primary key(id))
			";
			simple_query($sql);
		}
		
		//budget settings to store at time of load so that changes do not affect the past budget...add as group
		if(!field_exists("load_handler","budget_average_mpg")) {
			
			//mimic default settings with these
			$sql = "alter table load_handler add column budget_average_mpg decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_days_in_month decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_labor_per_hour decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_labor_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_labor_per_mile_team decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_driver_week_hours decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_tractor_maint_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_trailer_maint_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			
			$sql = "alter table load_handler add column budget_truck_accidents_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_tires_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_mileage_exp_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_misc_exp_per_mile decimal(10,4) default '0.0000'";
			simple_query($sql);
			
			//mimic important fixed items			
			$sql = "alter table load_handler add column budget_cargo_insurance decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_general_liability decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_liability_damage decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_payroll_admin decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_rent decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_tractor_lease decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_trailer_exp decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_trailer_lease decimal(10,4) default '0.0000'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_misc_exp decimal(10,4) default '0.0000'";
			simple_query($sql);
		}
		if(!field_exists("load_handler","budget_active_trucks")) {
			
			//mimic default settings with these
			$sql = "alter table load_handler add column budget_active_trucks int default '0'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_active_trailers int default '0'";
			simple_query($sql);
			$sql = "alter table load_handler add column budget_day_variance decimal(10,2) default '0.0000'";
			simple_query($sql);
		}		
		
		$current_version = 1.15;
		update_version($current_version);
	}

	if($current_version == 1.15) {
		if(!field_exists("load_handler", "linedate_edi_invoice_response")) {
			$sql = "
				alter table load_handler add column linedate_edi_invoice_response datetime default '0000-00-00'
			";
			simple_query($sql);
		}
		if(!field_exists("drivers", "linedate_cov_expires")) {
			$sql = "
				alter table drivers add column linedate_cov_expires datetime default '0000-00-00 00:00:00'
			";
			simple_query($sql);
		}
		if(!field_exists("customers", "stoplight_warn_notes")) {
			$sql = "
				alter table customers add column stoplight_warn_notes text
			";
			simple_query($sql);
		}
		
		//add quote budget items
		if(!field_exists("quotes", "tires_per_mile")) {
			$sql = "
				alter table quotes add column tires_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("quotes", "accidents_per_mile")) {
			$sql = "
				alter table quotes add column accidents_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("quotes", "mile_exp_per_mile")) {
			$sql = "
				alter table quotes add column mile_exp_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("quotes", "misc_per_mile")) {
			$sql = "
				alter table quotes add column misc_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		
		//add trucks_log budget items
		if(!field_exists("trucks_log", "tires_per_mile")) {
			$sql = "
				alter table trucks_log add column tires_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("trucks_log", "accidents_per_mile")) {
			$sql = "
				alter table trucks_log add column accidents_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("trucks_log", "mile_exp_per_mile")) {
			$sql = "
				alter table trucks_log add column mile_exp_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("trucks_log", "misc_per_mile")) {
			$sql = "
				alter table trucks_log add column misc_per_mile decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		
		//more fields for budget tracking...on dispatch level...
		if(!field_exists("trucks_log", "truck_cost")) {
			$sql = "
				alter table trucks_log add column truck_cost decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		if(!field_exists("trucks_log", "trailer_cost")) {
			$sql = "
				alter table trucks_log add column trailer_cost decimal(10,4) default '0.0000'
			";
			simple_query($sql);
		}
		/*
		if(!field_exists("trailers", "dedicated_trailer")) {
			$sql = "
				alter table trailers add column dedicated_trailer int default 0
			";
			simple_query($sql);
		}
		*/
		if(!field_exists("trailers_dropped", "dedicated_trailer")) {
			$sql = "
				alter table trailers_dropped add column dedicated_trailer int default 0
			";
			simple_query($sql);
			
			$sql = "alter table trailers_dropped add index dedicated_trailer(dedicated_trailer)";
			simple_query($sql);	
		}
		
		if(!field_exists("drivers_unavailable", "reason_unavailable")) {
			$sql = "
				alter table drivers_unavailable add column reason_unavailable varchar(255) default ''
			";
			simple_query($sql);
		}
				
		//added May 2012 for comparison report account codes per budget section so that Dale and company can change the accounts going into the budget calculations...and quickly add them as well.
		if(!table_exists("comparison_sections")) {
			$sql = "
				create table comparison_sections (
					id int not null auto_increment,
					linedate_added datetime,
					budget_name varchar(255),
					deleted int,
					active int,
					notes varchar(255),
					comparison_code int,
					primary key(id))
			";
			simple_query($sql);
			
			$sql = "
				create table comparison_section_items (
					id int not null auto_increment,
					section_id int,
					linedate_added datetime,
					account_code varchar(255),
					active int,
					deleted int,
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table comparison_section_items add index section_id(section_id)";
			simple_query($sql);
			$sql = " alter table comparison_section_items add index active(active)";
			simple_query($sql);
			$sql = " alter table comparison_section_items add index deleted(deleted)";
			simple_query($sql);
			$sql = " alter table comparison_section_items add index account_code(account_code)";
			simple_query($sql);
		}
		
		
		$current_version = 1.16;
		update_version($current_version);
	}
	
	if($current_version == 1.16) {
		//add default email to the list
		if(!default_exists('sicap_integration_key')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('sicap_integration_url',
					'http://trucking.conardlogistics.com/accounting',
					'API Integration URL',
					1,
					'Financial'),
					
					('sicap_integration_key',
					'',
					'API Integration Key',
					1,
					'Financial')
			";
			simple_query($sql);
		}
		
		if(!field_exists("users", "uuid")) {
			$sql = "
				alter table users add column uuid varchar(255) default ''
			";
			simple_query($sql);
			
			$sql = "alter table users add index uuid(uuid)";
			simple_query($sql);	
		}
		
		if(!field_exists("drivers", "hazmat")) {
			$sql = "
				alter table drivers add column hazmat int default 0
			";
			simple_query($sql);
			
			$sql = "alter table drivers add index hazmat(hazmat)";
			simple_query($sql);	
		}
		
		//added July 2012 for comparison report account codes per budget section so that Dale and company can change the accounts going into the budget calculations...and quickly add them as well.
		if(!table_exists("comparison_scenarios")) {
			$sql = "
				create table comparison_scenarios (
					id int not null auto_increment,
					linedate_added datetime,
					user_id int,
					active int,
					section_id int,
					section_setting varchar(100),
					section_value decimal(10,3),
					section_other varchar(100),
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table comparison_scenarios add index user_id(user_id)";
			simple_query($sql);
			$sql = " alter table comparison_scenarios add index active(active)";
			simple_query($sql);
			$sql = " alter table comparison_scenarios add index section_id(section_id)";
			simple_query($sql);
			$sql = " alter table comparison_scenarios add index section_setting(section_setting)";
			simple_query($sql);
			$sql = " alter table comparison_scenarios add index section_value(section_value)";
			simple_query($sql);			
		}
		
		//added July 2012 for comparison report archiving purposes
		if(!table_exists("comparison_archive")) {
			$sql = "
				create table comparison_archive (
					id int not null auto_increment,
					linedate_added datetime,
					linedate_start datetime,
					linedate_end datetime,
					section_id int,
					sales_percent decimal(10,2),
					budget_value decimal(10,2),
					actual_value decimal(10,2), 
					variance_value decimal(10,2),
					difference decimal(10,2),
					difference_percent decimal(10,2),			 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table comparison_archive add index linedate_start(linedate_start)";
			simple_query($sql);
			$sql = " alter table comparison_archive add index linedate_end(linedate_end)";
			simple_query($sql);
			$sql = " alter table comparison_archive add index section_id(section_id)";
			simple_query($sql);	
		}
		
		//new cookie login settings
		if(!table_exists("user_cookies")) {
			$sql = "
				create table user_cookies (
					id int not null auto_increment,
					linedate_added datetime,
					user_id int,
					time_secs int,
					uuid varchar(255),			 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table user_cookies add index user_id(user_id)";
			simple_query($sql);
			$sql = " alter table user_cookies add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table user_cookies add index uuid(uuid)";
			simple_query($sql);	
		}
		if(!default_exists('login_cookie_expiration')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('login_cookie_expiration',
					'0',
					'Login Cookie Minutes',
					1,
					'General Settings')
			";
			simple_query($sql);
		}
		
		//added to trucks in July 2012
		if(!field_exists("trucks", "prepass")) {
			$sql = "
				alter table trucks add column prepass varchar(255)
			";
			simple_query($sql);
			
			$sql = "alter table trucks add index prepass(prepass)";
			simple_query($sql);	
		}
		if(!field_exists("trucks", "insurance_exclude")) {
			$sql = "
				alter table trucks add column insurance_exclude int default 0
			";
			simple_query($sql);
			
			$sql = "alter table trucks add index insurance_exclude(insurance_exclude)";
			simple_query($sql);	
		}
		
		//new default settings for load and dispatch windows (in pixels)...added July 2012
		if(!default_exists('window_size_load_width')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_load_width',
					'1000',
					'Manage Load Width (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_load_height')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_load_height',
					'1000',
					'Manage Load Height (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_dispatch_width')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_dispatch_width',
					'1000',
					'Manage Dispatch Width (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_dispatch_height')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_dispatch_height',
					'1000',
					'Manage Dispatch Height (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_trailer_drop_width')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_trailer_drop_width',
					'1000',
					'Manage Trailer Drop Width (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_trailer_drop_height')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_trailer_drop_height',
					'1000',
					'Manage Trailer Drop Height (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_misc_width')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_misc_width',
					'1000',
					'Manage Other Pop-ups Width (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		if(!default_exists('window_size_misc_height')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('window_size_misc_height',
					'1000',
					'Manage Other Pop-ups Height (pixels)',
					1,
					'Window Sizing')
			";
			simple_query($sql);
		}
		
		//peoplenet truck tracking interface...
		if(!default_exists('peoplenet_account_number')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_account_number',
					'0',
					'PeopleNet Account Number',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_account_password')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_account_password',
					'0',
					'PeopleNet Account Password',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!field_exists("trucks", "peoplenet_tracking")) {
			$sql = "
				alter table trucks add column peoplenet_tracking int default 0
			";
			simple_query($sql);
			
			$sql = "alter table trucks add index peoplenet_tracking(peoplenet_tracking)";
			simple_query($sql);	
		}
		
		if(!default_exists('google_map_api_key')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('google_map_api_key',
					'0',
					'Google Map API Key',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
				
		$current_version = 1.17;
		update_version($current_version);
	}
	
	if($current_version == 1.17) {
		if(!default_exists('fedex_ftp_username')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('fedex_ftp_username',
					'',
					'Fedex FTP Username',
					1,
					'EDI'),
					
					('fedex_ftp_password',
					'',
					'Fedex FTP Password',
					1,
					'EDI'),
					
					('fedex_ftp_address',
					'',
					'Fedex FTP Address',
					1,
					'EDI')
					
			";
			simple_query($sql);
		}
			
		if(!field_exists("load_handler_stops", "latitude")) {
			$sql = "
				alter table load_handler_stops add column latitude float(10,6) default 0
			";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index latitude(latitude)";
			simple_query($sql);	
		}
		if(!field_exists("load_handler_stops", "longitude")) {
			$sql = "
				alter table load_handler_stops add column longitude float(10,6) default 0
			";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index longitude(longitude)";
			simple_query($sql);	
		}
		if(!field_exists("load_handler", "pickup_number")) {
			$sql = "
				alter table load_handler add column pickup_number varchar(255)
			";
			simple_query($sql);
			
			$sql = "alter table load_handler add index pickup_number(pickup_number)";
			simple_query($sql);	
		}
		if(!field_exists("load_handler", "delivery_number")) {
			$sql = "
				alter table load_handler add column delivery_number varchar(255)
			";
			simple_query($sql);
			
			$sql = "alter table load_handler add index delivery_number(delivery_number)";
			simple_query($sql);	
		}
		
		if(!field_exists("customers", "payment_notes")) {
			$sql = "
				alter table customers add column payment_notes text
			";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler_stops", "linedate_arrival")) {
			$sql = "
				alter table load_handler_stops add column linedate_arrival datetime default '0000-00-00 00:00:00'
			";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index linedate_arrival(linedate_arrival)";
			simple_query($sql);	
		}
		
				
		if(!field_exists("load_handler_stops", "linedate_updater")) {
			$sql = "
				alter table load_handler_stops add column linedate_updater datetime default '0000-00-00 00:00:00'
			";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index linedate_updater(linedate_updater)";
			simple_query($sql);	
		}
		
		if(!field_exists("trucks", "apu_number")) {
			$sql = "
				alter table trucks add column apu_number varchar(100) default ''
			";
			simple_query($sql);
			
			$sql = "alter table trucks add index apu_number(apu_number)";
			simple_query($sql);	
		}
		
		if(!table_exists("truck_tracking_canned_message")) {
			$sql = "
				create table truck_tracking_canned_message (
					id int not null auto_increment,
					linedate_added datetime,
					user_id int,
					active int,
					deleted int,
					canned_subject varchar(250),
					canned_message text,	 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table truck_tracking_canned_message add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table truck_tracking_canned_message add index user_id(user_id)";
			simple_query($sql);
			$sql = " alter table truck_tracking_canned_message add index active(active)";
			simple_query($sql);		
			$sql = " alter table truck_tracking_canned_message add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table truck_tracking_canned_message add index canned_subject(canned_subject)";
			simple_query($sql);			
		}
		
		if(!field_exists("customers", "use_fuel_surcharge")) {
			$sql = "
				alter table customers add column use_fuel_surcharge int default 0
			";
			simple_query($sql);
			
			$sql = "alter table customers add index use_fuel_surcharge(use_fuel_surcharge)";
			simple_query($sql);	
			
			//add more indexing to this table..............Added Oct. 2012
			$sql = "alter table customers add index deleted(deleted)";
			simple_query($sql);	
			$sql = "alter table customers add index active(active)";
			simple_query($sql);
			$sql = "alter table customers add index sicap_id(sicap_id)";
			simple_query($sql);				
			$sql = "alter table customers add index credit_hold(credit_hold)";
			simple_query($sql);	
			$sql = "alter table customers add index slow_pays(slow_pays)";
			simple_query($sql);					
			$sql = "alter table customers add index email_invoice(email_invoice)";
			simple_query($sql);	
			$sql = "alter table customers add index linedate_added(linedate_added)";
			simple_query($sql);
			
			//different table but added at the same time...Added Oct. 2012
			$sql = "alter table log_fuel_updates add index fuel_surcharge(fuel_surcharge)";
			simple_query($sql);	
			$sql = "alter table fuel_surcharge add index fuel_surcharge(fuel_surcharge)";
			simple_query($sql);	
			$sql = "alter table fuel_surcharge add index customer_id(customer_id)";
			simple_query($sql);	
			$sql = "alter table fuel_surcharge add index range_lower(range_lower)";
			simple_query($sql);	
			$sql = "alter table fuel_surcharge add index range_upper(range_upper)";
			simple_query($sql);	
			
			//more indexing for speedy queries............Added Oct. 2012
			$sql = "alter table customer_contacts add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table customer_contacts add index customer_id(customer_id)";
			simple_query($sql);
			
			$sql = "alter table comparison_sections add index deleted(deleted)";
			simple_query($sql);	
			$sql = "alter table comparison_sections add index active(active)";
			simple_query($sql);
			$sql = "alter table comparison_sections add index comparison_code(comparison_code)";
			simple_query($sql);
			
			$sql = "alter table drivers add index active(active)";
			simple_query($sql);
			$sql = "alter table drivers add index name_driver_first(name_driver_first)";
			simple_query($sql);
			$sql = "alter table drivers add index name_driver_last(name_driver_last)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_birthday(linedate_birthday)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_drugtest(linedate_drugtest)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_started(linedate_started)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_available_notes(linedate_available_notes)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_cov_expires(linedate_cov_expires)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_license_expires(linedate_license_expires)";
			simple_query($sql);
			$sql = "alter table drivers add index linedate_driver_has_load(linedate_driver_has_load)";
			simple_query($sql);
			$sql = "alter table drivers add index attached_truck_id(attached_truck_id)";
			simple_query($sql);
			$sql = "alter table drivers add index attached_trailer_id(attached_trailer_id)";
			simple_query($sql);
			$sql = "alter table drivers add index hide_unavailable(hide_unavailable)";
			simple_query($sql);
			$sql = "alter table drivers add index employer_id(employer_id)";
			simple_query($sql);
			
			$sql = "alter table equipment_history add index equipment_type_id(equipment_type_id)";
			simple_query($sql);
			$sql = "alter table equipment_history add index equipment_id(equipment_id)";
			simple_query($sql);
			$sql = "alter table equipment_history add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table equipment_history add index linedate_aquired(linedate_aquired)";
			simple_query($sql);
			$sql = "alter table equipment_history add index linedate_returned(linedate_returned)";
			simple_query($sql);
			$sql = "alter table equipment_history add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table equipment_history add index replacement(replacement)";
			simple_query($sql);
			$sql = "alter table equipment_history add index xref_id(xref_id)";
			simple_query($sql);
			$sql = "alter table equipment_history add index replacement_xref_id(replacement_xref_id)";
			simple_query($sql);
			
			$sql = "alter table help_desk add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table help_desk add index active(active)";
			simple_query($sql);
			$sql = "alter table help_desk add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table help_desk add index page_name(page_name)";
			simple_query($sql);
			$sql = "alter table help_desk add index field_name(field_name)";
			simple_query($sql);
			
			$sql = "alter table load_handler add index days_run_otr(days_run_otr)";
			simple_query($sql);
			$sql = "alter table load_handler add index days_run_hourly(days_run_hourly)";
			simple_query($sql);
			$sql = "alter table load_handler add index hours_worked(hours_worked)";
			simple_query($sql);
			$sql = "alter table load_handler add index estimated_miles(estimated_miles)";
			simple_query($sql);
			$sql = "alter table load_handler add index deadhead_miles(deadhead_miles)";
			simple_query($sql);
			$sql = "alter table load_handler add index loaded_miles_hourly(loaded_miles_hourly)";
			simple_query($sql);
			$sql = "alter table load_handler add index master_load(master_load)";
			simple_query($sql);
			$sql = "alter table load_handler add index dedicated_load(dedicated_load)";
			simple_query($sql);
			$sql = "alter table load_handler add index sicap_invoice_number(sicap_invoice_number)";
			simple_query($sql);
			$sql = "alter table load_handler add index linedate_invoiced(linedate_invoiced)";
			simple_query($sql);
			$sql = "alter table load_handler add index update_fuel_surcharge(update_fuel_surcharge)";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index shipper_name(shipper_name)";
			simple_query($sql);
			
			$sql = "alter table trailers add index trailer_name(trailer_name)";
			simple_query($sql);
			$sql = "alter table trailers add index active(active)";
			simple_query($sql);
			$sql = "alter table trailers add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table trailers add index allow_multiple(allow_multiple)";
			simple_query($sql);
			$sql = "alter table trailers add index dedicated_trailer(dedicated_trailer)";
			simple_query($sql);
			$sql = "alter table trailers add index location_updated(location_updated)";
			simple_query($sql);
			$sql = "alter table trailers add index linedate_aquired(linedate_aquired)";
			simple_query($sql);
			$sql = "alter table trailers add index linedate_returned(linedate_returned)";
			simple_query($sql);
			$sql = "alter table trailers add index no_insurance(no_insurance)";
			simple_query($sql);
			$sql = "alter table trailers add index rental_flag(rental_flag)";
			simple_query($sql);
			$sql = "alter table trailers add index company_owned(company_owned)";
			simple_query($sql);
			
			$sql = "alter table trucks add index name_truck(name_truck)";
			simple_query($sql);
			$sql = "alter table trucks add index active(active)";
			simple_query($sql);
			$sql = "alter table trucks add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table trucks add index rental(rental)";
			simple_query($sql);
			$sql = "alter table trucks add index linedate_aquired(linedate_aquired)";
			simple_query($sql);
			$sql = "alter table trucks add index linedate_returned(linedate_returned)";
			simple_query($sql);
			$sql = "alter table trucks add index count_billable(count_billable)";
			simple_query($sql);
			$sql = "alter table trucks add index company_owned(company_owned)";
			simple_query($sql);
			$sql = "alter table trucks add index no_insurance(no_insurance)";
			simple_query($sql);
			$sql = "alter table trucks add index replacement(replacement)";
			simple_query($sql);
			
			$sql = "alter table trip_packs add index location_added(location_added)";
			simple_query($sql);
			$sql = "alter table trip_packs add index load_id(load_id)";
			simple_query($sql);
			$sql = "alter table trip_packs add index dispatch_id(dispatch_id)";
			simple_query($sql);
			$sql = "alter table trip_packs add index truck_id(truck_id)";
			simple_query($sql);
			$sql = "alter table trip_packs add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table trip_packs add index deleted(deleted)";
			simple_query($sql);
			
			$sql = "alter table trailers_dropped add index drop_completed(drop_completed)";
			simple_query($sql);
			$sql = "alter table trailers_dropped add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table trailers_dropped add index linedate(linedate)";
			simple_query($sql);
			
			$sql = "alter table trucks_odometer add index deleted(deleted)";
			simple_query($sql);
			
			$sql = "alter table users add index active(active)";
			simple_query($sql);
			$sql = "alter table users add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table users add index username(username)";
			simple_query($sql);
			$sql = "alter table users add index name_first(name_first)";
			simple_query($sql);
			$sql = "alter table users add index name_last(name_last)";
			simple_query($sql);
			$sql = "alter table users add index access(access)";
			simple_query($sql);
			$sql = "alter table users add index inventory_access(inventory_access)";
			simple_query($sql);
			$sql = "alter table users add index punch_clock(punch_clock)";
			simple_query($sql);			
		}
		
		if(!table_exists("trailer_switched")) {
			$sql = "
				create table trailer_switched (
					id int not null auto_increment,
					linedate_added datetime,
					linedate datetime,
					dispatch_id int,
					deleted int,
					old_trailer_id int,
					new_trailer_id int,
					old_trailer_cost decimal(10,2),
					new_trailer_cost decimal(10,2),	 
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table trailer_switched add index dispatch_id(dispatch_id)";
			simple_query($sql);				
			$sql = " alter table trailer_switched add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table trailer_switched add index linedate(linedate)";
			simple_query($sql);
			$sql = " alter table trailer_switched add index old_trailer_id(old_trailer_id)";
			simple_query($sql);
			$sql = " alter table trailer_switched add index new_trailer_id(new_trailer_id)";
			simple_query($sql);		
			$sql = " alter table trailer_switched add index deleted(deleted)";
			simple_query($sql);
			$sql = " alter table trailer_switched add index old_trailer_cost(old_trailer_cost)";
			simple_query($sql);
			$sql = " alter table trailer_switched add index new_trailer_cost(new_trailer_cost)";
			simple_query($sql);				
		}
			
		if(!field_exists("trailer_switched", "stop_id")) {
			$sql = "
				alter table trailer_switched add column stop_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table trailer_switched add index stop_id(stop_id)";
			simple_query($sql);	
		}	
				
		if(!default_exists('load_board_display_timeoff')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('load_board_display_timeoff',
					'0',
					'TimeOff Panel ',
					1,
					'Load Board Display')
			";
			simple_query($sql);
		}
		if(!default_exists('load_board_display_calendar')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('load_board_display_calendar',
					'0',
					'Calendar',
					1,
					'Load Board Display')
			";
			simple_query($sql);
		}
		if(!default_exists('load_board_display_events')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('load_board_display_events',
					'0',
					'Events Panel',
					1,
					'Load Board Display')
			";
			simple_query($sql);
		}
		if(!default_exists('load_board_display_notes')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('load_board_display_notes',
					'0',
					'Notes Panel',
					1,
					'Load Board Display')
			";
			simple_query($sql);
		}
		if(!default_exists('load_board_display_peoplenet')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('load_board_display_peoplenet',
					'0',
					'PeopleNet Messages',
					1,
					'Load Board Display')
			";
			simple_query($sql);
		}	
		
		if(!field_exists("load_handler_stops", "start_trailer_id")) {
			$sql = "
				alter table load_handler_stops add column start_trailer_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index start_trailer_id(start_trailer_id)";
			simple_query($sql);	
		}
		if(!field_exists("load_handler_stops", "end_trailer_id")) {
			$sql = "
				alter table load_handler_stops add column end_trailer_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index end_trailer_id(end_trailer_id)";
			simple_query($sql);	
		}
				
		$current_version = 1.18;
		update_version($current_version);
	}	
	
	if($current_version == 1.18) {
		
		if(!table_exists("log_api_import")) {
			$sql = "
				create table log_api_import (
					id int not null auto_increment, 
					api_vendor_name varchar(100),
					filename varchar(255),
					linedate_added datetime,
					processed int default 0,
					primary key(id))
			";
			simple_query($sql);
		}		
		
		
		if(!field_exists("customers", "override_credit_hold")) {
			$sql = "
				alter table customers add column override_credit_hold int default 0
			";
			simple_query($sql);
			
			$sql = "alter table customers add index override_credit_hold(override_credit_hold)";
			simple_query($sql);	
		}
		
		if(!default_exists('gmt_offset_peoplenet')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('gmt_offset_peoplenet',
					'0',
					'PeopleNet GMT Offset',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		
		if(!table_exists("drivers_employer_change")) {
			$sql = "
				create table drivers_employer_change (
					id int not null auto_increment, 
					linedate_added datetime,
					linedate datetime,
					driver_id int default 0,
					old_employer_id int default 0,
					new_employer_id int default 0,
					deleted int default 0,
					primary key(id))
			";
			simple_query($sql);
						
			$sql = "alter table drivers_employer_change add index linedate_added(linedate_added)";
			simple_query($sql);	
			$sql = "alter table drivers_employer_change add index linedate(linedate)";
			simple_query($sql);	
			$sql = "alter table drivers_employer_change add index deleted(deleted)";
			simple_query($sql);	
			$sql = "alter table drivers_employer_change add index driver_id(driver_id)";
			simple_query($sql);	
			$sql = "alter table drivers_employer_change add index old_employer_id(old_employer_id)";
			simple_query($sql);	
			$sql = "alter table drivers_employer_change add index new_employer_id(new_employer_id)";
			simple_query($sql);	
		}
		
		if(!field_exists("trucks_log", "employer_id")) {
			$sql = "
				alter table trucks_log add column employer_id int default 0
			";
			simple_query($sql);
			
			$sql = "
				alter table trucks_log add column truck_rental int default 0
			";
			simple_query($sql);
			
			
			$sql = "alter table trucks_log add index employer_id(employer_id)";
			simple_query($sql);	
			
			$sql = "alter table trucks_log add index truck_rental(truck_rental)";
			simple_query($sql);	
		}
				
		if(!default_exists('peoplenet_geofencing_arriving')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_geofencing_arriving',
					'0',
					'Arriving radius (feet)',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_geofencing_arrived')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_geofencing_arrived',
					'0',
					'Arrived radius (feet)',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_geofencing_departed')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_geofencing_departed',
					'0',
					'Departed radius (feet)',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_geofencing_tolerance')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_geofencing_tolerance',
					'0',
					'GeoFencing Tolerance (feet)',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		
		
		if(!field_exists("customers", "hot_load_switch")) {
			
			$sql = "alter table customers add column hot_load_switch int default 0";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_timer int default 0";
			simple_query($sql);
			
			$sql = "alter table customers add column hot_load_email_arriving varchar(255)";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_email_arrived varchar(255)";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_email_departed varchar(255)";
			simple_query($sql);
			
			$sql = "alter table customers add column hot_load_email_msg_arriving text";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_email_msg_arrived text";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_email_msg_departed text";
			simple_query($sql);
						
			
			$sql = "alter table customers add index hot_load_switch(hot_load_switch)";
			simple_query($sql);
			$sql = "alter table customers add index hot_load_timer(hot_load_timer)";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "hot_load_email_msg_arriving_shipper")) {
						
			$sql = "alter table customers add column hot_load_email_msg_arriving_shipper text";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_email_msg_arrived_shipper text";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_email_msg_departed_shipper text";
			simple_query($sql);			
		}
		
		if(!field_exists("customers", "hot_load_radius_arriving")) {
			
			$sql = "alter table customers add column hot_load_radius_arriving int default 0";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_radius_arrived int default 0";
			simple_query($sql);
			$sql = "alter table customers add column hot_load_radius_departed int default 0";
			simple_query($sql);
			
			$sql = "alter table customers add index hot_load_radius_arriving(hot_load_radius_arriving)";
			simple_query($sql);
			$sql = "alter table customers add index hot_load_radius_arrived(hot_load_radius_arrived)";
			simple_query($sql);
			$sql = "alter table customers add index hot_load_radius_departed(hot_load_radius_departed)";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "geofencing_radius_active")) {
			
			$sql = "alter table customers add column geofencing_radius_active int default 0";
			simple_query($sql);
			
			$sql = "alter table customers add index geofencing_radius_active(geofencing_radius_active)";
			simple_query($sql);
		}
		
		if(!table_exists("payroll_employer_vendor")) {
			$sql = "
				create table payroll_employer_vendor (
					id int not null auto_increment, 
					linedate_added datetime,
					linedate_started datetime,
					linedate_ended datetime,
					employer_id int default 0,
					old_sicap_vendor_id int default 0,
					new_sicap_vendor_id int default 0,
					deleted int default 0,
					primary key(id))
			";
			simple_query($sql);
						
			$sql = "alter table payroll_employer_vendor add index linedate_added(linedate_added)";
			simple_query($sql);	
			$sql = "alter table payroll_employer_vendor add index linedate_started(linedate_started)";
			simple_query($sql);	
			$sql = "alter table payroll_employer_vendor add index linedate_ended(linedate_ended)";
			simple_query($sql);	
			$sql = "alter table payroll_employer_vendor add index deleted(deleted)";
			simple_query($sql);	
			$sql = "alter table payroll_employer_vendor add index employer_id(employer_id)";
			simple_query($sql);	
			$sql = "alter table payroll_employer_vendor add index old_sicap_vendor_id(old_sicap_vendor_id)";
			simple_query($sql);	
			$sql = "alter table payroll_employer_vendor add index new_sicap_vendor_id(new_sicap_vendor_id)";
			simple_query($sql);	
		}
		
		
		if(!default_exists('peoplenet_geofencing_mph')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_geofencing_mph',
					'0',
					'GeoFencing Approximate MPH',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
			
		if(!default_exists('peoplenet_grading_offset_hrs')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_grading_offset_hrs',
					'0',
					'Grading Offset (hours)',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		
		
		if(!default_exists('peoplenet_hot_msg_logo')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_hot_msg_logo',
					'0',
					'Company Logo for Messages',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_hot_msg_cc')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_hot_msg_cc',
					'0',
					'Company Email Monitor (CC:)',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_hot_msg_arriving_insert')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_hot_msg_arriving_insert',
					'0',
					'Default Arriving Message',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_hot_msg_arrived_insert')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_hot_msg_arrived_insert',
					'0',
					'Default Arrived Message',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_hot_msg_departed_insert')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_hot_msg_departed_insert',
					'0',
					'Default Departed Message',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		if(!default_exists('peoplenet_hot_msg_template_num')) {
			$sql = "
				insert into defaults
					(xname,
					xvalue_string,
					display_name,
					load_default,
					section)
					
				values ('peoplenet_hot_msg_template_num',
					'0',
					'Default Message Template Number',
					1,
					'Truck Tracking')
			";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "geofencing_hot_msg_all_loads")) {
			
			$sql = "alter table customers add column geofencing_hot_msg_all_loads int default 0";
			simple_query($sql);
			
			$sql = "alter table customers add index geofencing_hot_msg_all_loads(geofencing_hot_msg_all_loads)";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler_stops", "stop_grade_id")) {
			
			$sql = "alter table load_handler_stops add column stop_grade_id int default 0";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index stop_grade_id(stop_grade_id)";
			simple_query($sql);
		}
		if(!field_exists("load_handler_stops", "stop_grade_note")) {
			
			$sql = "alter table load_handler_stops add column stop_grade_note varchar(255) default ''";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index stop_grade_note(stop_grade_note)";
			simple_query($sql);
		}
			
		if(!field_exists("load_handler_stops", "timezone_offset")) {
			
			$sql = "alter table load_handler_stops add column timezone_offset int default 0";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index timezone_offset(timezone_offset)";
			simple_query($sql);
		}
		if(!field_exists("load_handler_stops", "timezone_offset_dst")) {
			
			$sql = "alter table load_handler_stops add column timezone_offset_dst int default 0";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index timezone_offset_dst(timezone_offset_dst)";
			simple_query($sql);
		}
		if(!field_exists("load_handler_stops", "linedate_last_timezone")) {
			
			$sql = "alter table load_handler_stops add column linedate_last_timezone datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index linedate_last_timezone(linedate_last_timezone)";
			simple_query($sql);
		}
		
				
		if(!field_exists("customers", "flat_fuel_surchage_override")) {
			
			$sql = "alter table customers add column flat_fuel_surchage_override int default 0";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_mon decimal(10,3) default '0.000'";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_tue decimal(10,3) default '0.000'";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_wed decimal(10,3) default '0.000'";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_thu decimal(10,3) default '0.000'";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_fri decimal(10,3) default '0.000'";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_sat decimal(10,3) default '0.000'";
			simple_query($sql);
			$sql = "alter table customers add column flat_fuel_surchage_sun decimal(10,3) default '0.000'";
			simple_query($sql);
			
			$sql = "alter table customers add index flat_fuel_surchage_override(flat_fuel_surchage_override)";
			simple_query($sql);
			$sql = "alter table customers add index flat_fuel_surchage_mon(flat_fuel_surchage_mon)";
			simple_query($sql);
			$sql = "alter table customers add index flat_fuel_surchage_tue(flat_fuel_surchage_tue)";
			simple_query($sql);
			$sql = "alter table customers add index flat_fuel_surchage_wed(flat_fuel_surchage_wed)";
			simple_query($sql);
			$sql = "alter table customers add index flat_fuel_surchage_thu(flat_fuel_surchage_thu)";
			simple_query($sql);
			$sql = "alter table customers add index flat_fuel_surchage_fri(flat_fuel_surchage_fri)";
			simple_query($sql);
			$sql = "alter table customers add index flat_fuel_surchage_sat(flat_fuel_surchage_sat)";
			simple_query($sql);			
			$sql = "alter table customers add index flat_fuel_surchage_sun(flat_fuel_surchage_sun)";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler_stops", "pn_dispatch_id")) {
			$sql = "alter table load_handler_stops add column pn_dispatch_id varchar(25) default ''";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column pn_stop_id varchar(25) default ''";
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index pn_dispatch_id(pn_dispatch_id)";
			simple_query($sql);
			$sql = "alter table load_handler_stops add index pn_stop_id(pn_stop_id)";
			simple_query($sql);
		}
		
		if(!field_exists("trucks", "pn_odometer_offset")) {
			$sql = "alter table trucks add column pn_odometer_offset decimal(10,2) default '0.00'";
			simple_query($sql);
			
			$sql = "alter table trucks add index pn_odometer_offset(pn_odometer_offset)";
			simple_query($sql);
		}
		
		if(!field_exists("drivers", "linedate_terminated")) {
			$sql = "alter table drivers add column linedate_terminated datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table drivers add index linedate_terminated(linedate_terminated)";
			simple_query($sql);
		}
		
		
		if(!field_exists("load_handler", "flat_fuel_rate_amount")) {
			$sql = "alter table load_handler add column flat_fuel_rate_amount decimal(10,2) default '0.00'";
			simple_query($sql);
			
			$sql = "alter table load_handler add index flat_fuel_rate_amount(flat_fuel_rate_amount)";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "override_slow_pays")) {
			$sql = "
				alter table customers add column override_slow_pays int default 0
			";
			simple_query($sql);
			
			$sql = "alter table customers add index override_slow_pays(override_slow_pays)";
			simple_query($sql);	
		}
		if(!field_exists("drivers", "peoplenet_driver_id")) {
			$sql = "
				alter table drivers add column peoplenet_driver_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table drivers add index peoplenet_driver_id(peoplenet_driver_id)";
			simple_query($sql);	
		}
		
		if(!field_exists("drivers", "linedate_rehire")) {
			$sql = "alter table drivers add column linedate_rehire datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = "alter table drivers add column linedate_refire datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table drivers add index linedate_rehire(linedate_rehire)";
			simple_query($sql);	
			$sql = "alter table drivers add index linedate_refire(linedate_refire)";
			simple_query($sql);	
		}
		
		if(!default_exists('pn_dot_driver_min_movement')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_min_movement','0','DOT PN Min Movement (ft)',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_max_speed','0','DOT Max Speed',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_drive_rule','0','DOT Max Hours Driving per day',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_work_rule','0','DOT Max Hours Working per day',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_break_rule','0','DOT Required Break Hours (day reset)',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_week_days','0','DOT Days in Week',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_week_hours','0','DOT Max Work Hours per Week',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_driver_week_break','0','DOT Required Break Hours (week reset)',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
		}
		
		if(!default_exists('pn_dot_inspection_pre')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_inspection_pre','0','DOT PN Inspection Pre-trip (hrs)',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);
			
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_inspection_post','0','DOT PN Inspection Post-trip (hrs)',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);			
		}
		
		if(!table_exists("safety_report")) {
			$sql = "
				create table safety_report (
					id int not null auto_increment,
					linedate_added datetime,
					linedate_start datetime,
					linedate_end datetime,	
					driver_id int,
					truck_id int,
					distance_feet decimal(10,2) default '0.00',
					hours_driven decimal(10,2) default '0.00',
					hours_worked decimal(10,2) default '0.00',
					hours_rested decimal(10,2) default '0.00',
					wk_hours_driven decimal(10,2) default '0.00',
					wk_hours_worked decimal(10,2) default '0.00',
					wk_hours_rested decimal(10,2) default '0.00',
					speeding_violations int,
					dot_violations int,
					violation_notes text,
					excuse_flag int default 0,
					excused_by_id int default 0,
					excuse_notes text,
					deleted int default 0,
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table safety_report add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table safety_report add index linedate_start(linedate_start)";
			simple_query($sql);
			$sql = " alter table safety_report add index linedate_end(linedate_end)";
			simple_query($sql);
			$sql = " alter table safety_report add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table safety_report add index driver_id(driver_id)";
			simple_query($sql);	
			
			$sql = " alter table safety_report add index dot_violations(dot_violations)";
			simple_query($sql);	
			$sql = " alter table safety_report add index speeding_violations(speeding_violations)";
			simple_query($sql);	
			$sql = " alter table safety_report add index distance_feet(distance_feet)";
			simple_query($sql);	
			$sql = " alter table safety_report add index excuse_flag(excuse_flag)";
			simple_query($sql);	
			$sql = " alter table safety_report add index excused_by_id(excused_by_id)";
			simple_query($sql);		
			$sql = " alter table safety_report add index deleted(deleted)";
			simple_query($sql);					
		}
		if(!field_exists("safety_report", "hour_11_violation")) {
			$sql = "alter table safety_report add column hour_11_violation int default 0";
			simple_query($sql);
			$sql = "alter table safety_report add column hour_14_violation int default 0";
			simple_query($sql);
			$sql = "alter table safety_report add column break_10_violation int default 0";
			simple_query($sql);
			$sql = "alter table safety_report add column hour_70_violation int default 0";
			simple_query($sql);
			$sql = "alter table safety_report add column break_34_violation int default 0";
			simple_query($sql);
			
			$sql = "alter table safety_report add index hour_11_violation(hour_11_violation)";
			simple_query($sql);	
			$sql = "alter table safety_report add index hour_14_violation(hour_14_violation)";
			simple_query($sql);	
			$sql = "alter table safety_report add index break_10_violation(break_10_violation)";
			simple_query($sql);	
			$sql = "alter table safety_report add index hour_70_violation(hour_70_violation)";
			simple_query($sql);	
			$sql = "alter table safety_report add index break_34_violation(break_34_violation)";
			simple_query($sql);	
		}
		if(!field_exists("safety_report", "abrupt_shutdown_flag")) {
			$sql = "alter table safety_report add column abrupt_shutdown_flag int default 0";
			simple_query($sql);
			
			$sql = "alter table safety_report add index abrupt_shutdown_flag(abrupt_shutdown_flag)";
			simple_query($sql);	
		}
				
		if(!default_exists('pn_dot_gap_detection')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('pn_dot_gap_detection','0','DOT PN GPS Abrupt Shutdown (hrs)',1,'Truck Tracking Driver Rules')
			";
			simple_query($sql);		
		}
		
		if(!table_exists("driver_elog_events")) {
			$sql = "
				create table driver_elog_events (
					id int not null auto_increment,
                         elog_event varchar(255),
                         active int(11),
                         deleted int(11),
                         linedate_added datetime,
                         event_data1 varchar(100),
                         event_data2 varchar(100),
                         event_data3 varchar(100),
                         event_data4 varchar(100),
                         setting1 varchar(100),
                         setting2 varchar(100),
                         setting3 varchar(100),
                         setting4 varchar(100),
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table driver_elog_events add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table driver_elog_events add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table driver_elog_events add index active(active)";
			simple_query($sql);			
		}
		
		if(!table_exists("safety_driver_points")) {
			$sql = "
				create table safety_driver_points (
					id int not null auto_increment,
                      	driver_id int(11),
                      	points int(11),
                      	linedate_added datetime,
                      	deleted int(11),
                      	safety_code int(11),
                      	active int(11),                         
					primary key(id))
			";
			simple_query($sql);
						
			
			$sql = " alter table safety_driver_points add index driver_id(driver_id)";
			simple_query($sql);
			$sql = " alter table safety_driver_points add index safety_code(safety_code)";
			simple_query($sql);
			
			$sql = " alter table safety_driver_points add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table safety_driver_points add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table safety_driver_points add index active(active)";
			simple_query($sql);			
		}
		
		if(!table_exists("safety_driver_codes")) {
			$sql = "
				create table safety_driver_codes (
					id int not null auto_increment,
                      	safety_code varchar(10),
                      	safety_description varchar(255),
                      	deleted int(11),
                      	active int(11),
                      	points int(11),
                      	linedate_added datetime,                      	                    
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table safety_driver_codes add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table safety_driver_codes add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table safety_driver_codes add index active(active)";
			simple_query($sql);			
		}
				
		if(!default_exists('insurance_report_email_trucks')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('insurance_report_email_trucks','0','Insurance Report Truck Email On',1,'Insurance Report')
			";
			simple_query($sql);		
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('insurance_report_email_trailers','0','Insurance Report Trailer Email On',1,'Insurance Report')
			";
			simple_query($sql);	
		}
		if(!default_exists('insurance_report_email_address1')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('insurance_report_email_address1','','Insurance Co Email Address',1,'Insurance Report')
			";
			simple_query($sql);		
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('insurance_report_email_address2','','Account Copy Email Address',1,'Insurance Report')
			";
			simple_query($sql);	
		}
				
		//...added July 2013
		if(!table_exists("dispatcher_tasks")) {
			$sql = "
				create table dispatcher_tasks (
					id int not null auto_increment,
					linedate_added datetime, 
					linedate_updated datetime, 
					deleted int(11),
                      	active int(11),
                      	created_by_id int(11),
					assigned_to_id int(11),
					task varchar(255),
					freq_days int(11),
					linedate_start datetime, 
					linedate_complete datetime, 
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table dispatcher_tasks add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table dispatcher_tasks add index linedate_updated(linedate_updated)";
			simple_query($sql);
			$sql = " alter table dispatcher_tasks add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index active(active)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index linedate_start(linedate_start)";
			simple_query($sql);
			$sql = " alter table dispatcher_tasks add index linedate_complete(linedate_complete)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index created_by_id(created_by_id)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index assigned_to_id(assigned_to_id)";
			simple_query($sql);		
		}
		if(!table_exists("dispatcher_tasks_work")) {
			$sql = "
				create table dispatcher_tasks_work (
					id int not null auto_increment,
					linedate_added datetime, 
					linedate datetime, 
					deleted int(11),
                      	active int(11),
                      	task_id int(11),
                      	user_id int(11),
                      	work_code int(11),
					work varchar(255),
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table dispatcher_tasks_work add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table dispatcher_tasks_work add index linedate(linedate)";
			simple_query($sql);
			$sql = " alter table dispatcher_tasks_work add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks_work add index active(active)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index task_id(task_id)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index user_id(user_id)";
			simple_query($sql);	
			$sql = " alter table dispatcher_tasks add index work_code(work_code)";
			simple_query($sql);		
		}
		
		if(!field_exists("load_handler_stops", "appointment_window")) {
			$sql = "alter table load_handler_stops add column appointment_window int default 0";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column linedate_appt_window_start datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column linedate_appt_window_end datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index appointment_window(appointment_window)";
			simple_query($sql);	
			$sql = "alter table load_handler_stops add index linedate_appt_window_start(linedate_appt_window_start)";
			simple_query($sql);	
			$sql = "alter table load_handler_stops add index linedate_appt_window_end(linedate_appt_window_end)";
			simple_query($sql);	
		}
		
		if(!field_exists("load_handler", "billing_notes")) {
			$sql = "alter table load_handler add column billing_notes text";
			simple_query($sql);
			$sql = "alter table load_handler add column driver_notes text";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler_stops", "geofencing_arriving_sent")) {
			$sql = "alter table load_handler_stops add column geofencing_arriving_sent int default 0";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column geofencing_arrived_sent int default 0";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column geofencing_departed_sent int default 0"; 
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add column linedate_geofencing_arriving datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column linedate_geofencing_arrived datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = "alter table load_handler_stops add column linedate_geofencing_departed datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index geofencing_arriving_sent(geofencing_arriving_sent)";
			simple_query($sql);	
			$sql = "alter table load_handler_stops add index geofencing_arrived_sent(geofencing_arrived_sent)";
			simple_query($sql);	
			$sql = "alter table load_handler_stops add index geofencing_departed_sent(geofencing_departed_sent)";
			simple_query($sql);	
			
			$sql = "alter table load_handler_stops add index linedate_geofencing_arriving(linedate_geofencing_arriving)";
			simple_query($sql);	
			$sql = "alter table load_handler_stops add index linedate_geofencing_arrived(linedate_geofencing_arrived)";
			simple_query($sql);	
			$sql = "alter table load_handler_stops add index linedate_geofencing_departed(linedate_geofencing_departed)";
			simple_query($sql);	
		}
		
		//new field for comparison report...added Aug 2013.
		//trailer_exp_per_mile
		if(!field_exists("trucks_log", "trailer_exp_per_mile")) {
			$sql = "alter table trucks_log add column trailer_exp_per_mile decimal(10,4) default '0.0000'"; 
			simple_query($sql);
			
			$sql = "alter table trucks_log add index trailer_exp_per_mile(trailer_exp_per_mile)";
			simple_query($sql);
		}
		if(!default_exists('trailer_mile_exp_per_mile')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('trailer_mile_exp_per_mile','0.0100','Trailer Mileage Expense Per Mile',1,'Financial')
			";
			simple_query($sql);	
		}
		
		//new table for zip code and GPS locations... to use with PC Miler when needed...Added Sep. 2013
		if(!table_exists("gps_to_zip_code")) {
			$sql = "
				create table gps_to_zip_code (
					id int not null auto_increment,
					linedate_added datetime, 
					deleted int(11),
                      	zip_code int(11) default 0,
                      	city varchar(100) default '',
					state varchar(10) default '',
                      	latitude float(10,6) default '0.000000',
					longitude float(10,6) default '0.000000',                     	
                      	population int(11) default 0,
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table gps_to_zip_code add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table gps_to_zip_code add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table gps_to_zip_code add index zip_code(zip_code)";
			simple_query($sql);	
			$sql = " alter table gps_to_zip_code add index latitude(latitude)";
			simple_query($sql);	
			$sql = " alter table gps_to_zip_code add index longitude(longitude)";
			simple_query($sql);		
		}
		
		if(!field_exists("users", "alert_call_priority")) {
			$sql = "alter table users add column alert_call_priority int default 0"; 
			simple_query($sql);
			
			$sql = "alter table users add index alert_call_priority(alert_call_priority)";
			simple_query($sql);
		}
		if(!field_exists("users", "alert_call_phone")) {
			$sql = "alter table users add column alert_call_phone varchar(20) default ''"; 
			simple_query($sql);
		}
		if(!field_exists("users", "alert_call_email")) {
			$sql = "alter table users add column alert_call_email varchar(255) default ''"; 
			simple_query($sql);
		}
		if(!default_exists('alert_call_priority_time_from')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('alert_call_priority_time_from','00:00:00','Alert Call Time From',1,'Alert Call')
			";
			simple_query($sql);	
		}
		if(!default_exists('alert_call_priority_time_to')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('alert_call_priority_time_to','06:00:00','Alert Call Time To',1,'Alert Call')
			";
			simple_query($sql);	
		}
		
		if(!default_exists('visual_load_plus')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('visual_load_plus','0','Import Loads (Visual Load Plus)',1,'Import Utilities')
			";
			simple_query($sql);	
		}
		
		if(!field_exists("customers", "document_75k_received")) {
			$sql = "alter table customers add column document_75k_received int default 0"; 
			simple_query($sql);
			$sql = "alter table customers add column document_75k_exempt int default 0"; 
			simple_query($sql);
			$sql = "alter table customers add column linedate_document_75k datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			
			$sql = "alter table customers add index document_75k_received(document_75k_received)";
			simple_query($sql);
			$sql = "alter table customers add index document_75k_exempt(document_75k_exempt)";
			simple_query($sql);
			$sql = "alter table customers add index linedate_document_75k(linedate_document_75k)";
			simple_query($sql);
		}
		if(!field_exists("load_handler", "vpl_imported")) {
			$sql = "alter table load_handler add column vpl_imported int default 0"; 
			simple_query($sql);
			
			$sql = "alter table load_handler add index vpl_imported(vpl_imported)";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler", "vpl_import_processed")) {
			$sql = "alter table load_handler add column vpl_import_processed int default 0"; 
			simple_query($sql);
			
			$sql = "alter table load_handler add index vpl_import_processed(vpl_import_processed)";
			simple_query($sql);
		}
		
		if(!field_exists("drivers", "head_shot_photo")) {
			$sql = "alter table drivers add column head_shot_photo varchar(255) default ''"; 
			simple_query($sql);
		}
				
		if(!field_exists("customers", "invoice_discount_percent")) {
			$sql = "alter table customers add column invoice_discount_percent decimal(10,4) default '0.0000'"; 
			simple_query($sql);
		}
		
		if(!field_exists("load_handler", "master_load_label")) {
			$sql = "alter table load_handler add column master_load_label varchar(255) default ''"; 
			simple_query($sql);
		}
		
		if(!default_exists('terminal_hub_address')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('terminal_hub_address','','Terminal Hub Address',1,'Company Information')
			";
			simple_query($sql);	
		}
		if(!default_exists('terminal_hub_name')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('terminal_hub_name','','Terminal Hub Name',1,'Company Information')
			";
			simple_query($sql);	
		}
		if(!default_exists('terminal_hub_phone')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('terminal_hub_phone','','Terminal Hub Phone',1,'Company Information')
			";
			simple_query($sql);	
		}
		
		if(!default_exists('visual_load_plus_email')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('visual_load_plus_email','','Import Loads Email Notice',1,'Import Utilities')
			";
			simple_query($sql);	
		}
		
		if(!default_exists('visual_load_plus_email2')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('visual_load_plus_email2','','Import Loads Email Notice Alt',1,'Import Utilities')
			";
			simple_query($sql);	
		}
				
		if(!field_exists("trailers_dropped", "mrr_drop_mode")) {
			$sql = "alter table trailers_dropped add column mrr_drop_mode int default 0"; 
			simple_query($sql);
			
			$sql = "alter table trailers_dropped add index mrr_drop_mode(mrr_drop_mode)";
			simple_query($sql);
		}
								
		if(!field_exists("notes_main", "access_level")) {
			$sql = "alter table notes_main add column access_level int default 0"; 
			simple_query($sql);
			
			$sql = "alter table notes_main add index access_level(access_level)";
			simple_query($sql);
		}
				
		if(!table_exists("user_menu_access")) {
			$sql = "
				create table user_menu_access (
					id int not null auto_increment,
					linedate_added datetime, 
					access_level int(11) default 0,                   	
                      	admin_url varchar(255),
                      	deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
		}
		
		if(!table_exists("user_menu_group")) {
			$sql = "
				create table user_menu_group (
					id int not null auto_increment,
					linedate_added datetime, 
					access_level int(11) default 0,                   	
                      	menu_name varchar(255),
                      	deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
		}
		
		if(!field_exists("trucks_log", "driver_2_labor_per_mile")) {
			$sql = "alter table trucks_log add column driver_2_labor_per_mile decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver_2_labor_per_hour decimal(10,3) default '0.000'"; 
			simple_query($sql);
			
			$sql = "alter table trucks_log add index driver_2_labor_per_mile(driver_2_labor_per_mile)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver_2_labor_per_hour(driver_2_labor_per_hour)";
			simple_query($sql);
		}
		
		if(!table_exists("last_payroll_report")) {
			$sql = "
				create table last_payroll_report (
					id int not null auto_increment,
					linedate_added datetime, 
					linedate datetime, 
					employer_id int(11) default 0,                   	
                      	user_id int(11) default 0,   
                      	deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table last_payroll_report add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table last_payroll_report add index linedate(linedate)";
			simple_query($sql);
			$sql = "alter table last_payroll_report add index employer_id(employer_id)";
			simple_query($sql);
			$sql = "alter table last_payroll_report add index user_id(user_id)";
			simple_query($sql);
			$sql = "alter table last_payroll_report add index deleted(deleted)";
			simple_query($sql);
		}
		
		if(!field_exists("drivers", "tanker_endorsement")) {
			$sql = "alter table drivers add column tanker_endorsement int default 0"; 
			simple_query($sql);
			
			$sql = "alter table drivers add index tanker_endorsement(tanker_endorsement)";
			simple_query($sql);
		}
		
		if(!field_exists("drivers", "jit_driver_flag")) {
			$sql = "alter table drivers add column jit_driver_flag int default 0"; 
			simple_query($sql);
			
			$sql = "alter table drivers add index jit_driver_flag(jit_driver_flag)";
			simple_query($sql);
		}
				
		if(!field_exists("load_handler", "preplan_driver2_id")) {
			$sql = "alter table load_handler add column preplan_driver2_id int default 0"; 
			simple_query($sql);
			$sql = "alter table load_handler add column preplan_leg2_driver_id int default 0"; 
			simple_query($sql);
			$sql = "alter table load_handler add column preplan_leg2_driver2_id int default 0"; 
			simple_query($sql);
			$sql = "alter table load_handler add column preplan_leg2_stop_id int default 0"; 
			simple_query($sql);
			
			$sql = "alter table load_handler add index preplan_driver2_id(preplan_driver2_id)";
			simple_query($sql);
			$sql = "alter table load_handler add index preplan_leg2_driver_id(preplan_leg2_driver_id)";
			simple_query($sql);
			$sql = "alter table load_handler add index preplan_leg2_driver2_id(preplan_leg2_driver2_id)";
			simple_query($sql);
			$sql = "alter table load_handler add index preplan_leg2_stop_id(preplan_leg2_stop_id)";
			simple_query($sql);
		}
		
		if(!table_exists("driver_vacation_advances")) {
			$sql = "
				create table driver_vacation_advances (
					id int not null auto_increment,
					linedate_added datetime, 
					linedate_start datetime,
					linedate_end datetime,
					linedate_approve datetime,
					linedate_cancel datetime,
					driver_id int(11) default 0,
					user_id int(11) default 0,
					approved_by_id int(11) default 0,
					cancelled_by_id int(11) default 0,
					deleted int(11) default 0,
					miles_per_day decimal(10,2) default '0.00',
					daily_pay_rate decimal(10,2) default '0.00',
					hours_per_day decimal(10,2) default '0.00',
					hourly_pay_rate decimal(10,2) default '0.00',
					cash_advance decimal(10,2) default '0.00',
                      	comments text,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table driver_vacation_advances add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index linedate_start(linedate_added)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index linedate_end(linedate_added)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index linedate_approve(linedate_approve)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index linedate_cancel(linedate_cancel)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index user_id(user_id)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index cancelled_by_id(cancelled_by_id)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index approved_by_id(approved_by_id)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index miles_per_day(miles_per_day)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index hours_per_day(hours_per_day)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index daily_pay_rate(daily_pay_rate)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index hourly_pay_rate(hourly_pay_rate)";
			simple_query($sql);	
			$sql = "alter table driver_vacation_advances add index cash_advance(cash_advance)";
			simple_query($sql);		
		}
		
		if(!table_exists("driver_vacation_advances_dates")) {
			$sql = "
				create table driver_vacation_advances_dates (
					id int not null auto_increment,
					dva_id int(11) default 0,
					linedate_added datetime, 
					linedate datetime,
					deleted int(11) default 0,
					driver_id int(11) default 0,
					miles decimal(10,2) default '0.00',
					hours decimal(10,2) default '0.00',
					miles_pay_rate decimal(10,2) default '0.00',					
					hours_pay_rate decimal(10,2) default '0.00',
					cash_advance decimal(10,2) default '0.00',
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table driver_vacation_advances_dates add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index linedate(linedate)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index dva_id(dva_id)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index miles(miles)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index hours(hours)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index miles_pay_rate(miles_pay_rate)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index hours_pay_rate(hours_pay_rate)";
			simple_query($sql);		
			$sql = "alter table driver_vacation_advances_dates add index cash_advance(cash_advance)";
			simple_query($sql);		
		}
		
		if(!field_exists("load_handler_stops", "master_load_include")) {
			$sql = "alter table load_handler_stops add column master_load_include int default 0"; 
			simple_query($sql);
			$sql = "alter table load_handler_stops add column master_load_pickup_eta datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			
			$sql = "alter table load_handler_stops add index master_load_include(master_load_include)";
			simple_query($sql);
			$sql = "alter table load_handler_stops add index master_load_pickup_eta(master_load_pickup_eta)";
			simple_query($sql);
		}
		
		
		if(!table_exists("customer_accounting_aging")) {
			$sql = "
				create table customer_accounting_aging (
					id int not null auto_increment,
					customer_id int(11) default 0,
					linedate_added datetime,
					deleted int(11) default 0,
					due_15 decimal(10,2) default '0.00',
					due_30 decimal(10,2) default '0.00',
					due_45 decimal(10,2) default '0.00',
					due_46 decimal(10,2) default '0.00',
					due_15_text text,
					due_30_text text,
					due_45_text text,
					due_46_text text,
					cust_name varchar(255) default '',
					tot_days decimal(10,2) default '0.00',
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table customer_accounting_aging add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table customer_accounting_aging add index linedate(linedate)";
			simple_query($sql);
			$sql = "alter table customer_accounting_aging add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table customer_accounting_aging add index customer_id(customer_id)";
			simple_query($sql);
		}
				
		if(!field_exists("trailers", "interchange_flag")) {
			$sql = "alter table trailers add column interchange_flag int default 0"; 
			simple_query($sql);
			
			$sql = "alter table trailers add index interchange_flag(interchange_flag)";
			simple_query($sql);
		}
		if(!field_exists("trailers", "nick_name")) {
			$sql = "alter table trailers add column nick_name varchar(255) default ''"; 
			simple_query($sql);
		}
		
		
		if(!default_exists('log_storage_database_name')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('log_storage_database_name','','Log Storage Database Name',1,'General Settings')
			";
			simple_query($sql);	
		}
		
		if(!field_exists("trucks", "apu_serial")) {
			$sql = "alter table trucks add column apu_serial varchar(255) default ''"; 
			simple_query($sql);
			
			$sql = "alter table trucks add column apu_value decimal(10,2) default '0.00'"; 
			simple_query($sql);
			$sql = "alter table trucks add index apu_value(apu_value)";
			simple_query($sql);
		}
		
		if(!field_exists("customers", "customer_login_name")) {
			$sql = "alter table customers add column customer_login_name varchar(255) default ''"; 
			simple_query($sql);
			$sql = "alter table customers add index customer_login_name(customer_login_name)";
			simple_query($sql);
			
			$sql = "alter table customers add column customer_login_pass varchar(255) default ''"; 
			simple_query($sql);
			$sql = "alter table customers add index customer_login_pass(customer_login_pass)";
			simple_query($sql);
		}
		
		if(!field_exists("trailers_dropped", "linedate_completed")) {
			$sql = "alter table trailers_dropped add column linedate_completed datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			$sql = "alter table trailers_dropped add index linedate_completed(linedate_completed)";
			simple_query($sql);
		}
		
		if(!field_exists("load_handler_stops", "stop_spec_notes")) {
			$sql = "alter table load_handler_stops add column stop_spec_notes text default ''"; 
			simple_query($sql);
		}
		
		if(!field_exists("trucks", "in_the_shop")) {
			$sql = "alter table trucks add column in_the_shop int default 0"; 
			simple_query($sql);
			$sql = "alter table trucks add index in_the_shop(in_the_shop)";
			simple_query($sql);
		}
		if(!field_exists("trailers", "in_the_shop")) {
			$sql = "alter table trailers add column in_the_shop int default 0"; 
			simple_query($sql);
			$sql = "alter table trailers add index in_the_shop(in_the_shop)";
			simple_query($sql);
		}
		
		if(!table_exists("cache_holder")) {
			$sql = "
				create table cache_holder (
					id int not null auto_increment,
					linedate_added datetime,
					page_html text,
					cache_name varchar(200) default '',
					primary key(id))
			";
			simple_query($sql);
		}
		
		if(!default_exists('overtime_hours_min')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('overtime_hours_min','40','Overtime Hours (Use 40 if >40 hours = overtime)',1,'Payroll')
			";
			simple_query($sql);	
		}
		
		if(!default_exists('overtime_def_rate')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('overtime_def_rate','1.50','Overtime Default Rate (1.50 = Time and a Half)',1,'Payroll')
			";
			simple_query($sql);	
		}
		
		if(!field_exists("drivers", "overtime_hourly_charged")) {
			$sql = "alter table drivers add column overtime_hourly_charged decimal(10,3) default '0.00'"; 
			simple_query($sql);
			$sql = "alter table drivers add column overtime_hourly_paid decimal(10,3) default '0.00'"; 
			simple_query($sql);
			
			$sql = "alter table drivers add index overtime_hourly_charged(overtime_hourly_charged)";
			simple_query($sql);
			$sql = "alter table drivers add index overtime_hourly_paid(overtime_hourly_paid)";
			simple_query($sql);
		}
		
		if(!field_exists("trucks_log", "driver1_overtime_hourly_charged")) {
			$sql = "alter table trucks_log add column driver1_overtime_hourly_charged decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver1_overtime_hourly_paid decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver2_overtime_hourly_charged decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver2_overtime_hourly_paid decimal(10,3) default '0.000'"; 
			simple_query($sql);
			
			$sql = "alter table trucks_log add index driver1_overtime_hourly_charged(driver1_overtime_hourly_charged)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver1_overtime_hourly_paid(driver1_overtime_hourly_paid)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver2_overtime_hourly_charged(driver2_overtime_hourly_charged)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver2_overtime_hourly_paid(driver2_overtime_hourly_paid)";
			simple_query($sql);
		}
		
		if(!field_exists("trucks_log", "driver1_pay_per_mile")) {
			$sql = "alter table trucks_log add column driver1_pay_per_mile decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver1_pay_per_hour decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver2_pay_per_mile decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table trucks_log add column driver2_pay_per_hour decimal(10,3) default '0.000'"; 
			simple_query($sql);
			
			$sql = "alter table trucks_log add index driver1_pay_per_mile(driver1_pay_per_mile)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver1_pay_per_hour(driver1_pay_per_hour)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver2_pay_per_mile(driver2_pay_per_mile)";
			simple_query($sql);
			$sql = "alter table trucks_log add index driver2_pay_per_hour(driver2_pay_per_hour)";
			simple_query($sql);
		}
		
		if(!default_exists('payroll_lockdown_access_level')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('payroll_lockdown_access_level','99','Access Level after Payroll',1,'Payroll')
			";
			simple_query($sql);	
		}
		
		if(!field_exists("driver_vacation_advances", "driver_charged_per_mile")) {
			$sql = "alter table driver_vacation_advances add column driver_charged_per_mile decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add column driver_paid_per_mile decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add column driver_holiday_pay decimal(10,2) default '0.00'"; 
			simple_query($sql);
			
			$sql = "alter table driver_vacation_advances add index driver_charged_per_mile(driver_charged_per_mile)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index driver_paid_per_mile(driver_paid_per_mile)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances add index driver_holiday_pay(driver_holiday_pay)";
			simple_query($sql);
		}
		if(!field_exists("driver_vacation_advances_dates", "driver_charged_per_mile_rate")) {
			$sql = "alter table driver_vacation_advances_dates add column driver_charged_per_mile_rate decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add column driver_paid_per_mile_rate decimal(10,3) default '0.000'"; 
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add column driver_holiday_pay_rate decimal(10,2) default '0.00'"; 
			simple_query($sql);
			
			$sql = "alter table driver_vacation_advances_dates add index driver_charged_per_mile_rate(driver_charged_per_mile_rate)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index driver_paid_per_mile_rate(driver_paid_per_mile_rate)";
			simple_query($sql);
			$sql = "alter table driver_vacation_advances_dates add index driver_holiday_pay_rate(driver_holiday_pay_rate)";
			simple_query($sql);
		}
		
		if(!field_exists("driver_vacation_advances", "driver_insurance_amnt")) {
			$sql = "alter table driver_vacation_advances add column driver_insurance_amnt decimal(10,2) default '0.00'"; 
			simple_query($sql);
			
			$sql = "alter table driver_vacation_advances add index driver_insurance_amnt(driver_insurance_amnt)";
			simple_query($sql);
		}
		if(!field_exists("driver_vacation_advances_dates", "driver_insurance_amnt_rate")) {
			$sql = "alter table driver_vacation_advances_dates add column driver_insurance_amnt_rate decimal(10,2) default '0.00'"; 
			simple_query($sql);
			
			$sql = "alter table driver_vacation_advances_dates add index driver_insurance_amnt_rate(driver_insurance_amnt_rate)";
			simple_query($sql);
		}
		
		if(!field_exists("drivers", "driver_address_1")) {
			$sql = "alter table drivers add column driver_address_1 varchar(100) default ''"; 
			simple_query($sql);
			$sql = "alter table drivers add column driver_address_2 varchar(100) default ''"; 
			simple_query($sql);
			$sql = "alter table drivers add column driver_city varchar(100) default ''"; 
			simple_query($sql);
			$sql = "alter table drivers add column driver_state varchar(20) default ''"; 
			simple_query($sql);
			$sql = "alter table drivers add column driver_zip varchar(20) default ''"; 
			simple_query($sql);
			$sql = "alter table drivers add column driver_email varchar(255) default ''"; 
			simple_query($sql);
		}
		
		if(!field_exists("trailers", "linedate_last_pmi")) {
			$sql = "alter table trailers add column linedate_last_pmi datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			$sql = "alter table trailers add column pmi_test_ignore int default 0"; 
			simple_query($sql);
			
			$sql = "alter table trailers add index linedate_last_pmi(linedate_last_pmi)";
			simple_query($sql);
			$sql = "alter table trailers add index pmi_test_ignore(pmi_test_ignore)";
			simple_query($sql);
		}
		
		if(!default_exists('trailer_pmi_date_days')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('trailer_pmi_date_days','90','Days between PMI',1,'Trailer Tracking')
			";
			simple_query($sql);	
		}
		if(!default_exists('trailer_fed_date_days')) {
			$sql = "
				insert into defaults (xname,xvalue_string,display_name,load_default,section)
					
				values ('trailer_fed_date_days','365','Days between Federal Inspection',1,'Trailer Tracking')
			";
			simple_query($sql);	
		}
		if(!field_exists("trailers", "linedate_last_fed")) {
			$sql = "alter table trailers add column linedate_last_fed datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			$sql = "alter table trailers add column fed_test_ignore int default 0"; 
			simple_query($sql);
			
			$sql = "alter table trailers add index linedate_last_fed(linedate_last__fed)";
			simple_query($sql);
			$sql = "alter table trailers add index fed_test_ignore(fed_test_ignore)";
			simple_query($sql);
		}
		
		
		if(!table_exists("driver_absenses")) {
			$sql = "
				create table driver_absenses (
					id int not null auto_increment,
					driver_id int(11) default 0,
					linedate_added datetime,
					linedate datetime,
					deleted int(11) default 0,
					driver_code int(11) default 0,
					driver_reason varchar(255) default '',
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table driver_absenses add index linedate_added(linedate_added)";
			simple_query($sql);		
			$sql = "alter table driver_absenses add index linedate(linedate)";
			simple_query($sql);	
			$sql = "alter table driver_absenses add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table driver_absenses add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table driver_absenses add index driver_code(driver_code)";
			simple_query($sql);
		}
		
		if(!table_exists("driver_payroll_changes")) {
			$sql = "
				create table driver_payroll_changes (
					id int not null auto_increment,
					user_id int(11) default 0,
					linedate_added datetime,
					driver_id int(11) default 0,					
					linedate datetime,
					
					single_hour_pay decimal(10,3) default '0.000',
					single_mile_pay decimal(10,3) default '0.000',
					single_hour_pay_charged decimal(10,3) default '0.000',
					single_mile_pay_charged decimal(10,3) default '0.000',
					team_hour_pay decimal(10,3) default '0.000',
					team_mile_pay decimal(10,3) default '0.000',
					team_hour_pay_charged decimal(10,3) default '0.000',
					team_mile_pay_charged decimal(10,3) default '0.000',					
					
					auto_schedule int(11) default 0,
					
					deleted int(11) default 0,
					raise_notes varchar(255) default '',
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table driver_payroll_changes add index linedate_added(linedate_added)";
			simple_query($sql);		
			$sql = "alter table driver_payroll_changes add index linedate(linedate)";
			simple_query($sql);	
			$sql = "alter table driver_payroll_changes add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table driver_payroll_changes add index user_id(user_id)";
			simple_query($sql);
			$sql = "alter table driver_payroll_changes add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table driver_payroll_changes add index driver_code(driver_code)";
			simple_query($sql);
			$sql = "alter table driver_payroll_changes add index auto_schedule(auto_schedule)";
			simple_query($sql);
		}
		
		
		if(!field_exists("drivers", "linedate_review_due")) {
			$sql = "alter table drivers add column linedate_review_due datetime default '0000-00-00 00:00:00'"; 
			simple_query($sql);
			
			$sql = "alter table drivers add index linedate_review_due(linedate_review_due)";
			simple_query($sql);
		}
		
		
		if(!table_exists("equipment_value_tracking")) {
			$sql = "
				create table equipment_value_tracking (
					id int not null auto_increment,
					user_id int(11) default 0,
					linedate_added datetime,
					linedate datetime,
					linedate_end datetime,
					truck_id int(11) default 0,
					trailer_id int(11) default 0,							
					equip_value decimal(10,2) default '0.00',
					equip_value_end decimal(10,2) default '0.00',
					unit_value decimal(10,2) default '0.00',
					unit_value_end decimal(10,2) default '0.00',
					deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table equipment_value_tracking add index linedate_added(linedate_added)";
			simple_query($sql);		
			$sql = "alter table equipment_value_tracking add index linedate(linedate)";
			simple_query($sql);	
			$sql = "alter table equipment_value_tracking add index linedate_end(linedate_end)";
			simple_query($sql);	
			$sql = "alter table equipment_value_tracking add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table equipment_value_tracking add index user_id(user_id)";
			simple_query($sql);
			$sql = "alter table equipment_value_tracking add index truck_id(truck_id)";
			simple_query($sql);
			$sql = "alter table equipment_value_tracking add index trailer_id(trailer_id)";
			simple_query($sql);
		}
		
		if(!table_exists("dispatch_im")) {
			$sql = "
				create table dispatch_im (
					id int not null auto_increment,
					user_id int(11) default 0,
					to_user_id int(11) default 0,						
					im_msg varchar(255) default '',
					linedate_added datetime,
					im_read int(11) default 0,
					deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table dispatch_im add index linedate_added(linedate_added)";
			simple_query($sql);		
			$sql = "alter table dispatch_im add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table dispatch_im add index user_id(user_id)";
			simple_query($sql);
			$sql = "alter table dispatch_im add index to_user_id(to_user_id)";
			simple_query($sql);
			$sql = "alter table dispatch_im add index im_read(im_read)";
			simple_query($sql);
		}
		
		if(!table_exists("user_menu_pages")) {
			$sql = "
				create table user_menu_pages (
					id int not null auto_increment,
					access_level int(11) default 0,				
					page_name varchar(255) default '',
					linedate_added datetime,
					deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table user_menu_pages add index linedate_added(linedate_added)";
			simple_query($sql);		
			$sql = "alter table user_menu_pages add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table user_menu_pages add index access_level(access_level)";
			simple_query($sql);
		}
		if(!table_exists("user_menu_custom")) {
			$sql = "
				create table user_menu_custom (
					id int not null auto_increment,
					user_id int(11) default 0,
					page_id int(11) default 0,						
					label varchar(255) default '',
					tool_tip varchar(255) default '',
					linedate_added datetime,
					deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table user_menu_custom add index linedate_added(linedate_added)";
			simple_query($sql);		
			$sql = "alter table user_menu_custom add index deleted(deleted)";
			simple_query($sql);
			$sql = "alter table user_menu_custom add index user_id(user_id)";
			simple_query($sql);
			$sql = "alter table user_menu_custom add index page_id(page_id)";
			simple_query($sql);
		}
		
		
		$current_version = 1.19;
		update_version($current_version);
	
	}	
	
	echo "<p>Update completed to version $current_version...";
	
	include("update_logs.php");
?>
	<br><br>
	<a href="index.php">Click here</a> to return to the home page
	</td>
</tr>
</table>
</font>
<? if(isset($_GET['auto_update']) && !isset($_GET['force_update'])) {
	javascript_redirect('index.php');
} else {
	include("footer.php");
} ?>