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
		die("Unable to determine the current Log version, please check manually");
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
<? if(isset($_GET['force_update'])) echo "<br><font color='red'>Force Update - From Log version $_GET[force_update]</font>"; ?></b><p>
<?
	
	$mrr_log_db="".mrr_find_log_database_name()."";
	
	echo "<br> V 1.00";
	if($current_version == 1.0) {
				
		$current_version = 1.01;
		update_version($current_version);
	}
	echo "<br> V 1.01";
	if($current_version == 1.01) {
				
		$current_version = 1.02;
		update_version($current_version);
	}
	echo "<br> V 1.02";
	if($current_version == 1.02) {
							
		$current_version = 1.03;
		update_version($current_version);
	}
	echo "<br> V 1.03";
	if($current_version == 1.03) {
		
		$current_version = 1.04;
		update_version($current_version);
	}
	echo "<br> V 1.04";
	if($current_version == 1.04) {
				
		$current_version = 1.05;
		update_version($current_version);
	}
	echo "<br> V 1.05";
	if($current_version == 1.05) {
		
		$current_version = 1.06;
		update_version($current_version);
	}
	echo "<br> V 1.06";
	if($current_version == 1.06) {
		if(!table_exists("log_scan_loads" ,$mrr_log_db)) {
			$sql = "
				CREATE TABLE  ".$mrr_log_db."`log_scan_loads` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `filename` varchar(255),
				  `filesize` int default 0,
				  `linedate_added` datetime DEFAULT '0000-00-00',
				  `linedate_reviewed` datetime DEFAULT '0000-00-00',
				  `section_id` int default 0,
				  `notes` varchar(255),
				  `rslt` int default 0,
				  `load_id` varchar(100),
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
		}
			
		if(!field_exists("log_scan_loads", "deleted" ,$mrr_log_db)) {
			$sql = "
				alter table ".$mrr_log_db."log_scan_loads add column deleted int default 0
			";
			simple_query($sql);
		}
		
		if(!field_exists("log_scan_loads", "document_text" ,$mrr_log_db)) {
			$sql = "
				alter table ".$mrr_log_db."log_scan_loads engine=MyISAM
			";
			simple_query($sql);
			
			$sql = "
				alter table ".$mrr_log_db."log_scan_loads add column document_text text default null,
						add fulltext document_text(document_text)
			";
			simple_query($sql);
		}
					
		$current_version = 1.07;
		update_version($current_version);
	}
	echo "<br> V 1.07";
	if($current_version == 1.07) {
				
		$current_version = 1.08;
		update_version($current_version);
	}	
	
	echo "<br> V 1.08";
	if($current_version == 1.08) {
		
		$current_version = 1.09;
		update_version($current_version);
	}	
	echo "<br> V 1.09";
	if($current_version == 1.09) {
		if(!table_exists("log_edi" ,$mrr_log_db)) {
			$sql = "
				CREATE TABLE  ".$mrr_log_db."`log_edi` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `filename` varchar(255),
				  `linedate_added` datetime DEFAULT '0000-00-00',
				  `linedate_reviewed` datetime DEFAULT '0000-00-00',
				  `notes` varchar(255),
				  `rslt` int default 0,
				  `load_id` varchar(100),
				  PRIMARY KEY (`id`)
				) 
			";
			simple_query($sql);
		}

		$current_version = 1.1;
		update_version($current_version);
	}
	echo "<br> V 1.10";
	if($current_version == 1.1) {
		
		if(!table_exists("log_login" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."log_login (
					id int not null auto_increment, 
					user_id int,
					linedate_added datetime,
					ip_address varchar(100),
					invalid_username varchar(200),
					primary key(id))
			";
			simple_query($sql);
		}	
		
		$current_version = 1.11;
		update_version($current_version);
	}
	echo "<br> V 1.11";
	if($current_version == 1.11) {
		
		$current_version = 1.12;
		update_version($current_version);
	}
	echo "<br> V 1.12";
	if($current_version == 1.12) {
		
		$current_version = 1.13;
		update_version($current_version);
	}
	
	echo "<br> V 1.13";
	if($current_version == 1.13) {
		
		if(!table_exists("log_page_loads" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."log_page_loads (
					id int not null auto_increment,
					time_stamp datetime,
					ip_address varchar(50), 
					page_url varchar(255),
					user_id int,
					start_load double,
					end_load double,
					load_time double,	
					primary key(id))
			";
			simple_query($sql);
		}
					
		$current_version = 1.14;
		update_version($current_version);
	}
	
	echo "<br> V 1.14";
	if($current_version == 1.14) {
				
		$current_version = 1.15;
		update_version($current_version);
	}
	echo "<br> V 1.15";
	if($current_version == 1.15) {
				
		if(!table_exists("log_user_action" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."log_user_action (
					id int not null auto_increment,
					user_id int, 
					linedate_added datetime,
					page_url varchar(255),
					page_get varchar(255),
					page_action varchar(255),
					driver_id int, 
					truck_id int, 
					trailer_id int, 
					load_handler_id int,
					dispatch_id int,
					stop_id int,
					flag int,
					deleted int,
					page_notes text, 
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table ".$mrr_log_db."log_user_action add index user_id(user_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index driver_id(driver_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index truck_id(truck_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index trailer_id(trailer_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index load_handler_id(load_handler_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index dispatch_id(dispatch_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index stop_id(stop_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index flag(flag)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_action add index deleted(deleted)";
			simple_query($sql);
		}
		
		if(!table_exists("log_user_changes" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."log_user_changes (
					id int not null auto_increment,
					user_id int, 
					linedate_added datetime,
					table_name varchar(255),
					field_name varchar(255),
					field_value varchar(255),					
					deleted int,
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table ".$mrr_log_db."log_user_changes add index user_id(user_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_changes add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."log_user_changes add index deleted(deleted)";
			simple_query($sql);
		}
				
		$current_version = 1.16;
		update_version($current_version);
	}
	
	echo "<br> V 1.16";
	if($current_version == 1.16) {
		
		if(!table_exists("truck_tracking" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking (
					id int not null auto_increment,
					linedate_added datetime,
					truck_id int,
					linedate datetime,
					truck_speed int,
					truck_heading int,
					gps_quality int,
					latitude float(10,6),
					longitude float(10,6),
					location varchar(255),
					fix_type int,
					ignition int,
					gps_odometer decimal(10,2),
					gps_rolling_odometer decimal(10,2),
					performx_odometer decimal(10,2),
					performx_fuel decimal(10,2),
					performx_speed decimal(10,2),
					performx_idle decimal(10,2),
					serial_number int,
					packet_id int,
					driver_id int,
					driver2_id int,							 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."truck_tracking add index truck_id(truck_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking add index linedate(linedate)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking add index latitude(latitude)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking add index longitude(longitude)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking add index location(location)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking add index packet_id(packet_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking add index driver_id(driver_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking add index driver2_id(driver2_id)";
			simple_query($sql);			
		}
		if(!table_exists("truck_tracking_messages" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_messages (
					id int not null auto_increment,
					linedate_added datetime,
					truck_id int,
					user_id int,
					linedate datetime,
					message text,					 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."truck_tracking_messages add index truck_id(truck_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_messages add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_messages add index linedate(linedate)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_messages add index user_id(user_id)";
			simple_query($sql);				
		}
		if(!table_exists("truck_tracking_packets" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_packets (
					id int not null auto_increment,
					linedate_added datetime,
					packet_id int,					
					user_id int,
					linedate datetime,
					next_packet_id int,			 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."truck_tracking_packets add index packet_id(packet_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_packets add index next_packet_id(next_packet_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_packets add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_packets add index linedate(linedate)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_packets add index user_id(user_id)";
			simple_query($sql);				
		}		
		
		$current_version = 1.17;
		update_version($current_version);
	}
	echo "<br> V 1.17";
	if($current_version == 1.17) {
		
		if(!table_exists("truck_tracking_dispatches" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_dispatches (
					id int not null auto_increment,
					linedate_added datetime,
					user_id int,
					truck_id int,					
					dispatch_id int,
					stops int,
					linedate datetime,
					peoplenet_id int,			 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add index truck_id(truck_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add index dispatch_id(dispatch_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add index peoplenet_id(peoplenet_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add index linedate(linedate)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add index user_id(user_id)";
			simple_query($sql);				
		}	
		
		if(!field_exists("truck_tracking_dispatches", "preplan_use_load_id" ,$mrr_log_db)) {
			$sql = "
				alter table ".$mrr_log_db."truck_tracking_dispatches add column preplan_use_load_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index preplan_use_load_id(preplan_use_load_id)";
			simple_query($sql);	
		}
		
		//added Sep. 2012
		if(!field_exists("truck_tracking_packets", "next_msg_packet_id" ,$mrr_log_db)) {
			$sql = "
				alter table ".$mrr_log_db."truck_tracking_packets add column next_msg_packet_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_packets add index next_msg_packet_id(next_msg_packet_id)";
			simple_query($sql);	
		}
		if(!table_exists("truck_tracking_msg_history" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_msg_history (
					id int not null auto_increment,
					linedate_added datetime,
					packet_id int,					
					truck_id int,
					truck_name varchar(50),
					linedate_created datetime,
					linedate_received datetime,
					recipient_id int,
					recipient_name varchar(100),
					msn varchar(25),
					base_msn varchar(25),
					msg_type varchar(25),
					msg_text text,	 
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index packet_id(packet_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index linedate_created(linedate_created)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index linedate_received(linedate_received)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index recipient_name(recipient_name)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index msn(msn)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index base_msn(base_msn)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add index msg_type(msg_type)";
			simple_query($sql);			
		}
		
		if(!field_exists("truck_tracking_msg_history", "linedate_read" ,$mrr_log_db)) {
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add column linedate_read datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add column linedate_reply datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add column user_id_read int default '0'";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add column user_id_reply int default '0'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index user_id_read(user_id_read)";
			simple_query($sql);			
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index user_id_reply(user_id_reply)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index linedate_read(linedate_read)";
			simple_query($sql);			
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index linedate_reply(linedate_reply)";
			simple_query($sql);	
		}
		
		if(!field_exists("truck_tracking_messages", "reply_msg_id" ,$mrr_log_db)) {
			$sql = " alter table ".$mrr_log_db."truck_tracking_messages add column reply_msg_id int default '0'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add index reply_msg_id(reply_msg_id)";
			simple_query($sql);	
		}
		
		if(!field_exists("truck_tracking_messages", "archived" ,$mrr_log_db)) {
			$sql = " alter table ".$mrr_log_db."truck_tracking_messages add column archived int default '0'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add index archived(archived)";
			simple_query($sql);	
		}
		if(!field_exists("truck_tracking_msg_history", "archived" ,$mrr_log_db)) {
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_history add column archived int default '0'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index archived(archived)";
			simple_query($sql);	
		}
		
		if(!field_exists("truck_tracking_dispatches", "canceled" ,$mrr_log_db)) {
			$sql = " alter table ".$mrr_log_db."truck_tracking_dispatches add column canceled int default '0'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index canceled(canceled)";
			simple_query($sql);	
		}		
		
		if(!table_exists("truck_tracking_odometer" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_odometer (
					id int not null auto_increment,
					linedate datetime,
					truck_id int,
					odometer int, 
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table ".$mrr_log_db."truck_tracking_odometer add index truck_id(truck_id)";
			simple_query($sql);				
			$sql = " alter table ".$mrr_log_db."truck_tracking_odometer add index linedate(linedate)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_odometer add index odometer(odometer)";
			simple_query($sql);
		}
				
		if(!field_exists("truck_tracking_dispatches", "arriving_code" ,$mrr_log_db)) {
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add column arriving_code int default 0";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add column arriving_date datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add column arrived_code int default 0";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add column arrived_date datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add column departed_code int default 0";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add column departed_date datetime default '0000-00-00 00:00:00'";
			simple_query($sql);			
			
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index arriving_code(arriving_code)";
			simple_query($sql);				
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index arriving_date(arriving_date)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index arrived_code(arrived_code)";
			simple_query($sql);				
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index arrived_date(arrived_date)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index departed_code(departed_code)";
			simple_query($sql);				
			$sql = "alter table ".$mrr_log_db."truck_tracking_dispatches add index departed_date(departed_date)";
			simple_query($sql);			
		}
	
		$current_version = 1.18;
		update_version($current_version);
	}	
	
	echo "<br> V 1.18";
	if($current_version == 1.18) {
		
		
		
		if(!table_exists("log_api_import" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."log_api_import (
					id int not null auto_increment, 
					api_vendor_name varchar(100),
					filename varchar(255),
					linedate_added datetime,
					processed int default 0,
					primary key(id))
			";
			simple_query($sql);
		}		
		/*		
		if(!field_exists("truck_tracking_packets", "page_load_notes" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_packets add column page_load_notes varchar(255)";
			simple_query($sql);			
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_packets add index page_load_notes(page_load_notes)";
			simple_query($sql);	
		}
		*/
		//echo "<br> V 1.18ZZZ";		
		if(!table_exists("geofence_hot_load_tracking" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."geofence_hot_load_tracking (
					id int not null auto_increment, 
					linedate_added datetime,
					linedate datetime,
					deleted int default 0,
					active int default 0,					
					load_id int default 0,
					dispatch_id int default 0,
					stop_id int default 0,
					driver_id int default 0,
					truck_id int default 0,
					trailer_id int default 0,
					customer_id int default 0,					
					dest_longitude float(10,6) default '0.00',
					dest_latitude float(10,6) default '0.00',					
					linedate_last_gps datetime,
					last_gps_longitude float(10,6) default '0.00',
					last_gps_latitude float(10,6) default '0.00',					
					dest_distance int default 0,
					dest_message varchar(255),					
					dest_arriving int default 0,
					dest_arrived int default 0,
					dest_departed int default 0,					
					dest_remaining_arriving int default 0,
					dest_remaining_arrived int default 0,
					dest_remaining_departed int default 0,					
					dest_time_arriving int default 0,
					dest_time_arrived int default 0,
					dest_time_departed int default 0,		
					dispatch_grade int default 0,			
					primary key(id))
			";
			simple_query($sql);
						
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate_added(linedate_added)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index deleted(deleted)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index active(active)";
			simple_query($sql);				
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate(linedate)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index load_id(load_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dispatch_id(dispatch_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index stop_id(stop_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index driver_id(driver_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index trailer_id(trailer_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index customer_id(customer_id)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_longitude(dest_longitude)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_latitude(dest_latitude)";
			simple_query($sql);			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate_last_gps(linedate_last_gps)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index last_gps_longitude(last_gps_longitude)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index last_gps_latitude(last_gps_latitude)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_distance(dest_distance)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_arriving(dest_arriving)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_arrived(dest_arrived)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_departed(dest_departed)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_remaining_arriving(dest_remaining_arriving)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_remaining_arrived(dest_remaining_arrived)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_remaining_departed(dest_remaining_departed)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_time_arriving(dest_time_arriving)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_time_arrived(dest_time_arrived)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dest_time_departed(dest_time_departed)";
			simple_query($sql);	
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index dispatch_grade(dispatch_grade)";
			simple_query($sql);	
		}
		
		echo "<br> V 1.18A";
		
		if(!field_exists("geofence_hot_load_tracking", "linedate_last_msg" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column linedate_last_msg datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate_last_msg(linedate_last_msg)";
			simple_query($sql);
		}
		if(!field_exists("geofence_hot_load_tracking", "linedate_last_arriving" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column linedate_last_arriving datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate_last_arriving(linedate_last_arriving)";
			simple_query($sql);
		}
		if(!field_exists("geofence_hot_load_tracking", "linedate_last_arrived" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column linedate_last_arrived datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate_last_arrived(linedate_last_arrived)";
			simple_query($sql);
		}
		if(!field_exists("geofence_hot_load_tracking", "linedate_last_departed" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column linedate_last_departed datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index linedate_last_departed(linedate_last_departed)";
			simple_query($sql);
		}
		
		if(!field_exists("geofence_hot_load_tracking", "msg_last_arriving" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column msg_last_arriving int default 0";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index msg_last_arriving(msg_last_arriving)";
			simple_query($sql);
		}
		if(!field_exists("geofence_hot_load_tracking", "msg_last_arrived" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column msg_last_arrived int default 0";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index msg_last_arrived(msg_last_arrived)";
			simple_query($sql);
		}
		if(!field_exists("geofence_hot_load_tracking", "msg_last_departed" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column msg_last_departed int default 0";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index msg_last_departed(msg_last_departed)";
			simple_query($sql);
		}
		if(!field_exists("geofence_hot_load_tracking", "stop_completed" ,$mrr_log_db)) {
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add column stop_completed int default 0";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."geofence_hot_load_tracking add index stop_completed(stop_completed)";
			simple_query($sql);
		}
				
		//added Apr. 2013
		if(!field_exists("truck_tracking_packets", "next_event_packet_id" ,$mrr_log_db)) {
			$sql = "
				alter table ".$mrr_log_db."truck_tracking_packets add column next_event_packet_id int default 0
			";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_packets add index next_event_packet_id(next_event_packet_id)";
			simple_query($sql);	
		}
		if(!table_exists("truck_tracking_event_history" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_event_history (
					id int not null auto_increment,
					linedate_added datetime,
					linedate datetime,
					packet_id int,	
					pn_dispatch_id int default 0,
					pn_stop_id int default 0,
					e_type varchar(50),
					e_reason varchar(50),
					e_latitude float(10,6) default '0.00',
					e_longitude float(10,6) default '0.00',
					px_fuel decimal(10,3) default '0.000',
					px_odometer decimal(10,3) default '0.000',
					px_odo_type int default 0,
					stop_data varchar(255),					
					load_id int default 0,
					disptach_id int default 0,
					stop_id int default 0,
					truck_id int,
					truck_name varchar(50),
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index linedate(linedate)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index packet_id(packet_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index pn_dispatch_id(pn_dispatch_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index pn_stop_id(pn_stop_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index load_id(load_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index disptach_id(disptach_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index stop_id(stop_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_event_history add index truck_id(truck_id)";
			simple_query($sql);			
		}
		echo "<br> V 1.18B";
		if(!field_exists("truck_tracking_event_history", "email_sent" ,$mrr_log_db)) {
			$sql = "alter table ".$mrr_log_db."truck_tracking_event_history add column email_sent int default 0";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_event_history add column email_sent_date datetime default '0000-00-00 00:00:00'";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_event_history add index email_sent(email_sent)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_event_history add index email_sent_date(email_sent_date)";
			simple_query($sql);
		}
		
				
		if(!field_exists("truck_tracking", "safety_report_made" ,$mrr_log_db)) {
			$sql = "alter table ".$mrr_log_db."truck_tracking add column safety_report_made int default 0";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking add index safety_report_made(safety_report_made)";
			simple_query($sql);	
		}
		
		
		if(!table_exists("safety_report_signs" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."safety_report_signs (
					id int not null auto_increment,
					linedate_added datetime,
					deleted int default 0,
					ne_latitude float(10,6) default '0.000000',
					ne_longitude float(10,6) default '0.000000',
					sw_latitude float(10,6) default '0.000000',
					sw_longitude float(10,6) default '0.000000',
					sign_info text,
					sign_list text,
					max_speed int,
					min_speed int,
					avg_speed int,
					sign_count int,
					url varchar(255),					
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index linedate_start(linedate_start)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index deleted(deleted)";
			simple_query($sql);				
			
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index ne_latitude(ne_latitude)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index ne_longitude(ne_longitude)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index sw_latitude(sw_latitude)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index sw_longitude(sw_longitude)";
			simple_query($sql);	
			
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index max_speed(max_speed)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index min_speed(min_speed)";
			simple_query($sql);				
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index avg_speed(avg_speed)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_signs add index sign_count(sign_count)";
			simple_query($sql);							
		}
		
		
		if(!table_exists("safety_report_violations" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."safety_report_violations (
					id int not null auto_increment,
					linedate_added datetime,
					linedate datetime,
					deleted int default 0,
					truck_id int default 0,
					driver_id int default 0,
					employer_id int default 0,
					violation_code int default 0,
					violation varchar(255),
					abrupt_shutdown int default 0,
					sign_id int,
					cur_speed int,
					cur_miles decimal(10,2) default '0.00',
					cur_feet decimal(10,2) default '0.00',
					cur_hours_driven decimal(10,2) default '0.00',
					cur_hours_worked decimal(10,2) default '0.00',
					cur_hours_rested decimal(10,2) default '0.00',
					wk_hours_driven decimal(10,2) default '0.00',
					wk_hours_worked decimal(10,2) default '0.00',
					wk_hours_rested decimal(10,2) default '0.00',
					excused int,
					excused_by int,
					excused_date datetime default '0000-00-00 00:00:00',
					excused_notes text,	
					latitude float(10,6) default '0.000000',
					longitude float(10,6) default '0.000000',
					location varchar(255),			
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index linedate(linedate)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index deleted(deleted)";
			simple_query($sql);	
						
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index driver_id(driver_id)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index employer_id(employer_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index violation_code(violation_code)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index abrupt_shutdown(abrupt_shutdown)";
			simple_query($sql);	
			
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index sign_id(sign_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index cur_speed(cur_speed)";
			simple_query($sql);				
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index cur_miles(cur_miles)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index cur_feet(cur_feet)";
			simple_query($sql);	
			
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index cur_hours_driven(cur_hours_driven)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index cur_hours_worked(cur_hours_worked)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index cur_hours_rested(cur_hours_rested)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index wk_hours_driven(wk_hours_driven)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index wk_hours_worked(wk_hours_worked)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index wk_hours_rested(wk_hours_rested)";
			simple_query($sql);			
			
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index excused(excused)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index excused_by(excused_by)";
			simple_query($sql);	
			
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index latitude(latitude)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."safety_report_violations add index longitude(longitude)";
			simple_query($sql);							
		}
		echo "<br> V 1.18C";
		if(!table_exists("driver_elog_entries" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."driver_elog_entries (
					id int not null auto_increment,
                      	event_id int(11),
                      	driver_id int(11),
                      	linedate_added datetime,
                      	linedate_created datetime,
                      	linedate_effective datetime,
                      	event_data1 varchar(255),
                      	event_data2 varchar(255),
                      	event_data3 varchar(255),
                      	event_data4 varchar(255),
                      	setting1 varchar(255),
                      	setting2 varchar(255),
                      	setting3 varchar(255),
                      	setting4 varchar(255),
                      	active int(11),
                      	deleted int(11),
                      	pardoned int(11),
                      	pardoned_by_user int(11),
                      	pardoned_reason varchar(255),
                      	customer_id int(11),
                      	truck_id int(11),
                      	trailer_id int(11),
                      	load_id int(11),
                      	dispatch_id int(11),
                      	stop_id int(11),
                      	packet_id int(11),
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index linedate_created(linedate_created)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index linedate_effective(linedate_effective)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index active(active)";
			simple_query($sql);		
			
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index pardoned(pardoned)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index pardoned_by_user(pardoned_by_user)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index customer_id(customer_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index trailer_id(trailer_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index load_id(load_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index dispatch_id(dispatch_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index stop_id(stop_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."driver_elog_entries add index packet_id(packet_id)";
			simple_query($sql);		
		}
		
		if(!table_exists("truck_tracking_packet_xml" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_packet_xml (
					id int not null auto_increment,
					packet_id int,
                      	packet_type int, 
                      	packet_xml text,                     	
                      	linedate_added datetime,                      	     	                    
					primary key(id))
			";
			simple_query($sql);		
			
			$sql = " alter table ".$mrr_log_db."truck_tracking_packet_xml add index packet_id(packet_id)";
			simple_query($sql);	
		}
		
		if(!table_exists("user_change_log" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."user_change_log (
					id int not null auto_increment,
					linedate_added datetime,
					active int(11),
                      	deleted int(11),
					user_id int(11),
					customer_id int(11),
					driver_id int(11),
                      	truck_id int(11),
                      	trailer_id int(11),
                      	load_id int(11),
                      	dispatch_id int(11),
                      	stop_id int(11),
                      	notes varchar(255),
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."user_change_log add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."user_change_log add index active(active)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index user_id(user_id)";
			simple_query($sql);				
			$sql = " alter table ".$mrr_log_db."user_change_log add index customer_id(customer_id)";
			simple_query($sql);		
			$sql = " alter table ".$mrr_log_db."user_change_log add index driver_id(driver_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index trailer_id(trailer_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index load_id(load_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index dispatch_id(dispatch_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."user_change_log add index stop_id(stop_id)";
			simple_query($sql);					
		}
		
		if(!table_exists("truck_tracking_msg_record" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_msg_record (
					id int not null auto_increment,
					linedate_added datetime, 
                      	truck_id int(11),                      	
                      	deleted int(11),
                      	active int(11),
                      	load_id int(11),
                      	dispatch_id int(11),
                      	stop_id int(11),
                      	sent_msg int(11),     
                      	msg text,                	                     	                    
					primary key(id))
			";
			simple_query($sql);
			
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index active(active)";
			simple_query($sql);		
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index load_id(load_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index dispatch_id(dispatch_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index stop_id(stop_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."truck_tracking_msg_record add index sent_msg(sent_msg)";
			simple_query($sql);	
		}
				
		if(!table_exists("insurance_email_log" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."insurance_email_log (
					id int not null auto_increment,
					linedate_added datetime,
					active int(11),
                      	deleted int(11),
					user_id int(11),
					driver_id int(11),
                      	truck_id int(11),
                      	trailer_id int(11),
                      	email_addr varchar(255),
                      	email_msg text,
					primary key(id))
			";
			simple_query($sql);
						
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index active(active)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index deleted(deleted)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index user_id(user_id)";
			simple_query($sql);		
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index driver_id(driver_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index truck_id(truck_id)";
			simple_query($sql);	
			$sql = " alter table ".$mrr_log_db."insurance_email_log add index trailer_id(trailer_id)";
			simple_query($sql);	
		}
				
		if(!field_exists("truck_tracking_msg_history", "no_response_needed" ,$mrr_log_db)) {
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add column no_response_needed int default 0"; 
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index no_response_needed(no_response_needed)";
			simple_query($sql);
		}
		echo "<br> V 1.18D";
		
		//temp ground for XML to be saved for review...not intended to be on all the time, but for testing.
		if(!table_exists("truck_tracking_dispatch_xml" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."truck_tracking_dispatch_xml (
					id int not null auto_increment,
					linedate_added datetime, 
					run_code int(11) default 0,                   	
                      	xml_string text,
					primary key(id))
			";
			simple_query($sql);	
		}
		
		if(!table_exists("zip_to_zip_trips" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."zip_to_zip_trips (
					id int not null auto_increment,
					linedate_added datetime, 
					zip_code_1 int(11) default 0,
					zip_code_2 int(11) default 0,
					miles decimal(10,2) default '0.00', 
					timer time default '00:00:00', 
                      	deleted int(11) default 0,
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table ".$mrr_log_db."zip_to_zip_trips add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."zip_to_zip_trips add index zip_code_1(zip_code_1)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."zip_to_zip_trips add index zip_code_2(zip_code_2)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."zip_to_zip_trips add index miles(miles)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."zip_to_zip_trips add index deleted(deleted)";
			simple_query($sql);
		}
		
		if(!field_exists("truck_tracking_msg_history", "driver_id" ,$mrr_log_db)) {
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add column driver_id int default 0"; 
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add column dispatch_id int default 0"; 
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add column load_id int default 0"; 
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index dispatch_id(dispatch_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_msg_history add index load_id(load_id)";
			simple_query($sql);
		}
		
		if(!field_exists("truck_tracking_messages", "driver_id" ,$mrr_log_db)) {
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add column driver_id int default 0"; 
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add column dispatch_id int default 0"; 
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add column load_id int default 0"; 
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add index dispatch_id(dispatch_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."truck_tracking_messages add index load_id(load_id)";
			simple_query($sql);
		}
		
		
		if(!table_exists("pro_miles_zip_codes" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."pro_miles_zip_codes (
					id int not null auto_increment,
					linedate_added datetime,
					deleted int(11) default 0,					
					city varchar(100) default '',
					state varchar(10) default '',
					zip varchar(10) default '',
					latitude decimal(10,6) default '0.000000',
					longitude decimal(10,6) default '0.000000',
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index deleted(deleted)";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index city(city)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index state(state)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index zip(zip)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index latitude(latitude)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."pro_miles_zip_codes add index longitude(longitude)";
			simple_query($sql);
		}
		
		if(!table_exists("twilio_call_log" ,$mrr_log_db)) {
			$sql = "
				create table ".$mrr_log_db."twilio_call_log (
					id int not null auto_increment,
					linedate_added datetime,
					deleted int(11) default 0,	
					phone_from varchar(20) default '',
					cmd varchar(100) default '',
					text_code varchar(100) default '',
					response varchar(100) default '',
					driver_id int(11) default 0,
					truck_id int(11) default 0,					
					trailer_id int(11) default 0,
					load_id int(11) default 0,	
					disp_id int(11) default 0,	
					stop_id int(11) default 0,	
					customer_id int(11) default 0,
					city varchar(100) default '',
					state varchar(10) default '',
					location varchar(255) default '',
					latitude decimal(10,6) default '0.000000',
					longitude decimal(10,6) default '0.000000',
					truck_speed int(11) default 0,
					truck_heading varchar(20) default '',
					subject varchar(255) default '',
					message text,			
					primary key(id))
			";
			simple_query($sql);	
			
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index linedate_added(linedate_added)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index deleted(deleted)";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index driver_id(driver_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index truck_id(truck_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index trailer_id(trailer_id)";
			simple_query($sql);
			
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index load_id(load_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index disp_id(disp_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index stop_id(stop_id)";
			simple_query($sql);
			$sql = "alter table ".$mrr_log_db."twilio_call_log add index customer_id(customer_id)";
			simple_query($sql);
		}
		echo "<br> V 1.18F";			
		$current_version = 1.19;
		update_version($current_version);
	
	}	
	
	echo "<p>Update completed to Log version $current_version...";

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