<? include("application.php") ?>
<?
     $mrr_micro_seconds_start_ajax=time();
          
     error_reporting(E_ALL);
     ini_set('display_errors', '1');
     ini_set('max_input_vars', '60000');
     date_default_timezone_set("America/Chicago");
     
     //mrr_get_ar_detail_info_find_v2()
     
     $mrr_activity_log['driver_id']=0;
     $mrr_activity_log['truck_id']=0;
     $mrr_activity_log['trailer_id']=0;
     $mrr_activity_log['load_handler_id']=0;
     $mrr_activity_log['dispatch_id']=0;
     $mrr_activity_log['stop_id']=0;
     $mrr_activity_log['notes']="";

	if(!isset($new_style_path))
	{
		$new_style_path="images/2012/";	
	}
	
	include_once('functions_lynnco.php');
     include_once('functions_koch.php');

	switch ($_GET['cmd']) {
		case 'move_truck_day':
			move_truck_day();
			break;
		case 'update_trailer_location':
			update_trailer_location();
			break;
		case 'move_note_day':
			move_note_day();
			break;
          case 'load_notes':
               load_notes();
               break;
          case 'load_notes_pre':
			load_notes_pre();
			break;
		case 'mrr_load_notes':
			mrr_load_notes();
			break;
		case 'delete_note':
			delete_note();
			break;
		case 'mrr_update_note_entry_access':
			mrr_update_note_entry_access();
			break;
		case 'display_notes_mr':
			display_notes_mr();
			break;
          case 'display_notes':
               display_notes();
               break;
		case 'add_note_entry':
			add_note_entry();
			break;
		case 'mrr_send_driver_message_out':
			mrr_send_driver_message_out();
			break;
		case 'display_attachments':
			display_attachments();
			break;
		case 'display_attachments_alt':
			display_attachments_alt();
			break;
		case 'delete_attachment':
			delete_attachment();
			break;
		case 'update_file_attachment_name':
			update_file_attachment_name();
			break;
			
		case 'set_calendar_display_mode':
			set_calendar_display_mode();
			break;
		case 'load_driver_history':
			load_driver_history();
			break;
		case 'load_truck_history':
			load_truck_history();
			break;
		case 'load_trailer_check':
			load_trailer_check();
			break;
		case 'load_customer_brief':
			load_customer_brief();
			break;
		
		case 'delete_note_entry':
			delete_note_entry();
			break;
		case 'set_dispatch_display_mode':
			set_dispatch_display_mode();
			break;
	 	case 'load_available_loads':
	 		load_available_loads();
	 		break;
	 	case 'load_stops':
	 	 	load_stops();
	 	 	break;
	 	case 'mrr_reset_stop_times':
	 		mrr_reset_stop_times();
	 		break;
	 	case 'mrr_switch_drop_trailer_on_stop':
	 		mrr_switch_drop_trailer_on_stop();
	 		break;
	 	case 'mrr_drop_switched_trailer_action':
	 		mrr_drop_switched_trailer_action();
	 		break;
	 	case 'manage_stop':
	 		manage_stop();
	 		break;
	 	case 'load_stop_id':
	 		load_stop_id();
	 		break;
	 	case 'delete_stop':
	 		delete_stop();
	 		break;
	 	case 'mrr_address_purgery':
	 		mrr_address_purgery();
	 		break;
	 	case 'mrr_zone_purgery':
	 		mrr_zone_purgery();
	 		break;
	 	case 'mrr_auto_update_preplan_marker':
	 		mrr_auto_update_preplan_marker();
	 		break;
          case 'mrr_auto_update_preplan_driver':
               mrr_auto_update_preplan_driver();
               break;
	 	case 'update_stop_dispatch':
	 		update_stop_dispatch();
	 		break;
	 	case 'update_stop_arrival':
	 		update_stop_arrival();
	 		break;
	 	case 'update_stop_completed':
	 		update_stop_completed();
	 		break;
	 	case 'update_stop_completed_no_arrival':
	 		update_stop_completed_no_arrival();
	 		break;
	 	case 'load_handler_quick_create':
	 		load_handler_quick_create();
	 		break;
	 	case 'add_dispatch_expense':
	 		add_dispatch_expense();
	 		break;
	 	case 'load_dispatch_expenses':
	 		load_dispatch_expenses();
	 		break;
	 	case 'delete_dispatch_expense':
	 		delete_dispatch_expense();
	 		break;
	 	case 'load_dispatchs':
	 		load_dispatchs();
	 		break;
	 	case 'search_stop_address':
	 		search_stop_address();
	 		break;
	 	case 'load_address_by_stop_id':
	 		load_address_by_stop_id();
	 		break;
	 	case 'mrr_fetch_spec_notes':
	 		mrr_fetch_spec_notes();
	 		break;
	 	case 'clear_address_history':
	 		clear_address_history();
	 		break;
	 	case 'load_driver_expenses':
	 		load_driver_expenses();
	 		break;
	 	case 'add_driver_expense':
	 		add_driver_expense();
	 		break;
	 	case 'delete_driver_expense':
	 		delete_driver_expense();
	 		break;
	 	case 'get_daily_cost_ajax':
	 		get_daily_cost_ajax();
	 		break;
	 	case 'mrr_update_history_truck_value':
	 		mrr_update_history_truck_value();
	 		break;
	 	case 'save_odometer_reading':
	 	 	save_odometer_reading();
	 	 	break;
	 	case 'truck_odometer_alert':
	 	 	truck_odometer_alert();
	 	 	break;
	 	case 'load_odometer_history':
	 		load_odometer_history();
	 		break;
	 	case 'delete_odometer_entry':
	 		delete_odometer_entry();
	 		break;
	 	case 'load_driver_unavailable':
	 		load_driver_unavailable();
	 		break;
	 	case 'add_driver_unavailability':
	 		add_driver_unavailability();
	 		break;
	 	case 'delete_driver_unavailability':
	 		delete_driver_unavailability();
	 		break;
	 	case 'mrr_edit_driver_unavailability':
	 		mrr_edit_driver_unavailability();
	 		break;
	 	case 'driver_unavailable':
	 		driver_unavailable();
	 		break;
	 	case 'driver_unavailable_range':
	 		driver_unavailable_range();
	 		break;
	 		
          case 'get_truck_notes':
               get_truck_notes();
               break;
	 	case 'add_truck_note':
	 		add_truck_note();
	 		break;
	 	case 'add_truck_note_mrr_alt':
	 		add_truck_note_mrr_alt();
	 		break;
	 	case 'mrr_truck_note_deadlined':
	 		mrr_truck_note_deadlined();
	 		break;
	 	case 'detach_truck':
	 		detach_truck();
	 		break;
	 	case 'detach_trailer':
	 		detach_trailer();
	 		break;
	 	case 'load_flat_rate_routes':
	 		load_flat_rate_routes();
	 		break;
	 		
	 	case 'load_attached_equipment':
	 		load_attached_equipment();
	 		break;
	 	case 'get_detach_info':
	 		get_detach_info();
	 		break;
	 	case 'rename_scanned_load':
	 		rename_scanned_load();
	 		break;
	 	case 'delete_scanned_load':
	 		delete_scanned_load();
	 		break;
	 	case 'mrr_rename_scanned_file':
	 		mrr_rename_scanned_file();
	 		break;
	 	case 'mrr_delete_scanned_file':
	 		mrr_delete_scanned_file();
	 		break;	
	 	case 'driver_load_flag':
	 		driver_load_flag();
	 		break;
	 	case 'mrr_sicap_clear_from_invoice':
	 		mrr_sicap_clear_from_invoice();
	 		break;
	 	case 'ajax_sicap_create_invoice':
	 		ajax_sicap_create_invoice();
	 		break;
	 	case 'update_driver_notes':
	 		update_driver_notes();
	 		break;
	 	case 'get_driver_notes':
	 		get_driver_notes();
	 		break;
	 	case 'ajax_sicap_delete_invoice':
	 		ajax_sicap_delete_invoice();
	 		break;
	 	case 'get_driver_rate_per_mile':
	 		get_driver_rate_per_mile();
	 		break;
	 	case 'update_stop_odometer':
	 		update_stop_odometer();
	 		break;
	 	case 'update_predispatch':
		 	update_predispatch();
		 	break;
		case 'ajax_get_option_list':
		 	ajax_get_option_list();
		 	break;
		case 'mrr_prior_maint_request':
			mrr_prior_maint_request();
			break;
		case 'ajax_get_last_odometer_reading':
		 	ajax_get_last_odometer_reading();
		 	break; 
		case 'ajax_get_option_single':
		 	ajax_get_option_single();
		 	break;
		case 'ajax_update_maint_req':
		 	ajax_update_maint_req();
		 	break;	
		case 'ajax_maint_req_list':
		 	ajax_maint_req_list();
		 	break;
		case 'ajax_make_line_item_list':
		 	ajax_make_line_item_list();
		 	break;
		case 'ajax_update_maint_req_item':
		 	ajax_update_maint_req_item();
		 	break;
		case 'ajax_get_single_line_item':
		 	ajax_get_single_line_item();
		 	break;
		case 'ajax_remove_one_maint_line_item':
		 	ajax_remove_one_maint_line_item();
		 	break;
          case 'ajax_remove_mr_quick_note_entry':
               ajax_remove_mr_quick_note_entry();
               break;
		case 'ajax_generate_recurring_schedule_notices':
			ajax_generate_recurring_schedule_notices();
			break;
		case 'ajax_copy_maint_request_or_recurring':
			ajax_copy_maint_request_or_recurring();	
			break;
		case 'mrr_load_stop_odometer_grab':
			mrr_load_stop_odometer_grab();
			break;
		case 'mrr_get_customer_email':
			mrr_get_customer_email();
			break;
		case 'mrr_email_this_thing':
			mrr_email_this_thing();
			break;
		case 'mrr_email_this_quote':
			mrr_email_this_quote();
			break;
		case 'mrr_store_google_map':
			mrr_store_google_map();
			break;
		case 'mrr_store_stop_miles':
			mrr_store_stop_miles();
			break;
		case 'display_additional_contacts':
			display_additional_contacts();
			break;
		case 'save_additional_contacts':
			save_additional_contacts();
			break;
		case 'load_additional_contacts':
			load_additional_contacts();
			break;
		case 'kill_additional_contacts':
			kill_additional_contacts();
			break;
		case 'display_help_desk':
			display_help_desk();
			break;
		case 'save_help_desk':
			save_help_desk();
			break;
		case 'load_help_desk':
			load_help_desk();
			break;
		case 'kill_help_desk':
			kill_help_desk();
			break;
		
		case 'add_log_user_validation':
			add_log_user_validation();
			break;
			
		case 'load_customer_surcharge_list':
			load_customer_surcharge_list();
			break;
		case 'mrr_pull_surcharge':
			mrr_pull_surcharge();
			break;
		case 'add_trip_packs':
			add_trip_packs();
			break;
		case 'kill_trip_packs':
			kill_trip_packs();
			break;
		case 'kill_trip_packs_alt':
			kill_trip_packs_alt();
			break;
		case 'mrr_kill_search_file':
			mrr_kill_search_file();
			break;
		case 'mrr_kill_search_note':
			mrr_kill_search_note();
			break;
		case 'mrr_get_accident_reports':
			mrr_get_accident_reports();
			break;
		case 'mrr_add_accident_reports':
			mrr_add_accident_reports();
			break;
		case 'mrr_kill_accident_reports':
			mrr_kill_accident_reports();
			break;
		case 'mrr_kill_accident_damage':
			mrr_kill_accident_damage();
			break;
		case 'mrr_kill_all_accident_damage':
			mrr_kill_all_accident_damage();
			break;
		case 'mrr_list_accident_reports':
			mrr_list_accident_reports();
			break;
		case 'mrr_test_name_type':
			mrr_test_name_type();
			break;
		case 'mrr_list_master_loads':
			mrr_list_master_loads();
			break;
		case 'mrr_copy_load_handler_from_master_load':
			mrr_copy_load_handler_from_master_load();
			break;
		case 'mrr_expand_punch_clock_data':
			mrr_expand_punch_clock_data();
			break;
		case 'mrr_update_punch_clock_data_notes':
			mrr_update_punch_clock_data_notes();
			break;
		case 'mrr_update_punch_clock_data_hrs':
			mrr_update_punch_clock_data_hrs();
			break;
		case 'mrr_reload_graph_comparison':
			mrr_reload_graph_comparison();
			break;
		case 'mrr_full_graph_generator':
			mrr_full_graph_generator();
			break;
		case 'mrr_ajax_get_budget_list':
			mrr_ajax_get_budget_list();
			break;
		case 'mrr_ajax_get_budget':
			mrr_ajax_get_budget();
			break;
		case 'mrr_ajax_save_budget':
			mrr_ajax_save_budget();
			break;
		case 'mrr_ajax_delete_budget':
			mrr_ajax_delete_budget();
			break;
		case 'mrr_ajax_get_budget_item_list':
			mrr_ajax_get_budget_item_list();
			break;
		case 'mrr_ajax_save_budget_item':
			mrr_ajax_save_budget_item();
			break;
		case 'mrr_ajax_calc_budget_table':
			mrr_ajax_calc_budget_table();
			break;
		case 'mrr_get_ar_summary_info_find':
			mrr_get_ar_summary_info_find();
			break;
		case 'mrr_get_ar_detail_info_find':
			mrr_get_ar_detail_info_find();
			break;
		case 'mrr_get_ar_detail_info_find_v2':
			mrr_get_ar_detail_info_find_v2();
			break;
		case 'mrr_save_stoplight_warning_notes':
			mrr_save_stoplight_warning_notes();
			break;
		case 'mrr_restore_canceled_load':
			mrr_restore_canceled_load();
			break;
		case 'mrr_pull_driver_charge_rate':
			mrr_pull_driver_charge_rate();
			break;
		case 'mrr_lading_number_search':
			mrr_lading_number_search();
			break;
		case 'mrr_toggle_dropped_trailers_on':
			mrr_toggle_dropped_trailers_on();
			break;
		case 'mrr_toggle_dropped_trailers_off':
			mrr_toggle_dropped_trailers_off();
			break;
		case 'mrr_full_search': 
			mrr_full_search();
			break;
		case 'mrr_snap_shot':
			mrr_snap_shot();
			break;
		case 'mrr_verify_item_name':
			mrr_verify_item_name();
			break;
		case 'mrr_truck_tracking_report':
			mrr_truck_tracking_report();
			break;
		case 'mrr_truck_tracking_dispatches_sent':
			mrr_truck_tracking_dispatches_sent();
			break;
		case 'mrr_truck_tracking_equip_messages_sent':
			mrr_truck_tracking_equip_messages_sent();
			break;
		case 'mrr_truck_tracking_messages_sent':
			mrr_truck_tracking_messages_sent();
			break;
		case 'mrr_truck_tracking_messages_received':
			mrr_truck_tracking_messages_received();
			break;
			
          case 'mrr_equipment_special_notices':
               mrr_equipment_special_notices();
               break;
			
		case 'mrr_geotab_messages_mark_read':
			mrr_geotab_messages_mark_read();
			break;
		case 'mrr_geotab_messages_sent':
			mrr_geotab_messages_sent();
			break;
		case 'mrr_geotab_messages_received':
			mrr_geotab_messages_received();
			break;
		case 'mrr_geotab_dispatches_sent':
			mrr_geotab_dispatches_sent();
			break;
			
		case 'mrr_truck_tracking_messages_mark_read':
			mrr_truck_tracking_messages_mark_read();
			break;
		case 'mrr_truck_tracking_messages_check_for_new':
			mrr_truck_tracking_messages_check_for_new();
			break;
		case 'mrr_plot_truck_tracking_report':
			mrr_plot_truck_tracking_report();
			break;
		case 'mrr_cust_average_pay_report':
			mrr_cust_average_pay_report();
			break;
		case 'mrr_count_text_box_characters':
			mrr_count_text_box_characters();
			break;
		case 'mrr_current_date_and_time':
			mrr_current_date_and_time();
			break;
		case 'mrr_fetch_canned_message':
			mrr_fetch_canned_message();
			break;
		case 'mrr_customer_fuel_surcharge_by_date':
			mrr_customer_fuel_surcharge_by_date();
			break;
		case 'mrr_clear_switch_id':
			mrr_clear_switch_id();
			break;
		case 'mrr_get_current_location_for_truck_id':
			mrr_get_current_location_for_truck_id();
			break;
		case 'view_comdata_log':
			view_comdata_log();
			break;
		case 'mrr_update_driver_employer':
			mrr_update_driver_employer();
			break;	
		case 'mrr_update_driver_payroll_change':
			mrr_update_driver_payroll_change();
			break;	
		case 'mrr_add_driver_payroll_change':
			mrr_add_driver_payroll_change();
			break;
		case 'mrr_add_hot_load_tracking':
			mrr_add_hot_load_tracking();
			break;
		case 'mrr_get_hot_load_tracking':
			mrr_get_hot_load_tracking();
			break;
		case 'mrr_remove_hot_load_tracking':
			mrr_remove_hot_load_tracking();
			break;
		case 'mrr_update_hot_load_tracking':
			mrr_update_hot_load_tracking();
			break;
		case 'mrr_send_email_for_hot_load_tracking':
			mrr_send_email_for_hot_load_tracking();
			break;
		case 'mrr_check_up_on_geo_id':
			mrr_check_up_on_geo_id();
			break;
		case 'mrr_check_up_on_geotab_id':
			mrr_check_up_on_geotab_id();
			break;
		case 'mrr_truck_tracking_geofencing_report':
			mrr_truck_tracking_geofencing_report();
			break;
		case 'load_recent_pn_messages':
			load_recent_pn_messages();
			break;
		case 'load_recent_phone_only_messages':
			load_recent_phone_only_messages();
			break;
		case 'check_city_state_zip_info':
			check_city_state_zip_info();
			break;
		case 'mrr_dispatcher_tasks_display':
			mrr_dispatcher_tasks_display();
			break;
		case 'mrr_dispatcher_tasks_work_status':
			mrr_dispatcher_tasks_work_status();
			break;
		case 'mrr_dispatcher_tasks_work_update':
			mrr_dispatcher_tasks_work_update();
			break;
		case 'mrr_auto_trailer_drop_complete':
			mrr_auto_trailer_drop_complete();
			break;
		case 'mrr_graduate_load_to_master_load':
			mrr_graduate_load_to_master_load();
			break;
		case 'mrr_pn_email_processor':
			mrr_pn_email_processor();
			break;
		case 'mrr_pn_update_incoming_message_ignore':
			mrr_pn_update_incoming_message_ignore();
			break;	
			
		case 'mrr_geotab_update_incoming_message_ignore':
			mrr_geotab_update_incoming_message_ignore();
			break;	
			
		case 'mrr_special_ops_copy_stops_from_load':
			mrr_special_ops_copy_stops_from_load();
			break;
		case 'mrr_preplan_auto_set_driver_for_load':
			mrr_preplan_auto_set_driver_for_load();
			break;
		case 'mrr_display_previous_trailer_drops':
			mrr_display_previous_trailer_drops();
			break;
		case 'mrr_display_complete_trailer_drop':
			mrr_display_complete_trailer_drop();
			break;
		case 'mrr_build_run_miles_by_zip':
			mrr_build_run_miles_by_zip();
			break;			
		case 'mrr_build_run_miles_by_zip_alt':
			mrr_build_run_miles_by_zip_alt();
			break;
		case 'mrr_build_run_miles_by_zip_alt2':
			mrr_build_run_miles_by_zip_alt2();
			break;
		case 'mrr_value_update_equip_value_id':
			mrr_value_update_equip_value_id();
			break;		
		case 'mrr_pro_miles_dist_calc':
			mrr_pro_miles_dist_calc();
			break;				
		case 'mrr_update_pn_mileage_values':
			mrr_update_pn_mileage_values();
			break;
		case 'mrr_quick_message_form_display':
			mrr_quick_message_form_display();
			break;
		case 'mrr_quick_message_form_display_geotab':
			mrr_quick_message_form_display_geotab();
			break;
		case 'mrr_quick_message_form_sender':
			mrr_quick_message_form_sender();
			break;
		case 'mrr_quick_message_form_sender_geotab':
			mrr_quick_message_form_sender_geotab();
			break;
		case 'search_gps_zip_codes':
			search_gps_zip_codes();
			break;
		case 'search_gps_zip_codes2':
			search_gps_zip_codes2();
			break;
		case 'load_city_state_by_zip':
			load_city_state_by_zip();
			break;
		case 'mrr_deflag_master_load':
			mrr_deflag_master_load();
			break;
		case 'mrr_get_driver_dot_info_for_load_planning':
			mrr_get_driver_dot_info_for_load_planning();
			break;
		case 'mrr_get_driver_timeoff':
			mrr_get_driver_timeoff();
			break;
		case 'mrr_truck_in_shop_switch':
			mrr_truck_in_shop_switch();
			break;
		case 'mrr_truck_in_shop_switch_toggle':
			mrr_truck_in_shop_switch_toggle();
			break;
		case 'mrr_ajax_driver_hours_for_week':
			mrr_ajax_driver_hours_for_week();
			break;
		
		case 'mrr_add_driver_absense_record':
			mrr_add_driver_absense_record();
			break;
		case 'mrr_list_driver_absense_records':
			mrr_list_driver_absense_records();
			break;
		case 'mrr_remove_driver_absense_records':
			mrr_remove_driver_absense_records();
			break;
		
		case 'mrr_add_user_absense_record':
			mrr_add_user_absense_record();
			break;
		case 'mrr_list_user_absense_records':
			mrr_list_user_absense_records();
			break;
		case 'mrr_remove_user_absense_records':
			mrr_remove_user_absense_records();
			break;
			
		case 'mrr_disp_cost_calc_viewer':
			mrr_disp_cost_calc_viewer();
			break;
			
		case 'mrr_update_maint_request_unit_location':
			mrr_update_maint_request_unit_location();
			break;
		case 'mrr_update_maint_request_unit_location_alt':
			mrr_update_maint_request_unit_location_alt();
			break;
          case 'mrr_update_mr_unit_local_snooze':
               mrr_update_mr_unit_local_snooze();
               break;
          case 'mrr_send_mr_msg_note':
               mrr_send_mr_msg_note();
               break;
     
          case 'mrr_pull_accident_list':
               mrr_pull_accident_list();
               break;
		
		case 'mrr_update_maint_truck_inspect_item':
			mrr_update_maint_truck_inspect_item();
			break;
		case 'mrr_update_maint_truck_inspection':
			mrr_update_maint_truck_inspection();
			break;
		case 'mrr_update_maint_trailer_inspection':
			mrr_update_maint_trailer_inspection();
			break;
		
		case 'mrr_make_sicap_invoice':
			mrr_make_sicap_invoice();
			break;
		case 'mrr_kill_sicap_invoice':
			mrr_kill_sicap_invoice();
			break;
		
		case 'mrr_make_sicap_invoice_timesheet':
			mrr_make_sicap_invoice_timesheet();
			break;
		case 'mrr_kill_sicap_invoice_timesheet':
			mrr_kill_sicap_invoice_timesheet();
			break;
		
		case 'mrr_make_sicap_invoice_trailer':
			mrr_make_sicap_invoice_trailer();
			break;
		case 'mrr_kill_sicap_invoice_trailer':
			mrr_kill_sicap_invoice_trailer();
			break;
		case 'mrr_kill_sicap_invoice_trailer_all':
			mrr_kill_sicap_invoice_trailer_all();
			break;
			
		case 'mrr_update_stop_fault_grade':
			mrr_update_stop_fault_grade();
			break;
			
		case 'mrr_update_dispatch_flat_costs':
			mrr_update_dispatch_flat_costs();
			break;
		
		case 'display_dispatch_im_msgs':
			display_dispatch_im_msgs();
			break;
		case 'add_dispatch_im_msg':
			add_dispatch_im_msg();
			break;
		case 'kill_dispatch_im_msg':
			kill_dispatch_im_msg();
			break;
		
		case 'mrr_bypass_geotab_location_updater':
			mrr_bypass_geotab_location_updater();
			break;
			
		case 'mrr_find_truck_tracking_dispatch_record_all':
			ajax_mrr_find_truck_tracking_dispatch_record_all();
			break;
			
		case 'mrr_auto_complete_dispatch_from_report':
			mrr_auto_complete_dispatch_from_report();
			break;
		
		case 'kill_cust_timesheets':
			kill_cust_timesheets();
			break;	
		case 'add_cust_timesheets':
			add_cust_timesheets();
			break;
		case 'list_cust_timesheets':
			list_cust_timesheets();
			break;
			
		case 'form_timesheets_entries':
			form_timesheets_entries();
			break;
		
		case 'find_truck_for_driver':
			find_truck_for_driver();
			break;
			
		case 'kill_switch_shuttle_rates':
			kill_switch_shuttle_rates();
			break;
		case 'find_switch_shuttle_rates':
			find_switch_shuttle_rates();
			break;	
		case 'add_switch_shuttle_rates':
			add_switch_shuttle_rates();
			break;
		case 'list_switch_shuttle_rates':
			list_switch_shuttle_rates();
			break;
          
          case 'mrr_set_miles_timesheet_invoice':
               mrr_set_miles_timesheet_invoice();
               break;
          case 'mrr_get_miles_timesheet_invoice':
               mrr_get_miles_timesheet_invoice();
               break;
          case 'mrr_calc_miles_timesheet_invoice':
               mrr_calc_miles_timesheet_invoice();
               break;
		
		case 'mrr_accident_reports_item_adder':
			mrr_accident_reports_item_adder();
			break;
		case 'mrr_accident_reports_item_lister':
			mrr_accident_reports_item_lister();
			break;
		case 'mrr_accident_reports_item_remover':
			mrr_accident_reports_item_remover();
			break;
		case 'mrr_accident_email_log':
			mrr_accident_email_log();
			break;
		case 'mrr_accident_email_app':
			mrr_accident_email_app();
			break;
		case 'mrr_accident_email_app_send':
			mrr_accident_email_app_send();
			break;
		
		case 'mrr_set_driver_raise_dispatches':
			mrr_set_driver_raise_dispatches();
			break;
		
		case 'mrr_get_avail_driver_summary':
			mrr_get_avail_driver_summary();
			break;
			
          case 'mrr_rate_sheet_upload_mover':
               mrr_rate_sheet_upload_mover();
               break;
               
          case 'mrr_ooic_auto_update_route':
               mrr_ooic_auto_update_route();
               break;
		case 'mrr_ooic_auto_update':
			mrr_ooic_auto_update();
			break;
          case 'mrr_ooic_auto_update_misc':
               mrr_ooic_auto_update_misc();
               break;
		case 'mrr_ooic_auto_emailer':
			mrr_ooic_auto_emailer();
			break;
			
          case 'mrr_update_load_bill_cust_amnt':
               mrr_update_load_bill_cust_amnt();
               break;
               
          case 'mrr_update_inv_part_qty':
               mrr_update_inv_part_qty();
               break;
			
		case 'mrr_payroll_api':
			mrr_payroll_api();
			break;
		
		case 'search_coa_chart':
			search_coa_chart();
			break;
		
          case 'mrr_change_user_pg_usage':
               mrr_change_user_pg_usage();
               break;
                         
          case 'mrr_add_detention_note':
               mrr_add_detention_note();
               break;
          case 'mrr_show_detention_notes':
               mrr_show_detention_notes();
               break;
                    
          case 'mrr_mark_off_internal_task':
               mrr_mark_off_internal_task();
               break;
          case 'mrr_clear_internal_task':
               mrr_clear_internal_task();
               break;	
          case 'mrr_set_internal_task':
               mrr_set_internal_task();
               break;
          
          case 'mrr_show_truck_info_validation':
               mrr_show_truck_info_validation();
               break;
               
          case 'kill_mini_menu_item':
			kill_mini_menu_item();
			break;
	}
		
	function move_truck_day() {
		$sql = "
			update trucks_log
			set linedate = '".sql_friendly($_POST['linedate'])."',
				linedate_updated = now()
				
			where id = '".sql_friendly($_POST['log_id'])."'
			limit 1
		";
		simple_query($sql);
																
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Dispatch ".$_POST['log_id']." Moved. ";
		$mrr_activity_log['dispatch_id']=$_POST['log_id'];
		//......................................................................................................................................................
		
	}
	
	function update_trailer_location() {
		$sql = "
			update trailers
			set current_location = '".sql_friendly($_POST['new_location'])."',
				location_updated = now()
			where id = '".sql_friendly($_POST['trailer_id'])."'
		";
		simple_query($sql);
																
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Trailer ".$_POST['trailer_id']." Location Moved. ";
		$mrr_activity_log['trailer_id']=$_POST['trailer_id'];
		//......................................................................................................................................................
		
	}
	
	function move_note_day() {
		$sql = "
			update notes
			set linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."'
			where id = '".sql_friendly($_POST['note_id'])."'
		";
		simple_query($sql);
																
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Note ".$_POST['note_id']." Day Moved. ";
		//......................................................................................................................................................
		
	}

     function get_truck_notes() 
     {          
	     $html="<table cellspacing='0' cellpadding='0' style='text-align:left' width='550'>
		<tr>
			<td style='width:50px'><b>Day</b></td>
			<td style='width:60px' align='right'><b>Date</b></td>
			<td style='width:50px' align='right'><b>Time</b></td>
			<td>&nbsp;&nbsp;&nbsp;<b>User</b></td>
			<td style='margin-left:20px'><b>Note</b></td>
		</tr>";
	     
	     $sql = "
                    select trucks_log_notes.*,
                        (select username from users where users.id=trucks_log_notes.user_id) as author 
                    from trucks_log_notes
                    where truck_log_id='".sql_friendly($_POST['dispatch_id'])."'
                    order by trucks_log_notes.linedate_added desc
               ";
          $data=simple_query($sql);
          while($row = mysqli_fetch_array($data))
          {
               $html.="<tr>
					<td>".date("D", strtotime($row['linedate_added']))."</td>
					<td align='right'>".date("n-j-y", strtotime($row['linedate_added']))."</td>
					<td align='right'>".date("H:i", strtotime($row['linedate_added']))."</td>
					<td>&nbsp;&nbsp;&nbsp;".$row['author']."</td>
					<td>".$row['note']."</td>
				</tr>"; 
          }
          $html.="</table>";
          
          display_xml_response("<rslt>1</rslt><DispHTML><![CDATA[".$html."]]></DispHTML>");
     }
	function add_truck_note() {
		$sql = "
			insert into trucks_log_notes
				(truck_log_id,
				linedate_added,
				note,
				user_id,
				deleted)
				
			values ('".sql_friendly($_POST['dispatch_id'])."',
				now(),
				'".sql_friendly($_POST['note'])."',
				'".sql_friendly($_SESSION['user_id'])."',
				0)
		";
		simple_query($sql);
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Dispatch ".$_POST['dispatch_id']." Note Added. ";
		$mrr_activity_log['dispatch_id']=$_POST['dispatch_id'];
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	function add_truck_note_mrr_alt() {
		$sql = "
			insert into trucks_log_notes
				(truck_log_id,
				linedate_added,
				note,
				user_id,
				deleted,
				deadline)
				
			values ('".sql_friendly($_POST['dispatch_id'])."',
				now(),
				'".sql_friendly($_POST['note'])."',
				'".sql_friendly($_SESSION['user_id'])."',
				0,
				'".sql_friendly($_SESSION['deadline'])."')
		";
		simple_query($sql);
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Dispatch ".$_POST['dispatch_id']." Note and Deadline Added. ";
		$mrr_activity_log['dispatch_id']=$_POST['dispatch_id'];
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	
	function load_notes() {
		
	     $disp_id=$_POST['log_id'];
	     $load_id=0;
          
          $sql = "
			select load_handler_id
			
			from trucks_log
			where id = '".sql_friendly($disp_id)."'
		";
          $data_load_id = simple_query($sql);
          $row_load_id = mysqli_fetch_array($data_load_id);
                    
          $load_id=$row_load_id['load_handler_id'];
	     
	     $sql = "
			select trucks_log_notes.linedate_added,
				trucks_log_notes.note,
				trucks_log_notes.truck_log_id,
				trucks_log.load_handler_id
			
			from trucks_log_notes
			     left join trucks_log on trucks_log.id=trucks_log_notes.truck_log_id
			where (trucks_log_notes.truck_log_id = '".sql_friendly($disp_id)."')
				and trucks_log_notes.linedate_added >= '".date("Y-m-d", strtotime("-7 day", time()))."'
				and trucks_log_notes.deleted = 0
				
			order by trucks_log_notes.linedate_added desc
			limit 20
		";        // or trucks_log.load_handler_id= '".sql_friendly($load_id)."'
		$data = simple_query($sql);
						
		$sql = "
			select special_instructions			
			from load_handler
			where id = '".$load_id."'
		";
		$data_load = simple_query($sql);
		$row_load = mysqli_fetch_array($data_load);
		
		if($row_load['special_instructions'] != '') {
			echo "
				<div class='mrr_note_style_box mrr_note_style_text'><b>Special Instructions:</b> ".force_line_wrap($row_load['special_instructions'], 50)."</div><p>
			";
		}
		
		if(!mysqli_num_rows($data)) {
			echo "<div class='mrr_note_style_box mrr_note_style_text'>No notes found</div>";
		} else {
			echo "
				<table style='border:0px red solid;width:425px;' class='mrr_note_style_box'>
				<tr>
					<td style='width:50px'><span class='mrr_note_style_text'><b>Day</b></span></td>
					<td style='width:75px' align='right'><span class='mrr_note_style_text'><b>Date</b></span></td>
					<td style='width:50px' align='right'><span class='mrr_note_style_text'><b>Time</b></span></td>
					<td style='width:20px'><span class='mrr_note_style_text'></span></td>
					<td style='margin-left:20px'><span class='mrr_note_style_text'><b>Note</b></span></td>
					
				</tr>
			";        //<td style='margin-left:20px'><span class='mrr_note_style_text'><b>Type</b></span></td>
			while($row_notes = mysqli_fetch_array($data)) {
				
				// force wrap lines, since for some reason, the table won't do it itself (even with a forced width ste				
				$note_holder = force_line_wrap($row_notes['note'], 50);

				// end of code to force a line wrap
				
                    $typer="Dispatch";
                    if($row_notes['truck_log_id']!=$disp_id)        $typer="Load-Wide";
                    
				echo "
					<tr>
						<td><span class='mrr_note_style_text'>".date("D", strtotime($row_notes['linedate_added']))."</span></td>
						<td align='right'><span class='mrr_note_style_text'>".date("n-j-y", strtotime($row_notes['linedate_added']))."</span></td>
						<td align='right'><span class='mrr_note_style_text'>".date("H:i", strtotime($row_notes['linedate_added']))."</span></td>
						<td><span class='mrr_note_style_text'></span></td>
						<td><span class='mrr_note_style_text'>$note_holder</span></td>
						
					</tr>
				";        //<td><span class='mrr_note_style_text'>$typer</span></td>
			}
			echo "
				</table>
			";
		}
	}
     function load_notes_pre() {
          //same as load notes, only this time, there is no dispatch.  Used for preplanned and available loads.
          $load_id=$_POST['pre_load_id'];
          
          $sql = "
                    select *                    
                    from notes_main
                    where notes_main.xref_id = '".sql_friendly($load_id)."'
                         and notes_main.note_type_id='8'
                         and notes_main.deleted = 0                         
                    order by notes_main.linedate_added desc
                    limit 20
               ";        // or trucks_log.load_handler_id= '".sql_friendly($load_id)."'
          $data = simple_query($sql);
          
          $sql = "
                    select special_instructions			
                    from load_handler
                    where id = '".$load_id."'
               ";
          $data_load = simple_query($sql);
          $row_load = mysqli_fetch_array($data_load);          
          if($row_load['special_instructions'] != '') {
               echo "
                         <div class='mrr_note_style_box mrr_note_style_text'><b>Special Instructions:</b> ".force_line_wrap($row_load['special_instructions'], 50)."</div><p>
                    ";
          }
          
          if(!mysqli_num_rows($data)) {
               echo "<div class='mrr_note_style_box mrr_note_style_text'>No notes found</div>";
          } else {
               echo "
                         <table style='border:0px red solid;width:425px;' class='mrr_note_style_box'>
                         <tr>
                              <td style='width:50px'><span class='mrr_note_style_text'><b>Day</b></span></td>
                              <td style='width:75px' align='right'><span class='mrr_note_style_text'><b>Date</b></span></td>
                              <td style='width:50px' align='right'><span class='mrr_note_style_text'><b>Time</b></span></td>
                              <td style='width:20px'><span class='mrr_note_style_text'></span></td>
                              <td style='margin-left:20px'><span class='mrr_note_style_text'><b>Note</b></span></td>
                              
                         </tr>
                    ";        //<td style='margin-left:20px'><span class='mrr_note_style_text'><b>Type</b></span></td>
               while($row_notes = mysqli_fetch_array($data)) {
                    
                    // force wrap lines, since for some reason, the table won't do it itself (even with a forced width set				
                    $note_holder = force_line_wrap($row_notes['note'], 50);
                    
                    // end of code to force a line wrap
                    
                    $typer="Load";
                    
                    echo "
                              <tr>
                                   <td><span class='mrr_note_style_text'>".date("D", strtotime($row_notes['linedate_added']))."</span></td>
                                   <td align='right'><span class='mrr_note_style_text'>".date("n-j-y", strtotime($row_notes['linedate_added']))."</span></td>
                                   <td align='right'><span class='mrr_note_style_text'>".date("H:i", strtotime($row_notes['linedate_added']))."</span></td>
                                   <td><span class='mrr_note_style_text'></span></td>
                                   <td><span class='mrr_note_style_text'>$note_holder</span></td>
                                   
                              </tr>
                         ";        //<td><span class='mrr_note_style_text'>$typer</span></td>
               }
               echo "
                         </table>
                    ";
          }
     }
	function delete_note() {
		$sql = "
			update trucks_log_notes
			set deleted = 1
			where id = '".sql_friendly($_POST['note_id'])."'
		";
		simple_query($sql);
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Truck Note Entry ".$_POST['note_id']." Removed. ";
		//......................................................................................................................................................
		
	}
	function mrr_load_notes() 
	{
		$sql = "
			select trucks_log_notes.linedate_added,
				trucks_log_notes.note,
				users.username
			
			from trucks_log_notes
			     left join users on users.id=trucks_log_notes.user_id
			where trucks_log_notes.truck_log_id = '".sql_friendly($_POST['log_id'])."'
				and trucks_log_notes.linedate_added >= '".date("Y-m-d", strtotime("-7 day", time()))."'
				and trucks_log_notes.deleted = 0
				
			order by trucks_log_notes.linedate_added desc
			limit 20
		";
		$data = simple_query($sql);
		
		$sql = "
			select load_handler_id
			
			from trucks_log
			where id = '".sql_friendly($_POST['log_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);
		
		$sql = "
			select special_instructions
			
			from load_handler
			where id = '".$row_load_id['load_handler_id']."'
		";
		$data_load = simple_query($sql);
		$row_load = mysqli_fetch_array($data_load);
		
		$html="";
		
		if($row_load['special_instructions'] != '') {
			$html.="
				<div class='mrr_note_style_box mrr_note_style_text'><b>Special Instructions:</b> ".force_line_wrap($row_load['special_instructions'], 50)."</div><p>
			";
		}
		
		if(!mysqli_num_rows($data)) {
			$html.="<div class='mrr_note_style_box mrr_note_style_text'>No notes found</div>";
		} else {
			$html.="
				<table style='border:0px red solid;width:500px;' class='mrr_note_style_box'>
				<tr>
					<td style='width:50px'><span class='mrr_note_style_text'><b>Day</b></span></td>
					<td style='width:75px' align='right'><span class='mrr_note_style_text'><b>Date</b></span></td>
					<td style='width:50px' align='right'><span class='mrr_note_style_text'><b>Time</b></span></td>
					<td style='width:75px'><span class='mrr_note_style_text'><b>User</b></span></td>
					<td style='width:20px'><span class='mrr_note_style_text'></td>
					<td style='margin-left:20px'><span class='mrr_note_style_text'><b>Note</b></span></td>
				</tr>
			";
			while($row_notes = mysqli_fetch_array($data)) {
				
				// force wrap lines, since for some reason, the table won't do it itself (even with a forced width ste				
				$note_holder = force_line_wrap($row_notes['note'], 50);

				// end of code to force a line wrap
				
				$html.="
					<tr>
						<td><span class='mrr_note_style_text'>".date("D", strtotime($row_notes['linedate_added']))."</span></td>
						<td align='right'><span class='mrr_note_style_text'>".date("n-j-y", strtotime($row_notes['linedate_added']))."</span></td>
						<td align='right'><span class='mrr_note_style_text'>".date("H:i", strtotime($row_notes['linedate_added']))."</span></td>
						<td><span class='mrr_note_style_text'>".$row_notes['username']."</span></td>
						<td><span class='mrr_note_style_text'></span></td>
						<td><span class='mrr_note_style_text'>
							$note_holder</span>
						</td>
					</tr>
				";
			}
			$html.="
				</table>
			";
		}
		
		$return_var= "
			<rslt>1</rslt>
			<DispHTML><![CDATA[".$html."]]></DispHTML>			
		";
		display_xml_response($return_var);
	}
	function mrr_update_note_entry_access()
	{
		$sql = "
			update notes_main set
				access_level = '".sql_friendly($_POST['access'])."'
			where id = '".sql_friendly($_POST['id'])."'
		";	
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
function display_notes_mr()
{
     $access_level=0;
     if(isset($_SESSION['user_id']))	               $access_level=mrr_get_user_access_level($_SESSION['user_id']);
          
     //if($access_level==99 && $_POST['section_id']==1)  $access_level=100;       //MRR...Added this on 10/28/2020 for Justin so anyone with 99 or higher can see all notes <=100 as well (without updating each note's access level).
          
     $sql = "
			select notes_main.id,
			     notes_main.linedate_added,
			     notes_main.access_level,	     
			     notes_main.note,	
			     (select users.username from users where users.id=notes_main.created_by_user_id) as user_namer,
			     notes_main.note_type_id
			
			from notes_main
			where deleted = 0
				and note_type_id = '".sql_friendly($_POST['section_id'])."'
				and xref_id = '".sql_friendly($_POST['xref_id'])."'
				
				".($access_level < 99 ? "and access_level <= '".sql_friendly($access_level)."'" : "") ."
				
			union 
				
			select mr_unit_locations.id,
		          mr_unit_locations.linedate_added,
		          100,
		          mr_unit_locations.mr_location,
		          (select users.username from users where users.id=mr_unit_locations.user_id) as user_namer,
		          999
		     from mr_unit_locations
		     where mr_unit_locations.deleted=0 
		          and mr_unit_locations.maint_id='".sql_friendly($_POST['xref_id'])."' 
				
			order by linedate_added desc
		";	//".($_POST['section_id']==1 ? "and (xref_id = '".sql_friendly($_POST['xref_id'])."' or xref_id='0')" : "and xref_id = '".sql_friendly($_POST['xref_id'])."'")."
     //".($_POST['section_id']==10  ? "and LOCATE('Maint Request Prompt Noticed by ',note)=0" : "")."
     $data = simple_query($sql);
     
     $mrr_adder="";
     $mrr_adder2="";
     if($_POST['section_id']==1)
     {	//drivers only			
          if($_POST['xref_id'] > 0)
          {
               $mrr_adder="<br><br>...or Add and Send employer email.".show_help('admin_drivers.php','Send Note').
                    " <input type='button' value='Send' onclick=\"add_note_entry_send($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val(),$('#new_note_restriction_level').val())\">";
          }
          $mrr_adder2="Add note, without email. ".show_help('admin_drivers.php','Add Note');
     }
     if($_POST['section_id']==10)
     {	//drivers only			
          if($_POST['xref_id'] > 0)
          {
               $mrr_adder="<br><br>...or Add and Send Dispatch email.".show_help('maint.php','Send Note').
                    " <input type='button' value='Send' onclick=\"add_note_entry_send($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val(),$('#new_note_restriction_level').val())\">";
          }
          $mrr_adder2="Add note, without email. ".show_help('maint.php','Add Note');
     }
     
     //restricted notes...
     $restricted_setting="";
     if($access_level>=50) {
          $restricted_setting="
				<tr>
					<td valign='top' colspan='5'>
						<b>Restriction Level:</b>						
						<input type='text' id='new_note_restriction_level' name='new_note_restriction_level' value='".$access_level."' style='text-align:right; width:50px;'>	
						".show_help('Site Wide','Note Restriction Level')." Ex: 0=Everyone logged in can see this note, <br>
						50=User must have 50+ Access to view, 100=Top Admin Users Only can see this note.
						<br>Your Access Level is ".$access_level.".
					</td>
				</tr>
			";
     }
     else
     {
          $restricted_setting="
				<tr>
					<td colspan='5'>
						<input type='hidden' id='new_note_restriction_level' name='new_note_restriction_level' value='".$access_level."'>
					</td>
				</tr>
			";
     }
     
     
     // add_note_entry js funtion is located in includes/functions.js 
     echo "
			<table width='100%' class='mrr_aligner'>
			<tr>
				<td valign='top'>Add Note ". show_help('Site Wide','Notes')."</td>
				<td valign='top' colspan='5' align='right' nowrap>
					<span id='note_entry_loading' style='display:none'><img src='images/loader.gif'></span>
					".$mrr_adder2." <input type='button' value='Add' onClick=\"add_note_entry($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val(),$('#new_note_restriction_level').val());\">				
					".$mrr_adder."
				</td>
			</tr>
			<tr>
				<td colspan='5'><textarea name='new_note_entry' id='new_note_entry' style='width:98%'></textarea></td>
			</tr>
			".$restricted_setting."
			".($_POST['section_id']==10  ? "<tr><td colspan='6'><center><span style='cursor:pointer; color:#0000CC;' onClick='mrr_toggle_main_request_auto_notes();'><b>Show/Hide Auto-Notes</b></span></center></td></tr>" : "")."
			<tr>
				<td valign='top'><b>Note</b></td>
				<td valign='top' width='25' align='right'><b>Access</b></td>
				<td valign='top' width='40' align='right' title='date note was created.'><b>Date</b></td>
				<td valign='top' width='40' align='right' title='who created the note.'><b>User</b></td>
				<td valign='top' width='".($_POST['section_id']==1 ? "40" : "5")."' align='right' title='Expires 6 months from the creation date.'><b>".($_POST['section_id']==1 ? "Expires" : "&nbsp;")."</b></td>
				<td valign='top' width='20'></td>
			</tr>
		";
     if(!mysqli_num_rows($data))
     {
          echo "
				<tr>
					<td colspan='6'><i>No Notes</i></td>
				</tr>				
			";	//<tr><td colspan='6'>".$sql."</td></tr> Access Level is ".$access_level."
     }
     
     $nower=date("Ymd", time());
     
     while($row = mysqli_fetch_array($data))
     {
          $expires_date=date("m/d/Y", strtotime("+6 months",strtotime($row['linedate_added'])));
          
          $expires_date2=date("YMD", strtotime("+6 months",strtotime($row['linedate_added'])));
          
          $access_editor="".$row['access_level']."";
          if($access_level >=$row['access_level'])
          {	// && $_POST['section_id']==1
               $access_editor="<input type='text' name='note_entry_".$row['id']."_access' id='note_entry_".$row['id']."_access' value='".(int) $row['access_level']."' style='width:30px; color:#0000cc; text-align:right;' onBlur='mrr_update_note_access(".$row['id'].");'>";
          }
          
          echo "
				<tr id='note_entry_$row[id]' ".($_POST['section_id']==10 && substr_count($row['note'],"Maint Request Prompt Noticed by ") > 0  ? " class='auto_notes'" : "").">
					<td valign='top'>$row[note]</td>
					<td valign='top' align='right'>".($row['note_type_id']==999 ? "100" : "".$access_editor."")."</td>
					<td valign='top' align='right'>".date("m/d/Y", strtotime($row['linedate_added']))."</td>
					<td valign='top' align='right'>".trim($row['user_namer'])."</td>
					<td valign='top' align='right'>".($_POST['section_id']==1 ? "<span class='".($expires_date2 < $nower ? "mrr_alert" : "mrr_good_alert")."'>".$expires_date."</span>" : "&nbsp;")."</td>
					<td valign='top' nowrap>
					   &nbsp;&nbsp;&nbsp;&nbsp;
					   ".($row['note_type_id']==999 ? "MR-Log" : "<a href='javascript:delete_note_entry($row[id])'><img src='images/delete_sm.gif' alt='Delete Note' title='Delete Note' border='0'></a>")."					   
					</td>
				</tr>
			";	//".($_POST['section_id']==1 && $_POST['xref_id'] > 0 && $row['xref_id']==0 ? "<b>All</b>" : "<a href='javascript:delete_note_entry($row[id])'><img src='images/delete_sm.gif' alt='Delete Note' title='Delete Note' border='0'></a>")."
     }
     
     echo "</table>";
     if($_POST['section_id']==10)
     {
          echo "
			<script type='text/javascript'>
				mrr_toggle_main_request_auto_notes();
			</script>
			";
     }
}

	function display_notes() 
	{		
		$access_level=0;
		if(isset($_SESSION['user_id']))	$access_level=mrr_get_user_access_level($_SESSION['user_id']);	
		
		$sql = "
			select *
			
			from notes_main
			where deleted = 0
				and note_type_id = '".sql_friendly($_POST['section_id'])."'
				and xref_id = '".sql_friendly($_POST['xref_id'])."'
				
				".($access_level < 99 ? "and access_level <= '".sql_friendly($access_level)."'" : "")."
				
			order by linedate_added desc
		";	//".($_POST['section_id']==1 ? "and (xref_id = '".sql_friendly($_POST['xref_id'])."' or xref_id='0')" : "and xref_id = '".sql_friendly($_POST['xref_id'])."'")."
			//".($_POST['section_id']==10  ? "and LOCATE('Maint Request Prompt Noticed by ',note)=0" : "")."
		$data = simple_query($sql);
		
		$mrr_adder="";
		$mrr_adder2="";
		if($_POST['section_id']==1)
		{	//drivers only			
			if($_POST['xref_id'] > 0)
			{			
				$mrr_adder="<br><br>...or Add and Send employer email.".show_help('admin_drivers.php','Send Note').
							" <input type='button' value='Send' onclick=\"add_note_entry_send($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val(),$('#new_note_restriction_level').val())\">";
			}
			$mrr_adder2="Add note, without email. ".show_help('admin_drivers.php','Add Note');
		}
		if($_POST['section_id']==10)
		{	//drivers only			
			if($_POST['xref_id'] > 0)
			{			
				$mrr_adder="<br><br>...or Add and Send Dispatch email.".show_help('maint.php','Send Note').
							" <input type='button' value='Send' onclick=\"add_note_entry_send($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val(),$('#new_note_restriction_level').val())\">";
			}
			$mrr_adder2="Add note, without email. ".show_help('maint.php','Add Note');
		}
		
		//restricted notes...
		$restricted_setting="";
		if($access_level>=50) {
			$restricted_setting="
				<tr>
					<td valign='top' colspan='5'>
						<b>Restriction Level:</b>						
						<input type='text' id='new_note_restriction_level' name='new_note_restriction_level' value='".$access_level."' style='text-align:right; width:50px;'>	
						".show_help('Site Wide','Note Restriction Level')." Ex: 0=Everyone logged in can see this note, <br>
						50=User must have 50+ Access to view, 100=Top Admin Users Only can see this note.
						<br>
						Your have access level ".$access_level." here...
					</td>
				</tr>
			";
		}
		else
		{
			$restricted_setting="
				<tr>
					<td colspan='5'>
						<input type='hidden' id='new_note_restriction_level' name='new_note_restriction_level' value='".$access_level."'>
					</td>
				</tr>
			";
		}
		
		
		// add_note_entry js funtion is located in includes/functions.js 
		echo "
			<table width='100%' class='mrr_aligner'>
			<tr>
				<td valign='top'>Add Note ". show_help('Site Wide','Notes')."</td>
				<td valign='top' colspan='4' align='right' nowrap>
					<span id='note_entry_loading' style='display:none'><img src='images/loader.gif'></span>
					".$mrr_adder2." <input type='button' value='Add' onClick=\"add_note_entry($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val(),$('#new_note_restriction_level').val());\">				
					".$mrr_adder."
				</td>
			</tr>
			<tr>
				<td colspan='5'><textarea name='new_note_entry' id='new_note_entry' style='width:98%'></textarea></td>
			</tr>
			".$restricted_setting."
			".($_POST['section_id']==10  ? "<tr><td colspan='5'><center><span style='cursor:pointer; color:#0000CC;' onClick='mrr_toggle_main_request_auto_notes();'><b>Show/Hide Auto-Notes</b></span></center></td></tr>" : "")."
			<tr>
				<td valign='top'><b>Note</b></td>
				<td valign='top' width='25' align='right'><b>Access</b></td>
				<td valign='top' width='40' align='right' title='date note was created.'><b>Date</b></td>
				<td valign='top' width='".($_POST['section_id']==1 ? "40" : "5")."' align='right' title='Expires 6 months from the creation date.'><b>".($_POST['section_id']==1 ? "Expires" : "&nbsp;")."</b></td>
				<td valign='top' width='20'></td>
			</tr>
		";
		if(!mysqli_num_rows($data)) 
		{
			echo "
				<tr>
					<td colspan='5'><i>No Notes</i></td>
				</tr>				
			";	//<tr><td colspan='5'>".$sql."</td></tr> Access Level is ".$access_level."
		}
		
		$nower=date("Ymd", time());
		
		while($row = mysqli_fetch_array($data)) 
		{	
			$expires_date=date("m/d/Y", strtotime("+6 months",strtotime($row['linedate_added'])));
			
			$expires_date2=date("YMD", strtotime("+6 months",strtotime($row['linedate_added'])));
			
			$access_editor="".$row['access_level']."";
			if($access_level >=$row['access_level'])	
			{	// && $_POST['section_id']==1
				$access_editor="<input type='text' name='note_entry_".$row['id']."_access' id='note_entry_".$row['id']."_access' value='".(int) $row['access_level']."' style='width:30px; color:#0000cc; text-align:right;' onBlur='mrr_update_note_access(".$row['id'].");'>";
			}
			
			echo "
				<tr id='note_entry_$row[id]' ".($_POST['section_id']==10 && substr_count($row['note'],"Maint Request Prompt Noticed by ") > 0  ? " class='auto_notes'" : "").">
					<td valign='top'>$row[note]</td>
					<td valign='top' align='right'>".$access_editor."</td>
					<td valign='top' align='right'>".date("m/d/Y", strtotime($row['linedate_added']))."</td>
					<td valign='top' align='right'>".($_POST['section_id']==1 ? "<span class='".($expires_date2 < $nower ? "mrr_alert" : "mrr_good_alert")."'>".$expires_date."</span>" : "&nbsp;")."</td>
					<td valign='top' nowrap>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:delete_note_entry($row[id])'><img src='images/delete_sm.gif' alt='Delete Note' title='Delete Note' border='0'></a></td>
				</tr>
			";	//".($_POST['section_id']==1 && $_POST['xref_id'] > 0 && $row['xref_id']==0 ? "<b>All</b>" : "<a href='javascript:delete_note_entry($row[id])'><img src='images/delete_sm.gif' alt='Delete Note' title='Delete Note' border='0'></a>")."
		}	
		
		echo "</table>";
		if($_POST['section_id']==10)
		{
			echo "
			<script type='text/javascript'>
				mrr_toggle_main_request_auto_notes();
			</script>
			";	
		}
	}
	
	function mrr_send_driver_message_out()
	{	
		global $defaultsarray;
		$sent_name=$defaultsarray['company_name'];
		$sent_email=$defaultsarray['company_email_address'];
		
		$driver_id=(int) $_POST['driver_id'];
		$sub=trim($_POST['subject']);
		$msg=trim($_POST['message']);
		
		$cc="";
     	$bcc="";
     	
		$subject="".strip_tags($sub)."";
     			
     	$txt="".strip_tags(trim($msg))."";
     	$message="<b>".$sub."</b><br><br>".trim($msg)."";		
				
		
		$dnum=0;
		$dids[0]=0;
		$dfnm[0]="";
		$dlnm[0]="";
		$deml[0]="";
		$get_id=0;
		if($driver_id > 0) 
		{ 
			$sqld = "
				select id, name_driver_first, name_driver_last, driver_email				
				from drivers
				where id = '".sql_friendly($driver_id)."'
			";
			$datad = simple_query($sqld);
			if($rowd = mysqli_fetch_array($datad))
			{
				$dids[$dnum]=$rowd['id'];
				$dfnm[$dnum]=trim($rowd['name_driver_first']);
				$dlnm[$dnum]=trim($rowd['name_driver_last']);
				$deml[$dnum]=trim($rowd['driver_email']);
				$dnum++;
			}
		}
		else
		{
			$sqld = "
				select id, name_driver_first, name_driver_last, driver_email				
				from drivers
				where deleted=0 
					and active>0
					and driver_email!=''
				order by name_driver_last asc, name_driver_first asc, driver_email asc
			";
			$datad = simple_query($sqld);
			while($rowd = mysqli_fetch_array($datad))
			{
				$dids[$dnum]=$rowd['id'];
				$dfnm[$dnum]=trim($rowd['name_driver_first']);
				$dlnm[$dnum]=trim($rowd['name_driver_last']);
				$deml[$dnum]=trim($rowd['driver_email']);
				$dnum++;
			}
		}
		
		$dlister="";
		for($d=0;$d < $dnum; $d++)
		{
			$disp_name=strip_tags(trim( $dfnm[$d]." ".$dlnm[$d] ));
			$email=trim($deml[$d]);
			
			//$email="jgriffith@conardtransportation.com";			$disp_name="James";
			//$email=$defaultsarray['special_email_monitor'];			$disp_name="Richard (Test Driver) Hed";
			if($email!="")
			{
				$dlister.="".$disp_name." [<b>".$email."</b>]<br>";
				mrr_trucking_sendMail($email,$disp_name,$sent_email,$sent_name,$cc,$bcc,$subject,$txt,$message);
				
				$sqlu="
					insert into driver_email_log
						(id,
						linedate_added,
						user_id,
						driver_id,
						subject,
						sent_list,
						body,
						deleted)
					values 
						(NULL,
						NOW(),
						'".sql_friendly($_SESSION['user_id'])."',
						'".sql_friendly($dids[$d])."',
						'".sql_friendly($subject)."',
						'".sql_friendly("".$disp_name." [<b>".$email."</b>]")."',
						'".sql_friendly($message)."',
						0)
				";
				simple_query($sqlu);
			}
		}
		if($driver_id==0 && $dlister!="")
		{
			$sqlu="
					insert into driver_email_log
						(id,
						linedate_added,
						user_id,
						driver_id,
						subject,
						sent_list,
						body,
						deleted)
					values 
						(NULL,
						NOW(),
						'".sql_friendly($_SESSION['user_id'])."',
						'".sql_friendly(0)."',
						'".sql_friendly($subject)."',
						'".sql_friendly("All Active Drivers")."',
						'".sql_friendly($message)."',
						0)
				";
			simple_query($sqlu);
		}
			
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$dlister."]]></Disp>";		
		display_xml_response($return_val);	
	}
	
	
	function display_attachments() 
	{
		global $defaultsarray;
		
		$user_id=0;
		$use_admin_level=0;
		if(isset($_SESSION['user_id']))	
		{
			$use_admin_level=mrr_get_user_access_level($_SESSION['user_id']);
			$user_id=$_SESSION['user_id'];
		}
		$sql = "
			select *
			
			from attachments
			where deleted = 0
				and section_id = '".sql_friendly($_POST['section_id'])."'
				and xref_id = '".sql_friendly($_POST['xref_id'])."'
			order by cat_id asc,linedate_added desc
		";
		$data = simple_query($sql);
		
		echo "
			<table width='100%'>
			<tr>
				<td><b>Filename</b> ". show_help('Site Wide','Attachments')."</td>
				<td align='right'><b>Date Uploaded</b></td>
				<td></td>
			</tr>
		";
		
		$last_cat_id=0;
		if($_POST['section_id']==1)
		{
			echo "<tr><td colspan='3'><b>DOT Files</b></td></tr>";	
		}
		
		while($row = mysqli_fetch_array($data)) 
		{
			if(($use_admin_level>=99 && $row['cat_id']>=0) || $row['cat_id']==0)
			{     			
     			$valid=0;
     			if($row['cat_id']==0)			$valid=1;
     			if($row['cat_id']==1)			$valid=1;
     			if($row['cat_id'] > 1)
     			{	// && ($user_id==52 || $user_id==15 || $user_id==18 || $user_id==23 || $user_id==73 || $user_id==92 || $user_id==51 || $user_id==81 || $user_id==19)
     				$valid=1;		//Only allow Megan, Dale, James, Katie, Ashley W.,Patti, Rusty G., Justin G., or Sherrod access to these attachements.
     			}     			     			
     			     			
     			if($_POST['section_id']==1  && $row['cat_id']==1 && $last_cat_id==0)
     			{
     				echo "<tr><td colspan='3'><b>Personnel Files</b></td></tr>";	
     			}     						
     			if($_POST['section_id']==1  && $row['cat_id']==2 && $last_cat_id<=1 && $valid==1)
     			{
     				echo "<tr><td colspan='3'><b>Payroll Files</b></td></tr>";	
     			}
     			if($_POST['section_id']==1  && $row['cat_id']==3 && $last_cat_id<=2 && $valid==1)
     			{
     				echo "<tr><td colspan='3'><b>Insurance Files</b></td></tr>";	
     			}     			
     			if($_POST['section_id']==1  && $row['cat_id']==4 && $last_cat_id <=3 && $valid==1)
     			{
     				echo "<tr><td colspan='3'><b>HOS (Hours of Service)</b></td></tr>";	
     			}
     			
     			$public_name=$row['fname'];
     			if(trim($row['public_name'])!="")		$public_name=trim($row['public_name']);
     			
     			$use_path="$defaultsarray[document_upload_dir]/$row[fname]";
				
				$use_path=rawurlencode($use_path);

     			if(!isset($row['descriptor']) && $row['cat_id']==99)	$use_path="/scanner_upload/problem/$row[fname]";
     			
     			
     			if($valid==1)
     			{
          			$mrr_use_date="";
          			if($row['linedate_start']=='0000-00-00 00:00:00')		$row['linedate_start']=$row['linedate_added'];	
          			
          			if($row['linedate_start']!='0000-00-00 00:00:00')		$mrr_use_date=date("m/d/Y", strtotime("+6 month",strtotime($row['linedate_start'])));          			
          			
          			echo "
          				<tr id='attachment_row_$row[id]'>
          					<td>
          						<span class='mrr_link_like_on' onClick='mrr_rename_attachment(".$_POST['section_id'].",".$_POST['xref_id'].",".$row['id'].",\"".$public_name."\",".$row['cat_id'].",\"".date("m/d/Y", strtotime($row['linedate_start']))."\");'>Rename</span> 
          						<a href=\"".$use_path."\" target='blank_$row[id]'>".$public_name."</a>
          						".($row['cat_id']==4 && $row['linedate_start']!='0000-00-00 00:00:00' ? "<span class='mrr_good_alert'>Exp: ".date("m/d/Y", strtotime("+6 month",strtotime($row['linedate_start'])))." starting ".date("m/d/Y", strtotime($row['linedate_start']))."</span>" : "")."
          						".($row['cat_id']==4 && $row['linedate_start']=='0000-00-00 00:00:00' ? "<span class='mrr_alert'>Error: No Start Date!</span>" : "")."
          					</td>
          					<td align='right'>".date("m/d/Y", strtotime($row['linedate_added']))."</td>
          					<td>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:delete_attachment($row[id])'><img src='images/delete_sm.gif' alt='Delete Attachment' title='Delete Attachment' border='0'></a></td>
          				</tr>
          			";
          			$last_cat_id=$row['cat_id'];
     			}     			
			}	
		}
		echo "</table>";	//<p>QUERY: ".$sql."</p>
	}
	function display_attachments_alt() {
		global $defaultsarray;
		
		$use_admin_level=0;
		if(isset($_SESSION['user_id']))		$use_admin_level=mrr_get_user_access_level($_SESSION['user_id']);
		
		$tab="";
		
		$sql = "
			select *
			
			from attachments
			where deleted = 0
				and section_id = '".sql_friendly($_POST['section_id'])."'
				and xref_id = '".sql_friendly($_POST['xref_id'])."'
			order by cat_id asc, linedate_added desc
		";
		$data = simple_query($sql);
		
		$tab.="
			<table width='100%'>
			<tr>
				<td><b>Filename</b> ". show_help('Site Wide','Attachments')."</td>
				<td align='right'><b>Date Uploaded</b></td>
				<td></td>
			</tr>
		";
		
		$last_cat_id=0;
		if($_POST['section_id']==1)
		{
			$tab.="<tr><td colspan='3'><b>DOT Files</b></td></tr>";	
		}
		
		while($row = mysqli_fetch_array($data)) 
		{			
			if(($use_admin_level>=95 && $row['cat_id']>=0) || $row['cat_id']==0)
			{
     			if($_POST['section_id']==1  && $row['cat_id']==1 && $last_cat_id==0)
     			{
     				$tab.="<tr><td colspan='3'><b>Personnel Files</b></td></tr>";	
     			}
     			
     			if($_POST['section_id']==1  && $row['cat_id']==2 && $last_cat_id < 2)
     			{
     				$tab.="<tr><td colspan='3'><b>Payroll</b></td></tr>";	
     			}
     			if($_POST['section_id']==1  && $row['cat_id']==3 && $last_cat_id < 3)
     			{
     				$tab.="<tr><td colspan='3'><b>Insurance</b></td></tr>";	
     			}
     			if($_POST['section_id']==1  && $row['cat_id']==4 && $last_cat_id < 4)
     			{
     				$tab.="<tr><td colspan='3'><b>HOS (Hours of Service)</b></td></tr>";	
     			}
     			
     						
     			$public_name=$row['fname'];
     			if(trim($row['public_name'])!="")		$public_name=trim($row['public_name']);
     			
     			$mrr_use_date="";
     			if($row['linedate_start']=='0000-00-00 00:00:00')		$row['linedate_start']=$row['linedate_added'];	
     			
          		if($row['linedate_start']!='0000-00-00 00:00:00')		$mrr_use_date=date("m/d/Y", strtotime("+6 month",strtotime($row['linedate_start'])));              		
          			
     			$tab.="
     				<tr id='attachment_row_$row[id]'>
     					<td>
     						<span class='mrr_link_like_on' onClick='mrr_rename_attachment(".$_POST['section_id'].",".$_POST['xref_id'].",".$row['id'].",\"".$public_name."\",".$row['cat_id'].",\"".date("m/d/Y", strtotime($row['linedate_start']))."\");'>Rename</span> 
     						<a href=\"$defaultsarray[document_upload_dir]/$row[fname]\" target='blank_$row[id]'>".$public_name."</a>
     						".($row['cat_id']==4 && $row['linedate_start']!='0000-00-00 00:00:00' ? "<span class='mrr_good_alert'>Exp: ".date("m/d/Y", strtotime("+6 month",strtotime($row['linedate_start'])))." starting ".date("m/d/Y", strtotime($row['linedate_start']))."</span>" : "")."
     						".($row['cat_id']==4 && $row['linedate_start']=='0000-00-00 00:00:00' ? "<span class='mrr_alert'>Error: No Start Date!</span>" : "")."
     					</td>
     					<td align='right'>".date("m/d/Y", strtotime($row['linedate_added']))."</td>
     					<td>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:delete_attachment($row[id])'><img src='images/delete_sm.gif' alt='Delete Attachment' title='Delete Attachment' border='0'></a></td>
     				</tr>
     			";
     			
     			$last_cat_id=$row['cat_id'];
			}
		}
		$tab.="</table>";
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$tab."]]></mrrTab>");
	}
	
	
	function delete_attachment() 
	{
		$sql = "
			update attachments
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
			limit 1
		";
		simple_query($sql);		
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Attachment ".$_POST['id']." Removed. ";
		//......................................................................................................................................................		
	}
	
	function update_file_attachment_name()
	{
		if(!isset($_POST['new_cat_id']))		$_POST['new_cat_id']=0;
		
		$date_added="linedate_start='0000-00-00 00:00:00',";
		if(trim($_POST['new_date'])!="")		$date_added="linedate_start='".date("Y-m-d",strtotime(trim($_POST['new_date'])))." 00:00:00',";
		
		$sql = "
			update attachments set
				cat_id='".sql_friendly($_POST['new_cat_id'])."',
				".$date_added."
				public_name='".sql_friendly($_POST['new_name'])."'
			where id = '".sql_friendly($_POST['id'])."'
		";
		simple_query($sql);		
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Attachment ".$_POST['id']." Renamed. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");	
	}
	
	function set_calendar_display_mode() {
		if($_POST['full_view'] == '1') {
			$_SESSION['full_calendar_view_flag'] = true;
			echo "full view";
		} else {
			$_SESSION['full_calendar_view_flag'] = false;
			echo "small view";
		}
	}
	
	function load_available_loads() {
		
		
		// get the available loads
		$sql = "
			select load_handler.*,
				customers.name_company,
				concat(drivers.name_driver_last, ', ', drivers.name_driver_first) as driver_name
			
			from load_handler
				left join customers on load_handler.customer_id = customers.id
				left join drivers on drivers.id = load_handler.preplan_driver_id
			where load_handler.deleted = 0
				and (load_available = 1
					or (select count(*) from trucks_log where load_handler_id = load_handler.id and trucks_log.deleted = 0) = 0)				
			order by origin_state, origin_city, dest_state, dest_city
				
		";
		$data = simple_query($sql);
		
		
		$return_var = "<rslt>".mysqli_num_rows($data)."</rslt>";
		
		$disphtml = "
			<table>
			<tr>
				<td nowrap><b>Load ID</b> ". show_help('Site Wide','Available Loads')."</td>
				<td><b>Customer</b></td>
				<td><b>Origin</b></td>
				<td><b>Destination</b></td>
				<td nowrap><b>Preplan Flag</b></td>
			</tr>
		";
		while($row = mysqli_fetch_array($data)) {
			$disphtml .= "
				<tr style='font-size:10px'>
					<td><a href='manage_load.php?load_id=$row[id]'>$row[id]</a></td>
					<td nowrap>$row[name_company]</td>
					<td nowrap>$row[origin_city]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
					<td nowrap>$row[dest_city]".($row['dest_state'] != '' ? ', '.$row['dest_state'] : '')."</td>
					<td nowrap>".($row['preplan'] ? $row['driver_name'] : "")."</td>
				</tr>
			";
		}
		$disphtml .= "</table><div style='clear:both'></div>";
		
		
		$return_var .= "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			
		";
		display_xml_response($return_var);
		
	}

	function load_driver_history() {
		
		// get the driver info
		$sql = "
			select *			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data_driver = simple_query($sql);
		$row_driver = mysqli_fetch_array($data_driver);
		
		$tm_flag=0;		if(isset($_POST['tm_driver_flag']))		$tm_flag=$_POST['tm_driver_flag'];
		
		
		// get the loads this driver ran
		$sql = "
			select customers.name_company,
				trailers.trailer_name,
				trucks.name_truck,
				trucks_log.id,
				trucks_log.linedate,
				(
					select load_handler_stops.linedate_completed
					from load_handler_stops 
					where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
					order by load_handler_stops.linedate_completed desc 
					limit 1
				) as pickup_eta,
				(
					select (CASE WHEN load_handler_stops.linedate_completed IS NULL THEN 0 ELSE 1 END) 
					from load_handler_stops 
					where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
					order by load_handler_stops.linedate_completed desc 
					limit 1
				) as pickup_eta_is_null,
				trucks_log.origin,
				trucks_log.origin_state,
				trucks_log.destination,
				trucks_log.destination_state,
				trucks_log.profit
			
			from trucks_log
				left join customers on trucks_log.customer_id = customers.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
			where trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'
				and linedate > '".date("Y-m-d", strtotime("-14 day", time()))."'
				and trucks_log.deleted<=0
			order by linedate desc, pickup_eta_is_null asc, pickup_eta desc
		";
		$data = simple_query($sql);
		
		
		$return_var = "<rslt>".mysqli_num_rows($data)."</rslt>";
		
		$mrr_emp_name=mrr_get_employer_by_id($row_driver['employer_id']);
		
		//added April 2014...
		$date_now=date("m/d/Y");
		$dweek=date("w");
		$ddiff=6 - $dweek;
				
		$date_from=date("m/d/Y",strtotime("-".$ddiff." day",strtotime($date_now)));
		$date_to=date("m/d/Y",strtotime("+6 day",strtotime($date_from)));
		$mres=mrr_get_driver_miles_per_period($_POST['driver_id'],$date_from,$date_to);
		$mres2=mrr_get_driver_miles_per_period_preplan($_POST['driver_id'],$date_from,$date_to);
		$mileshtml="
			<table cellpadding='0' cellspacing='0' border='0'>
			<tr>
				<td valign='top' align='left' colspan='3'>Summary for ".$date_from." thru ".$date_to."</td>
			</tr>
			<tr>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>Miles</td>
				<td valign='top' align='right'>Hours</td>
			</tr>
			<tr>
				<td valign='top' align='right'>Dispatched</td>
				<td valign='top' align='right'>".number_format(($mres['miles'] + $mres['miles_deadhead']),2)."</td>
				<td valign='top' align='right'>".number_format($mres['hours'],2)."</td>
			</tr>
			<tr>
				<td valign='top' align='right'>Preplanned</td>
				<td valign='top' align='right'>".number_format($mres2['miles'],2)."</td>
				<td valign='top' align='right'>".number_format($mres2['hours'],2)."</td>
			</tr>
			<tr>
				<td valign='top' align='right'>Total</td>
				<td valign='top' align='right'>".number_format(($mres['miles'] + $mres['miles_deadhead'] + $mres2['miles']),2)."</td>
				<td valign='top' align='right'>".number_format(($mres['hours'] + $mres2['hours']),2)."</td>
			</tr>
			</table>
		";
		//...................
		
		$disphtml = "
			<table>
			<tr>
				<td><b>Driver:</b> ". show_help('Site Wide','Driver History')."</td>
				<td colspan='7'>$row_driver[name_driver_first] $row_driver[name_driver_last]</td>
			</tr>
			<tr>
				<td><b>Employer:</b></td>
				<td colspan='7'>$mrr_emp_name</td>
			</tr>
			<tr>
				<td><b>Cell Phone:</b></td>
				<td colspan='7'>$row_driver[phone_cell]</td>
			</tr>
			<tr>
				<td colspan='8'><hr></td>
			</tr>
			<tr>
				<td><b>DispID</b></td>
				<td><b>Truck</b></td>
				<td><b>Trailer</b></td>
				<td><b>Date</b></td>
				<td><b>Completed</b></td>
				<td><b>Origin</b></td>
				<td><b>Destination</b></td>
				<td align='right'><b>Profit</b></td>
			</tr>
		";
		while($row = mysqli_fetch_array($data)) 
          {
			$stop_dater="".date("m/d/y H:i", strtotime($row['pickup_eta']))."";
     
               if(!isset($row['pickup_eta']) || $row['pickup_eta']=="0000-00-00 00:00:00")    $stop_dater="N/A";
			
               $disphtml .= "
				<tr style='font-size:10px'>
					<td nowrap>$row[id]</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>$row[trailer_name]</td>
					<td nowrap>".date("M, j", strtotime($row['linedate']))."</td>
					<td nowrap><span style='color:purple;'>".$stop_dater."</span></td>
					<td nowrap>$row[origin]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
					<td nowrap>$row[destination]".($row['destination_state'] != '' ? ', '.$row['destination_state'] : '')."</td>
					<td align='right'>$".money_format('', $row['profit'])."</td>
				</tr>
			";
		}
		
		$disphtml .= "
			<tr>
				<td colspan='8'><hr></td>
			</tr>
			<tr>
				<td colspan='8'>".$mileshtml."</td>
			</tr>
		";
		
		$disphtml .= "</table>";
		
		
		$return_var .= "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			<PayPerMile>$row_driver[charged_per_mile]</PayPerMile>
			<PayPerMileTeam>$row_driver[charged_per_mile_team]</PayPerMileTeam>
			<PayPerHour>$row_driver[charged_per_hour]</PayPerHour>
			<PayPerHourTeam>$row_driver[charged_per_hour_team]</PayPerHourTeam>
			<OwnerOperator>$row_driver[owner_operator]</OwnerOperator>
		";
		display_xml_response($return_var);
		
	}
	function load_truck_history() {
		
		global $defaultsarray;
		$disphtml = "";
		$lockdown=0;
		
		// get the truck info
		$sql = "
			select *
			
			from trucks
			where id = '".sql_friendly($_POST['truck_id'])."'
		";
		$data_truck = simple_query($sql);
		$row_truck = mysqli_fetch_array($data_truck);
		
		$lockdown=$row_truck['maint_req_lock'];
		
		// get the loads this truck ran
		$sql = "
			select customers.name_company,
				trailers.trailer_name,
				trucks.name_truck,
				trucks_log.linedate_pickup_eta,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trucks_log.origin,
				trucks_log.origin_state,
				trucks_log.destination,
				trucks_log.destination_state,
				trucks_log.profit,
				trucks_log.daily_run_otr,
				trucks_log.hours_worked,
				trucks_log.id,
				trucks_log.truck_id
			
			from trucks_log
				left join customers on trucks_log.customer_id = customers.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join drivers on drivers.id = trucks_log.driver_id
			where trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'
				and linedate_pickup_eta >= '".date("Y-m-d", strtotime("-45 day", time()))."'
				and trucks_log.deleted = 0
			order by linedate_pickup_eta
		";
		$data = simple_query($sql);
		
		// get the profit for past x day ranges
		
		$last_week_start = strtotime("-".(date("w") + 7)." day", time());
		$last_week_end = strtotime("-".(date("w"))." day", time());
		$last_month = strtotime("-1 month", time());
		
		// find the first sunday of the month
		$i=0;
		$sdate = strtotime(date("m/1/Y"));
		for($i=0;$i<7;$i++) {
			if(date("w", strtotime("$i day", $sdate)) == '0') break;
		}
		
		$date_week0 = strtotime(date("m/1/Y", time()));
		$date_week1 = strtotime("$i day", $sdate);
		$date_week2 = strtotime("7 day", $date_week1);
		$date_week3 = strtotime("7 day", $date_week2);
		$date_week4 = strtotime("7 day", $date_week3);
		$date_week5 = strtotime("7 day", $date_week4);
		$date_week6 = strtotime("7 day", $date_week5);
		
		//$disphtml .= date("m/d/Y", $date_week0)." | " .date("m/d/Y", $date_week1)."<br>";
		
		/*
						(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta > '".date("Y-m-d", strtotime("-7 day", time()))."') as profit7,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta > '".date("Y-m-d", strtotime("-14 day", time()))."') as profit14,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta > '".date("Y-m-d", strtotime("-30 day", time()))."') as profit30,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-01")."' and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", time()))."') as profit_mtd,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-01", $last_month)."' and linedate_pickup_eta < '".date("Y-m-01")."') as profit_last_month,
				
			<!---
			<tr>
				<td colspan='3'><b>Profit Month to Date</b></td>
				<td>$".money_format('', $row_profit['profit_mtd'])."</span></td>
			</tr>
			<tr>
				<td colspan='3'><b>Profit Last Month</b></td>
				<td>$".money_format('', $row_profit['profit_last_month'])."</span></td>
			</tr>
			--->
		*/
		$sql = "
			select 
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", strtotime("-".date("w")." day", time()))."') as profit_wtd,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $last_week_start)."' and linedate_pickup_eta <= '".date("Y-m-d", $last_week_end)."') as profit_last_week,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week0)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week1)."') as profit_week0,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week1)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week2)."') as profit_week1,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week2)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week3)."') as profit_week2,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week3)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week4)."') as profit_week3,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week4)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week5)."') as profit_week4,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week5)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week6)."') as profit_week5
		";
		
		//echo $sql;
		
		$data_profit = simple_query($sql);
		$row_profit = mysqli_fetch_array($data_profit);

		$return_var = "<rslt>".mysqli_num_rows($data)."</rslt>";
		
		$daily_cost = get_daily_cost($_POST['truck_id']);
		
		$disphtml .= "
			<table style='width:100%'>
			<tr>
				<td colspan='5'><b>Truck: $row_truck[name_truck]</b> ". show_help('Site Wide','Truck History')."</td>
			</tr>
			<tr>
				<td></td>
				<td align='right'><b>Profit</b></td>
				<td align='right' nowrap><b>&nbsp;&nbsp; Days Run</b></td>
				<td align='right'><b>Days Avail</b></td>
				<td align='center'><b>Variance</b></td>
				<td align='right' nowrap><b>Est Profit</b></td>
			</tr>
		";
		
		for($i=0;$i<6;$i++) 
		{
			$date_week_start = ${'date_week'.$i};
			$date_week_end = strtotime("6 day", $date_week_start);
						
			$datemaska0=date("Y-m-d H:i:s",$date_week0);
			$datemaska1=date("Y-m-d H:i:s",$date_week1);
			$datemaska2=date("Y-m-d H:i:s",$date_week2);
			$datemaska3=date("Y-m-d H:i:s",$date_week3);
			$datemaska4=date("Y-m-d H:i:s",$date_week4);
			$datemaska5=date("Y-m-d H:i:s",$date_week5);
			$datemaska6=date("Y-m-d H:i:s",$date_week6);
						
			$vardater1="datemaska".$i."";
			$vardater2="datemaska".($i+1)."";
			
			//new date range for available days and runs
			$vardater1a="date_week".$i."";
			$vardater2a="date_week".($i+1)."";
			
			$days_available_array = get_days_available($$vardater1a, $$vardater2a, $_POST['truck_id']);
			$days_actual = get_days_run($$vardater1a,$$vardater2a , $_POST['truck_id']);		// strtotime("-1 day", $$vardater2a)    $$vardater2a     
			$mrr_actual = mrr_get_days_run($$vardater1a,$$vardater2a , $_POST['truck_id']);		//strtotime("-1 day", $$vardater2a)      $$vardater2a     
			
			//$days_available_array = get_days_available($date_week_start, $date_week_end, $_POST['truck_id']);
			//$days_actual = get_days_run($date_week_start, strtotime("-1 day", $date_week_end), $_POST['truck_id']);
						
			$days_variance = $days_actual - $days_available_array['days_available_so_far'];
			
			$variance_cost = $days_variance * $daily_cost;
						
			$disphtml .= "
				<tr>
					<td colspan='10'>
						<table style='border:1px black solid;margin-bottom:5px;width:100%'>
			";			
				$disphtml .= "
					<tr class='even'>
						<td><b>Driver</b></td>
						<td width='100'><b>Trailer</b></td>
						<td width='50'><b>Date</b></td>
						<td width='50' align='center'><b>Days</b></td>
						<td width='100'><b>Origin</b></td>
						<td width='100'><b>Destination</b></td>
						<td width='100' align='right'><b>Profit</b></td>
					</tr>
				";
				if(mysqli_num_rows($data)) mysqli_data_seek($data,0);
				while($row = mysqli_fetch_array($data)) 
				{
					
					//section added to include OTR...using same formula as get_days_run() function.......................					
					$mrr_hourly_otr=0;
					if($defaultsarray['local_driver_workweek_hours'] > 0)
					{
						$mrr_hourly_otr=$row['hours_worked'] / $defaultsarray['local_driver_workweek_hours'];		
					}
					//...................................................................................................
					
					//mrr_clear_truck_profit_history($row['truck_id'],$row['linedate_pickup_eta'],$row['id']);
					//mrr_add_truck_profit_history($row['truck_id'],$row['linedate_pickup_eta'],$row['id'],$row['profit']);
					
					if(strtotime(date("m/d/Y", strtotime($row['linedate_pickup_eta']))) >= ${'date_week'.$i} && strtotime($row['linedate_pickup_eta']) < ${'date_week'.($i+1)}) 
					{
						$disphtml .= "
							<tr style='font-size:10px'>
								<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
								<td nowrap>$row[trailer_name]</td>
								<td nowrap>".date("M, j", strtotime($row['linedate_pickup_eta']))."</td>
								<td nowrap align='center'>".number_format(($row['daily_run_otr']+$mrr_hourly_otr),1)."</td>
								<td nowrap>$row[origin]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
								<td nowrap>$row[destination]".($row['destination_state'] != '' ? ', '.$row['destination_state'] : '')."</td>
								<td align='right'>$".money_format('', $row['profit'])."</td>
							</tr>
						";
					}
				}
			
			
			$str_mrr=" NOW using Row Week ".$i." Range is from ".$$vardater1." to ".$$vardater2."";
			$mrr_str="";
			//if($_SESSION['user_id']==23)	
			//$mrr_str="".$mrr_actual."<br>";
			
			
			$datemask1=date("Y-m-d H:i:s",$date_week_start);
			$datemask2=date("Y-m-d H:i:s",strtotime("-1 day", $date_week_end));
			
			$wk_profit=$variance_cost + $row_profit['profit_week'.$i];
			if($wk_profit!=0)
			{
				mrr_clear_truck_profit_history($_POST['truck_id'],$datemask1,0);
				mrr_add_truck_profit_history($_POST['truck_id'],$datemask1,0,$wk_profit);
			}
			$disphtml .= "
						<tr class='odd'>
							<td nowrap><b>".(time() > ${'date_week'.$i} && time() <= strtotime("6 day", ${'date_week'.$i}) ? " <span class='alert'>" : "<span>")."Week $i (".date("n/d", ${'date_week'.$i}).")</span></b></td>
							<td align='center' colspan='2'>$".money_format('', $row_profit['profit_week'.$i])."</span></td>
							<td align='center'><span title='Date Range was from Day ".$datemask1." until ".$datemask2."...".$str_mrr."'>".number_format($days_actual,1)."</span></td>
							<td align='center'>$days_available_array[days_available_so_far]</td>
							<td align='right' nowrap>$days_variance = $".money_format('', $variance_cost)."</td>
							<td align='right' colspan='2'>$".money_format('',$wk_profit)."</td>
						</tr>			
						</table>".$mrr_str."
					</td>
				</tr>				
			";
		}

		$disphtml .= "</table>";
		
		
		$return_var .= "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			<DailyCost><![CDATA[$daily_cost]]></DailyCost>
			<LockDown>$lockdown</LockDown>
			
		";
		display_xml_response($return_var);
	}
	function load_trailer_check() 
	{		
		$lockdown=0;
		
		// get the trailer info
		$sql = "
			select *			
			from trailers
			where id = '".sql_friendly($_POST['trailer_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$lockdown=$row['maint_req_lockdown'];
				
		$return_var = "
			<LockDown>$lockdown</LockDown>			
		";
		display_xml_response($return_var);
	}
	
	function load_customer_brief() {
		// function to show some simple information like the customer contact, email, and phone number
		// (used right now on the load handler page)
		
		global $defaultsarray;
		
		$sql = "
			select *
			
			from customers
			where id = '".sql_friendly($_POST['customer_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$use_surcharge_override=$row['flat_fuel_surchage_override'];	
		$use_surcharge_mon=$row['flat_fuel_surchage_mon'];
		$use_surcharge_tue=$row['flat_fuel_surchage_tue'];
		$use_surcharge_wed=$row['flat_fuel_surchage_wed'];
		$use_surcharge_thu=$row['flat_fuel_surchage_thu'];
		$use_surcharge_fri=$row['flat_fuel_surchage_fri'];
		$use_surcharge_sat=$row['flat_fuel_surchage_sat'];
		$use_surcharge_sun=$row['flat_fuel_surchage_sun'];
				
		// get the fuel surcharge
		$sql = "
			select ifnull(nullif(customers.fuel_surcharge,0),fuel_surcharge.fuel_surcharge) as fuel_surcharge
			from fuel_surcharge, customers
			where range_lower <= '".(money_strip($_POST['fuel_avg']) == 0 ? sql_friendly(money_strip($defaultsarray['fuel_surcharge'])) : sql_friendly(money_strip($_POST['fuel_avg'])))."'
				and customer_id = '".sql_friendly($_POST['customer_id'])."'
				and customers.id = fuel_surcharge.customer_id
				
			order by range_lower desc
			limit 1
		";
		$data_surcharge = simple_query($sql);
		if(!mysqli_num_rows($data_surcharge)) {
			$use_surcharge = 0;
		} else {
			$row_surcharge = mysqli_fetch_array($data_surcharge);
			$use_surcharge = $row_surcharge['fuel_surcharge'];
		}
				
		$return_html = "
			<table>
			<tr>
				<td>Contact:</td>
				<td><b>$row[contact_primary]</b></td>
			</tr>
			<tr>
				<td>E-Mail:</td>
				<td><a href='mailto:$row[contact_email]'>$row[contact_email]</a></td>
			</tr>
			<tr>
				<td>Phone:</td>
				<td><b>$row[phone_work]</b></td>
			</tr>
			</table>
		";
		
		$return_var = "
			<ReturnHTML><![CDATA[$return_html]]></ReturnHTML>
			<Contact><![CDATA[$row[contact_primary]]]></Contact>
			<ContactEMail><![CDATA[$row[contact_email]]]></ContactEMail>
			<PhoneWork><![CDATA[$row[phone_work]]]></PhoneWork>
			<FuelPerMile><![CDATA[$use_surcharge]]></FuelPerMile>
			<FuelPerMileUsed><![CDATA[$row[use_fuel_surcharge]]]></FuelPerMileUsed>
			<FuelPerMileAuto><![CDATA[$row[use_fuel_surcharge_auto]]]></FuelPerMileAuto>
			<FlatRateOverride><![CDATA[$use_surcharge_override]]></FlatRateOverride>
			<FlatRateMon><![CDATA[$use_surcharge_mon]]></FlatRateMon>
			<FlatRateTue><![CDATA[$use_surcharge_tue]]></FlatRateTue>
			<FlatRateWed><![CDATA[$use_surcharge_wed]]></FlatRateWed>
			<FlatRateThu><![CDATA[$use_surcharge_thu]]></FlatRateThu>
			<FlatRateFri><![CDATA[$use_surcharge_fri]]></FlatRateFri>
			<FlatRateSat><![CDATA[$use_surcharge_sat]]></FlatRateSat>
			<FlatRateSun><![CDATA[$use_surcharge_sun]]></FlatRateSun>
		";
		display_xml_response($return_var);
	}
	
	function add_note_entry() 
	{		
		$restricted=(int) $_POST['note_restrict'];
		
		if($_POST['section_id']==1 && $_POST['xref_id']==0)
		{
			$sql="
				select id
				from drivers 
				where deleted=0 and active>0
			";
			$data=simple_query($sql);
			while($row=mysqli_fetch_array($data))
			{
				$id=$row['id'];
				$sql = "
          			insert into notes_main
          				(linedate_added,
          				deleted,
          				created_by_user_id,
          				note_type_id,
          				xref_id,
          				note,
          				access_level)
          				
          			values (now(),
          				0,
          				'".sql_friendly($_SESSION['user_id'])."',
          				'".sql_friendly($_POST['section_id'])."',
          				'".sql_friendly($id)."',
          				'".sql_friendly($_POST['note'])."',
          				'".sql_friendly($restricted)."')
          		";
          		simple_query($sql);
			}
		}
		else
		{
     		$sql = "
     			insert into notes_main
     				(linedate_added,
     				deleted,
     				created_by_user_id,
     				note_type_id,
     				xref_id,
     				note,
     				access_level)
     				
     			values (now(),
     				0,
     				'".sql_friendly($_SESSION['user_id'])."',
     				'".sql_friendly($_POST['section_id'])."',
     				'".sql_friendly($_POST['xref_id'])."',
     				'".sql_friendly($_POST['note'])."',
     				'".sql_friendly($restricted)."')
     		";
     		simple_query($sql);
		}
		//..................................................................................Added for Driver Employer email (of note)...Oct 2012
		$note_added="";
		if($_POST['section_id'] ==1 && $_POST['sendit'] > 0 && $_POST['xref_id']  > 0)
		{
			global $defaultsarray;
			$sent_name=$defaultsarray['company_name'];
			$sent_email=$defaultsarray['company_email_address'];
			
			$sql="
				select employer_id,
					name_driver_last,
					name_driver_first 
				from drivers 
				where id='".sql_friendly($_POST['xref_id'])."'
			";
			$data=simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$employer_id=$row['employer_id'];	
				$driver_name=$row['name_driver_first']." ".$row['name_driver_last'];	
					
				$sql2="
					select dummy_val,
						fvalue 
					from option_values,option_cat 
					where option_values.id='".sql_friendly($employer_id)."'
						and option_values.cat_id=option_cat.id
				";
				$data2=simple_query($sql2);
				if($row2=mysqli_fetch_array($data2))
				{
					$email_list=trim($row2['dummy_val']);
					$company=trim($row2['fvalue']);
					
					$email_list=str_replace(",",";",$email_list);
					$email_arr=explode(";",$email_list);
					for($e=0; $e < sizeof($email_arr);	$e++)
					{
     					$email=trim($email_arr[ $e ]);
     					if(trim($email)!="")
     					{
     						$cc="";
     						$bcc="";
     						$subject="Notice added to Driver ".$driver_name." Records.";
     						
     						$txt="NOTICE REGARDING ".strtoupper($driver_name).": ".$_POST['note']."";
     						$message="<b>Notice regarding ".$driver_name.":</b><br><br>".$_POST['note']."<br>";						
     						
     						mrr_trucking_sendMail($email,$company,$sent_email,$sent_name,$cc,$bcc,$subject,$txt,$message);
     						
     						$note_added=".. and sent to employer at ".$email.".";
     					}
					}		
				}
			}			
		}
		elseif($_POST['section_id'] ==10 && $_POST['sendit'] > 0 && $_POST['xref_id']  > 0)
		{	//Maint Request version... send note to dispatch/James/etc...
			global $defaultsarray;
			$sent_name=$defaultsarray['company_name'];
			$sent_email=$defaultsarray['company_email_address'];
			$sent_email="system@conardtransportation.com";
			
			//$email="jgriffith@conardtransportation.com";			$disp_name="James";
			$email="trucking@conardtransportation.com";			$disp_name="Dispatch";
			$email2="jallen@conardtransportation.com";			$disp_name2="Josh";
			$email3=$defaultsarray['special_email_monitor'];				$disp_name3="Michael";
			$cc="";
     		$bcc="";
			
			$request_name="";
			$request_link="";
			
			$sql="
				select maint_requests.*,
					(select name_truck from trucks where trucks.id=maint_requests.ref_id) as truck_namer,
					(select trailer_name from trailers where trailers.id=maint_requests.ref_id) as trailer_namer
				from maint_requests
				where maint_requests.id='".sql_friendly($_POST['xref_id'])."'
			";
			$data=simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$request_name=trim(str_replace("'","&apos;",$row['maint_desc']));	
				$request_link="<a href='https://trucking.conardtransportation.com/maint.php?id=".$row['id']."'>Request ".$row['id']."</a>";
				
				$unit_name="";
				if($row['equip_type']==1 || $row['equip_type']==58)	$unit_name="Truck ".trim($row['truck_namer'])."";
				if($row['equip_type']==2 || $row['equip_type']==59)	$unit_name="Trailer ".trim($row['trailer_namer'])."";
				
     			$subject="Notice added to Maintenance Request ".$row['id'].".";
     			
     			$txt="NOTICE REGARDING Maintenance Request ".$row['id'].": ".$_POST['note']."";
     			$message="<b>Notice regarding Maintenance Request ".$row['id'].":</b><br><br>Unit: ".$unit_name."<br><br>".$_POST['note']."<br><br>Request: ".$request_name."<br>".$request_link."<br>";						
     						
     			mrr_trucking_sendMail($email,$disp_name,$sent_email,$sent_name,$cc,$bcc,$subject,$txt,$message);
     			mrr_trucking_sendMail($email2,$disp_name2,$sent_email,$sent_name,$cc,$bcc,$subject,$txt,$message);
     			mrr_trucking_sendMail($email3,$disp_name3,$sent_email,$sent_name,$cc,$bcc,$subject,$txt,$message);
     						
				$note_added=".. and sent to ".$disp_name." via email.";
			}			
		}
		//......................................................................................................................................................
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Note Entry Added.".$note_added."";
		//......................................................................................................................................................
		
		echo "1";
	}
	
	function delete_note_entry() {
		$sql = "
			update notes_main
			set deleted = 1
			where id = '".sql_friendly($_POST['note_id'])."'
		";
		simple_query($sql);
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Note Entry ".$_POST['note_id']." Removed. ";
		//......................................................................................................................................................
			
		echo "1";
	}
	
	function set_dispatch_display_mode() {
		if($_POST['show_all'] == '1') {
			$_SESSION['show_all_dispatches'] = true;
			echo "Show all";
		} else {
			$_SESSION['show_all_dispatches'] = false;
			echo "Show Open";
		}
	}
	
	function load_stops() 
	{		
		$hub_zip="37086";
		$mrr_warn="";
		$last_arrival="000000000000";
		$last_completed="000000000000";
		
		if($_POST['load_id'] == 0) {
			$return_var = "<rslt>0</rslt>";
			display_xml_response($return_var);
		}
		
		// get the load info
		$sql = "
			select *
			
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data_load = simple_query($sql);
		$row_load = mysqli_fetch_array($data_load);
		
		//trailer_name  switched for nick_name on 1/30/2017
		$mrr_miles=0;
		$mrr_cntr=0;
		$last_city="";
		$last_state="";
		$last_zip="";
		$last_name="";
		$sql = "
			select id,pcm_miles,shipper_name,shipper_city,shipper_state,shipper_zip			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and deleted = 0
				".(isset($_POST['dispatch_id']) ? " and (trucks_log_id is null or trucks_log_id = 0 or trucks_log_id = '$_POST[dispatch_id]') " : "")."
			order by linedate_pickup_eta asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			if($mrr_cntr > 0)
			{
				if($row['pcm_miles'] > 0)
				{				
					$mrr_miles+=$row['pcm_miles'];	
				}
				else
				{									
					$tmp_miles=mrr_micro_miler_leg($last_zip,$row['shipper_zip'],0);
					
					if($tmp_miles > 0)
					{
						$mrr_miles+=$tmp_miles;
						$sqlu = "		
                    			update load_handler_stops set
                    				pcm_miles = '".sql_friendly($tmp_miles)."'                    			
                    			where id = '".sql_friendly($row['id'])."'
                    		";
                    		simple_query($sqlu);	
					}
				}				
			}	
			else
			{	//Verified with Dale that the first stop should not have any miles calculated.
				/*
				if($row['pcm_miles'] > 0)
				{				
					$mrr_miles+=$row['pcm_miles'];	
				}
				else
				{
     				$tmp_miles=mrr_micro_miler_leg($hub_zip,$row['shipper_zip'],0);	
     				$mrr_miles+=$tmp_miles;
     				if($tmp_miles > 0)
     				{
     					$mrr_miles+=$tmp_miles;
     					$sqlu = "		
                    			update load_handler_stops set
                    				pcm_miles = '".sql_friendly($tmp_miles)."'                    			
                    			where id = '".sql_friendly($row['id'])."'
                    		";
                    		simple_query($sqlu);	
     				}
				}
				*/
			}
               $last_name=trim($row['shipper_name']);
			$last_city=trim($row['shipper_city']);
			$last_state=trim($row['shipper_state']);
			$last_zip=trim($row['shipper_zip']);
			$mrr_cntr++;
		}
			
		$sql = "
			select *,
				(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
				(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
				(select nick_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_nick_name,
				(select nick_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_nick_name,
				date_format(linedate_completed, '%Y-%m-%d') as linedate_completed_date,
				date_format(linedate_completed, '%H:%i') as linedate_completed_time,
				date_format(linedate_arrival, '%Y-%m-%d') as linedate_arrival_date,
				date_format(linedate_arrival, '%H:%i') as linedate_arrival_time
			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and deleted = 0
				".(isset($_POST['dispatch_id']) ? " and (trucks_log_id is null or trucks_log_id = 0 or trucks_log_id = '$_POST[dispatch_id]') " : "")."
			order by linedate_pickup_eta, linedate_pickup_pta, linedate_dropoff_eta
		";
		$data = simple_query($sql);
		
		$sql = "
			select *,
				(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_trailer_name,
				(select nick_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_nick_name,
				(select dedicated_trailer from trailers where trailers.id=trucks_log.trailer_id) as mrr_dedicated_trailer
			
			from trucks_log
			where load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and deleted = 0
		";
		$data_dispatch = simple_query($sql);
		
		$mrr_title="";
		// predispatch
		/*
		$mrr_title="Last Odometer Reading was ".$dropodom." on ".$dropdate.".";	// from Load ID=".$lastload.".";
		$mrr_value=0;
		if($row_load['predispatch_odometer'] > 0) 	$mrr_value=number_format($row_load['predispatch_odometer'],0);
		if($mrr_value=="" || $mrr_value==0)		$mrr_value=$dropodom;
				
		$disphtml = "
			<table width='100%'>
			<tr>
				<td><b>Predispatch</b></td>
				<td><b>Odometer</b></td>
				<td><b>City</b></td>
				<td><b>State</b></td>
				<td><b>Zip</b></td>
			</tr>
			<tr>
				<td></td>
				<td><input class='odometer_update' title='".$mrr_title."' name='predispatch_odometer' id='predispatch_odometer' value=\"".$mrr_value."\" style='width:80px' onchange=\"js_update_predispatch($_POST[load_id])\"></td>
				<td><input class='odometer_update' name='predispatch_city' id='predispatch_city' value=\"$row_load[predispatch_city]\" onchange=\"js_update_predispatch($_POST[load_id])\"></td>
				<td><input class='odometer_update' name='predispatch_state' id='predispatch_state' value=\"$row_load[predispatch_state]\" onchange=\"js_update_predispatch($_POST[load_id])\"></td>
				<td><input class='odometer_update' name='predispatch_zip' id='predispatch_zip' value=\"$row_load[predispatch_zip]\" style='width:80px' onchange=\"js_update_predispatch($_POST[load_id])\"></td>
			</tr>
			</table>
		";
		*/
		$disphtml="";	//removed PreDispatch section above...
		
		$disphtml .= "
			<table width='100%'>
			<tr>
				".(isset($_POST['dispatch_id']) ? "<td></td>" : "")."
				<td nowrap><b>Stop ID</b> ". show_help('Site Wide','Stop List')."</td>
				<td nowrap><b>Stop Type</b></td>
				<td nowrap><b>Odometer</b></td>
				<td><b>Name</b></td>
				<td nowrap><b>City / State</b></td>
				<td nowrap align='right'><b>PC*M</b></td>
				<td><b>Appointment</b></td>				
				".(isset($_POST['dispatch_id']) ? '' : "<td><b>Dispatch ID</b></td>")."
				<td nowrap><b>Arrival Date</b></td>
				<td nowrap><b>Arrival Time</b></td>
				<td nowrap><b>Date Completed</b></td>
				<td nowrap><b>Time Completed</b></td>
				<td nowrap><b>&nbsp;</b></td>
				<td nowrap><b>Trailer</b></td>
				<td nowrap><b>Switch</b></td>
				<td nowrap><b>Grade</b></td>
				<td nowrap><b>Grade Notes/Reason</b></td>
			</tr>
		";
	
		$last_dispatch_id = 0;
		$pcm_miles_total = 0;
		$prev_city_state = "";
		$mrr_cntr=0;
		$stop_cntr=mysqli_num_rows($data);
		
		while($row = mysqli_fetch_array($data)) 
		{
			$mrr_cntr++;
			
			$city_state = "$row[shipper_city]".($row['shipper_state'] != '' ? ', '.$row['shipper_state'] : '');
			$mrr_city=$row['shipper_city'];
			$mrr_state=$row['shipper_state'];
			$graded=$row['stop_grade_id'];
			$graded_note=$row['stop_grade_note'];	
			
			$dispatch_completed=0;
			
			if($last_dispatch_id != $row['trucks_log_id']) {
				if($last_dispatch_id != 0) {
					$disphtml .= "
						<tr>
							<td colspan='17'><hr></td>
						</tr>
					";
				}
				//$prev_city_state = $city_state;
				$last_dispatch_id = $row['trucks_log_id'];
			}
			

			$mrr_clicker="";
			if($row['stop_type_id']==1)
			{
				$mrr_clicker="<span class='mrr_odom_reader' id='odometer_grab' onClick='mrr_odometer_grab(".$row['id'].")' title='Click to Load and Save Last Odometer Reading.'>
								<img src='/images/import.png' alt='...'>
							</span>";	
			}
			
			$prev_city_state = $city_state;
			
			//appointment window...
			$appt_window_tag1="";
			$appt_window_tag2="";			
			$appt_window=$row['appointment_window'];
			if($appt_window > 0)
			{				
				$ideal_time=date("M d, Y", strtotime($row['linedate_pickup_eta']))." ".time_prep($row['linedate_pickup_eta']);
				$appt_window_start="";
				$appt_window_start_time="";
				$appt_window_end="";
				$appt_window_end_time="";
				
				if(strtotime($row['linedate_appt_window_start']) > 0)
				{
					$appt_window_start=date("M d, Y", strtotime($row['linedate_appt_window_start']));
					$appt_window_start_time=time_prep($row['linedate_appt_window_start']);
				}
				if(strtotime($row['linedate_appt_window_end']) > 0)
				{
					$appt_window_end=date("M d, Y", strtotime($row['linedate_appt_window_end']));
					$appt_window_end_time=time_prep($row['linedate_appt_window_end']);
				}
				$appt_window_tag1.="<span onMouseOver=\"mrr_appt_window_display(".$row['id'].",'".$ideal_time."','".$appt_window_start." ".$appt_window_start_time."','".$appt_window_end." ".$appt_window_end_time."');\" onMouseOut=\"mrr_appt_window_no_display(".$row['id'].");\" class='mrr_link_like_on'>";				
				$appt_window_tag2.="</span><div id='stop_".$row['id']."_appt_window' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>";
			}			
			//.....................
			
			
			$last_stopper=1;
			//if($mrr_cntr==$stop_cntr)		$last_stopper=1;
			
			$disphtml .= "
				<tr style='font-size:10px' id='stop_id_$row[id]'>
					".(isset($_POST['dispatch_id']) ? "<td><input type='checkbox' name='checkbox_stop[]' ".($row['trucks_log_id'] == $_POST['dispatch_id'] ? "checked" : "")." value='$row[id]'></td>" : "")."
					<td nowrap>
						".(isset($_POST['dispatch_id']) ? $row['id'] : "<a href='javascript:load_stop_id($row[id])'>$row[id]</a>")."
						<input type='hidden' name='stop_id_array[]' value='$row[id]'>
						<input type='hidden' id='stop_dispatch_$row[id]_last_stop' name='stop_dispatch_$row[id]_last_stop' value='".$last_stopper."'>
					</td>
					<td nowrap>".($row['stop_type_id'] == '1' ? "Shipper $mrr_clicker" : "Consignee")."</td>
					<td nowrap><input name='odometer_reading_$row[id]' id='odometer_reading_$row[id]' style='width:80px' value='".number_format($row['odometer_reading'],0)."' onchange=\"js_update_stop_odometer($row[id], $_POST[load_id])\"></td>
					<td nowrap>$row[shipper_name]</td>
					<td nowrap>$city_state</td>
					<td align='right'><span class='pcm_miles'>$row[pcm_miles]</span></td>
					<td nowrap>".$appt_window_tag1."".(strtotime($row['linedate_pickup_eta']) <= 0 ? '' : date("M d, Y", strtotime($row['linedate_pickup_eta'])))." ".time_prep($row['linedate_pickup_eta'])."".$appt_window_tag2."</td>
					
			";		//<td nowrap>".(strtotime($row['linedate_pickup_pta']) <= 0 ? '' : date("M d, Y", strtotime($row['linedate_pickup_pta'])))." ".time_prep($row['linedate_pickup_pta'])."</td>
			
			$mrr_trailer_id=0;
			$mrr_driver_id=0;
			$mrr_customer_id=0;
			$mrr_dedicated_id=0;
			$mrr_notes="Quick Trailer Drop.";
			$mrr_sel_opt="";
			
			$mrr_start_trailer="";
			$mrr_end_trailer="";
			
			$stop_starting_trailer_id=$row['start_trailer_id'];
			$stop_starting_trailer_name=$row['start_trailer_name'];
			if(trim($row['start_nick_name'])!="")		$stop_starting_trailer_name=$row['start_nick_name'];
			
			$stop_ending_trailer_id=$row['end_trailer_id'];
			$stop_ending_trailer_name=$row['end_trailer_name'];	
			if(trim($row['end_nick_name'])!="")		$stop_ending_trailer_name=$row['end_nick_name'];	
			
			//$mrr_data_seeker=0;
			
			if(isset($_POST['dispatch_id'])) {
				
			} else {
				$disphtml .= "
						<td nowrap>
						<select name='stop_dispatch_$row[id]' id='stop_dispatch_$row[id]' class='stop_dispatch' stop_id='$row[id]' onchange='update_stop_dispatch($row[id])'>
							<option value='0'>select one</option>
				";
							/*
							if($mrr_data_seeker > 0)
							{
								@mysqli_data_seek($data_dispatch,0);
								$mrr_data_seeker=0;
							}
							*/
							if(isset($data_dispatch))	@ mysqli_data_seek($data_dispatch,0);
							
							while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
							{
								$mrr_data_seeker=1;
								$mrr_driver_id=$row_dispatch['driver_id'];
								$mrr_customer_id=$row_dispatch['customer_id'];
								$mrr_dedicated_id=$row_dispatch['mrr_dedicated_trailer'];	
								
								$mrr_trailer_id=$row_dispatch['trailer_id'];
								$mrr_trailer_name=$row_dispatch['mrr_trailer_name'];
								if(trim($row_dispatch['mrr_nick_name'])!="")		$mrr_trailer_name=$row_dispatch['mrr_nick_name'];
								
								if($row['trucks_log_id']==$row_dispatch['id'])
								{
									$dispatch_completed=$row_dispatch['dispatch_completed'];	
								}
																
								if($row['start_trailer_id']==0 && $row['end_trailer_id'] ==0 && $row['trucks_log_id']==$row_dispatch['id'])
								{
									$stop_starting_trailer_id=$mrr_trailer_id;
									$stop_starting_trailer_name=$mrr_trailer_name;
									$stop_ending_trailer_id=$mrr_trailer_id;
									$stop_ending_trailer_name=$mrr_trailer_name;										
									
									//set this stop to  use and keep the trailer...no drop for this stop
									$sqls = "
										update load_handler_stops set
											start_trailer_id='".sql_friendly($mrr_trailer_id)."',
											end_trailer_id='".sql_friendly($mrr_trailer_id)."'
										where id = '".sql_friendly($row['id'])."'
									";
									simple_query($sqls);
									$row['start_trailer_id']=$mrr_trailer_id;
									$row['end_trailer_id']=$mrr_trailer_id;
								}								
								
								$mrr_start_trailer="".$mrr_trailer_name."";
								$mrr_end_trailer="".$mrr_trailer_name."";							
															
								
								$disphtml .= "<option value='$row_dispatch[id]' ".($row_dispatch['id'] == $row['trucks_log_id'] ? 'selected' : '').">$row_dispatch[id]</option>";
							}
				$disphtml .= "
						</select>
						</td>
				";
				
			}
									
			if($stop_starting_trailer_id > 0)		$mrr_start_trailer="".$stop_starting_trailer_name."";
			if($stop_ending_trailer_id > 0)		$mrr_end_trailer="".$stop_ending_trailer_name."";
			if($stop_starting_trailer_id > 0 && $stop_ending_trailer_id == 0)	$mrr_end_trailer="Drop";
			
               $mrr_locked="";
               $mrr_locked_beg="";
               if($dispatch_completed==0)
               {
                    $mrr_locked=" class='mrr_link_like_on' onClick='mrr_drop_switched_trailer_js(".$row['id'].",1);' title='Click to switch or drop Trailer ".$mrr_end_trailer.".'";
               }
			if($stop_starting_trailer_id == 0 && $stop_ending_trailer_id == 0)
               {    //show this only if their is no trailer...or it was dropped from a previus stop.
                    $mrr_locked_beg=" class='mrr_link_like_on' onClick='mrr_drop_switched_trailer_js(".$row['id'].",1);' title='Click to add or hook a Trailer.'";
                    $mrr_start_trailer="Hook";
               }
			
			
			
			
			
			$mrr_time_updater_function="js_update_stop_commpleted($row[id]);";
			if($row['stop_type_id']!=1)
			{
				$mrr_time_updater_function="js_update_stop_commpleted_time($row[id]);";	
			}
			
			$show_master_load_setting="";
			if($row['master_load_include'] > 0)
			{
				$show_master_load_setting="&nbsp; &nbsp; <span style='color:brown' title='Master load ETA ".date("m/d/Y H:i", strtotime($row['master_load_pickup_eta']))."'><b>ML</b></span>";	
			}
			
			
			$mrr_test_arrival=date("YmdHi",strtotime($row['linedate_arrival']));
			$mrr_test_completed=date("YmdHi",strtotime($row['linedate_completed']));
			
			if(!isset($row['linedate_arrival']) || $row['linedate_arrival']=="0000-00-00 00:00:00")			$mrr_test_arrival="000000000000";
			if(!isset($row['linedate_completed']) || $row['linedate_completed']=="0000-00-00 00:00:00")		$mrr_test_completed="000000000000";
			
			if($mrr_test_completed < $mrr_test_arrival && $mrr_test_arrival!="000000000000" && $mrr_test_completed!="000000000000")
			{
				$mrr_warn.="<div class='alert'>Oops, looks like Stop ".$row['id']." Arrival/Completed dates are mixed up...please correct them.</div>";	
			}
			if($last_arrival!="000000000000" && $mrr_test_arrival!="000000000000" && $last_arrival > $mrr_test_arrival)
			{
				$mrr_warn.="<div class='alert'>Stop ".$row['id']." Arrival dates is before the previous stop. Please verify Arrival.</div>";		
			}
			if($last_completed!="000000000000" && $mrr_test_completed!="000000000000" && $last_completed > $mrr_test_completed)
			{
				$mrr_warn.="<div class='alert'>Stop ".$row['id']." Completed dates is before the previous stop. Please verify Completed.</div>";		
			}
			
			$last_arrival=$mrr_test_arrival;
			$last_completed=$mrr_test_completed;
			
			$stop_grader=mrr_select_load_stop_grades("stop_grade_id_".$row['id']."",$graded,"Ungraded"," onChange='js_update_stop_commpleted(".$row['id'].")'",1);		
			$disphtml .= "
					<td nowrap>
						<input name='linedate_arrival_$row[id]' id='linedate_arrival_$row[id]' style='width:80px' class='date_picker_completed' value='".(strtotime($row['linedate_arrival']) > 0 ? date("m/d/Y", strtotime($row['linedate_arrival'])) : "")."' onchange=\"js_update_stop_commpleted($row[id])\">
					</td>
					<td nowrap>
						<input name='linedate_arrival_time_$row[id]' id='linedate_arrival_time_$row[id]' style='width:80px' class='time_picker_completed' value='".$row['linedate_arrival_time']."' onblur=\"js_update_stop_commpleted($row[id])\">
					</td>					
					<td nowrap>
						<input name='linedate_completed_$row[id]' id='linedate_completed_$row[id]' style='width:80px' class='date_picker_completed' value='".(strtotime($row['linedate_completed_date']) > 0 ? date("m/d/Y", strtotime($row['linedate_completed'])) : "")."' onchange=\"js_update_stop_commpleted($row[id])\">
					</td>
					<td nowrap>
						<input name='linedate_completed_time_$row[id]' id='linedate_completed_time_$row[id]' style='width:80px' class='time_picker_completed' value='".$row['linedate_completed_time']."' onblur=\"".$mrr_time_updater_function."\">
					</td>
					<td nowrap>
						<span class='mrr_link_like_on' onClick='mrr_reset_stop_times(".$row['id'].");'>Reset Times</span>
					</td>
					<td nowrap>
						<span id='stop_".$row['id']."_trailer_start'".$mrr_locked_beg.">".$mrr_start_trailer."</span>
					</td>
					<td nowrap>
						<span id='stop_".$row['id']."_trailer_switch'".$mrr_locked.">".$mrr_end_trailer."</span>
					</td>
					<td nowrap>
						".$stop_grader."
					</td>
					<td nowrap>
						<input name='stop_grade_note_$row[id]' id='stop_grade_note_$row[id]' class='input_medium_long' value='".$graded_note."' onchange=\"js_update_stop_commpleted($row[id]);\">
						
						".$show_master_load_setting."
					</td>
					".(isset($_POST['dispatch_id']) ? "" : "<td><a href='javascript:delete_stop($row[id])'><img src='images/delete_small.png' alt='Delete Stop' title='Delete Stop' style='border:0'></a></td>")."
				</tr>
			";					
		}		
		
		$disphtml .= "</table>";
		
		
		$return_var = "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			<DispWarn><![CDATA[$mrr_warn]]></DispWarn>
		";
		display_xml_response($return_var);
	}
	
	function mrr_reset_stop_times()
	{
		$stop_id=$_POST['stop_id'];
		$load_id=$_POST['load_id'];
				
		//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...
		//stop_grade_id='0',
		//stop_grade_note='',
		
		$sql = "
			update load_handler_stops set
				linedate_arrival='0000-00-00 00:00:00',
				linedate_completed='0000-00-00 00:00:00'				
			where load_handler_stops.id = '".sql_friendly($stop_id)."'
		";
		simple_query($sql);
		
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Stop ".$stop_id." arrival and departure times reset ";
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['stop_id']=$stop_id;
		//......................................................................................................................................................
			
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);	
	}
	
	function mrr_switch_drop_trailer_on_stop()
	{
		$stop_id=$_POST['stop_id'];
		$mode="switched";
		$load_id=0;
		$disphtml="";
		
		$sql = "
			select load_handler_stops.*,
				(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
				(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
				(select nick_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_nick_name,
				(select nick_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_nick_name,
				date_format(linedate_completed, '%Y-%m-%d') as linedate_completed_date,
				date_format(linedate_completed, '%H:%i') as linedate_completed_time,
				date_format(linedate_arrival, '%Y-%m-%d') as linedate_arrival_date,
				date_format(linedate_arrival, '%H:%i') as linedate_arrival_time
			
			from load_handler_stops
			where load_handler_stops.id = '".sql_friendly($stop_id)."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$disp_id=$row['trucks_log_id'];
			$load_id=$row['load_handler_id'];
               $mrr_name=$row['shipper_name'];
			$mrr_city=$row['shipper_city'];
			$mrr_state=$row['shipper_state'];	
			$stop_starting_trailer_id=$row['start_trailer_id'];
			$stop_starting_trailer_name=$row['start_trailer_name'];
			if(trim($row['start_nick_name'])!="")		$stop_starting_trailer_name=$row['start_nick_name'];
			
			$stop_ending_trailer_id=$row['end_trailer_id'];
			$stop_ending_trailer_name=$row['end_trailer_name'];	
			if(trim($row['end_nick_name'])!="")		$stop_ending_trailer_name=$row['end_nick_name'];	
			
			/*
			$sql2 = "
     			select *,
     				(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_trailer_name,
     				(select nick_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_nick_name,
     				(select dedicated_trailer from trailers where trailers.id=trucks_log.trailer_id) as mrr_dedicated_trailer
     			
     			from trucks_log
     			where id = '".sql_friendly($disp_id)."'
     		";
     		$data2 = simple_query($sql2);	
     		*/
     		
     		$mrr_sel_opt=mrr_get_available_dropped_trailers($mrr_city,$mrr_state,"");
			$mrr_sel="<select name='stop_n_drop_trailer' id='stop_n_drop_trailer' style='width:200px;' onChange='mrr_set_this_trailer();'>";
			if($stop_ending_trailer_id > 0)
			{
				$mrr_sel.="<option value='0'>Drop Trailer (off site)</option>";
				$mrr_sel.="<option value='9991'>Drop Trailer (at Conard Terminal 1)</option>";
				$mrr_sel.="<option value='9992'>Drop Trailer (at Conard Terminal 1 as available)</option>";
				$mrr_sel.="<option value='9993'>Drop Trailer (at Conard Terminal 2)</option>";
				$mrr_sel.="<option value='9994'>Drop Trailer (at Conard Terminal 2 as available)</option>";
				$mrr_sel.="<option value='".$stop_ending_trailer_id."' selected>".$stop_ending_trailer_name." (Current Trailer)</option>";
			}
			else
			{
				$mrr_sel.="<option value='0' selected>Drop Trailer</option>";	
			}
			$mrr_sel.=$mrr_sel_opt;		
			$mrr_sel.="</select>
				<input type='hidden' name='stop_and_drop_trailer1' id='stop_and_drop_trailer1' value='".$stop_starting_trailer_id."'>
				<input type='hidden' name='stop_and_drop_trailer2' id='stop_and_drop_trailer2' value='".$stop_ending_trailer_id."'>
				<input type='hidden' name='new_stop_and_drop_trailer' id='new_stop_and_drop_trailer' value='".$stop_ending_trailer_id."'>
				";
     		
     		$disphtml.="<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
     		$disphtml.="<tr>";
     		$disphtml.=	"<td valign='top' colspan='2'>";
     		//$disphtml.=	"<div class='alert'>This feature is not yet fully operational.  Do not trust this yet! Thank you.</div>";
     		$disphtml.=	"<div>&nbsp;</div>";
     		$disphtml.=	"<div>Trailer <b>".$stop_starting_trailer_name."</b> to be dropped in ".$mrr_city.", ".$mrr_state." (".$mrr_name.")?</div>";
     		$disphtml.=	"<div>&nbsp;</div>";
     		$disphtml.=	"<div>Select <b>Drop Trailer</b> or a new trailer to pick up in or near ".$mrr_city.", ".$mrr_state." (".$mrr_name.").</div>";
     		$disphtml.=	"<div>&nbsp;</div>";
     		$disphtml.=	"<div id='switcher_result'></div>";
     		$disphtml.=	"</td>";
     		$disphtml.="</tr>";
     		$disphtml.="<tr>";
     		$disphtml.=	"<td valign='top'><b>Starting Trailer</b></td>";
     		$disphtml.=	"<td valign='top'><b>Switch to Trailer or Drop</b></td>";
     		$disphtml.="</tr>";
     		$disphtml.="<tr>";
     		$disphtml.=	"<td valign='top'>".$stop_starting_trailer_name."</td>";
     		$disphtml.=	"<td valign='top'>".$mrr_sel."</td>";
     		//$disphtml.=	"<td valign='top'><span class='mrr_link_like_on' onClick='mrr_drop_switched_trailer_action(".$stop_id.");'>Switch/Drop Trailer</span></td>";
     		$disphtml.="</tr>";
     		$disphtml.="</table>";
     		
     		$disphtml.="
     		<script type='text/javascript'>
     			function mrr_set_this_trailer()
     			{ 
     				thistrailer=$('#stop_n_drop_trailer').val();
     				$('#new_stop_and_drop_trailer').val(thistrailer);     				
     			}
     		</script>";
     		
     		
		}	
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Stop ".$stop_id." trailer ".$mode.". ";
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['stop_id']=$stop_id;
		//......................................................................................................................................................
			
		$return_var = "<rslt>1</rslt><DispHTML><![CDATA[".$disphtml."]]></DispHTML>";
		
		display_xml_response($return_var);		
	}
	function mrr_drop_switched_trailer_action()
	{
		global $datasource;

		$stop_id=$_POST['stop_id'];
		$trailer1_id=$_POST['start_trailer_id'];
		$trailer2_id=$_POST['end_trailer_id'];	//original trailer
		$trailer3_id=$_POST['new_trailer_id'];
		
		$flag_terminal=0;
		$flag_available=0;
		if($trailer3_id == 9991 || $trailer3_id==9992)
		{
			$flag_terminal=1;							//Drop Trailer (Conard Terminal 1)
			if($trailer3_id==9992)	$flag_available=1;		//Drop Trailer (Conard Terminal 1 as available)
			$trailer3_id=0;
		}
		if($trailer3_id == 9993 || $trailer3_id==9994)
		{
			$flag_terminal=2;							//Drop Trailer (Conard Terminal 2)
			if($trailer3_id==9994)	$flag_available=1;		//Drop Trailer (Conard Terminal 2 as available)	
			$trailer3_id=0;
		}
		
		$tname1="";
		$tname2="";
		$tname3="";	
		
		$load_id=0;
		$disphtml="";
		
     	$mrr_name="";
          $mrr_city="";
     	$mrr_state="";
     	$mrr_zip="";
     	$driver_id=0;
     	$customer_id=0;
     	$dedicated_trailer=0;
     	$notes="";
				
		$mode="none";
		//if($trailer1_id == $trailer3_id && $mode=="skipped")
		//{
		//	$return_var = "<rslt>1</rslt><DispHTML><![CDATA[".$disphtml."]]></DispHTML>";		
		//	display_xml_response($return_var);		
		//}
		//else
		//{
			if($trailer1_id != $trailer3_id)	$mode="switched";
			if($trailer3_id == 0)			$mode="dropped";
			
			if($trailer1_id == $trailer3_id)	$mode="reset";
			
			$sql = "
     			select trailer_name from trailers where id='".sql_friendly($trailer1_id)."'
     		";
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data))
			{
				$tname1=$row['trailer_name'];	
			}
			//$tname2="";
			$sql = "
     			select trailer_name from trailers where id='".sql_friendly($trailer3_id)."'
     		";
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data))
			{
				$tname3=$row['trailer_name'];	
			}
			if($trailer3_id==0)		$tname3="Dropped";
			
			$notes="  Switch from Trailer ".$tname1." to Trailer ".$tname3.".";
			$notes2="  Switch from Trailer ".$tname1." to Trailer ".$tname3." between loads.";
			$next_stop=0;
			
			//get info for later...
			$sql = "
     			select *,
     				(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
     				(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
     				(select nick_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_nick_name,
     				(select nick_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_nick_name,
     				date_format(linedate_completed, '%Y-%m-%d') as linedate_completed_date,
     				date_format(linedate_completed, '%H:%i') as linedate_completed_time,
     				date_format(linedate_arrival, '%Y-%m-%d') as linedate_arrival_date,
     				date_format(linedate_arrival, '%H:%i') as linedate_arrival_time
     			
     			from load_handler_stops
     			where id = '".sql_friendly($stop_id)."'
     		";
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data))
     		{
     			$disp_id=$row['trucks_log_id'];
     			$load_id=$row['load_handler_id'];
     			$mrr_name=$row['shipper_name'];
                    $mrr_city=$row['shipper_city'];
     			$mrr_state=$row['shipper_state'];
     			$mrr_zip=$row['shipper_zip'];
               
                    if(trim($mrr_name)!="")       $mrr_zip=trim($mrr_name);
               
                    $linedate=$row['linedate_pickup_eta'];	
     			$next_stop=0;
     			
     			//get next stop on this load...if none after the current one, there is no reason to do as much for the current load.  Just drop the trailer and attach the new trailer to the driver.
     			$sql2 = "
          			select id          			
          			from load_handler_stops
          			where id != '".sql_friendly($stop_id)."'
          				and load_handler_id='".sql_friendly($load_id)."'
          				and linedate_pickup_eta >'".$linedate."'
          				and deleted=0
          		";
          		$data2 = simple_query($sql2);	
          		if($row2 = mysqli_fetch_array($data2))
          		{
          			$next_stop=$row2['id'];	
          		}
     			
     			
     			$driver_id=0;
     			$customer_id=0;
     			$dedicated_trailer=0;
     			
     			
     			$sql2 = "
          			select *,
          				(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_trailer_name,
          				(select nick_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_nick_name,
          				(select dedicated_trailer from trailers where trailers.id=trucks_log.trailer_id) as mrr_dedicated_trailer
          			
          			from trucks_log
          			where id = '".sql_friendly($disp_id)."'
          		";
          		$data2 = simple_query($sql2);	
          		if($row2 = mysqli_fetch_array($data2))
          		{
          			$driver_id=$row2['driver_id'];	
          			$customer_id=$row2['customer_id'];
          			$dedicated_trailer=$row2['mrr_dedicated_trailer'];
          			
          		}
          		
          		
          		
          		//----------------------------------------------User more stops to determine method----------------------------------------------
     		     			
          		if($next_stop>0)
          		{	//more stops on this load...switch current load stops as well
          			//this is the full process
          			
          			//first reset this stop's trailer...
          			$sql2 = "
               			update load_handler_stops set
               				load_handler_stops.end_trailer_id='".sql_friendly($trailer3_id)."'
               			where id = '".sql_friendly($stop_id)."'
               		";
               		simple_query($sql2);
               		
               		//update stops that follow on this Dispatch
               		if($disp_id>0)
               		{
               			$sql2 = "
               			update load_handler_stops set
               				load_handler_stops.start_trailer_id='".sql_friendly($trailer3_id)."',
               				load_handler_stops.end_trailer_id='".sql_friendly($trailer3_id)."'
               			where trucks_log_id = '".sql_friendly($disp_id)."'
               				and linedate_pickup_eta > '".sql_friendly($linedate)."'
               				
               			";
               			simple_query($sql2);	
               		}
          			
          			//clear out old notes
          			$sql2 = "
               			update trailer_switched set
               				deleted = '1'
               			where stop_id='".sql_friendly($stop_id)."'
          			";
          			simple_query($sql2);
          			
          			//clear out old trailer drops
          			$sql2 = "
               			update trailers_dropped set
               				deleted = '1'
               			where trailer_id='".sql_friendly($trailer3_id)."'
               				and LOCATE('Switch from Trailer',notes)>0
               			order by linedate desc
               			limit 1
          			";
          			simple_query($sql2);
          			
          			if($trailer3_id > 0)
          			{
          				//this trailer is now in use, not dropped
          				$sql2 = "
               				update trailers_dropped set
               					drop_completed = '1', linedate_completed=NOW()
               				where trailer_id='".sql_friendly($trailer3_id)."'
               					and drop_completed=0
               				order by linedate desc
               				limit 1
          				";			
          				simple_query($sql2);
          			}
          			if($trailer2_id > 0)
          			{
          				//this trailer is no longer being used and should be dropped back.
          				$sql2 = "
               				update trailers_dropped set
               					drop_completed = '0', linedate_completed='0000-00-00 00:00:00'
               				where trailer_id='".sql_friendly($trailer2_id)."'
               					and drop_completed=0
               				order by linedate desc
               				limit 1
          				";			
          				simple_query($sql2);
          			}
          			
          			if($mode!="reset")
          			{
               			//now make new trailer drop with old trailer...		
                    		$sql2 = "
                    				insert into trailers_dropped
                    					(id,
                    					linedate_added,
                    					linedate,
                    					created_by_user_id,
                    					customer_id,
                    					mrr_drop_mode)
                    					
                    				values (NULL,
                    					now(),
                    					now(),
                    					'".sql_friendly($_SESSION['user_id'])."',
                    					'".sql_friendly($customer_id)."',
                    					2)
                    			";
                    		simple_query($sql2);
                    		$trailer_drop_id = mysqli_insert_id($datasource);	
                    		
                    		//added Nov 2013...complete all prior drops for this trailer so that trailer does not get dropped in more than one location at the same time...
                    		$sql2 = "
                    			update trailers_dropped set 
                    				drop_completed = 1, linedate_completed=NOW()
                    			where id != '".sql_friendly($trailer_drop_id)."'
                    				and trailer_id = '".sql_friendly($trailer1_id)."'	
                    				and drop_completed=0	
                    		";
                    		simple_query($sql2);
                    		
                    		//if(trim($mrr_name)!="")       $mrr_zip=trim($mrr_name);                    		
                    				
                    		$sql2 = "
                    			update trailers_dropped set
                    				
                    				trailer_id = '".sql_friendly($trailer1_id)."',
                    				customer_id = '".sql_friendly($customer_id)."',
                    				location_city = '".sql_friendly($mrr_city)."',
                    				location_state = '".sql_friendly($mrr_state)."',
                    				location_zip = '".sql_friendly($mrr_zip)."',
                    				notes = '".sql_friendly(trim($notes))."',
                    				drop_completed = '0',
                    				linedate_completed='0000-00-00 00:00:00',
                    				dedicated_trailer = '".$dedicated_trailer."'
                    				
                    			where id = '".sql_friendly($trailer_drop_id)."'
                    		";	//drop_completed = '1',
                    		simple_query($sql2);
               		}
               		
               		//create switch record...
          			$trailer1_cost=mrr_get_trailer_cost($trailer1_id);
          			if($trailer1_cost > 0)		$trailer1_cost=mrr_get_option_variable_settings('Trailer Expense');
          		
          			$trailer3_cost=0;
          			if($trailer3_id > 0)	
          			{
          				$trailer3_cost=mrr_get_trailer_cost($trailer3_id);
          				if($trailer3_cost > 0)	$trailer3_cost=mrr_get_option_variable_settings('Trailer Expense');
          			}
          			
          			
          			//add trailer switch_note
          			$sql2 = "
               			insert into trailer_switched
               				(id,
               				linedate_added,
               				linedate,
               				dispatch_id,
               				stop_id,
               				deleted,
               				old_trailer_id,
               				new_trailer_id,
               				old_trailer_cost,
               				new_trailer_cost)
               			values
               				(NULL,
               				NOW(),
               				NOW(),
               				'".sql_friendly($disp_id)."',
               				'".sql_friendly($stop_id)."',
               				0,
               				'".sql_friendly($trailer1_id)."',
               				'".sql_friendly($trailer3_id)."',
               				'".sql_friendly($trailer1_cost)."',
               				'".sql_friendly($trailer3_cost)."')
          			";
          			simple_query($sql2);
          			
          			//update driver so that this trailer is in use
          			$sqld = "
          				update drivers set					
          					attached_trailer_id = '".sql_friendly($trailer3_id)."'
          				where id = '".$driver_id."'
          			";
          			simple_query($sqld);
          			
          			//update truck log/dispatch to show this is the current trailer
          			$sql2 = "
          				update trucks_log set					
          					trailer_id = '".sql_friendly($trailer3_id)."',
          					linedate_updated=NOW(),
          					dropped_trailer='1'
          					
          				where id = '".$disp_id."'
          			";	//,dropped_trailer='1'
          			simple_query($sql2);
          			
          			
          		}
          		else
          		{	//switch for next load but leave this load alone...drop and switch is between loads, not for current load.
          			//this is a shorter process because the current load does not have to change.
          			
          			//clear out old trailer drops
          			$sql2 = "
               			update trailers_dropped set
               				drop_completed = '1', linedate_completed=NOW()
               			where trailer_id='".sql_friendly($trailer3_id)."'     
               				and drop_completed=0    				
               			order by linedate desc
          			";
          			/*		and deleted = '0'
          					and LOCATE('Switch from Trailer',notes)>0
               				and LOCATE(' between loads.',notes)>0
          			*/
          			simple_query($sql2);
          			if($trailer3_id > 0)
          			{
          				//this trailer is now in use, not dropped
          				$sql2 = "
               				update trailers_dropped set
               					drop_completed = '1', linedate_completed=NOW()
               				where trailer_id='".sql_friendly($trailer3_id)."'      
               					and drop_completed=0    					
               				order by linedate desc
               				limit 1
          				";		//and deleted = '0'
          				simple_query($sql2);     				
          			}
          			if($trailer2_id > 0)
          			{
          				//this trailer is no longer being used and should be dropped back.
          				$sql2 = "
               				update trailers_dropped set
               					drop_completed = '1', linedate_completed=NOW()
               				where trailer_id='".sql_friendly($trailer2_id)."'     
               					and drop_completed=0     					
               				order by linedate desc
               				limit 1
          				";		//and deleted = '0'	
          				simple_query($sql2);
          			}
          			
     				//now make new trailer drop with old trailer...		
               		$sql2 = "
               				insert into trailers_dropped
               					(id,
               					linedate_added,
               					linedate,
               					created_by_user_id,
               					customer_id,
               					mrr_drop_mode)
               					
               				values (NULL,
               					now(),
               					now(),
               					'".sql_friendly($_SESSION['user_id'])."',
               					'".sql_friendly($customer_id)."',
               					3)
               			";
               		simple_query($sql2);
               		$trailer_drop_id = mysqli_insert_id($datasource);	
               		
               		//added Nov 2013...complete all prior drops for this trailer so that trailer does not get dropped in more than one location at the same time...
               		$sql2 = "
               			update trailers_dropped set 
               				drop_completed = 1, linedate_completed=NOW()
               			where id != '".sql_friendly($trailer_drop_id)."'
               				and trailer_id = '".sql_friendly($trailer1_id)."'	
               				and drop_completed = 0		
               		";
               		simple_query($sql2);
               				
               		$sql2 = "
               			update trailers_dropped set
               				
               				trailer_id = '".sql_friendly($trailer1_id)."',
               				customer_id = '".sql_friendly($customer_id)."',
               				location_city = '".sql_friendly($mrr_city)."',
               				location_state = '".sql_friendly($mrr_state)."',
               				location_zip = '".sql_friendly($mrr_zip)."',
               				notes = '".sql_friendly(trim($notes2))."',
               				drop_completed = '0', 
               				linedate_completed='0000-00-00 00:00:00',
               				dedicated_trailer = '".$dedicated_trailer."'
               				
               			where id = '".sql_friendly($trailer_drop_id)."'
               		";	//drop_completed = '1',
               		simple_query($sql2);
          			
          			//update truck log/dispatch to show this is the current trailer
          			$sql2 = "
          				update trucks_log set					
          					dropped_trailer='1'          					
          				where id = '".$disp_id."'
          			";	//,dropped_trailer='1'
          			simple_query($sql2);
          			
          			//update driver so that this trailer is in use
          			$sqld = "
          				update drivers set					
          					attached_trailer_id = '".sql_friendly($trailer3_id)."'
          				where id = '".$driver_id."'
          			";
          			simple_query($sqld);          			
          		}

			}
     		$disphtml=$mode;
     		
     		//...................SET FOR USER ACTION LOG............................................................................................................
     		global $mrr_activity_log;
     		$mrr_activity_log["notes"]="Stop ".$stop_id." trailer ".$mode.". ";
     		$mrr_activity_log['load_handler_id']=$load_id;
     		$mrr_activity_log['stop_id']=$stop_id;
     		//......................................................................................................................................................
     			
     		$return_var = "<rslt>1</rslt><DispHTML><![CDATA[".$disphtml."]]></DispHTML>";
     		
     		display_xml_response($return_var);	
		//}
	}

	
	function manage_stop() 
	{
		global $defaultsarray;
		global $datasource;
		
		if($_POST['pickup_eta'] == '') 			$_POST['pickup_eta'] = '0000-00-00 ';
		if($_POST['pickup_pta'] == '') 			$_POST['pickup_pta'] = '0000-00-00 ';
		if($_POST['pickup_eta_time'] != '') 		$_POST['pickup_eta'] .= $_POST['pickup_eta_time'];
		if($_POST['pickup_pta_time'] != '') 		$_POST['pickup_pta'] .= $_POST['pickup_pta_time'];
		//if($_POST['dropoff_eta_time'] != '') 		$_POST['dropoff_eta'] .= $_POST['dropoff_eta_time'];
		//if($_POST['dropoff_pta_time'] != '') 		$_POST['dropoff_pta'] .= $_POST['dropoff_pta_time'];		
		
		if($_POST['appt_window_start'] == '') 		$_POST['appt_window_start'] = '0000-00-00 ';
		if($_POST['appt_window_end'] == '') 		$_POST['appt_window_end'] = '0000-00-00 ';
		if($_POST['appt_window_start_time'] != '') 	$_POST['appt_window_start'] .= $_POST['appt_window_start_time'];
		if($_POST['appt_window_end_time'] != '') 	$_POST['appt_window_end'] .= $_POST['appt_window_end_time'];
		
		$use_appt_needed =0;
          $use_appt_window =0;
          if($_POST['use_appt_window'] > 0) 		$use_appt_window = 1;
		if($_POST['needs_appt'] > 0) 		     $use_appt_needed = 1;
		
		if($_POST['stop_id'] == 0) 
		{
			// new stop, add the initial entry, then update the others
			$sql = "
				insert into load_handler_stops
					(load_handler_id,
					created_by_user_id,
					deleted,
					lynnco_edi_status,
					linedate_added)
					
				values ('".sql_friendly($_POST['load_id'])."',
					'".sql_friendly($_SESSION['user_id'])."',
					0,
					'',
					now())
			";
			simple_query($sql);
			
			$_POST['stop_id'] = mysqli_insert_id($datasource);
		}
		
		//new code to set and get GeoTab premade zone or create one.
		$geotab_stop_id="";
		$geotab_zone_id=0;
		$lat="";
		$long="";
		
		$sql = "
			select geotab_stop_id,geotab_zone_id,latitude,longitude,shipper_address1,shipper_city,shipper_state,shipper_zip			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$geotab_stop_id=trim($row['geotab_stop_id']);
			$geotab_zone_id=$row['geotab_zone_id'];			
			$lat=trim($row['latitude']);
			$long=trim($row['longitude']);
			
			$addr_changed=0;         //check if anything changed in the address... if so, allow it to get the new GPS point...
			if(trim(strtolower($row['shipper_address1'])) != trim(strtolower($_POST['shipper_address1'])))     $addr_changed=1;
               if(trim(strtolower($row['shipper_city'])) != trim(strtolower($_POST['shipper_city'])))             $addr_changed=1;
               if(trim(strtolower($row['shipper_state'])) != trim(strtolower($_POST['shipper_state'])))           $addr_changed=1;
               if(trim(strtolower($row['shipper_zip'])) != trim(strtolower($_POST['shipper_zip'])))               $addr_changed=1;
               if($addr_changed > 0)    $geotab_zone_id=0;       //reset the zone ID in case the address changed it.    
						
			if($lat=="" || $lat=="0" || $long=="" || $long=="0" || $addr_changed > 0)
			{	//no GPS point (or new address from an update), so find one.
				$res=mrr_geotab_get_coordinate_from_addr(trim($_POST['shipper_address1']),trim($_POST['shipper_city']),trim($_POST['shipper_state']),trim($_POST['shipper_zip']));
				$lat=trim($res['lat']);
				$long=trim($res['long']);
			}
			
			if($geotab_zone_id>0)
			{	//get zone name from ID in library...already should have it.
				$geotab_stop_id=mrr_find_geotab_stop_zones_by_id($geotab_zone_id);
				
				if($geotab_stop_id=="")
				{
					$geotab_stop_id=mrr_get_geotab_zones("",trim($_POST['shipper']),1);
					if($geotab_stop_id=="")
					{
						$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
     					$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;	
     					
						$gres=mrr_gps_point_box_creator($long,$lat,$x_off,$y_off);
     					$res['long_zone_w']="".$gres['pt0_long_w']."";
          				$res['long_zone_e']="".$gres['pt1_long_e']."";
          				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
          				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
     					
     					$geotab_stop_id=mrr_make_geotab_zone($long,$lat,trim($_POST['shipper']),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
     					     					
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($_POST['shipper']);     								
     					
     					$geotab_zone_id=mrr_create_geotab_stop_zones($res);	
					}
									
					$res['geotab_id_name']=trim($geotab_stop_id);
					$res['conard_name']=trim($_POST['shipper']);
					$res['id']=$geotab_zone_id;
					$res['long']=$long;
					$res['lat']=$lat;
					
					$res['address_1']=trim($_POST['shipper_address1']);
     				$res['city']=trim($_POST['shipper_city']);
     				$res['state']=trim($_POST['shipper_state']);
     				$res['zip']=trim($_POST['shipper_zip']);	
					
					mrr_update_geotab_stop_zones_gps_shipper_by_id($res);
					mrr_update_geotab_stop_zones_points_name_by_id($res);
				}
			}
			else
			{	//not already saved, so see if the zone exists.
				$res=mrr_find_geotab_stop_zones_by_addr(trim($_POST['shipper_address1']),trim($_POST['shipper_city']),trim($_POST['shipper_state']),trim($_POST['shipper_zip']));
				$geotab_zone_id=$res['id'];
				
				if($geotab_zone_id > 0)
				{
					$geotab_stop_id=trim($res['geotab_id_name']);
										
					if($geotab_stop_id=="")
					{
						$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
     					$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;	
     					
						$gres=mrr_gps_point_box_creator($long,$lat,$x_off,$y_off);
     					$res['long_zone_w']="".$gres['pt0_long_w']."";
          				$res['long_zone_e']="".$gres['pt1_long_e']."";
          				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
          				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
     					
     					$geotab_stop_id=mrr_make_geotab_zone($long,$lat,trim($_POST['shipper']),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
     					     					
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($_POST['shipper']);
     					     					
     					$geotab_zone_id=mrr_create_geotab_stop_zones($res);	
					}		
														
					$res['geotab_id_name']=trim($geotab_stop_id);
					$res['conard_name']=trim($_POST['shipper']);
					$res['id']=$geotab_zone_id;
					$res['long']=$long;
					$res['lat']=$lat;
					
					$res['address_1']=trim($_POST['shipper_address1']);
     				$res['city']=trim($_POST['shipper_city']);
     				$res['state']=trim($_POST['shipper_state']);
     				$res['zip']=trim($_POST['shipper_zip']);
					
					mrr_update_geotab_stop_zones_gps_shipper_by_id($res);
					mrr_update_geotab_stop_zones_points_name_by_id($res);			
				}
				else
				{	//not stored, so create on GeoTab side.
					$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
     				$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;	
     				
					$gres=mrr_gps_point_box_creator($long,$lat,$x_off,$y_off);
					$res['long_zone_w']="".$gres['pt0_long_w']."";
     				$res['long_zone_e']="".$gres['pt1_long_e']."";
     				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
     				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
					
					$geotab_stop_id=mrr_make_geotab_zone($long,$lat,trim($_POST['shipper']),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
										
					$res['geotab_id_name']=trim($geotab_stop_id);
					$res['conard_name']=trim($_POST['shipper']);
					$res['address_1']=trim($_POST['shipper_address1']);
					$res['city']=trim($_POST['shipper_city']);
					$res['state']=trim($_POST['shipper_state']);
					$res['zip']=trim($_POST['shipper_zip']);
					$res['long']=$long;
					$res['lat']=$lat;
									
					$geotab_zone_id=mrr_create_geotab_stop_zones($res);					
				}
			}
		}
		//end new code for GeoTab usage.
		
		$sql = "
			update load_handler_stops set
				shipper_name = '".sql_friendly($_POST['shipper'])."',
				shipper_address1 = '".sql_friendly(trim($_POST['shipper_address1']))."',
				shipper_address2 = '".sql_friendly(trim($_POST['shipper_address2']))."',
				shipper_city = '".sql_friendly(trim($_POST['shipper_city']))."',
				shipper_state = '".sql_friendly(trim($_POST['shipper_state']))."',
				shipper_zip = '".sql_friendly(trim($_POST['shipper_zip']))."',
				stop_phone = '".sql_friendly(trim($_POST['stop_phone']))."',
				
				latitude='".sql_friendly($lat)."',
				longitude='".sql_friendly($long)."',
				geotab_stop_id='".sql_friendly($geotab_stop_id)."',
				geotab_zone_id='".sql_friendly($geotab_zone_id)."',
				
				directions = '".sql_friendly($_POST['directions'])."',
				stop_spec_notes = '".sql_friendly($_POST['spec_notes'])."',
				stop_type_id = '".sql_friendly($_POST['stop_type'])."',
				linedate_pickup_eta = '".(strtotime($_POST['pickup_eta']) > 0 ? date("Y-m-d H:i:s", strtotime($_POST['pickup_eta'])) : '0000-00-00')."',
				linedate_pickup_pta = '".(strtotime($_POST['pickup_pta']) > 0 ? date("Y-m-d H:i:s", strtotime($_POST['pickup_pta'])) : '0000-00-00')."',
				appointment_window='".sql_friendly($use_appt_window)."',
				needs_appt='".sql_friendly($use_appt_needed)."',
				linedate_appt_window_start='".(strtotime($_POST['appt_window_start']) > 0 ? date("Y-m-d H:i:s", strtotime($_POST['appt_window_start'])) : '0000-00-00')."',
				linedate_appt_window_end='".(strtotime($_POST['appt_window_end']) > 0 ? date("Y-m-d H:i:s", strtotime($_POST['appt_window_end'])) : '0000-00-00')."'
				
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);
		
		update_origin_dest($_POST['load_id']);
														
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Manage Update. ";
		$mrr_activity_log['load_handler_id']=$_POST['load_id'];
		$mrr_activity_log['stop_id']=$_POST['stop_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,((int) $_POST['load_id']),0,$_POST['stop_id'],"Updated Stop ".$_POST['stop_id']." Load ".$_POST['load_id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
			
		$return_var = "
			<rslt><![CDATA[1]]></rslt>
			<StopID>$_POST[stop_id]</StopID>
		";
		display_xml_response($return_var);			
		
	}
	
	function load_stop_id() {
		$sql = "
			select *			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$return_var = "
			<rslt></rslt>
			<StopType><![CDATA[$row[stop_type_id]]]></StopType>
			<ShipperName><![CDATA[$row[shipper_name]]]></ShipperName>
			<ShipperAddress1><![CDATA[$row[shipper_address1]]]></ShipperAddress1>
			<ShipperAddress2><![CDATA[$row[shipper_address2]]]></ShipperAddress2>
			<ShipperCity><![CDATA[$row[shipper_city]]]></ShipperCity>
			<ShipperState><![CDATA[$row[shipper_state]]]></ShipperState>
			<ShipperZip><![CDATA[$row[shipper_zip]]]></ShipperZip>
			<ShipperPhone><![CDATA[$row[stop_phone]]]></ShipperPhone>
			<Directions><![CDATA[$row[directions]]]></Directions>
			<SpecNotes><![CDATA[$row[stop_spec_notes]]]></SpecNotes>
			<time>".strtotime($row['linedate_pickup_eta'])."</time>
			<PickupETA>".(strtotime($row['linedate_pickup_eta']) <= 0 ? '' : date("m/d/Y", strtotime($row['linedate_pickup_eta'])))."</PickupETA>
			<PickupETATime>".time_prep($row['linedate_pickup_eta'])."</PickupETATime>
			<PickupPTA>".(strtotime($row['linedate_pickup_pta']) <= 0 ? '' : date("m/d/Y", strtotime($row['linedate_pickup_pta'])))."</PickupPTA>
			<PickupPTATime>".time_prep($row['linedate_pickup_pta'])."</PickupPTATime>
			<ApptNeeded>".$row['needs_appt']."</ApptNeeded>			
			<ApptWindow>".$row['appointment_window']."</ApptWindow>
			<ApptWindowStart>".(strtotime($row['linedate_appt_window_start']) <= 0 ? '' : date("m/d/Y", strtotime($row['linedate_appt_window_start'])))."</ApptWindowStart>
			<ApptWindowStartTime>".time_prep($row['linedate_appt_window_start'])."</ApptWindowStartTime>
			<ApptWindowEnd>".(strtotime($row['linedate_appt_window_end']) <= 0 ? '' : date("m/d/Y", strtotime($row['linedate_appt_window_end'])))."</ApptWindowEnd>
			<ApptWindowEndTime>".time_prep($row['linedate_appt_window_end'])."</ApptWindowEndTime>
			<GeotabAPImsgURL><![CDATA[<b>Last GeoTab API Msg URL log:</b> ".trim($row['geotab_api_msg_url'])."]]></GeotabAPImsgURL>
		";
		
		display_xml_response($return_var);	
	}
	
	function delete_stop() {
		$sql = "
			update load_handler_stops set
				deleted = 1
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);
		
		$sql = "
			select load_handler_id			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);		
		update_origin_dest($row_load_id['load_handler_id']);
												
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Removed. ";
		$mrr_activity_log['load_handler_id']=$row_load_id['load_handler_id'];
		$mrr_activity_log['stop_id']=$_POST['stop_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,((int) $row_load_id['load_handler_id']),0,$_POST['stop_id'],"Removed Stop ".$_POST['stop_id']." Load ".$row_load_id['load_handler_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
			
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);	
	}
	
	function mrr_address_purgery()
	{
		$copy_id=(int) $_POST['copy_id'];
		$stop_id=(int) $_POST['stop_id']; 
		
		if($copy_id==0 || $stop_id==0)
		{
			$return_var = "<rslt>0</rslt>";		
			display_xml_response($return_var);	
			return;
		}
		
		$sql = "
			select *			
			from load_handler_stops
			where id = '".sql_friendly($copy_id)."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$comp=trim($row['shipper_name']);
			$addr=trim($row['shipper_address1']);
			$city=trim($row['shipper_city']);
			$state=trim($row['shipper_state']);
			$zip=trim($row['shipper_zip']);
			
			$zone_id=trim($row['geotab_zone_id']);
			
			//Now find the stops to be replaced.... or more accurately, the stops that need the addresses updated.
			//This is the data that will get replaced... but in every stop that is similar... not just this one.
			$sql2 = "
     			select *			
     			from load_handler_stops
     			where id = '".sql_friendly($stop_id)."'
     		";
     		$data2 = simple_query($sql2);
     		if($row2 = mysqli_fetch_array($data2))
     		{
     			$comp2=trim($row2['shipper_name']);
     			$addr2=trim($row2['shipper_address1']);
     			$city2=trim($row2['shipper_city']);
     			$state2=trim($row2['shipper_state']);
     			$zip2=trim($row2['shipper_zip']);
     			
     			$zone_id2=trim($row2['geotab_zone_id']);
     			
     			//replace all the addresses that look like that
     			$sqlu = "
					update load_handler_stops set
					
						shipper_name = '".sql_friendly($comp)."',
						shipper_address1 = '".sql_friendly($addr)."',
						shipper_city = '".sql_friendly($city)."',
						shipper_state = '".sql_friendly($state)."',
						shipper_zip = '".sql_friendly($zip)."'
						
					where shipper_name = '".sql_friendly($comp2)."'
						and shipper_address1 = '".sql_friendly($addr2)."'
						and shipper_city = '".sql_friendly($city2)."'
						and shipper_state = '".sql_friendly($state2)."'
						and shipper_zip = '".sql_friendly($zip2)."'
				";
				simple_query($sqlu);  
     			    			
     			//replace the zone IDs with the better one.
     			$sqlu = "
					update load_handler_stops set
						geotab_zone_id = '".sql_friendly($zone_id)."'
					where geotab_zone_id = '".sql_friendly($zone_id2)."'
				";
				simple_query($sqlu);     				
     		}			
		}
		
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);		
	}
	function mrr_zone_purgery()
	{
		$copy_id=(int) $_POST['copy_id'];
		$zone_id=(int) $_POST['zone_id']; 
		
		if($copy_id==0 || $zone_id==0)
		{
			$return_var = "<rslt>0</rslt>";		
			display_xml_response($return_var);	
			return;
		}
		
		$sql = "
			select *			
			from geotab_stop_zones
			where id = '".sql_friendly($copy_id)."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$comp=trim($row['conard_name']);
			$addr=trim($row['address_1']);
			$city=trim($row['city']);
			$state=trim($row['state']);
			$zip=trim($row['zip']);
			
			$zone=trim($row['geotab_id_name']);
			
			//Now find the zones to be replaced.... or more accurately, the zones that need the addresses updated.
			//This is the data that will get replaced... but in every zone that is similar... not just this one.
			$sql2 = "
     			select *			
     			from geotab_stop_zones
     			where id = '".sql_friendly($zone_id)."'
     		";
     		$data2 = simple_query($sql2);
     		if($row2 = mysqli_fetch_array($data2))
     		{
     			$comp2=trim($row2['conard_name']);
     			$addr2=trim($row2['address_1']);
     			$city2=trim($row2['city']);
     			$state2=trim($row2['state']);
     			$zip2=trim($row2['zip']);
     			
     			$zone2=trim($row2['geotab_id_name']);
     			
     			//replace all the addresses that look like that
     			$sqlu = "
					update geotab_stop_zones set
					
						conard_name = '".sql_friendly($comp)."',
						address_1 = '".sql_friendly($addr)."',
						city = '".sql_friendly($city)."',
						state = '".sql_friendly($state)."',
						zip = '".sql_friendly($zip)."'
						
					where conard_name = '".sql_friendly($comp2)."'
						and address_1 = '".sql_friendly($addr2)."'
						and city = '".sql_friendly($city2)."'
						and state = '".sql_friendly($state2)."'
						and zip = '".sql_friendly($zip2)."'
				";
				//simple_query($sqlu);  						///Skip this for now.  Keep the address variations but assign to the same zone ID below.
     			    			
     			//replace the zone IDs with the better one.
     			$sqlu = "
					update geotab_stop_zones set
						geotab_id_name = '".sql_friendly($zone)."'
					where geotab_id_name = '".sql_friendly($zone2)."'
				";
				simple_query($sqlu);     				
     		}			
		}
		
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);		
	}
	
	function mrr_auto_update_preplan_marker()
	{
		$sql = "
			update load_handler set
				preplan_marker = '".sql_friendly(trim($_POST['marker']))."'
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		simple_query($sql);
		
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);		
	}
     function mrr_auto_update_preplan_driver()
     {
          $sql = "
                    update load_handler set
                         preplan_driver_id = '".sql_friendly(trim($_POST['driver_id']))."',
                         preplan='1'
                    where id = '".sql_friendly($_POST['load_id'])."'
               ";
          simple_query($sql);
          
          $return_var = "<rslt>1</rslt>";
          
          display_xml_response($return_var);
     }
	
	function update_stop_dispatch() {
		$sql = "
			update load_handler_stops set
				trucks_log_id = '".sql_friendly($_POST['trucks_log_id'])."'
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);

		$sql = "
			select load_handler_id			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);		
		update_origin_dest($row_load_id['load_handler_id']);
												
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Updated. ";
		$mrr_activity_log['load_handler_id']=$row_load_id['load_handler_id'];
		$mrr_activity_log['dispatch_id']=$_POST['trucks_log_id'];
		$mrr_activity_log['stop_id']=$_POST['stop_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,((int) $row_load_id['load_handler_id']),$_POST['trucks_log_id'],$_POST['stop_id'],"Update Dispatch Stop ".$_POST['stop_id']." Load ".$row_load_id['load_handler_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
				
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);
	}
	
	function update_predispatch() {
		
		$_POST['predispatch_odometer'] = money_strip($_POST['predispatch_odometer']);
		
		$sql = "
			update load_handler
			set predispatch_odometer = ".(is_numeric($_POST['predispatch_odometer']) ? "'$_POST[predispatch_odometer]'" : 0).",
				predispatch_city = '".sql_friendly($_POST['predispatch_city'])."',
				predispatch_state = '".sql_friendly($_POST['predispatch_state'])."',
				predispatch_zip = '".sql_friendly($_POST['predispatch_zip'])."'
				
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		simple_query($sql);
										
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Updated pre-dispatch for Load ".$_POST['load_id'].". ";
		$mrr_activity_log['load_handler_id']=$_POST['load_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,((int) $_POST['load_id']),0,0,"Updated pre-dispatch for Load ".$_POST['load_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function update_stop_odometer() {
		
		$_POST['odometer_reading'] = money_strip($_POST['odometer_reading']);
		
		if(!is_numeric($_POST['odometer_reading'])) {
			display_xml_response("<rslt>0</rslt>");
			die;
		}
		
		$sql = "
			update load_handler_stops set
				odometer_reading = '".sql_friendly($_POST['odometer_reading'])."'
			where id = '".sql_friendly($_POST['stop_id'])."'
			limit 1
		";
		simple_query($sql);
										
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Odometer Reading changed to ".$_POST['odometer_reading'].". ";
		$mrr_activity_log['stop_id']=$_POST['stop_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,0,0,$_POST['stop_id'],"Stop ".$_POST['stop_id']." Odometer Reading changed to ".$_POST['odometer_reading'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function update_stop_arrival() 
	{	
		if(!isset($_POST['stop_grade_id']))		$_POST['stop_grade_id']=0;
		if(!isset($_POST['stop_grade_note']))		$_POST['stop_grade_note']="";
          
          //PTA date and time updated only.  SIMPLE VERSION.  
          if($_POST['projected_arrival'] == '')
          {
               $_POST['projected_arrival'] = '0000-00-00';
          }
          else
          {
               $_POST['projected_arrival'] = date("Y-m-d", strtotime($_POST['projected_arrival']));
          }
          
          if($_POST['projected_arrival_time'] == '') 					$_POST['projected_arrival_time'] = '00:00:00';
          
          if(substr_count($_POST['projected_arrival_time'],":")==1)		$_POST['projected_arrival_time'].=":00";
          elseif(substr_count($_POST['projected_arrival_time'],":")==0)	$_POST['projected_arrival_time'].=":00:00";
		
		
		
		
		
		
		//arrival time updated only.  SIMPLE VERSION.  No completion, grade, or error checking unless it is only about the date/time itself.
		if($_POST['linedate_arrival'] == '') 
		{
			$_POST['linedate_arrival'] = '0000-00-00';
		} 
		else 
		{
			$_POST['linedate_arrival'] = date("Y-m-d", strtotime($_POST['linedate_arrival']));
		}
		
		if($_POST['linedate_arrival_time'] == '') 					$_POST['linedate_arrival_time'] = '00:00:00';
		
		if(substr_count($_POST['linedate_arrival_time'],":")==1)		$_POST['linedate_arrival_time'].=":00";
		elseif(substr_count($_POST['linedate_arrival_time'],":")==0)	$_POST['linedate_arrival_time'].=":00:00";
				
		$mrr_test_sql="";			
		$return_var="";
		
		$err_msg="";
		
		if($_POST['stop_id'] > 0)
		{
     		$mrr_update_arrival="".date("Y-m-d H:i:s",strtotime($_POST['linedate_arrival']." ".$_POST['linedate_arrival_time']))."";
		     $mrr_update_projected="".date("Y-m-d H:i:s",strtotime($_POST['projected_arrival']." ".$_POST['projected_arrival_time']))."";
     		
		     if($_POST['linedate_arrival']=="0000-00-00")
     		{
     			$_POST['stop_grade_id']=0;
     			$_POST['stop_grade_note']="";
                    $mrr_update_arrival="0000-00-00 00:00:00";
     		}
               if($_POST['projected_arrival']=="0000-00-00")      $mrr_update_projected="0000-00-00 00:00:00";
     		
     		$sql="
                    update load_handler_stops set 
                        linedate_arrival='".$mrr_update_arrival."',
                        linedate_pickup_pta='".$mrr_update_projected."' 
                    where id='".sql_friendly($_POST['stop_id'])."'";
     		$mrr_test_sql=$sql;
     		simple_query($sql);
     		
     		$sql = "
     			select load_handler_id,
     				trucks_log_id,     				
     				TIMESTAMPDIFF(MINUTE,linedate_arrival,linedate_pickup_eta) as mrr_time_diff
     			
     			from load_handler_stops
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		$data_load_id = simple_query($sql);
     		$row_load_id = mysqli_fetch_array($data_load_id);
     		
     		//autograde this stop   		//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...  		
     		if($_POST['stop_grade_id']==0 && $_POST['linedate_arrival']!="0000-00-00" && 1==2)
     		{
     			$calc_stop_grade=0;
     			$calc_stop_reason="";
     			if($row_load_id['mrr_time_diff'] >= 0 && $row_load_id['mrr_time_diff'] <= 30)		{	$calc_stop_grade=5;		$calc_stop_reason="Auto-Graded: 0-30 minutes early.";		}	//On Time
     			elseif($row_load_id['mrr_time_diff'] > 30 && $row_load_id['mrr_time_diff'] >= 120)	{	$calc_stop_grade=7;		$calc_stop_reason="Auto-Graded: 30-120 minutes early.";	}	//Early
     			elseif($row_load_id['mrr_time_diff'] > 120)									{	$calc_stop_grade=8;		$calc_stop_reason="Auto-Graded: more than 2 hrs early.";	}	//Very Early	
     			elseif($row_load_id['mrr_time_diff'] < 0 && $row_load_id['mrr_time_diff'] >=-60)	{	$calc_stop_grade=4;		$calc_stop_reason="Auto-Graded: 0-60 minutes late.";		}	//Late
     			elseif($row_load_id['mrr_time_diff'] < -60 && $row_load_id['mrr_time_diff'] >=-180)	{	$calc_stop_grade=3;		$calc_stop_reason="Auto-Graded: 1-3 hours late.";			}	//Very Late
     			elseif($row_load_id['mrr_time_diff'] < -180 && $row_load_id['mrr_time_diff'] >=-600)	{	$calc_stop_grade=2;		$calc_stop_reason="Auto-Graded: 3-10 hours late.";		}	//Past Due
     			elseif($row_load_id['mrr_time_diff'] < -600 && $row_load_id['mrr_time_diff'] >=-60)	{	$calc_stop_grade=1;		$calc_stop_reason="Auto-Graded: more than 10 hours late.";	}	//Epic Fail
     			
     			$_POST['stop_grade_id']=$calc_stop_grade;
     			$_POST['stop_grade_note']=$calc_stop_reason;    			
     		}
     		$sqlx = "
     			update load_handler_stops set
     				stop_grade_id = '".sql_friendly($_POST['stop_grade_id'])."',
     				stop_grade_note = '".sql_friendly($_POST['stop_grade_note'])."'
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		simple_query($sqlx);
     		
     										
     		//...................SET FOR USER ACTION LOG............................................................................................................
     		global $mrr_activity_log;
     		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Arrival Set. ";
     		$mrr_activity_log['stop_id']=$_POST['stop_id'];
     		
     		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,0,0,$_POST['stop_id'],"Stop ".$_POST['stop_id']." Arrival Set.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
     		
     		//......................................................................................................................................................
     				
     		$return_var = "
     			<rslt>1</rslt>
     			<ErrorMsg><![CDATA[".$err_msg."]]></ErrorMsg>
     			<StopID>$_POST[stop_id]</StopID>
     			<ADT>$_POST[linedate_arrival] $_POST[linedate_arrival_time]</ADT>
     			<SQL><![CDATA[$mrr_test_sql]]></SQL>
     			<StopGradeID><![CDATA[".$_POST['stop_grade_id']."]]></StopGradeID>
    				<StopGradeNote><![CDATA[".$_POST['stop_grade_note']."]]></StopGradeNote>
     		";     		
		}
		else
		{
			$err_msg="No Stop ID Given";
			$return_var = "
				<rslt>0</rslt>
				<ErrorMsg><![CDATA[".$err_msg."]]></ErrorMsg>
				<ADT>$_POST[linedate_arrival] $_POST[linedate_arrival_time]</ADT>
				<SQL><![CDATA[$mrr_test_sql]]></SQL>
				<StopGradeID><![CDATA[".$_POST['stop_grade_id']."]]></StopGradeID>
    				<StopGradeNote><![CDATA[".$_POST['stop_grade_note']."]]></StopGradeNote>
			";
     			
		}
		
		display_xml_response($return_var);
	}
	
	function update_stop_completed() 
	{		
		$is_last_stop=0;
		if(isset($_POST['is_last_stop']))		$is_last_stop=$_POST['is_last_stop'];
		
		if($_POST['linedate_completed'] == '') {
			$_POST['linedate_completed'] = '0000-00-00';
			$_POST['linedate_completed_time'] = '00:00:00';
		} else {
			$_POST['linedate_completed'] = date("Y-m-d", strtotime($_POST['linedate_completed']));
		}
		
		if($_POST['linedate_completed_time'] == '' || $_POST['linedate_completed_time'] == '00:00') 		$_POST['linedate_completed_time'] = '00:00:00';
		
		//arrival time
		if($_POST['linedate_arrival'] == '') {
			$_POST['linedate_arrival'] = '0000-00-00';
		} else {
			$_POST['linedate_arrival'] = date("Y-m-d", strtotime($_POST['linedate_arrival']));
		}
		
		if($_POST['linedate_arrival_time'] == '') $_POST['linedate_arrival_time'] = '';
		
		if(!isset($_POST['stop_grade_id']))		$_POST['stop_grade_id']=0;
		if(!isset($_POST['stop_grade_note']))		$_POST['stop_grade_note']="";
		
		$mrr_test_sql="";		
		/*
		
		$drop_the_trailer=0;
		$switch_the_trailer=0;
		if(isset($_POST['starting_trailer_id']) && isset($_POST['drop_trailer_switcher']))
		{
			$mrr_test_sql="point 1";
			if($_POST['drop_trailer_switcher'] != $_POST['starting_trailer_id']) 
			{
				$switch_the_trailer=0;
				$drop_the_trailer=1;
				$mrr_test_sql="point 2";
				if($_POST['drop_trailer_switcher']!=0)		$switch_the_trailer=1;
				//{					
					$marr['dispatch_id']=$_POST['switch_dispatch_id'];
					$marr['stop_id']=$_POST['stop_id'];
					$marr['customer_id']=$_POST['switch_customer'];
					$marr['driver_id']=$_POST['switch_driver'];
					$marr['location_city']=$_POST['switch_local_city'];
					$marr['location_state']=$_POST['switch_local_state'];
					$marr['location_zip']=$_POST['switch_local_zip'];
					$marr['notes']=$_POST['switch_notes'];
					$marr['linedate']=$_POST['switch_linedate'];
					$marr['dedicated_trailer']=$_POST['switch_dedicated_trailer'];
					$marr['trailer1_id']=$_POST['starting_trailer_id'];
					$marr['trailer2_id']=$_POST['drop_trailer_switcher'];
					
					$mrr_test_sql=mrr_trailer_drop_and_switch($marr);
				//}					
			}
		}		
		
		*/
		$return_var="";
		
		$load_id=0;
		$disp_id=0;
		
		//test date...to see if completing before last stop
		$err_msg="";
		
		$edi_214_status_code="AF";
		
		if($_POST['moder']==1)
		{	//only test date if consignee...moder=1
			$edi_214_status_code="D1";
			
			$mrr_test_date="".sql_friendly($_POST['linedate_completed'])." ".sql_friendly($_POST['linedate_completed_time'])."";
     		
     		$sql="
     			select load_handler_id,
     				trucks_log_id,
     				linedate_completed
     			from load_handler_stops
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		$data=simple_query($sql);
     		if($row=mysqli_fetch_array($data))
     		{
     			$load_id=$row['load_handler_id'];
				$disp_id=$row['trucks_log_id'];
				$old_comp_date=$row['linedate_completed'];
				$new_comp_date="";
				if(trim($_POST['linedate_completed'])!="" && trim($_POST['linedate_completed_time'])!="")
				{
					if(trim($_POST['linedate_completed'])!="0000-00-00" || trim($_POST['linedate_completed_time'])!="00:00:00")
					{					
						$new_comp_date=date("Y-m-d H:i:s",strtotime($mrr_test_date));
					}
     			}
     			if($old_comp_date==$new_comp_date || $new_comp_date=="")		$is_last_stop=0;
     			
     			$cntr=0;
     			$sqls="
     				select *
     				from load_handler_stops
     				where load_handler_id = '".sql_friendly($row['load_handler_id'])."'
     					".($row['trucks_log_id'] > 0 ? " and trucks_log_id='".sql_friendly($row['trucks_log_id'])."'" : "")."
     					and linedate_completed > '0000-00-00 00:00:00'
     				";
     			$datas=simple_query($sqls);	
     			while($rows=mysqli_fetch_array($datas))
     			{	
     				$last_date=date("Ymd",strtotime($rows['linedate_completed']));
     				$last_time=date("Hi",strtotime($rows['linedate_completed']));	
     				if($is_last_stop == 0)
     				{
     					if(date("Ymd", strtotime($mrr_test_date)) < $last_date || ( date("Ymd", strtotime($mrr_test_date))==$last_date && date("Hi", strtotime($mrr_test_date)) < $last_time))
     					{
     						$err_msg="Oops, The date and time you entered are before the last stop.";	
     					}
     				}
     			}
     		}
		}
		
		if(trim($err_msg)=="")
		{
     		if($_POST['linedate_completed']=="0000-00-00")
     		{	//turned this off so grades could be given even if not completed yet....Aug 2015
     			//$_POST['stop_grade_id']=0;
     			//$_POST['stop_grade_note']="";
     		}     		
     		
     		//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...Added back below Aug 2015
     		//stop_grade_id='".sql_friendly( $_POST['stop_grade_id'])."',
     		//stop_grade_note='".sql_friendly(trim($_POST['stop_grade_note']))."',
     		
     		$stoplight_warning_flag=0;
     		if($_POST['stop_grade_id'] > 0 && $_POST['stop_grade_id']<=4)		$stoplight_warning_flag=1;
     		$sql = "
     			update load_handler_stops set
     				stop_grade_id='".sql_friendly( $_POST['stop_grade_id'])."',
     				stop_grade_note='".sql_friendly(trim($_POST['stop_grade_note']))."',
     				stoplight_warning_flag='".sql_friendly( $stoplight_warning_flag)."',
     				linedate_completed = '".sql_friendly($_POST['linedate_completed'])." ".sql_friendly($_POST['linedate_completed_time'])."',
     				linedate_arrival = '".sql_friendly($_POST['linedate_arrival'])." ".sql_friendly($_POST['linedate_arrival_time'])."'     				
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		simple_query($sql);
     		
     		$sql = "
     			select load_handler_id,
     				trucks_log_id,     				
     				TIMESTAMPDIFF(MINUTE,linedate_completed,linedate_pickup_eta) as mrr_time_diff,
     				lynnco_edi_status_date
     			
     			from load_handler_stops
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";	
     		
     		$data_load_id = simple_query($sql);
     		$row_load_id = mysqli_fetch_array($data_load_id);
     		update_origin_dest($row_load_id['load_handler_id']);
     		
     		//$lynnco_edi_status_date=$row_load_id['lynnco_edi_status_date'];
     		
     		//when date is cleared, reset this dispatch as not completed...  added Mar 2013
     		if($_POST['linedate_completed'] == '0000-00-00' || $_POST['linedate_completed'] == '')
     		{
     			$sql = "
     				update trucks_log
     					set dispatch_completed = '0'
     				where id = '".sql_friendly($row_load_id['trucks_log_id'])."'
     			";
     			simple_query($sql);	
     		}	
     		
     		     		
     		//autograde this stop 			//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...    		
     		if($_POST['stop_grade_id']==0 && $_POST['linedate_completed']!="0000-00-00" && 1==2)
     		{
     			$calc_stop_grade=0;
     			$calc_stop_reason="";
     			if($row_load_id['mrr_time_diff'] >= 0 && $row_load_id['mrr_time_diff'] <= 30)		{	$calc_stop_grade=5;		$calc_stop_reason="Auto-Graded: 0-30 minutes early.";		}	//On Time
     			elseif($row_load_id['mrr_time_diff'] > 30 && $row_load_id['mrr_time_diff'] >= 120)	{	$calc_stop_grade=7;		$calc_stop_reason="Auto-Graded: 30-120 minutes early.";	}	//Early
     			elseif($row_load_id['mrr_time_diff'] > 120)									{	$calc_stop_grade=8;		$calc_stop_reason="Auto-Graded: more than 2 hrs early.";	}	//Very Early	
     			elseif($row_load_id['mrr_time_diff'] < 0 && $row_load_id['mrr_time_diff'] >=-60)	{	$calc_stop_grade=4;		$calc_stop_reason="Auto-Graded: 0-60 minutes late.";		}	//Late
     			elseif($row_load_id['mrr_time_diff'] < -60 && $row_load_id['mrr_time_diff'] >=-180)	{	$calc_stop_grade=3;		$calc_stop_reason="Auto-Graded: 1-3 hours late.";			}	//Very Late
     			elseif($row_load_id['mrr_time_diff'] < -180 && $row_load_id['mrr_time_diff'] >=-600)	{	$calc_stop_grade=2;		$calc_stop_reason="Auto-Graded: 3-10 hours late.";		}	//Past Due
     			elseif($row_load_id['mrr_time_diff'] < -600)									{	$calc_stop_grade=1;		$calc_stop_reason="Auto-Graded: more than 10 hours late.";	}	//Epic Fail
     			
     			$_POST['stop_grade_id']=$calc_stop_grade;
     			$_POST['stop_grade_note']=$calc_stop_reason;    				
     		}
     		$sqlx = "
     			update load_handler_stops set
     				stop_grade_id = '".sql_friendly($_POST['stop_grade_id'])."',
     				stop_grade_note = '".sql_friendly($_POST['stop_grade_note'])."'
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		simple_query($sqlx);
     		     		
     		     		
     		//update dispatch completed if no more stops incomplete
     		if($disp_id > 0 && $load_id > 0)
     		{
          		$sql_cnt = "
          			select count(*) as mycount 
          			from load_handler_stops
          			where deleted=0
          				and load_handler_id='".sql_friendly($load_id)."'
          				and trucks_log_id='".sql_friendly($disp_id)."'
          				and (linedate_completed='0000-00-00 00:00:00' or linedate_completed is NULL or linedate_completed=0)
          		";	     		
          		$data_cnt = simple_query($sql_cnt);
          		if($row_cnt = mysqli_fetch_array($data_cnt))
          		{
               		if($row_cnt['mycount'] == 0)
               		{
               			$sql = "
          					update trucks_log set
          						dispatch_completed = '1'
          					where id = '".sql_friendly($disp_id)."'
          				";
          				simple_query($sql);	
               		}
               		else
               		{
               			$sql = "
          					update trucks_log set
          						dispatch_completed = '0'
          					where id = '".sql_friendly($disp_id)."'
          				";
          				simple_query($sql);	
               		}
          		}
          		else
          		{
          			$sql = "
          				update trucks_log set
          					dispatch_completed = '1'
          				where id = '".sql_friendly($disp_id)."'
          			";
          			simple_query($sql);		
          		}
     		} 
     		
     		if($is_last_stop > 0)
     		{
     			mrr_send_manual_pn_cust_emails($_POST['stop_id'],0);	//2nd arg is test mode if=1
     			$edi_214_status_code="D1";
     		}
     		   		
     		mrr_check_if_edi_214_file_needed($row_load_id['load_handler_id'],$_POST['stop_id'],$edi_214_status_code);	     //LynnCo
               mrr_check_if_edi_214_file_needed_koch($row_load_id['load_handler_id'],$_POST['stop_id'],$edi_214_status_code);	//Koch Logistics
     		
     									
     		//...................SET FOR USER ACTION LOG............................................................................................................
     		global $mrr_activity_log;
     		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Completed. ";
     		$mrr_activity_log['load_handajax_maint_req_listler_id']=$row_load_id['load_handler_id'];
     		$mrr_activity_log['stop_id']=$_POST['stop_id'];
     		
     		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$row_load_id['load_handler_id'],0,$_POST['stop_id'],"Stop ".$_POST['stop_id']." Completed.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
     		
     		//......................................................................................................................................................
     				
     		$return_var = "
     			<rslt>1</rslt>
     			<ErrorMsg><![CDATA[".$err_msg."]]></ErrorMsg>
     			<SQL><![CDATA[$sql]]></SQL>
     			<DT>$_POST[linedate_completed] $_POST[linedate_completed_time]</DT>
     			<ADT>$_POST[linedate_arrival] $_POST[linedate_arrival_time]</ADT>
     			<testSQL><![CDATA[$mrr_test_sql]]></testSQL>
     			<StopGradeID><![CDATA[".$_POST['stop_grade_id']."]]></StopGradeID>
     			<StopGradeNote><![CDATA[".$_POST['stop_grade_note']."]]></StopGradeNote>
     		";     		
		}
		else
		{
			$return_var = "
				<rslt>0</rslt>
				<ErrorMsg><![CDATA[".$err_msg."]]></ErrorMsg>
				<SQL><![CDATA[$sql]]></SQL>
				<DT>$_POST[linedate_completed] $_POST[linedate_completed_time]</DT>
				<ADT>$_POST[linedate_arrival] $_POST[linedate_arrival_time]</ADT>
				<testSQL><![CDATA[$mrr_test_sql]]></testSQL>
				<StopGradeID><![CDATA[".$_POST['stop_grade_id']."]]></StopGradeID>
     			<StopGradeNote><![CDATA[".$_POST['stop_grade_note']."]]></StopGradeNote>
			";
     			
		}
		
		if($load_id > 0)		mrr_dispatch_completion_updates($load_id,0);
		
		display_xml_response($return_var);
	}	
	
	function update_stop_completed_no_arrival() 
	{
		$bypass=1;		//bypass the error check...maybe show error, but save anyway.
		
		if($_POST['linedate_completed'] == '') {
			$_POST['linedate_completed'] = '0000-00-00';
		} else {
			$_POST['linedate_completed'] = date("Y-m-d", strtotime($_POST['linedate_completed']));
		}
		if($_POST['linedate_completed_time'] == '') 		$_POST['linedate_completed_time'] = '00:00';
		$return_var="";
		
		if(!isset($_POST['stop_grade_id']))		$_POST['stop_grade_id']=0;
		if(!isset($_POST['stop_grade_note']))		$_POST['stop_grade_note']="";
		
		//test date...to see if completing before last stop
		$err_msg="";
		$mrr_test_date="".sql_friendly($_POST['linedate_completed'])." ".sql_friendly($_POST['linedate_completed_time'])."";
		
		$load_id=0;
		$disp_id=0;
		
		$sql="
			select load_handler_id,
				trucks_log_id 
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$load_id=$row['load_handler_id'];
			$disp_id=$row['trucks_log_id'];
			
			$cntr=0;
			$sqls="
				select *
				from load_handler_stops
				where load_handler_id = '".sql_friendly($row['load_handler_id'])."'
					".($row['trucks_log_id'] > 0 ? " and trucks_log_id='".sql_friendly($row['trucks_log_id'])."'" : "")."
					and linedate_completed > '0000-00-00 00:00:00'
				";
			$datas=simple_query($sqls);	
			
			while($rows=mysqli_fetch_array($datas))
			{	
				$last_date=date("Ymd",strtotime($rows['linedate_completed']));
				$last_time=date("Hi",strtotime($rows['linedate_completed']));	
				
				if(date("Ymd", strtotime($mrr_test_date)) < $last_date || ( date("Ymd", strtotime($mrr_test_date))==$last_date && date("Hi", strtotime($mrr_test_date)) < $last_time))
				{
					$err_msg="Oops, The date and time you entered are before the last stop!";	
				}
			}
		}
		
		$edi_214_status_code="";
		
		if(trim($err_msg)=="" || $bypass > 0)
		{
     		if($_POST['linedate_completed']=="0000-00-00")
     		{
     			$_POST['stop_grade_id']=0;
     			$_POST['stop_grade_note']="";
     		} 
     		
     		$sql = "
     			update load_handler_stops set
     				linedate_completed = '".sql_friendly($_POST['linedate_completed'])." ".sql_friendly($_POST['linedate_completed_time'])."'
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		simple_query($sql);
     				
     		$sql = "
     			select load_handler_id,
     				trucks_log_id,     				
     				TIMESTAMPDIFF(MINUTE,linedate_completed,linedate_pickup_eta) as mrr_time_diff
     			
     			from load_handler_stops
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		$data_load_id = simple_query($sql);
     		$row_load_id = mysqli_fetch_array($data_load_id);
     		update_origin_dest($row_load_id['load_handler_id']);    		
     		
     		
     		//autograde this stop   		//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...  		
     		if($_POST['stop_grade_id']==0 && $_POST['linedate_completed']!="0000-00-00" && 1==2)
     		{
     			$calc_stop_grade=0;
     			$calc_stop_reason="";
     			if($row_load_id['mrr_time_diff'] >= 0 && $row_load_id['mrr_time_diff'] <= 30)		{	$calc_stop_grade=5;		$calc_stop_reason="Auto-Graded: 0-30 minutes early.";		}	//On Time
     			elseif($row_load_id['mrr_time_diff'] > 30 && $row_load_id['mrr_time_diff'] >= 120)	{	$calc_stop_grade=7;		$calc_stop_reason="Auto-Graded: 30-120 minutes early.";	}	//Early
     			elseif($row_load_id['mrr_time_diff'] > 120)									{	$calc_stop_grade=8;		$calc_stop_reason="Auto-Graded: more than 2 hrs early.";	}	//Very Early	
     			elseif($row_load_id['mrr_time_diff'] < 0 && $row_load_id['mrr_time_diff'] >=-60)	{	$calc_stop_grade=4;		$calc_stop_reason="Auto-Graded: 0-60 minutes late.";		}	//Late
     			elseif($row_load_id['mrr_time_diff'] < -60 && $row_load_id['mrr_time_diff'] >=-180)	{	$calc_stop_grade=3;		$calc_stop_reason="Auto-Graded: 1-3 hours late.";			}	//Very Late
     			elseif($row_load_id['mrr_time_diff'] < -180 && $row_load_id['mrr_time_diff'] >=-600)	{	$calc_stop_grade=2;		$calc_stop_reason="Auto-Graded: 3-10 hours late.";		}	//Past Due
     			elseif($row_load_id['mrr_time_diff'] < -600 && $row_load_id['mrr_time_diff'] >=-60)	{	$calc_stop_grade=1;		$calc_stop_reason="Auto-Graded: more than 10 hours late.";	}	//Epic Fail
     			
     			$_POST['stop_grade_id']=$calc_stop_grade;
     			$_POST['stop_grade_note']=$calc_stop_reason;    			
     		}
     		$sqlx = "
     			update load_handler_stops set
     				stop_grade_id = '".sql_friendly($_POST['stop_grade_id'])."',
     				stop_grade_note = '".sql_friendly($_POST['stop_grade_note'])."'
     			where id = '".sql_friendly($_POST['stop_id'])."'
     		";
     		simple_query($sqlx);
     		
     		//when date is cleared, reset this dispatch as not completed...  added Mar 2013
     		if($_POST['linedate_completed'] == '0000-00-00' || $_POST['linedate_completed'] == '')
     		{
     			$sql = "
     				update trucks_log
     					set dispatch_completed = '0'
     				where id = '".sql_friendly($row_load_id['trucks_log_id'])."'
     			";
     			simple_query($sql);	
     		}	
     		     		
     		//update dispatch completed if no more stops incomplete
     		if($disp_id > 0 && $load_id > 0)
     		{
          		$sql_cnt = "
          			select count(*) as mycount 
          			from load_handler_stops
          			where deleted=0
          				and load_handler_id='".sql_friendly($load_id)."'
          				and trucks_log_id='".sql_friendly($disp_id)."'
          				and (linedate_completed='0000-00-00 00:00:00' or linedate_completed is NULL or linedate_completed=0)
          		";	     		
          		$data_cnt = simple_query($sql_cnt);
          		$row_cnt = mysqli_fetch_array($data_cnt);	
          		if($row_cnt['mycount'] == 0)
          		{
          			$sql = "
     					update trucks_log set
     						dispatch_completed = '1'
     					where id = '".sql_friendly($disp_id)."'
     				";
     				simple_query($sql);	
          		}
          		else
          		{
          			$sql = "
     					update trucks_log set
     						dispatch_completed = '0'
     					where id = '".sql_friendly($disp_id)."'
     				";
     				simple_query($sql);	
          		}
          		$edi_214_status_code="D1";
     		}    
     		
     		mrr_check_if_edi_214_file_needed($row_load_id['load_handler_id'],$_POST['stop_id'],$edi_214_status_code);	     //LynnCo
               mrr_check_if_edi_214_file_needed_koch($row_load_id['load_handler_id'],$_POST['stop_id'],$edi_214_status_code);	//Koch Logistics
     		   		
     										
     		//...................SET FOR USER ACTION LOG............................................................................................................
     		global $mrr_activity_log;
     		$mrr_activity_log["notes"]="Stop ".$_POST['stop_id']." Completed. ";
     		$mrr_activity_log['load_handler_id']=$row_load_id['load_handler_id'];
     		$mrr_activity_log['stop_id']=$_POST['stop_id'];
     		
     		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$row_load_id['load_handler_id'],0,$_POST['stop_id'],"Stop ".$_POST['stop_id']." Completed.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
     		
     		//......................................................................................................................................................
     	     				
			$return_var = "
				<rslt>1</rslt>
				<ErrorMsg><![CDATA[".$err_msg."]]></ErrorMsg>
				<SQL><![CDATA[$sql]]></SQL>
				<DT>$_POST[linedate_completed] $_POST[linedate_completed_time]</DT>
				<StopGradeID><![CDATA[".$_POST['stop_grade_id']."]]></StopGradeID>
     			<StopGradeNote><![CDATA[".$_POST['stop_grade_note']."]]></StopGradeNote>
			";
		}
		else
		{
			$return_var = "
				<rslt>0</rslt>
				<ErrorMsg><![CDATA[".$err_msg."]]></ErrorMsg>
				<SQL><![CDATA[$sql]]></SQL>
				<DT>$_POST[linedate_completed] $_POST[linedate_completed_time]</DT>
				<StopGradeID><![CDATA[".$_POST['stop_grade_id']."]]></StopGradeID>
     			<StopGradeNote><![CDATA[".$_POST['stop_grade_note']."]]></StopGradeNote>
			";
		}
		
		if($load_id > 0)		mrr_dispatch_completion_updates($load_id,0);
		
		display_xml_response($return_var);
	}
	
	function load_handler_quick_create() {
		global $defaultsarray;
		global $datasource;
		
		if($_POST['customer_name'] != '') {
			// adding a new customer, add a quick customer
			$sql = "
				insert into customers
					(name_company,
					contact_email,
					phone_work,
					address1,
					address2,
					city,
					state,
					zip,
					active,
					deleted)
					
				values ('".sql_friendly($_POST['customer_name'])."',
					'".sql_friendly($_POST['customer_email'])."',
					'".sql_friendly($_POST['customer_phone'])."',
					'".sql_friendly($_POST['customer_address1'])."',
					'".sql_friendly($_POST['customer_address2'])."',
					'".sql_friendly($_POST['customer_city'])."',
					'".sql_friendly($_POST['customer_state'])."',
					'".sql_friendly($_POST['customer_zip'])."',
					1,
					0)
			";
			simple_query($sql);
			
			$_POST['customer_id'] = mysqli_insert_id($datasource);
			
			mrr_add_user_change_log($_SESSION['user_id'],$_POST['customer_id'],0,0,0,0,0,0,"Updated customer ".$_POST['customer_id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
			
			if($defaultsarray['sicap_integration'] == 1) sicap_update_customers($_GET['eid']);
		}
		
		//default settings used for budget items
     	$mrr_average_mpg=mrr_get_default_variable_setting('average_mpg');
          $mrr_billable_days_in_month=mrr_get_default_variable_setting('billable_days_in_month');
          $mrr_labor_per_hour=mrr_get_default_variable_setting('labor_per_hour');
          $mrr_labor_per_mile=mrr_get_default_variable_setting('labor_per_mile');
          $mrr_labor_per_mile_team=mrr_get_default_variable_setting('labor_per_mile_team');
          $mrr_local_driver_workweek_hours=mrr_get_default_variable_setting('local_driver_workweek_hours');
          $mrr_tractor_maint_per_mile=mrr_get_default_variable_setting('tractor_maint_per_mile');
          $mrr_trailer_maint_per_mile=mrr_get_default_variable_setting('trailer_maint_per_mile');
          
          $mrr_truck_accidents_per_mile=mrr_get_default_variable_setting('truck_accidents_per_mile');
     	$mrr_tires_per_mile=mrr_get_default_variable_setting('tires_per_mile');
     	$mrr_mileage_expense_per_mile=mrr_get_default_variable_setting('mileage_expense_per_mile');
     	$mrr_misc_expense_per_mile=mrr_get_default_variable_setting('misc_expense_per_mile');
     	
     	$mrr_cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
          $mrr_general_liability=mrr_get_option_variable_settings('General Liability');
          $mrr_liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
          $mrr_payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
          $mrr_rent=mrr_get_option_variable_settings('Rent');
          $mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
          $mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
          $mrr_trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
          $mrr_misc_expenses=mrr_get_option_variable_settings('Misc Expenses');
		
		
		$sql = "
			insert into load_handler
				(linedate_added,
				customer_id,
				created_by_id,
				budget_average_mpg,
				budget_days_in_month,
				budget_labor_per_hour,
				budget_labor_per_mile,
				budget_labor_per_mile_team,
				budget_driver_week_hours,
				budget_tractor_maint_per_mile,
				budget_trailer_maint_per_mile,
				budget_truck_accidents_per_mile,
				budget_tires_per_mile,
				budget_mileage_exp_per_mile,
				budget_misc_exp_per_mile,
				budget_cargo_insurance,
				budget_general_liability,
				budget_liability_damage,
				budget_payroll_admin,
				budget_rent,
				budget_tractor_lease,
				budget_trailer_exp,
				budget_trailer_lease,
				budget_misc_exp,
				budget_active_trucks,
				budget_active_trailers,
				budget_day_variance,
				billing_notes,
				driver_notes)
				
			values (now(),
				'".sql_friendly($_POST['customer_id'])."',
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($mrr_average_mpg)."',
				'".sql_friendly($mrr_billable_days_in_month)."',
				'".sql_friendly($mrr_labor_per_hour)."',
				'".sql_friendly($mrr_labor_per_mile)."',	
				'".sql_friendly($mrr_labor_per_mile_team)."',
				'".sql_friendly($mrr_local_driver_workweek_hours)."',	
				'".sql_friendly($mrr_tractor_maint_per_mile)."',
				'".sql_friendly($mrr_trailer_maint_per_mile)."',	
				'".sql_friendly($mrr_truck_accidents_per_mile)."',
				'".sql_friendly($mrr_tires_per_mile)."',	
				'".sql_friendly($mrr_mileage_expense_per_mile)."',
				'".sql_friendly($mrr_misc_expense_per_mile)."',	
				'".sql_friendly($mrr_cargo_insurance)."',
				'".sql_friendly($mrr_general_liability)."',	
				'".sql_friendly($mrr_liability_phy_damage)."',
				'".sql_friendly($mrr_payroll___admin)."',	
				'".sql_friendly($mrr_rent)."',
				'".sql_friendly($mrr_tractor_lease)."',	
				'".sql_friendly($mrr_trailer_expense)."',
				'".sql_friendly($mrr_trailer_lease)."',	
				'".sql_friendly($mrr_misc_expenses)."',				
				'".sql_friendly( get_active_truck_count() )."',
				'".sql_friendly( get_active_trailer_count() )."',
				'".sql_friendly( get_daily_cost(0,0) )."',
				'',
				'')
		";
		simple_query($sql);
		
		$load_id = mysqli_insert_id($datasource);
								
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Quick Create method used to make Load ".$load_id.". ";
		$mrr_activity_log['load_handler_id']=$load_id;
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,0,0,"Quick Create method used to make Load ".$load_id.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		//......................................................................................................................................................
		
		
		$return_var = "<rslt>1</rslt><LoadID>$load_id</LoadID><CustomerID>$_POST[customer_id]</CustomerID>";
		
		display_xml_response($return_var);
	}
	
	function add_dispatch_expense() {
		global $datasource;

		$sql = "
			insert into dispatch_expenses
				(linedate_added,
				added_by_user_id,
				dispatch_id,
				expense_type_id,
				expense_amount,
				expense_desc,
				deleted)
				
			values (now(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($_POST['dispatch_id'])."',
				'".sql_friendly($_POST['expense_type'])."',
				'".sql_friendly($_POST['expense_amount'])."',
				'".sql_friendly($_POST['expense_desc'])."',
				0)
		";
		simple_query($sql);
		
		$expense_id = mysqli_insert_id($datasource);
		
		$load_id = get_load_id_from_dispatch_id($_POST['dispatch_id']);
		update_origin_dest($load_id);
								
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Dispatch ".$_POST['dispatch_id']." Expenses Added. ";
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$_POST['dispatch_id'];
		//......................................................................................................................................................
				
		$return_var = "<rslt>1</rslt><ExpenseID>$expense_id</ExpenseID>";
		
		display_xml_response($return_var);
	}
	
	function load_dispatch_expenses() {
		$sql = "
			select dispatch_expenses.*,
				option_values.fvalue as expense_type
			
			from dispatch_expenses, option_values
			where dispatch_expenses.dispatch_id = '".sql_friendly($_POST['dispatch_id'])."'
				and dispatch_expenses.deleted = 0
				and dispatch_expenses.expense_type_id = option_values.id
			order by dispatch_expenses.linedate_added
		";
		$data = simple_query($sql);
		
		
		$return_var = '';
		if(!mysqli_num_rows($data)) {
			$return_var .= "No expenses found for this dispatch";
		} else {
			$return_var .= "
				<table class='section2' style='width:100%'>
				<tr>
					<td><b>Expense Type</b> ". show_help('Site Wide','Exspense Listing')."</td>
					<td><b>Description</b></td>
					<td align='right'><b>Amount</b></td>
				</tr>
			";
			$expense_total = 0;
			while($row = mysqli_fetch_array($data)) {
				$expense_total += $row['expense_amount'];
				$return_var .= "
					<tr id='row_expense_$row[id]'>
						<td>$row[expense_type]</td>
						<td>$row[expense_desc]</td>
						<td align='right'>".money_format('',$row['expense_amount'])."</td>
						<td><a href='javascript:delete_dispatch_expense($row[id])'><img src='images/delete_sm.gif' title='Delete Expense' alt='Delete Expense' style='border:0'></a></td>
					</tr>
				";
			}
			$return_var .= "
				<tr>
					<td colspan='5'><hr></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td align='right'><b>Total Expenses: ".money_format('',$expense_total)."</b></td>
				</tr>
				</table>
			";
		}
		
		$return_var = "<rslt>1</rslt><HTML><![CDATA[$return_var]]></HTML>";
		
		display_xml_response($return_var);
	}
	
	function delete_dispatch_expense() {
		$sql = "
			update dispatch_expenses
			set deleted = 1
			where id = '".sql_friendly($_POST['expense_id'])."'
		";
		simple_query($sql);
		
		$sql = "
			select dispatch_id
			
			from dispatch_expenses
			where id = '".sql_friendly($_POST['expense_id'])."'
		";
		$data_dispatch_id = simple_query($sql);
		$row_dispatch_id = mysqli_fetch_array($data_dispatch_id);
		
	
		$load_id = get_load_id_from_dispatch_id($row_dispatch_id['dispatch_id']);
		update_origin_dest($load_id);
								
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Dispatch ".$row_dispatch_id['dispatch_id']." Expenses Removed. ";
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$row_dispatch_id['dispatch_id'];
		//......................................................................................................................................................
				
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);
	}
	
	function load_dispatchs() {
		
		
		if($_POST['load_id'] == '' || $_POST['load_id'] == 0) {
			// no load specified, don't go any further
			$return_var = "<rslt>1</rslt>";
			
			display_xml_response($return_var);
			return;
		}
		
		
		$actual_bill_customer=0;	
		$flat_fuel_rate_amount=0;
		$actual_total_cost=0;
		$load_profit=0;
		
		$sql="
			select 
				actual_bill_customer,
				flat_fuel_rate_amount,
				actual_total_cost,
				(load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost) as load_profit
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data))
		{
			$actual_bill_customer=$row['actual_bill_customer'];	
			$flat_fuel_rate_amount=$row['flat_fuel_rate_amount'];
			$actual_total_cost=$row['actual_total_cost'];
			$load_profit=$row['load_profit'];
		} 
		
		
		$sql = "
			select id,pcm_miles,
				miles,miles_deadhead,
				daily_run_otr,daily_run_hourly,
				miles_deadhead_hourly,loaded_miles_hourly
			
			from trucks_log
			where trucks_log.load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and trucks_log.deleted = 0
			
			order by trucks_log.linedate_pickup_eta, trucks_log.linedate, trucks_log.id
		";
		$data_dispatch = simple_query($sql);	
		while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
		{
			//$row_dispatch['miles']
			//$row_dispatch['miles_deadhead']
			//$row_dispatch['pcm_miles']
			if($row_dispatch['pcm_miles'] ==0)
			{
				$mrr_miles=0;
				$stop_cntr=0;
				$dh_miles=0;
				$sqlx = "
          			select id,pcm_miles		
          			from load_handler_stops
          			where load_handler_id = '".sql_friendly($_POST['load_id'])."' and trucks_log_id='".sql_friendly($row_dispatch['id'])."'
          				and deleted = 0
          			order by linedate_pickup_eta
          		";
          		$datax = simple_query($sqlx);
          		while($rowx = mysqli_fetch_array($datax)) 
          		{
          			$mrr_miles+=$rowx['pcm_miles'];
          			
          			//if($stop_cntr==0)		$dh_miles=$rowx['pcm_miles'];
          			$stop_cntr++;
          		}
				
				if($mrr_miles > 0)
				{	
					// || $row_dispatch['miles_deadhead_hourly'] == 0					miles_deadhead='".sql_friendly($dh_miles)."',
					// || $row_dispatch['miles_deadhead'] == 0						miles_deadhead_hourly='".sql_friendly($dh_miles)."',
					$sqlu = "
               			update trucks_log set               				
               				".(($row_dispatch['daily_run_otr'] > 0 && $row_dispatch['miles'] == 0)                   ? "miles='".sql_friendly(($mrr_miles - $dh_miles))."',"               : "")."
               				".(($row_dispatch['daily_run_hourly'] > 0 && $row_dispatch['loaded_miles_hourly'] == 0)  ? "loaded_miles_hourly='".sql_friendly(($mrr_miles - $dh_miles))."'," : "")."
               				pcm_miles='".sql_friendly($mrr_miles)."'
               			where id = '".sql_friendly($row_dispatch['id'])."'
               		";
					simple_query($sqlu);	
				}			
			}
		}		
		
				
		$sql = "
			select trucks_log.*,
				trucks.name_truck,
				trailers.trailer_name,
				concat(drivers.name_driver_first, ' ', drivers.name_driver_last) as driver_name
			
			from trucks_log
				left join trucks on trucks_log.truck_id = trucks.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join drivers on drivers.id = trucks_log.driver_id
			where trucks_log.load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and trucks_log.deleted = 0
			
			order by trucks_log.linedate_pickup_eta, trucks_log.linedate, trucks_log.id
		";
		$data_dispatch = simple_query($sql);	
		$data_dispatch2 = simple_query($sql);
		
		$html = "
			<table width='100%'>
			<tr>
				<td><b>ID</b> ". show_help('Site Wide','Load Dispatch List')."</td>
				<td><b>Truck</b></td>
				<td><b>Trailer</b></td>
				
				<td><b>Driver</b></td>
				<td align='right'><b>PC*M</b></td>
				<td align='right'><b>Miles</b></td>
				<td align='right'><b>Deadhead</b></td>
				<td>&nbsp;</td>
				<td><b>Origin</b></td>
				<td><b>Dest</b></td>
				<td><b>Date</b></td>
				<td align='right'><b>Cost</b></td>
				<td align='right'><b>Profit</b></td>
			</tr>
		";		//<td><b>Switch Notes</b></td>
		if(!isset($data_dispatch) || !mysqli_num_rows($data_dispatch) ) { 
			$html .= "
				<tr>
					<td colspan='11'>
						No dispatches associated with this load yet
					</td>
				</tr>
			";
		} else {
			$total_loaded_miles = 0;
			$total_deadhead_miles = 0;
			$last_dispatch_id = 0;
			$total_pcm_miles = 0;
			$total_profit=0;
			$total_cost=0;
			$extra_cost=0;
			
			
			//determine "primary" dispatch for cost/profit display.
			$prime_dispatch_id=0;
			$prime_dispatch_miles=0;
			
			while($row_dispatch2 = mysqli_fetch_array($data_dispatch2)) 
			{					
				$prime_dispatch_hours=0;
				//$prime_dispatch_hours=$row_dispatch2['hours_worked'];		//$row_dispatch['labor_per_hour']
				
				if($prime_dispatch_id==0)
				{
					$prime_dispatch_id=$row_dispatch2['id'];
					$prime_dispatch_miles=($row_dispatch2['miles'] + $row_dispatch2['miles_deadhead'] + $row_dispatch2['pcm_miles']);
					$total_cost=$row_dispatch2['cost'];
					//$total_profit=$row_dispatch2['profit'];
				}
				elseif(($row_dispatch2['miles'] + $row_dispatch2['miles_deadhead'] + $row_dispatch2['pcm_miles']) > $prime_dispatch_miles || $prime_dispatch_hours > 0)
				{
					$prime_dispatch_miles=($row_dispatch2['miles'] + $row_dispatch2['miles_deadhead'] + $row_dispatch2['pcm_miles']);
					$prime_dispatch_id=$row_dispatch2['id'];
					$total_cost=$row_dispatch2['cost'];
					//$total_profit=$row_dispatch2['profit'];
				}	
				else
				{
					//$extra_cost+=$row_dispatch2['cost'];
				}							
			}
			//........................totals found for profit and cost
			
			$running_profit=0;
					
			while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
			{
				$total_loaded_miles += $row_dispatch['miles'];
				$total_deadhead_miles += $row_dispatch['miles_deadhead'];
				$total_pcm_miles += $row_dispatch['pcm_miles'];
				$switch_notes="";
				//$total_cost+=$row_dispatch['cost'];
				//$total_profit+=$row_dispatch['profit'];
				
				$use_tot_cost="&nbsp;";
				$use_tot_profit="&nbsp;";
				$extra_cost=0;
				$extra_profit=0;
				
				$prime_dispatch_hours=$row_dispatch['hours_worked'];		//$row_dispatch['labor_per_hour']
				
				$total_profit=mrr_figure_profit_for_load_dispatch($_POST['load_id'],$row_dispatch['id']);		//see functions.php file for this profit.
                    
                    $use_tot_profit_mrr=$total_profit;
				if($total_profit!=0)
				{
					$use_tot_profit="$".number_format($total_profit,2)."";
                         $use_tot_profit_mrr=$total_profit;
					
					$running_profit+=$total_profit;
				}
				
				if($prime_dispatch_id==$row_dispatch['id'])
				{
					if($total_profit <= 0)
					{
						$total_profit=$row_dispatch['profit'];
						//$running_profit+=$total_profit;
						$use_tot_profit="$".number_format($total_profit,2)."";
                              
                              $use_tot_profit_mrr=$total_profit;
					}					
					
					$use_tot_cost="$".number_format($total_cost,2)."";
					//$use_tot_profit="$".number_format($total_profit,2)."";	
				}
				else
				{
					$extra_cost=$row_dispatch['cost'];
					$running_profit-=$extra_cost;
					$extra_profit=$running_profit;
                         
                         $use_tot_profit_mrr=$extra_profit;
				}
								
				/*
				$sql2="
					select trailer_switched.*,
						(select ifnull(trailers.trailer_name,'None') from trailers where trailers.id=trailer_switched.old_trailer_id) as mrr_trailer1,
						(select ifnull(trailers.trailer_name,'None') from trailers where trailers.id=trailer_switched.new_trailer_id) as mrr_trailer2  
					from trailer_switched 
					where deleted=0 
						and dispatch_id='".sql_friendly($row_dispatch['id'])."'
					order by stop_id asc
				";
				$data2=simple_query($sql2);
				while($row2=mysqli_fetch_array($data2))
				{
					$stamp=date("m/d/Y G:i",strtotime($row2['linedate']));	
					$tname1=$row2['mrr_trailer1'];
					$tname2=$row2['mrr_trailer2'];
					$switch_notes.="<div id='mrr_switcher_".$row2['id']."' class='mrr_trailer_switch'>
									".$tname1." to <span title='Date of trailer switch/drop was ".$stamp."'>".$tname2."</span> 
									<span class='mrr_link_like_on' onClick='mrr_clear_trailer_switch(".$row2['id'].");'><img src='/images/delete_sm.gif' border='0' height='16' width='12' alt='X'></span>
								</div>";
				}				
				*/
								
				$extra_color="00CC00";
				if($extra_profit < 0)		$extra_color="CC0000";				
								
				$html .= "
					<tr>
						<td valign='top'><a href='javascript:void(0)' onclick='manage_dispatch($row_dispatch[id])' ".($row_dispatch['dispatch_completed'] ? "class='dispatch_completed'" : "").">$row_dispatch[id]</a></td>
						<td valign='top'>$row_dispatch[name_truck]</td>
						<td valign='top'>$row_dispatch[trailer_name]</td>
						
						<td valign='top'>$row_dispatch[driver_name]</td>
						<td valign='top' align='right'>".number_format($row_dispatch['pcm_miles'])."</td>
						<td valign='top' align='right'>".number_format($row_dispatch['miles'])."</td>
						<td valign='top' align='right'>".number_format($row_dispatch['miles_deadhead'])."</td>
						<td valign='top'></td>
						<td valign='top'>$row_dispatch[origin], $row_dispatch[origin_state]</td>
						<td valign='top'>$row_dispatch[destination], $row_dispatch[destination_state]</td>
						<td valign='top'>".date("n-j-Y", strtotime($row_dispatch['linedate']))."</td>
						<td valign='top' align='right'>".($use_tot_cost!="&nbsp;" ? "".$use_tot_cost."" : "<span style='color:#".$extra_color.";'><i>$".number_format($extra_cost,2)."</i></span>")."</td>
						<td valign='top' align='right'>".($use_tot_profit!="&nbsp;" ? "".$use_tot_profit."" : "<span style='color:#".$extra_color.";'><i>$".number_format($extra_profit,2)."</i></span>")."</td>
					</tr>
				";	//<td valign='top'>".$switch_notes."</td>
                    
                    $sql2u="
					update trucks_log set
						profit='".$use_tot_profit_mrr."'
					where id='".sql_friendly($row_dispatch['id'])."'
				";
                    simple_query($sql2u);
			}
			
			/*
			$actual_bill_customer=0;	
			$flat_fuel_rate_amount=0;
			$actual_total_cost=0;
			$load_profit=0;
			*/
						
			$html .= "
				<tr>
					<td colspan='4'></td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_pcm_miles)."</td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_loaded_miles)."</td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_deadhead_miles)."</td>
					<td colspan='4'></td>
					<td align='right' style='border-top:1px black solid'>$".number_format($actual_total_cost,2)."</td>
					<td align='right' style='border-top:1px black solid'>$".number_format($load_profit,2)."</td>
				</tr>
			";
				//<td align='right' style='border-top:1px black solid'>$".number_format($total_cost,2)."</td>					
				//<td align='right' style='border-top:1px black solid'>$".number_format($total_profit,2)."</td>
		}
		$html .= "</table>";
				
		$return_var = "<rslt>1</rslt><HTML><![CDATA[$html]]></HTML>";
		
		display_xml_response($return_var);
	}
	
	function search_shipper_name() 
	{		
		$stop_type=0;
		
		if(isset($_GET['mrr']) && $_GET['mrr']==1)	$stop_type=1;
		if(isset($_GET['mrr']) && $_GET['mrr']==2)	$stop_type=2;
		
		if(strlen($_GET['q']) >= 3)
     	{     		
     		$sql = "
     			select distinct(shipper_name)
     			from load_handler_stops, load_handler
     			where load_handler.id = load_handler_stops.load_handler_id
     				and shipper_name like '".sql_friendly($_GET['q'])."%'
     				and load_handler_stops.deleted = 0
     				and load_handler.deleted = 0
     				and load_handler_stops.ignore_address = 0
     				".($stop_type > 0 ? "and load_handler_stops.stop_type_id='".$stop_type."'" : "")."
     			order by shipper_name, shipper_address1, id desc
     			limit 100
     		";		     		
     		$data = simple_query($sql);
     		
     		$last_shipper_check = '';     		
     		while($row = mysqli_fetch_array($data)) 
     		{
     			/*
     			$shipper_check = "$row[shipper_name] $row[shipper_address1]";
     			// create a simple bit of code to check for and remove duplicates
     			if($last_shipper_check != $shipper_check) 
     			{
     				$last_shipper_check = $shipper_check;
     				echo "$row[shipper_name]|$row[shipper_address1] ($row[shipper_city], $row[shipper_state])|$row[id]\n";
     			}
     			*/
     			echo "$row[shipper_name]\n";
     		}
		}
	}
	
	function search_stop_address() {
		
		if(strlen($_GET['q']) >= 3)
     	{     		
     		$sql = "
     			select load_handler_stops.*
     				
     			from load_handler_stops, load_handler
     			where load_handler.id = load_handler_stops.load_handler_id
     				and shipper_name like '%".sql_friendly($_GET['q'])."%'
     				and load_handler_stops.deleted = 0
     				and load_handler.deleted = 0
     				and load_handler_stops.ignore_address = 0
     				and load_handler_stops.linedate_pickup_eta >= DATE_SUB(NOW(),INTERVAL 365 DAY)
     			order by shipper_name, shipper_address1, id desc
     			limit 200
     		";		
     		
     		$data = simple_query($sql);
     		
     		$last_shipper_check = '';
     		
     		while($row = mysqli_fetch_array($data)) {
     			$shipper_check = "$row[shipper_name] $row[shipper_address1]";
     			// create a simple bit of code to check for and remove duplicates
     			if($last_shipper_check != $shipper_check) {
     				$last_shipper_check = $shipper_check;
     				echo "$row[shipper_name]|$row[shipper_address1] ($row[shipper_city], $row[shipper_state])|$row[id]\n";
     			}
     		}
		}
	}
	
	function load_address_by_stop_id() {
		$sql = "
			select *
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$return_var = "
			<rslt>1</rslt>
			<Address1><![CDATA[$row[shipper_address1]]]></Address1>
			<Address2><![CDATA[$row[shipper_address2]]]></Address2>
			<City><![CDATA[$row[shipper_city]]]></City>
			<State><![CDATA[$row[shipper_state]]]></State>
			<Zip><![CDATA[$row[shipper_zip]]]></Zip>
			<Directions><![CDATA[$row[directions]]]></Directions>
			<SpecNotes><![CDATA[$row[stop_spec_notes]]]></SpecNotes>
			<Phone><![CDATA[$row[stop_phone]]]></Phone>
		";
		
		display_xml_response($return_var);	
	}
	function mrr_fetch_spec_notes()
	{
		$dater=trim($_POST['pickup_date']);
		$timer=trim($_POST['pickup_time']);
		
		$use_date="";
		if($dater!="")	
		{
			$use_date=$dater;
			
			if($timer!="")		$use_date.=" ".$timer.":00";		else		$use_date.=" 00:00:00";	
		}
		
		//convert to upper case for comparison...
		$name=strtoupper(trim($_POST['name']));
		$addr1=strtoupper(trim($_POST['address1']));
		$city=strtoupper(trim($_POST['city']));
		$state=strtoupper(trim($_POST['state']));
		$zip=strtoupper(trim($_POST['zip_code']));
				
		$sql = "
			select *			
			from load_handler_stops
			where deleted=0
				and UCASE(shipper_name)='".sql_friendly($name)."'
				and UCASE(shipper_address1)='".sql_friendly($addr1)."'
				and UCASE(shipper_city)='".sql_friendly($city)."'
				and UCASE(shipper_state)='".sql_friendly($state)."'
				and UCASE(shipper_zip)='".sql_friendly($zip)."'
				".( trim($use_date)!="" ? " and linedate_pickup_eta < '".date("Y-m-d H:i",strtotime(trim($use_date))).":00'" : "")."
				
			order by linedate_pickup_eta desc,id desc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{		
     		$return_var = "
     			<rslt>1</rslt>
     			<StopID><![CDATA[$row[id]]]></StopID>
     			<Address1><![CDATA[$row[shipper_address1]]]></Address1>
     			<Address2><![CDATA[$row[shipper_address2]]]></Address2>
     			<City><![CDATA[$row[shipper_city]]]></City>
     			<State><![CDATA[$row[shipper_state]]]></State>
     			<Zip><![CDATA[$row[shipper_zip]]]></Zip>
     			<Directions><![CDATA[$row[directions]]]></Directions>
     			<SpecNotes><![CDATA[$row[stop_spec_notes]]]></SpecNotes>
     			<Phone><![CDATA[$row[stop_phone]]]></Phone>
     		";
		}
		else
		{
			$return_var = "
     			<rslt>1</rslt>
     			<StopID><![CDATA[0]]></StopID>
     			<Address1><![CDATA[]]></Address1>
     			<Address2><![CDATA[]]></Address2>
     			<City><![CDATA[]]></City>
     			<State><![CDATA[]]></State>
     			<Zip><![CDATA[]]></Zip>
     			<Directions><![CDATA[]]></Directions>
     			<SpecNotes><![CDATA[]]></SpecNotes>
     			<Phone><![CDATA[]]></Phone>
     		";	
		}
		display_xml_response($return_var);	
	}
	
	function clear_address_history() {
		// clear out a specific address from the load_handler_stops (in case of a typo, or other error) 
		
		$sql = "
			select shipper_name,
				shipper_address1
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['address_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);		
		
		$sql = "
			update load_handler_stops set
				ignore_address = 1
			where shipper_name = '".sql_friendly($row['shipper_name'])."'
				and shipper_address1 = '".sql_friendly($row['shipper_address1'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Address History ".$_POST['address_id']." Cleared. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");	
	}
	
	function load_driver_expenses() {
		$sql = "
			select *			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data_info = simple_query($sql);
		$row_info = mysqli_fetch_array($data_info);
		
		$sql = "
			select drivers_expenses.*,
				option_values.fvalue as expense_type
			
			from drivers_expenses
				left join option_values on option_values.id = drivers_expenses.expense_type_id
			where drivers_expenses.driver_id = '".sql_friendly($_POST['driver_id'])."'
				and drivers_expenses.deleted = 0
			order by drivers_expenses.linedate desc
			limit 20
		";
		$data = simple_query($sql);
		
		$html = "
			<table class='admin_menu2'>
			<tr>
				<td colspan='7'>
					<div class='section_heading'>
						Driver Expenses ". show_help('Site Wide','Driver Expenses')."<br>
						Driver: $row_info[name_driver_first] $row_info[name_driver_last]
					</div>
					
				</td>
			</tr>
		";
		
		if(!mysqli_num_rows($data)) {
			$html .= "
				<tr>
					<td colspan='7'>
						<span class='alert'>No expenses found for this driver</span>
					</td>
				</tr>
			";
		} else {
		
			$html .= "
				<tr>
					<th>Type</th>
					<th>Date</th>
					<th align='right'>Cost</th>
					<th align='right'>Billable</th>
					<th>Account</th>
					<th>Description</th>
					<th></th>
				</tr>
			";
			while($row = mysqli_fetch_array($data)) {
				
				$account=mrr_get_coa_chart_name_by_id($row['chart_id']);
				/*				
				if($row['chart_id'] > 0)
				{
					$results=mrr_get_coa_list($row['chart_id'],'');		//67000	//first arg is $chart_id, second arg is $chart_number	
		
                    	foreach($results as $key2 => $value2 )
                    	{
                    		if($key2=="ChartEntry")
                    		{
                         		foreach($value2 as $key => $value )
                    			{         		
                              		$prt=trim($key);		$tmp=trim($value);
                              		//if($prt=="ID")		$chart_id=$tmp;
                              		if($prt=="Name")		$account=$tmp;
                              		//if($prt=="Number")	$chart_acct=$tmp;                              		
                         		}//end for loop for each chart entry
                    		}//end if
                    	}//end for loop for each result returned
				}//end if ID check		$row['chart_id']	
				*/
				
				$html .= "
					<tr id='driver_expense_entry_$row[id]'>
						<td>$row[expense_type]</td>
						<td>".date("M j, Y", strtotime($row['linedate']))."</td>
						<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $".money_format('',$row['amount'])."</td>
						<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $".money_format('',$row['amount_billable'])."</td>
						<td>".$account."</td>
						<td>$row[desc_long]</td>
						<td>
							<a href='javascript:void(0)' onclick='confirm_delete_driver_expense($row[id])'><img src='images/delete_small.png' alt='Delete driver expense' title='Delete driver expense' style='border:0'>
						</td>
					</tr>
				";
			}
		}
		$html .= "</table>";
		
		display_xml_response("<rslt>1</rslt><html><![CDATA[$html]]></html>");	
	}
	
	function add_driver_expense() {
		global $datasource;

		if(!isset($_POST['driver_expense_id'])) $_POST['driver_expense_id'] = 0;
		
		if($_POST['driver_expense_id'] == 0) {
			$sql = "
				insert into drivers_expenses
					(linedate_added,
					created_by_user_id)
					
				values (now(),
					'".sql_friendly($_SESSION['user_id'])."')
			";
			simple_query($sql);
			
			$_POST['driver_expense_id'] = mysqli_insert_id($datasource);
		}
		
		$use_chart_id=mrr_get_coa_chart_id_by_name($_POST['mrr_chart_name']);		//$_POST['mrr_chart_id']
		$exptype=$_POST['expense_type_id'];
		if($use_chart_id==0)
		{	//attempt to assign the ID based on the general type.			
			if($exptype==27)		$use_chart_id=1707;		//generic lease drivers
			if($exptype==34)		$use_chart_id=1707;		//generic lease drivers
			if($exptype==142)		$use_chart_id=1707;		//generic lease drivers
			
			if($exptype==26)		$use_chart_id=1716;		//Misc Exp - All trucks
			if($exptype==103)		$use_chart_id=1726;		//Tolls
		}
		
		$sql = "
			update drivers_expenses
			set driver_id = '".sql_friendly($_POST['driver_id'])."',
				linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."',
				desc_long = '".sql_friendly($_POST['desc_long'])."',
				expense_type_id = '".sql_friendly($_POST['expense_type_id'])."',
				amount = '".sql_friendly($_POST['amount'])."',
				amount_billable = '".sql_friendly($_POST['amount_billable'])."',
				chart_id = '".sql_friendly($use_chart_id)."',
				payroll = '".(isset($_POST['payroll']) && $_POST['payroll'] == '1' ? '1' : '0')."'
			
			where id = '".sql_friendly($_POST['driver_expense_id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Added Driver ".$_POST['driver_id']." Expense. ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function delete_driver_expense() {
		$sql = "
			update drivers_expenses
			
			set deleted = 1
			where id = '".sql_friendly($_POST['driver_expense_id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Driver Expense ".$_POST['driver_expense_id']." Removed. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function get_daily_cost_ajax() {
		if(!isset($_POST['truck_id'])) 	$_POST['truck_id'] = 0;
		if(!isset($_POST['trailer_id'])) 	$_POST['trailer_id'] = 0;
		if(!isset($_POST['load_id'])) 	$_POST['load_id'] = 0;
		if(!isset($_POST['dispatch_id'])) 	$_POST['dispatch_id'] = 0;
		
		$daily_cost = get_daily_cost($_POST['truck_id'], $_POST['trailer_id']);
		$mrr_daily_cost=mrr_quick_and_easy_daily_cost($_POST['dispatch_id'] ,1);		//if dispatch is zero,cost will not pull up 
				
		$rval="
			<DailyCost>".$daily_cost."</DailyCost>
			<TruckCost>".$mrr_daily_cost['truck_cost']."</TruckCost>
			<TrailerCost>".$mrr_daily_cost['trailer_cost']."</TrailerCost>
			<DailyShow>".$mrr_daily_cost['daily_cost']."</DailyShow>
		";
		display_xml_response("<rslt>1</rslt>$rval");
	}
	
	function save_odometer_reading() {
		$sql = "
			insert trucks_odometer
				(truck_id,
				linedate_added,
				linedate,
				odometer)
				
			values ('".sql_friendly($_POST['truck_id'])."',
				now(),
				'".date("Y-m-d", strtotime($_POST['linedate']))."',
				'".sql_friendly($_POST['odometer'])."')
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Truck ".$_POST['truck_id']." Odometer Reading added. ";
		$mrr_activity_log['truck_id']=$_POST['truck_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,$_POST['truck_id'],0,0,0,0,"Truck ".$_POST['truck_id']." Odometer Reading added.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function truck_odometer_alert() {
		// we're within 4 days of the end of the month, start prompting for odometer readings

		$disp_limit = 100000;
		if(isset($_POST['disp_limit'])) $disp_limit = $_POST['disp_limit'];

		$sql = "
			select *
			
			from trucks
			where active = 1
				and deleted = 0
				and trucks.id not in (select trucks_odometer.truck_id from trucks_odometer where deleted = 0 and linedate >= '".date("Y-m-1")."' and linedate <= '".date("Y-m-t")."')
			order by name_truck
		";
		$data_trucks = simple_query($sql);

		$return_var = "<b>".mysqli_num_rows($data_trucks)." truck(s) need odometer readings</b><br>";

		$counter = 0;
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			$counter++;
			if($counter > $disp_limit) {
				break;
			}
			$return_var .= "<a href='javascript:void(0)' onclick=\"enter_odo($row_truck[id],'$row_truck[name_truck]')\">$row_truck[name_truck]</a><br>";
		}

		display_xml_response("<rslt>1</rslt><TruckList><![CDATA[$return_var]]></TruckList><TruckCount>".mysqli_num_rows($data_trucks)."</TruckCount>");
	}
	
	function mrr_update_history_truck_value()
	{		
		$id=(int) $_POST['truck_id'];
		$last=trim($_POST['old_value']);				$last=str_replace(",","",str_replace("$","",$last));
		$new=trim($_POST['new_value']);				$new=str_replace(",","",str_replace("$","",$new));
		$res=0;
		$sqlu="";
		
		if($id > 0 && trim($new)!="")
		{	// && trim($new)!="0.00" && trim($new)!="0"
			$sqlu="
				update equipment_history set 
					equipment_value='".sql_friendly($new)."'
				where equipment_type_id = 1 and equipment_id = '".sql_friendly($id)."' and deleted = 0 and equipment_id > 0
				order by id desc
				limit 1
			";
			simple_query($sqlu);
			
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Updated Truck Value from $".$last." to $".$new.".";
			$mrr_activity_log['truck_id']=$id;
			
			mrr_add_user_change_log($_SESSION['user_id'],0,0,$truck_id,0,0,0,0,"Updated Truck Value from $".$last." to $".$new.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
					
			//......................................................................................................................................................
			$res=1;
		}
		display_xml_response("<rslt>".$res."</rslt><truck>".$id."</truck><old><![CDATA[".$last."]]></old><new><![CDATA[".$new."]]></new><sql><![CDATA[".$sqlu."]]></sql>");		
	}
	
	
	function load_odometer_history() {
		$sql = "
			select *
			
			from trucks
			where id = '".sql_friendly($_POST['truck_id'])."'
		";
		$data_truck = simple_query($sql);
		$row_truck = mysqli_fetch_array($data_truck);
		
		$sql = "
			select *
			
			from trucks_odometer
			where truck_id = '".sql_friendly($_POST['truck_id'])."'
				and deleted = 0
			order by linedate desc
			limit 1
		";
		$data_odom = simple_query($sql);
		$row_odom = mysqli_fetch_array($data_odom);
		
		$sql = "
			select *
			
			from trucks_odometer
			where truck_id = '".sql_friendly($_POST['truck_id'])."'
				and deleted = 0
			order by linedate desc
			limit 12
		";
		$data = simple_query($sql);
		
		if(!isset($row_odom['odometer']))		$row_odom['odometer']=0;
		
		$return_var = "
			<table class='admin_menu1' style='width:350px'>
			<tr>
				<td colspan='3' class='border_bottom'><div class='section_heading'>Odometer History - 
						<a href='javascript:void(0)' onclick=\"enter_odo($_POST[truck_id],'$row_truck[name_truck]',$row_odom[odometer])\">Add Odometer Reading</a>
						  ". show_help('Site Wide','Odometer History')."
				</div></td>
			</tr>

			<tr>
				<td><b>Date</b></td>
				<td><b>Odometer</b></td>
				<td></td>
			</tr>
			
		";
		while($row = mysqli_fetch_array($data))	{
			
			$del_line="<a href='javascript:void(0)' onclick='confirm_delete_odometer_reading($row[id])' class='mrr_delete_access'><img src='images/delete_sm.gif' style='border:0'></a>";
			if(isset($_POST['delete_blocker']) && $_POST['delete_blocker'] > 0)	$del_line="";
			
			$return_var .= "
				<tr>
					<td>".date("M j, Y", strtotime($row['linedate']))."</td>
					<td>".number_format($row['odometer'])."</td>
					<td>".$del_line."</td>
				</tr>
			";
		}		
		
		$return_var .= "</table>
			<script type='text/javascript'>
				$('#odometer_date_entry').datepicker();
			</script>
		";
		
		display_xml_response("<rslt>1</rslt><OdometerHistory><![CDATA[$return_var]]></OdometerHistory>");
	}
	
	function delete_odometer_entry() {
		$sql = "
			update trucks_odometer
			set deleted = 1
			where id = '".sql_friendly($_POST['odometer_entry_id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Odometer Reading ".$_POST['odometer_entry_id']." Removed. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function load_driver_unavailable() {
		$sql = "
			select *
			
			from drivers_unavailable
			where driver_id = '".sql_friendly($_POST['driver_id'])."'
				and deleted = 0
			
			order by linedate_start
		";
		$data = simple_query($sql);
		
		$res=mrr_validate_driver_truck_trailer_attachment($_POST['driver_id']);
		//echo $res['table'];
		//echo "<br>Driver ".$mrr_driver_id." Validity is ".$res['valid']."<br>";
		
		$new_un="<a href='javascript:add_driver_unavailable($_POST[driver_id])'><img src='images/add.gif' style='border:0'> Add new scheduled time off</a>";
		/*
		if($res['valid']=="false")
		{
			$new_un="<b>Driver is already Attached to Truck/Trailer.</b>";
		}
		*/
		$html = "
			<table class='admin_menu2' width='400'>
			<tr>
				<td colspan='4'>
					<div class='section_heading'>
						Scheduled Time Off ". show_help('Site Wide','Driver Unavailable History')."<br>
					</div>
					
				</td>
			</tr>
			<tr>
				<td colspan='4'>".$new_un."</td>
			</tr>
		";
		
		if(!mysqli_num_rows($data)) {
			$html .= "
				<tr>
					<td colspan='4'>
						<span class='alert'>No entries found for this driver</span>
					</td>
				</tr>
			";
		} else {
		
			$html .= "
				<tr>
					<th>Date Start</th>
					<th>Date End</th>
					<th></th>
					<th>Reason</th>
				</tr>
			";
			while($row = mysqli_fetch_array($data)) {
				$html .= "
					<tr id='driver_unavailability_entry_$row[id]'>
						<td valign='top'>".date("M j, Y", strtotime($row['linedate_start']))."</td>
						<td valign='top'>".date("M j, Y", strtotime($row['linedate_end']))."</td>
						<td valign='top'><a href='javascript:void(0)' onclick='confirm_delete_driver_unavailable($row[id])'><img src='images/delete_small.png' alt='Delete driver unavailablity' title='Delete driver unavailablity' style='border:0'></a></td>
						<td valign='top' width='200'>".trim($row['reason_unavailable'])."</td>
					</tr>
				";
			}
		}
		$html .= "</table>";
		
		display_xml_response("<rslt>1</rslt><html><![CDATA[$html]]></html>");	
	}
	
	function add_driver_unavailability() 
     {
          if($_POST['unavailable_interval'] > 0)
          {
               $wkday_name="Sunday";
               if($_POST['unavailable_interval'] == 1) {    $wkday_name="Monday";    }
               if($_POST['unavailable_interval'] == 2) {    $wkday_name="Tuesday";    }
               if($_POST['unavailable_interval'] == 3) {    $wkday_name="Wednesday";    }
               if($_POST['unavailable_interval'] == 4) {    $wkday_name="Thursday";    }
               if($_POST['unavailable_interval'] == 5) {    $wkday_name="Friday";    }
               if($_POST['unavailable_interval'] == 6) {    $wkday_name="Saturday";    }
     
               $endDate = strtotime($_POST['linedate_end']);
               for($i = strtotime($wkday_name, strtotime($_POST['linedate_start'])); $i <= $endDate; $i = strtotime('+1 week', $i)) 
               {
                    $mrr_dater=date('Y-m-d', $i);
     
                    $sql = "
                         insert into drivers_unavailable
                              (driver_id,
                              deleted,
                              linedate_added,
                              linedate_start,
                              linedate_end,
                              reason_unavailable,
                              recurring_wk_day,
                              added_by)
                              
                         values ('" . sql_friendly($_POST['driver_id']) . "',
                              0,
                              now(),
                              '" . $mrr_dater. "',
                              '" . $mrr_dater. "',
                              '".$wkday_name."-".sql_friendly(trim($_POST['c_reason']))."',
                              '".$_POST['unavailable_interval']."',
                              '" . sql_friendly($_SESSION['user_id']) . "')
                    ";
                    simple_query($sql);
               }               
          }
          else 
          {
               $sql = "
                    insert into drivers_unavailable
                         (driver_id,
                         deleted,
                         linedate_added,
                         linedate_start,
                         linedate_end,
                         reason_unavailable,
                         recurring_wk_day,
                         added_by)
                         
                    values ('" . sql_friendly($_POST['driver_id']) . "',
                         0,
                         now(),
                         '" . date("Y-m-d", strtotime($_POST['linedate_start'])) . "',
                         '" . date("Y-m-d", strtotime($_POST['linedate_end'])) . "',
                         '" . sql_friendly(trim($_POST['c_reason'])) . "',
                         '0',
                         '" . sql_friendly($_SESSION['user_id']) . "')
		     ";
               simple_query($sql);
          }				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Driver ".$_POST['driver_id']." marked Unavailable ".($_POST['unavailable_interval'] > 0 ? "...Recurring Intervals." : "").". ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Driver ".$_POST['driver_id']." marked Unavailable.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function delete_driver_unavailability() {
		
		$driver_id=0;
		$sql = "
			select driver_id				
			from drivers_unavailable
			where id = '".sql_friendly($_POST['id'])."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$driver_id=$row['driver_id'];
		}
		$sql = "
			update drivers_unavailable
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Driver ".$driver_id." Unavailable Record ".$_POST['id']." Removed. ";
		$mrr_activity_log['driver_id']=$driver_id;
		mrr_add_user_change_log($_SESSION['user_id'],0,$driver_id,0,0,0,0,0,"Driver ".$driver_id." Unavailable Record ".$_POST['id']." Removed.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
			
		display_xml_response("<rslt>1</rslt>");
	}
	
	function mrr_edit_driver_unavailability() {
		$id=$_POST['entry_id'];
		$driver=$_POST['driver_id'];
		$start=$_POST['linedate_start'];
		$end=$_POST['linedate_end'];
		$reason=$_POST['c_reason'];
				
		$sql = "
			update drivers_unavailable set
				linedate_start='".date("Y-m-d", strtotime($start))."',
				linedate_end='".date("Y-m-d", strtotime($end))."',
				reason_unavailable='".sql_friendly($reason)."',
				added_by='".sql_friendly($_SESSION['user_id'])."'
			where id='".sql_friendly($id)."'
				and driver_id='".sql_friendly($driver)."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Driver ".$_POST['driver_id']." Unavailability updated. ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Driver ".$_POST['driver_id']." Unavailability updated.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function driver_unavailable() {
		// set a flag so that the driver won't show on the available driver list
		
		$sql = "
			update drivers
			set hide_available = 1
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Driver ".$_POST['driver_id']." marked Unavailable and out of list. ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Driver ".$_POST['driver_id']." marked Unavailable and out of list.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
			
		display_xml_response("<rslt>1</rslt>");
	}
	
	function detach_truck() {
		$sql = "
			update drivers set
				attached_truck_id = 0,
				attached2_truck_id = 0
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Detaching truck from Driver ".$_POST['driver_id'].". ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Detaching truck from Driver ".$_POST['driver_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
			
		display_xml_response("<rslt>1</rslt>");
	}
	
	function detach_trailer() {
		$sql = "
			update drivers
			set attached_trailer_id = 0
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
						
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Detaching trailer from Driver ".$_POST['driver_id'].". ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Detaching trailer from Driver ".$_POST['driver_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
				
		display_xml_response("<rslt>1</rslt>");
	}
	
	function load_flat_rate_routes()
	{
		$route_id=$_POST['route_id'];
		if($route_id > 0)
		{		
			$sql = "
				select *				
				from option_values
				where id = '".sql_friendly($route_id)."'
			";
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);		
			
			$retvar = "<FlatRate>".trim($row['fvalue'])."</FlatRate>";
		}
		else
		{
			$retvar = "<FlatRate>0.00</FlatRate>";	
		}
		display_xml_response("<rslt>1</rslt>$retvar");	
	}
	function load_attached_equipment() {
		// if the driver changes on the manage dispatch screen
		// check to see if the new driver has a truck or trailer attached to him
		
		$driver_id=$_POST['driver_id'];
		if($driver_id > 0)
		{		
			$sql = "
				select *
				
				from drivers
				where id = '".sql_friendly($driver_id)."'
			";
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);		
			$retvar = "
				<AttachedTruckID>$row[attached_truck_id]</AttachedTruckID>
				<AttachedTrailerID>$row[attached_trailer_id]</AttachedTrailerID>
				<OwnerOperator>$row[owner_operator]</OwnerOperator>
			";
		}
		else
		{
			$retvar = "
				<AttachedTruckID>0</AttachedTruckID>
				<AttachedTrailerID>0</AttachedTrailerID>
				<OwnerOperator>0</OwnerOperator>
			";	
		}
		display_xml_response("<rslt>1</rslt>$retvar");
	}
	
	function get_detach_info() {
		
		$retvar = "";
		
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data_driver = simple_query($sql);
		$row_driver = mysqli_fetch_array($data_driver);
		
		$retvar .= "<DriverName><![CDATA[$row_driver[name_driver_first] $row_driver[name_driver_last]]]></DriverName>";
		
		if(isset($_POST['truck_id'])) {
			$sql = "
				select *
				
				from trucks
				where id = '".sql_friendly($_POST['truck_id'])."'
			";
			$data_truck = simple_query($sql);
			$row_truck = mysqli_fetch_array($data_truck);
			
			$retvar .= "<TruckName><![CDATA[$row_truck[name_truck]]]></TruckName>";
		}
		
		
		if(isset($_POST['trailer_id'])) {
			$sql = "
				select *
				
				from trailers
				where id = '".sql_friendly($_POST['trailer_id'])."'
			";
			$data_trailer = simple_query($sql);
			$row_trailer = mysqli_fetch_array($data_trailer);
			
			$retvar .= "<TrailerName><![CDATA[$row_trailer[trailer_name]]]></TrailerName>";
		}
		
		display_xml_response("<rslt>1</rslt>$retvar");
	}
	
	function rename_scanned_load() {
		$sql = "
			update ".mrr_find_log_database_name()."log_scan_loads
			set load_id = '".sql_friendly($_POST['load_number'])."'
			where id = '".sql_friendly($_POST['id'])."'
		";
		simple_query($sql);
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Scanned Load ".$_POST['driver_id']." Renamed. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function delete_scanned_load() {
		$sql = "
			update ".mrr_find_log_database_name()."log_scan_loads
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
				and filename = '".sql_friendly($_POST['filename'])."'
		";
		simple_query($sql);
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Scanned Load ".$_POST['id']." Updated. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function mrr_rename_scanned_file() 
	{	
		//$output_dir = getcwd()."/scanner_upload/";
		$input_dir = getcwd()."/scanner_upload/problem/";
		$input_base_dir = getcwd()."/scanner_upload/";
		
		$source_file=$input_dir ."".trim($_POST['file_old']);
		$new_file=$input_base_dir."".trim($_POST['file_name']);
		
		$bypass=0;
		$rflag=0;
		$rmsg="".trim($_POST['file_old'])." to ".trim($_POST['file_name']).".";
		
		if(trim($_POST['file_old'])==trim($_POST['file_name']))		$bypass=1;		//bypass renaming part.  Same name.  Still change the section_id/xref_id
		
		//allow category/section attachment
		$sector=0;
		$xrefid=0;
		if(isset($_POST['cust_id']) && $_POST['cust_id'] > 0)
		{
			$sector=SECTION_CUSTOMER;	$xrefid=$_POST['cust_id'];	
		}
		elseif(isset($_POST['load_id']) && $_POST['load_id'] > 0)
		{
			$sector=SECTION_LOAD;		$xrefid=$_POST['load_id'];	
		}
		elseif(isset($_POST['disp_id']) && $_POST['disp_id'] > 0)
		{
			$sector=SECTION_DISPATCH;	$xrefid=$_POST['disp_id'];	
		}
		elseif(isset($_POST['driver_id']) && $_POST['driver_id'] > 0)
		{
			$sector=SECTION_DRIVER;		$xrefid=$_POST['driver_id'];	
		}
		elseif(isset($_POST['user_id']) && $_POST['user_id'] > 0)
		{
			$sector=SECTION_USER;		$xrefid=$_POST['user_id'];	
		}
		elseif(isset($_POST['truck_id']) && $_POST['truck_id'] > 0)
		{
			$sector=SECTION_TRUCK;		$xrefid=$_POST['truck_id'];	
		}
		elseif(isset($_POST['trailer_id']) && $_POST['trailer_id'] > 0)
		{
			$sector=SECTION_TRAILER;		$xrefid=$_POST['trailer_id'];	
		}
		elseif(isset($_POST['maint_id']) && $_POST['maint_id'] > 0)
		{
			$sector=SECTION_MAINT;		$xrefid=$_POST['maint_id'];	
		}
		elseif(isset($_POST['acc_id']) && $_POST['acc_id'] > 0)
		{
			$sector=SECTION_ACCIDENTS;	$xrefid=$_POST['acc_id'];	
		}
		
		
		if(isset($_POST['bypass']) || $bypass > 0)
		{
			$rslt=true;
		}
		else	
		{				
			$rslt = @ rename($source_file, $new_file);
		}
		if($rslt)
		{			
			$sql = "
				update attachments set
					fname='".sql_friendly($_POST['file_name'])."',
					
					section_id='".sql_friendly($sector)."',
					xref_id='".sql_friendly($xrefid)."',
					cat_id='99',
					
					result='".sql_friendly($rslt)."'				
				where id = '".sql_friendly($_POST['id'])."'
			";
			simple_query($sql);
			/*
			$sql = "
				update attachments
				set deleted = 1
				where id = '".sql_friendly($_POST['id'])."'
			";
			simple_query($sql);
			*/
			$rflag=1;
		}	
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Error Attachment ".$_POST['id']." Renamed. ";	//
		//......................................................................................................................................................
		
		display_xml_response("<rslt>".$rflag."</rslt><rsltmsg><![CDATA[".$rmsg."]]></rsltmsg><sector><![CDATA[".$sector."]]></sector><xref><![CDATA[".$xrefid."]]></xref>");
	}
	function mrr_delete_scanned_file() {
		$sql = "
			update attachments
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
		";
		simple_query($sql);
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Error Attachment ".$_POST['id']." Removed. ";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function driver_load_flag() {
		$sql = "
			update drivers
			set driver_has_load = '".sql_friendly($_POST['load_flag'])."',
				linedate_driver_has_load = ".($_POST['load_flag'] == 1 ? "now()" : "'0000-00-00'")."
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Driver ".$_POST['driver_id']." Flagged to Having Load. ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Driver ".$_POST['driver_id']." Flagged to Having Load.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		
		display_xml_response("<rslt>1</rslt>");
	}
	function mrr_sicap_clear_from_invoice() 
	{
		
		if(!isset($_POST['load_id']) || !isset($_POST['invoice_id'])) {
			display_xml_response("<rslt>0</rslt><rsltmsg>Missing Load ID or Invoice ID Field</rsltmsg>");
			die;
		}
		
		$sql = "
			update load_handler set
				invoice_number = '',
				sicap_invoice_number = '',
				linedate_invoiced = '0000-00-00 00:00:00'
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		simple_query($sql);
		
		$rslt = mrr_sicap_remove_invoice_line_item($_POST['load_id'], $_POST['invoice_id']);
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Load ".$_POST['load_id']." removed from Invoice ".$_POST['invoice_id']." ";
		$mrr_activity_log['load_handler_id']=$_POST['load_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$_POST['load_id'],0,0,"Load ".$_POST['load_id']." removed from Invoice ".$_POST['invoice_id']." ");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	function ajax_sicap_create_invoice() {
		
		if(!isset($_POST['load_id'])) {
			display_xml_response("<rslt>0</rslt><rsltmsg>Missing Load ID Field</rsltmsg>");
			die;
		}
		
		if(isset($_POST['invoice_id']) && $_POST['invoice_id'] > 0) {
			// add to an existing sicap_invoice
			$rslt = sicap_create_invoice($_POST['load_id'], $_POST['invoice_id']);
		} else {
			// create a new sicap_invoice
			$rslt = sicap_create_invoice($_POST['load_id']);
		}
		
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Accounting Invoice Update for Load ".$_POST['load_id'].". ";
		$mrr_activity_log['load_handler_id']=$_POST['load_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$_POST['load_id'],0,0,"Accounting Invoice Update for Load ".$_POST['load_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		
		if($rslt->rslt == '0') {
			display_xml_response("<rslt>0</rslt><rsltmsg><![CDATA[".$rslt->rsltmsg."]]></rsltmsg>");
		} else {
		
			// get the invoice number
			$sql = "
				select sicap_invoice_number,
					linedate_invoiced
				
				from load_handler
				where id = '".sql_friendly($_POST['load_id'])."'
			";
			$data = simple_query($sql);
			
			$row = mysqli_fetch_array($data);
			
			display_xml_response("<rslt>1</rslt><SICAPInvoiceNumber>$row[sicap_invoice_number]</SICAPInvoiceNumber><InvoiceDate><![CDATA[".date("m/d/Y", strtotime($row['linedate_invoiced']))."]]></InvoiceDate>");
		}
	}

	function update_driver_notes() {
		$sql = "
			update drivers
			set available_notes = '".sql_friendly($_POST['driver_notes'])."',
				linedate_available_notes = now()
				
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Updated Driver Notes for Driver ".$_POST['driver_id'].". ";
		$mrr_activity_log['driver_id']=$_POST['driver_id'];
		mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Updated Driver Notes for Driver ".$_POST['driver_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function get_driver_notes() {
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		display_xml_response("<rslt>1</rslt><html><![CDATA[$row[available_notes]]]></html><modified_date>".date("M j Y - h:i a", strtotime($row['linedate_available_notes']))."</modified_date>");
	}
	
	function ajax_sicap_delete_invoice() {
		$msg = '';
		$sql = "
			select sicap_invoice_number
			
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data = simple_query($sql);
		
		if(!mysqli_num_rows($data)) {
			display_xml_response("<rslt>1</rslt><rsltmsg>Could not locate load</rsltmsg>");
			die;
		}
		
		$row = mysqli_fetch_array($data);
		
		if($row['sicap_invoice_number'] == '') {
			display_xml_response("<rslt>1</rslt><rsltmsg>Could not locate an accounting Invoice associated with this load</rsltmsg>");
			die;
		}
		
		$sql = "
			update load_handler
			set sicap_invoice_number = '',
				linedate_invoiced = '0000-00-00',
				invoice_number = ''
			where id = '".sql_friendly($_POST['load_id'])."'
			limit 1
		";
		simple_query($sql);
		
		// see if there are any other loads that use this SICAP invoice number, if so, alert the user that manual updates will be necessary
		$sql = "
			select id
			
			from load_handler
			where deleted = 0
				and sicap_invoice_number = '".sql_friendly($row['sicap_invoice_number'])."'
		";
		$data_check = simple_query($sql);
		
		if(mysqli_num_rows($data_check)) {
			$msg = "
				This invoice has multiple loads associated with it. So, the invoice was not deleted, but the link between the trucking
				system has been cleared. You will need to go into the accounting system and delete entries associated with this load manually.
			";
			simple_query($sql);
		} else {
			// this is the only load with this invoice number, so go ahead and delete the invoice
			$api = new sicap_api_connector();
			
			$api->addParam("InvoiceID", $row['sicap_invoice_number']);
			$api->command = "delete_invoice";
			
			$rslt = $api->execute();
		}
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Accounting Invoice Removal Attempted for Load ".$_POST['load_id'].". ";
		$mrr_activity_log['load_handler_id']=$_POST['load_id'];
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$_POST['load_id'],0,0,"Accounting Invoice Removal Attempted for Load ".$_POST['load_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
				
		display_xml_response("<rslt>1</rslt><rsltmsg>$msg</rsltmsg>");
	}
	
	function get_driver_rate_per_mile() {
		$sql = "
		";
	}
	function ajax_get_option_list() 
     {	
		$skip_deleted=" and active='1' and deleted='0'";		
		if(isset($_POST['show_deleted']) && $_POST['show_deleted'] > 0)		$skip_deleted="";	
				
		$cur_xref_id=(int) $_POST['equipment_xref_id'];
		$cur_mr_id=(int) $_POST['cur_mr_id'];
		if($cur_mr_id > 0)       $skip_deleted=" and (active='1' or id='".$cur_xref_id."') and deleted='0'";
						
		if(get_option_name_by_id($_POST['equipment_type']) == 'truck') {
			// trucks
			$sql = "
				select id as use_val,name_truck as use_disp, active as is_active 
				from trucks 
				where id>0".$skip_deleted."
				order by deleted asc,active desc,name_truck asc			
			";
		} else {
			// trailers
			$sql = "
				select id as use_val,trailer_name as use_disp, active as is_active				
				from trailers 
				where id>0".$skip_deleted." 
				order by deleted asc,active desc,trailer_name asc
			";
		}
		
		$data = simple_query($sql);
		
		$rval = "";
		while($row = mysqli_fetch_array($data)) 
		{
			$rval .= "
				<EquipmentEntry>
					<EquipmentID><![CDATA[$row[use_val]]]></EquipmentID>
					<EquipmentName><![CDATA[$row[use_disp]]]></EquipmentName>
					<EquipmentAct><![CDATA[$row[is_active]]]></EquipmentAct>
				</EquipmentEntry>
			";
		}
		
		display_xml_response("<rslt>1</rslt>$rval");
	}
	
	function mrr_prior_maint_request()
	{
		$req_id=$_POST['req_id'];
		$e_type=$_POST['e_type'];
		$e_xref=$_POST['e_xref'];
		$others="";
		$rval = "";
		
		$sql = "
			select id
			from maint_requests
			where id!='".sql_friendly($req_id)."'
				and ".($e_type==1 || $e_type==58  ? "(equip_type=1 or equip_type=58)" : "(equip_type=2 or equip_type=59)" )."
				and deleted=0
				and ref_id='".sql_friendly($e_xref)."'
				and linedate_completed='0000-00-00 00:00:00' 
			order by id asc
		";
		$data = simple_query($sql);			
		while($row = mysqli_fetch_array($data)) 
		{
			$others.="<a href='maint.php?id=".$row['id']."'>".$row['id']."</a><br>";
		}
		
		$rval .="
			<RequestID>$req_id</RequestID>
			<EquipmentType>$e_type</EquipmentType>
			<EquipmentID>$e_xref</EquipmentID>
			<OtherRequests><![CDATA[".$others."]]></OtherRequests>	
		";
		display_xml_response("<rslt>1</rslt>$rval");
	}
	
	function ajax_get_last_odometer_reading() 
	{
		//get last odometer reading
		$rval = "";
		if(get_option_name_by_id($_POST['equipment_type']) == 'truck' && $_POST['equipment_xref_id']> 0 ) 
		{
			$sql = "
				select odometer,DATE_FORMAT(linedate,'%m/%d/%Y') as mydate				
				from trucks_odometer
				where truck_id = '".sql_friendly( $_POST['equipment_xref_id'] )."'
					and deleted = 0
				order by linedate desc
				limit 1
			";
			$data = simple_query($sql);			
			if($row = mysqli_fetch_array($data)) 
			{
				$mydate=$row['mydate'];
				$odometer=$row['odometer'];
				$rval="
					<LastOdometerReading>
						<Mode>1</Mode>
						<Odometer><![CDATA[".number_format($odometer)."]]></Odometer>
						<ReadingDate><![CDATA[".date("m/d/Y",strtotime($mydate))."]]></ReadingDate>
					</LastOdometerReading>
				";
			}
			$sql = "
				select geotab_last_odometer_date,geotab_last_odometer_reading		
				from trucks
				where id = '".sql_friendly( $_POST['equipment_xref_id'] )."'
			";
			$data = simple_query($sql);			
			if($row = mysqli_fetch_array($data)) 
			{
				$mydate=$row['geotab_last_odometer_date'];
				$odometer=$row['geotab_last_odometer_reading'];
				if($odometer > 0)
				{	//only use GeoTab mode if there is one...otherwise use the previous Manual mode.
					$rval="
					<LastOdometerReading>
						<Mode>2</Mode>
						<Odometer><![CDATA[".number_format($odometer)."]]></Odometer>
						<ReadingDate><![CDATA[".date("m/d/Y",strtotime($mydate))."]]></ReadingDate>
					</LastOdometerReading>
					";
				}
			}
		}
		else	
		{
			$rval="
				<LastOdometerReading>
					<Mode>0</Mode>
					<Odometer>0</Odometer>
					<ReadingDate> </ReadingDate>
				</LastOdometerReading>
			";					
		}
			
		display_xml_response("<rslt>1</rslt>$rval");
	}
	
	function ajax_get_option_single() {
		if(get_option_name_by_id($_POST['equipment_type']) == 'truck' && $_POST['equipment_xref_id']> 0) {
			// trucks
			$sql = "
				select id as use_val,name_truck as use_disp 
				from trucks 
				where active='1' and deleted='0' and id='".sql_friendly( $_POST['equipment_xref_id'] )."'							
			";
		} 
		elseif(get_option_name_by_id($_POST['equipment_type']) == 'trailer' && $_POST['equipment_xref_id']> 0) {
			// trailers
			$sql = "
				select id as use_val,trailer_name as use_disp 
				
				from trailers 
				where active='1' and deleted='0' and id='".sql_friendly( $_POST['equipment_xref_id'] )."' 				
			";
		}
		
		$data = simple_query($sql);
		
		$rval = "";
		while($row = mysqli_fetch_array($data)) {
			$rval .= "
				<EquipmentEntry>
					<EquipmentID>$row[use_val]</EquipmentID>
					<EquipmentName><![CDATA[$row[use_disp]]]></EquipmentName>
				</EquipmentEntry>
			";
		}
		
		display_xml_response("<rslt>1</rslt>$rval");
	}
	
	function ajax_update_maint_req()
	{	
		global $datasource;
		//update function "mrr_auto_create_maint_request" as well if new fields are required.
		
		$rval="";
		$mydate=date("m/d/Y");
		$req_id=$_POST['req_id'];
		$request_description=trim($_POST['req_desc']);
		
		global $defaultsarray;
		
		if($request_description=="")		$request_description="Maintenance Request ".$mydate.".";
				
		$request_description=htmlspecialchars($request_description,ENT_QUOTES,"UTF-8");
						
		$send_email=0;
		
		if($req_id==0)
		{
			$sql = "
					insert into maint_requests
						(id,
						linedate_added,
						linedate_scheduled,
						maint_desc,
						recur_days,
						recur_mileage,
						recur_flag,
						recur_ref,
						urgent,	
						safety_shutdown,
						active,
						unit_breakdown,
						auto_created,
						auto_drain,
						auto_valve,
						auto_oil,
						auto_fed,
						auto_pm,
						deleted)
							
					values (NULL,
						NOW(),
						NOW(),
						'". sql_friendly( $request_description ) ."',
						0,
						0,
						0,
						0,
						0,
						0,
						1,
						0,
						0,
						0,
						0,
						0,
						0,
						0,
						0)
				";		
				
			simple_query($sql);
			$req_id = mysqli_insert_id($datasource);
			if($req_id > 0)
			{
				$sql = "
						update maint_requests set
							user_id='0',
							linedate_completed='0000-00-00 00:00:00',
							odometer_reading='0',
							equip_type='0',
							ref_id='0',
							down_time_hours='0.00',
							cost='0.00'
						
						where id='".sql_friendly( $req_id )."'
					";		
			
				simple_query($sql);
				$send_email=1;
			}
			
		}
		
		$scheduled="0000-00-00 00:00:00";
		$etype=$_POST['req_equip_type'];
		$eid=$_POST['req_equip_id'];
		$unit_breakdown=$_POST['req_breakdown'];
		
		if($req_id>0)
		{
			if($_POST['req_scheduled']!="")	$scheduled=date("Y-m-d", strtotime($_POST['req_scheduled']));	else		$scheduled="0000-00-00 00:00:00";
			if($_POST['req_completed']!="")	$completed=date("Y-m-d", strtotime($_POST['req_completed']));	else		$completed="0000-00-00 00:00:00";
		
			$req_actor=0;
			if($_POST['req_active']==1)		$req_actor=1;	
			
			$est_cost=str_replace(',','',$_POST['req_cost']);	
			$down_hrs=str_replace(',','',$_POST['req_downtime']);
			
			$recur_flag=$_POST['req_recur_flag'];	
			$recur_days=$_POST['req_recur_days'];	
			$recur_miles=$_POST['req_recur_miles'];	
			$urgent=$_POST['req_urgent'];
			$safety_shutdown=$_POST['req_safety'];
					
			$sql = "
					update maint_requests set
						user_id='".($_SESSION['user_id'] != '' ? sql_friendly( $_SESSION['user_id'] ) : "0")."',
						linedate_scheduled=linedate_added,
						linedate_completed='".sql_friendly($completed)."',
						odometer_reading='".($_POST['req_odometer'] != '' ? sql_friendly( $_POST['req_odometer'] ) : "0")."',
						equip_type='".($_POST['req_equip_type'] != '' ? sql_friendly( $_POST['req_equip_type'] ) : "0")."',
						ref_id='".($_POST['req_equip_id'] != '' ? sql_friendly( $_POST['req_equip_id'] ) : "0")."',
						down_time_hours='".($down_hrs != '' ? sql_friendly( $down_hrs ) : "0.00")."',
						cost='".( $est_cost != '' ? sql_friendly( $est_cost ) : "0.00")."',
						maint_desc='".($request_description != '' ? sql_friendly( $request_description ) : "")."',
						recur_days='".sql_friendly($recur_days)."',
						recur_mileage='".sql_friendly($recur_miles)."',
						recur_flag='".sql_friendly($recur_flag)."',	
						urgent='".sql_friendly($urgent)."',
						unit_breakdown='".sql_friendly($unit_breakdown)."',
						safety_shutdown='".sql_friendly($safety_shutdown)."',
						active='".$req_actor."'
					
					where id='".sql_friendly( $req_id )."'
				";		//linedate_scheduled='".sql_friendly($scheduled)."',
			
			simple_query($sql);	
			
			if($unit_breakdown > 0 && ($_POST['req_equip_type']==1 || $_POST['req_equip_type']==58) && $_POST['req_equip_id'] > 0)
			{
				$sqlu = "
          			update trucks set
          				in_the_shop = '1',          				
          				in_shop_note= 'Broken Down!'          				
          			where id = '".sql_friendly($_POST['req_equip_id'])."'
          		";	  
          				//in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
          				//in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."', 
          				//on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
          				//fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',	       		
          		simple_query($sqlu);
			}
			elseif($unit_breakdown > 0 && ($_POST['req_equip_type']==2 || $_POST['req_equip_type']==59) && $_POST['req_equip_id'] > 0)
			{
				$sqlu = "
          			update trailers set
          				in_the_shop = '1',          				
          				in_shop_notes= 'Broken Down!'          				
          			where id = '".sql_friendly($_POST['req_equip_id'])."'
          		";	  	       		
          		simple_query($sqlu);
			}
			elseif( ($_POST['req_equip_type']==1 || $_POST['req_equip_type']==58) && $_POST['req_equip_id'] > 0)
			{
				$sqlu = "
          			update trucks set
          				in_the_shop = '0',          				
          				in_shop_note= ''          				
          			where id = '".sql_friendly($_POST['req_equip_id'])."' and in_the_shop = '1' and in_shop_note= 'Broken Down!'
          		";	  
          				//in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
          				//in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."', 
          				//on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
          				//fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',	       		
          		simple_query($sqlu);
			}
			elseif( ($_POST['req_equip_type']==2 || $_POST['req_equip_type']==59) && $_POST['req_equip_id'] > 0)
			{
				$sqlu = "
          			update trailers set
          				in_the_shop = '0',          				
          				in_shop_notes= ''          				
          			where id = '".sql_friendly($_POST['req_equip_id'])."' and in_the_shop = '1' and in_shop_notes= 'Broken Down!'
          		";	  
          				//in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
          				//in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."', 
          				//on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
          				//fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',	       		
          		simple_query($sqlu);
			}
			
			
			
			if($safety_shutdown > 0 && ($_POST['req_equip_type']==1 || $_POST['req_equip_type']==58) && $_POST['req_equip_id'] > 0)
			{
				$sqlu = "
          			update trucks set
          				in_the_shop = '1',          				
          				in_shop_note= 'Safety Shut Down!'          				
          			where id = '".sql_friendly($_POST['req_equip_id'])."'
          		";	  
          				//in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
          				//in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."', 
          				//on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
          				//fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',	       		
          		simple_query($sqlu);
			}
			if($safety_shutdown > 0 && ($_POST['req_equip_type']==2 || $_POST['req_equip_type']==59) && $_POST['req_equip_id'] > 0)
			{
				$sqlu = "
          			update trailers set
          				in_the_shop = '1',          				
          				in_shop_notes= 'Safety Shut Down!'          				
          			where id = '".sql_friendly($_POST['req_equip_id'])."'
          		";	  
          				//in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
          				//in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."', 
          				//on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
          				//fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',	       		
          		simple_query($sqlu);
			}
			
			
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Updated Maint Request ".$req_id.". ";
			//......................................................................................................................................................
			$send_email=1;
		}
		
		if($send_email > 0 && $req_id > 0)
		{
			$send_to=$defaultsarray['company_email_address'];
			//$send_to=$defaultsarray['peoplenet_hot_msg_cc'];
						
			$equip_type=get_option_name_by_id($etype);	
			
			$name=identify_truck_trailer($etype , $eid);
			
			$updater_label="updated";
			if($_POST['req_completed']!="")		$updater_label="completed.";
			
			
			$req_date="Request Scheduled for ".date("m/d/Y", strtotime($scheduled)).".";
			
			$subj="Maintenance Request ".$req_id." has been ".$updater_label.".";
			$msg1="Maintenance request has been ".$updater_label.".  ".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id." ".$equip_type." #".$name.". ".$req_date." for ".$request_description.".";
			$msg2="Maintenance request has been ".$updater_label.":  <a href='".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."'>".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."</a> <b>".$equip_type." #".$name."</b> for <b>".$request_description."</b>.".$req_date."";
			
			mrr_trucking_sendMail($send_to,'Dispatch',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
               mrr_trucking_sendMail("conardmaintenance@conardtransportation.com",'Dispatch',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
			//mrr_trucking_sendMail('Bfinley@conardtransportation.com','Bfinley',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
			//mrr_trucking_sendMail('dconard@conardtransportation.com','Dale Coanrd',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
		}
		
		
			
		$rval= "
			<NewMaintRequest>
				<RequestID><![CDATA[$req_id]]></RequestID>
				<RequestDate><![CDATA[$mydate]]></RequestDate>
			</NewMaintRequest>
		";
				
		display_xml_response("<rslt>1</rslt>$rval");
	}			
	
	function ajax_maint_req_list()
	{
		$rep=mrr_lockdown_all_truck_trailers_for_urgent_maint(0);
		//echo $rep;
		
		$rval = "";
		$active_count = 0;
		$choose_recur = 0;
		if($_POST['req_recur_flag']> 0 )	$choose_recur = 1;
		
		$mrr_adder=" and linedate_completed='0000-00-00 00:00:00'";
		if($_POST['req_equip_type']> 0 )	$mrr_adder.=" and maint_requests.equip_type='". sql_friendly( $_POST['req_equip_type'])."'";
		if($_POST['req_equip_id']> 0 )	$mrr_adder.=" and maint_requests.ref_id='". sql_friendly( $_POST['req_equip_id'] )."'";
				
		$sql = "
				select maint_requests.*,
				     (select mr_unit_locations.linedate_added from mr_unit_locations where mr_unit_locations.maint_id=maint_requests.id order by mr_unit_locations.linedate_added desc limit 1) as last_note_date,
					(select trucks.name_truck from trucks where trucks.id=maint_requests.ref_id and (maint_requests.equip_type=1 or maint_requests.equip_type=58)) as truck_namer,
					(select trailers.trailer_name from trailers where trailers.id=maint_requests.ref_id and (maint_requests.equip_type=2 or maint_requests.equip_type=59)) as trailer_namer,
					(select users.username from users where users.id=maint_requests.user_id) as user_namer,
					(select mr_location from mr_unit_locations where mr_unit_locations.maint_id=maint_requests.id order by mr_unit_locations.linedate_added desc limit 1) as cur_local,
					(
                             select IF(LOCATE('SNOOZE: ',mr_unit_locations.mr_location) > 0, 1,0)
                             from mr_unit_locations 
                             where mr_unit_locations.maint_id = maint_requests.id and mr_unit_locations.deleted = 0
                             order by mr_unit_locations.linedate_added desc, mr_unit_locations.id desc
                             limit 1
                         ) as snoozing
				from maint_requests
				where maint_requests.deleted ='0' 
				     ".$mrr_adder."					
					and maint_requests.recur_flag='". sql_friendly( $choose_recur )."'
				order by last_note_date asc,
				     maint_requests.equip_type asc,
				     truck_namer asc,
				     trailer_namer asc,
				     maint_requests.linedate_scheduled asc,
				     maint_requests.maint_desc asc,
				     maint_requests.id asc			
		";		// and active=1
				//order by maint_requests.urgent desc,maint_requests.linedate_scheduled desc, maint_requests.id desc
				//and (maint_requests.equip_type=1 OR maint_requests.equip_type=58  or  maint_requests.equip_type=2 OR maint_requests.equip_type=59)
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$classy="inactive";
			if($row['active']==1) 	
			{
				$active_count++;
				$classy="alert";
			}
			
			$cur_local=trim($row['cur_local']);
			$unit_log=mrr_get_mr_unit_locations($row['id'],1);
			
			$main_desc=$row['maint_desc'];
			$mrr_equi_id="";		//"[".$row['ref_id']."]";
			
			if($row['ref_id']==0)
			{
				if(substr_count($main_desc,"TRAILER") > 0 || substr_count($main_desc,"TRL") > 0 || substr_count($main_desc,"trailer") > 0 || substr_count($main_desc,"trl") > 0 || substr_count($main_desc,"Trailer") > 0 || substr_count($main_desc,"Trl") > 0)
				{
					//$mrr_equi_id="";
					
					//$row['equip_type']=59;
					$pos1=strpos($main_desc,"trailer");	
					if(!$pos1)	$pos1=strpos($main_desc,"TRAILER");
					if(!$pos1)	$pos1=strpos($main_desc,"Trailer");
					if(!$pos1)	$pos1=strpos($main_desc,"TRL");
					if(!$pos1)	$pos1=strpos($main_desc,"Trl");
					if(!$pos1)	$pos1=strpos($main_desc,"trl");
					     				
     				if($pos1>0 || (substr_count($main_desc,"TRAILER") > 0 || substr_count($main_desc,"TRL") > 0 || substr_count($main_desc,"trailer") > 0 || substr_count($main_desc,"trl") > 0 || substr_count($main_desc,"Trailer") > 0 || substr_count($main_desc,"Trl") > 0))
               		{
               			$pos2=0;
               			$pos3=0;
               			
               			//$mrr_equi_id="A";
               			if(substr_count($main_desc,"TRAILER ") > 0 || substr_count($main_desc,"TRL ") > 0 || substr_count($main_desc,"trailer ") > 0 || substr_count($main_desc,"trl ") > 0 || substr_count($main_desc,"Trailer ") > 0 || substr_count($main_desc,"Trl ") > 0)
               			{
               				$pos2=strpos($main_desc," ",$pos1);
               				$pos3=strpos($main_desc," ",($pos2 + 1));
               				//$mrr_equi_id="B - ".$pos3." - ".$pos2."";
               			}
               			elseif(substr_count($main_desc,"TRAILER#") > 0 || substr_count($main_desc,"TRL#") > 0 || substr_count($main_desc,"trailer#") > 0 || substr_count($main_desc,"trl#") > 0 || substr_count($main_desc,"Trailer#") > 0 || substr_count($main_desc,"Trl#") > 0)
               			{
               				$pos2=strpos($main_desc,"#",$pos1);
               				$pos3=strpos($main_desc,"-",($pos2 + 1));
               				if($pos3==0 || ($pos3 - $pos2) > 8)	$pos3=strpos($main_desc," ",($pos2 + 1));
               				//$mrr_equi_id="C - ".$pos3." - ".$pos2."";
               			}
               			elseif(substr_count($main_desc,"TRAILER") > 0 || substr_count($main_desc,"TRL") > 0 || substr_count($main_desc,"trailer") > 0 || substr_count($main_desc,"trl") > 0 || substr_count($main_desc,"Trailer") > 0 || substr_count($main_desc,"Trl") > 0)
               			{
               				$pos2=$pos1;
               				$pos3=strpos($main_desc,"-",($pos2 + 1));
               				if($pos3==0 || ($pos3 - $pos2) > 8)	$pos3=strpos($main_desc," ",($pos2 + 1));
               				//$mrr_equi_id="D - ".$pos3." - ".$pos2."";
               			}
               			               						
               			//$pos2=strpos($main_desc," ",$pos1);
               			//$pos3=strpos($main_desc," ",$pos2);
               			
               			if($pos3 > $pos2 && $pos2 > $pos1)
               			{
               				$sub=substr($main_desc,$pos2,($pos3 - $pos2));
               				
               				$trailer=str_replace("#","",$trailer);
               				$trailer=str_replace("-","",$trailer);
               				$trailer=trim($sub);
               				
               				//$mrr_equi_id=$trailer;
               				//$mrr_equi_id=$sub;
               				
               				$sqlx="
               					select id,trailer_name
               					from trailers
               					where trailer_name='".sql_friendly($trailer)."'	
               				";	
               				$datax=simple_query($sqlx);
               				if($rowx = mysqli_fetch_array($datax))
               				{
               					$trailer_id=$rowx['id'];
               					$row['ref_id']=$trailer_id;
               					$row['equip_type']=59;
               					$odometer=0;
               					$local="";
               					$equip_id=$trailer_id;
               					$maint_mode=59;		//Trailer request found, so switch modes and equipment ID.
               					
               					//update the request.
               					$sqlu="
               						update maint_requests set
               							equip_type='59',
               							ref_id='".sql_friendly($trailer_id)."'	
               						where id='".sql_friendly($row['id'])."'	
               					";	
               					simple_query($sqlu);
               				}
               			}
               		}
          		}
			}
			
			
			$equip_type=get_option_name_by_id($row['equip_type']);	
			
			$name=identify_truck_trailer($row['equip_type'] , $row['ref_id']);
			
			if($row['equip_type']>0 && trim($name)=="")		$name="All";
			
			
			if(strlen($main_desc)>28)		$main_desc=substr($main_desc,0,25)."...";
			
			$scheduled=$row['linedate_scheduled'];
			$completed=$row['linedate_completed'];
			
			$rdays=$row['recur_days'];
			$rmiles=$row['recur_mileage'];
			$rflag=$row['recur_flag'];	
			$recuref=$row['recur_ref'];
			$unit_breakdown=$row['unit_breakdown'];
			$safety_shutdown=$row['safety_shutdown'];
			$urgent=$row['urgent'];
			
			$user_id=$row['user_id'];
			$created_by=$row['user_namer'];
			$created_on=date("m/d/Y H:i", strtotime($row['linedate_added']));
			
			//formatting
			if($scheduled=="0000-00-00 00:00:00")	$scheduled="";		else		$scheduled=date("m/d/Y", strtotime($scheduled));
			if($completed=="0000-00-00 00:00:00")	$completed="";		else		$completed=date("m/d/Y", strtotime($completed));
			
			$raw_id=$row['id'];
			$linker="<a href='?id=".$row['id']."' class='".$classy."' title='".str_replace("'","",$row['maint_desc'])."'>".$main_desc."</a>";
			
			$coster=money_format('',$row['cost']);
			$trash='<a href="javascript:confirm_delete('.$row['id'].')"><img src="images/delete_sm.gif" border="0"></a>';
			$recur_file="";
			if($recuref>0)		$recur_file="<a href='maint_recur.php?id=".$recuref."' target='_blank'>Edit</a>";
			
			$location=mrr_find_equip_current_location($row['equip_type'] , $row['ref_id']);
			
			$zres=mrr_find_equip_current_pmi_fed($row['equip_type'] , $row['ref_id']);
			
			$tires=$zres['tires'];
			$pmi=$zres['pmi'];
			$fed=$zres['fed'];
			
			$snooze=$row['snoozing'];
			
			//xml output						 - $mrr_equi_id
			$rval.= "
				<MaintRequest>
					<RequestLink><![CDATA[$linker]]></RequestLink>
					<RequestType><![CDATA[$equip_type]]></RequestType>
					<RequestName><![CDATA[$name]]></RequestName>
					<RequestScheduled><![CDATA[$scheduled]]></RequestScheduled>
					<RequestCompleted><![CDATA[$completed]]></RequestCompleted>
					<RequestCost><![CDATA[$coster]]></RequestCost>
					<RequestRDays><![CDATA[$rdays]]></RequestRDays>
					<RequestRMiles><![CDATA[$rmiles]]></RequestRMiles>
					<RequestRecur><![CDATA[$rflag]]></RequestRecur>
					<RequestRecurRef><![CDATA[$recur_file]]></RequestRecurRef>
					<RequestUrgent><![CDATA[$urgent]]></RequestUrgent>					
					<RequestBreakdown><![CDATA[$unit_breakdown]]></RequestBreakdown>	
					<SafetyShutDown><![CDATA[$safety_shutdown]]></SafetyShutDown>	
					<RequestLocation><![CDATA[$location]]></RequestLocation>				
					<RequestTrash><![CDATA[$trash]]></RequestTrash>
					<RequestCreatedID><![CDATA[$user_id]]></RequestCreatedID>
					<RequestCreatedBY><![CDATA[$created_by]]></RequestCreatedBY>
					<RequestCreatedON><![CDATA[$created_on]]></RequestCreatedON>
					
					<RequestID><![CDATA[$raw_id]]></RequestID>
					<RequestCurLocal><![CDATA[$cur_local]]></RequestCurLocal>
					<RequestUnitLog><![CDATA[$unit_log]]></RequestUnitLog>
					
					<RequestSnooze><![CDATA[$snooze]]></RequestSnooze>
					
					<RequestTires><![CDATA[$tires]]></RequestTires>
					<RequestPM><![CDATA[$pmi]]></RequestPM>
					<RequestFED><![CDATA[$fed]]></RequestFED>					
					
					<RequestCount><![CDATA[$active_count]]></RequestCount>
				</MaintRequest>
			";
			
		}	
		mysqli_free_result($data);			
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	//copy function to take existing Recurring schedule and produce individual Maint Request or...
	//	take existing individual Maint Request and produce Recurring schedule by code_value
	function ajax_copy_maint_request_or_recurring()
	{
		global $datasource;

		$source_id=$_POST['request_id'];
		$copy_mode=$_POST['req_recur_flag'];		//0= MaintRequest to Recurring....  1=Recurring to MaintRequest.
		
		$recur_ref=0;
		if($copy_mode==1)	$recur_ref=$source_id;
		
		$sel_type=0;		if($_POST['equipment_type'] > 0 )			$sel_type=$_POST['equipment_type'];
		$sel_item=0;		if($_POST['equipment_xref_id'] > 0 )		$sel_item=$_POST['equipment_xref_id'];
		
		$item_count=0;
				
		$rval = "";
		$sql = "
				select *
				from maint_requests
				where deleted ='0' 
					and id='". sql_friendly( $source_id )."' 
					and recur_flag='". sql_friendly( $copy_mode )."'	
				limit 1
			
		";		
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		//verify the row was found before attempting to copy
		if($row['id'] > 0 )
		{	
			//capture result row
			$actor=$row['active'];
			$typer=$row['equip_type'];
			$ref=$row['ref_id'];
			$scheduled=$row['linedate_scheduled'];
			$complete=$row['linedate_completed'];
			$desc=$row['maint_desc'];
			
			$rdays=$row['recur_days'];
			$rmiles=$row['recur_mileage'];
			$rflag=$row['recur_flag'];
			$recref=$row['recur_ref'];
			$urgent=$row['urgent'];
			
			$cost=$row['cost'];
			$downtime=$row['down_time_hours'];
			$odometer=$row['odometer_reading'];
			
			
			$validator=ajax_request_duplication_check($desc,$typer,$ref,$copy_mode);
			if($validator=="Invalid")
			{
				$req_id=0;
				$item_count++;	
			}
			elseif($validator=="Valid")
			{
     			//create new row
     			$sql = "
     					insert into maint_requests
     						(id,
     						linedate_added,
     						maint_desc,						
     						active,
     						deleted)
     							
     					values (NULL,
     						now(),
     						'". sql_friendly( $desc ) ."',						
     						1,
     						0)
     				";		
     				
     			simple_query($sql);
     			$req_id = mysqli_insert_id($datasource);
     			if($req_id > 0)
     			{
     				//if new row has been made, update with inverted setting based on MaintRequest or Recurring Schedule
     				$sql = "
     						update maint_requests set
     							user_id='".($_SESSION['user_id'] != '' ? sql_friendly( $_SESSION['user_id'] ) : "0")."',
     							linedate_scheduled='".sql_friendly( $scheduled )."',
     							linedate_completed='0000-00-00 00:00:00',
     							odometer_reading='".sql_friendly( $odometer )."',
     							equip_type='".sql_friendly( $sel_type )."',
     							ref_id='".sql_friendly( $sel_item )."',
     							down_time_hours='".sql_friendly( $downtime )."',
     							cost='".sql_friendly( $cost )."',
     							recur_days='".sql_friendly( $rdays )."',
     							recur_mileage='".sql_friendly( $rmiles )."',
     							recur_flag='".($rflag == '1' ? "0" : "1")."',
     							recur_ref='".sql_friendly($recur_ref)."',	
								urgent='".sql_friendly($urgent)."',
     							active='".sql_friendly( $actor )."'
     						
     						where id='".sql_friendly( $req_id )."'					
     					";		
     				simple_query($sql);
     				
     				
     				//now copy all line_items to the new Request, exactly as the settings are.
     				$item_count=0;
     				
     				
     				$mrr_adder2=" and ref_id='".sql_friendly( $source_id )."'";
     			
     				$sql = "
     						select *
     						from maint_line_items
     						where deleted = 0 ".$mrr_adder2."
     						order by linedate_added asc, id asc
     					
     				";		// and active=1
     				$data2 = simple_query($sql);
     						
     				while($row2 = mysqli_fetch_array($data2))
     				{
     					$mycat=$row2['cat_id'];
     					$mydesc=$row2['lineitem_desc'];
     					$myquant=$row2['quantity'];
     					$mymake=$row2['make'];
     					$mymodel=$row2['model'];
     					$mytime=$row2['down_time_hours'];
     					$mycost=$row2['item_cost'];
     					$mylocfrnt=$row2['location_front'];
     					$mylocleft=$row2['location_left'];
     					$myloctop=$row2['location_top'];
     					$mylocinside=$row2['location_inside'];
     					$myact=$row2['active'];
     					
     					$sql = "
     					insert into maint_line_items
     						(id,
      							ref_id,
       							cat_id,
       							lineitem_desc,
       							linedate_added,
       							quantity,
       							make,
       							model,
       							down_time_hours,
       							item_cost,
       							location_front,
       							location_left,
       							location_top,
       							location_inside,
       							active,
       							deleted)
     								
     						values (NULL,
     							'". sql_friendly( $req_id ) ."',
     							'". sql_friendly( $mycat ) ."',
     							'". sql_friendly( $mydesc ) ."',
     							NOW(),
     							'". sql_friendly( $myquant ) ."',
     							'". sql_friendly( $mymake ) ."',
     							'". sql_friendly( $mymodel ) ."',
     							'". sql_friendly( $mytime ) ."',
     							'". sql_friendly( $mycost ) ."',
     							'". sql_friendly( $mylocfrnt ) ."',
     							'". sql_friendly( $mylocleft ) ."',
     							'". sql_friendly( $myloctop ) ."',
     							'". sql_friendly( $mylocinside ) ."',
     							'". sql_friendly( $myact ) ."',
     							0)
     						";
     								
     					simple_query($sql);
     					$item_id = mysqli_insert_id($datasource);
     					if($item_id>0)	$item_count++;
     				}	//end while loop				
     			}		//end if
			
			}//end else/if VALID check
			
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Copied ".$item_count." Maint Item(s) from Request ".$source_id." to new Request ".$req_id.". ";
			//......................................................................................................................................................
				
			//xml output
			$rval.= "
				<CopyRequest>
					<RequestSourceID><![CDATA[$source_id]]></RequestSourceID>
					<RequestDestinationID><![CDATA[$req_id]]></RequestDestinationID>
					<RequestLineItems><![CDATA[$item_count]]></RequestLineItems>
				</CopyRequest>
			";
			
		}
		display_xml_response("<rslt>1</rslt>$rval");	
		
	}
	
	function ajax_generate_recurring_schedule_notices()
	{
		$sel_type=0;		if($_POST['equipment_type'] > 0 )			$sel_type=$_POST['equipment_type'];
		$sel_item=0;		if($_POST['equipment_xref_id'] > 0 )		$sel_item=$_POST['equipment_xref_id'];
		$show_scheduled=0;	if($_POST['show_sched'] > 0 )				$show_scheduled=1;
		
		$cnt=0;
		$mrr_adder="";
		if($sel_type> 0 )	$mrr_adder.=" and (equip_type='". sql_friendly( $sel_type )."' or equip_type='0')";
		if($sel_item> 0 )	$mrr_adder.=" and (ref_id='". sql_friendly( $sel_item )."' or ref_id='0')";
		
		$rval = "";
		$sql = "
				select *
				from maint_requests
				where deleted ='0' 
					and active='1' 
					and recur_flag='1'	".$mrr_adder."
				order by urgent desc,linedate_scheduled asc, id asc
			
		";		
		$data = simple_query($sql);	
		while($row = mysqli_fetch_array($data))
		{
			$schedule_id=$row['id'];
			$cnt++;
			$temp_holder=generate_this_recurring_schedule($schedule_id,$cnt,$sel_type,$sel_item,$show_scheduled);
					
			$rval.=$temp_holder;
			
		}
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function generate_this_recurring_schedule($id,$cnt,$sel_type,$sel_item,$show_scheduled)
	{
		$rval = "";
		$sql = "
				select *
				from maint_requests
				where id='".sql_friendly( $id )."'	
				limit 1
			
		";	
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		//verify the row was found before attempting to copy
		if($row['id'] > 0 )
		{	
			$scheduled=$row['linedate_scheduled'];
			$desc=$row['maint_desc'];
			
			$rdays=$row['recur_days'];
			$rmiles=$row['recur_mileage'];
			$rflag=$row['recur_flag'];
			$recref=$row['recur_ref'];
			$urgent=$row['urgent'];
			
			$cost=$row['cost'];
			$downtime=$row['down_time_hours'];
			
			$recur_file="";
			if($recref>0)		$recur_file="<a href='maint_recur.php?id=".$recref."' target='_blank'>Edit</a>";
			
			$etype_name=get_option_name_by_id($row['equip_type']);	
			$etype_id=$row['equip_type'];
			$name=identify_truck_trailer($row['equip_type'] , $row['ref_id']);
			
			if($row['equip_type']>0 && trim($name)=="")		$name="All";
			$etype_name=trim($etype_name);
			$etype_name=str_replace("tr","Tr",$etype_name);
						
			//get list of trucks/trailers/both that should have notices
			$lineitems=generate_truck_trailer_notices($etype_name,$row['ref_id'],$name,$rdays,$rmiles,$scheduled,$desc,$etype_id,$id,$sel_type,$sel_item,$show_scheduled);
			$desc_mask="<span class='mrr_maint_recur'>".trim($desc)."</span>";
			
			if($scheduled=="0000-00-00 00:00:00")		$scheduled="";		else		$scheduled=date("m/d/Y", strtotime($scheduled));
			
			//xml output
			$rval.= "
				<MaintRequest>
					<RequestID><![CDATA[$id]]></RequestID>
					<RequestLink><![CDATA[$desc_mask]]></RequestLink>
					<RequestType><![CDATA[$etype_name]]></RequestType>
					<RequestName><![CDATA[$name]]></RequestName>
					<RequestScheduled><![CDATA[$scheduled]]></RequestScheduled>
					<RequestCost><![CDATA[$cost]]></RequestCost>
					<RequestRDays><![CDATA[$rdays]]></RequestRDays>
					<RequestRMiles><![CDATA[$rmiles]]></RequestRMiles>
					<RequestRecur><![CDATA[$rflag]]></RequestRecur>
					<RequestRecurRef><![CDATA[$recur_file]]></RequestRecurRef>
					<RequestUrgent><![CDATA[$urgent]]></RequestUrgent>	
					<RequestCount><![CDATA[$cnt]]></RequestCount>
					<RequestItemsList>
						$lineitems
					</RequestItemsList>
				</MaintRequest>
			";			
		}
		return $rval;
	}
	function generate_truck_trailer_notices($type,$item,$name,$days,$miles,$dater,$desc,$etype_id,$maint_id,$sel_type,$sel_item,$show_scheduled)
	{
		//check days and mileage for this item or each truck/trailer/both
		$rval = "";
		$type=strtolower($type);
		$xcntr=0;
		
		//both types of equipment
		$section1=1;
		$section2=1;
		$mrr_adder="";
		if($sel_item>0)	$mrr_adder=" and id='".sql_friendly( $sel_item )."'";
		
		if($type=="truck" || $type=="trucks")		$section2=0;		//turn off trailer section
		if($type=="trailer" || $type=="trailer")	$section1=0;		//turn off truck section
		
		if($section1>0)
		{
			//find all trucks or this truck if selected
			$sql = "
				select *
				from trucks
				where deleted=0 and active='1' ".$mrr_adder."	
				order by name_truck asc, id asc
			
			";	
			$data = simple_query($sql);
			
			$type_label="Truck";
			
			while($row = mysqli_fetch_array($data))
			{
				$truck_id=$row['id'];
				$tname=$row['name_truck'];
				
				//reset flags for this truck
				$odom=0;
				$odom_date="0000-00-00 00:00:00";
				$odometer=0;
				$madedate="0000-00-00 00:00:00";
				//$compdate="0000-00-00 00:00:00";				
				
				//get odometer reading from truck_odometer table if present
				$res=mrr_last_odometer_reading($type,$truck_id);
				$odom=(int) $res['odometer'];
				$odom_date=$res['linedate'];	
				
				//now get last time an actual MaintRequest was done for this reason by this item
				$vals=mrr_last_request_reading($type,$truck_id,$desc,$etype_id,$dater,$days);
				$odometer=(int) $vals['odometer'];
				$madedate=$vals['scheduled'];
				//$compdate=$vals['completed'];	
				$msg=$vals['msg'];
				$found_id=$vals['found_id'];
				
				$tot_days=mrr_count_days_between_date($madedate,$odom_date);
				
				$show_already_scheduled=0;
				$display_this_item=0;
				if($miles>0 && $days>0)
				{
					if( ( $odom >=( $odometer+$miles ) || $odometer==0 )  &&  
						($tot_days>=$days || $tot_days < 0 || $madedate=="0000-00-00 00:00:00" )  )
					{
						$display_this_item=1;
					}
				}
				elseif($miles>0)
				{
					//mileage setting used.  Display here since odometer is over last request found.
					if( $odom >=( $odometer+$miles ) || $odometer==0 )
					{
						$display_this_item=1;
					}
				}
				elseif($days>0 )
				{
					//days setting used.  Display here since last request does not exist or has days> than DAYS
					if($tot_days>=$days || $tot_days < 0 || $madedate=="0000-00-00 00:00:00" )
					{
						$display_this_item=1;
					}
				}
				
				
				if($sel_type>0 && $sel_type!=$etype_id)
				{
					$display_this_item=0;
				}	
				if($sel_item>0 && $sel_item!=$truck_id)
				{
					$display_this_item=0;
				}
				
				if($display_this_item==0 && $madedate!="0000-00-00 00:00:00")
				{
					$show_already_scheduled=1;	
				}
				
				$odom=number_format($odom,0);
				$odometer=number_format($odometer,0);
				
				if($display_this_item==1)
				{
					//xml output
					$xcntr++;
					$linker="<div id='container_".$maint_id."_".$etype_id."_".$truck_id."'>
								<span class='mrr_link_like_on' onClick='schedule_item_maint(".$maint_id.",".$truck_id.",".$etype_id.");'><b>Schedule</b></span>
							</div>";
							
					if($odom_date=="0000-00-00 00:00:00")		$odom_date="";		else		$odom_date=date("m/d/Y", strtotime($odom_date));
					if($madedate=="0000-00-00 00:00:00")		$madedate="";		else		$madedate=date("m/d/Y", strtotime($madedate));
					
					$rval.= "
						<ItemRecurring>
							<ItemMaintDesc><![CDATA[$desc]]></ItemMaintDesc>	
							<ItemID><![CDATA[$truck_id]]></ItemID>
							<ItemName><![CDATA[$tname]]></ItemName>
							<ItemTypeID><![CDATA[$etype_id]]></ItemTypeID>
							<ItemTypeName><![CDATA[$type_label]]></ItemTypeName>
							<ItemCurOdom><![CDATA[$odom]]></ItemCurOdom>
							<ItemCurDate><![CDATA[$odom_date]]></ItemCurDate>
							<ItemReqOdom><![CDATA[$odometer]]></ItemReqOdom>
							<ItemReqDate><![CDATA[$madedate]]></ItemReqDate>
							<ItemAddReq><![CDATA[$linker]]></ItemAddReq>	
							<ItemCounter><![CDATA[$xcntr]]></ItemCounter>
							<ItemDebugger><![CDATA[$msg]]></ItemDebugger>
						</ItemRecurring>											
					";		
				}
				elseif($show_already_scheduled==1 && $show_scheduled > 0 )	
				{
					if($odom_date=="0000-00-00 00:00:00")		$odom_date="";		else		$odom_date=date("m/d/Y", strtotime($odom_date));
					if($madedate=="0000-00-00 00:00:00")		$madedate="";		else		$madedate=date("m/d/Y", strtotime($madedate));
					
					$linker="<div id='container_".$maint_id."_".$etype_id."_".$truck_id."'>
								<a href='maint.php?id=".$found_id."' class='mrr_scheduled_link' target='_blank'><b>Scheduled</b></a>
							</div>";
					
					$rval.= "
						<ItemRecurring>
							<ItemMaintDesc><![CDATA[$desc]]></ItemMaintDesc>	
							<ItemID><![CDATA[$truck_id]]></ItemID>
							<ItemName><![CDATA[$tname]]></ItemName>
							<ItemTypeID><![CDATA[$etype_id]]></ItemTypeID>
							<ItemTypeName><![CDATA[$type_label]]></ItemTypeName>
							<ItemCurOdom><![CDATA[$odom]]></ItemCurOdom>
							<ItemCurDate><![CDATA[$odom_date]]></ItemCurDate>
							<ItemReqOdom><![CDATA[$odometer]]></ItemReqOdom>
							<ItemReqDate><![CDATA[$madedate]]></ItemReqDate>
							<ItemAddReq><![CDATA[$linker]]></ItemAddReq>	
							<ItemCounter><![CDATA[0]]></ItemCounter>
							<ItemDebugger><![CDATA[$msg]]></ItemDebugger>
						</ItemRecurring>											
					";	
				}					
			}
		}
		
		if($section2>0)
		{
			//find all trailers or this trailer if selected
			$sql = "
				select *
				from trailers
				where deleted=0 and active='1' ".$mrr_adder."	
				order by trailer_name asc, id asc
			
			";	
			$data = simple_query($sql);
			
			$type_label="Trailer";
			
			while($row = mysqli_fetch_array($data))
			{
				$trailer_id=$row['id'];
				$tname=$row['trailer_name'];
				
				//reset flags for this truck
				$odom=0;
				$odom_date="0000-00-00 00:00:00";
				$odometer=0;
				$madedate="0000-00-00 00:00:00";
				//$compdate="0000-00-00 00:00:00";	
				
				//get odometer reading from truck_odometer table if present
				//$res=mrr_last_odometer_reading($type,$truck_id);
				//$odom=$res['odometer'];
				//$odom_date=$res['linedate'];	
				
				//now get last time an actual MaintRequest was done for this reason by this item
				$vals=mrr_last_request_reading($type,$trailer_id,$desc,$etype_id,$dater,$days);
				$odometer=(int) $vals['odometer'];
				$madedate=$vals['scheduled'];
				//$compdate=$vals['completed'];
				$msg=$vals['msg'];	
				$found_id=$vals['found_id'];
				
				$tot_days=mrr_count_days_between_date($madedate,$odom_date);
				
				$show_already_scheduled=0;
				$display_this_item=0;
				//if($miles>0 && ( $odom >=( $odometer+$miles ) || $odometer==0 ) )
				//{
					//mileage setting used.  Display here since odometer is over last request found.
				//	$display_this_item=1;
				//}
				if($days>0 && ($tot_days>=$days || $tot_days < 0 || $madedate=="0000-00-00 00:00:00" ) )
				{
					//days setting used.  Display here since last request does not exist or has days> than DAYS
					$display_this_item=1;
				}
				if($sel_type>0 && $sel_type!=$etype_id)
				{
					$display_this_item=0;
				}	
				if($sel_item>0 && $sel_item!=$trailer_id)
				{
					$display_this_item=0;
				}
				
				if($display_this_item==0 && $madedate!="0000-00-00 00:00:00")
				{
					$show_already_scheduled=1;	
				}
				
				$odom=number_format($odom,0);
				$odometer=number_format($odometer,0);
				
				if($display_this_item==1)
				{
					//xml output
					$xcntr++;
					$linker="<div id='container_".$maint_id."_".$etype_id."_".$trailer_id."'>
								<span class='mrr_link_like_on' onClick='schedule_item_maint(".$maint_id.",".$trailer_id.",".$etype_id.");'><b>Schedule</b></span>
							</div>";
					
					if($odom_date=="0000-00-00 00:00:00")		$odom_date="";		else		$odom_date=date("m/d/Y", strtotime($odom_date));
					if($madedate=="0000-00-00 00:00:00")		$madedate="";		else		$madedate=date("m/d/Y", strtotime($madedate));
					
					$rval.= "
						<ItemRecurring>
							<ItemMaintDesc><![CDATA[$desc]]></ItemMaintDesc>	
							<ItemID><![CDATA[$trailer_id]]></ItemID>
							<ItemName><![CDATA[$tname]]></ItemName>
							<ItemTypeID><![CDATA[$etype_id]]></ItemTypeID>
							<ItemTypeName><![CDATA[$type_label]]></ItemTypeName>
							<ItemCurOdom><![CDATA[]]></ItemCurOdom>
							<ItemCurDate><![CDATA[$odom_date]]></ItemCurDate>
							<ItemReqOdom><![CDATA[]]></ItemReqOdom>
							<ItemReqDate><![CDATA[$madedate]]></ItemReqDate>
							<ItemAddReq><![CDATA[$linker]]></ItemAddReq>	
							<ItemCounter><![CDATA[$xcntr]]></ItemCounter>
							<ItemDebugger><![CDATA[$msg]]></ItemDebugger>
						</ItemRecurring>											
					";		
				}
				elseif($show_already_scheduled==1 && $show_scheduled==1)	
				{
					if($odom_date=="0000-00-00 00:00:00")		$odom_date="";		else		$odom_date=date("m/d/Y", strtotime($odom_date));
					if($madedate=="0000-00-00 00:00:00")		$madedate="";		else		$madedate=date("m/d/Y", strtotime($madedate));
					$linker="<div id='container_".$maint_id."_".$etype_id."_".$trailer_id."'>
							<a href='maint.php?id=".$found_id."' class='mrr_scheduled_link' target='_blank'><b>Scheduled</b></a>
							</div>";
					$xcntr++;
					
					$rval.= "
						<ItemRecurring>
							<ItemMaintDesc><![CDATA[$desc]]></ItemMaintDesc>	
							<ItemID><![CDATA[$truck_id]]></ItemID>
							<ItemName><![CDATA[$tname]]></ItemName>
							<ItemTypeID><![CDATA[$etype_id]]></ItemTypeID>
							<ItemTypeName><![CDATA[$type_label]]></ItemTypeName>
							<ItemCurOdom><![CDATA[$odom]]></ItemCurOdom>
							<ItemCurDate><![CDATA[$odom_date]]></ItemCurDate>
							<ItemReqOdom><![CDATA[$odometer]]></ItemReqOdom>
							<ItemReqDate><![CDATA[$madedate]]></ItemReqDate>
							<ItemAddReq><![CDATA[$linker]]></ItemAddReq>	
							<ItemCounter><![CDATA[$xcntr]]></ItemCounter>
							<ItemDebugger><![CDATA[$msg]]></ItemDebugger>
						</ItemRecurring>											
					";	
				}
			}
		}
			
		return $rval;
	}
	
	//functions for maintenance request line items
	function ajax_update_maint_req_item()
	{
		global $datasource;

		$rval="";
		$mydate=date("m/d/Y");
		$maint_id=$_POST['maint_id'];
		$item_id=$_POST['item_id'];
		$item_desc=$_POST['item_desc'];
		if(trim($item_desc)=="")		$item_desc="Line Item";
		
		if($item_id==0)
		{
			$sql = "
					insert into maint_line_items
						(id,
 						ref_id,
  						cat_id,
  						lineitem_desc,
  						linedate_added,
  						quantity,
  						make,
  						model,
  						down_time_hours,
  						item_cost,
  						location_front,
  						location_left,
  						location_top,
  						location_inside,
  						active,
  						deleted)
							
					values (NULL,
						'". sql_friendly( $maint_id ) ."',
						0,
						'". sql_friendly( $item_desc ) ."',
						NOW(),
						0,
						'',
						'',
						0,
						0,
						0,
						0,
						0,
						0,
						1,
						0)
				";
								
			simple_query($sql);
			$item_id = mysqli_insert_id($datasource);
			
		}
		
		if($item_id>0)
		{
			$cat=(int) $_POST['cat_id'];
			$quantity=$_POST['quantity'];
			$make=trim($_POST['make']);
			$model=trim($_POST['model']);
			$down_hrs=str_replace(',','',$_POST['item_downtime']);
			$est_cost=str_replace(',','',$_POST['item_cost']);
			$front_pos= $_POST['location_front'];
			$left_pos= $_POST['location_left'];
			$top_pos= $_POST['location_top'];
			$inside_pos= $_POST['location_inside'];
						
			$item_actor=0;
			if($_POST['item_active']==1)		$item_actor=1;	
									
			$sql = "
					update maint_line_items set
						ref_id='". sql_friendly( $maint_id )."',
						cat_id='". sql_friendly( $cat ) ."',
						lineitem_desc='". sql_friendly( $item_desc )."',
						quantity='". sql_friendly( $quantity )."',
						make='". sql_friendly( $make )."',
						model='". sql_friendly( $model ) ."',
						down_time_hours='".($down_hrs != '' ? sql_friendly( $down_hrs ) : "0.00")."',
						item_cost='".( $est_cost != '' ? sql_friendly( $est_cost ) : "0.00")."',
						location_front='". sql_friendly( $front_pos )."',
						location_left='". sql_friendly( $left_pos ) ."',
						location_top='". sql_friendly( $top_pos )."',
						location_inside='". sql_friendly( $inside_pos ) ."',
						active='".sql_friendly( $item_actor )."'
					
					where id='".sql_friendly( $item_id )."'
				";		
			
			simple_query($sql);	
			
			
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Updated Maint Item ".$item_id ." for Maint Request ".$maint_id.". ";
			//......................................................................................................................................................
	
		}
		
		$rval= "
			<NewMaintRequestItem>
				<RequestItemID><![CDATA[$item_id]]></RequestItemID>
				<RequestItemDate><![CDATA[$mydate]]></RequestItemDate>
			</NewMaintRequestItem>
		";
		
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	function ajax_make_line_item_list()
	{
		$rval = "";
		$active_count = 0;
		global $defaultsarray;
		$labor_rate_val=floatval($defaultsarray['maint_labor_rate']);
		//$markup_val=floatval($defaultsarray['maint_invoice_markup']);
					
		$sql = "
				select *
				from maint_line_items
				where deleted = 0 
					 and ref_id='".sql_friendly($_POST['maint_id'] )."'
				order by linedate_added asc, id asc
			
		";		// and active=1
		$data = simple_query($sql);
		
		$all_items=0;
		$tot_items=0;
		$tot_quant=0;
		$tot_hours=0;
		$tot_cost=0;
		
		while($row = mysqli_fetch_array($data))
		{
			$classy="mrr_link_like_off";
			$checked="";
			
			$quant=$row['quantity'];
			$unit_cost=$row['item_cost'];
			$hours=number_format($row['down_time_hours'],2);
			if($unit_cost==0 && $hours>0)	
			{
				$unit_cost=number_format(($labor_rate_val*$hours),2);
				$sqlu="update maint_line_items set item_cost='".sql_friendly(($labor_rate_val*$hours))."' where id='".sql_friendly($row['id'])."'";		
				simple_query($sqlu);
			}
			$sub_cost=$quant * $unit_cost;
			$coster=money_format('',$sub_cost);
						
			if($row['active']==1) 	
			{
				$active_count++;
				$classy="mrr_link_like_on";
				$checked=" checked";
				$tot_cost+=$sub_cost;				//projected cost Q*C=subtot  Changing quantity will change sub
				$tot_items++;						//added items
				$tot_quant+=$row['quantity'];			//add number of items
				$tot_hours+=$row['down_time_hours'];	//hours to accumulate
			}
						
			$main_desc=trim($row['lineitem_desc']);
			if(strlen($main_desc)>28)	$main_desc=substr($main_desc,0,25)."...";
			
			$maker=trim($row['make']);
			if(strlen($maker)>28)		$maker=substr($maker,0,25)."...";
			
			$model=trim($row['model']);
			if(strlen($model)>28)		$model=substr($model,0,25)."...";
									
			$cat_type=mrr_get_option_fvalue_by_id($row['cat_id']);	
			$front_type=mrr_get_option_fvalue_by_id($row['location_front'],1);	
			$left_type=mrr_get_option_fvalue_by_id($row['location_left'],1);	
			$top_type=mrr_get_option_fvalue_by_id($row['location_top'],1);	
			$inside_type=mrr_get_option_fvalue_by_id($row['location_inside'],1);			
			
			$trash='<a href="javascript:confirm_delete_item('.$_POST['maint_id'].','.$row['id'].')"><img src="images/delete_sm.gif" border="0"></a>';
			
			//$linker="<a href='?id=".$_POST['maint_id']."&item=".$row['id']."' class='".$classy."'>".$main_desc."</a>";
			$linker="<span id='link_like_".$all_items."' class='".$classy."' onClick='load_line_item_form(".$_POST['maint_id'].",".$row['id'].");'><b>".$main_desc."</b></span>";
			$all_items++;		
			
			$unit_label="<span class='".$classy."'>$".$unit_cost."</span>";
			$cost_label="<span class='".$classy."'>$".$coster."</span>";
			$hour_label="<span class='".$classy."'>".$hours."</span>";
			$quant_label="<span class='".$classy."'>".$quant."</span>";
			
			//xml output
			$rval.= "
				<MaintRequestItem>
					<RequestItemLink><![CDATA[$linker]]></RequestItemLink>					
					<RequestItemName><![CDATA[$main_desc]]></RequestItemName>
					<RequestItemCat><![CDATA[$cat_type]]></RequestItemCat>
					<RequestItemMaker><![CDATA[$maker]]></RequestItemMaker>
					<RequestItemModel><![CDATA[$model]]></RequestItemModel>
					<RequestItemFront><![CDATA[$front_type]]></RequestItemFront>
					<RequestItemLeft><![CDATA[$left_type]]></RequestItemLeft>
					<RequestItemTop><![CDATA[$top_type]]></RequestItemTop>
					<RequestItemInside><![CDATA[$inside_type]]></RequestItemInside>
					<RequestItemQuant><![CDATA[$quant_label]]></RequestItemQuant>
					<RequestItemHours><![CDATA[$hour_label]]></RequestItemHours>
					<RequestItemCost><![CDATA[$cost_label]]></RequestItemCost>
					<RequestItemUnit><![CDATA[$unit_label]]></RequestItemUnit>
					<RequestItemTrash><![CDATA[$trash]]></RequestItemTrash>		
				</MaintRequestItem>
			";
			
		}	
		
		if($all_items>0)
		{
			$tot_cost=money_format('',$tot_cost);
			$tot_hours=number_format($tot_hours,2);
			$rval.= "
				<MaintRequestItem>
					<RequestItemLink><![CDATA[Total Items]]></RequestItemLink>					
					<RequestItemName><![CDATA[Total Items]]></RequestItemName>
					<RequestItemCat><![CDATA[$active_count]]></RequestItemCat>
					<RequestItemMaker><![CDATA[]]></RequestItemMaker>
					<RequestItemModel><![CDATA[]]></RequestItemModel>
					<RequestItemFront><![CDATA[]]></RequestItemFront>
					<RequestItemLeft><![CDATA[]]></RequestItemLeft>
					<RequestItemTop><![CDATA[]]></RequestItemTop>
					<RequestItemInside><![CDATA[]]></RequestItemInside>
					<RequestItemQuant><![CDATA[$tot_quant]]></RequestItemQuant>
					<RequestItemHours><![CDATA[$tot_hours]]></RequestItemHours>
					<RequestItemCost><![CDATA[$".$tot_cost."]]></RequestItemCost>
					<RequestItemUnit><![CDATA[]]></RequestItemUnit>
					<RequestItemTrash><![CDATA[]]></RequestItemTrash>			
				</MaintRequestItem>
				<TotItemCost><![CDATA[".$tot_cost."]]></TotItemCost>
				<TotItemHours><![CDATA[".$tot_hours."]]></TotItemHours>
			";
			$sqlu="
				update maint_requests set 
					down_time_hours='".sql_friendly($tot_hours)."',
					cost='".sql_friendly($tot_cost)."' 
				where id='".sql_friendly($_POST['maint_id'] )."'
			";		
			//simple_query($sqlu);
		}
		else
		{
			$rval.= "
				<MaintRequestItem>
					<RequestItemLink><![CDATA[]]></RequestItemLink>					
					<RequestItemName><![CDATA[No Line Items]]></RequestItemName>
					<RequestItemCat><![CDATA[]]></RequestItemCat>
					<RequestItemMaker><![CDATA[]]></RequestItemMaker>
					<RequestItemModel><![CDATA[]]></RequestItemModel>
					<RequestItemFront><![CDATA[]]></RequestItemFront>
					<RequestItemLeft><![CDATA[]]></RequestItemLeft>
					<RequestItemTop><![CDATA[]]></RequestItemTop>
					<RequestItemInside><![CDATA[]]></RequestItemInside>
					<RequestItemQuant><![CDATA[]]></RequestItemQuant>
					<RequestItemHours><![CDATA[]]></RequestItemHours>
					<RequestItemCost><![CDATA[]]></RequestItemCost>
					<RequestItemUnit><![CDATA[]]></RequestItemUnit>
					<RequestItemTrash><![CDATA[]]></RequestItemTrash>		
				</MaintRequestItem>
				<TotItemCost><![CDATA[0.00]]></TotItemCost>
				<TotItemHours><![CDATA[0.00]]></TotItemHours>
			";
		}	
		display_xml_response("<rslt>1</rslt>$rval");	
		
	}
	function ajax_get_single_line_item()
	{
		$rval = "";
		
		$mrr_adder=" and ref_id='".sql_friendly($_POST['maint_id'] )."' and id='".sql_friendly($_POST['item_id'] )."'";
			
		$sql = "
				select *
				from maint_line_items
				where deleted = 0 ".$mrr_adder."
				order by linedate_added asc, id asc
			
		";		// and active=1
		$data = simple_query($sql);
				
		while($row = mysqli_fetch_array($data))
		{
			
			$actor=0;	
			
			$myid=$_POST['item_id'];
			$maint=$_POST['maint_id'];
			
			$main_desc=trim($row['lineitem_desc']);			
			$maker=trim($row['make']);			
			$model=trim($row['model']);
			
			$quant=$row['quantity'];			//add number of items
			$hours=number_format($row['down_time_hours'],2);
			$unit_cost=number_format($row['item_cost'],2);
						
			$cat_type=$row['cat_id'];	
			$front_type=$row['location_front'];	
			$left_type=$row['location_left'];	
			$top_type=$row['location_top'];	
			$inside_type=$row['location_inside'];	
					
			if($row['active']==1) 	$actor=1;
						
			//xml output
			$rval.= "
				<MaintRequestItem>	
					<RequestItemID><![CDATA[$myid]]></RequestItemID>
					<RequestItemRefer><![CDATA[$maint]]></RequestItemRefer>				
					<RequestItemName><![CDATA[$main_desc]]></RequestItemName>
					<RequestItemCat><![CDATA[$cat_type]]></RequestItemCat>
					<RequestItemMaker><![CDATA[$maker]]></RequestItemMaker>
					<RequestItemModel><![CDATA[$model]]></RequestItemModel>
					<RequestItemFront><![CDATA[$front_type]]></RequestItemFront>
					<RequestItemLeft><![CDATA[$left_type]]></RequestItemLeft>
					<RequestItemTop><![CDATA[$top_type]]></RequestItemTop>
					<RequestItemInside><![CDATA[$inside_type]]></RequestItemInside>
					<RequestItemQuant><![CDATA[$quant]]></RequestItemQuant>
					<RequestItemHours><![CDATA[$hours]]></RequestItemHours>
					<RequestItemUnit><![CDATA[".$unit_cost."]]></RequestItemUnit>
					<RequestItemActive><![CDATA[$actor]]></RequestItemActive>	
				</MaintRequestItem>
			";
			
		}	
			
		display_xml_response("<rslt>1</rslt>$rval");	
		
	}
	
	function ajax_remove_one_maint_line_item() {
		
		$myid=$_POST['item_id'];
		$maint=$_POST['maint_id'];
		$sql = "
			update maint_line_items
			
			set	deleted = 1
			where ref_id='".sql_friendly( $maint)."' and id='".sql_friendly($myid )."'
		";
		$data_delete = simple_query($sql);
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed Item ".$myid." from Maint Request ".$maint.". ";
		//......................................................................................................................................................
	
		$rval= "
				<MaintRequestItem>	
					<RequestItemID><![CDATA[$myid]]></RequestItemID>
					<RequestItemRefer><![CDATA[$maint]]></RequestItemRefer>
				</MaintRequestItem>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function ajax_remove_mr_quick_note_entry()
     {
          $myid=$_POST['id'];
     
          $sql = "
			update mr_unit_locations set		
			     deleted = 1
			where id='".sql_friendly($myid )."'
		";
          simple_query($sql);
     
          display_xml_response("<rslt>1</rslt>");      //$rval
     }
	
	
	//added ...Nov 2011
	//find details about load stop
	function mrr_load_stop_odometer_grab()
	{
		$stop_id=$_POST['stop_id'];
		$truck=0;
		$dropodom=0;
		$dropdate="00/00/0000";
		$truck=mrr_stop_truck_finder($stop_id);
		
		$rval= "";
		
		//now get last odometer reading
		if($truck > 0 )
		{				
			$sql = "
				select load_handler_stops.load_handler_id,
					load_handler_stops.shipper_city,
					load_handler_stops.shipper_state,
					load_handler_stops.shipper_zip,
					load_handler_stops.stop_type_id,
					trucks_log.truck_id,
					trucks_log.driver_id,
					trucks_log.trailer_id,
					load_handler_stops.linedate_completed,
					load_handler_stops.odometer_reading 
				from load_handler_stops,trucks_log 
				where load_handler_stops.trucks_log_id=trucks_log.id 
					and trucks_log.truck_id='".sql_friendly( $truck )."'
					and load_handler_stops.id!='".sql_friendly( $stop_id )."'
					and load_handler_stops.stop_type_id=2 
					and load_handler_stops.deleted=0
					and trucks_log.deleted=0
				order by load_handler_stops.odometer_reading desc,
						load_handler_stops.linedate_completed desc
				limit 1			
			";
			$data = simple_query($sql);
			while($row = mysqli_fetch_array($data))
			{		
				$dropodom=$row['odometer_reading'];
				$dropdate=trim($row['linedate_completed']);	
				$dropdate=date("m/d/Y", strtotime($dropdate));
				$rval.= "
					<MRROdometerReader>	
						<OdometerValue><![CDATA[$dropodom]]></OdometerValue>
					</MRROdometerReader>
				";
			}	//end while loop
		}//end if		
		if( $dropodom > 0 )
		{
			$sql = "
				update load_handler_stops set odometer_reading='".sql_friendly( $dropodom )."' where id='".sql_friendly( $stop_id )."'			
			";
			$data = simple_query($sql);	
		}
		display_xml_response("<rslt>1</rslt>$rval");			
	}
	//find truck for this stop
	function mrr_stop_truck_finder($stop_id)
	{
		$log_id=0;
		$cur_load=0;
		$truck=0;
		$dropodom=0;
		$dropdate="00/00/0000";
		//use stop to get current load and log id
		$sql = "
				select trucks_log_id,
					load_handler_id 
					from load_handler_stops 
					where id='".sql_friendly( $stop_id )."' 			
		";
		$data2 = simple_query($sql);
		while($row2 = mysqli_fetch_array($data2))
		{		
			$log_id=$row2['trucks_log_id'];
			$cur_load=$row2['load_handler_id'];
		}
		
		//use log to get truck id
		$sql = "
				select truck_id 
					from trucks_log 
					where id='".sql_friendly( $log_id )."' 
					order by truck_id asc,trailer_id asc,id asc			
		";
		$data2 = simple_query($sql);
		while($row2 = mysqli_fetch_array($data2))
		{		
			$truck=$row2['truck_id'];
		}
			
		return $truck;		
	}//end revised function
	
	function mrr_get_customer_email()
	{
		$rval="";
		$cust_id=$_POST['cust_id'];
		
		$email=mrr_get_customer_id_email($cust_id);
		if($email!="")
		{		
			$rval.= "
					<MRRCustomer>	
						<MRRCustomerEmail><![CDATA[$email]]></MRRCustomerEmail>
					</MRRCustomer>
				";
		}		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_email_this_thing()
	{
		global $defaultsarray;
		$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
		$From=mrr_get_default_xvalue_by_xname('company_email_address');
		$FromName=mrr_get_default_xvalue_by_xname('company_name');
		
		
		$xref_id=$_POST['id'];
		$To=trim($_POST['email_address']);
		$ToName="";
		$mrr_main_path="https://trucking.conardtransportation.com/";
		
		$text_html=trim($_POST['html_body']);
		
		$text_html=str_replace("<a href=","<a name=",$text_html);
		$text_html=str_replace("<input ","<input disabled ",$text_html);
		$text_html=str_replace("<select ","<select disabled ",$text_html);
		$text_html=str_replace("<textarea ","<textarea disabled ",$text_html);
		
		$Subject=trim($_POST['subject']);
		$html="
			<div style='width:800px; height:50px; background-color:#000000;'>
     			<center><img src='".$mrr_main_path."".$comp_logo."' border='0' width='154' height='43' alt='".$FromName."' /></center>
     		</div> 
			".$text_html."
			<div style='width:800px;'>
				Please let us know when the maintenance status has been completed or updated.  Thank you.
				<br><br>
				
				<div style='float:right; width:50%' align='right'>
					Phone: 615-213-2270<br>Toll Free: 800-548-8672<br>Fax: 615-213-2280<br>Email: info@conardtransportation.com<br>
				</div>
				<div style='width:50%'>
     				Conard Logistics, Inc.<br>200 International Blvd<br>
					LaVergne, TN 37086 <br> <br> <br>
				</div>
				
     		</div> 
		";
				
		$Text=$html;
		
		sendMail($From,$FromName,$To,$ToName,$Subject,$Text,$html,'');
		
		$rval="
			<MRRSendMail>	
				<MRRSendAddress><![CDATA[$To]]></MRRSendAddress>
				<MRRSendMailMessage><![CDATA[$html]]></MRRSendMailMessage>
			</MRRSendMail>
		";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_email_this_quote()
	{
		$rval="";
		$quote=$_POST['quote_id'];
		$email=trim($_POST['email_address']);
		$From=mrr_get_default_xvalue_by_xname('company_email_address');
		$FromName=mrr_get_default_xvalue_by_xname('company_name');
		
		//get quote details
		$sql = "
			select *
			
			from quotes
			where id = '".sql_friendly($quote)."'
		";
		$data_quote = simple_query($sql);
		$row = mysqli_fetch_array($data_quote);
		
		$linedate = date("n/j/Y", strtotime($row['linedate']));
		$quote_name = $row['quote_name'];
		$bill_customer = $row['bill_customer'];
		$quote_notes = $row['quote_notes'];
		$customer_id = $row['customer_id'];
		$driver_id = $row['driver_id'];
		$trailer_id = $row['trailer_id'];
		$truck_id = $row['truck_id'];
		$days_run_otr = $row['days_run_otr'];
		$days_run_hourly = $row['days_run_hourly'];
		$loaded_miles = $row['miles_loaded'];
		$pcm_loaded_miles = $row['miles_pcm'];
		$deadhead_miles = $row['miles_deadhead'];
		$loaded_miles_hourly = $row['miles_hourly'];
		$hours_worked = $row['hours_worked'];
		$trailer_maint_per_mile = $row['maint_per_mile_trailer'];
		$tractor_maint_per_mile = $row['maint_per_mile_tractor'];
		$avg_mpg = $row['average_mpg'];
		$doe_fuel_avg = $row['fuel_avg'];
		$daily_cost = $row['daily_cost'];
		$labor_per_mile = $row['labor_per_mile'];
		$labor_per_hour = $row['labor_per_hour'];
		$team_drivers = $row['team_driver'];
		$load_taken = $row['load_taken'];
		$map_storage = $row['map_storage'];
		$expire_date = date("n/j/Y", strtotime($row['linedate_expires']));
		
		// get the stops

		$stpmiles=0;		$cntr=1;		$zips[0]=0;
		$all_stops="<table border=0>";
		$all_stops.="<tr>
					<td valign='top' align='right'>Stop </td>
					<td valign='top'>Location</td>
					<td valign='top' align='right'>Miles</td>
					<td valign='top' align='right'>Total</td>
				</tr>";
		$sql = "
			select *			
			from quotes_stops
			where quote_id = '".sql_friendly($quote)."'
				and deleted = 0
			order by stop_order_id
		";
		$data_stops = simple_query($sql);		
		while($row_stop = mysqli_fetch_array($data_stops)) {
			
			$zips[$cntr]=0;
			$stpmiles+=$row_stop['stop_miles'];
			$all_stops.="<tr>
					<td valign='top' align='right'>".$cntr." </td>
					<td valign='top'>".$row_stop['stop_location']."</td>
					<td valign='top' align='right'>".$row_stop['stop_miles']."</td>
					<td valign='top' align='right'>".$stpmiles."</td>
				</tr>";
				$tmp=substr($row_stop['stop_location'],0,5);
			$zips[$cntr]=(int) $tmp;
			$cntr++;
		}
		$all_stops.="</table>";
		
		$map_url="";	//http://maps.google.com/maps
		for($i=1; $i < $cntr; $i++)
		{
			if($i==1)
			{
				$map_url.="?saddr=".$zips[ $i ]."";
			}
			elseif($i==2)
			{
				$map_url.="&daddr=".$zips[ $i ]."";
			}
			else
			{
				$map_url.="+to:".$zips[ $i ]."";	
			}
		}
		$map="<a href='http://trucking.conardtransportation.com/quote_map.php?quote_id=".$quote."' target='_blank'>Click to view map of route.</a>";
		if($map_storage!=$map_url)
		{
			$sql = "
				update quotes
				set map_storage = '".sql_friendly($map_url)."'
				where id = '".sql_friendly($quote)."'
			";
			simple_query($sql);	
		}	
		
		
		// get the variable expenses		
		$all_expenses="<table border=0>";
		$all_expenses.="<tr>
					<td valign='top'>Expense</td>
					<td valign='top' align='right'>Amount</td>
					
				</tr>";
		$sql = "
			select *			
			from quotes_expenses
			where quote_id = '".sql_friendly($quote)."'
		";
		$data_expenses = simple_query($sql);		
		while($row_expense = mysqli_fetch_array($data_expenses)) {
			$opt_value= mrr_get_option_fvalue_by_id($row_expense['expense_type_id']);
			
			$all_expenses.="<tr>					
					<td valign='top'>".$opt_value."</td>
					<td valign='top' align='right'>$ ".$row_expense['amount']."</td>
				</tr>";			
		}
		$all_expenses.="</table>";
				
		$customer_name="";
		$sql = "
			select name_company from customers where id='".sql_friendly($customer_id)."'
		";
		$datax = simple_query($sql);		
		while($rowx = mysqli_fetch_array($datax)) {
			$customer_name=$rowx['name_company'];
		}
		
		$driver_name="";
		$sql = "
			select name_driver_first,name_driver_last from drivers where id='".sql_friendly($driver_id)."'
		";
		$datax = simple_query($sql);		
		while($rowx = mysqli_fetch_array($datax)) {
			$driver_name=$rowx['name_driver_first'];
			$driver_name.=" ".$rowx['name_driver_last'];
		}
		
		$truck_name="";
		$sql = "
			select name_truck from trucks where id='".sql_friendly($truck_id)."'
		";
		$datax = simple_query($sql);		
		while($rowx = mysqli_fetch_array($datax)) {
			$truck_name=$rowx['name_truck'];
		}
		
		$trailer_name="";
		$sql = "
			select trailer_name from trailers where id='".sql_friendly($trailer_id)."'
		";
		$datax = simple_query($sql);		
		while($rowx = mysqli_fetch_array($datax)) {
			$trailer_name=$rowx['trailer_name'];
		}
		
		$taken="No";
		$teamer="No";
		if( $load_taken > 0)		$taken="Yes";
		if( $team_drivers > 0)		$teamer="Yes";
		
		$class_style="margin:10px; text-align:left; padding:2px 10px; background-color:#F7F6FF; border:1px black solid;font-family:arial;font-size:12px;font-weight:normal;text-align:left;";
		$xstr="<br><b>$quote_name</b><br>
		To &nbsp;&nbsp;&nbsp;&nbsp;<b>$email</b><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>$customer_name</b><br>
		From <b>$FromName</b><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>$From</b><br><br>
		<table style='".$class_style."'>
		<tr>
			<td valign='top' width='150'><b>Quote Identification</b></td>
			<td valign='top' width='300'>$quote</td>
		</tr>
		<tr>
			<td valign='top'><b>Quote Name</b></td>
			<td valign='top'>$quote_name</td>
		</tr>
		<tr>
			<td valign='top'><b>Quote Notes</b></td>
			<td valign='top'>$quote_notes</td>
		</tr>
		<tr>
			<td valign='top'><b>Customer</b></td>
			<td valign='top'>$customer_name</td>
		</tr>
		<tr>
			<td valign='top'><b>Driver</b></td>
			<td valign='top'>$driver_name</td>
		</tr>
		<tr>
			<td valign='top'><b>Tractor</b></td>
			<td valign='top'>$truck_name</td>
		</tr>
		<tr>
			<td valign='top'><b>Trailer</b></td>
			<td valign='top'>$trailer_name</td>
		</tr>
		<tr>
			<td valign='top'><b>Quote Date</b></td>
			<td valign='top'>$linedate</td>
		</tr>
		<tr>
			<td valign='top'><b>Quote Expires</b></td>
			<td valign='top'>$expire_date</td>
		</tr>
		<tr>
			<td valign='top'><b>Load Taken</b></td>
			<td valign='top'>$taken</td>
		</tr>
		<tr>
			<td valign='top'><b>Team Drivers</b></td>
			<td valign='top'>$teamer</td>
		</tr>
		<tr>
			<td valign='top'><b>Stops</b></td>
			<td valign='top'>".$all_stops."</td>
		</tr>
		<tr>
			<td valign='top'><b>Total Miles</b></td>
			<td valign='top'>".number_format($pcm_loaded_miles,0)."</td>
		</tr>
		<tr>
			<td><b>Quote Amount</b></td>
			<td>$ $bill_customer</td>
		</tr>
		<tr>
			<td valign='top'><b>Map</b></td>
			<td valign='top'>".$map."</td>
		</tr>
		</table><br>";
		$txt=str_replace("<tr>"," \n <tr>",$xstr);	
		$txt=str_replace("<td>"," \t <td>",$xstr);
		$txt=strip_tags($txt);
				
		
		$To=$email;
		$ToName="";
		$Subject="Quote: ".$quote_name.".";
		$Text=$txt;
		$Html=$xstr;
		
		sendMail($From,$FromName,$To,$ToName,$Subject,$Text,$Html,'');
		
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Sent Email for Quote ".$quote.". ";
		$mrr_activity_log['driver_id']=$driver_id;
		$mrr_activity_log['truck_id']=$truck_id;
		$mrr_activity_log['trailer_id']=$trailer_id;
		//......................................................................................................................................................
	
		
		$rval.= "
					<MRRSendMail>	
						<MRRSendMailMessage><![CDATA[$xstr]]></MRRSendMailMessage>
					</MRRSendMail>
				";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function mrr_store_google_map()
	{
		$quote=$_POST['quote_id'];
		$map=$_POST['map_html'];
		
		$sql = "
				update quotes 
				set map_storage='".sql_friendly( $map )."' 
				where id='".sql_friendly( $quote )."'
		";
		simple_query($sql);
			
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Updated Map for Quote ".$quote.". ";
			//......................................................................................................................................................
		
					
		$rval= "
					<MRRQuoteMap>	
						<MRRQuote><![CDATA[$quote]]></MRRQuote>
						<MRRMap><![CDATA[$map]]></MRRMap>
					</MRRQuoteMap>
				";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_store_stop_miles()
	{
		$quote=$_POST['quote_id'];
		$stop=$_POST['stop_number'];
		$miles=$_POST['stop_miles'];	
		
		$sql = "
				update quotes_stops 
				set stop_miles='".sql_friendly( $miles )."' 
				where stop_order_id='".sql_friendly( $stop )."' and quote_id='".sql_friendly( $quote )."'
		";
		simple_query($sql);
				
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Update Stop ".$stop." Miles to ".$miles." for Quote ".$quote.". ";
			$mrr_activity_log['stop_id']=$stop;
			//......................................................................................................................................................
		
		$rval= "
					<MRRStopMiles>	
						<MRRStop><![CDATA[$stop]]></MRRStop>
						<MRRMileage><![CDATA[$miles]]></MRRMileage>
					</MRRStopMiles>
				";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function display_additional_contacts()
	{
		$contact_id=$_POST['contact_id'];
		$customer_id=$_POST['customer_id'];
		
		$buttn_label="Add Contact Info";
		if($contact_id > 0 )	$buttn_label="Update Contact Info";
		
		
		$contact='<table>
					<tr>
						<td colspan="2"><div class="mrr_link_like_on" onClick="mrr_load_contact_info(0);"><b>Add New Contact</b></div>
							<input type="hidden" id="add_contact_id" name="add_contact_id" value="0"></td>
					</tr>
					<tr>
						<td>Contact Name: '. show_help('admin_customers.php','add_contact_name') .'</td>
						<td><input id="add_contact_name" name="add_contact_name" value="" size="40"></td>
					</tr>
					<tr>
						<td>Address 1: '. show_help('admin_customers.php','add_contact_address1') .'</td>
						<td><input id="add_contact_address1" name="add_contact_address1" value="" size="40"></td>
					</tr>
					<tr>
						<td>Address 2: '. show_help('admin_customers.php','add_contact_address2') .'</td>
						<td><input id="add_contact_address2" name="add_contact_address2" value="" size="40"></td>
					</tr>
					<tr>
						<td>City: '. show_help('admin_customers.php','add_contact_city') .'</td>
						<td><input id="add_contact_city" name="add_contact_city" value=""></td>
					</tr>
					<tr>
						<td>State: '. show_help('admin_customers.php','add_contact_state') .'</td>
						<td><input id="add_contact_state" name="add_contact_state" value=""></td>
					</tr>
					<tr>
						<td>Zip: '. show_help('admin_customers.php','add_contact_zip') .'</td>
						<td><input id="add_contact_zip" name="add_contact_zip" value=""></td>
					</tr>
					<tr>
						<td>E-Mail: '. show_help('admin_customers.php','add_contact_email') .'</td>
						<td><input id="add_contact_email" name="add_contact_email" value="" size="40"></td>
					</tr>					
					<tr>
						<td>Phone 1 (Work):</td>
						<td><input id="add_contact_work" name="add_contact_work" value=""> '. show_help('admin_customers.php','add_contact_work') .'</td>
					</tr>
					<tr>
						<td>Phone 2 (Fax):</td>
						<td><input id="add_contact_fax" name="add_contact_fax" value=""> '. show_help('admin_customers.php','add_contact_fax') .'</td>
					</tr>
					<tr>
						<td>Phone 3 (Mobile):</td>
						<td><input id="add_contact_cell" name="add_contact_cell" value=""> '. show_help('admin_customers.php','add_contact_cell') .'</td>
					</tr>
					<tr>
						<td>Phone 4 (Home):</td>
						<td><input id="add_contact_home" name="add_contact_home" value=""> '. show_help('admin_customers.php','add_contact_home') .'</td>
					</tr>
					<tr>
						<td colspan="2"><center>
							<input type="button" id="update_contact_info" name="update_contact_info" value="'.$buttn_label.'" onClick="mrr_save_contact_info();">
						</center></td>
					</tr>
				</table>';
		
		
		$sql = "
			select *			
			from customer_contacts
			where customer_id = '".sql_friendly($customer_id)."' and deleted=0
			order by contact_name asc,zip asc,state asc,city asc,address1 asc,linedate_added asc
		";
		$data = simple_query($sql);
		$mn=mysqli_num_rows($data);
		
		$header="";
		if($mn > 0 )		$header="<b><center>Contact List</center></b><br>";
			
		$contact.="<br>$header				
				<table width='100%' border=0>";	
		while($row = mysqli_fetch_array($data))
		{
			$classy="mrr_link_like_ff";
			
			$id=$row['id'];
			$cust=$row['customer_id'];
			$stamp= date("n/j/Y", strtotime($row['linedate_added']));
			$name=$row['contact_name'];
			$addr1=$row['address1'];
			$addr2=$row['address2'];
			$city=$row['city'];
			$state=$row['state'];
			$zip=$row['zip'];
			$email=$row['email'];
			$home=$row['phone_home'];
			$work=$row['phone_work'];
			$cell=$row['phone_cell'];
			$fax=$row['phone_fax'];
			$act=$row['active'];
			
			if($act==1)		$classy="mrr_link_like_on";
			$trash='<a href="javascript:confirm_del_contact('.$id.','.$customer_id.')"><img src="images/delete_sm.gif" border="0"></a>';
			$linker="<span class='".$classy."' onClick='mrr_load_contact_info(".$id.");'><b>".$name."</b></span>";
			
			$contact.="<tr class='contact_".$id."'>
						<td valign='top' width=''>".$linker."</td>
						<td valign='top' align='right'>Home</td>	
						<td valign='top' align='right'>".$home."</td>
						<td valign='top'>&nbsp;</td>						
					</tr>
					<tr class='contact_".$id."'>
						<td valign='top'>".$addr1."</td>
						<td valign='top' align='right'>Work</td>
						<td valign='top' align='right'>".$work."</td>
						<td valign='top'>&nbsp;</td>
					</tr>
					<tr class='contact_".$id."'>
						<td valign='top'>".$addr2."</td>
						<td valign='top' align='right'>Mobile</td>
						<td valign='top' align='right'>".$cell."</td>
						<td valign='top'>&nbsp;</td>
					</tr>
					<tr class='contact_".$id."'>
						<td valign='top'>".$city.", ".$state."  ".$zip."</td>
						<td valign='top' width='60' align='right'>Fax</td>
						<td valign='top' width='100' align='right'>".$fax."</td>
						<td valign='top' width='15'>&nbsp;</td>
					</tr>
					<tr class='contact_".$id."' height='16'>
						<td valign='middle'>".$email."</td>
						<td valign='middle' align='right'>Added</td>
						<td valign='middle' align='right'>".$stamp."</td>
						<td valign='middle' align='right'>".$trash."</td>
					</tr>
					<tr class='contact_".$id."'>
						<td valign='top' colspan=3>&nbsp;</td>
					</tr>";
		}
		$contact.="</table>";
		echo $contact;
	}
	
	function save_additional_contacts()
	{
		global $datasource;

		$id=$_POST['ac_id'];
		
		$custid=$_POST['ac_cust'];
		$name=$_POST['ac_name'];
		$addr1=$_POST['ac_addr1'];
		$addr2=$_POST['ac_addr2'];	
		$city=$_POST['ac_city'];
		$state=$_POST['ac_state'];
		$zip=$_POST['ac_zip'];	
		$email=$_POST['ac_email'];
		$work=$_POST['ac_work'];
		$fax=$_POST['ac_fax'];
		$cell=$_POST['ac_cell'];
		$home=$_POST['ac_home'];
		
		if(trim($name)=="")		$name="General Contact";	
		
		if($id==0)
		{
			$sql = "
			insert into customer_contacts
			 	(id, 
					customer_id,
					linedate_added,
					contact_name,
					active,
					deleted)
				values (NULL,
					'".sql_friendly($custid)."',
					NOW(),
					'".sql_friendly($name)."',
					1,
					0)			
			";
			$data = simple_query($sql);	
			$id=mysqli_insert_id($datasource);	
		}
		if($id>0)
		{
			$sql = "
					update customer_contacts set
						contact_name='".sql_friendly( $name )."',
						address1='".sql_friendly( $addr1 )."',
						address2='".sql_friendly( $addr2 )."',
						city='".sql_friendly( $city )."',
						state='".sql_friendly( $state )."',
						zip='".sql_friendly( $zip )."',
						email='".sql_friendly( $email )."',
						phone_home='".sql_friendly( $home )."',
						phone_work='".sql_friendly( $work )."',
						phone_cell='".sql_friendly( $cell )."',
						phone_fax='".sql_friendly( $fax )."',
						active='1'
					
					where id='".sql_friendly( $id )."'
				";		
			
			simple_query($sql);	
					
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Updated Contact ".$id.". ";
			//......................................................................................................................................................
		
		}		
		$rval= "
					<Contact>	
						<ContactID><![CDATA[$id]]></ContactID>
						<ContactName><![CDATA[$name]]></ContactName>
					</Contact>
				";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function load_additional_contacts()
	{
		$contact_id=$_POST['contact_id'];
		
		$classy="mrr_link_like_off";
		$linedate="0000-00-00 00:00:00";
     	$custid=0;
     	$name="";
     	$addr1="";
     	$addr2="";	
     	$city="";
     	$state="";
     	$zip="";
     	$email="";
     	$work="";
     	$fax="";
     	$cell="";
     	$home="";
     	$active=0;
     	$linker="";
     	$trash="";
		
		
		if($contact_id>0)
		{
     		$sql = "
     			select *
     			
     			from customer_contacts
     			where id = '".sql_friendly($contact_id)."' and deleted=0
     		";
     		$data_quote = simple_query($sql);
     		$row = mysqli_fetch_array($data_quote);
     		
     		$linedate = date("n/j/Y", strtotime($row['linedate_added']));
     		$custid=$row['customer_id'];
     		$name=$row['contact_name'];
     		$addr1=$row['address1'];
     		$addr2=$row['address2'];	
     		$city=$row['city'];
     		$state=$row['state'];
     		$zip=$row['zip'];
     		$email=$row['email'];
     		$home=$row['phone_home'];
     		$work=$row['phone_work'];
     		$cell=$row['phone_cell'];
     		$fax=$row['phone_fax'];
     		$active=$row['active'];
     		if($active==1)		$classy="mrr_link_like_on";
     		$linker="";
     		$trash="";
     		
     		$trash='<a href="javascript:confirm_del_contact('.$contact_id.','.$custid.')"><img src="images/delete_sm.gif" border="0"></a>';
			
			$linker="<span class='".$classy."' onClick='mrr_load_contact_info(".$contact_id.");'><b>".$name."</b></span>";
		}
		//xml output
		$rval= "
				<Contact>
					<ContactID><![CDATA[$contact_id]]></ContactID>
					<ContactCust><![CDATA[$custid]]></ContactCust>
					<ContactName><![CDATA[$name]]></ContactName>
					<ContactAddr1><![CDATA[$addr1]]></ContactAddr1>
					<ContactAddr2><![CDATA[$addr2]]></ContactAddr2>
					<ContactCity><![CDATA[$city]]></ContactCity>
					<ContactState><![CDATA[$state]]></ContactState>
					<ContactZip><![CDATA[$zip]]></ContactZip>
					<ContactEmail><![CDATA[$email]]></ContactEmail>
					<ContactWork><![CDATA[$work]]></ContactWork>
					<ContactFax><![CDATA[$fax]]></ContactFax>
					<ContactCell><![CDATA[$cell]]></ContactCell>	
					<ContactHome><![CDATA[$home]]></ContactHome>
					<ContactActive><![CDATA[$active]]></ContactActive>
					<ContactAdded><![CDATA[$linedate]]></ContactAdded>
					<ContactLinker><![CDATA[$linker]]></ContactLinker>	
					<ContactTrash><![CDATA[$trash]]></ContactTrash>
				</Contact>
			";
		
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	function kill_additional_contacts()
	{
		$contact_id=$_POST['contact_id'];
				
		$sql = "
			update customer_contacts
			set deleted = 1
			
			where id = '".sql_friendly($contact_id)."'
		";
		$data = simple_query($sql);	
				
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Removed Contact ".$contact_id.". ";
			//......................................................................................................................................................
		
		//xml output
		$rval= "
				<Contact>
					<ContactID><![CDATA[$contact_id]]></ContactID>
				</Contact>
			";
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	//help desk functions
	function display_help_desk()
	{
		$help_id=$_POST['help_id'];
		//$sect_id=$_POST['section_id'];
		
		$pgname=$_POST['pgname'];
		$fldname=$_POST['fldname'];
		$stext=$_POST['stext'];
		
		
		$mrr_adder="";
		
		if($help_id > 0 )			$mrr_adder.=" and id='".sql_friendly($help_id)."'";
		if(trim($pgname)!="")		$mrr_adder.=" and page_name='".sql_friendly($pgname)."'";
		if(trim($fldname)!="")		$mrr_adder.=" and field_name='".sql_friendly($fldname)."'";
		if(trim($stext)!="")
		{
			$mrr_adder.=" and (quick_text LIKE '%".sql_friendly($stext)."%' or help_text LIKE '%".sql_friendly($stext)."%'
						or page_name LIKE '%".sql_friendly($stext)."%' or field_name LIKE '%".sql_friendly($stext)."%') ";
		}
		
		$sql = "
			select *			
			from help_desk
			where deleted=0
				".$mrr_adder."
			order by page_name asc,field_name asc,quick_text asc,linedate_added asc
		";
		$data = simple_query($sql);
		//$mn=mysqli_num_rows($data);
		$contact="<table class='admin_menu1' width='100%' border=0>
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top'><b>Page Name</b></td>	
						<td valign='top'><b>Field Name</b></td>
						<td valign='top'><b>Quick Text</b></td>
						<td valign='top'>&nbsp;</td>						
					</tr>";
			
		while($row = mysqli_fetch_array($data))
		{
			$classy="mrr_link_like_off";
			
			$id=$row['id'];
			$pgnm=$row['page_name'];
			$fldnm=$row['field_name'];
			$quick=$row['quick_text'];			
			$act=$row['active'];
			$stamp= date("n/j/Y", strtotime($row['linedate_added']));
			
			
			if($act==1)		$classy="mrr_link_like_on";
			$trash='<a href="javascript:confirm_del_help('.$id.')"><img src="images/delete_sm.gif" border="0"></a>';
			$linker="<span class='".$classy."' onClick='mrr_load_help_info(".$id.");'><b>".$id."</b></span>";
			
			$contact.="<tr class='helper_".$id."'>
						<td valign='top' width=''>".$linker."</td>
						<td valign='top'>".$pgnm."</td>	
						<td valign='top'>".$fldnm."</td>
						<td valign='top'>".$quick."</td>
						<td valign='top'>".$trash."</td>						
					</tr>";
		}
		$contact.="</table>";
		echo $contact;
	}
		   		
	function save_help_desk()
	{
		global $datasource;

		$help_id=$_POST['help_id'];
		//$sect_id=$_POST['section_id'];
		
		$page_name=$_POST['help_page'];
		$field_name=$_POST['help_field'];
		$quick_text=$_POST['help_quick'];
		$help_text=$_POST['help_text'];
				
		if(trim($page_name)=="")		$page_name="General Help";	
		
		if($help_id==0)
		{
			$sql = "
			insert into help_desk
			 		(id,
			 		page_name,
			 		field_name,
			 		linedate_added,
			 		quick_text,
			 		help_text,
			 		active,
			 		deleted)
				values (NULL,
					'".sql_friendly($page_name)."',
					'".sql_friendly($field_name)."',
					NOW(),
					'',
					'',
					1,
					0)			
			";
			$data = simple_query($sql);	
			$help_id=mysqli_insert_id($datasource);	
		}
		if($help_id>0)
		{
			$sql = "
					update help_desk set
						page_name='".sql_friendly($page_name)."',
			 			field_name='".sql_friendly($field_name)."',
			 			quick_text='".sql_friendly($quick_text)."',
			 			help_text='".sql_friendly($help_text)."',
						active='1'
					
					where id='".sql_friendly( $help_id )."'
				";		
			
			simple_query($sql);	
		}	
				
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Updated Help Desk Item ".$help_id.". ";
			//......................................................................................................................................................
			
		$rval= "
					<Help>	
						<HelpID><![CDATA[$help_id]]></HelpID>
						<HelpPage><![CDATA[$page_name]]></HelpPage>
					</Help>
				";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function load_help_desk()
	{
		$help_id=$_POST['help_id'];
		//$sect_id=$_POST['section_id'];
		
		$classy="mrr_link_like_off";
		$linedate="00/00/0000";
     	$pgnm="";
		$fldnm="";
		$quick="";
		$help_text="";
     	$active=0;
     	$linker="";
     	$trash="";
		
		
		if($help_id>0)
		{
     		$sql = "
     			select *
     			
     			from help_desk
     			where id = '".sql_friendly($help_id)."' and deleted=0
     		";
     		$data = simple_query($sql);
     		$row = mysqli_fetch_array($data);
     		
     		$linedate = date("n/j/Y", strtotime($row['linedate_added']));
     		$id=$row['id'];
			$pgnm=$row['page_name'];
			$fldnm=$row['field_name'];
			$quick=$row['quick_text'];
			$help_text=$row['help_text'];
     		$active=$row['active'];
     		if($active==1)		$classy="mrr_link_like_on";
     		     		
     		$trash='<a href="javascript:confirm_del_help('.$help_id.')"><img src="images/delete_sm.gif" border="0"></a>';
			
			$linker="<span class='".$classy."' onClick='mrr_load_help_info(".$help_id.");'><b>".$id."</b></span>";
		}
		//xml output
		$rval= "
				<Help>
					<HelpID><![CDATA[$help_id]]></HelpID>
					<HelpPage><![CDATA[$pgnm]]></HelpPage>
					<HelpField><![CDATA[$fldnm]]></HelpField>
					<HelpQuick><![CDATA[$quick]]></HelpQuick>
					<HelpText><![CDATA[$help_text]]></HelpText>
					<HelpActive><![CDATA[$active]]></HelpActive>
					<HelpAdded><![CDATA[$linedate]]></HelpAdded>
					<HelpLinker><![CDATA[$linker]]></HelpLinker>
					<HelpTrash><![CDATA[$trash]]></HelpTrash>
				</Help>
			";
		
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	function kill_help_desk()
	{
		$help_id=$_POST['help_id'];
		//$sect_id=$_POST['section_id'];
				
		$sql = "
			update help_desk
			set deleted = 1
			
			where id = '".sql_friendly($help_id)."'
		";
		$data = simple_query($sql);	
				
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Removed Help Desk Item ".$help_id.". ";
			//......................................................................................................................................................
		
		//xml output
		$rval= "
				<Help>
					<HelpID><![CDATA[$help_id]]></HelpID>
				</Help>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function add_log_user_validation()
	{
		$cd=$_POST['mode'];
		$page=$_POST['page'];
		
		$sql = "
			insert into log_user_validation
				(id,
				linedate_added,
				user_id,
				page_url,
				clicked_yes)
			values 
				(NULL,
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($page)."',
				'".sql_friendly($cd)."')			
		";
		simple_query($sql);	
		
		display_xml_response("<rslt>1</rslt>");		
	}
	
	function load_customer_surcharge_list() {
		global $defaultsarray;
		/* get a list of companies that have separate fuel surcharge prices */
		$sql = "
			select customers.fuel_surcharge,
				customers.name_company,
				customers.id,
				(select fuel_surcharge.fuel_surcharge from fuel_surcharge where customer_id = customers.id and fuel_surcharge.range_lower <= $defaultsarray[fuel_surcharge] order by fuel_surcharge desc limit 1 ) as surcharge_list
			
			from customers 
			where customers.deleted = 0
				and customers.active = 1
			 	
			 having surcharge_list > 0 or customers.fuel_surcharge > 0
			order by name_company
		";
		$data_surcharge = simple_query($sql);
		
		
		$rval = "
				<table class='border_solid'>
				<tr>
					<td colspan='2'>
						<b><span class='fuel_surcharge_holder'>Fuel Surcharge Ntl Avg:</span></b>
						<b><span class='alert'>$defaultsarray[fuel_surcharge]</span> 
						&nbsp; 
						<span class='fuel_surcharge_holder'>$defaultsarray[fuel_surcharge_last_update]</span></b>
					</td>
				</tr>
		";
		$i = 0;
		while($row_surcharge = mysqli_fetch_array($data_surcharge)) {
			$i++;
			
			$rval .= "
				<tr>
					<td class='standard12' align='left'><b>$row_surcharge[name_company]</b></td>
					<td class='standard12' align='left'>&nbsp;&nbsp;&nbsp; 
						".($row_surcharge['fuel_surcharge'] > 0 ? $row_surcharge['fuel_surcharge'] : $row_surcharge['surcharge_list'])."
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
			";
		}
		$rval .= "</table>";
		
		//display_xml_response("<rslt>1</rslt><html><![CDAT[$rval]]></html>");	
		echo $rval;
	}
	
	//pull last surcharge update from file.
	function mrr_pull_surcharge()
	{
		$cust_id=$_POST['cust_id'];
		$alert_date=trim($_POST['alert_date']);
		$fuel_surcharge=0;
		
		$rval="";

		$resval="";
		
		if($alert_date!="")
		{
			if($alert_date=="")		$alert_date="0000-00-00";
			else					$alert_date=date("Y-m-d-", strtotime($alert_date));
			
			if($alert_date!="0000-00-00")
     		{
     			$sql = "
          			select NOW() as curtime
          				from trucks
          			where '".$alert_date."'>=NOW()
          		";
          		$data = simple_query($sql);
              		$row = mysqli_fetch_array($data);	
     			$resval=$row['curtime'];
     		}
     		if($resval=="")
     		{
          		$sql = "
          			select fuel_surcharge
          				from fuel_surcharge			
          			where customer_id = '".sql_friendly($cust_id)."'
          			order by id desc 
          			limit 1
          		";
          		$data = simple_query($sql);
              		$row = mysqli_fetch_array($data);	
          		$fuel_surcharge=$row['fuel_surcharge'];
          		//xml output
          		$rval= "
          				<Fuel>
          					<Customer><![CDATA[$cust_id]]></Customer>
          					<Dated><![CDATA[$alert_date]]></Dated>
          					<Surcharge><![CDATA[$fuel_surcharge]]></Surcharge>
          				</Fuel>
          			";
          	}
          	else
          	{
          		$alert_date="VOID";
     			$rval= "
     				<Fuel>
     					<Customer><![CDATA[$cust_id]]></Customer>
     					<Dated><![CDATA[$alert_date]]></Dated>
     					<Surcharge><![CDATA[$fuel_surcharge]]></Surcharge>
     				</Fuel>
     			";		
          	}		
		}
		else
		{
			$alert_date="VOID";
     		$rval= "
     				<Fuel>
     					<Customer><![CDATA[$cust_id]]></Customer>
     					<Dated><![CDATA[$alert_date]]></Dated>
     					<Surcharge><![CDATA[$fuel_surcharge]]></Surcharge>
     				</Fuel>
     			";	
		}
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function add_trip_packs()
	{
		global $datasource;

		$load_id=$_POST['load_id'];
		$dispatch_id=$_POST['dispatch_id'];
		$truck_id=$_POST['truck_id'];
		$driver_id=$_POST['driver_id'];
		$rval="";	

		$sql = "
			insert into trip_packs
					(id,
					load_id,
					dispatch_id,
					truck_id,
					driver_id,
					linedate_added,
					deleted) 
				values (NULL,
					'".sql_friendly($load_id)."',
					'".sql_friendly($dispatch_id)."',
					'".sql_friendly($truck_id)."',
					'".sql_friendly($driver_id)."',
					NOW(),
					0)
			";
		$data = simple_query($sql);
		$newid=mysqli_insert_id($datasource);
		if($newid>0)
		{	
			$dater=date("Y-m-d");
			//xml output
			$rval= "
				<TripPack>
					<TripPackID><![CDATA[$newid]]></TripPackID>
     				<TripPackDate><![CDATA[$dater]]></TripPackDate>
     				<TripPackLoad><![CDATA[$load_id]]></TripPackLoad>
     				<TripPackDispatch><![CDATA[$dispatch_id]]></TripPackDispatch>
     				<TripPackTruck><![CDATA[$truck_id]]></TripPackTruck>
     				<TripPackDriver><![CDATA[$driver_id]]></TripPackDriver>
     			</TripPack>
			";
			
					
			//...................SET FOR USER ACTION LOG............................................................................................................
			global $mrr_activity_log;
			$mrr_activity_log["notes"]="Added Trip Pack ".$newid.". ";
			$mrr_activity_log['driver_id']=$driver_id;
			$mrr_activity_log['truck_id']=$truck_id;
			$mrr_activity_log['load_handler_id']=$load_id;
			$mrr_activity_log['dispatch_id']=$dispatch_id;
			
			mrr_add_user_change_log($_SESSION['user_id'],0,$driver_id,$truck_id,0,$load_id,$dispatch_id,0,"Added Trip Pack ".$newid.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
					
			//......................................................................................................................................................
		
		}
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function kill_trip_packs()
	{
		$tpid=$_POST['tpid'];
				
		$sql = "
			update trip_packs
			set deleted = 1
			
			where id = '".sql_friendly($tpid)."'
		";
		$data = simple_query($sql);
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed Trip Pack ".$tpid.". ";
		//......................................................................................................................................................
			
		//xml output
		$rval= "
				<TripPack>
					<TripPackID><![CDATA[$tpid]]></TripPackID>
				</TripPack>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function kill_trip_packs_alt()
	{
		$load_id=$_POST['load_id'];
		$dispatch_id=$_POST['dispatch_id'];
				
		$sql = "
			update trip_packs
			set deleted = 1
			
			where load_id='".sql_friendly($load_id)."'
				and dispatch_id='".sql_friendly($dispatch_id)."' 
		";
		$data = simple_query($sql);	
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed Trip Pack for Load ".$load_id." and Dispatch ".$dispatch_id.".";
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$dispatch_id;
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,$dispatch_id,0,"Removed Trip Pack for Load ".$load_id." and Dispatch ".$dispatch_id.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
			
		//......................................................................................................................................................
		
		//xml output
		$rval= "
				<TripPack>
					<TripPackLoad><![CDATA[$load_id]]></TripPackLoad>
     				<TripPackDispatch><![CDATA[$dispatch_id]]></TripPackDispatch>
				</TripPack>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function mrr_kill_search_note()
	{
		$note_id=$_POST['note_id'];
				
		$sql = "
			update notes_main
			set deleted = 1
			
			where id = '".sql_friendly($note_id)."'
		";
		$data = simple_query($sql);	
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed Note ".$note_id.". ";
		//......................................................................................................................................................
		
		//xml output
		$rval= "
				<Note>
					<NoteID><![CDATA[$note_id]]></NoteID>
				</Note>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_kill_search_file()
	{
		$file_id=$_POST['file_id'];
				
		$sql = "
			update attachments
			set deleted = 1
			
			where id = '".sql_friendly($file_id)."'
		";
		$data = simple_query($sql);	
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed attachment ".$file_id.". ";
		//......................................................................................................................................................
		
		//xml output
		$rval= "
				<File>
					<FileID><![CDATA[$file_id]]></FileID>
				</File>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	
	function mrr_kill_accident_reports()
	{
		$acc_id=$_POST['acc_id'];
				
		$sql = "
			update accident_reports
			set deleted = 1
			
			where id = '".sql_friendly($acc_id)."'
		";
		$data = simple_query($sql);	
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed Accident Report ".$acc_id.". ";
		//......................................................................................................................................................
			
		//xml output
		$rval= "
				<Accident>
					<AccidentID><![CDATA[$acc_id]]></AccidentID>
				</Accident>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_kill_accident_damage()
	{
		$acc_id=$_POST['acc_id'];
				
		$sql = "
			update accident_damage
			set deleted = 1
			
			where id = '".sql_friendly($acc_id)."'
		";
		$data = simple_query($sql);	
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed Accident Damage for Accident ".$acc_id.". ";
		//......................................................................................................................................................
		
		//xml output
		$rval= "
				<Accident>
					<AccidentID><![CDATA[$acc_id]]></AccidentID>
				</Accident>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_kill_all_accident_damage()
	{
		$acc_id=$_POST['acc_id'];
				
		$sql = "
			update accident_damage
			set deleted = 1
			
			where xref_id = '".sql_friendly($acc_id)."'
		";
		$data = simple_query($sql);	
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Removed All Accident Damage for Accident ".$acc_id.". ";
		//......................................................................................................................................................
		
		//xml output
		$rval= "
				<Accident>
					<AccidentID><![CDATA[$acc_id]]></AccidentID>
				</Accident>
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_add_accident_reports()
	{
		global $datasource;

		$newid=$_POST['accident_id'];
		
          $driver_id=$_POST['driver_id'];
          $truck_id=$_POST['truck_id'];
          $trailer_id=$_POST['trailer_id'];
          $dispatch_id=$_POST['dispatch_id'];
          $load_id=$_POST['load_id'];
          $accident_date=$_POST['accident_date'];
          $claim_date=$_POST['claim_date'];
          $insurance_claim=$_POST['insurance_claim'];
          $insurance_covered=$_POST['insurance_covered'];
          $reviewed=$_POST['reviewed'];
          $insurance_company=$_POST['insurance_company'];
          $accident_desc=$_POST['accident_desc'];
          $accident_cost=$_POST['accident_cost'];
          $accident_deductable=$_POST['accident_deductable'];
          $accident_downtime=$_POST['accident_downtime'];
          $injury_desc=$_POST['injury_desc'];
          $injury_cost=$_POST['injury_cost'];
          $injury_deductable=$_POST['injury_deductable'];
          $injury_downtime=$_POST['injury_downtime'];
          $driver_desc=$_POST['driver_desc'];
          $driver_cost=$_POST['driver_cost'];
          $driver_deductable=$_POST['driver_deductable'];
          $driver_downtime=$_POST['driver_downtime'];
          $maint_id=$_POST['maint_id'];
          $completed_date=$_POST['completed_date'];
          $active=1;	//$_POST['active'];
          $notes_and_updates=$_POST['notes_and_updates'];
          $accident_number=$_POST['accident_number'];
		
		$rval="";	
		if($newid==0)
		{
			$sql = "
			insert into accident_reports
					(id, 
					truck_id,
					trailer_id,
					driver_id,
					dispatch_id,
					load_id,
					linedate_added,				
					active,
					accident_number,
					deleted) 
				values (NULL,
					'".sql_friendly($truck_id)."',
					'".sql_friendly($trailer_id)."',
					'".sql_friendly($driver_id)."',					
					'".sql_friendly($dispatch_id)."',
					'".sql_friendly($load_id)."',
					NOW(),
					1,
					'',
					0)
			";
			$data = simple_query($sql);
			$newid=mysqli_insert_id($datasource);
		}
		if($newid>0)
		{	
			if($accident_date!="")		$accident_date=date("Y-m-d", strtotime($accident_date));	else		$accident_date="0000-00-00 00:00:00";
			if($claim_date!="")			$claim_date=date("Y-m-d", strtotime($claim_date));		else		$claim_date="0000-00-00 00:00:00";
			if($completed_date!="")		$completed_date=date("Y-m-d", strtotime($completed_date));	else		$completed_date="0000-00-00";
			
			$sql = "
			update accident_reports set
					truck_id='".sql_friendly($truck_id)."',
					trailer_id='".sql_friendly($trailer_id)."',
					driver_id='".sql_friendly($driver_id)."',
					dispatch_id='".sql_friendly($dispatch_id)."',
					load_id='".sql_friendly($load_id)."',
					accident_date='".( $accident_date !='0000-00-00 00:00:00' ? sql_friendly($accident_date) : '0000-00-00 00:00:00')."',
					insurance_company='".sql_friendly($insurance_company)."',
					claim_date='".( $claim_date !='0000-00-00 00:00:00' ? sql_friendly($claim_date) : '0000-00-00 00:00:00')."',
					insurance_claim='".( $insurance_claim > 0 ? '1' : '0')."',
					insurance_covered='".( $insurance_covered > 0 ? '1' : '0')."',										
					accident_desc='".sql_friendly($accident_desc)."',
					accident_cost='".( $accident_cost != '' ? sql_friendly( $accident_cost ) : "0.00")."',
					accident_deductable='".( $accident_deductable != '' ? sql_friendly( $accident_deductable ) : "0.00")."',
					accident_downtime='".( $accident_downtime != '' ? sql_friendly( $accident_downtime ) : "0.00")."',					
					injury_desc='".sql_friendly($injury_desc)."',
					injury_cost='".( $injury_cost != '' ? sql_friendly( $injury_cost ) : "0.00")."',
					injury_deductable='".( $injury_deductable != '' ? sql_friendly( $injury_deductable ) : "0.00")."',
					injury_downtime='".( $injury_downtime != '' ? sql_friendly( $injury_downtime ) : "0.00")."',					
					driver_desc='".sql_friendly($driver_desc)."',
					driver_cost='".( $driver_cost != '' ? sql_friendly( $driver_cost ) : "0.00")."',
					driver_deductable='".( $driver_deductable != '' ? sql_friendly( $driver_deductable ) : "0.00")."',
					driver_downtime='".( $driver_downtime != '' ? sql_friendly( $driver_downtime ) : "0.00")."',										
					maint_id='".sql_friendly($maint_id)."',
					accident_number='".sql_friendly(trim($accident_number))."',
					reviewed='".( $reviewed > 0 ? '1' : '0')."',	
					completed_date='".( $completed_date !='0000-00-00' ? sql_friendly($completed_date) : '0000-00-00')."',
					notes_and_updates = '".sql_friendly($notes_and_updates)."',
					active='1'
				where id='".sql_friendly($newid)."'
			";
			simple_query($sql);			
			$dater=date("Y-m-d");
			//xml output
			$rval= "
				<Accident>
					<AccidentID><![CDATA[$newid]]></AccidentID>
					<AccidentNumber><![CDATA[".trim($accident_number)."]]></AccidentNumber>
     				<AccidentDated><![CDATA[$dater]]></AccidentDated>
     				<AccidentLoad><![CDATA[$load_id]]></AccidentLoad>
     				<AccidentDispatch><![CDATA[$dispatch_id]]></AccidentDispatch>
     				<AccidentTruck><![CDATA[$truck_id]]></AccidentTruck>
     				<AccidentTrailer><![CDATA[$trailer_id]]></AccidentTrailer>
     				<AccidentDriver><![CDATA[$driver_id]]></AccidentDriver>
     				<AccidentDate><![CDATA[$accident_date]]></AccidentDate>
     				<AccidentClaimDate><![CDATA[$claim_date]]></AccidentClaimDate>
     				<AccidentInsClaim><![CDATA[$insurance_claim]]></AccidentInsClaim>
     				<AccidentInsCover><![CDATA[$insurance_covered]]></AccidentInsCover>
     				<AccidentReviewed><![CDATA[$reviewed]]></AccidentReviewed>
     				<AccidentInsComp><![CDATA[$insurance_company]]></AccidentInsComp>
     				<AccidentADesc><![CDATA[$accident_desc]]></AccidentADesc>
     				<AccidentACost><![CDATA[$accident_cost]]></AccidentACost>
     				<AccidentADeduct><![CDATA[$accident_deductable]]></AccidentADeduct>
     				<AccidentADowntime><![CDATA[$accident_downtime]]></AccidentADowntime>
     				<AccidentIDesc><![CDATA[$injury_desc]]></AccidentIDesc>
     				<AccidentICost><![CDATA[$injury_cost]]></AccidentICost>
     				<AccidentIDeduct><![CDATA[$injury_deductable]]></AccidentIDeduct>
     				<AccidentIDowntime><![CDATA[$injury_downtime]]></AccidentIDowntime>
     				<AccidentDDesc><![CDATA[$driver_desc]]></AccidentDDesc>
     				<AccidentDCost><![CDATA[$driver_cost]]></AccidentDCost>
     				<AccidentDDeduct><![CDATA[$driver_deductable]]></AccidentDDeduct>
     				<AccidentDDowntime><![CDATA[$driver_downtime]]></AccidentDDowntime>
     				<AccidentMaintID><![CDATA[$maint_id]]></AccidentMaintID>
     				<AccidentActive><![CDATA[$active]]></AccidentActive>
     				<AccidentCompleted><![CDATA[$completed_date]]></AccidentCompleted>
     				<NotesUpdates><![CDATA[$notes_and_updates]]></NotesUpdates>
     			</Accident>     			
			";			
		}
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Report updated for Accident ".$newid.". ";
		$mrr_activity_log['driver_id']=$driver_id;
		$mrr_activity_log['truck_id']=$truck_id;
		$mrr_activity_log['trailer_id']=$trailer_id;
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$dispatch_id;
		//......................................................................................................................................................
			
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_get_accident_reports()
	{
		$newid=$_POST['accident_id'];
		
          $driver_id=0;
          $truck_id=0;
          $trailer_id=0;
          $dispatch_id=0;
          $load_id=0;
          $accident_date="0000-00-00 00:00:00";
          $dater="0000-00-00 00:00:00";
          $claim_date="0000-00-00 00:00:00";
          $insurance_claim=0;
          $insurance_covered=0;
          $reviewed=0;
          $insurance_company="";
          $accident_desc="";
          $accident_cost="0.00";
          $accident_deductable="0.00";
          $accident_downtime="0.00";
          $injury_desc="";
          $injury_cost="0.00";
          $injury_deductable="0.00";
          $injury_downtime="0.00";
          $driver_desc="";
          $driver_cost="0.00";
          $driver_deductable="0.00";
          $driver_downtime="0.00";
          $maint_id=0;
          $active=0;
          $completed_date="0000-00-00";
          $notes_and_updates="";
          
          $accident_number="";
		
		$rval="";	
		if($newid > 0)
		{
			$sql = "
				select * 
				from accident_reports
				where id='".sql_friendly($newid)."'
			";
			$data = simple_query($sql);
			while($row = mysqli_fetch_array($data))
			{	
          		$driver_id=$row['driver_id'];
                    $truck_id=$row['truck_id'];
                    $trailer_id=$row['trailer_id'];
                    $dispatch_id=$row['dispatch_id'];
                    $load_id=$row['load_id'];
                    $dater=($row['linedate_added']!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($row['linedate_added'])) : '');
                    $accident_date=($row['accident_date']!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($row['accident_date'])) :'');
                    $claim_date=($row['claim_date']!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($row['claim_date'])):'');
                    $insurance_claim=$row['insurance_claim'];
                    $insurance_covered=$row['insurance_covered'];
                    $reviewed=$row['reviewed'];
                    $insurance_company=$row['insurance_company'];
                    $accident_desc=$row['accident_desc'];
                    $accident_cost=$row['accident_cost'];
                    $accident_deductable=$row['accident_deductable'];
                    $accident_downtime=$row['accident_downtime'];
                    $injury_desc=$row['injury_desc'];
                    $injury_cost=$row['injury_cost'];
                    $injury_deductable=$row['injury_deductable'];
                    $injury_downtime=$row['injury_downtime'];
                    $driver_desc=$row['driver_desc'];
                    $driver_cost=$row['driver_cost'];
                    $driver_deductable=$row['driver_deductable'];
                    $driver_downtime=$row['driver_downtime'];
                    $maint_id=$row['maint_id'];
                    $active=$row['active'];
                    $notes_and_updates=$row['notes_and_updates'];
                    $completed_date=($row['completed_date']!="0000-00-00" ? date("m/d/Y", strtotime($row['completed_date'])): '');
                    $accident_number=trim($row['accident_number']);
               }			
		}
			
		//xml output
		$rval= "
				<Accident>
					<AccidentID><![CDATA[$newid]]></AccidentID>
					<AccidentNumber><![CDATA[".trim($accident_number)."]]></AccidentNumber>
     				<AccidentDated><![CDATA[$dater]]></AccidentDated>
     				<AccidentLoad><![CDATA[$load_id]]></AccidentLoad>
     				<AccidentDispatch><![CDATA[$dispatch_id]]></AccidentDispatch>
     				<AccidentTruck><![CDATA[$truck_id]]></AccidentTruck>
     				<AccidentTrailer><![CDATA[$trailer_id]]></AccidentTrailer>
     				<AccidentDriver><![CDATA[$driver_id]]></AccidentDriver>
     				<AccidentDate><![CDATA[$accident_date]]></AccidentDate>
     				<AccidentClaimDate><![CDATA[$claim_date]]></AccidentClaimDate>
     				<AccidentInsClaim><![CDATA[$insurance_claim]]></AccidentInsClaim>
     				<AccidentInsCover><![CDATA[$insurance_covered]]></AccidentInsCover>
     				<AccidentReviewed><![CDATA[$reviewed]]></AccidentReviewed>
     				<AccidentInsComp><![CDATA[$insurance_company]]></AccidentInsComp>
     				<AccidentADesc><![CDATA[$accident_desc]]></AccidentADesc>
     				<AccidentACost><![CDATA[$accident_cost]]></AccidentACost>
     				<AccidentADeduct><![CDATA[$accident_deductable]]></AccidentADeduct>
     				<AccidentADowntime><![CDATA[$accident_downtime]]></AccidentADowntime>
     				<AccidentIDesc><![CDATA[$injury_desc]]></AccidentIDesc>
     				<AccidentICost><![CDATA[$injury_cost]]></AccidentICost>
     				<AccidentIDeduct><![CDATA[$injury_deductable]]></AccidentIDeduct>
     				<AccidentIDowntime><![CDATA[$injury_downtime]]></AccidentIDowntime>
     				<AccidentDDesc><![CDATA[$driver_desc]]></AccidentDDesc>
     				<AccidentDCost><![CDATA[$driver_cost]]></AccidentDCost>
     				<AccidentDDeduct><![CDATA[$driver_deductable]]></AccidentDDeduct>
     				<AccidentDDowntime><![CDATA[$driver_downtime]]></AccidentDDowntime>
     				<AccidentMaintID><![CDATA[$maint_id]]></AccidentMaintID>
     				<AccidentActive><![CDATA[$active]]></AccidentActive>
     				<AccidentCompleted><![CDATA[$completed_date]]></AccidentCompleted>
     				<NotesUpdates><![CDATA[$notes_and_updates]]></NotesUpdates>
     			</Accident>     			
			";			
		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_list_accident_reports()
	{
		$driver_id=$_POST['driver_id'];
          $truck_id=$_POST['truck_id'];
          $trailer_id=$_POST['trailer_id'];
          $dispatch_id=$_POST['dispatch_id'];
          $load_id=$_POST['load_id'];
		
		$rval = "";
		$mrr_adder="";
		if($driver_id > 0)		$mrr_adder.=" and driver_id='".sql_friendly($driver_id)."' ";
		if($truck_id > 0)		$mrr_adder.=" and truck_id='".sql_friendly($truck_id)."' ";
		if($trailer_id > 0)		$mrr_adder.=" and trailer_id='".sql_friendly($trailer_id)."' ";
		if($dispatch_id > 0)	$mrr_adder.=" and dispatch_id='".sql_friendly($dispatch_id)."' ";
		if($load_id > 0)		$mrr_adder.=" and load_id='".sql_friendly($load_id)."' ";
				
		$active_count=0;		
		$sql = "
				select *
				from accident_reports
				where deleted ='0'
					".$mrr_adder."					
				order by accident_date desc,id desc
			
		";		// and active=1 and completed_date='0000-00-00'
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$id=$row['id'];
			$driver_id=$row['driver_id'];
               $truck_id=$row['truck_id'];
               $trailer_id=$row['trailer_id'];
               $dispatch_id=$row['dispatch_id'];
               $load_id=$row['load_id'];
               $dater=($row['linedate_added']!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($row['linedate_added'])) : '');
               $accident_date=($row['accident_date']!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($row['accident_date'])) :'');
               $claim_date=($row['claim_date']!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($row['claim_date'])):'');
               $insurance_claim=$row['insurance_claim'];
               $insurance_covered=$row['insurance_covered'];
               $reviewed=$row['reviewed'];
               $insurance_company=$row['insurance_company'];
               $accident_desc=$row['accident_desc'];
               $accident_cost=$row['accident_cost'];
               $accident_deductable=$row['accident_deductable'];
               $accident_downtime=$row['accident_downtime'];
               $injury_desc=$row['injury_desc'];
               $injury_cost=$row['injury_cost'];
               $injury_deductable=$row['injury_deductable'];
               $injury_downtime=$row['injury_downtime'];
               $driver_desc=$row['driver_desc'];
               $driver_cost=$row['driver_cost'];
               $driver_deductable=$row['driver_deductable'];
               $driver_downtime=$row['driver_downtime'];
               $maint_id=$row['maint_id'];
               $active=$row['active'];
               $completed_date=($row['completed_date']!="0000-00-00" ? date("m/d/Y", strtotime($row['completed_date'])): '');
               $notes_and_updates=$row['notes_and_updates'];
               $accident_number=trim($row['accident_number']);
               
			//formatting
			if(strlen($accident_desc)>28)		$accident_desc=substr($accident_desc,0,25)."...";
			
			if($accident_desc == '') $accident_desc = '(no description entered)';
			
			$classy="inactive";
			if($active==1) 	
			{
				$active_count++;
				$classy="alert";
			}
			
			//$accident_cost=money_format('',$accident_cost);
			//$accident_deductable=money_format('',$accident_deductable);
			$accident_downtime=number_format($accident_downtime,2);
			
			$injury_cost=money_format('',$injury_cost);
			$injury_deductable=money_format('',$injury_deductable);
			$injury_downtime=number_format($injury_downtime,2);
			
			$driver_cost=money_format('',$driver_cost);
			$driver_deductable=money_format('',$driver_deductable);
			$driver_downtime=number_format($driver_downtime,2);
						
			$linker="<span class='".$classy."' onClick='load_accident_truck(".$id.");'><b>".$accident_desc."</b></span>";		//
			$trash='<a href="javascript:confirm_del_accident('.$id.')"><img src="images/delete_sm.gif" border="0"></a>';
			
			//get truck
			$sql2 = "
				select name_truck				
				from trucks
				where deleted = 0
					and id='".sql_friendly($truck_id)."' 
				limit 1
			";
			$tdata = simple_query($sql2);
			$trow = mysqli_fetch_array($tdata);
			$tnamer=$trow['name_truck'];
			
			//get trailer
			$sql2 = "
				select trailer_name				
				from trailers
				where deleted = 0
					and id='".sql_friendly($trailer_id)."' 
				limit 1
			";
			$tdata = simple_query($sql2);
			$trow = mysqli_fetch_array($tdata);
			$rnamer=$trow['trailer_name'];
			
			//get driver
			$sql3 = "
				select name_driver_first,name_driver_last				
				from drivers
				where deleted = 0
					and id='".sql_friendly($driver_id)."' 
				limit 1
			";
			$ddata = simple_query($sql3);
			$drow = mysqli_fetch_array($ddata);
			$dnamer=$drow['name_driver_first']." ".$drow['name_driver_last'];
						
			//xml output
			$rval.= "
				<Accident>
					<AccidentID><![CDATA[$id]]></AccidentID>
					<AccidentNumber><![CDATA[".trim($accident_number)."]]></AccidentNumber>
     				<AccidentDated><![CDATA[$dater]]></AccidentDated>
     				<AccidentLoad><![CDATA[$load_id]]></AccidentLoad>
     				<AccidentDispatch><![CDATA[$dispatch_id]]></AccidentDispatch>
     				<AccidentTruck><![CDATA[$truck_id]]></AccidentTruck>
     				<AccidentTrailer><![CDATA[$trailer_id]]></AccidentTrailer>
     				<AccidentDriver><![CDATA[$driver_id]]></AccidentDriver>     				
     				<AccidentDate><![CDATA[$accident_date]]></AccidentDate>
     				<AccidentClaimDate><![CDATA[$claim_date]]></AccidentClaimDate>
     				<AccidentInsClaim><![CDATA[$insurance_claim]]></AccidentInsClaim>
     				<AccidentInsCover><![CDATA[$insurance_covered]]></AccidentInsCover>
     				<AccidentReviewed><![CDATA[$reviewed]]></AccidentReviewed>
     				<AccidentInsComp><![CDATA[$insurance_company]]></AccidentInsComp>
     				<AccidentADesc><![CDATA[$accident_desc]]></AccidentADesc>
     				<AccidentACost><![CDATA[$accident_cost]]></AccidentACost>
     				<AccidentADeduct><![CDATA[$accident_deductable]]></AccidentADeduct>
     				<AccidentADowntime><![CDATA[$accident_downtime]]></AccidentADowntime>
     				<AccidentIDesc><![CDATA[$injury_desc]]></AccidentIDesc>
     				<AccidentICost><![CDATA[$injury_cost]]></AccidentICost>
     				<AccidentIDeduct><![CDATA[$injury_deductable]]></AccidentIDeduct>
     				<AccidentIDowntime><![CDATA[$injury_downtime]]></AccidentIDowntime>
     				<AccidentDDesc><![CDATA[$driver_desc]]></AccidentDDesc>
     				<AccidentDCost><![CDATA[$driver_cost]]></AccidentDCost>
     				<AccidentDDeduct><![CDATA[$driver_deductable]]></AccidentDDeduct>
     				<AccidentDDowntime><![CDATA[$driver_downtime]]></AccidentDDowntime>
     				<AccidentMaintID><![CDATA[$maint_id]]></AccidentMaintID>
     				<AccidentActive><![CDATA[$active]]></AccidentActive>
     				<AccidentLink><![CDATA[$linker]]></AccidentLink>
     				<AccidentTrash><![CDATA[$trash]]></AccidentTrash>
     				<AccidentTruckName><![CDATA[$tnamer]]></AccidentTruckName>
     				<AccidentTrailerName><![CDATA[$rnamer]]></AccidentTrailerName>
     				<AccidentDriverName><![CDATA[$dnamer]]></AccidentDriverName>
     				<AccidentCount><![CDATA[$active_count]]></AccidentCount>
     				<AccidentCompleted><![CDATA[$completed_date]]></AccidentCompleted>
     				<NotesUpdates><![CDATA[$notes_and_updates]]></NotesUpdates>
     			</Accident> 
			";
			
		}				
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function mrr_test_name_type()
	{
		$namer=$_POST['equipment_name'];	
		$result=1;		//true by default.
		
		$tester=$namer;
		$tester=str_replace(",","",$tester);
		
		//if(!is_numeric($tester))		$result=0;
		if($tester!=$namer)			$result=0;
		
		$rval= "
				<Namer>
					<NamerResult><![CDATA[$result]]></NamerResult>
					<NamerNamer><![CDATA[$namer]]></NamerNamer>
     			</Namer> 
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	
	function mrr_list_master_loads()
	{
		$mrr_cntr=0;
		$mrr_arr[0]=0;
		
		$rval= "
				<MasterLoads>
			";		
		$sql = "
				select distinct(load_handler.id),
					 load_handler.customer_id,
					 load_handler.master_load_label,
					 load_handler_stops.trucks_log_id,
					 trucks_log.driver_id,
					 trucks_log.truck_id,
					 trucks_log.trailer_id,
					 trucks_log.origin,
					 trucks_log.origin_state,
					 trucks_log.destination,
					 trucks_log.destination_state,
					 drivers.name_driver_first,
					 drivers.name_driver_last,
					 customers.name_company,
					 trucks.name_truck,
					 trailers.trailer_name
				from load_handler
					left join	load_handler_stops on load_handler.id=load_handler_stops.load_handler_id
					left join	trucks_log on trucks_log.id=load_handler_stops.trucks_log_id
					left join customers on trucks_log.customer_id = customers.id
					left join trailers on trailers.id = trucks_log.trailer_id
					left join trucks on trucks.id = trucks_log.truck_id
					left join drivers on drivers.id = trucks_log.driver_id
				where load_handler.deleted ='0'
					and load_handler_stops.deleted ='0'
					and drivers.deleted ='0'
					and trucks.deleted ='0'
					and trailers.deleted ='0'
					and customers.deleted ='0'
					and load_handler.master_load='1'
				order by load_handler.id desc
			
		";		// and active=1
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$id=$row['id'];
			$cust_id=$row['customer_id'];
			$driver_id=$row['driver_id'];
               $truck_id=$row['truck_id'];
               $trailer_id=$row['trailer_id'];
               $fname=$row['name_driver_first'];
               $lname=$row['name_driver_last'];
               $cname=$row['name_company'];
               $tru_name=$row['name_truck'];
               $tra_name=$row['trailer_name'];
               
               $org=$row['origin'];
               $org_state=$row['origin_state'];
               $dest=$row['destination'];
               $dest_state=$row['destination_state'];
               $labeler=$row['master_load_label'];  
                             
               $foundit=0; 
               for($i=0; $i < $mrr_cntr ; $i++)
               {
               	if($mrr_arr[ $i ] == $id )  $foundit=1; 
               } 
               if( $foundit==0 )
               {
               	$mrr_arr[ $mrr_cntr ]=$id; 	 
              	 	$mrr_cntr++;                     
                     
                    $linker="<a href='manage_load.php?load_id=".$id."' target='_blank'>".$id."</a>";
                    $clinker="<a href='admin_customers.php?eid=".$cust_id."' target='_blank'>".$cname."</a>";
                    $trulinker="<a href='admin_trucks.php?id=".$truck_id."' target='_blank'>".$tru_name."</a>";
                    $tralinker="<a href='admin_trailers.php?id=".$trailer_id."' target='_blank'>".$tra_name."</a>";
                    $dlinker="<a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$fname." ".$lname."</a>";
                    
                    
                    
                    $rval.= "
     				<MasterLoad>
     					<LoadID><![CDATA[$id]]></LoadID>
     					<LoadLabel><![CDATA[$labeler]]></LoadLabel>
     					<LoadLink><![CDATA[$linker]]></LoadLink>
     					<LoadCustID><![CDATA[$cust_id]]></LoadCustID>
     					<LoadCustName><![CDATA[$cname]]></LoadCustName>
     					<LoadCustLink><![CDATA[$clinker]]></LoadCustLink>
     					<LoadDriverID><![CDATA[$driver_id]]></LoadDriverID>
     					<LoadDriverName><![CDATA[$fname $lname]]></LoadDriverName>
     					<LoadDriverLink><![CDATA[$dlinker]]></LoadDriverLink>
     					<LoadTruckID><![CDATA[$truck_id]]></LoadTruckID>
     					<LoadTruckName><![CDATA[$tru_name]]></LoadTruckName>
     					<LoadTruckLink><![CDATA[$trulinker]]></LoadTruckLink>
     					<LoadTrailerID><![CDATA[$trailer_id]]></LoadTrailerID>
     					<LoadTrailerName><![CDATA[$tra_name]]></LoadTrailerName>
     					<LoadTrailerLink><![CDATA[$tralinker]]></LoadTrailerLink>
     					<LoadOrigin><![CDATA[$org]]></LoadOrigin>
     					<LoadOriginState><![CDATA[$org_state]]></LoadOriginState>
     					<LoadDest><![CDATA[$dest]]></LoadDest>
     					<LoadDestState><![CDATA[$dest_state]]></LoadDestState>
          			</MasterLoad> 
     			";
			}//end if...
          }	
          
          $rval.= "
				</MasterLoads> 
			";
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_copy_load_handler_from_master_load()
	{
		global $datasource;

		$load_id=$_POST['load_id'];
		$newid=0;
		
		//default settings used for budget items
     	$mrr_average_mpg=mrr_get_default_variable_setting('average_mpg');
          $mrr_billable_days_in_month=mrr_get_default_variable_setting('billable_days_in_month');
          $mrr_labor_per_hour=mrr_get_default_variable_setting('labor_per_hour');
          $mrr_labor_per_mile=mrr_get_default_variable_setting('labor_per_mile');
          $mrr_labor_per_mile_team=mrr_get_default_variable_setting('labor_per_mile_team');
          $mrr_local_driver_workweek_hours=mrr_get_default_variable_setting('local_driver_workweek_hours');
          $mrr_tractor_maint_per_mile=mrr_get_default_variable_setting('tractor_maint_per_mile');
          $mrr_trailer_maint_per_mile=mrr_get_default_variable_setting('trailer_maint_per_mile');
          
          $mrr_truck_accidents_per_mile=mrr_get_default_variable_setting('truck_accidents_per_mile');
     	$mrr_tires_per_mile=mrr_get_default_variable_setting('tires_per_mile');
     	$mrr_mileage_expense_per_mile=mrr_get_default_variable_setting('mileage_expense_per_mile');
     	$mrr_misc_expense_per_mile=mrr_get_default_variable_setting('misc_expense_per_mile');
     	
     	$mrr_trailer_mile_exp_per_mile=mrr_get_default_variable_setting('trailer_mile_exp_per_mile');
     	
     	$mrr_cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
          $mrr_general_liability=mrr_get_option_variable_settings('General Liability');
          $mrr_liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
          $mrr_payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
          $mrr_rent=mrr_get_option_variable_settings('Rent');
          $mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
          $mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
          $mrr_trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
          $mrr_misc_expenses=mrr_get_option_variable_settings('Misc Expenses');		
		
		if($load_id>0)
		{
			$sql = "
     				select *
     				from load_handler
     				where load_handler.deleted ='0'
     					and load_handler.id='".sql_friendly($load_id)."'
     			
     		";	
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data))
     		{
     			$columns="(id,
     				origin_address1,
     				origin_address2,
     				origin_city,
     				origin_state,
     				origin_zip,
     				dest_address1,
     				dest_address2,
     				dest_city,
     				dest_state,
     				dest_zip,
     				linedate_added,
     				special_instructions,
     				estimated_miles,
     				created_by_id,
     				customer_id,
     				deadhead_miles,
     				linedate_pickup_eta,
     				linedate_pickup_pta,
     				deleted,
     				linedate_dropoff_eta,
     				linedate_dropoff_pta,
     				quote,
     				fuel_charge_per_mile,
     				invoice_number,
     				shipper,
     				consignee,
     				load_available,
     				rate_unloading,
     				rate_stepoff,
     				rate_misc,
     				rate_fuel_surcharge_per_mile,
     				rate_fuel_surcharge_total,
     				rate_base,
     				rate_lumper,
     				preplan,
     				preplan_driver_id,
     				rate_fuel_surcharge,
     				actual_rate_fuel_surcharge,
     				actual_bill_customer,
     				days_run_otr,
     				days_run_hourly,
     				loaded_miles_hourly,
     				hours_worked,
     				actual_fuel_charge_per_mile,
     				load_number,
     				pickup_number,
     				delivery_number,
     				actual_total_cost,
     				actual_fuel_surcharge_per_mile,
     				otr_daily_cost,
     				linedate_invoiced,
     				sicap_invoice_number,
     				linedate_auto_created_reviewed,
     				auto_created,
     				linedate_edi_response_sent,
     				linedate_edi_invoice_sent,
     				predispatch_odometer,
     				predispatch_city,
     				predispatch_state,
     				predispatch_zip,
     				update_fuel_surcharge,
     				master_load,
     				budget_average_mpg,
     				budget_days_in_month,
     				budget_labor_per_hour,
     				budget_labor_per_mile,
     				budget_labor_per_mile_team,
     				budget_driver_week_hours,
     				budget_tractor_maint_per_mile,
     				budget_trailer_maint_per_mile,
     				budget_truck_accidents_per_mile,
     				budget_tires_per_mile,
     				budget_mileage_exp_per_mile,
     				budget_misc_exp_per_mile,
     				budget_cargo_insurance,
     				budget_general_liability,
     				budget_liability_damage,
     				budget_payroll_admin,
     				budget_rent,
     				budget_tractor_lease,
     				budget_trailer_exp,
     				budget_trailer_lease,
     				budget_misc_exp,
     				budget_active_trucks,
     				budget_active_trailers,
     				budget_day_variance,
     				dedicated_load,
     				billing_notes,
     				driver_notes)";
     			
     			$values="(NULL,
     				'".sql_friendly($row['origin_address1'])."',
     				'".sql_friendly($row['origin_address2'])."',
     				'".sql_friendly($row['origin_city'])."',
     				'".sql_friendly($row['origin_state'])."',
     				'".sql_friendly($row['origin_zip'])."',
     				'".sql_friendly($row['dest_address1'])."',
     				'".sql_friendly($row['dest_address2'])."',
     				'".sql_friendly($row['dest_city'])."',
     				'".sql_friendly($row['dest_state'])."',
     				'".sql_friendly($row['dest_zip'])."',
     				NOW(),
     				'".sql_friendly($row['special_instructions'])."',
     				'".sql_friendly($row['estimated_miles'])."',
     				'".sql_friendly($row['created_by_id'])."',
     				'".sql_friendly($row['customer_id'])."',
     				'".sql_friendly($row['deadhead_miles'])."',
     				'0000-00-00 00:00:00',
     				'0000-00-00 00:00:00',
     				'".sql_friendly($row['deleted'])."',
     				'0000-00-00 00:00:00',
     				'0000-00-00 00:00:00',
     				'".sql_friendly($row['quote'])."',
     				'".sql_friendly($row['fuel_charge_per_mile'])."',
     				'',
     				'".sql_friendly($row['shipper'])."',
     				'".sql_friendly($row['consignee'])."',
     				'".sql_friendly($row['load_available'])."',
     				'".sql_friendly($row['rate_unloading'])."',
     				'".sql_friendly($row['rate_stepoff'])."',
     				'".sql_friendly($row['rate_misc'])."',
     				'".sql_friendly($row['rate_fuel_surcharge_per_mile'])."',
     				'".sql_friendly($row['rate_fuel_surcharge_total'])."',
     				'".sql_friendly($row['rate_base'])."',
     				'".sql_friendly($row['rate_lumper'])."',
     				'".sql_friendly($row['preplan'])."',
     				'".sql_friendly($row['preplan_driver_id'])."',
     				'".sql_friendly($row['rate_fuel_surcharge'])."',
     				'".sql_friendly($row['actual_rate_fuel_surcharge'])."',
     				'".sql_friendly($row['actual_bill_customer'])."',
     				'".sql_friendly($row['days_run_otr'])."',
     				'".sql_friendly($row['days_run_hourly'])."',
     				'".sql_friendly($row['loaded_miles_hourly'])."',
     				'".sql_friendly($row['hours_worked'])."',
     				'".sql_friendly($row['actual_fuel_charge_per_mile'])."',
     				'',
     				'',
     				'',
     				'".sql_friendly($row['actual_total_cost'])."',
     				'".sql_friendly($row['actual_fuel_surcharge_per_mile'])."',
     				'".sql_friendly($row['otr_daily_cost'])."',
     				'0000-00-00 00:00:00',
     				'',
     				'0000-00-00 00:00:00',
     				'1',
     				'0000-00-00 00:00:00',
     				'0000-00-00 00:00:00',
     				'".sql_friendly($row['predispatch_odometer'])."',
     				'".sql_friendly($row['predispatch_city'])."',
     				'".sql_friendly($row['predispatch_state'])."',
     				'".sql_friendly($row['predispatch_zip'])."',
     				'0000-00-00',
     				'0',
          			'".sql_friendly($mrr_average_mpg)."',
     				'".sql_friendly($mrr_billable_days_in_month)."',
     				'".sql_friendly($mrr_labor_per_hour)."',
     				'".sql_friendly($mrr_labor_per_mile)."',	
     				'".sql_friendly($mrr_labor_per_mile_team)."',
     				'".sql_friendly($mrr_local_driver_workweek_hours)."',	
     				'".sql_friendly($mrr_tractor_maint_per_mile)."',
     				'".sql_friendly($mrr_trailer_maint_per_mile)."',	
     				'".sql_friendly($mrr_truck_accidents_per_mile)."',
     				'".sql_friendly($mrr_tires_per_mile)."',	
     				'".sql_friendly($mrr_mileage_expense_per_mile)."',
     				'".sql_friendly($mrr_misc_expense_per_mile)."',	
     				'".sql_friendly($mrr_cargo_insurance)."',
     				'".sql_friendly($mrr_general_liability)."',	
     				'".sql_friendly($mrr_liability_phy_damage)."',
     				'".sql_friendly($mrr_payroll___admin)."',	
     				'".sql_friendly($mrr_rent)."',
     				'".sql_friendly($mrr_tractor_lease)."',	
     				'".sql_friendly($mrr_trailer_expense)."',
     				'".sql_friendly($mrr_trailer_lease)."',	
     				'".sql_friendly($mrr_misc_expenses)."',				
     				'".sql_friendly( get_active_truck_count() )."',
     				'".sql_friendly( get_active_trailer_count() )."',
     				'".sql_friendly( get_daily_cost(0,0) )."',
     				'".sql_friendly($row['dedicated_load'])."',
     				'".sql_friendly($row['billing_notes'])."',
     				'".sql_friendly($row['driver_notes'])."')";
     			
                    $sql = "
     				insert into load_handler ".$columns." values ".$values."
     			";
                    simple_query($sql);			//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<TURN BACK ON WHEN READY
                    $newid=mysqli_insert_id($datasource);	//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<TURN BACK ON WHEN READY
                   
                   	if($newid>0)
                   	{
                   		
                   		//copy attachments...files already present, just copies the database linkage
                   		/*
                   		$sql1= "
     					select * 
     					from attachments
     					where section_id='8' and xref_id='".sql_friendly($load_id)."'
     					order by id asc
     				";
                   		$data1=simple_query($sql1);
                   		while($row1 = mysqli_fetch_array($data1))
                   		{
                   			$columns2="(id,
                   					fname,
                   					linedate_added,
                   					section_id,
                   					xref_id,
                   					deleted,
                   					file_ext,
                   					filesize,
                   					user_id,
                   					result,
                   					descriptor)";
                   			$values2="(NULL,
                   					'".sql_friendly($row1['fname'])."',
                   					NOW(),
                   					'8',
                   					'".sql_friendly($newid)."',
                   					'".sql_friendly($row1['deleted'])."',
                   					'".sql_friendly($row1['file_ext'])."',
                   					'".sql_friendly($row1['filesize'])."',
                   					'".sql_friendly($_SESSION['user_id'])."',
                   					'".sql_friendly($row1['result'])."',
                   					'".sql_friendly($row1['descriptor'])."')";
                   			       			
                   			$sql2 = "
     						insert into attachments ".$columns2." values ".$values2."
     					";     					
                   			simple_query($sql2);                   				
                   		}
                   		
                   		//copy actual VAR expenses
                   		$sql1= "
     					select * 
     					from load_handler_actual_var_exp 
     					where load_handler_id='".sql_friendly($load_id)."'
     					order by id asc
     				";
                   		$data1=simple_query($sql1);
                   		while($row1 = mysqli_fetch_array($data1))
                   		{
                   			$columns2="(id,load_handler_id,expense_type_id,expense_amount)";
                   			$values2="(NULL,'".sql_friendly($newid)."','".sql_friendly($row1['expense_type_id'])."','".sql_friendly($row1['expense_amount'])."')";
                   			       			
                   			$sql2 = "
     						insert into load_handler_actual_var_exp ".$columns2." values ".$values2."
     					";     					
                   			simple_query($sql2);                   				
                   		}
                   		
                   		//copy quote VAR expenses
                   		$sql1= "
     					select * 
     					from load_handler_quote_var_exp 
     					where load_handler_id='".sql_friendly($load_id)."'
     					order by id asc
     				";
                   		$data1=simple_query($sql1);
                   		while($row1 = mysqli_fetch_array($data1))
                   		{
                   			$columns2="(id,load_handler_id,expense_type_id,expense_amount)";
                   			$values2="(NULL,'".sql_friendly($newid)."','".sql_friendly($row1['expense_type_id'])."','".sql_friendly($row1['expense_amount'])."')";
                   			       			
                   			$sql2 = "
     						insert into load_handler_quote_var_exp ".$columns2." values ".$values2."
     					";     					
                   			simple_query($sql2);                   				
                   		}                  		
                   		*/
                   		
                   		//copy log entries
                   		$sql1= "
     					select * 
     					from trucks_log 
     					where load_handler_id='".sql_friendly($load_id)."'
     					order by id asc
     				";
                   		$data1=simple_query($sql1);
                   		while($row1 = mysqli_fetch_array($data1))
                   		{
                              $old_log_id=sql_friendly($row1['id']);
                              
                              //get current pay rate for now...regardless of when source load was made
                              $mrr_labor_mile="0.000";
                              $mrr_labor_hour="0.000";
                              if($row1['driver2_id'] > 0)
                              {
                              	$mrr_labor_mile=mrr_get_driver_pay_rate($row1['driver2_id'],6);
                              	$mrr_labor_hour=mrr_get_driver_pay_rate($row1['driver2_id'],7);	
                              }
                              
                              $columns2="(id,
                                  		truck_id,
                                  		trailer_id,
                                  		driver_id,
                                  		customer_id,
                                  		trailer,
                                  		location,
                                  		linedate_added,
                                  		deleted,
                                  		dayofweek,
                                  		user_id,
                                  		notes,
                                  		linedate_updated,
                                  		linedate,
                                  		color,
                                  		eta,
                                  		pta,
                                  		origin,
                                  		destination,
                                  		miles,
                                  		miles_deadhead,
                                  		load_id,
                                  		driver2_id,
                                  		load_handler_id,
                                  		dropped_trailer,
                                  		origin_state,
                                  		destination_state,
                                  		linedate_pickup_eta,
                                  		linedate_pickup_pta,
                                  		linedate_dropoff_eta,
                                  		linedate_dropoff_pta,
                                  		has_load_flag,
                                  		dispatch_completed,
                                  		daily_run_otr,
                                  		daily_run_hourly,
                                  		loaded_miles_hourly,
                                  		hours_worked,
                                  		cost,
                                  		profit,
                                  		otr_daily_cost,
                                  		avg_mpg,
                                  		tractor_maint_per_mile,
                                  		trailer_maint_per_mile,
                                  		labor_per_hour,
                                  		labor_per_mile,
                                  		daily_cost,
                                  		pcm_miles,
                                  		manual_miles_flag,
                                  		valid_trip_pack,
                                  		user_id_verified_trip_pack,
                                  		trailer_exp_per_mile,
                                  		driver_2_labor_per_mile,
                                  		driver_2_labor_per_hour)
                                  	";
                   			
                   			$values2="(NULL,
                   					'".sql_friendly($row1['truck_id'])."',
                                  		'".sql_friendly($row1['trailer_id'])."',
                                  		'".sql_friendly($row1['driver_id'])."',
                                  		'".sql_friendly($row1['customer_id'])."',
                                  		'".sql_friendly($row1['trailer'])."',
                                  		'".sql_friendly($row1['location'])."',
                                  		NOW(),
                                  		'".sql_friendly($row1['deleted'])."',
                                  		'".sql_friendly($row1['dayofweek'])."',
                                  		'".sql_friendly($_SESSION['user_id'])."',
                                  		'".sql_friendly($row1['notes'])."',
                                  		NOW(),
                                  		'0000-00-00 00:00:00',
                                  		'".sql_friendly($row1['color'])."',
                                  		'0000-00-00 00:00:00',
                                  		'0000-00-00 00:00:00',
                                  		'".sql_friendly($row1['origin'])."',
                                  		'".sql_friendly($row1['destination'])."',
                                  		'".sql_friendly($row1['miles'])."',
                                  		'".sql_friendly($row1['miles_deadhead'])."',
                                  		'".sql_friendly($row1['load_id'])."',
                                  		'".sql_friendly($row1['driver2_id'])."',
                                  		'".sql_friendly($newid)."',
                                  		'".sql_friendly($row1['dropped_trailer'])."',
                                  		'".sql_friendly($row1['origin_state'])."',
                                  		'".sql_friendly($row1['destination_state'])."',
                                  		'0000-00-00 00:00:00',
                                  		'0000-00-00 00:00:00',
                                  		'0000-00-00 00:00:00',
                                  		'0000-00-00 00:00:00',
                                  		'".sql_friendly($row1['has_load_flag'])."',
                                  		'0',
                                  		'".sql_friendly($row1['daily_run_otr'])."',
                                  		'".sql_friendly($row1['daily_run_hourly'])."',
                                  		'".sql_friendly($row1['loaded_miles_hourly'])."',
                                  		'".sql_friendly($row1['hours_worked'])."',
                                  		'".sql_friendly($row1['cost'])."',
                                  		'".sql_friendly($row1['profit'])."',
                                  		'".sql_friendly($row1['otr_daily_cost'])."',
                                  		'".sql_friendly($row1['avg_mpg'])."',
                                  		'".sql_friendly($row1['tractor_maint_per_mile'])."',
                                  		'".sql_friendly($row1['trailer_maint_per_mile'])."',
                                  		'".sql_friendly($row1['labor_per_hour'])."',
                                  		'".sql_friendly($row1['labor_per_mile'])."',
                                  		'".sql_friendly($row1['daily_cost'])."',
                                  		'".sql_friendly($row1['pcm_miles'])."',
                                  		'".sql_friendly($row1['manual_miles_flag'])."',
                                  		'".sql_friendly($row1['valid_trip_pack'])."',
                                  		'".sql_friendly($row1['user_id_verified_trip_pack'])."',
                                  		'".sql_friendly($mrr_trailer_mile_exp_per_mile)."',
                                  		'".sql_friendly($mrr_labor_mile)."',
                                  		'".sql_friendly($mrr_labor_hour)."')
                        		";
                   			       			
                   			$sql2 = "
     						insert into trucks_log ".$columns2." values ".$values2."
     					";     					
                   			//simple_query($sql2); 	
                   			//$logid=mysql_insert_id();
                   			
                   			
                        		//copy each load_handler_stops
                        		$sql2= "
          					select * 
          					from load_handler_stops 
          					where trucks_log_id='".sql_friendly($old_log_id)."' 
          					order by id asc         						
          				";	//and load_handler_id='".sql_friendly($load_id)."'
          				
                        		$data2=simple_query($sql2);
                        		while($row2 = mysqli_fetch_array($data2))
                        		{
                        			$columns3="(id,
                             			load_handler_id,
                             			trucks_log_id,
                             			shipper_name,
                             			shipper_address1,
                             			shipper_address2,
                             			shipper_city,
                             			shipper_state,
                             			shipper_zip,
                             			shipper_eta,
                             			shipper_pta,
                             			dest_name,
                             			dest_address1,
                             			dest_address2,
                             			dest_city,
                             			dest_state,
                             			dest_zip,
                             			dest_eta,
                             			dest_pta,
                             			deleted,
                             			linedate_added,
                             			created_by_user_id,
                             			linedate_pickup_eta,
                             			linedate_pickup_pta,
                             			linedate_dropoff_eta,
                             			linedate_dropoff_pta,
                             			stop_type_id,
                             			linedate_completed,
                             			directions,
                             			stop_phone,
                             			ignore_address,
                             			pcm_miles,
                             			odometer_reading,
                             			appointment_window,
                             			linedate_appt_window_start,
                             			linedate_appt_window_end)";
                        			
                        			$values3="(NULL,
                             			'".sql_friendly($newid)."',
                             			'0',
                             			'".sql_friendly($row2['shipper_name'])."',
                             			'".sql_friendly($row2['shipper_address1'])."',
                             			'".sql_friendly($row2['shipper_address2'])."',
                             			'".sql_friendly($row2['shipper_city'])."',
                             			'".sql_friendly($row2['shipper_state'])."',
                             			'".sql_friendly($row2['shipper_zip'])."',
                             			'0000-00-00 00:00:00',
                             			'0000-00-00 00:00:00',
                             			'".sql_friendly($row2['dest_name'])."',
                             			'".sql_friendly($row2['dest_address1'])."',
                             			'".sql_friendly($row2['dest_address2'])."',
                             			'".sql_friendly($row2['dest_city'])."',
                             			'".sql_friendly($row2['dest_state'])."',
                             			'".sql_friendly($row2['dest_zip'])."',
                             			'0000-00-00 00:00:00',
                             			'0000-00-00 00:00:00',
                             			'".sql_friendly($row2['deleted'])."',
                             			NOW(),
                             			'".sql_friendly($_SESSION['user_id'])."',
                             			'0000-00-00 00:00:00',
                             			'0000-00-00 00:00:00',
                             			'0000-00-00 00:00:00',
                             			'0000-00-00 00:00:00',
                             			'".sql_friendly($row2['stop_type_id'])."',
                             			'0000-00-00 00:00:00',
                             			'".sql_friendly($row2['directions'])."',
                             			'".sql_friendly($row2['stop_phone'])."',
                             			'".sql_friendly($row2['ignore_address'])."',
                             			'".sql_friendly($row2['pcm_miles'])."',
                             			'".sql_friendly($row2['odometer_reading'])."',
                             			0,
                             			'0000-00-00 00:00:00',
                             			'0000-00-00 00:00:00')";
                        			       		//".sql_friendly($logid)."
                        			       		//".sql_friendly($row2['directions'])."	
                        			       		
                        			$sql3 = "
          						insert into load_handler_stops ".$columns3." values ".$values3."
          					";     					
                        			simple_query($sql3);                   				
                        		}	//end load_handler_stop while loop
                   			                        			
                   		}//end trucls_log while loop      		
                   		                   		
               	}//NEWID if check
               	
               }//end load_handler row found		
     	
     	}//end LOAD_ID sent check
				
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Copied Master Load ".$load_id." to new Load ".$newid.". ";
		$mrr_activity_log['load_handler_id']=$load_id;
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,0,0,"Copied Master Load ".$load_id." to new Load ".$newid.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
						
		$rval= "
				<NewLoadID><![CDATA[$newid]]></NewLoadID>  
			";
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	function mrr_expand_punch_clock_data()
	{
		$rval= "";
		$user_id=$_POST['user_id'];
          $date_start=$_POST['date_start'];
          $date_ender=$_POST['date_ender'];
		
		if($date_start!="")		$date_start=date("Y-m-d", strtotime($date_start))." 00:00:00";	else		$date_start="0000-00-00 00:00:00";
		if($date_ender!="")		$date_ender=date("Y-m-d", strtotime($date_ender))." 23:59:59";	else		$date_ender="0000-00-00 00:00:00";
		
		$mrr_adder="";
		if($date_start!="0000-00-00 00:00:00")		$mrr_adder.=" and punch_clock.linedate_added>='".sql_friendly($date_start)."' ";
		if($date_ender!="0000-00-00 00:00:00")		$mrr_adder.=" and punch_clock.linedate_added<='".sql_friendly($date_ender)."' ";
		
		if($user_id>0)
		{
			$prev_id=0;
			$next_id=0;
			$tot_so_far=0;
			$sql = "
     				select punch_clock.id
     				from punch_clock
     				where punch_clock.user_id='".sql_friendly($user_id)."'
     					".$mrr_adder."
     				order by punch_clock.linedate_added asc,punch_clock.id asc     			
     		";	
     		$data = simple_query($sql);
     		while($row = mysqli_fetch_array($data))
     		{
     			$id=$row['id'];
     			$res=mrr_punch_clock_vals($id);
     			$next_id=mrr_punch_clock_next($id,$user_id);
     			$moder="Out";
     			$auto="";
     			if($res['clock_hrs'] >= 0)
     			{
          			if($res['clock_mode'] > 0)	$moder="In";
          			if($res['clock_auto'] > 0)	$auto="Auto";
          			$tot_so_far+=$res['clock_hrs'];
          			
                         $rval.= "
     				<PunchClock>
     					<PunchClockID><![CDATA[".$id."]]></PunchClockID>
     					<PunchClockUserID><![CDATA[".$res['user_id']."]]></PunchClockUserID>
     					<PunchClockUsername><![CDATA[".$res['username']."]]></PunchClockUsername>
     					<PunchClockFirstName><![CDATA[".$res['name_first']."]]></PunchClockFirstName>
     					<PunchClockLastName><![CDATA[".$res['name_last']."]]></PunchClockLastName>
     					<PunchClockStamp><![CDATA[".$res['linedate_added']."]]></PunchClockStamp>
     					<PunchClockDate><![CDATA[".$res['linedate_date']."]]></PunchClockDate>
     					<PunchClockTime><![CDATA[".$res['linedate_time']."]]></PunchClockTime>
     					<PunchClockDay><![CDATA[".$res['linedate_day']."]]></PunchClockDay>					
     					<PunchClockIP><![CDATA[".$res['ip_address']."]]></PunchClockIP>
     					<PunchClockAuto><![CDATA[".$res['clock_auto']."]]></PunchClockAuto>
     					<PunchClockAutoDisp><![CDATA[".$auto."]]></PunchClockAutoDisp>
     					<PunchClockMode><![CDATA[".$res['clock_mode']."]]></PunchClockMode>
     					<PunchClockModeDisp><![CDATA[".$moder."]]></PunchClockModeDisp>
     					<PunchClockHours><![CDATA[".$res['clock_hrs']."]]></PunchClockHours>
     					<PunchClockNotes><![CDATA[".$res['notes']."]]></PunchClockNotes>
     					<PunchClockPrevID><![CDATA[".$prev_id."]]></PunchClockPrevID>
     					<PunchClockNextID><![CDATA[".$next_id."]]></PunchClockNextID>
     					<PunchClockTotHrs><![CDATA[".number_format($tot_so_far,2)."]]></PunchClockTotHrs>
          			</PunchClock> 
     				";
                         $prev_id=$id;
               	}
			}	
		}
		
		display_xml_response("<rslt>1</rslt>$rval<sql><![CDATA[".$sql."]]></sql>");		
	}
	function mrr_update_punch_clock_data_notes()
	{
		$rval= "";
		$clock_id=$_POST['clock_id'];
		$new_notes=$_POST['new_notes'];
		
		mrr_punch_clock_update_notes_only($clock_id,$new_notes);
		
		
		$rval= "
				<ClockID><![CDATA[$clock_id]]></ClockID> 
				<NewNotes><![CDATA[$new_notes]]></NewNotes>  
			";
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	function mrr_update_punch_clock_data_hrs()
	{
		$rval= "";
		$user_id=$_POST['user_id'];
		$clock_id=$_POST['clock_id'];
		$mode_id=$_POST['mode_id'];
		$prev_id=$_POST['prev_id'];
		$next_id=$_POST['next_id'];
		$new_time=$_POST['new_time'];
		$dater=$_POST['dater'];
		$dater=date("Y-m-d", strtotime($dater));
		
		mrr_punch_clock_update_time_only($clock_id,$dater,$new_time);
		$dater=$dater." ".$new_time.":00";
		
		$totalhrs=0;
		if($mode_id==1 && $next_id > 0)
		{
			//clocked in, change next ID hours	
			$res1=mrr_punch_clock_vals($clock_id);
			$res2=mrr_punch_clock_vals($next_id);
			
     		$hrs1=date("H", strtotime($res1['linedate_time'] ));		$hrs1=(int)$hrs1;
          	$min1=date("i", strtotime($res1['linedate_time']));		$min1=(int)$min1;
          	$sec1=date("s", strtotime($res1['linedate_time']));		$sec1=(int)$sec1;
          	
          	$hrs2=date("H", strtotime($res2['linedate_time']));		$hrs2=(int)$hrs2;
          	$min2=date("i", strtotime($res2['linedate_time']));		$min2=(int)$min2;
          	$sec2=date("s", strtotime($res2['linedate_time']));		$sec2=(int)$sec2;
          	
          	if($sec1 >=30)	$min1++;
          	if($sec2 >=30)	$min2++;
          	
          	$totalmins1=($hrs1*60) + $min1;
          	$totalmins2=($hrs2*60) + $min2;
          	$totalhrs=($totalmins2 - $totalmins1)/60;	
     		$totalhrs=number_format($totalhrs,2);
     		mrr_punch_clock_update_hours_only($next_id,$totalhrs);
		}
		elseif($prev_id>0)
		{
			//clocked out, change this hours, but use previous ID time to compute hours.
			$res1=mrr_punch_clock_vals($prev_id);
			$res2=mrr_punch_clock_vals($clock_id);			
			
     		$hrs1=date("H", strtotime($res1['linedate_time'] ));		$hrs1=(int)$hrs1;
          	$min1=date("i", strtotime($res1['linedate_time']));		$min1=(int)$min1;
          	$sec1=date("s", strtotime($res1['linedate_time']));		$sec1=(int)$sec1;
          	
          	$hrs2=date("H", strtotime($res2['linedate_time']));		$hrs2=(int)$hrs2;
          	$min2=date("i", strtotime($res2['linedate_time']));		$min2=(int)$min2;
          	$sec2=date("s", strtotime($res2['linedate_time']));		$sec2=(int)$sec2;
          	
          	if($sec1 >=30)	$min1++;
          	if($sec2 >=30)	$min2++;
          	
          	$totalmins1=($hrs1*60) + $min1;
          	$totalmins2=($hrs2*60) + $min2;
          	$totalhrs=($totalmins2 - $totalmins1)/60;	
     		$totalhrs=number_format($totalhrs,2);
     		mrr_punch_clock_update_hours_only($clock_id,$totalhrs);	
		}
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Updated Punch Clock Hours for UserID=".$user_id."";
		//......................................................................................................................................................
		
		$rval= "
				<ClockID><![CDATA[$clock_id]]></ClockID> 
				<NewTime><![CDATA[$dater]]></NewTime> 
				<NewHours><![CDATA[$totalhrs]]></NewHours>  
			";
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	function mrr_truck_note_deadlined()
	{
		$rval="<Deadliners>";
		
		$sql = "	select *
     			from notes
     			where deadline<='".date("Y-m-d")." 00:00:00' 
     				and deleted=0
     				and deadline>'0000-00-00 00:00:00'
     			order by deadline asc,id asc     			
     		";	
     	$data = simple_query($sql);
     	while($row = mysqli_fetch_array($data))
     	{
     		$id=$row['id'];
     		$notice=$row['desc_long'];
     		$deadline=date("m/d/Y",strtotime($row['deadline']));
			$rval.= "
			<Deadliner>
				<NoteID><![CDATA[$id]]></NoteID> 
				<Notice><![CDATA[$notice]]></Notice> 
				<Deadline><![CDATA[$deadline]]></Deadline>
			</Deadliner> 
			";
		}	
		$rval.="</Deadliners>";
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	function mrr_reload_graph_comparison()
	{
		global $defaultsarray;
		global $arr_FCColors;
		include_once("includes/fusioncharts/FC_Colors.php");
		include_once("includes/fusioncharts/FusionCharts.php");		
						
		$id=$_POST['graph_id'];
		$reload=$_POST['reload'];
		
		$moder=$_POST['moder'];
		$displayer=$_POST['displayer'];
		
		$chart_width=$_POST['chart_width'];
		$chart_height=$_POST['chart_height'];
		
		$miles=$_POST['miles'];
		$sales_tot=$_POST['sales_tot'];
		$invoiced=$_POST['invoiced'];
		
		$timesheet_total=0;
		if(isset($_POST['timesheet_total']))		$timesheet_total=$_POST['timesheet_total'];
				
		$days_run = get_days_available(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		$days_actual = get_days_run(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		
		$days_variance = $days_actual - $days_run['days_available_so_far'];
		$daily_cost=get_daily_cost();
		//$truck_cost = mrr_get_truck_cost(0);
		//$trailer_cost = mrr_get_trailer_cost(0);
		$mrr_res=mrr_find_expense_costs();
		
		$trailer_cost=$mrr_res['trailer'];
		
		$truck_cost=$mrr_res['truck'];
		$truck_cost_rental=$mrr_res['truck_rental'];
		$truck_cost_lease=$mrr_res['truck_lease'];
				
		$admin_cost=$mrr_res['admin'];
		$insur_cost=$mrr_res['insur'];	
				
		$rate_trailer_cost=0;
		$rate_truck_cost_rental=0;
		$rate_truck_cost_lease=0;
		$rate_admin_cost=0;
		$rate_insur_cost=0;
		if($daily_cost!=0)
		{
			$rate_trailer_cost=$trailer_cost / $daily_cost;
			$rate_truck_cost=$truck_cost / $daily_cost;
			$rate_admin_cost=$admin_cost / $daily_cost;
			$rate_insur_cost=$insur_cost / $daily_cost;
		}
		
		$part_budget[0]=0;		
		$variance_val[0]=0;
		$variance_tot=$days_variance * $daily_cost;
		for($xx=0; $xx <= 16; $xx++)
		{
			$variance_val[ $xx ]=0;	
			$part_budget[ $xx ]=0;	
		}		
		
		$mrr_coa_cntr=$_POST['mrr_coa_cntr'];
		$mrr_coa_names[0]="";
		$mrr_coa_numbs[0]="";
		$mrr_coa_group[0]="";
		for($xx=0; $xx < $mrr_coa_cntr; $xx++)
		{
			$mrr_coa_names[ $xx ]=$_POST['mrr_coa_names'][ $xx ];
			$mrr_coa_numbs[ $xx ]=$_POST['mrr_coa_numbs'][ $xx ];
			$mrr_coa_group[ $xx ]=$_POST['mrr_coa_group'][ $xx ];
		}
		
		$show_acct1[0]=",58800,58900,";										$show_group_name[0]="Fuel";					$show_sect1[0]=1;	
		$show_acct1[1]=",62300,";											$show_group_name[1]="Insurance";				$show_sect1[1]=2;	
		$show_acct1[2]=",67000-,65000-,75500-,78800,67100,66500,66510,66520,68850-";	$show_group_name[2]="Labor(Drivers)";			$show_sect1[2]=3;	
		$show_acct1[3]=",74500-,74900,68900";									$show_group_name[3]="Truck Maintenance";		$show_sect1[3]=4;	
		$show_acct1[4]=",,";												$show_group_name[4]="";
		$show_acct1[5]=",77600,";											$show_group_name[5]="Tires";					$show_sect1[5]=6;	
		$show_acct1[6]=",78000-,";											$show_group_name[6]="Truck Lease";				$show_sect1[6]=7;	
		$show_acct1[7]=",77500,77800,77450,";									$show_group_name[7]="Trailer Maintenance";		$show_sect1[7]=8;	
		$show_acct1[8]=",77950-,";											$show_group_name[8]="Truck Rental";			$show_sect1[8]=9;	
		$show_acct1[9]=",78100-,78050-,";										$show_group_name[9]="Mileage Expense";			$show_sect1[9]=10;	
		$show_acct1[10]=",85000-OH,97000-OH,97000";								$show_group_name[10]="Admin Expense";			$show_sect1[10]=11;	
		$show_acct1[11]=",,";												$show_group_name[11]="";
		$show_acct1[12]=",68270-,68800,77250,79000-,57500,74400,";					$show_group_name[12]="Miscellaneous Expense";	$show_sect1[12]=13;
		$show_acct1[13]=",,";												$show_group_name[13]="";
		$show_acct1[14]=",77470,";											$show_group_name[14]="Trailer Rental Expense";	$show_sect1[14]=15;	
		$show_acct1[15]=",74000-,77485-";										$show_group_name[15]="Accidents";				$show_sect1[15]=16;	
		$show_acct1[16]=",77475-,";											$show_group_name[16]="Trailer Mileage Expense";	$show_sect1[16]=17;	
		$show_acct1[17]=",,";												$show_group_name[17]="";
		
		$captured="";
				
		for($xx=0; $xx < 17; $xx++)
		{
			if($xx==4)		$xx++;
			if($xx==11)		$xx++;
			if($xx==13)		$xx++;
			
			$show_acct1[$xx]="";
			
			$mres=mrr_get_budget_all_comparison_section_items( $show_sect1[$xx], 1);
			
			$show_acct1[$xx]=$mres['all'];
			//$captured.="<br>".$xx.". ".$mres['sql']."<br> -- ".$mres['all']."";
		}												
							
		$parts=$_POST['parts'];		
		$part_titles[0]="";
		$part_values1[0]=0;
		$part_values2[0]=0;
		$part_values3[0]=0;
		$part_values4[0]=0;
		$acct_lister[0]="";
		$acct_lister2[0]="";
		$part_diff[0]=0;
		$percent_total =0;
		$budget_total =0;
		$actual_total =0;
		$diff_total =0;
		
		$part_perc[0]=0;
		$part_perc_span[0]="";
		$percent_total2 =0;
				
		for($i=0; $i < $parts; $i++)
		{
			$part_titles[ $i ]=$_POST['part_titles'][ $i ];
			$part_values1[ $i ]=$_POST['part_values1'][ $i ];
			$part_values2[ $i ]=$_POST['part_values2'][ $i ];
			$part_values3[ $i ]=$_POST['part_values3'][ $i ];
			$part_values4[ $i ]=$_POST['part_values4'][ $i ];
			$acct_lister[ $i ]="";
			$acct_lister2[ $i ]="";
			
			$part_diff[ $i ]=($_POST['part_values3'][ $i ] - $_POST['part_values4'][ $i ])*1;
			
			$part_perc[ $i ]=0;
			if($_POST['part_values3'][ $i ] > 0)		$part_perc[ $i ]=($part_diff[ $i ] / $_POST['part_values3'][ $i ]) * 100;
			
			$part_perc_span[ $i ]="".number_format($part_perc[ $i ],2)."%";
			if($part_perc[ $i ] > 0)
			{
				$part_perc_span[ $i ]="<span style='color:#".$arr_FCColors[3].";'><b>+".number_format($part_perc[ $i ],2)."%</b></span>";
			}
			elseif($part_perc[ $i ] < 0)
			{
				$part_perc_span[ $i ]="<span style='color:#".$arr_FCColors[5].";'><b>".number_format($part_perc[ $i ],2)."%</b></span>";	
			}
			//$percent_total2+=$part_perc[ $i ];
						
			$percent_total += $_POST['part_values1'][ $i ];
			$budget_total += $_POST['part_values3'][ $i ];
			$actual_total += $_POST['part_values4'][ $i ];
			$diff_total += ($_POST['part_values3'][ $i ] - $_POST['part_values4'][ $i ])*1;
			
			//.................................................................................			
			$acct_lister[ $i ]="COA(s): ";	//
			$acct_lister2[ $i ]="
				<div id='coa_lister_".$i."' style='max-height:300px; height:500px; overflow:auto; display:none;'>
				<table border='0'>
				<tr>
					<td valign='top' colspan='2' align='center'><b>".$show_group_name[ $i ]." COA(s)</b></td>
				</tr>
				<tr>
					<td valign='top' width='100' align='left'><b>Group</b></td>					
					<td valign='top' width='400' align='left'><b>Account Name</b></td>
				</tr>
			";	//<td valign='top' width='150' align='left'><b>Chart Number</b></td>
			for($xx=0; $xx < $mrr_coa_cntr; $xx++)
			{
				//$mrr_coa_names[ $xx ]=$_POST['mrr_coa_names'][ $xx ];
				//$mrr_coa_numbs[ $xx ]=$_POST['mrr_coa_numbs'][ $xx ];
				//$mrr_coa_group[ $xx ]=$_POST['mrr_coa_group'][ $xx ];
				
				if(substr_count($show_acct1[ $i ],$mrr_coa_group[ $xx ]) > 0 && $show_acct1[$i]!=",,")
				{
					if(substr_count($acct_lister2[ $i ],$mrr_coa_group[ $xx ]) == 0)
					{
						$acct_lister2[ $i ].="
						<tr>
							<td valign='top'>".$mrr_coa_group[ $xx ]."</td>						
							<td valign='top'>".$mrr_coa_names[ $xx ]."</td>
						</tr>
						";	//<td valign='top'>".$mrr_coa_numbs[ $xx ]."</td>
					}
										
					
					if(substr_count($acct_lister[ $i ],$mrr_coa_group[ $xx ]) == 0)
					{
						$acct_lister[ $i ].=", ".$mrr_coa_group[ $xx ]."";
					}
				}
			}
			$acct_lister2[ $i ].="
				</table>
				</div>
			";
			$acct_lister[ $i ]= str_replace("COA(s): ,","COA(s): ", $acct_lister[ $i ]); 
			//.................................................................................
		}
		
		$diff_total+=$timesheet_total;
		
		$percent_total2=0;
		if($budget_total>0)   $percent_total2=($diff_total/$budget_total)*100;
						
		//actual chart files						//name of current chart					//next chart in rotation				//previous chart in rotation		
		$graph_display[0]="FCF_Pie3D.swf";				$chart_namer[0]="3D Pie Chart";			$chart_next[0]="3D Columns";			$chart_prev[0]="2D Area";
		$graph_display[1]="FCF_Column3D.swf";			$chart_namer[1]="3D Columns";				$chart_next[1]="Line Graph";			$chart_prev[1]="3D Pie Chart";
		$graph_display[2]="FCF_Line.swf";				$chart_namer[2]="Line Graph";				$chart_next[2]="2D Columns";			$chart_prev[2]="3D Columns";
		$graph_display[3]="FCF_Column2D.swf";			$chart_namer[3]="2D Columns";				$chart_next[3]="2D Area";			$chart_prev[3]="Line Graph";
		$graph_display[4]="FCF_Area2D.swf";			$chart_namer[4]="2D Area";				$chart_next[4]="3D Pie Chart";		$chart_prev[4]="2D Columns";	
				
		//clear old data
		//if($reload > 0)			mrr_clear_graph_data($id);
		
		//switch display modes
		if($moder==2)
		{
			$displayer++;
			if($displayer>4)		$displayer=0;
		}
		elseif($moder==1)
		{
			$displayer--;
			if($displayer < 0)		$displayer=4;
		}		
					
		$mrr_html="";
		$mrr_html2="";
		$mrr_debug_section="";
				
		//change chart data	
		if($id==1)
		{
			$spacer="&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;";
			$spacer1="&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;";
			$captor="Chart Of Accounts";
			
			$strXML = "<graph caption='Comparison Report with Sales' xAxisName='".$captor."' yAxisName='Dollars' decimalPrecision='0' formatNumberScale='0'>";
			
			//$col1=$arr_FCColors[0];
			//$col2=$arr_FCColors[5];	
			//$strXML .= "<set name='S' value='".$sales_tot."' color='".$col1."'/>";		//$arr_FCColors[$i]
			//$strXML .= "<set name='I' value='".$invoiced."' color='".$col2."'/>";		//$arr_FCColors[$i]
			
			// get the past 6 months sales
			for($i=0;	$i < $parts; $i++)
			{
				$myname=$part_titles[ $i ];
				$myvalue1=$part_values1[ $i ];
				$myvalue2=$part_values2[ $i ];
				$myvalue3=$part_values3[ $i ];
				$myvalue4=$part_values4[ $i ];
				$my_diff=$part_diff[ $i ];
				
				if(trim($myvalue1)=="")		$myvalue1=0;
				if(trim($myvalue2)=="")		$myvalue2=0;
				if(trim($myvalue3)=="")		$myvalue3=0;
				if(trim($myvalue4)=="")		$myvalue4=0;
				
				if(trim($myname)!="")
				{		
					$col1=$arr_FCColors[0];
					$col2=$arr_FCColors[4];
					
					$col3=$arr_FCColors[3];
					if($my_diff < 0)
					{
						$col3=$arr_FCColors[5];	
					}
										
					//$strXML .= "<set name='".$myname."' value='".$myvalue2."' color='".$arr_FCColors[$i]."'/>";
					$strXML .= "<set name='B' value='".$myvalue3."' color='".$col1."' title='".$myname."'/>";		//$arr_FCColors[$i]
					$strXML .= "<set name='A' value='".$myvalue4."' color='".$col2."' title='".$myname."'/>";		//$arr_FCColors[$i]
					$strXML .= "<set name='OU' value='".abs($my_diff)."' color='".$col3."' title='".$myname."'/>";		//$arr_FCColors[$i]
				}
			}

			$strXML .=  "</graph>";
						
			$mrr_type=$graph_display[ ($displayer + 0) ];
			
			$mrr_html="<div align='left' style='margin-left:0px;' class='border_solid'>";
			$mrr_html.=renderChartHTML("includes/fusioncharts/".$mrr_type."", "", $strXML, "myFirst", $chart_width, $chart_height);	//class='graph_links'	
			$mrr_html.="</div>";
			
			$wider=104;	//(int) ($chart_width/12);
			$spacer="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".
					" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;";
			
			$spacer2="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".
					" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						
			$spacer3="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".
					" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			
					
			//added Apr 2013...............................
			$rpercenter=0;
			$rvariance=$variance_tot * $rate_truck_cost;
			$lvariance=0;
			if($part_values3[10] + $part_values3[6] > 0)		$rpercenter=$part_values3[8] / ($part_values3[8] + $part_values3[6]);
			if($rpercenter > 0)
			{
				$lvariance=($variance_tot * $rate_truck_cost) - ($variance_tot * $rate_truck_cost * $rpercenter);
				$rvariance=($variance_tot * $rate_truck_cost * $rpercenter);	
			}			
			//.............................................
			
			$variance_val[1]= $variance_tot * $rate_insur_cost;		// $part_values3[1] * $rate_insur_cost;							//Insurance Variance
     		$variance_val[6]= $lvariance;							// $part_values3[6] * $rate_truck_cost;							//Truck Lease Variance
     		$variance_val[8]= $rvariance;		//$variance_tot * $rate_truck_cost;		// $part_values3[8] * $rate_truck_cost;			//Truck Rental Variance
     		$variance_val[10]= $variance_tot * $rate_admin_cost;		// $part_values3[10] * $rate_admin_cost;						//Admin Exp Variance
     		$variance_val[14]= $variance_tot * $rate_trailer_cost;		// $part_values3[14] * $rate_trailer_cost;						//Trailer Variance
     		
			$var_truck_percent_rental=0;
			$var_truck_percent_lease=0;
			
			//$mrr_debug_section.="<br><b>Truck Lease</b>=".$part_values1[6]."  ....   ".$part_values3[6]."  ....  ".$part_values4[6]."  ....<br>";
			//$mrr_debug_section.="<br><b>Truck Lease</b>=".$part_values1[8]."  ....   ".$part_values3[8]."  ....  ".$part_values4[8]."  ....<br>";
			
			$mrr_truck_cost = mrr_get_option_variable_settings('Tractor Lease');
			$mrr_truck_cost_rental= mrr_get_option_variable_settings('Truck Rental');
			
			$mrr_truck_cost+=$mrr_truck_cost_rental;
			
			if($mrr_truck_cost>0)
			{
				$var_truck_percent_rental=$mrr_truck_cost_rental / $mrr_truck_cost;
				//$variance_val[6]=$variance_val[8] * $var_truck_percent_rental;
				
				//$variance_val[8]=$variance_val[8] - $variance_val[6];
				
				//$part_values3[6]=$part_values3[8] * $var_truck_percent_rental;
				//$part_values3[8]=$part_values3[8] - $part_values3[6];		
			}
			
			//Added April 2013....show "ideal" budgetary numbers based on basic settings, not from load break down...................................................
			$loaded_miles=$_POST['mrr_loaded_miles'];
			$rep_load_id=$_POST['mrr_rep_load_id'];
			$fuel_rate=0;
			$budget_total2=0;
			
			$sqlx="
				select id 
				from trailers 
				where deleted=0
					and rental_flag>0
			";
			$datax=simple_query($sqlx);
			$bud_trailers_rental=mysqli_num_rows($datax);
			
			$sqlx="
				select id 
				from trailers 
				where deleted=0
					and company_owned>0
			";
			$datax=simple_query($sqlx);
			$bud_trailers_company=mysqli_num_rows($datax);
			
			$sqlx="
				select id 
				from trucks 
				where deleted=0
					and replacement='0'
					and company_owned > 0
			";
			$datax=simple_query($sqlx);
			$bud_trucks_company=mysqli_num_rows($datax);
			
			$sqlx="
				select id 
				from trucks 
				where deleted=0
					and replacement='0'
					and rental>0
			";
			$datax=simple_query($sqlx);
			$bud_trucks_rental=mysqli_num_rows($datax);
			
			
			$trailer_mileage_exp=0;
			$sqlx="
				select trailer_exp_per_mile 
				from trucks_log 
				where load_handler_id='".sql_friendly($rep_load_id)."'
					and deleted=0
			";
			$datax=simple_query($sqlx);
			if($rowx=mysqli_fetch_array($datax))
			{
				$trailer_mileage_exp=$rowx['trailer_exp_per_mile'];
			}
			
			
			$sqlx="
				select * 
				from load_handler 
				where id='".sql_friendly($rep_load_id)."'
			";
			$datax=simple_query($sqlx);
			if($rowx=mysqli_fetch_array($datax))
			{
				$bres=get_active_truck_count_ranged($_POST['date_from'],$_POST['date_to']);
				$b2res=get_active_trailer_count_ranged($_POST['date_from'],$_POST['date_to']);
				
				$tot_trucks=$rowx['budget_active_trucks'] - $bud_trucks_company;	//removed company owned trucks
				$rate_trucks_rented=0;
				if($tot_trucks > 0)		$rate_trucks_rented=($bud_trucks_rental/$tot_trucks)/100;
				
				$tot_trailers=$rowx['budget_active_trailers'] - $bud_trailers_company;
				//$rate_trailers_rented=0;
				//if($tot_trailers > 0)		$rate_trailers_rented=$bud_trailers_rental/$tot_trailers;
				
				$tot_insur_bud=($rowx['budget_cargo_insurance'] + $rowx['budget_general_liability'] + $rowx['budget_liability_damage']) * $tot_trucks;	// / $rowx['budget_days_in_month'] * $rowx['budget_active_trailers']
				//$tot_trucks_bud=$rowx['budget_tractor_lease']  * $tot_trucks;	// / $rowx['budget_days_in_month']
				//$tot_trailer_bud=($rowx['budget_trailer_exp'] + $rowx['budget_trailer_lease']) * $tot_trailers;	// / $rowx['budget_days_in_month']
				
				$tot_trucks_bud_leased=$bres['lease_value'];
				$tot_trucks_bud_rental=$bres['rent_value'];
				$tot_trailer_bud=$b2res['rent_value'] + $b2res['lease_value'];
				
							
				$fuel_rate=$rowx['rate_fuel_surcharge'];
				if($rowx['actual_rate_fuel_surcharge'] > $fuel_rate) $fuel_rate=$rowx['actual_rate_fuel_surcharge'];
				/*
				$rowx['budget_days_in_month']
				$rowx['budget_labor_per_hour']				
				$rowx['budget_labor_per_mile_team']
				$rowx['budget_driver_week_hours']
				$rowx['budget_day_variance']				
				*/
				$part_budget[0]=( $loaded_miles / $rowx['budget_average_mpg']) * $fuel_rate;									//Fuel
				$part_budget[1]=$tot_insur_bud;	//Insurance
				$part_budget[2]=$rowx['budget_labor_per_mile'] * $loaded_miles;												//Labor
				$part_budget[3]=$rowx['budget_tractor_maint_per_mile'] * $loaded_miles;										//Truck Maint
				$part_budget[5]=$rowx['budget_tires_per_mile'] * $loaded_miles;												//Tires
				$part_budget[6]=$tot_trucks_bud_leased;																	//Truck Lease
				$part_budget[7]=$rowx['budget_trailer_maint_per_mile'] * $loaded_miles;										//Trailer Maint
				$part_budget[8]=$tot_trucks_bud_rental;																	//Truck Rental
				$part_budget[9]=$rowx['budget_mileage_exp_per_mile'] * $loaded_miles;											//Mileage Exp
				$part_budget[10]=$rowx['budget_payroll_admin'] + $rowx['budget_rent'];										//Admin Exp
				$part_budget[12]=$rowx['budget_misc_exp_per_mile'] * $loaded_miles;											//Misc. Exp
				$part_budget[14]=$tot_trailer_bud;																		//Trailer Rental
				$part_budget[15]=$rowx['budget_truck_accidents_per_mile'] * $loaded_miles;										//Accidents
				$part_budget[16]=$trailer_mileage_exp * $loaded_miles;														//Trailer Milage Exp
			}				
			//........................................................................................................................................................
			
			$budget_total2 += $part_budget[0];
			$budget_total2 += $part_budget[1];
			$budget_total2 += $part_budget[2];
			$budget_total2 += $part_budget[3];
			$budget_total2 += $part_budget[5];
			$budget_total2 += $part_budget[6];
			$budget_total2 += $part_budget[7];
			$budget_total2 += $part_budget[8];
			$budget_total2 += $part_budget[9];
			$budget_total2 += $part_budget[10];
			$budget_total2 += $part_budget[12];
			$budget_total2 += $part_budget[14];
			$budget_total2 += $part_budget[15];
			$budget_total2 += $part_budget[16];
			/*
			$truck_res=mrr_get_rental_truck_counts();
			//$truck_res['all_trucks']=0;
			//$truck_res['rentals']=0;
			//$truck_res['comp_owned']=0;
			//$truck_res['lease']=0;
			if($truck_res['all_trucks'] > 0)
			{
				$var_truck_percent_rental =($truck_res['all_trucks'] - $truck_res['comp_owned'] - $truck_res['rentals'])/ $truck_res['all_trucks'];	//gets the rental part...
				$variance_val[6]=$variance_val[8] * $var_truck_percent_rental;
				
				$variance_val[8]=$variance_val[8] - $variance_val[6];
				
				$part_values3[6]=$part_values3[8] * $var_truck_percent_rental;
				$part_values3[8]=$part_values3[8] - $part_values3[6];				
			}		
			*/
			$mrr_html2="
			".$mrr_debug_section."
			<table border=1 cellpadding=0 cellspacing=0 width='1750' id='budget_comparison_table'>
			<tr>
				<td valign='top' align='left' width='370'>&nbsp; <b>Account Group</b></td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(0);'><b>Fuel</b></span>".$acct_lister2[0]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(1);'><b>Insurance</b></span>".$acct_lister2[1]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(2);'><b>Labor<br>(Drivers)</b></span>".$acct_lister2[2]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(3);'><b>Truck<br>Maint</b></span>".$acct_lister2[3]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(5);'><b>Tires</b></span>".$acct_lister2[5]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(6);'><b>Truck<br>Lease</b></span>".$acct_lister2[6]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(7);'><b>Trailer<br>Maint</b></span>".$acct_lister2[7]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(8);'><b>Truck<br>Rental</b></span>".$acct_lister2[8]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(9);'><b>Truck<br>Mileage<br>Expenses</b></span>".$acct_lister2[9]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(10);'><b>Admin<br>Expenses</b></span>".$acct_lister2[10]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(12);'><b>Misc.<br>Expenses</b></span>".$acct_lister2[12]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(14);'><b>Trailer<br>Rental</b></span>".$acct_lister2[14]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(15);'><b>Accidents</b></span>".$acct_lister2[15]."</td>
				<td valign='top' align='center' width='".$wider."'><span class='mrr_link_like_on' onClick='mrr_coa_displayer(16);'><b>Trailer<br>Mileage<br>Expenses</b></span>".$acct_lister2[16]."</td>
				<td valign='top' align='right'><b>Total</b>&nbsp;</td>
			</tr>
			
			<tr>
				<td valign='top' align='left'>&nbsp; <b>Sales ".$spacer2." (Total $<span id='calc_sales_tot'>".number_format($sales_tot,2)."</span>)</b></td>
				<td valign='top' align='right'>".number_format($part_values1[0],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[1],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[2],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[3],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[5],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[6],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[7],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[8],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[9],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[10],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[12],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[14],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[15],2)."% &nbsp;</td>
				<td valign='top' align='right'>".number_format($part_values1[16],2)."% &nbsp;</td>
				<td valign='top' align='right'><b><span id='calc_sales_percent'>".number_format($percent_total,2)."</span>% &nbsp;</b></td>
			</tr>
			<tr>
				<td valign='top' align='left'><span style='color:orange;'>&nbsp; <b>Budgetary (Ideal)</b></span> ".$spacer3."<span title='Representative Load ID ".$rep_load_id."'>(".number_format($loaded_miles,0)." Miles)</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[0],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[1],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[2],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[3],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[5],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[6],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[7],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[8],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[9],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[10],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[12],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[14],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[15],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'>$".number_format($part_budget[16],2)." &nbsp;</span></td>
				<td valign='top' align='right'><span style='color:orange;'><b>$".number_format($budget_total2,2)." &nbsp;</b></span></td>
			</tr>			
			<tr>
				<td valign='top' align='left'><span style='color:#". $arr_FCColors[0] .";'>&nbsp; <b>Budgetary (Calculated from Loads)</b></span></td>
				<td valign='top' align='right'>$".number_format($part_values3[0],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[1],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[2],2)." &nbsp;<br><span title='(Carlex) Labor Timesheets'>TS</span>: $".number_format($timesheet_total,2)." &nbsp;<br>$".number_format(($part_values3[2] + $timesheet_total),2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[3],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[5],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[6],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[7],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[8],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[9],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[10],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[12],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[14],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[15],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values3[16],2)." &nbsp;</td>
				<td valign='top' align='right'><b>$<span id='calc_budget_tot'>".number_format($budget_total,2)."</span> &nbsp;</b></td>
			</tr>
			<tr>
				<td valign='top' align='left'><span style='color:#". $arr_FCColors[4] .";'>&nbsp; <b>Actual</b></span></td>
				<td valign='top' align='right'>$".number_format($part_values4[0],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[1],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[2],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[3],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[5],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[6],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[7],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[8],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[9],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[10],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[12],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[14],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[15],2)." &nbsp;</td>
				<td valign='top' align='right'>$".number_format($part_values4[16],2)." &nbsp;</td>
				<td valign='top' align='right'><b>$<span id='calc_actual_tot'>".number_format($actual_total,2)."</span> &nbsp;</b></td>
			</tr>
			<tr>
				<td valign='top' align='left'>&nbsp; <b>Variance</b></td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($variance_val[1],2)." &nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($variance_val[6],2)." &nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($variance_val[8],2)." &nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($variance_val[10],2)." &nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($variance_val[14],2)." &nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'><b>$<span id='calc_actual_tot'>".number_format($variance_tot,2)."</span> &nbsp;</b></td>
			</tr>			
			<tr>
				<td valign='top' align='left'>&nbsp; <b><span style='color:#". $arr_FCColors[5] .";'>Over</span>/<span style='color:#". $arr_FCColors[3] .";'>Under</span></b></td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[0])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[1])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',($part_diff[2] + $timesheet_total))." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[3])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[5])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[6])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[7])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[8])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[9])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[10])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[12])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[14])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[15])." &nbsp;</td>
				<td valign='top' align='right'>$".money_format('$',$part_diff[16])." &nbsp;</td>
				<td valign='top' align='right'><b><span id='calc_diff_tot'>$".money_format('$',$diff_total)."</span> &nbsp;</b></td>
			</tr>			
			<tr>
				<td valign='top' align='left'>&nbsp; <b>Over/Under Percentage</b></td>
				<td valign='top' align='right'>".$part_perc_span[0]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[1]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[2]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[3]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[5]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[6]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[7]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[8]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[9]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[10]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[12]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[14]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[15]." &nbsp;</td>
				<td valign='top' align='right'>".$part_perc_span[16]." &nbsp;</td>
				<td valign='top' align='right'><b><span id='calc_tot_percent'>".number_format($percent_total2,2)."</span>% &nbsp;</b></td>
			</tr>						
			</table>	
			".$captured."		
			";
			//
			
			//added July 2012....store values displayed for this period
			$period_start=date("Y-m-d",strtotime($_POST['date_from'])) . " 00:00:00";
			$period_end=date("Y-m-d",strtotime($_POST['date_to'])) . " 23:59:59";
			$sqlz="
				delete from comparison_archive 
				where linedate_start='".sql_friendly($period_start)."' 
					and linedate_end='".sql_friendly($period_end)."'
			";
			simple_query($sqlz);	
						
			for($z=0;$z <= 16; $z++)
			{
				if($z==4 || $z==11 || $z==13)	$z++;	//skip these sections
				
				$mrrpart1=trim($part_values1[ $z ]);
				$mrrpart3=trim($part_values3[ $z ]);
				$mrrpart4=trim($part_values4[ $z ]);
				$mrrvar=trim($variance_val[ $z ]);
				$mrrdiff=trim($part_diff[ $z ]);
				$mrrdiffper=trim($part_perc_span[ $z ]);
				
				$mrrpart1=str_replace("$","",$mrrpart1);
				$mrrpart3=str_replace("$","",$mrrpart3);
				$mrrpart4=str_replace("$","",$mrrpart4);
				$mrrvar=str_replace("$","",$mrrvar);
				$mrrdiff=str_replace("$","",$mrrdiff);
				$mrrdiffper=str_replace("$","",$mrrdiffper);
				
				$mrrpart1=str_replace(",","",$mrrpart1);
				$mrrpart3=str_replace(",","",$mrrpart3);
				$mrrpart4=str_replace(",","",$mrrpart4);
				$mrrvar=str_replace(",","",$mrrvar);
				$mrrdiff=str_replace(",","",$mrrdiff);
				$mrrdiffper=str_replace(",","",$mrrdiffper);
				
				$mrrpart1=str_replace("%","",strip_tags($mrrpart1));
				$mrrpart3=str_replace("%","",strip_tags($mrrpart3));
				$mrrpart4=str_replace("%","",strip_tags($mrrpart4));
				$mrrvar=str_replace("%","",strip_tags($mrrvar));
				$mrrdiff=str_replace("%","",strip_tags($mrrdiff));
				$mrrdiffper=str_replace("%","",strip_tags($mrrdiffper));
				
				$mrrpart1=(float)($mrrpart1);
				$mrrpart3=(float)($mrrpart3);
				$mrrpart4=(float)($mrrpart4);
				$mrrvar=(float)($mrrvar);
				$mrrdiff=(float)($mrrdiff);
				$mrrdiffper=(float)($mrrdiffper);
				
				$sqlz="
					insert into comparison_archive
						(id,
						linedate_added,
						linedate_start,
						linedate_end,
						section_id,
						sales_percent,
						budget_value,
						actual_value, 
						variance_value,
						difference,
						difference_percent)
					values
						(NULL,
						NOW(),
						'".sql_friendly($period_start)."',
						'".sql_friendly($period_end)."',
						'".$z."',
						'".sql_friendly($mrrpart1)."',
						'".sql_friendly($mrrpart3)."',
						'".sql_friendly($mrrpart4)."',
						'".sql_friendly($mrrvar)."',
						'".sql_friendly($mrrdiff)."',
						'".sql_friendly($mrrdiffper)."')
				";
				simple_query($sqlz);			
			}
			
			//general settings
			$mrrpart1=trim($sales_tot);
			$mrrpart3=trim($miles);
			$mrrpart4=trim($invoiced);
			$mrrvar=trim($days_run['days_available_so_far']);
			$mrrdiff=trim($days_actual);
			$mrrdiffper="0";
			
			$mrrpart1=str_replace("$","",$mrrpart1);
			$mrrpart3=str_replace("$","",$mrrpart3);
			$mrrpart4=str_replace("$","",$mrrpart4);
			$mrrvar=str_replace("$","",$mrrvar);
			$mrrdiff=str_replace("$","",$mrrdiff);
			$mrrdiffper=str_replace("$","",$mrrdiffper);
			
			$mrrpart1=str_replace(",","",$mrrpart1);
			$mrrpart3=str_replace(",","",$mrrpart3);
			$mrrpart4=str_replace(",","",$mrrpart4);
			$mrrvar=str_replace(",","",$mrrvar);
			$mrrdiff=str_replace(",","",$mrrdiff);
			$mrrdiffper=str_replace(",","",$mrrdiffper);
			
			$mrrpart1=str_replace("%","",strip_tags($mrrpart1));
			$mrrpart3=str_replace("%","",strip_tags($mrrpart3));
			$mrrpart4=str_replace("%","",strip_tags($mrrpart4));
			$mrrvar=str_replace("%","",strip_tags($mrrvar));
			$mrrdiff=str_replace("%","",strip_tags($mrrdiff));
			$mrrdiffper=str_replace("%","",strip_tags($mrrdiffper));
			
			$mrrpart1=(float)($mrrpart1);
			$mrrpart3=(float)($mrrpart3);
			$mrrpart4=(float)($mrrpart4);
			$mrrvar=(float)($mrrvar);
			$mrrdiff=(float)($mrrdiff);
			$mrrdiffper=(float)($mrrdiffper);		
			$sqlz="
					insert into comparison_archive
						(id,
						linedate_added,
						linedate_start,
						linedate_end,
						section_id,
						sales_percent,
						budget_value,
						actual_value, 
						variance_value,
						difference,
						difference_percent)
					values
						(NULL,
						NOW(),
						'".sql_friendly($period_start)."',
						'".sql_friendly($period_end)."',
						'97',
						'".sql_friendly($mrrpart1)."',
						'".sql_friendly($mrrpart3)."',
						'".sql_friendly($mrrpart4)."',
						'".sql_friendly($mrrvar)."',
						'".sql_friendly($mrrdiff)."',
						'".sql_friendly($mrrdiffper)."')
				";
			simple_query($sqlz);
					
			$mrrpart1=trim($daily_cost);
			$mrrpart3=trim($days_variance);
			$mrrpart4=trim($truck_cost);
			$mrrvar=trim($trailer_cost);
			$mrrdiff=trim($admin_cost);
			$mrrdiffper=trim($insur_cost);
			
			$mrrpart1=str_replace("$","",$mrrpart1);
			$mrrpart3=str_replace("$","",$mrrpart3);
			$mrrpart4=str_replace("$","",$mrrpart4);
			$mrrvar=str_replace("$","",$mrrvar);
			$mrrdiff=str_replace("$","",$mrrdiff);
			$mrrdiffper=str_replace("$","",$mrrdiffper);
			
			$mrrpart1=str_replace(",","",$mrrpart1);
			$mrrpart3=str_replace(",","",$mrrpart3);
			$mrrpart4=str_replace(",","",$mrrpart4);
			$mrrvar=str_replace(",","",$mrrvar);
			$mrrdiff=str_replace(",","",$mrrdiff);
			$mrrdiffper=str_replace(",","",$mrrdiffper);
			
			$mrrpart1=str_replace("%","",$mrrpart1);
			$mrrpart3=str_replace("%","",$mrrpart3);
			$mrrpart4=str_replace("%","",$mrrpart4);
			$mrrvar=str_replace("%","",$mrrvar);
			$mrrdiff=str_replace("%","",$mrrdiff);
			$mrrdiffper=str_replace("%","",$mrrdiffper);
			
			$mrrpart1=(float)($mrrpart1);
			$mrrpart3=(float)($mrrpart3);
			$mrrpart4=(float)($mrrpart4);
			$mrrvar=(float)($mrrvar);
			$mrrdiff=(float)($mrrdiff);
			$mrrdiffper=(float)($mrrdiffper);
			$sqlz="
					insert into comparison_archive
						(id,
						linedate_added,
						linedate_start,
						linedate_end,
						section_id,
						sales_percent,
						budget_value,
						actual_value, 
						variance_value,
						difference,
						difference_percent)
					values
						(NULL,
						NOW(),
						'".sql_friendly($period_start)."',
						'".sql_friendly($period_end)."',
						'98',
						'".sql_friendly($mrrpart1)."',
						'".sql_friendly($mrrpart3)."',
						'".sql_friendly($mrrpart4)."',
						'".sql_friendly($mrrvar)."',
						'".sql_friendly($mrrdiff)."',
						'".sql_friendly($mrrdiffper)."')
				";
			simple_query($sqlz);
			
			
				
			//totals
			$mrrpart1=trim($percent_total);
			$mrrpart3=trim($budget_total);
			$mrrpart4=trim($actual_total);
			$mrrvar=trim($variance_tot);
			$mrrdiff=trim($diff_total);
			$mrrdiffper=trim($percent_total);
			
			$mrrpart1=str_replace("$","",$mrrpart1);
			$mrrpart3=str_replace("$","",$mrrpart3);
			$mrrpart4=str_replace("$","",$mrrpart4);
			$mrrvar=str_replace("$","",$mrrvar);
			$mrrdiff=str_replace("$","",$mrrdiff);
			$mrrdiffper=str_replace("$","",$mrrdiffper);
			
			$mrrpart1=str_replace(",","",$mrrpart1);
			$mrrpart3=str_replace(",","",$mrrpart3);
			$mrrpart4=str_replace(",","",$mrrpart4);
			$mrrvar=str_replace(",","",$mrrvar);
			$mrrdiff=str_replace(",","",$mrrdiff);
			$mrrdiffper=str_replace(",","",$mrrdiffper);
			
			$mrrpart1=str_replace("%","",strip_tags($mrrpart1));
			$mrrpart3=str_replace("%","",strip_tags($mrrpart3));
			$mrrpart4=str_replace("%","",strip_tags($mrrpart4));
			$mrrvar=str_replace("%","",strip_tags($mrrvar));
			$mrrdiff=str_replace("%","",strip_tags($mrrdiff));
			$mrrdiffper=str_replace("%","",strip_tags($mrrdiffper));
						
			$mrrpart1=(float)($mrrpart1);
			$mrrpart3=(float)($mrrpart3);
			$mrrpart4=(float)($mrrpart4);
			$mrrvar=(float)($mrrvar);
			$mrrdiff=(float)($mrrdiff);
			$mrrdiffper=(float)($mrrdiffper);
			
			$sqlz="
					insert into comparison_archive
						(id,
						linedate_added,
						linedate_start,
						linedate_end,
						section_id,
						sales_percent,
						budget_value,
						actual_value, 
						variance_value,
						difference,
						difference_percent)
					values
						(NULL,
						NOW(),
						'".sql_friendly($period_start)."',
						'".sql_friendly($period_end)."',
						'99',						
						'".sql_friendly($mrrpart1)."',
						'".sql_friendly($mrrpart3)."',
						'".sql_friendly($mrrpart4)."',
						'".sql_friendly($mrrvar)."',
						'".sql_friendly($mrrdiff)."',
						'".sql_friendly($mrrdiffper)."')
				";
			simple_query($sqlz);		
		}
		if($id==2)
		{
			$strXML = "<graph caption='Comparison Report Sales Percent' xAxisName='COA' yAxisName='Percent' decimalPrecision='2' formatNumberScale='0'>";
			
			$sales_tot_tot=0;
			
			
			// get the parts
			for($i=0;	$i < $parts; $i++)
			{
				$myname=$part_titles[ $i ];
				$myvalue1=$part_values1[ $i ];
				$myvalue2=$part_values2[ $i ];
				$myvalue3=$part_values3[ $i ];
				$myvalue4=$part_values4[ $i ];
				
				if(trim($myvalue1)=="")		$myvalue1=0;
				if(trim($myvalue2)=="")		$myvalue2=0;
				if(trim($myvalue3)=="")		$myvalue3=0;
				if(trim($myvalue4)=="")		$myvalue4=0;
				
				if(trim($myname)!="" && $myvalue1!=0 && $myvalue1!="0.00")
				{		
					$strXML .= "<set name='".$myname."' value='".$myvalue1."' color='".$arr_FCColors[($i+1)]."'/>";
					$sales_tot_tot+=$myvalue4;
				}
			}
			
			$sales_tot_diff=$sales_tot - $sales_tot_tot;
			if($sales_tot_diff>0)
			{
				$sales_tot_diff_per=0;
				if($sales_tot>0)	$sales_tot_diff_per=$sales_tot_diff/$sales_tot * 100;
				
				
				$strXML .= "<set name='Other Accounts' value='".$sales_tot_diff_per."' color='".$arr_FCColors[0]."'/>";	
			}

			$strXML .=  "</graph>";
			
			$mrr_type=$graph_display[ ($displayer + 0) ];
			
			$mrr_html="<div class='border_solid'>";
			$mrr_html.=renderChartHTML("includes/fusioncharts/".$mrr_type."", "", $strXML, "myFirst", $chart_width, $chart_height);	//class='graph_links'
			$mrr_html.="</div>
				<div id='mrr_clicker".$chart_width."' onClick='mrr_enlarge_pie_chart();' style='width:250px; text-align:center;' class='mrr_link_like_on'><b>Click to Enlarge</b></div>
				<br>
			";
			
			
			$mrr_html2="";	
		}
		$rval="
			<Graph>
				<GraphID><![CDATA[$id]]></GraphID>
				<GraphHTML><![CDATA[$mrr_html]]></GraphHTML>
				<GraphHTML2><![CDATA[$mrr_html2]]></GraphHTML2>
				<GraphBudget><![CDATA[".$budget_total."]]></GraphBudget>			
			</Graph>
		";
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	function mrr_full_graph_generator()
	{	//for comparison archive report...chart only...
		$month_listing="";
		if(isset($_POST['month_list']))	$month_listing=trim($_POST['month_list']);
				
		global $defaultsarray;
		global $arr_FCColors;
		include_once("includes/fusioncharts/FC_Colors.php");
		include_once("includes/fusioncharts/FusionCharts.php");	
		
		$mrr_html="<center>";
		
		if($_POST['month_list']=="")
		{
			$mrr_html.="<b>No Months Selected</b>";
		}
		else
		{
			$setting1[0][0]=0;
			$setting2[0][0]=0;
			$tot[0][0]=0;
			$slots[0][0][0]=0;
			$lab_mon[0]="";
			$m_cntr=0;
			
          	//normal account rows
          	$labels[0]="Sales Total";	// ($".number_format($sales_tot,2).")
          	$labels[1]="Budgetary";
          	$labels[2]="Actual";
          	$labels[3]="Variance";
          	$labels[4]="Over/Under";
          	$labels[5]="O/U Percent";
          		
			//$mrr_html.="<b>Selected ".$month_listing."</b>";	
			$groups = explode(" ... ", $month_listing);
			$grps=count($groups);
			for($x=0;$x < $grps; $x++)
			{
     			$groups[ $x ] = str_replace("...","",$groups[ $x ]);
     			$groups[ $x ] = trim($groups[ $x ]);
     			if(trim($groups[ $x ])=="")	$x++;
     			
     			$date_from="".trim($groups[ $x ])."-01";
     			$date_to="".trim($groups[ $x ])."-31";
     			
     			
     			if(substr_count($date_to,"-4-") > 0 || substr_count($date_to,"-6-") > 0 || substr_count($date_to,"-9-") > 0 || substr_count($date_to,"-11-") > 0)
     			{
     				$date_to="".trim($groups[ $x ])."-30";	
     			}
     			if(substr_count($date_to,"-2-") > 0)
     			{
     				$date_to="".trim($groups[ $x ])."-28";	
     			}
     			
     			//$mrr_html.="<br><b>Finding ".$date_from." to ".$date_to."</b></br>";	
     			$lab_mon[ $m_cntr ]="".trim($groups[ $x ])."";
     			//get settings
          		$setting1[ ($m_cntr) ][0]=0;
          		$setting1[ ($m_cntr) ][1]=0;
          		$setting1[ ($m_cntr) ][2]=0;
          		$setting1[ ($m_cntr) ][3]=0;
          		$setting1[ ($m_cntr) ][4]=0;
          		$setting1[ ($m_cntr) ][5]=0;
          		
          		$setting2[ ($m_cntr) ][0]=0;
          		$setting2[ ($m_cntr) ][1]=0;
          		$setting2[ ($m_cntr) ][2]=0;
          		$setting2[ ($m_cntr) ][3]=0;
          		$setting2[ ($m_cntr) ][4]=0;
          		$setting2[ ($m_cntr) ][5]=0;
          		
          		$tot[ ($m_cntr) ][0]=0;
          		$tot[ ($m_cntr) ][1]=0;
          		$tot[ ($m_cntr) ][2]=0;
          		$tot[ ($m_cntr) ][3]=0;
          		$tot[ ($m_cntr) ][4]=0;
          		$tot[ ($m_cntr) ][5]=0;	
          				
          		$sql="
          			select *
          			from comparison_archive
          			where linedate_start='".date("Y-m-d",strtotime($date_from))." 00:00:00' 
          				and linedate_end='".date("Y-m-d",strtotime($date_to))." 23:59:59'
          				and section_id>=97
          			order by section_id asc
          		";
          		$data = simple_query($sql);
          		while($row=mysqli_fetch_array($data))
          		{			
          			if($row['section_id']==97)
          			{	//general settings
          				$setting1[ ($m_cntr) ][0]=(float)$row['sales_percent'];
          				$setting1[ ($m_cntr) ][1]=(float)$row['budget_value'];
          				$setting1[ ($m_cntr) ][2]=(float)$row['actual_value'];
          				$setting1[ ($m_cntr) ][3]=(float)$row['variance_value'];
          				$setting1[ ($m_cntr) ][4]=(float)$row['difference'];
          				//$setting1[5]=(float)$row['difference_percent'];
          			}
          			elseif($row['section_id']==98)
          			{	//general settings
          				$setting2[ ($m_cntr) ][0]=(float)$row['sales_percent'];
          				$setting2[ ($m_cntr) ][1]=(float)$row['budget_value'];
          				$setting2[ ($m_cntr) ][2]=(float)$row['actual_value'];
          				$setting2[ ($m_cntr) ][3]=(float)$row['variance_value'];
          				$setting2[ ($m_cntr) ][4]=(float)$row['difference'];
          				$setting2[ ($m_cntr) ][5]=(float)$row['difference_percent'];
          			}
          			elseif($row['section_id']==99)
          			{	//totals
          				$tot[ ($m_cntr) ][0]=(float)$row['sales_percent'];
          				$tot[ ($m_cntr) ][1]=(float)$row['budget_value'];
          				$tot[ ($m_cntr) ][2]=(float)$row['actual_value'];
          				$tot[ ($m_cntr) ][3]=(float)$row['variance_value'];
          				$tot[ ($m_cntr) ][4]=(float)$row['difference'];
          				$tot[ ($m_cntr) ][5]=(float)$row['difference_percent'];	
          			}						
          		}
          		
          		
          		$slots[ ($m_cntr) ][0][0]=0;
          		for($i=0;$i < 6; $i++)
          		{	//rows
          			for($j=0;$j <= 16; $j++)
          			{	//columns
          				if($j==4 || $j==11 || $j==13)	$j++;	//skip these sections
          				$slots[ ($m_cntr) ][ $i ][ $j ]=0;
          			}	
          		}
          		$sql="
          			select *
          			from comparison_archive
          			where linedate_start='".date("Y-m-d",strtotime($date_from))." 00:00:00' 
          				and linedate_end='".date("Y-m-d",strtotime($date_to))." 23:59:59'
          				and section_id<97
          			order by section_id asc
          		";
          		$data = simple_query($sql);
          		while($row=mysqli_fetch_array($data))
          		{
          			$sales=$row['sales_percent'];
          			$budget=$row['budget_value'];
          			$actual=$row['actual_value'];
          			$variance=$row['variance_value'];
          			$diff=$row['difference'];
          			$diff_perc=$row['difference_percent'];	
          			
          			$ind=$row['section_id'];
          			
          			$slots[ ($m_cntr) ][ 0 ][ $ind ]=(float)$sales;
          			$slots[ ($m_cntr) ][ 1 ][ $ind ]=(float)$budget;
          			$slots[ ($m_cntr) ][ 2 ][ $ind ]=(float)$actual;
          			$slots[ ($m_cntr) ][ 3 ][ $ind ]=(float)$variance;
          			$slots[ ($m_cntr) ][ 4 ][ $ind ]=(float)$diff;
          			$slots[ ($m_cntr) ][ 5 ][ $ind ]=(float)$diff_perc;			
          		}
          		
          		$m_cntr++;
			}	//end X for loop			
						
			//=================================================================================================================================================================			
			//now create the one chart for all selected months...			
			$graph_mode=5;
			$chart_width=600;
			$chart_height=300;
			
			//actual chart files						//name of current chart					//next chart in rotation				//previous chart in rotation		
			$graph_display[0]="FCF_Pie3D.swf";				$chart_namer[0]="3D Pie Chart";			$chart_next[0]="3D Columns";			$chart_prev[0]="2D Area";
			$graph_display[1]="FCF_Column3D.swf";			$chart_namer[1]="3D Columns";				$chart_next[1]="Line Graph";			$chart_prev[1]="3D Pie Chart";
			$graph_display[2]="FCF_Line.swf";				$chart_namer[2]="Line Graph";				$chart_next[2]="2D Columns";			$chart_prev[2]="3D Columns";
			$graph_display[3]="FCF_Column2D.swf";			$chart_namer[3]="2D Columns";				$chart_next[3]="2D Area";			$chart_prev[3]="Line Graph";
			$graph_display[4]="FCF_Area2D.swf";			$chart_namer[4]="2D Area";				$chart_next[4]="3D Pie Chart";		$chart_prev[4]="2D Columns";	
			$graph_display[5]="FCF_MSColumn3DLineDY.swf";	//
			$graph_display[6]="FCF_MSLine.swf";			//
			$graph_display[7]="FCF_StackedColumn3D.swf";		//
			$graph_display[8]="FCF_Candlestick.swf";		//
			$graph_display[9]="FCF_Doughnut2D.swf";			//
			$graph_display[10]="FCF_Funnel.swf";			//
			$graph_display[11]="FCF_Gantt.swf";			//
		
			$mrr_type=$graph_display[ $graph_mode ];
			
			//$arr_FCColors[ ($col[ 0 ]) ];
			$col[0]="";
			for($j=0;$j <= 16; $j++)
			{
				$col[ $j ]=$arr_FCColors[ $j ];	
			}			
			
			$months[0]="";				$maxdays[0]=0;
			$months[1]="January";		$maxdays[1]=31;
			$months[2]="February";		$maxdays[2]=28;
			$months[3]="March";			$maxdays[3]=31;
			$months[4]="April";			$maxdays[4]=30;
			$months[5]="May";			$maxdays[5]=31;
			$months[6]="June";			$maxdays[6]=30;
			$months[7]="July";			$maxdays[7]=31;
			$months[8]="August";		$maxdays[8]=31;
			$months[9]="September";		$maxdays[9]=30;
			$months[10]="October";		$maxdays[10]=31;
			$months[11]="November";		$maxdays[11]=30;
			$months[12]="December";		$maxdays[12]=31;
			
			$cat_header="<categories>";
			//$cat_header.="<category name='Before'/>";
			for($x=($m_cntr - 1); $x >=0 ; $x--)
			{
				$chead=$lab_mon[ $x ];
				$chead_suff=substr($chead,0,4);		//year
				$chead_pref=substr($chead,4);
				$chead_pref=trim($chead_pref);
				$chead_int=str_replace("-","",$chead_pref);
				$chead_int=(int)$chead_int;
				$chead_pref=$months[ $chead_int ];
				
				$cat_header.="<category name='".$chead_pref." ".$chead_suff."'/>";		//$labels		//$lab_mon
			}
			//$cat_header.="<category name='After'/>";
			$cat_header.="</categories>";
			
			$mrr_html="<table cellpadding='0' cellspacing='0' border='0' width='1800'>
					<tr>";
			//run six separate charts...
			for($i=0;$i < 6; $i++)
			{
				if($i%3==0 && $i>0)
				{
					$mrr_html.="
						</tr>
						<tr>
					";	
				}
				
				//$set_adder="<set value='0'/>";				
				$set_adder="";
				$str_data[0]="<dataset seriesName='Fuel' color='".$col[0]."' anchorBorderColor='".$col[0]."' anchorBgColor='".$col[0]."'>".$set_adder."";	
				$str_data[1]="<dataset seriesName='Insurance' color='".$col[1]."' anchorBorderColor='".$col[1]."' anchorBgColor='".$col[1]."'>".$set_adder."";	
				$str_data[2]="<dataset seriesName='Labor (Drivers)' color='".$col[2]."' anchorBorderColor='".$col[2]."' anchorBgColor='".$col[2]."'>".$set_adder."";	
				$str_data[3]="<dataset seriesName='Truck Maint' color='".$col[3]."' anchorBorderColor='".$col[3]."' anchorBgColor='".$col[3]."'>".$set_adder."";	
				$str_data[4]="";	
				$str_data[5]="<dataset seriesName='Tires' color='".$col[5]."' anchorBorderColor='".$col[5]."' anchorBgColor='".$col[5]."'>".$set_adder."";	
				$str_data[6]="<dataset seriesName='Truck Lease' color='".$col[6]."' anchorBorderColor='".$col[6]."' anchorBgColor='".$col[6]."'>".$set_adder."";	
				$str_data[7]="<dataset seriesName='Trailer Maint' color='".$col[7]."' anchorBorderColor='".$col[7]."' anchorBgColor='".$col[7]."'>".$set_adder."";	
				$str_data[8]="<dataset seriesName='Truck Rental' color='".$col[8]."' anchorBorderColor='".$col[8]."' anchorBgColor='".$col[8]."'>".$set_adder."";	
				$str_data[9]="<dataset seriesName='Truck Mileage Expenses' color='".$col[9]."' anchorBorderColor='".$col[9]."' anchorBgColor='".$col[9]."'>".$set_adder."";	
				$str_data[10]="<dataset seriesName='Admin Expenses' color='".$col[10]."' anchorBorderColor='".$col[10]."' anchorBgColor='".$col[10]."'>".$set_adder."";	
				$str_data[11]="";	
				$str_data[12]="<dataset seriesName='Misc. Expenses' color='".$col[12]."' anchorBorderColor='".$col[12]."' anchorBgColor='".$col[12]."'>".$set_adder."";	
				$str_data[13]="";	
				$str_data[14]="<dataset seriesName='Trailer Rental' color='".$col[14]."' anchorBorderColor='".$col[14]."' anchorBgColor='".$col[14]."'>".$set_adder."";	
				$str_data[15]="<dataset seriesName='Accidents' color='".$col[15]."' anchorBorderColor='".$col[15]."' anchorBgColor='".$col[15]."'>".$set_adder."";	
				$str_data[16]="<dataset seriesName='Trailer Mileage Expenses' color='".$col[16]."' anchorBorderColor='".$col[16]."' anchorBgColor='".$col[16]."'>".$set_adder."";
				
				$strXML = "<graph caption='Comparison Archive Report' subcaption='".$labels[ $i ]."' 
          				lineThickness='1' showValues='0' formatNumberScale='0' anchorRadius='2' divLineAlpha='20' divLineColor='CC3300' 
          				showAlternateHGridColor='1' alternateHGridColor='CC3300' shadowAlpha='40' numvdivlines='5' chartRightMargin='35' 
          				bgColor='FDF5F3' alternateHGridAlpha='5' limitsDecimalPrecision='0' divLineDecimalPrecision='0' decimalPrecision='0'>
          				".$cat_header."
          		";
          		
          		for($x=($m_cntr - 1); $x >=0 ; $x--)
          		{	//months
          			
          			for($j=0;$j <= 16; $j++)
          			{	//columns	
          				if($j==4|| $j==11 || $j==13)	$j++;	//skip these sections	
          				$myvalue=(float) $slots[ $x ][ $i ][ $j ];
          				$str_data[ $j ].="<set value='".$myvalue."'/>"; 	
          			} 		
          			
          		}	
				
				for($j=0;$j <= 16; $j++)
				{
					if($j==4 || $j==11 || $j==13)	$j++;	//skip these sections	
					$str_data[ $j ].="".$set_adder."</dataset>";
					$strXML .=  $str_data[ $j ];		
				}
				
				$strXML .=  "</graph>";						
				
				$mrr_html.="<td valign='top' width'33%'>";
				$mrr_html.=	"<div align='left' style='margin-left:0px;' class='border_solid'>";
				$mrr_html.=		renderChartHTML("includes/fusioncharts/".$mrr_type."", "", $strXML, "myChart".$i."", $chart_width, $chart_height);	//class='graph_links'	
				$mrr_html.=	"</div>";
				$mrr_html.="</td>";		
			}
			
			$mrr_html.="</tr>
				</table>";			
				
		}	//end of else check for blank dates	
				
			
		/*		
		$strXML2="
          <graph caption='Daily Visits' subcaption='(from 8/6/2006 to 8/12/2006)' 
          		lineThickness='1' showValues='0' formatNumberScale='0' anchorRadius='2' divLineAlpha='20' divLineColor='CC3300' 
          		showAlternateHGridColor='1' alternateHGridColor='CC3300' shadowAlpha='40' numvdivlines='5' chartRightMargin='35' 
          		bgColor='FDF5F3' alternateHGridAlpha='5' limitsDecimalPrecision='0' divLineDecimalPrecision='0' decimalPrecision='0'>
          <categories>
                  <category name='8/6/2006'/>
                  <category name='8/7/2006'/>
                  <category name='8/8/2006'/>
                  <category name='8/9/2006'/>
                  <category name='8/10/2006'/>
                  <category name='8/11/2006'/>
                  <category name='8/12/2006'/>
          </categories>
          <dataset seriesName='Offline Marketing' color='1D8BD1' anchorBorderColor='1D8BD1' anchorBgColor='1D8BD1'>
                  <set value='1327'/>
                  <set value='1826'/>
                  <set value='1699'/>
                  <set value='1511'/>
                  <set value='1904'/>
                  <set value='1957'/>
                  <set value='1296'/>
          </dataset>
          <dataset seriesName='Search' color='F1683C' anchorBorderColor='F1683C' anchorBgColor='F1683C'>
                  <set value='2042'/>
                  <set value='3210'/>
                  <set value='2994'/>
                  <set value='3115'/>
                  <set value='2844'/>
                  <set value='3576'/>
                  <set value='1862'/>
          </dataset>
          </graph>";
		
		$mrr_html.="<br><div align='left' style='margin-left:0px;' class='border_solid'>";
		$mrr_html.=renderChartHTML("includes/fusioncharts/FCF_MSColumn3DLineDY.swf", "", $strXML2, "myFirst2", 1000, 1000);	//class='graph_links'	
		$mrr_html.="</div>";
		
		
*/		
		$mrr_html.="</center>";
		display_xml_response("<rslt>1</rslt><GraphHTML><![CDATA[$mrr_html]]></GraphHTML>");		
	}
	
	//...........BUDGET.......................................................
	function mrr_ajax_get_budget()
	{
		$rval="";
		$sql="
			select budget.*
			from budget
			where budget.id='".sql_friendly( $_POST['id'] )."'
               ";	
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
          	$linker="<div class='mrr_link_like_on' onClick='mrr_get_this_budget(".$row['id'].")'>".$row['budget_name']."</div>";
          	$actor="Inactive";
          	if($row['active']  > 0)	$actor="Active";
          	$trash="<div onClick='confirm_budget_delete(".$row['id'].");'><img src='images/delete_sm.gif' border='0'></div>";
          	
          	$rval.="
			<Budget>
				<BudgetID><![CDATA[".$row['id']."]]></BudgetID>
				<BudgetName><![CDATA[".$row['budget_name']."]]></BudgetName>
				<BudgetLink><![CDATA[".$linker."]]></BudgetLink>
				<BudgetAdded><![CDATA[".date("m/d/Y", strtotime($row['linedate_added']))."]]></BudgetAdded>
				<BudgetStart><![CDATA[".date("m/d/Y", strtotime($row['linedate_start']))."]]></BudgetStart>
				<BudgetEnded><![CDATA[".date("m/d/Y", strtotime($row['linedate_ended']))."]]></BudgetEnded>				
				<BudgetActive><![CDATA[".$row['active']."]]></BudgetActive>
				<BudgetActor><![CDATA[".$actor."]]></BudgetActor>
				<BudgetTrash><![CDATA[".$trash."]]></BudgetTrash>
			</Budget>
			";          	        	
          }
          else
          {
          	$rval.="
			<Budget>
				<BudgetID><![CDATA[0]]></BudgetID>
				<BudgetName><![CDATA[]]></BudgetName>
				<BudgetLink><![CDATA[]]></BudgetLink>
				<BudgetAdded><![CDATA[".date("m/d/Y", time())."]]></BudgetAdded>
				<BudgetStart><![CDATA[]]></BudgetStart>
				<BudgetEnded><![CDATA[]]></BudgetEnded>				
				<BudgetActive><![CDATA[1]]></BudgetActive>
				<BudgetActor><![CDATA[Active]]></BudgetActor>
				<BudgetTrash><![CDATA[]]></BudgetTrash>
			</Budget>
			";  	
          }
          display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_ajax_save_budget()
	{
		global $datasource;

		//$_POST['linedate_start']
          //$_POST['linedate_ended']
          //$_POST['budget_name']
          //$_POST['active']
		//$_POST['id']
		$rval="";
		if($_POST['id'] == 0)
		{
			$sql="
			insert into budget
				(id,
				linedate_added,
				deleted)
			values
				(NULL,
				NOW(),
				0)
               ";	
          	simple_query($sql);
			$_POST['id']=mysqli_insert_id($datasource);
		}
		//update new and old budget
		if($_POST['id'] > 0)
		{
			$sql="
			update budget set
          		budget.linedate_start='".date("Y-m-d", strtotime($_POST['linedate_start']))." 00:00:00',
          		budget.linedate_ended='".date("Y-m-d", strtotime($_POST['linedate_ended']))." 23:59:59',
          		budget.budget_name='".sql_friendly( $_POST['budget_name'] )."',
          		budget.active='".( (isset($_POST['active']) && $_POST['active'] > 0 ) ? "1" : "0" )."'
			where budget.id='".sql_friendly( $_POST['id'] )."'
               ";	
          	simple_query($sql);	
          	
          	$rval="<BudgetID><![CDATA[".$_POST['id']."]]></BudgetID>";
		}	
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_ajax_delete_budget()
	{
		$rval="";	
		if($_POST['id'] > 0)
		{
			$sql="
			update budget set
          		budget.deleted='1'
			where budget.id='".sql_friendly( $_POST['id'] )."'
               ";	
          	simple_query($sql);	
		}
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	function mrr_ajax_get_budget_list()
	{
		$rval="";	
		$sql="
			select budget.*
			from budget
			where budget.deleted='0'
			order by budget.active asc,
				budget.linedate_ended desc,
				budget.linedate_start desc,
				budget.budget_name asc
               ";	
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
          	$linker="<div class='mrr_link_like_on' onClick='mrr_get_this_budget(".$row['id'].")'>".$row['budget_name']."</div>";
          	$actor="Inactive";
          	if($row['active']  > 0)	$actor="Active";
          	$trash="<div onClick='confirm_budget_delete(".$row['id'].");'><img src='images/delete_sm.gif' border='0'></div>";
          	
          	$rval.="
			<Budget>
				<BudgetID><![CDATA[".$row['id']."]]></BudgetID>
				<BudgetName><![CDATA[".$row['budget_name']."]]></BudgetName>
				<BudgetLink><![CDATA[".$linker."]]></BudgetLink>
				<BudgetAdded><![CDATA[".date("m/d/Y", strtotime($row['linedate_added']))."]]></BudgetAdded>
				<BudgetStart><![CDATA[".date("m/d/Y", strtotime($row['linedate_start']))."]]></BudgetStart>
				<BudgetEnded><![CDATA[".date("m/d/Y", strtotime($row['linedate_ended']))."]]></BudgetEnded>				
				<BudgetActive><![CDATA[".$row['active']."]]></BudgetActive>
				<BudgetActor><![CDATA[".$actor."]]></BudgetActor>
				<BudgetTrash><![CDATA[".$trash."]]></BudgetTrash>
			</Budget>
			";          	        	
          }	
          
          $rval2=mrr_load_main_budget();
          
		display_xml_response("<rslt>1</rslt><BudgetList>$rval</BudgetList><BudgetTable><![CDATA[".$rval2."]]></BudgetTable>");	
	}
	//budget items
	
	function mrr_ajax_get_budget_item_list()
	{
		$rval="";	
		$sql="
			select budget_items.*
     		from budget_items
     		where budget_items.budget_id='".sql_friendly( $_POST['id'] )."'
     		order by budget_items.budget_cat asc,budget_items.id asc
               ";	
          $data=simple_query($sql);
          $cntr=0;
          while($row=mysqli_fetch_array($data))
          {
          	$cat=mrr_decode_budget_cat($row['budget_cat']);
               
          	$rval.="
			<BudgetItem>
				<BudgetItemID><![CDATA[".$row['id']."]]></BudgetItemID>
				<BudgetItemCatID><![CDATA[".$row['budget_cat']."]]></BudgetItemCatID>
				<BudgetItemCatName><![CDATA[".$cat."]]></BudgetItemCatName>
				<BudgetItemMile><![CDATA[".$row['per_mile']."]]></BudgetItemMile>
				<BudgetItemTruck><![CDATA[".$row['per_truck']."]]></BudgetItemTruck>
				<BudgetItemTrailer><![CDATA[".$row['per_trailer']."]]></BudgetItemTrailer>
				<BudgetItemDriver><![CDATA[".$row['per_driver']."]]></BudgetItemDriver>
				<BudgetItemDispatch><![CDATA[".$row['per_dispatch']."]]></BudgetItemDispatch>
				<BudgetItemLoad><![CDATA[".$row['per_load']."]]></BudgetItemLoad>
				<BudgetItemFlat><![CDATA[".$row['flat_amount']."]]></BudgetItemFlat>
				<BudgetItemAmnt><![CDATA[".$row['budget_amount']."]]></BudgetItemAmnt>
			</BudgetItem>
			";  
			$cntr++;        	        	
          }	                   

          $rval2=mrr_load_main_budget_items( $_POST['id'] );
          
		display_xml_response("<rslt>1</rslt><BudgetList>$rval</BudgetList><BudgetTable><![CDATA[".$rval2."]]></BudgetTable><BudgetCntr><![CDATA[".$cntr."]]></BudgetCntr>");
	}
	function mrr_ajax_save_budget_item()
	{
		$rval="";
		$id=$_POST['id'];
		
		$mile=$_POST['mile'];
		$truck=$_POST['truck'];
		$trailer=$_POST['trailer'];
		$driver=$_POST['driver'];
		$dispatch=$_POST['dispatch'];
		$load=$_POST['load'];
		$flat=$_POST['flat'];
		$amnt=$_POST['amnt'];
		
		if($id > 0)
		{
			$sql="
			update budget_items set
          		budget_items.per_mile='".sql_friendly( $mile )."',
          		budget_items.per_truck='".sql_friendly( $truck )."',
          		budget_items.per_trailer='".sql_friendly( $trailer )."',
          		budget_items.per_driver='".sql_friendly( $driver )."',
          		budget_items.per_dispatch='".sql_friendly( $dispatch )."',
          		budget_items.per_load='".sql_friendly( $load )."',
          		budget_items.flat_amount='".sql_friendly( $flat )."',
          		budget_items.budget_amount='".sql_friendly( $amnt )."'
			where budget_items.id='".sql_friendly( $id )."'
               ";	
          	simple_query($sql);		
		}
		
		display_xml_response("<rslt>1</rslt>$rval");		
	}
	
	function mrr_ajax_calc_budget_table()
	{
		global $defaultsarray;
		
		if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
		if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
				
		$discrep_link=" <a href='report_accounting_discrepancy.php?&date_from=".$_POST['date_from']."&date_to=".$_POST['date_to']."' target='_blank'><b>View</b></a>";	
		
		$calc_sales_tot=$_POST['calc_sales_tot'];
		$calc_sales_percent=$_POST['calc_sales_percent'];
		$calc_budget_tot=$_POST['calc_budget_tot'];
		$calc_actual_tot=$_POST['calc_actual_tot'];
		$calc_diff_tot=$_POST['calc_diff_tot'];
		$calc_tot_percent=$_POST['calc_tot_percent'];
		$mrr_misc_income=$_POST['calc_misc_income'];
		$mrr_disc_income=$_POST['calc_disc_income'];
		$mrr_net_profit=$_POST['calc_net_profit'];
		$mrr_variance=$_POST['calc_variance'];
		$mrr_use_net=$_POST['calc_use_tprofit'];
		
		$mrr_net_profit_income=$_POST['mrr_net_profit_income'];
		$mrr_net_profit_sales=$_POST['mrr_net_profit_sales'];
		
		$mrr_not_invoiced_cnt=$_POST['mrr_not_invoiced_cnt'];
		$mrr_not_invoiced_amount=$_POST['mrr_not_invoiced_amount'];
		$mrr_not_invoiced_amount=str_replace("$","",$mrr_not_invoiced_amount);
		$mrr_not_invoiced_amount=str_replace(",","",$mrr_not_invoiced_amount);
		
		//capture sales Report.........................................
		$search_date_range = '';
		//if((isset($_POST['dispatch_id']) && $_POST['dispatch_id'] != '') || $_POST['load_handler_id'] != '') {
		//} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and load_handler.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			";
		//}
		$driver_search = "";
		/*
		
		if($_POST['driver_id'] > 0) {
			$driver_search = "
				and load_handler.id in 
						(
							select load_handler_id 
							
							from trucks_log 
							where (driver_id = '".sql_friendly($_POST['driver_id'])."' or driver2_id = '".sql_friendly($_POST['driver_id'])."')
								and trucks_log.deleted = 0
						)
			";
		}
		*/
		
		$sql = "
			select load_handler.*,
				customers.name_company,
				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead			
			from load_handler
				left join customers on customers.id = load_handler.customer_id
			where load_handler.deleted = 0
				and customers.deleted = 0	
				and linedate_dropoff_eta < now()		
				$search_date_range			
			order by load_handler.id
		";
		/*			
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and load_handler.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".(isset($_POST['show_only_invoiced']) ? " and load_handler.invoice_number != '' " : '') ."
				$driver_search
		*/
		$data = simple_query($sql);
	
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		$total_cost = 0;
		$total_extra = 0;
		$total_sales = 0;
		$last_load_id = 0;
		$last_truck_id = 0;
		$not_invoiced = 0;
		$invoiced = 0;
		$invoiced_amount = 0;
		$not_invoiced_amount = 0;
		$fuel_charge = 0;
		
		$load_lister="";
		
		while($row = mysqli_fetch_array($data)) {
			$counter++;
			
			if($last_load_id != $row['id']) {
				$last_load_id = $row['id'];
				
				$load_miles = $row['miles'];
				$load_miles_deadhead = $row['miles_deadhead'];
				$total_miles += $load_miles;
				$total_deadhead += $load_miles_deadhead;
				$fuel_charge += ($row['miles'] + $row['miles_deadhead']) * $row['actual_rate_fuel_surcharge'] / $defaultsarray['average_mpg'];
				//echo $fuel_charge."<br>";

			}
			
			if(!is_numeric($row['sicap_invoice_number']) || $row['sicap_invoice_number'] == 0) 
			{
				$not_invoiced++;
				$not_invoiced_amount += $row['actual_bill_customer'];
				$load_lister.=" LoadX(".$not_invoiced.") ".$row['id']." ";				
			} 
			elseif(trim($row['invoice_number'])=="" ) 
			{
				$not_invoiced++;
				$not_invoiced_amount += $row['actual_bill_customer'];
				$load_lister.=" LoadY(".$not_invoiced.") ".$row['id']." ";				
			} 
			else 
			{
				$invoiced++;
				$invoiced_amount += $row['actual_bill_customer'];
			}
			
			$total_profit += $row['actual_bill_customer'] - $row['actual_total_cost'];
			$total_cost += $row['actual_total_cost'];
			$total_sales += $row['actual_bill_customer'];

		}

		$days_run = get_days_available(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		$days_actual = get_days_run(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		
		$days_variance = $days_actual - $days_run['days_available_so_far'];
		
		$daily_cost = get_daily_cost();
		$usage_difference = $daily_cost * $days_variance;
		
		$gross_profit = $total_profit + $usage_difference;
		
		$gallons_used = ($total_miles + $total_deadhead) / $defaultsarray['average_mpg'];
		$cost_per_gallon = $fuel_charge / $gallons_used;
		
		//.............................................................
		
		/*
		$calc_sales_tot=$_POST['calc_sales_tot'];
		$calc_sales_percent=$_POST['calc_sales_percent'];
		$calc_budget_tot=$_POST['calc_budget_tot'];
		$calc_actual_tot=$_POST['calc_actual_tot'];
		$calc_diff_tot=$_POST['calc_diff_tot'];
		$calc_tot_percent=$_POST['calc_tot_percent'];
		$mrr_misc_income=$_POST['calc_misc_income'];
		$mrr_disc_income=$_POST['calc_disc_income'];
		$mrr_net_profit=$_POST['calc_net_profit'];
		*/
		
		$calc_diff_tot=trim($calc_diff_tot);
		$calc_diff_tot=str_replace("$","",$calc_diff_tot);
		$calc_diff_tot=str_replace(",","",$calc_diff_tot);
		$calc_diff_tot=strip_tags($calc_diff_tot);
		$calc_diff_tot=trim($calc_diff_tot);
		
		$calc_diff_tot=(1.0 * $calc_diff_tot);
					
		$tab_egp=$mrr_use_net;		//$total_profit;
     	$tab_oub=$calc_diff_tot;
     	$tab_misc=$mrr_misc_income;	//comes in signed
     	$tab_disc=$mrr_disc_income;	//comes in signed
     	$tab_enp=0;
     	$tab_tap=$mrr_net_profit;	//comes in signed
     	$tab_dif=0;
     	$signer="";
     	//do calculations
     	$tab_enp=$tab_egp + $tab_oub + $tab_misc + $tab_disc;
     		
     	if($tab_oub < 0)	
     	{
     		$signer="";
     		$tab_enp=$tab_egp + $tab_oub + $tab_misc + $tab_disc;
     	}
     	if($tab_oub > 0)
     	{
     		$signer="";
     		$tab_enp=$tab_egp + $tab_oub + $tab_misc + $tab_disc;
     	}
     	$tab_dif=$tab_enp-$tab_tap;	
     	
     	//$tab_dif+=$mrr_variance;
     	
     	$mrr_net_profit_income=$mrr_net_profit_income - $mrr_misc_income;
     	$mrr_net_profit_income=$mrr_net_profit_income - $mrr_disc_income;
     	$sales_diff=$mrr_net_profit_sales-$mrr_net_profit_income;
     	$true_diff=$sales_diff-$tab_dif;
     	 
     	//formatting     	
     	$tag1a="";			$tag2a="";
     	$tag1b="";			$tag2b="";
     	$tag1c="";			$tag2c="";
     	$tag1d="";			$tag2d="";
     	$tag1e="";			$tag2e="";
     	$tag1f="";			$tag2f="";
     	//higher is bad...lower is better
     	if($tab_oub < 0)
     	{
     		$tag1a="<span style='color:#CC0000;'><b>";			$tag2a="</b></span>";	
     	}
     	if($tab_oub > 0)
     	{
     		$tag1a="<span style='color:#00CC00;'><b>";			$tag2a="</b></span>";	
     	}
     	
     	//lower is bad...higher is better
     	if($tab_enp < 0)
     	{
     		$tag1b="<span style='color:#CC0000;'><b>";			$tag2b="</b></span>";	
     	}
     	if($tab_enp > 0)
     	{
     		$tag1b="<span style='color:#00CC00;'><b>";			$tag2b="</b></span>";	
     	}
     	
     	//lower is bad...higher is better
     	if($tab_dif < 0)
     	{
     		$tag1c="<span style='color:#CC0000;'><b>";			$tag2c="</b></span>";	
     	}
     	if($tab_dif > 0)
     	{
     		$tag1c="<span style='color:#00CC00;'><b>";			$tag2c="</b></span>";	
     	} 
     	
     	//lower is bad...higher is better
     	if($mrr_variance < 0)
     	{
     		$tag1d="<span style='color:#CC0000;'><b>";			$tag2d="</b></span>";	
     	}
     	if($mrr_variance > 0)
     	{
     		$tag1d="<span style='color:#00CC00;'><b>";			$tag2d="</b></span>";	
     	}  
     	
     	//sales difference
     	if($sales_diff < 0)
     	{
     		$tag1e="<span style='color:#CC0000;'><b>";			$tag2e="</b></span>";	
     	}
     	if($sales_diff > 0)
     	{
     		$tag1e="<span style='color:#00CC00;'><b>";			$tag2e="</b></span>";	
     	}
     	 
     	//true difference
     	$true_diff=number_format($true_diff,2);
     	$true_diff=str_replace(",","",$true_diff);
     	$true_diff=(float)$true_diff;			
     	if($true_diff < 0)
     	{
     		$tag1f="<span style='color:#CC0000;'><b>";			$tag2f="</b></span>";	
     	}
     	if($true_diff > 0)
     	{
     		$tag1f="<span style='color:#00CC00;'><b>";			$tag2f="</b></span>";	
     	}            	
          /*
          	<tr>
     			<td colspan='2'><center><div class='mrr_link_like_on' onClick='mrr_make_calculations_show_filler();'><b>Click to update.</b></div></center></td>
     		</tr>
          */
          
          
          //$tab_enp+=$mrr_variance;
         /*
         		<tr>	
     			<td align='left'><b>Variance</b></td>
     			<td align='right'>$<span id='mrr_calc_egp'>".$tag1d."".number_format($mrr_variance,2)."".$tag2d."</span></td>
     		</tr>
         */
          $true_link="<b>True Difference</b>";
          if($true_diff != 0)
          {
          	$true_link="<a href='admin_budget_sections.php?id=0&go=1&date_from=".$_POST['date_from']."&date_to=".$_POST['date_to']."' target='_blank'><b>True Difference</b></a>";	
          }
          
          //$switch_not_invoiced=$not_invoiced;			
         	//$switch_not_invoiced_amnt=$not_invoiced_amount;	
          $switch_not_invoiced=$mrr_not_invoiced_cnt;	
          $switch_not_invoiced_amnt=$mrr_not_invoiced_amount;	
          
     	$mrr_tab="
     	<table border='0' width='100%'>   		
     		<tr>	
     			<td align='left'><b>Total Profit</b>".show_help('report_comparison.php','Estimated Gross Profit')."</td>
     			<td align='right'>$<span id='mrr_calc_egp'>".number_format($tab_egp,2)."</span></td>
     		</tr>
     		
     		<tr>	
     			<td align='left'><b>Over / Under Budget</b>".show_help('report_comparison.php','Over / Under Budget')."</td>
     			<td align='right'>".$tag1a."".$signer." $<span id='mrr_calc_oub'>".number_format($tab_oub,2)."</span>".$tag2a."</td>
     		</tr>
     		<tr>	
     			<td align='left'><b>Misc. Income</b></td>
     			<td align='right'>$<span id='mrr_calc_misc'>".number_format($tab_misc,2)."</span></td>
     		</tr>
     		<tr>	
     			<td align='left' style='border-bottom:1px solid black;'><b>Discounts</b>".show_help('report_comparison.php','Discounts')."</td>
     			<td align='right' style='border-bottom:1px solid black;'>$<span id='mrr_calc_disc'>".number_format($tab_disc,2)."</span></td>
     		</tr>
     		<tr>	
     			<td align='left'><b>Total Est. Net Profit</b>".show_help('report_comparison.php','Total Estimated Net Profit')."</td>
     			<td align='right'>".$tag1b."$<span id='mrr_calc_enp'>".number_format($tab_enp,2)."</span>".$tag2b."</td>
     		</tr>
     		<tr>	
     			<td align='left' style='border-bottom:1px solid black;'><b>Total Actual Profit</b>".show_help('report_comparison.php','Total Actual Profit')."</td>
     			<td align='right' style='border-bottom:1px solid black;'>$<span id='mrr_calc_tap'>".number_format($tab_tap,2)."</span></td>
     		</tr>
     		<tr>	
     			<td align='left' style='border-bottom:1px solid black;'><b>Difference</b>".show_help('report_comparison.php','Difference')."</td>
     			<td align='right' style='border-bottom:1px solid black;'>".$tag1c."$<span id='mrr_calc_dif'>".number_format($tab_dif,2)."</span>".$tag2c."</td>
     		</tr>
     		<tr>	
     			<td align='left'><b>Total Budget Sales (Sales Report)</b>".show_help('report_comparison.php','Total Budget Sales')."</td>
     			<td align='right'>$".number_format($mrr_net_profit_sales,2)."</td>
     		</tr>
     		<tr>	
     			<td align='left' style='border-bottom:1px solid black;'><b>Total Actual Sales (Accounting)</b>".show_help('report_comparison.php','Total Actual Sales')."</td>
     			<td align='right' style='border-bottom:1px solid black;'>$".number_format($mrr_net_profit_income,2)."</td>
     		</tr>
     		<tr>	
     			<td align='left'><b>Sales Difference</b>".show_help('report_comparison.php','Sales Difference')."</td>
     			<td align='right'>".$tag1e."$<span id='mrr_calc_dif'>".number_format($sales_diff,2)."</span>".$tag2e."</td>
     		</tr>
     		<tr>	
     			<td align='left' style='border-bottom:1px so so solid black;'><b><span title='".$load_lister."'>Not Invoiced (".$switch_not_invoiced.")</span></b>".show_help('report_comparison.php','Not Invoiced')."</td>
     			<td align='right' style='border-bottom:1px solid black;'>$".number_format($switch_not_invoiced_amnt,2)."</td>
     		</tr>
     		<tr>	
     			<td align='left' style='border-bottom:1px solid black;'><b>Not Invoiced - Sales Difference.</b>".show_help('report_comparison.php','Not Invoice - Sales Difference')."".$discrep_link."</td>
     			<td align='right' style='border-bottom:1px solid black;'>$".number_format(($switch_not_invoiced_amnt - $sales_diff),2)."</td>
     		</tr>
     		<tr>	
     			<td align='left'>".$true_link."".show_help('report_comparison.php','True Difference')."</td>
     			<td align='right'>".$tag1f."$<span id='mrr_calc_dif'>".number_format($true_diff,2)."</span>".$tag2f."</td>
     		</tr>
     	</table>
     	";																	
     	
     	
     	display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	//<GraphBudget><![CDATA[".$budget_total."]]></GraphBudget>
	}
	
	function mrr_get_ar_summary_info_find()
	{
		if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
		if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
		
		$customer_id=$_POST['cust_id'];
		$customer_name=$_POST['cust_name'];
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];	
		$pay_notes="";	
		$overrider1=0;
		$overrider2=0;
		$sicap_id=0;
		$stored_vals=0;
		
		$dbags=0;
		
		$cname="";
		$ar15=0;
		$ar30=0;
		$ar45=0;
		$ar46=0;
		$ar_tot=0;
		$ar_days=0;
		$mysql="";
		$tab15="";
		$tab30="";
		$tab45="";
		$tab46="";
		
		if($customer_id>0)
		{
               $pay_note="";
               $pay_user="";
               $pay_date="";
               
               $sqlu = "
                      select payment_notes_history.*,
                          (select username from users where users.id=payment_notes_history.user_id) as user_name                                
                      from payment_notes_history
                      where payment_notes_history.customer_id = '".sql_friendly($customer_id)."'
                           and payment_notes_history.deleted<=0
                      order by payment_notes_history.linedate_added desc
               ";
               $datau = simple_query($sqlu);
               if($rowu = mysqli_fetch_array($datau))
               {                    
                    $pay_note="".$rowu['payment_note']."";
                    $pay_user="".$rowu['user_name']."";
                    $pay_date="".date("m/d/Y H:i:s",strtotime($rowu['linedate_added']))."";
               }
		     	     
		     $sql = "
     			select customers.name_company,
     				customers.stoplight_warn_notes,
     				customers.slow_pays,
     				customers.override_slow_pays,
					customers.credit_hold,
					customers.dirt_bags_flag,
					customers.override_credit_hold,
     				customers.payment_notes,
     				customers.sicap_id
     			from customers
     			where customers.deleted = 0			
     				and customers.id='".sql_friendly($customer_id)."'		
     		";
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data)) 
     		{
     			$customer_name = $row['name_company'];
     			$customer_notes = $row['stoplight_warn_notes'];
     			$overrider1 = $row['override_credit_hold'];
     			$overrider2 = $row['override_slow_pays'];
     			
     			$dbags=$row['dirt_bags_flag'];
     			
     			$sicap_id=$row['sicap_id'];
     			
     			if($row['credit_hold'] > 0)			$pay_notes .="<span class='alert'>Credit Hold</span> ";
     			if($row['slow_pays'] > 0)			$pay_notes .="<span class='alert'>Slow Pays</span> ";
     			if($row['dirt_bags_flag'] > 0)		$pay_notes .="<span class='alert'><b>DIRT BAGS!</b></span> ";
     			if($overrider1 > 0)					$pay_notes .=" <span style='color:#00cc00;'><b>Override Credit Hold.</b></span> ";
     			if($overrider2 > 0)					$pay_notes .=" <span style='color:#00cc00;'><b>Override Slow Pays.</b></span> ";
     			
                    if(trim($pay_note)!="")
                    {
                         $pay_notes .=" <b>Payment Notes:</b> ".$pay_note." <i>".$pay_date."</i> <b>".$pay_user."</b>";
                    }
                    elseif(trim($row['payment_notes'])!="")
                    {
                         $pay_notes .=" <b>Payment Notes:</b> ".$row['payment_notes'];
                    }                    
     			
     			$sqlx = "
          			select *
          			from customer_accounting_aging
          			where deleted = 0			
          				and customer_id='".sql_friendly($customer_id)."'	
          				and linedate_added >= '".date("Y-m-d",strtotime($_POST['date_to']))." 00:00:00'
          				and linedate_added <= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
          		";
          		$datax = simple_query($sqlx);
          		if($rowx= mysqli_fetch_array($datax)) 
          		{
          			$stored_vals=1;
					$cname=$rowx['cust_name'];
					$ar15=$rowx['due_15'];
					$ar30=$rowx['due_30'];
					$ar45=$rowx['due_45'];
					$ar46=$rowx['due_46'];
					$ar_tot=$rowx['due_15'] + $rowx['due_30'] + $rowx['due_45'] + $rowx['due_46'];
					$ar_days=$rowx['tot_days'];
					$mysql="";
					$tab15=$rowx['due_15_text'];
					$tab30=$rowx['due_30_text'];
					$tab45=$rowx['due_45_text'];
					$tab46=$rowx['due_46_text'];
          		}
     			
     		}
		}
		
		$temp_sql="";
		if($stored_vals==0)	
		{	

     		$results=mrr_get_ar_summary_info($sicap_id,$customer_name,$date_from,$date_to);	
				
			
     		
          	foreach($results as $key => $value )
               {
               	$prt=trim($key);			$tmp=trim($value);
               	if($prt=="Comparison")		$misc_income+=(float)$tmp;
               	
               	if($prt=="AgingName")		$cname=$tmp;
     			if($prt=="AgingFifteen")		$ar15+=(float)$tmp;
     			if($prt=="AgingThirty")		$ar30+=(float)$tmp;
     			if($prt=="AgingFortyFive")	$ar45+=(float)$tmp;
     			if($prt=="AgingOld")		$ar46+=(float)$tmp;
               	if($prt=="AgingTot")		$ar_tot+=(float)$tmp;
               	if($prt=="AgingDays")		$ar_days+=(float)$tmp;
               	if($prt=="AgingSQL")		$mysql=$tmp;          	
               	if($prt=="AgingTabA")		$tab15=$tmp;
     			if($prt=="AgingTabB")		$tab30=$tmp;
     			if($prt=="AgingTabC")		$tab45=$tmp;
     			if($prt=="AgingTabD")		$tab46=$tmp;
     			if($prt=="tempSQL")			$temp_sql=$tmp;
               }  
               
               $sqlx = "
          			update customer_accounting_aging set
          				deleted = '1'
          			where customer_id='".sql_friendly($customer_id)."'
          		";
          	simple_query($sqlx);
               
               //save it for the next time...
               $sqlx = "
          			insert into customer_accounting_aging
          				(id,
          				customer_id,
          				linedate_added,
          				deleted,
          				due_15,
          				due_30,
          				due_45,
          				due_46,
          				due_15_text,
          				due_30_text,
          				due_45_text,
          				due_46_text,
          				cust_name,
          				tot_days)
          			values
          				(NULL,
          				'".sql_friendly($customer_id)."',
          				NOW(),
          				0,
          				'".sql_friendly($ar15)."',
          				'".sql_friendly($ar30)."',
          				'".sql_friendly($ar45)."',
          				'".sql_friendly($ar46)."',
          				'".sql_friendly($tab15)."',
          				'".sql_friendly($tab30)."',
          				'".sql_friendly($tab45)."',
          				'".sql_friendly($tab46)."',
          				'".sql_friendly($cname)."',
          				'".sql_friendly($ar_days)."')
          		";
          	simple_query($sqlx);
               
     	}
          $mark="None";
          $image="images/stoplight_green.png";
          //grading system for stoplight..................disabled the auto-grading for customer paying for James...June 2015.
          if($ar_tot > 0)
          {	//has total default to bad
          	$mark="Great";
          	$image="images/stoplight_green.png";
          	
          	$sql2 = "     				
     			update customers set
     				slow_pays='0'
     			where customers.id='".sql_friendly($customer_id)."'
     				and override_slow_pays='1'
     			";
     		//simple_query($sql2);
          	
          	if($ar45 > 0)
          	{
          		$mark="Warn";
          		$image="images/stoplight_yellow.png";
          		$sql2 = "     				
     				update customers set
     					slow_pays='1'
     				where customers.id='".sql_friendly($customer_id)."'
     					and override_slow_pays='0'
     			";
     			//simple_query($sql2);
          	}  
          	if($ar46 > 0)
          	{
          		$mark="Bad";
          		$image="images/stoplight_red.png";	
          		          		
          		$sql2 = "     				
     				update customers set
     					
     					slow_pays='1'
     				where customers.id='".sql_friendly($customer_id)."'
     					and override_slow_pays='0'  
     			";
     			//simple_query($sql2);		//credit_hold='1',
          	}          	        	
          }   
          if($dbags>0)
          {
          	$mark="Bad";
          	$image="images/stoplight_red.png";		
          }        
          
          $span_fill_15="";
          $span_fill_30="";
          $span_fill_45="";
          $span_fill_46="";
          
          if($ar15 > 0)	$span_fill_15=" class='mrr_link_like_on' onClick='mrr_show_hide_group_span_alt(15);'";	//mrr_show_hide_group_span(15)
          if($ar30 > 0)	$span_fill_30=" class='mrr_link_like_on' onClick='mrr_show_hide_group_span_alt(30);'";	//mrr_show_hide_group_span(30)
          if($ar45 > 0)	$span_fill_45=" class='mrr_link_like_on' onClick='mrr_show_hide_group_span_alt(45);'";	//mrr_show_hide_group_span(45)
          if($ar46 > 0)	$span_fill_46=" class='mrr_link_like_on' onClick='mrr_show_hide_group_span_alt(46);'";	//mrr_show_hide_group_span(46)
                                  
          $mrr_tab="
          	<table border='0' width='100%'>
          		<tr>
          			<td valign='top'><b>Aging Summary</b></td>
          			<td valign='top'><span title='".$mark."'><b>Grade</b></span></td>
          			<td valign='top' align='right'><b>0-15</b></td>
          			<td valign='top' align='right'><b>16-30</b></td>
          			<td valign='top' align='right'><b>31-45</b></td>
          			<td valign='top' align='right'><b>46+</b></td>
          			<td valign='top' align='right'><b>Total</b></td>
          			<td valign='top' align='right'><b>Avg.Days</b></td>
          		</tr>
          		<tr>
          			<td valign='top'><b>".$customer_name."</b></td>
          			<td valign='top'><img src='".$image."' width='60' height='20' alt='".$mark."'/></td>
          			<td valign='top' align='right'><span".$span_fill_15.">$".number_format($ar15,2)."</span></td>
          			<td valign='top' align='right'><span".$span_fill_30.">$".number_format($ar30,2)."</span></td>
          			<td valign='top' align='right'><span".$span_fill_45.">$".number_format($ar45,2)."</span></td>
          			<td valign='top' align='right'><span".$span_fill_46.">$".number_format($ar46,2)."</span></td>
          			<td valign='top' align='right'>$".number_format($ar_tot,2)."</td>
          			<td valign='top' align='right'>".number_format($ar_days,2)."</td>
          		</tr>    
          		<tr>
          			<td valign='top'><b>Notes</b></td>
          			<td valign='top' colspan='7'>
          				<textarea name='cust_stoplight_warn_notes' id='cust_stoplight_warn_notes' class='mrr_stoplight_box' wrap='virtual' onBlur='mrr_save_stoplight_notes(".$customer_id.");'>".$customer_notes."</textarea>
          			</td>
          		</tr>        		
          	</table>
          ";
          
          $mrrA="<div class='skip_if_inserted_elsewhere' style='width:600px; height:500px; overflow:auto;'>".$tab15."</div>";	// class='mrr_link_like_on' onClick='mrr_show_hide_group_span(0);'
          $mrrB="<div class='skip_if_inserted_elsewhere' style='width:600px; height:500px; overflow:auto;'>".$tab30."</div>";
          $mrrC="<div class='skip_if_inserted_elsewhere' style='width:600px; height:500px; overflow:auto;'>".$tab45."</div>";
          $mrrD="<div class='skip_if_inserted_elsewhere' style='width:600px; height:500px; overflow:auto;'>".$tab46."</div>";
          
          display_xml_response("<rslt>1</rslt><paymentNotes><![CDATA[".$pay_notes."]]></paymentNotes><tempSQL><![CDATA[".$temp_sql."]]></tempSQL><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab><mrrA><![CDATA[".$mrrA."]]></mrrA><mrrB><![CDATA[".$mrrB."]]></mrrB><mrrC><![CDATA[".$mrrC."]]></mrrC>/<mrrD><![CDATA[".$mrrD."]]></mrrD>");
          //<mrrSQL><![CDATA[".$mysql."]]></mrrSQL>
	}
	function mrr_get_ar_detail_info_find_v2()
	{
		if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
		if(!isset($_POST['aging_from'])) 	$_POST['aging_from']=0;
		if(!isset($_POST['aging_to'])) 	$_POST['aging_to']=99999;
		
		$date_from=$_POST['date_from'];
		$date_to=date("n/j/Y", time());
		
		$aging_from=$_POST['aging_from'];
		$aging_to=$_POST['aging_to'];		
		
		$mrr_tab="";
		$resultsx="Disabled for testing...0,'',".$date_from.",".$date_to.",".$aging_from.",".$aging_to.".";
		$results=mrr_get_ar_summary_detail_info_v2(0,'',$date_from,$date_to,$aging_from,$aging_to);
          foreach($results as $key => $value )
		{
			$prt=trim($key);			$tmp=trim($value);
               if($prt=="mrrTab")			$mrr_tab=$tmp; 
		}		
		
          display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab><resultMRR><![CDATA[".$resultsx."]]></resultMRR>");	//<mrrA><![CDATA[".$mrrA."]]></mrrA><mrrB><![CDATA[".$mrrB."]]></mrrB><mrrC><![CDATA[".$mrrC."]]></mrrC>/<mrrD><![CDATA[".$mrrD."]]></mrrD>
          //<mrrSQL><![CDATA[".$mysql."]]></mrrSQL>
	}
	function mrr_get_ar_detail_info_find()
	{
		if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
		if(!isset($_POST['aging_from'])) 	$_POST['aging_from']=0;
		if(!isset($_POST['aging_to'])) 	$_POST['aging_to']=99999;
		
		$date_from=$_POST['date_from'];
		$date_to=date("n/j/Y", time());
		
		$aging_from=$_POST['aging_from'];
		$aging_to=$_POST['aging_to'];
		
		$customer_id=0;
		$customer_name="";
		$customer_notes="";
		
		$mrr_tab="
			<table border='0' width='100%'>
          		<tr>
          			<td valign='top'><b>Aging Summary</b></td>
          			<td valign='top'><b>Grade</b></td>
          			<td valign='top' align='right'><b>0-15</b></td>
          			<td valign='top' align='right'><b>16-30</b></td>
          			<td valign='top' align='right'><b>31-45</b></td>
          			<td valign='top' align='right'><b>46+</b></td>
          			<td valign='top' align='right'><b>Total</b></td>
          			<td valign='top' align='right'><b>Avg.Days</b></td>
          		</tr>";
				
		if($customer_id==0)
		{
     		$sql = "
     			select customers.id, 
     				customers.name_company,
     				customers.stoplight_warn_notes	
     			from customers
     			where customers.deleted = 0		
     			order by name_company asc,id asc
     		";		
     		$data = simple_query($sql);
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$customer_id = $row['id'];
     			$customer_name = $row['name_company'];
     			$customer_notes = $row['stoplight_warn_notes'];
     		
          		$cname="";
          		$ar15=0;
          		$ar30=0;
          		$ar45=0;
          		$ar46=0;
          		$ar_tot=0;
          		$ar_days=0;
          		$mysql="";
          		$tabinv="";
          		$all_of_them=0;
          		
          		$results=mrr_get_ar_summary_detail_info(0,$customer_name,$date_from,$date_to,$aging_from,$aging_to);
               	foreach($results as $key => $value )
                    {
                    	$prt=trim($key);			$tmp=trim($value);
                    	if($prt=="Comparison")		$misc_income+=(float)$tmp;
                    	
                    	if($prt=="AgingName")		$cname=$tmp;
          			if($prt=="AgingFifteen")		$ar15+=(float)$tmp;
          			if($prt=="AgingThirty")		$ar30+=(float)$tmp;
          			if($prt=="AgingFortyFive")	$ar45+=(float)$tmp;
          			if($prt=="AgingOld")		$ar46+=(float)$tmp;
                    	if($prt=="AgingTot")		$ar_tot+=(float)$tmp;
                    	if($prt=="AgingDays")		$ar_days+=(float)$tmp;
                    	if($prt=="AgingSQL")		$mysql=$tmp;          	
                    	if($prt=="AgingTab")		$tabinv=$tmp;
                    	if($prt=="AgingAll")		$all_of_them=$tmp;
                    }  
                    
                    $mark="None";
                    $image="images/stoplight_green.png";
                    //grading system for stoplight
                    if($ar_tot > 0)
                    {	//has total default to bad
                    	$mark="Great";
                    	$image="images/stoplight_green.png";	
                    	
                    	if($ar45 > 0)
                    	{
                    		$mark="Warn";
                    		$image="images/stoplight_yellow.png";
                    	}  
                    	if($ar46 > 0)
                    	{
                    		$mark="Bad";
                    		$image="images/stoplight_red.png";	
                    	}          	        	
                    }           
                                            
                    $mrr_tab.="                    	
                    		<tr>
                    			<td valign='top'><b>".$customer_name."</b></td>
                    			<td valign='top'><img src='".$image."' width='60' height='20' alt='".$mark."'/></td>
                    			<td valign='top' align='right'>$".number_format($ar15,2)."</td>
                    			<td valign='top' align='right'>$".number_format($ar30,2)."</td>
                    			<td valign='top' align='right'>$".number_format($ar45,2)."</td>
                    			<td valign='top' align='right'>$".number_format($ar46,2)."</td>
                    			<td valign='top' align='right'>$".number_format($ar_tot,2)."</td>
                    			<td valign='top' align='right'>".number_format($ar_days,2)."</td>
                    		</tr> 
                    "; 
                    if(trim($tabinv)=="")    
                    {
                    	$all_of_them=(float)$all_of_them;
                    	$mrr_tab.="
                    		<tr>
          					<td valign='top'><b>Aged items from ".$aging_from."-".$aging_to." Days:</b><br>All $".number_format($all_of_them,2)."</td>
          					<td valign='top' colspan='7'>".$tabinv."</td>
          				</tr>
          			";
          		}	               
                    if(trim($customer_notes)!="")
                    {
                    	$mrr_tab.="
                    		<tr>
          					<td valign='top'><b>Notes</b></td>
          					<td valign='top' colspan='7'>".$customer_notes."</td>
          				</tr>
          			";	
                    }   
                    $mrr_tab.="
                    		<tr>
          					<td valign='top'><b>&nbsp;</b></td>
          					<td valign='top' colspan='7'>&nbsp;</td>
          				</tr>
          			";                
     		}
		}
		$mrr_tab.="</table>";
		
          display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	//<mrrA><![CDATA[".$mrrA."]]></mrrA><mrrB><![CDATA[".$mrrB."]]></mrrB><mrrC><![CDATA[".$mrrC."]]></mrrC>/<mrrD><![CDATA[".$mrrD."]]></mrrD>
          //<mrrSQL><![CDATA[".$mysql."]]></mrrSQL>
	}
	function mrr_save_stoplight_warning_notes()
	{
		$customer_id=$_POST['cust_id'];
		$customer_notes=$_POST['cust_notes'];
		$mrr_tab="Not Found";	
		
		if($customer_id>0)
		{
     		$sql = "
     			update customers
     			set customers.stoplight_warn_notes='".sql_friendly($customer_notes)."'
     			where customers.id='".sql_friendly($customer_id)."'	
     		";
     		simple_query($sql);
     		
     		$mrr_tab="Saved";
		}
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Added Note to Customer ".$customer_id.". ".$customer_notes."";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	
	}
	
	
	function mrr_restore_canceled_load()
	{
		$load_id=$_POST['load_id'];
		$mode=$_POST['moder'];
		
		$mrr_tab="Not Found";	
		
		if($load_id>0)
		{
     		$sql = "
     			update load_handler
     			set load_handler.deleted='".sql_friendly($mode)."'
     			where load_handler.id='".sql_friendly($load_id)."'	
     		";
     		simple_query($sql);
     		
     		$sql = "
     			update trucks_log
     			set trucks_log.deleted='".sql_friendly($mode)."'
     			where trucks_log.load_handler_id='".sql_friendly($load_id)."'	
     		";
     		simple_query($sql);
     		
     		$mrr_tab="Restored";
		}
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Restored Cancelled Load ".$load_id."";
		$mrr_activity_log['load_handler_id']=$load_id;
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,0,0,"Restored Cancelled Load ".$load_id.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	
	}
	
	function mrr_pull_driver_charge_rate()
	{
		$driver1_id=$_POST['driver1_id'];
		$driver2_id=$_POST['driver2_id'];
		
		global $defaultsarray;
		
		$miles1 = 0;
		$hours1 = 0;
		$miles1t = 0;
		$hours1t = 0;
		
		$miles2 = 0;
		$hours2 = 0;
		$miles2t = 0;
		$hours2t = 0;
		
		$rate_mile=0;
		$rate_hour=0;
		
		$use_team=0;
		if($driver1_id>0)
		{
			$use_team++;
			$sql = "
     			select charged_per_hour,
     				charged_per_mile,
     				charged_per_hour_team,
     				charged_per_mile_team,
     				id	
     			from drivers
     			where id='".sql_friendly($driver1_id)."'
     		";
     		$data = simple_query($sql);
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$miles1 = $row['charged_per_mile'];
     			$hours1 = $row['charged_per_hour'];
     			$miles1t = $row['charged_per_mile_team'];
     			$hours1t = $row['charged_per_hour_team'];
     		}
     		$rate_mile=$miles1;
			$rate_hour=$hours1;
			
			if($rate_mile==0)	$rate_mile=$defaultsarray['labor_per_mile'];	
			if($rate_hour==0)	$rate_hour=$defaultsarray['labor_per_hour'];
		}
		if($driver2_id>0)	
		{
			$use_team++;
			$sql = "
     			select charged_per_hour,
     				charged_per_mile,
     				charged_per_hour_team,
     				charged_per_mile_team,
     				id		
     			from drivers
     			where id='".sql_friendly($driver2_id)."'
     		";
     		$data = simple_query($sql);
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$miles2 = $row['charged_per_mile'];
     			$hours2 = $row['charged_per_hour'];
     			$miles2t = $row['charged_per_mile_team'];
     			$hours2t = $row['charged_per_hour_team'];
     		}
     		$rate_mile=$miles2;
			$rate_hour=$hours2;
		
			if($rate_mile==0)	$rate_mile=$defaultsarray['labor_per_mile'];	
			if($rate_hour==0)	$rate_hour=$defaultsarray['labor_per_hour'];
		}
				
		if($use_team>1)
		{	//use team values
			if($miles1t==0)	$miles1t=$defaultsarray['labor_per_mile_team']/2;
			if($miles2t==0)	$miles2t=$defaultsarray['labor_per_mile_team']/2;
			
			$rate_mile=$miles1t + $miles2t;
			$rate_hour=$hours1t + $hours2t;
		}	
		$rval="
			<PayRates>
				<PayRateMile><![CDATA[".$rate_mile."]]></PayRateMile>
				<PayRateHour><![CDATA[".number_format($rate_hour,2)."]]></PayRateHour>
			</PayRates>
		";		
		display_xml_response("<rslt>1</rslt>$rval");	
	}
	

	function mrr_lading_number_search()
	{
		$mrr_tab="";
		$lading_number=trim($_POST['lading_number']);
		$load_id=(int) $_POST['load_id'];
		$mn=0;
		
		if($lading_number!="")
		{
			$sql = "
     			select id,
     				load_number	
     			from load_handler
     			where id!='".sql_friendly($load_id)."'
     				and load_number='".sql_friendly($lading_number)."'
     				and deleted=0
     			order by id desc
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{
     			$mrr_tab.="
     				<div class='alert'>Warning: This Lading Number was found on ".$mn." Load(s) already.</div>
     				<table border='0'>
     				<tr>
     					<td valign='top' width='100'>Load</td>
     					<td valign='top'>Lading Number</td>
     				</tr>
     			";	
     		}     		
     		
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$lading = $row['load_number'];
     			$load = $row['id'];
     			
     			$mrr_tab.="
     				<tr>
     					<td valign='top'><a href='manage_load.php?load_id=".$load."' target='_blank'>".$load."</a></td>
     					<td valign='top'>".$lading."</td>
     				</tr>
     			";	
     		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab.="
     				</table>
     			";	
     		} 
		}
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Searching for Lading Number ".$lading_number." to use on Load ".$load_id.".  Found ".$mn." Match(es)";
		$mrr_activity_log['load_handler_id']=$load_id;
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");		
	}

	
	function mrr_toggle_dropped_trailers_on()
	{
		$_SESSION['toggle_dropped_trailer_on']=1;
		display_xml_response("<rslt>1</rslt><mrrToggle><![CDATA[On]]></mrrToggle>");	
	}
	function mrr_toggle_dropped_trailers_off()
	{
		$_SESSION['toggle_dropped_trailer_on']=0;
		display_xml_response("<rslt>1</rslt><mrrToggle><![CDATA[Off]]></mrrToggle>");		
	}
     	
	function mrr_full_search()
	{		
		$search_term=trim($_POST['search_term']);
		$search_term2=trim($_POST['search_term2']);
		$search_term3=trim($_POST['search_term3']);
		$search_term4=trim($_POST['search_term4']);
		$search_term5=trim($_POST['search_term5']);
		$search_term6=trim($_POST['search_term6']);
		$search_term7=trim($_POST['search_term7']);
		$search_term8=trim($_POST['search_term8']);
		
		$search_num=0;
		$mrr_adder_num="";
		$mrr_adder_cust="";
		$mrr_adder_truck="";
		$mrr_adder_trailer="";
		if(is_numeric($search_term))	$search_num=(int)$search_term;
		if($search_num>0)
		{
			$mrr_adder_num=" or id='".sql_friendly($search_num)."'";	
			$mrr_adder_cust=" and customers.mc_num='".sql_friendly($search_num)."'";	
			$mrr_adder_truck=" or trucks_log.id='".sql_friendly($search_num)."'";	
			$mrr_adder_trailer=" or load_handler_stops.id='".sql_friendly($search_num)."'";	
		}	
		$test_search=$search_term." ".$search_term2." ".$search_term3." ".$search_term4." ".$search_term5." ".$search_term6." ".$search_term7." ".$search_term8;		
		$mrr_tab="";	//"No results found for ".$test_search.".";
		
		$mrr_tab1="";
		$mrr_tab2="";
		$mrr_tab3="";
		$mrr_tab4="";
		$mrr_tab5="";
		$mrr_tab6="";
		$mrr_tab7="";
		$mrr_tab8="";
		$mrr_tab9="";
		$mrr_tab10="";
		$mrr_tab11="";
		$mrr_tab12="";
		
		if(trim($test_search)!="")
		{     		
     		//loads
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and (load_number like '%".sql_friendly($search_term)."%' or invoice_number like '%".sql_friendly($search_term)."%'".$mrr_adder_num.")";	
     		$mrr_adder2="";		if($search_term2!="")	$mrr_adder2=" and (origin_address1 like '%".sql_friendly($search_term2)."%' or dest_address1 like '%".sql_friendly($search_term2)."%')";
     		$mrr_adder3="";		if($search_term3!="")	$mrr_adder3=" and (origin_address2 like '%".sql_friendly($search_term3)."%' or dest_address2 like '%".sql_friendly($search_term3)."%')";
     		$mrr_adder4="";		if($search_term4!="")	$mrr_adder4=" and (origin_city like '%".sql_friendly($search_term4)."%' or dest_city like '%".sql_friendly($search_term4)."%')";
     		$mrr_adder5="";		if($search_term5!="")	$mrr_adder5=" and (origin_state like '%".sql_friendly($search_term5)."%' or dest_state like '%".sql_friendly($search_term5)."%')";
     		$mrr_adder6="";		if($search_term6!="")	$mrr_adder6=" and (origin_zip like '%".sql_friendly($search_term6)."%' or dest_zip like '%".sql_friendly($search_term6)."%')";
     		$mrr_adder7="";		if($search_term7!="")	$mrr_adder7="";
     		$mrr_adder8="";		if($search_term8!="")	$mrr_adder8="";
			$mn=0;
			$sql = "
     			select *	
     			from load_handler
     			where deleted>=0
     				".$mrr_adder."
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab1.="
     				<br><br><br><div class='section_heading'>Loads</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>ID</th>
     					<th valign='top'>Load#</th>
     					<th valign='top'>Invoice</th>
     					<th valign='top'>Type</th>
     					<th valign='top'>Address1</th>
     					<th valign='top'>Address2</th>
     					<th valign='top'>City</th>
     					<th valign='top'>State</th>
     					<th valign='top'>Zip</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$linker="<a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			     			
     			$mrr_tab1.="
     				<tr>
     					<td valign='top'>".$linker."</td>
     					<td valign='top'".$classy.">".$row['load_number']."</td>
     					<td valign='top'".$classy.">".$row['invoice_number']."</td>
     					<td valign='top'".$classy.">Origin<br>Dest</td>
     					<td valign='top'".$classy.">".$row['origin_address1']."<br>".$row['dest_address1']."</td>
     					<td valign='top'".$classy.">".$row['origin_address2']."<br>".$row['dest_address2']."</td>
     					<td valign='top'".$classy.">".$row['origin_city']."<br>".$row['dest_city']."</td>
     					<td valign='top'".$classy.">".$row['origin_state']."<br>".$row['dest_state']."</td>
     					<td valign='top'".$classy.">".$row['origin_zip']."<br>".$row['dest_zip']."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab1.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
     		
     		//load stops     		
     		$mrr_trailer="";		if($search_term!="")	$mrr_trailer=" or t1.trailer_name like '%".sql_friendly($search_term)."%' or t2.trailer_name like '%".sql_friendly($search_term)."%'";
     		
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and (shipper_name like '%".sql_friendly($search_term)."%' or dest_name like '%".sql_friendly($search_term)."%'".$mrr_adder_trailer."".$mrr_trailer.")";	
     		$mrr_adder2="";		if($search_term2!="")	$mrr_adder2=" and (shipper_address1 like '%".sql_friendly($search_term2)."%' or dest_address1 like '%".sql_friendly($search_term2)."%')";
     		$mrr_adder3="";		if($search_term3!="")	$mrr_adder3=" and (shipper_address2 like '%".sql_friendly($search_term3)."%' or dest_address2 like '%".sql_friendly($search_term3)."%')";
     		$mrr_adder4="";		if($search_term4!="")	$mrr_adder4=" and (shipper_city like '%".sql_friendly($search_term4)."%' or dest_city like '%".sql_friendly($search_term4)."%')";
     		$mrr_adder5="";		if($search_term5!="")	$mrr_adder5=" and (shipper_state like '%".sql_friendly($search_term5)."%' or dest_state like '%".sql_friendly($search_term5)."%')";
     		$mrr_adder6="";		if($search_term6!="")	$mrr_adder6=" and (shipper_zip like '%".sql_friendly($search_term6)."%' or dest_zip like '%".sql_friendly($search_term6)."%')";
     		$mrr_adder7="";		if($search_term7!="")	$mrr_adder7="";
     		$mrr_adder8="";		if($search_term8!="")	$mrr_adder8="";
			$mn=0;
			$sql = "
     			select load_handler_stops.*,
     				t1.trailer_name as start_trailer_name,
     				t2.trailer_name as end_trailer_name,	
     				t1.nick_name as start_nick_name,
     				t2.nick_name as end_nick_name
     			from load_handler_stops
     				left join trailers t1 on t1.id=load_handler_stops.start_trailer_id
     				left join trailers t2 on t2.id=load_handler_stops.end_trailer_id
     			where load_handler_stops.deleted>=0
     				".$mrr_adder."
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by load_handler_stops.id desc
     			limit 50
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab2.="
     				<div class='section_heading'>Stops</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Stop</th>
     					<th valign='top'>Load</th>
     					<th valign='top'>Dispatch</th>
     					<th valign='top'>Start Trailer</th>
     					<th valign='top'>End Trailer</th>				
     					<th valign='top'>Type</th>
     					<th valign='top'>Name</th>
     					<th valign='top'>Address1</th>
     					<th valign='top'>Address2</th>
     					<th valign='top'>City</th>
     					<th valign='top'>State</th>
     					<th valign='top'>Zip</th>
     					<th valign='top'>Pick Up ETA</th>
     					<th valign='top'>Completed</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{
     			
     			$linker1="<a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a>";
     			$linker2="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['trucks_log_id']."' target='_blank'>".$row['trucks_log_id']."</a>";
     			
     			$linker3="<a href='admin_trailers.php?id=".$row['start_trailer_id']."' target='_blank'>".$row['start_trailer_name']."</a>";
     			if(trim($row['start_nick_name'])!="")		$linker3="<a href='admin_trailers.php?id=".$row['start_trailer_id']."' target='_blank'>".$row['start_nick_name']."</a>";
     			$linker4="<a href='admin_trailers.php?id=".$row['end_trailer_id']."' target='_blank'>".$row['end_trailer_name']."</a>";
     			if(trim($row['end_nick_name'])!="")		$linker4="<a href='admin_trailers.php?id=".$row['end_trailer_id']."' target='_blank'>".$row['end_nick_name']."</a>";
     			
     			$dater="";
     			if($row['linedate_completed']!='0000-00-00 00:00:00')
     			{
     				$dater=date("m/d/Y",strtotime($row['linedate_completed']));	
     			}
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab2.="
     				<tr>
     					<td valign='top'>".$row['id']."</td>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'>".$linker2."</td>
     					<td valign='top'>".$linker3."</td>
     					<td valign='top'>".$linker4."</td>
     					<td valign='top'".$classy.">Ship<br>Dest</td>
     					<td valign='top'".$classy.">".$row['shipper_name']."<br>".$row['dest_name']."</td>
     					<td valign='top'".$classy.">".$row['shipper_address1']."<br>".$row['dest_address1']."</td>
     					<td valign='top'".$classy.">".$row['shipper_address2']."<br>".$row['dest_address2']."</td>
     					<td valign='top'".$classy.">".$row['shipper_city']."<br>".$row['dest_city']."</td>
     					<td valign='top'".$classy.">".$row['shipper_state']."<br>".$row['dest_state']."</td>
     					<td valign='top'".$classy.">".$row['shipper_zip']."<br>".$row['dest_zip']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</td>
     					<td valign='top'".$classy.">".$dater."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab2.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
     		
     		//dispatches
     		$mrr_truck="";			if($search_term!="")	$mrr_truck=" or name_truck like '%".sql_friendly($search_term)."%' or trailer_name like '%".sql_friendly($search_term)."%'";
     		
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and (origin like '%".sql_friendly($search_term)."%' or destination like '%".sql_friendly($search_term)."%' or location like '%".sql_friendly($search_term)."%' or trailer like '%".sql_friendly($search_term)."%'".$mrr_adder_truck."".$mrr_truck.")";	
     		$mrr_adder2="";		//if($search_term2!="")	$mrr_adder2=" and (shipper_address1 like '%".sql_friendly($search_term2)."%' or dest_address1 like '%".sql_friendly($search_term2)."%')";
     		$mrr_adder3="";		//if($search_term3!="")	$mrr_adder3=" and (shipper_address2 like '%".sql_friendly($search_term3)."%' or dest_address2 like '%".sql_friendly($search_term3)."%')";
     		$mrr_adder4="";		//if($search_term4!="")	$mrr_adder4=" and (shipper_city like '%".sql_friendly($search_term4)."%' or dest_city like '%".sql_friendly($search_term4)."%')";
     		$mrr_adder5="";		if($search_term5!="")	$mrr_adder5=" and (origin_state like '%".sql_friendly($search_term5)."%' or destination_state like '%".sql_friendly($search_term5)."%')";
     		$mrr_adder6="";		//if($search_term6!="")	$mrr_adder6=" and (shipper_zip like '%".sql_friendly($search_term6)."%' or dest_zip like '%".sql_friendly($search_term6)."%')";
     		$mrr_adder7="";		if($search_term7!="")	$mrr_adder7="";
     		$mrr_adder8="";		if($search_term8!="")	$mrr_adder8="";
			$mn=0;
			$sql = "
     			select trucks_log.*,
     				trucks.name_truck as truck_namer,
     				trailers.trailer_name as trailer_namer,
     				trailers.nick_name as nick_namer
     			from trucks_log
     				left join trucks on trucks.id=trucks_log.truck_id
     				left join trailers on trailers.id=trucks_log.trailer_id
     			where trucks_log.deleted>=0
     				".$mrr_adder."
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by trucks_log.id desc
     			limit 50
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab3.="
     				<div class='section_heading'>Dispatches</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Dispatch</th>
     					<th valign='top'>Load</th>  
     					<th valign='top'>Truck</th>   					
     					<th valign='top'>Trailer</th>
     					<th valign='top'>Location</th>
     					<th valign='top' colspan='2'>Origin</th>
     					<th valign='top' colspan='2'>Destination</th>
     					<th valign='top'>PickUp</th>
     					<th valign='top'>Updated</th>
     					<th valign='top'>Done</th>
     					<th valign='top'>Cost</th>
     					<th valign='top'>Profit</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{
     			
     			$linker1="<a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a>";
     			$linker2="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a>";
     			
     			$linker3="<a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_namer']."</a>";
     			$linker4="<a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_namer']."</a>";
     			if(trim($row['nick_namer'])!="")		$linker4="<a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['nick_namer']."</a>";
     			
     			$dater="";
     			$completer="No";
     			if($row['linedate_updated']!='0000-00-00 00:00:00')
     			{
     				$dater=date("m/d/Y",strtotime($row['linedate_updated']));	
     			}
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			
     			if($row['dispatch_completed'] > 0)		$completer="Yes";
     			$mrr_tab3.="
     				<tr>
     					<td valign='top'>".$linker2."</td>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'>".$linker3."</td>
     					<td valign='top'>".$linker4."</td>
     					<td valign='top'".$classy.">".$row['location']."</td>
     					<td valign='top'".$classy.">".$row['origin']."</td>
     					<td valign='top'".$classy.">".$row['origin_state']."</td>
     					<td valign='top'".$classy.">".$row['destination']."</td>
     					<td valign='top'".$classy.">".$row['destination_state']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</td>
     					<td valign='top'".$classy.">".$dater."</td>
     					<td valign='top'".$classy.">".$completer."</td>
     					<td valign='top'".$classy.">$".number_format($row['cost'],2)."</td>
     					<td valign='top'".$classy.">$".number_format($row['profit'],2)."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab3.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
     		
     		//dropped trailers
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and notes like '%".sql_friendly($search_term)."%'".$mrr_adder_num."";	
     		$mrr_adder2="";		//if($search_term2!="")	$mrr_adder2=" and shipper_address1 like '%".sql_friendly($search_term2)."%'";
     		$mrr_adder3="";		//if($search_term3!="")	$mrr_adder3=" and shipper_address2 like '%".sql_friendly($search_term3)."%'";
     		$mrr_adder4="";		if($search_term4!="")	$mrr_adder4=" and location_city like '%".sql_friendly($search_term4)."%'";
     		$mrr_adder5="";		if($search_term5!="")	$mrr_adder5=" and location_state like '%".sql_friendly($search_term5)."%'";
     		$mrr_adder6="";		if($search_term6!="")	$mrr_adder6=" and location_zip like '%".sql_friendly($search_term6)."%'";
     		$mrr_adder7="";		if($search_term7!="")	$mrr_adder7="";
     		$mrr_adder8="";		if($search_term8!="")	$mrr_adder8="";
			$mn=0;
			$sql = "
     			select *	
     			from trailers_dropped
     			where deleted>=0
     				".$mrr_adder."
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab4.="
     				<div class='section_heading'>Dropped Trailers</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Drop</th>
     					<th valign='top'>Notes</th>
     					<th valign='top'>City</th>
     					<th valign='top'>State</th>
     					<th valign='top'>Zip</th>
     					<th valign='top'>Date</th>
     					<th valign='top'>Dropped</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{
     			
     			$linker="<a href='trailer_drop.php?id=".$row['id']."' target='_blank'>".$row['id']."</a>";
     			
     			$completer="No";
     			if($row['drop_completed'] > 0)		$completer="Yes";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab4.="
     				<tr>
     					<td valign='top'>".$linker."</td>
     					<td valign='top'".$classy.">".$row['notes']."</td>
     					<td valign='top'".$classy.">".$row['location_city']."</td>
     					<td valign='top'".$classy.">".$row['location_state']."</td>
     					<td valign='top'".$classy.">".$row['location_zip']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate']))."</td>
     					<td valign='top'".$classy.">".$completer."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab4.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
     		
     		//customers
     		$mrr_adder="";		
     		$mrr_adder2="";	
     		$mrr_adder3="";	
     		$mrr_adder4="";	
     		$mrr_adder5="";	
     		$mrr_adder6="";	
     		$mrr_adder7="";
     		$mrr_adder8="";
     		
     		if(trim($mrr_adder_cust)!="")	
     		{
     			$mrr_adder=trim($mrr_adder_cust);		
     		}
     		else
     		{
     			if($search_term!="")	$mrr_adder=" and (name_company like '%".sql_friendly($search_term)."%' or contact_primary like '%".sql_friendly($search_term)."%'".$mrr_adder_num.")";	
     			if($search_term2!="")	$mrr_adder2=" and address1 like '%".sql_friendly($search_term2)."%'";
     			if($search_term3!="")	$mrr_adder3=" and address2 like '%".sql_friendly($search_term3)."%'";
     			if($search_term4!="")	$mrr_adder4=" and city like '%".sql_friendly($search_term4)."%'";
     			if($search_term5!="")	$mrr_adder5=" and state like '%".sql_friendly($search_term5)."%'";
     			if($search_term6!="")	$mrr_adder6=" and zip like '%".sql_friendly($search_term6)."%'";
     			if($search_term7!="")	$mrr_adder7="";
     			if($search_term8!="")	$mrr_adder8="";
     		}
     		
			$mn=0;
			$sql = "
     			select *	
     			from customers
     			where deleted>=0
     				".$mrr_adder."
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab5.="
     				<div class='section_heading'>Customers</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Company</th>
     					<th valign='top'>MC No.</th>
     					<th valign='top'>Contact</th>
     					<th valign='top'>Address1</th>
     					<th valign='top'>Address2</th>
     					<th valign='top'>City</th>
     					<th valign='top'>State</th>
     					<th valign='top'>Zip</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$linker="<a href='admin_customers.php?eid=".$row['id']."' target='_blank'>".$row['name_company']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab5.="
     				<tr>
     					<td valign='top'>".$linker."</td>
     					<td valign='top'".$classy.">".$row['mc_num']."</td>
     					<td valign='top'".$classy.">".$row['contact_primary']."</td>
     					<td valign='top'".$classy.">".$row['address1']."</td>
     					<td valign='top'".$classy.">".$row['address2']."</td>
     					<td valign='top'".$classy.">".$row['city']."</td>
     					<td valign='top'".$classy.">".$row['state']."</td>
     					<td valign='top'".$classy.">".$row['zip']."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab5.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
     		
     		//customer contacts
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and contact_name like '%".sql_friendly($search_term)."%'";
     		$mn=0;
			$sql = "
     			select *	
     			from customer_contacts
     			where deleted>=0
     				".$mrr_adder."
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab6.="
     				<div class='section_heading'>Customer Contacts</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Contact</th>
     					<th valign='top'>Address1</th>
     					<th valign='top'>Address2</th>
     					<th valign='top'>City</th>
     					<th valign='top'>State</th>
     					<th valign='top'>Zip</th>
     				</tr>
     				</thead>
     				<tbody>
     				
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$linker="<a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['contact_name']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab6.="
     				<tr>
     					<td valign='top'>".$linker."</td>
     					<td valign='top'".$classy.">".$row['address1']."</td>
     					<td valign='top'".$classy.">".$row['address2']."</td>
     					<td valign='top'".$classy.">".$row['city']."</td>
     					<td valign='top'".$classy.">".$row['state']."</td>
     					<td valign='top'".$classy.">".$row['zip']."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab6.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		}      	
			
			//trucks
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and (name_truck like '%".sql_friendly($search_term)."%'".$mrr_adder_num.")";
     		$mn=0;
			$sql = "
     			select *	
     			from trucks
     			where deleted>=0
     				".$mrr_adder."     				
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		/*		NO ADDRESS here
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     		*/
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab7.="
     				<div class='section_heading'>Trucks</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Truck Name</th>
     					<th valign='top'>Year</th>
     					<th valign='top'>Make</th>
     					<th valign='top'>Model</th>
     					<th valign='top'>Monthly Cost</th>
     					<th valign='top'>Aquired</th>
     					<th valign='top'>Returned</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$mrr_classy="";
     			if($row['active']==0)	$mrr_classy=" style='color:#dddddd;'";
     			
     			$linker1="<a".$mrr_classy." href='admin_trucks.php?id=".$row['id']."' target='_blank'>".$row['name_truck']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab7.="
     				<tr>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'".$classy.">".$row['truck_year']."</td>
     					<td valign='top'".$classy.">".$row['truck_make']."</td>
     					<td valign='top'".$classy.">".$row['truck_model']."</td>
     					<td valign='top'".$classy.">$".number_format($row['monthly_cost'],2)."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_aquired']))."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_returned']))."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab7.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		}
			
			//trailers
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and (trailer_name like '%".sql_friendly($search_term)."%'".$mrr_adder_num.")";
     		$mn=0;
			$sql = "
     			select *	
     			from trailers
     			where deleted>=0
     				".$mrr_adder."     				
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		/*		NO ADDRESS here
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     		*/
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab8.="
     				<div class='section_heading'>Trailers</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>Trailer Name</th>
     					<th valign='top'>Year</th>
     					<th valign='top'>Make</th>
     					<th valign='top'>Model</th>
     					<th valign='top'>Owner</th>
     					<th valign='top'>Monthly Cost</th>
     					<th valign='top'>Aquired</th>
     					<th valign='top'>Returned</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$mrr_classy="";
     			if($row['active']==0)	$mrr_classy=" style='color:#dddddd;'";
     			
     			$linker1="<a".$mrr_classy." href='admin_trailers.php?id=".$row['id']."' target='_blank'>".$row['trailer_name']."</a>";
     			if(trim($row['nick_name'])!="")	$linker1="<a".$mrr_classy." href='admin_trailers.php?id=".$row['id']."' target='_blank'>".$row['nick_name']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab8.="
     				<tr>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'".$classy.">".$row['trailer_year']."</td>
     					<td valign='top'".$classy.">".$row['trailer_make']."</td>
     					<td valign='top'".$classy.">".$row['trailer_model']."</td>
     					<td valign='top'".$classy.">".$row['trailer_owner']."</td>
     					<td valign='top'".$classy.">$".number_format($row['monthly_cost_actual'],2)."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_aquired']))."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_returned']))."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab8.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		}
			
			//drivers
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and (CONCAT(name_driver_first,' ',name_driver_last) like '%".sql_friendly($search_term)."%'".$mrr_adder_num.")";
     		$mn=0;
			$sql = "
     			select *	
     			from drivers
     			where deleted>=0
     				".$mrr_adder."     				
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		/*		NO ADDRESS here
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     		*/
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab9.="
     				<div class='section_heading'>Drivers</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>First Name</th>
     					<th valign='top'>Last Name</th>
     					<th valign='top'>Cell</th>
     					<th valign='top'>Home</th>
     					<th valign='top'>Other</th>
     					<th valign='top'>Started</th>
     					<th valign='top'>Birthday</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$mrr_classy="";
     			if($row['active']==0)	$mrr_classy=" style='color:#dddddd;'";
     			
     			$linker1="<a".$mrr_classy." href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['name_driver_first']."</a>";
     			$linker2="<a".$mrr_classy." href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['name_driver_last']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab9.="
     				<tr>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'>".$linker2."</td>
     					<td valign='top'".$classy.">".$row['phone_cell']."</td>
     					<td valign='top'".$classy.">".$row['phone_home']."</td>
     					<td valign='top'".$classy.">".$row['phone_other']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_started']))."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_birthday']))."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab9.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
     		
     		//notes
     		$mrr_adder="";			if($search_term!="")	$mrr_adder=" and desc_long like '%".sql_friendly($search_term)."%'";
     		$mn=0;
			$sql = "
     			select *	
     			from notes
     			where deleted>=0
     				".$mrr_adder."     				
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		/*		NO ADDRESS here
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     		*/
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab10.="
     				<div class='section_heading'>Notes</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>ID</th>
     					<th valign='top'>Note</th>
     					<th valign='top'>Date</th>
     					<th valign='top'>Deadline</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$mrr_classy="";
     			if($row['active']==0)	$mrr_classy=" style='color:#dddddd;'";
     			
     			$linker1="<a".$mrr_classy." href='edit_note.php?sid=1&id=".$row['id']."' target='_blank'>".$row['id']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab10.="
     				<tr>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'".$classy.">".$row['desc_long']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate']))."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['deadline']))."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab10.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		} 
		
			//quotes
     		$mrr_adder="";			
     		if($search_term!="")		$mrr_adder=" and (quote_name like '%".sql_friendly($search_term)."%' or quote_name like '%".sql_friendly($search_term)."%')";
     		
     		$mn=0;
			$sql = "
     			select *	
     			from quotes
     			where deleted>=0
     				".$mrr_adder."     				
     				".$mrr_adder7."
     				".$mrr_adder8."	
     			order by id desc
     		";
     		/*		NO ADDRESS here
     				".$mrr_adder2."
     				".$mrr_adder3."
     				".$mrr_adder4."
     				".$mrr_adder5."
     				".$mrr_adder6."
     		*/
     		$data = simple_query($sql);
     		$mn=mysqli_num_rows($data);
     		if($mn > 0)
     		{     			
     			$mrr_tab11.="
     				<div class='section_heading'>Quotes</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>ID</th>
     					<th valign='top'>Quote Name</th>
     					<th valign='top'>Quote Notes</th>
     					<th valign='top'>Date</th>
     					<th valign='top'>Expires</th>
     					<th valign='top'>Total Cost</th>
     				</tr>
     				</thead>
     				<tbody>
     			";	
     		}     		
     		while($row = mysqli_fetch_array($data)) 
     		{     			
     			$mrr_classy="";
     			//if($row['active']==0)	$mrr_classy=" style='color:#dddddd;'";
     			
     			$linker1="<a".$mrr_classy." href='quote.php?id=".$row['id']."' target='_blank'>".$row['id']."</a>";
     			
     			$classy="";
     			if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     			 
     			$mrr_tab11.="
     				<tr>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'".$classy.">".$row['quote_name']."</td>
     					<td valign='top'".$classy.">".$row['quote_notes']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate']))."</td>
     					<td valign='top'".$classy.">".date("m/d/Y",strtotime($row['linedate_expires']))."</td>
     					<td valign='top'".$classy.">$".number_format($row['total_cost'],2)."</td>
     				</tr>
     			";    			
       		}
     		
     		if($mn > 0)
     		{
     			$mrr_tab11.="
     				</tbody>
     				</table>
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>
     			";	
     		}
               
               
               $mn=0;
     		     //(select name_truck from trucks where maint_requests.ref_id=trucks.id and maint_requests.equip_type=58) as truck_namer,
                    //(select trailer_name from trailers where maint_requests.ref_id=trailers.id and maint_requests.equip_type=59) as trailer_namer,
               $sql = "
     			select 
     			    
                        
                        (select users.username from users where users.id=maint_requests.user_id) as user_namer,
     			    maint_requests.*	
     			from maint_requests
     			where maint_requests.deleted>=0
     				and (
     			        (select name_truck from trucks where maint_requests.ref_id=trucks.id and maint_requests.equip_type=58) like '%".sql_friendly($search_term)."%'
     			        or 
     			        (select trailer_name from trailers where maint_requests.ref_id=trailers.id and maint_requests.equip_type=59) like '%".sql_friendly($search_term)."%'
     				)	
     			order by maint_requests.id desc
     		";
               $data = simple_query($sql);
               $mn=mysqli_num_rows($data);
               if($mn > 0)
               {
                    $mrr_tab12.="
     				<div class='section_heading'>Maintenance Requests</div>
     				<table class='tablesorter' width='100%' border='0'>
     				<thead>
     				<tr>
     					<th valign='top'>ID</th>
     					<th valign='top'>Description</th>
     					<th valign='top'>Type</th>
     					<th valign='top'>Equipment</th>
     					<th valign='top'>Scheduled</th>
     					<th valign='top'>Completed</th>
                              <th valign='top' nowrap>Created By</th>
                              <th valign='top' nowrap>Created On</th>
     					<th valign='top'>Cost</th>
     				</tr>
     				</thead>
     				<tbody>
     			";
               }
               while($row = mysqli_fetch_array($data))
               {
                    $mrr_classy="";
                    //if($row['active']==0)	$mrr_classy=" style='color:#dddddd;'";
                    
                    $linker1="<a".$mrr_classy." href='maint.php?id=".$row['id']."' target='_blank'>".$row['id']."</a>";
                    
                    $classy="";
                    if($row['deleted'] > 0)		$classy=" class='deleted_item' style='color:#cc0000;'";
     
     
                    $e_type=$row['equip_type'];
                    $e_select=$row['ref_id'];
                    $main_desc=$row['maint_desc'];
                    //$req_active=$row['active'];
                    $schedule_date=$row['linedate_scheduled'];
                    $completed_date=$row['linedate_completed'];
                    //$down_time=$row['down_time_hours'];
                    $cost_est=$row['cost'];
                    //$odometer=$row['odometer_reading'];
     
                    //$recur_flag=$row['recur_flag'];
                    //$recur_days=$row['recur_days'];
                    //$recur_mileage=$row['recur_mileage'];
     
                    //$recur_ref=$row['recur_ref'];
                    $urgent=$row['urgent'];
     
                    $urgenter="";
                    //$recur_file="";
                    //if($recur_ref>0)		$recur_file="<a href='maint_recur.php?id=".$recur_ref."' target='_blank'>Edit</a>";
                    //if($recur_flag>0)		$recur_file="<span class='mrr_recur_styler'>Yes</span>";
                    if($urgent>0)			$urgenter="<span style='color:#CC0000;'><b>!!!</b></span> ";
     
                    $equip_type=get_option_name_by_id($e_type);
                    $name=identify_truck_trailer($e_type , $e_select, 1);
     
                    //$equip_local="";
                    //$equip_local=mrr_find_equip_current_location($e_type,$e_select);
                    
                    
                    $mrr_tab12.="
     				<tr>
     					<td valign='top'>".$linker1."</td>
     					<td valign='top'".$classy." width='400'>".$urgenter."".$main_desc."</td>
     					<td valign='top'".$classy.">".$equip_type."</td>
     					<td valign='top'".$classy.">".$name."</td>
     					<td valign='top'".$classy.">".($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "")."</td>
     					<td valign='top'".$classy.">".($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)): "")."</td>
     					<td valign='top'".$classy.">".$row['user_namer']."</td>
     					<td valign='top'".$classy.">".date("m/d/Y H:i", strtotime($row['linedate_added']))."</td>
     					<td valign='top'".$classy.">$".number_format($cost_est,2)."</td>
     				</tr>
     			";
               }
               
               if($mn > 0)
               {
                    $mrr_tab12.="
     				</tbody>
     				</table> 
     				<center><span class='deleted_item'>Note: Items in this style have been flagged Deleted.</span></center>    				
     			";   //
               }
          }		
		
		if(trim($mrr_tab1)!="")	$mrr_tab.=$mrr_tab1;		//loads
		if(trim($mrr_tab2)!="")	$mrr_tab.=$mrr_tab2;		//stops
		if(trim($mrr_tab3)!="")	$mrr_tab.=$mrr_tab3;		//dispatches
		if(trim($mrr_tab4)!="")	$mrr_tab.=$mrr_tab4;		//trailer drops
		if(trim($mrr_tab5)!="")	$mrr_tab.=$mrr_tab5;		//customers
		if(trim($mrr_tab6)!="")	$mrr_tab.=$mrr_tab6;		//contacts
		if(trim($mrr_tab7)!="")	$mrr_tab.=$mrr_tab7;		//trucks
		if(trim($mrr_tab8)!="")	$mrr_tab.=$mrr_tab8;		//trailers
		if(trim($mrr_tab9)!="")	$mrr_tab.=$mrr_tab9;		//drivers
		if(trim($mrr_tab10)!="")	$mrr_tab.=$mrr_tab10;		//notes
		if(trim($mrr_tab11)!="")	$mrr_tab.=$mrr_tab11;		//quotes
		if(trim($mrr_tab12)!="")	$mrr_tab.=$mrr_tab12;		//Maint Requests
		
		if(trim($mrr_tab)=="")	$mrr_tab="No results found for ".$test_search.".";
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Searching for ".$search_term." term in system";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");
		
	}	
	function mrr_snap_shot()
	{
		$label=trim($_POST['label']);
		$mode=trim($_POST['mode']);
		
		global $defaultsarray;
		
		$cur_month=date("m");		$cur_year=date("Y");
		
		$months[0]="";				$maxdays[0]=0;
		$months[1]="January";		$maxdays[1]=31;
		$months[2]="February";		$maxdays[2]=28;
		$months[3]="March";			$maxdays[3]=31;
		$months[4]="April";			$maxdays[4]=30;
		$months[5]="May";			$maxdays[5]=31;
		$months[6]="June";			$maxdays[6]=30;
		$months[7]="July";			$maxdays[7]=31;
		$months[8]="August";		$maxdays[8]=31;
		$months[9]="September";		$maxdays[9]=30;
		$months[10]="October";		$maxdays[10]=31;
		$months[11]="November";		$maxdays[11]=30;
		$months[12]="December";		$maxdays[12]=31;
	
		if($cur_year%4==0)			$maxdays[2]=29;			//simple leap year adjusted	
		
		$mrr_tab="
     				<div class='section_heading'>".$label."</div>
     				<table class='tablesorter' width='100%' border='0'>
     			";
     	
     	
     	if($mode=="bad_customers")
     	{
     		$mrr_tab.="     				
     				<thead>
     				<tr>
     					<th valign='top'>Customer</th>
     					<th valign='top'>Credit Limit</th>
     					<th valign='top'>Credit Hold</th>
     					<th valign='top'>DIRT BAGS!</th>
     					<th valign='top'>Slow Pays</th>
     					<th valign='top'>Bill Amount</th>
     					<th valign='top'>Stoplight Warning Notes</th>
     				</tr>
     				</thead>
     				<tbody>
     			";
     			
     			//and (load_handler.sicap_invoice_number='' or load_handler.sicap_invoice_number = NULL)
     		$sql = "
          			select customers.*,
          				(
          					select ifnull(sum(load_handler.actual_bill_customer),0) 
          						from load_handler 
          						where load_handler.customer_id = customers.id 
          							and load_handler.deleted = 0
          							
          				) as bill_amnt
          			
          			from customers
          			where customers.deleted=0
          				and (
          					customers.slow_pays>0 
          					or customers.credit_hold>0 
          					or customers.dirt_bags_flag>0
          					or customers.credit_limit!='0.00' 
          					or customers.stoplight_warn_notes!=''
          					)
          			order by customers.name_company asc
          		";	
          	$data = simple_query($sql);
          	while($row = mysqli_fetch_array($data)) 
          	{
          		$cust_id=$row['id'];
          		$cust_name=$row['name_company'];
          		
          		$shold="";	if($row['slow_pays'] > 0)		$shold="Yes";
          		$chold="";	if($row['credit_hold'] > 0)		$chold="Yes";
          		$dhold="";	if($row['dirt_bags_flag'] > 0)	$dhold="Yes";
          		
          		$climit=trim($row['credit_limit']);
          		if($climit=="")	$climit=0;
          		
          		$stoplight=trim($row['stoplight_warn_notes']);
          		
          		$mrr_tab.="
     				<tr>
     					<td valign='top' nowrap><a href='admin_customers.php?eid=".$cust_id."' target='_blank'>".$cust_name."</a></td>
     					<td valign='top' align='right'>$".number_format($climit,2)."</td>    
     					<td valign='top' align='right'>".$chold."</td>	
     					<td valign='top' align='right'>".$dhold."</td>
     					<td valign='top' align='right'>".$shold."</td>
     					<td valign='top' align='right'>$".number_format($row['bill_amnt'],2)."</td>   
     					<td valign='top'>".$stoplight."</td>					
     				</tr>
     			"; 
          		
          	}
          		
     	}
     	elseif($mode=="sales")
     	{
     		$mrr_tab="
     				<div class='section_heading'>".$label."</div>
     				<table width='100%' border='0'>
     				<tbody>
     				<tr>
     				<td valign='top'>
     				";
     		$mrr_tab1="";
     		$mrr_tab2="";
     		
     		$mrr_tab1.="<table class='tablesorter' width='100%' border='0'>     				
     				<thead>
     				<tr>
     					<th valign='top'><b>Dates</b></th>
     					<th valign='top' align='right'>Avg<br>MPG</th>
     					<th valign='top' align='right'>Gallons</th> 					
     					<th valign='top' align='right'>Loads</th>
     					<th valign='top' align='right'>Custs</th>     					
     					<th valign='top' align='right'>Days<br>Act</th>
     					<th valign='top' align='right'>Days<br>Run</th>    					
     					<th valign='top' align='right'>Inv</th>  
     					<th valign='top' align='right'>Not<br>Inv</th>
     					<th valign='top' align='right'>GP</th>
     				</tr>
     				</thead>
     				<tbody>
     			";
     				/*
     					<th valign='top' align='right'>Miles</th>
     					<th valign='top' align='right'>DH<br>Miles</th>
     				*/
     		$mrr_tab2.="<table class='tablesorter' width='100%' border='0'>     				
     				<thead>
     				<tr>
     					<th valign='top'><b>Dates</b></th>
     					<th valign='top' align='right'>CPG</th>
     					<th valign='top' align='right'>Fuel</th>
     					<th valign='top' align='right'>Variance</th>
     					<th valign='top' align='right'>DailyCost</th>
     					<th valign='top' align='right'>InvAmnt</th>   
     					<th valign='top' align='right'>NotInvAmnt</th>      					  					
     					<th valign='top' align='right'>Sales</th>
     					<th valign='top' align='right'>Cost</th>
     					<th valign='top' align='right'>Profit</th>
     				</tr>
     				</thead>
     				<tbody>
     			";
     			
     		
     		$months=6;
     		$this_month=$cur_month;
			$this_year=$cur_year;
			
     		for($i=0;$i < $months;$i++)
     		{          		
				$cmon=($cur_month - $i);
				$cyear=$this_year;
				if($cmon==0)			{	$cmon=12;			$cyear-=1;	}
				
				$sdater="".$cmon."/01/".$cyear."";				$edater="".$cmon."/".$maxdays[ $cmon ]."/".$cyear."";
				          		
          		$search_date_range = "
     				and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($sdater))." 00:00:00'
     				and load_handler.linedate_pickup_eta <= '".date("Y-m-d", strtotime($edater))." 23:59:59'
     			";
     			$sql = "
          			select load_handler.*,
          				customers.name_company,
          				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
          				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
          				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead
          			
          			from load_handler
          				left join customers on customers.id = load_handler.customer_id
          			where load_handler.deleted = 0
          				and customers.deleted = 0
          				
          				$search_date_range
          			order by load_handler.id
          		";	
          		$data = simple_query($sql);
          		
          		$counter = 0;		$amnt=0;
          		
          		$loads=0;			$load_array[0]=0;
          		$customers=0;		$cust_array[0]=0;
          		
          		$invoiced=0;		$invoiced_amnt=0;		//load_handler.invoice_number 
          		$not_invoiced=0;	$not_invoiced_amnt=0;
          		
          		$total_miles = 0;
          		$total_deadhead = 0;
          		$total_profit = 0;
          		$total_cost = 0;
          		$total_sales = 0;
          		$fuel_charge = 0;
          		
          		$last_load_id = 0;          		
          		while($row = mysqli_fetch_array($data)) 
          		{
          			$counter++;          			
          			$load_id=$row['id'];
          			
          			$customer_id=$row['customer_id'];
          			
          			$miles=$row['miles'];
          			$dh_miles=$row['miles_deadhead'];
          			
          			$fuel_rate=$row['actual_rate_fuel_surcharge'];
          			$avg_mpg=$defaultsarray['average_mpg'];
          			
          			$eta=date("M j, Y", strtotime($row['linedate_pickup_eta']));
          			
          			$invoice_number=$row['invoice_number'];
          			$billed=$row['actual_bill_customer'];
          			$cost=$row['actual_total_cost'];          			
          			$profit=$row['load_profit'];          			      			
          			
          			if($last_load_id != $load_id) 
          			{
          				$last_load_id = $load_id;
          				
          				$load_array[ $loads ]=$load_id;;
          				$loads++;
          				
          				for($x=0;$x < $customers; $x++)
          				{
          					$found=0;
          					if($cust_array[ $x ]== $customer_id)			$found=1;
          					if($found==0)
          					{
          						$cust_array[ $customers ]= $customer_id;	$customers++;	
          					}
          				}
          				          				
          				$total_miles += $miles;
          				$total_deadhead += $dh_miles;          				
          								
          				if($avg_mpg > 0)	$fuel_charge += ($miles + $dh_miles) * $fuel_rate / $avg_mpg;	
          				
          				if($invoice_number == '') 
          				{
          					$not_invoiced++;
          					$not_invoiced_amnt += $billed;
          				} 
          				else 
          				{
          					$invoiced++;
          					$invoiced_amnt += $billed;
          				}
          			}
          						
          			$total_profit += $billed - $cost;
          			$total_cost += $cost;
          			$total_sales += $billed;
          
          		}
          
          		$days_run = get_days_available(strtotime($sdater), strtotime($edater));
          		$days_actual = get_days_run(strtotime($sdater), strtotime($edater));
          		
          		$days_variance = $days_actual - $days_run['days_available_so_far'];
          		
          		$daily_cost = get_daily_cost();
          		$usage_difference = $daily_cost * $days_variance;
          		
          		$gross_profit = $total_profit + $usage_difference;
          		
          		$gallons_used=0;
          		if($avg_mpg > 0)		$gallons_used = ($total_miles + $total_deadhead) / $avg_mpg;
          		
          		$cost_per_gallon=0;
          		if($gallons_used > 0)	$cost_per_gallon = $fuel_charge / $gallons_used;
          		
          		$dc_res=mrr_get_daily_cost_from_truck_logs($sdater,$edater);
          		$mrr_cost_total=$dc_res['total'];
				$mrr_cost_cnt=$dc_res['num'];
				$mrr_cost_avg=$dc_res['avg'];
          		          		
          		$mrr_tab1.="
     				<tr>
     					<td valign='top' nowrap><b>".$sdater." - ".$edater."</b></td>
     					<td valign='top' align='right'>".number_format($avg_mpg,0)."</td>    
     					<td valign='top' align='right'>".number_format($gallons_used,0)."</td>	
     					<td valign='top' align='right'>".number_format($loads,0)."</td>
     					<td valign='top' align='right'>".number_format($customers,0)."</td>     									
     					<td valign='top' align='right'>".number_format($days_actual,0)."</td>
     					<td valign='top' align='right'>".number_format($days_run['days_available_so_far'],0)."</td>
     					<td valign='top' align='right'>".number_format($invoiced,0)."</td>
     					<td valign='top' align='right'>".number_format($not_invoiced,0)."</td>
     					<td valign='top' align='right'>$".number_format($gross_profit,2)."</td>  					
     				</tr>
     			"; 
     				/*
     					<td valign='top' align='right'>".number_format($total_miles,0)."</td>
     					<td valign='top' align='right'>".number_format($total_deadhead,0)."</td>
     				*/
     			
     			if($mrr_cost_avg > 0)		$daily_cost=$mrr_cost_avg;
     			
     			$mrr_tab2.="
     				<tr>
     					<td valign='top' nowrap><b>".$sdater." - ".$edater."</b></td>   
     					<td valign='top' align='right'>$".number_format($cost_per_gallon,2)."</td> 
     					<td valign='top' align='right'>$".number_format($fuel_charge,2)."</td>
     					<td valign='top' align='right'>$".number_format($days_variance,2)."</td> 
     					<td valign='top' align='right'><span title='Total=".$mrr_cost_total." Logs=".$mrr_cost_cnt." and Avg=".$mrr_cost_avg."'>$".number_format($daily_cost,2)."</span></td>
     					<td valign='top' align='right'>$".number_format($invoiced_amnt,2)."</td>  
     					<td valign='top' align='right'>$".number_format($not_invoiced_amnt,2)."</td> 
     					<td valign='top' align='right'>$".number_format($total_sales,2)."</td>
     					<td valign='top' align='right'>$".number_format($total_cost,2)."</td>
     					<td valign='top' align='right'>$".number_format($total_profit,2)."</td>    					
     				</tr>
     			"; 
     		}
     		$mrr_tab1.="</tbody></table>";
     		$mrr_tab2.="</tbody></table>";
     		
     		$mrr_tab.=$mrr_tab1."<div></div>".$mrr_tab2;
     	}
     	elseif($mode=="miles_run")
     	{
     		$mrr_tab.="     				
     				<thead>
     				<tr>
     					<th valign='top'>Dates</th>
     					<th valign='top'>Hours Worked</th>
     					<th valign='top'>Miles Hourly</th>
     					<th valign='top'>PCM Miles</th>
     					<th valign='top'>-</th>
     					<th valign='top'>Miles</th>
     					<th valign='top'>DeadHead</th>
     					<th valign='top'>Total</th>
     				</tr>
     				</thead>
     				<tbody>
     			";
     			
     		$months=6;
     		$this_month=$cur_month;
			$this_year=$cur_year;
			
     		for($i=0;$i < $months;$i++)
     		{          		
				$cmon=($cur_month - $i);
				$cyear=$this_year;
				if($cmon==0)			{	$cmon=12;			$cyear-=1;	}
				
				$sdater="".$cmon."/01/".$cyear."";				$edater="".$cmon."/".$maxdays[ $cmon ]."/".$cyear."";
				          		
          		$search_date_range = "
     				and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($sdater))." 00:00:00'
     				and load_handler.linedate_pickup_eta <= '".date("Y-m-d", strtotime($edater))." 23:59:59'
     			";
     			$sql = "
          			select load_handler.id,
          				(select ifnull(sum(trucks_log.hours_worked),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as hours_worked,
          				(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_hourly,
          				(select ifnull(sum(trucks_log.pcm_miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as pcm,
          				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
          				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead
          			
          			from load_handler
          			where load_handler.deleted = 0          				
          				$search_date_range
          			order by load_handler.id
          		";
               	$data = simple_query($sql);
               	
               	$tot_hours=0;
               	$hour_miles=0;
               	
               	$pcm_miles=0;
               	$tot_miles=0;               	
               	$tot_dh_miles=0;
               	
               	while($row = mysqli_fetch_array($data)) 
               	{
               		$tot_hours+=$row['hours_worked'];
               		$hour_miles+=$row['miles_hourly'];
               		
               		$pcm_miles+=$row['pcm'];
               		$tot_miles+=$row['miles'];
               		$tot_dh_miles+=$row['miles_deadhead'];
               	}
               	$gtot=$tot_miles+$tot_dh_miles;
               	
               	$mrr_tab.="
          				<tr>
          					<td valign='top' nowrap><b>".$sdater." - ".$edater."</b></td>
          					<td valign='top' align='right'>".number_format($tot_hours,2)."</td>	
          					<td valign='top' align='right'>".number_format($hour_miles,0)."</td>	
          					<td valign='top' align='right'>".number_format($pcm_miles,0)."</td>	
          					<td valign='top' align='right'>&nbsp;</td>
          					<td valign='top' align='right'>".number_format($tot_miles,0)."</td>	
          					<td valign='top' align='right'>".number_format($tot_dh_miles,0)."</td>	
          					<td valign='top' align='right'>".number_format($gtot,0)."</td>					
          				</tr>
          			"; 
               	
               }//end for loop
     	}
     	elseif($mode=="website_links")
     	{
     		$lab1="";					$sdater1="";				$edater1="";			$link_lab1="";
     		$lab2="";					$sdater2="";				$edater2="";			$link_lab2="";
     		$lab3="";					$sdater3="";				$edater3="";			$link_lab3="";
     		$lab4="";					$sdater4="";				$edater4="";			$link_lab4="";
     		$lab5="";					$sdater5="";				$edater5="";			$link_lab5="";
     		$lab6="";					$sdater6="";				$edater6="";			$link_lab6="";
     		
     		$cmon=$cur_month;
     		$cyear=$cur_year;
     		
     		$cmon=($cmon-1);					if($cmon==0)	{	$cyear-=1;	$cmon=12;	}
     		$lab1=$months[$cmon];								$link_lab1="".(int)$cmon."/".$cyear."";
     		$sdater1="".$cmon."_01_".$cyear."";					$edater1="".$cmon."_".$maxdays[ $cmon ]."_".$cyear."";
     		
     		$cmon=($cmon-1);					if($cmon==0)	{	$cyear-=1;	$cmon=12;	}
     		$lab2=$months[$cmon];								$link_lab2="".(int)$cmon."/".$cyear."";		
     		$sdater2="".$cmon."_01_".$cyear."";					$edater2="".$cmon."_".$maxdays[ $cmon ]."_".$cyear."";
     		
     		$cmon=($cmon-1);					if($cmon==0)	{	$cyear-=1;	$cmon=12;	}
     		$lab3=$months[$cmon];								$link_lab3="".(int)$cmon."/".$cyear."";		
     		$sdater3="".$cmon."_01_".$cyear."";					$edater3="".$cmon."_".$maxdays[ $cmon ]."_".$cyear."";
     		
     		$cmon=($cmon-1);					if($cmon==0)	{	$cyear-=1;	$cmon=12;	}
     		$lab4=$months[$cmon];								$link_lab4="".(int)$cmon."/".$cyear."";		
     		$sdater4="".$cmon."_01_".$cyear."";					$edater4="".$cmon."_".$maxdays[ $cmon ]."_".$cyear."";
     		
     		$cmon=($cmon-1);					if($cmon==0)	{	$cyear-=1;	$cmon=12;	}
     		$lab5=$months[$cmon];								$link_lab5="".(int)$cmon."/".$cyear."";		
     		$sdater5="".$cmon."_01_".$cyear."";					$edater5="".$cmon."_".$maxdays[ $cmon ]."_".$cyear."";
     		
     		$cmon=($cmon-1);					if($cmon==0)	{	$cyear-=1;	$cmon=12;	}
     		$lab6=$months[$cmon];								$link_lab6="".(int)$cmon."/".$cyear."";		
     		$sdater6="".$cmon."_01_".$cyear."";					$edater6="".$cmon."_".$maxdays[ $cmon ]."_".$cyear."";
     		     		
     		$mrr_tab.="     				
     				<thead>
     				<tr>
     					<th valign='top'>Link</th>
     					<th valign='top'>".$lab1."</th>
     					<th valign='top'>".$lab2."</th>
     					<th valign='top'>".$lab3."</th>
     					<th valign='top'>".$lab4."</th>
     					<th valign='top'>".$lab5."</th>
     					<th valign='top'>".$lab6."</th>
     				</tr>
     				</thead>
     				<tbody>
     			";
     		
     		$mrr_tab.="
     				<tr>
     					<td valign='top'><b>Comparison Report</b></td>
     					<td valign='top'><a href='report_comparison.php?date_from=".$sdater1."&date_to=".$edater1."' target='_blank'>".$link_lab1."</a></td>
     					<td valign='top'><a href='report_comparison.php?date_from=".$sdater2."&date_to=".$edater2."' target='_blank'>".$link_lab2."</a></td>
     					<td valign='top'><a href='report_comparison.php?date_from=".$sdater3."&date_to=".$edater3."' target='_blank'>".$link_lab3."</a></td>
     					<td valign='top'><a href='report_comparison.php?date_from=".$sdater4."&date_to=".$edater4."' target='_blank'>".$link_lab4."</a></td>
     					<td valign='top'><a href='report_comparison.php?date_from=".$sdater5."&date_to=".$edater5."' target='_blank'>".$link_lab5."</a></td>
     					<td valign='top'><a href='report_comparison.php?date_from=".$sdater6."&date_to=".$edater6."' target='_blank'>".$link_lab6."</a></td>
     				</tr>
     			";  			
     		$mrr_tab.="
     				<tr>
     					<td valign='top'><b>Sales (by Load) Report</b></td>
     					<td valign='top'><a href='report_sales_by_load.php?date_from=".$sdater1."&date_to=".$edater1."' target='_blank'>".$link_lab1."</a></td>
     					<td valign='top'><a href='report_sales_by_load.php?date_from=".$sdater2."&date_to=".$edater2."' target='_blank'>".$link_lab2."</a></td>
     					<td valign='top'><a href='report_sales_by_load.php?date_from=".$sdater3."&date_to=".$edater3."' target='_blank'>".$link_lab3."</a></td>
     					<td valign='top'><a href='report_sales_by_load.php?date_from=".$sdater4."&date_to=".$edater4."' target='_blank'>".$link_lab4."</a></td>
     					<td valign='top'><a href='report_sales_by_load.php?date_from=".$sdater5."&date_to=".$edater5."' target='_blank'>".$link_lab5."</a></td>
     					<td valign='top'><a href='report_sales_by_load.php?date_from=".$sdater6."&date_to=".$edater6."' target='_blank'>".$link_lab6."</a></td>
     				</tr>
     			"; 
     		$mrr_tab.="
     				<tr>
     					<td valign='top'><b>Payroll (for Bills) Report</b></td>
     					<td valign='top'><a href='report_payroll_for_bills.php?date_from=".$sdater1."&date_to=".$edater1."' target='_blank'>".$link_lab1."</a></td>
     					<td valign='top'><a href='report_payroll_for_bills.php?date_from=".$sdater2."&date_to=".$edater2."' target='_blank'>".$link_lab2."</a></td>
     					<td valign='top'><a href='report_payroll_for_bills.php?date_from=".$sdater3."&date_to=".$edater3."' target='_blank'>".$link_lab3."</a></td>
     					<td valign='top'><a href='report_payroll_for_bills.php?date_from=".$sdater4."&date_to=".$edater4."' target='_blank'>".$link_lab4."</a></td>
     					<td valign='top'><a href='report_payroll_for_bills.php?date_from=".$sdater5."&date_to=".$edater5."' target='_blank'>".$link_lab5."</a></td>
     					<td valign='top'><a href='report_payroll_for_bills.php?date_from=".$sdater6."&date_to=".$edater6."' target='_blank'>".$link_lab6."</a></td>
     				</tr>
     			"; 
     	}	
     			
		$mrr_tab.="
     				</tbody>
     				</table>
     			";	
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Snapshot pulled for ".$label." (mode ".$mode.")";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	
	}
	
	function mrr_verify_item_name()
	{
		$namer=trim($_POST['name']);
		$mode=$_POST['mode'];
		$id=$_POST['id'];		
		
		//global $defaultsarray;
		$moder[0]="User";		$tabler[0]="users";		$fielder[0]="username";										$href[0]="admin_users.php?eid=";
		$moder[1]="Driver";		$tabler[1]="drivers";	$fielder[1]="CONCAT(name_driver_first,' ',name_driver_last)";		$href[1]="admin_drivers.php?id=";
		$moder[2]="Customer";	$tabler[2]="customers";	$fielder[2]="name_company";									$href[2]="admin_customers.php?eid=";
		$moder[3]="Truck";		$tabler[3]="trucks";	$fielder[3]="name_truck";									$href[3]="admin_trucks.php?id=";
		$moder[4]="Trailer";	$tabler[4]="trailers";	$fielder[4]="trailer_name";									$href[4]="admin_trailers.php?id=";
		
		$mrr_tab="
			<table cellpadding='0' cellspacing='0' border='0' width='300'>
			<tr>
				<td valign='top' colspan='2'><center><span style='color:red;'><b>This name is already in use:</b></span></center></td>
			</tr>
			<tr>
				<td valign='top' align='left' width='100'><b>ID</b></td>
				<td valign='top' align='left'><b>Name</b></td>
			</tr>
		";
		
		$sql="
			select id,
				".$fielder[ $mode ]." as mrr_name 
			from ".$tabler[ $mode ]." 
			where ".$fielder[ $mode ]."='".sql_friendly($namer)."'
				and id!='".sql_friendly($id)."'
				and deleted='0'
			order by id asc
		";
		$data = simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$mrr_tab.="
				<tr>
					<td valign='top' align='left'><a href='".$href[ $mode ]."".$row['id']."'>".$row['id']."</a></td>
					<td valign='top' align='left'>".$row['mrr_name']."</td>
				</tr>	
			";
		}
		$mrr_tab.="</table>";
		
		if(mysqli_num_rows($data)==0)		$mrr_tab="";
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log["notes"]="Test for duplicate ".$moder[ $mode ]." for ".$namer." (and not ID=".$id.")";		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");		
	}
	
	
	//added Aug 2012...truck tracking
	function mrr_truck_tracking_report()
	{
		$mrr_tab="";
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$report_type=$_POST['report_type'];
			
		
		$date_range_tracking=" and truck_tracking.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
		$date_range_message=" and truck_tracking_messages.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_messages.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
		$date_range_msg_history=" and truck_tracking_msg_history.linedate_created>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_msg_history.linedate_created<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
			
		$label="All Trucks";			$adder="";
		if($truck_id>0)				$adder=" and id='".sql_friendly($truck_id)."'";	
		
		$decode_heading[0]="N"; 			$decode_heading[1]="NE";    		$decode_heading[2]="E";     		$decode_heading[3]="SE";
     	$decode_heading[4]="S"; 			$decode_heading[5]="SW";     		$decode_heading[6]="W";     		$decode_heading[7]="NW";
     	
     	$decode_fix[0]="Normal GPS"; 		$decode_fix[1]="Auto Position";	$decode_fix[2]="Vehicle Start";	$decode_fix[3]="Vehicle Stop";
     	
     	$decode_ignition[0]="Off";   		$decode_ignition[1]="On";
				
		$tab="";
		$tab.="<table class='admin_menu3' width='100%'>
				<tr>
					<td valign='top'>&nbsp;</td>
					<td valign='top'><b>Date</b></td>
					<td valign='top' align='right' align='right'><b>MPH</b></td>
					<td valign='top' align='right' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right' colspan='3><b>GPS</b></td>
					<td valign='top' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right' colspan='3'><b>GPS</b></td>
					<td valign='top' align='right' colspan='4'><b>PerformX</b></td>
				</tr>
				<tr>
					<td valign='top'><b>Truck</b></td>
					<td valign='top'><b>Time</b></td>
					<td valign='top' align='right'><b>Speed</b></td>
					<td valign='top' align='right'><b>Dir</b></td>
					<td valign='top' align='right'><b>Quality</b></td>
					<td valign='top' align='right'><b>Latitude</b></td>
					<td valign='top' align='right'><b>Longitude</b></td>
					<td valign='top'><b>Location</b></td>
					<td valign='top'><b>Fix</b></td>
					<td valign='top'><b>Ignition</b></td>
					<td valign='top' align='right'><b>Odometer</b></td>
					<td valign='top' align='right'><b>Rolling Odom</b></td>
					<td valign='top' align='right'><b>Odom</b></td>
					<td valign='top' align='right'><b>Fuel</b></td>
					<td valign='top' align='right'><b>Speed </b></td>
					<td valign='top' align='right'><b>Idle</b></td>
				</tr>
		";
				
		$sql = "
     		select trucks.*
     		from trucks
     		where trucks.deleted = 0
     			and trucks.peoplenet_tracking=1
     			".$adder."     			
     		order by trucks.name_truck asc
     	";		
     	$data = simple_query($sql);
     	$mn=mysqli_num_rows($data);
     	while($row = mysqli_fetch_array($data))
     	{
     		if($truck_id>0)	$label="".trim($row['name_truck'])."";
     		
     		$myname="".trim($row['name_truck'])."";
     		$myid=$row['id'];
     		//$alink="<a href='peoplenet_interface.php?truck_id=".$myid."&service_type=loc_onetruck'>".$myname."</a>";
     			
     		$tcntr=0;
     		
     		if($report_type==0 || $report_type==1)
     		{
          		$sql2 = "
               		select distinct(truck_tracking.linedate) as unique_stamp,
               			truck_tracking.*
               		from ".mrr_find_log_database_name()."truck_tracking
               		where truck_tracking.truck_id='".sql_friendly($myid) ."'
               			".$date_range_tracking."
               		group by truck_tracking.linedate desc
               	";
          		$data2 = simple_query($sql2);
          		$mn2=mysqli_num_rows($data2);	
          		while($row2 = mysqli_fetch_array($data2))
          		{     			     			
          			$use_truck_name="";
          			$blink="";
          			
          			if($tcntr==0)		$use_truck_name=$myname;     			
          			
          			if($row2['linedate']!="0000-00-00 00:00:00")		$blink="".date("m/d/Y H:i:s",strtotime($row2['linedate']))."";	
          			
          			$cmode="black";
          			if($row2['truck_speed']==0)									$cmode="teal";			
          			elseif($row2['truck_speed'] > 0 && $row2['truck_speed'] <=30)		$cmode="orange";	//; text-decoration:blink
          			elseif($row2['truck_speed'] > 30 && $row2['truck_speed'] <=70)		$cmode="green";
          			elseif($row2['truck_speed'] > 70)								$cmode="red";		//; text-decoration:blink
          			
          			$mrr_res=mrr_find_truck_driver_by_date($myid,$row2['linedate']);	
          			     			
          			$tab.="<tr>
               						<td valign='top'>".$use_truck_name."</td>
               						<td valign='top'>".$blink."</td>
               						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$row2['truck_speed']."</b></span></td>
               						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$decode_heading[ $row2['truck_heading'] ]."</b></span></td>
               						<td valign='top' align='right'>".$row2['gps_quality']."</td>
               						<td valign='top' align='right'>".$row2['latitude']."</td>
               						<td valign='top' align='right'>".$row2['longitude']."</td>
               						<td valign='top'> <span style='color:".$cmode.";'><b>".$row2['location']."</b></span></td>
               						<td valign='top'>".$decode_fix[ $row2['fix_type'] ]."</td>
               						<td valign='top'>".$decode_ignition[ $row2['ignition'] ]."</td>
               						<td valign='top' align='right'>".$row2['gps_odometer']."</td>
               						<td valign='top' align='right'>".$row2['gps_rolling_odometer']."</td>
               						<td valign='top' align='right'>".$row2['performx_odometer']."</td>
               						<td valign='top' align='right'>".$row2['performx_fuel']."</td>
               						<td valign='top' align='right'>".$row2['performx_speed']."</td>
               						<td valign='top' align='right'>".$row2['performx_idle']."</td>
          						<tr>";
          						
          			$travel_plan="";
          			if($mrr_res['dispatch_id'] > 0)		$travel_plan="Delivery:".$mrr_res['origin'].", ".$mrr_res['origin_state']." to ".$mrr_res['destination'].", ".$mrr_res['destination_state']."";	
          			
          			if($mrr_res['load_id'] > 0 && $mrr_res['dispatch_id'] > 0)
          			{					
               			$tab.="<tr>
                    						<td valign='top'></td>
                    						<td valign='top'>
                    								<a href='manage_load.php?load_id=".$mrr_res['load_id']."' target='_blank'>(".$mrr_res['load_id'].")</a> 
                    								<a href='add_entry_truck.php?load_id=".$mrr_res['load_id']."&id=".$mrr_res['dispatch_id']."' target='_blank'>".$mrr_res['dispatch_id']."</a>
                    						</td>
                    						<td valign='top' align='right'><span style='color:".$cmode.";'><b><a href='admin_trailers.php?id=".$mrr_res['trailer_id']."' target='_blank'>".$mrr_res['trailer_name']."</a></b></span></td>
                    						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$mrr_res['trailer']."</b></span></td>
                    						<td valign='top' align='right'></td>
                    						<td valign='top' align='right'><a href='admin_drivers.php?id=".$mrr_res['driver_id']."' target='_blank'>".$mrr_res['driver_name']."</a></td>
                    						<td valign='top' align='right'><a href='admin_drivers.php?id=".$mrr_res['driver2_id']."' target='_blank'>".$mrr_res['driver2_name']."</a></td>
                    						<td valign='top'> <span style='color:".$cmode.";'><b>".$travel_plan."</b></span></td>
                    						<td valign='top'></td>
                    						<td valign='top'></td>
                    						<td valign='top' align='right'></td>
                    						<td valign='top' align='right'></td>
                    						<td valign='top' align='right'></td>
                    						<td valign='top' align='right'></td>
                    						<td valign='top' align='right'></td>
                    						<td valign='top' align='right'></td>
               						<tr>";			
          			}			
          						
          			$tcntr++;				
          		}
          	}
          	if($report_type==0 || $report_type==2 || $report_type==4)
          	{	//messages pulled from packets
          		$tab.=mrr_get_messages_sent_by_truck($date_from, $date_to, $myid, $myname,0);
     		}
     		if($report_type==0 || $report_type==3 || $report_type==4)
     		{	//send and stored messages from Peoplenet Interface
          		$tab.=mrr_get_messages_sent_out_to_truck($date_from, $date_to, $myid, "",0);
     		}
     	}
     	$test_truck=1520428;
     	if($test_truck>0 && ($truck_id==1520428 || $truck_id==0))
     	{
     		if($truck_id>0)	$label="".trim($test_truck)."";
     		
     		$myname="".trim($test_truck)."";
     		$myid=$test_truck;
     		//$alink="<a href='peoplenet_interface.php?truck_id=".$myid."&service_type=loc_onetruck'>".$myname."</a>";
     			
     		$tcntr=0;
     		
     		if($report_type==0 || $report_type==1)
     		{          		
          		$sql2 = "
               		select distinct(truck_tracking.linedate) as unique_stamp,
               			truck_tracking.*
               		from ".mrr_find_log_database_name()."truck_tracking
               		where truck_tracking.truck_id='".sql_friendly($myid) ."'
               			".$date_range_tracking."
               		group by truck_tracking.linedate desc
               	";
          		$data2 = simple_query($sql2);
          		$mn2=mysqli_num_rows($data2);	
          		while($row2 = mysqli_fetch_array($data2))
          		{     			     			
          			$use_truck_name="";
          			$blink="";
          			
          			if($tcntr==0)		$use_truck_name=$myname;     			
          			
          			if($row2['linedate']!="0000-00-00 00:00:00")		$blink="".date("m/d/Y H:i:s",strtotime($row2['linedate']))."";	
          			
          			$cmode="black";
          			if($row2['truck_speed']==0)									$cmode="teal";			
          			elseif($row2['truck_speed'] > 0 && $row2['truck_speed'] <=30)		$cmode="orange";	//; text-decoration:blink
          			elseif($row2['truck_speed'] > 30 && $row2['truck_speed'] <=70)		$cmode="green";
          			elseif($row2['truck_speed'] > 70)								$cmode="red";		//; text-decoration:blink
          			     			
          			$tab.="<tr>
               						<td valign='top'>".$use_truck_name."</td>
               						<td valign='top'>".$blink."</td>
               						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$row2['truck_speed']."</b></span></td>
               						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$decode_heading[ $row2['truck_heading'] ]."</b></span></td>
               						<td valign='top' align='right'>".$row2['gps_quality']."</td>
               						<td valign='top' align='right'>".$row2['latitude']."</td>
               						<td valign='top' align='right'>".$row2['longitude']."</td>
               						<td valign='top'> <span style='color:".$cmode.";'><b>".$row2['location']."</b></span></td>
               						<td valign='top'>".$decode_fix[ $row2['fix_type'] ]."</td>
               						<td valign='top'>".$decode_ignition[ $row2['ignition'] ]."</td>
               						<td valign='top' align='right'>".$row2['gps_odometer']."</td>
               						<td valign='top' align='right'>".$row2['gps_rolling_odometer']."</td>
               						<td valign='top' align='right'>".$row2['performx_odometer']."</td>
               						<td valign='top' align='right'>".$row2['performx_fuel']."</td>
               						<td valign='top' align='right'>".$row2['performx_speed']."</td>
               						<td valign='top' align='right'>".$row2['performx_idle']."</td>
          						<tr>";
          			$tcntr++;				
          		}
     		}     		
     		if($report_type==0 || $report_type==2 || $report_type==4)
          	{	//messages pulled from packets
          		$tab.=mrr_get_messages_sent_by_truck($date_from, $date_to, $myid, $myname,0);
     		}
     		if($report_type==0 || $report_type==3 || $report_type==4)
     		{	//send and stored messages from Peoplenet Interface
          		$tab.=mrr_get_messages_sent_out_to_truck($date_from, $date_to, $myid, "",0);
     		}     		
     	}
     	
		$tab.="</table>";
		
		$mrr_tab.="<div class='section_heading'>Report Results for ".$label." From ".$date_from." To  ".$date_to."</div>";
		$mrr_tab.=$tab;
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");		
	}
	
	function mrr_truck_tracking_messages_received()
	{
		$mrr_tab="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$truck_name=$_POST['truck_name'];
		$limit=$_POST['limit'];
		$archived=$_POST['archived'];
		
		$mrr_tab=mrr_get_messages_sent_out_to_truck($date_from, $date_to, $truck_id, $truck_name, $limit, $archived);
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");
	}
	function mrr_truck_tracking_dispatches_sent()
	{
		$mrr_tab="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$truck_name=$_POST['truck_name'];
		$limit=$_POST['limit'];
		$archived=$_POST['archived'];
		$mode=0;
		if(isset($_POST['display_mode']))		$mode=$_POST['display_mode'];
		
		if($truck_id > 0)
		{
			$mrr_tab=mrr_get_past_dispatches_sent_by_truck($date_from, $date_to, $truck_id, $truck_name, $limit, $archived, $mode);	
		}
			
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	
	}
	function mrr_truck_tracking_equip_messages_sent()
	{
		$truck_id=$_POST['truck_id'];	
		$trailer_id=$_POST['trailer_id'];		
		
		global $defaultsarray;
		$extreme_alerts = (int) $defaultsarray['extreme_maint_alert'];
		$extreme_limit = (int) $defaultsarray['extreme_maint_alert_lim'];
		$ext_notes="";
		$ext_skips=" and created_by_user_id!=23";
		$ext_hdr="
			<p>
				<div style='color:#CC0000; background-color:#AAAAAA; border:1px inset #000000; padding:20px; margin:20px;'>
					<b>WARNING: Failure to read and use this unit without regarding the MR on it could get you TERMINATED!!!</b>
					<br>
					It is important for the working of this company for the truck and trailer units to be kept in working order.  If they are NOT, and need Maintenance, it is important that we make the effort to get them repaired... 
					for the good of the truck, the trailer, the driver, the dispatch, the load, the customer, the system, me, and you (and your job).
					<br>
					Please make sure you review the maintenance request(s) for this unit and <b>DO NOT SEND</b> a malfunctioning truck or trailer out on the road that desperately needs repairs, especailly if <b>URGENT</b>.
					So, do not add it to another dispatch until it is repaired or replaced by a safe and working unit.  
				</div>
			</p>
		";
				
		$truck_name="";
		$trailer_name="";
		
		$html="";
		
		if($truck_id > 0)
		{
			$sql = "
				select name_truck				
				from trucks
				where id='".sql_friendly($truck_id)."'
			";
			$data = simple_query($sql);
			if($row = mysqli_fetch_array($data))
			{
				$truck_name=trim($row['name_truck']);	
			}
			
			//get unread messages from this truck...Easy since the truck_id is already in the system.
			/*
			$sql3 = "
          		select truck_tracking_msg_history.*
          		from ".mrr_find_log_database_name()."truck_tracking_msg_history
          		where archived='0'
          			and linedate_read='0000-00-00 00:00:00'
          			and user_id_read='0'
          			and LOCATE('Warning: Dispatch',msg_text)=0
          			and truck_id='".sql_friendly($truck_id)."'
          		order by id desc
          	";
     		$data3 = simple_query($sql3);
			while($row3 = mysqli_fetch_array($data3))
			{
				$html.="<br><b>Message ".$row3['id'].":</b> ".date("m/d/Y H:i",strtotime($row3['linedate_created']))."<br>".$row3['msg_text']."";
				$sqlu="
					update ".mrr_find_log_database_name()."truck_tracking_msg_history set
						user_id_read='".sql_friendly($_SESSION['user_id'])."',
						linedate_read=NOW()
					where id='".sql_friendly($row3['id'])."'					
				";
				simple_query($sqlu);
			}	
			*/
			
			//also get maint request(s) if any exist...and are still active/not completed.
			$sql3 = "
          		select *
          		from maint_requests
          		where deleted=0 
          			and active>0 
          			and equip_type='58' 
          			and ref_id='".sql_friendly($truck_id)."'
          			and linedate_completed='0000-00-00 00:00:00'
          		order by id desc
          	";
     		$data3 = simple_query($sql3);
			while($row3 = mysqli_fetch_array($data3))
			{
				$html.="
					<br><b>Maint Request ".$row3['id'].":</b> ".date("m/d/Y H:i",strtotime($row3['linedate_added']))." -- Scheduled ".($row3['linedate_scheduled']!='0000-00-00 00:00:00' ? date("m/d/Y H:i",strtotime($row3['linedate_scheduled'])) : "N/A")."
				 	<br>".$row3['maint_desc']."
				 ";
				 
				 $user_name=mrr_peoplenet_pull_quick_username($_SESSION['user_id']);
				 
				 $sqlu="
					insert into notes_main
						(id,
						note,
						deleted,
						linedate_added,
						created_by_user_id,
						note_type_id,
						xref_id,
						access_level)
					values
						(NULL,
						'Maint Request Prompt Noticed by ".$user_name."',
						0,
						NOW(),
						'".sql_friendly($_SESSION['user_id'])."',
						10,
						'".sql_friendly($row3['id'])."',
						100)					
				";
				simple_query($sqlu);
				
				if($extreme_alerts > 0)
				{
					$ext_notes="".$ext_hdr."<br><b>The following Users have read this Truck MR Alert:</b>";
					
					//,(select username from users where users.id=notes_main.created_by_user_id) as user_name
					$sql4 = "
          				select notes_main.*
          				from notes_main
          				where notes_main.deleted=0 
          					and notes_main.note_type_id='10'
          					and notes_main.xref_id='".sql_friendly($row3['id'])."'
          					".$ext_skips."
          				order by notes_main.id desc
          				limit ".$extreme_limit."
          			";
     				$data4 = simple_query($sql4);
					while($row4 = mysqli_fetch_array($data4))
					{
						$ext_notes.="<br>".date("m/d/Y H:i",strtotime($row4['linedate_added']))." -- ".trim($row4['note']).".";					
					}					
				}
			}					
		}
		if($trailer_id > 0)
		{
			$sql = "
				select trailer_name				
				from trailers
				where id='".sql_friendly($trailer_id)."'
			";
			$data = simple_query($sql);
			if($row = mysqli_fetch_array($data))
			{
				$trailer_name=trim($row['trailer_name']);	
			}
			
			//get unread messages from this trailer...Not So Easy since the trailer_id is NOT in the message.  Must be pulled via text.
			/*
			$sql3 = "
          		select truck_tracking_msg_history.*
          		from ".mrr_find_log_database_name()."truck_tracking_msg_history
          		where archived='0'
          			and linedate_read='0000-00-00 00:00:00'
          			and user_id_read='0'
          			and LOCATE('Warning: Dispatch',msg_text)=0
          			and msg_text like '".sql_friendly("Trl ".$trailer_name." maint.")."%'
          		order by id desc
          	";
     		$data3 = simple_query($sql3);
			while($row3 = mysqli_fetch_array($data3))
			{
				
				$html.="<br><b>Message ".$row3['id'].":</b> ".date("m/d/Y H:i",strtotime($row3['linedate_created']))." -- (Sent From Truck ".$row3['truck_name'].")<br>".$row3['msg_text']."";
				$sqlu="
					update ".mrr_find_log_database_name()."truck_tracking_msg_history set
						user_id_read='".sql_friendly($_SESSION['user_id'])."',
						linedate_read=NOW()
					where id='".sql_friendly($row3['id'])."'					
				";
				simple_query($sqlu);
				
			}
			*/		
				
			//also get maint request(s) if any exist...and are still active/not completed.
			$sql3 = "
          		select *
          		from maint_requests
          		where deleted=0 
          			and active>0 
          			and equip_type='59' 
          			and ref_id='".sql_friendly($trailer_id)."'
          			and linedate_completed='0000-00-00 00:00:00'
          		order by id desc
          	";
     		$data3 = simple_query($sql3);
			while($row3 = mysqli_fetch_array($data3))
			{
				$html.="
					<br><b>Maint Request ".$row3['id'].":</b> ".date("m/d/Y H:i",strtotime($row3['linedate_added']))." -- Scheduled ".($row3['linedate_scheduled']!='0000-00-00 00:00:00' ? date("m/d/Y H:i",strtotime($row3['linedate_scheduled'])) : "N/A")."
				 	<br>".$row3['maint_desc']."
				 ";
				 
				 $user_name=mrr_peoplenet_pull_quick_username($_SESSION['user_id']);
				 
				 $sqlu="
					insert into notes_main
						(id,
						note,
						deleted,
						linedate_added,
						created_by_user_id,
						note_type_id,
						xref_id,
						access_level)
					values
						(NULL,
						'Maint Request Prompt Noticed by ".$user_name."',
						0,
						NOW(),
						'".sql_friendly($_SESSION['user_id'])."',
						10,
						'".sql_friendly($row3['id'])."',
						100)					
				";
				simple_query($sqlu);
				
				if($extreme_alerts > 0)
				{
					$ext_notes="".$ext_hdr."<br><b>The following Users have read this Trailer MR Alert:</b>";
					
					//,(select username from users where users.id=notes_main.created_by_user_id) as user_name
					$sql4 = "
          				select notes_main.*
          				from notes_main
          				where notes_main.deleted=0 
          					and notes_main.note_type_id='10'
          					and notes_main.xref_id='".sql_friendly($row3['id'])."'
          					".$ext_skips."
          				order by notes_main.id desc
          				limit ".$extreme_limit."
          			";
     				$data4 = simple_query($sql4);
					while($row4 = mysqli_fetch_array($data4))
					{
						$ext_notes.="<br>".date("m/d/Y H:i",strtotime($row4['linedate_added']))." -- ".trim($row4['note']).".";					
					}	
				}
			}
		}
		$mrr_tab="";		
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' || 1==1)	
		{
			$mrr_tab="<span class='alert'><b>Maint Request(s) for ".$truck_name."".$trailer_name.":</b></span><br>".$html."";		//Unread Message(s)
						
			if(trim($html)!="")		$mrr_tab.=$ext_notes;
		}
		if(trim($html)=="")		$mrr_tab="";	
				
		display_xml_response("<rslt>1</rslt><truck><![CDATA[".$truck_id."=".$truck_name."]]></truck><trailer><![CDATA[".$trailer_id."=".$trailer_name."]]></trailer><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");
	}
	function mrr_truck_tracking_messages_sent()
	{
		$mrr_tab="";
		$mrr_unread="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$truck_name=$_POST['truck_name'];
		$limit=$_POST['limit'];
		$archived=$_POST['archived'];
		$mode=0;
		if(isset($_POST['display_mode']))		$mode=$_POST['display_mode'];
		
		if($truck_id==0)
		{
			$mrr_unread=mrr_get_unread_messages_sent_by_all_trucks(0,$mode);	
			$mrr_tab="";
		}
		else
		{
			$mrr_unread="";
			$mrr_tab=mrr_get_messages_sent_by_truck($date_from, $date_to, $truck_id, $truck_name, $limit, $archived, $mode);	
		}
		
		//<img src='/images/note_msg.png' border='0' alt='Reply' width='12' height='16' onClick=\"pn_msg_box_mini_reply(".$rowx['trucks_log_id'].",".$rowx['load_handler_id'].",".$rowx['truckid'].",'".$mydate." 00:00:00');\">
     	//<div id='pn_note_mini_holder_".$rowx['trucks_log_id']."'></div>    
			
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab><mrrUnread><![CDATA[".$mrr_unread."]]></mrrUnread>");
	}
	function mrr_truck_tracking_geofencing_report()
	{
		$mrr_tab="";
		
		//$mrr_tab=mrr_pull_all_active_geofencing_rows(1);
		//$mrr_tab=mrr_pull_all_active_geofencing_rows_alt1(1);
		
		//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')	
		$mrr_tab=mrr_pull_all_active_geofencing_rows_alt_geotab(1);	
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");
	}
	function mrr_truck_tracking_messages_mark_read()
	{
		$msg_id=$_POST['message_id'];	
		$user_id=$_POST['user_id'];
			
		$sql = "
			update ".mrr_find_log_database_name()."truck_tracking_msg_history set
				user_id_read='".sql_friendly($user_id)."',
				linedate_read=NOW()				
			where id='".sql_friendly($msg_id)."'
				and user_id_read='0'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");	
	}
	
	
	
	function mrr_geotab_dispatches_sent()
	{
		$mrr_tab="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$truck_name=$_POST['truck_name'];
		$limit=$_POST['limit'];
		$archived=$_POST['archived'];
		$mode=0;
		if(isset($_POST['display_mode']))		$mode=$_POST['display_mode'];
		
		if($truck_id > 0)
		{
			$mrr_tab=mrr_get_geotab_dispatches_sent_to_truck($date_from, $date_to, $truck_id, $truck_name, $limit, $archived);	
		}
			
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");	
	}
	function mrr_geotab_messages_sent()
	{
		$mrr_tab="";
		$mrr_unread="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$truck_name=$_POST['truck_name'];
		$limit=$_POST['limit'];
		$archived=$_POST['archived'];
		$mode=0;
		if(isset($_POST['display_mode']))		$mode=$_POST['display_mode'];
		
		if($truck_id==0)
		{
			$mrr_unread=mrr_get_geotab_unread_messages_sent_by_all_trucks(0,$mode);	
			$mrr_tab="";
		}
		else
		{
			$mrr_unread="";
			$mrr_tab=mrr_get_geotab_messages_sent_by_truck($date_from, $date_to, $truck_id, $truck_name, $limit, $archived, $mode);	
		}
				
		//<img src='/images/note_msg.png' border='0' alt='Reply' width='12' height='16' onClick=\"pn_msg_box_mini_reply(".$rowx['trucks_log_id'].",".$rowx['load_handler_id'].",".$rowx['truckid'].",'".$mydate." 00:00:00');\">
     	//<div id='pn_note_mini_holder_".$rowx['trucks_log_id']."'></div>    
			
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab><mrrUnread><![CDATA[".$mrr_unread."]]></mrrUnread>");
	}
	function mrr_geotab_messages_received()
	{
		$mrr_tab="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		$truck_name=$_POST['truck_name'];
		$limit=$_POST['limit'];
		$archived=$_POST['archived'];
		$mode=0;
		if(isset($_POST['display_mode']))		$mode=$_POST['display_mode'];
		
		$mrr_tab=mrr_get_geotab_messages_sent_to_truck_true($date_from, $date_to, $truck_id, $truck_name, $limit, $archived,$mode);
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");
	}
	function mrr_geotab_messages_mark_read()
	{
		$msg_id=$_POST['message_id'];	
		$user_id=$_POST['user_id'];
			
		$sql = "
			update ".mrr_find_log_database_name()."geotab_messages_received set
				read_user_id='".sql_friendly($user_id)."',
				linedate_read=NOW()				
			where id='".sql_friendly($msg_id)."'
				and read_user_id='0'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");	
	}
	
	function mrr_equipment_special_notices()
     {
          $truck_id=$_POST['truck_id'];
          $trailer_id=$_POST['trailer_id'];
          
          $html="";
          $cntr=0;
               
          $sql="
               select equipment_special_notices.*,
                    (select trucks.name_truck from trucks where trucks.id=equipment_special_notices.truck_id) as truck_name,
                    (select trailers.trailer_name from trailers where trailers.id=equipment_special_notices.trailer_id) as tname
               from equipment_special_notices
               where equipment_special_notices.deleted=0 
                    and equipment_special_notices.active > 0
                    ".($truck_id > 0 ? "and equipment_special_notices.truck_id = '".$truck_id."'" : "")."
                    ".($trailer_id > 0 ? "and equipment_special_notices.trailer_id = '".$trailer_id."'" : "")."
               order by equipment_special_notices.active desc,
                    equipment_special_notices.truck_id asc, 
                    equipment_special_notices.trailer_id asc, 
                    equipment_special_notices.id asc
           ";
          $data = simple_query($sql);
          while($row = mysqli_fetch_array($data))
          {
               $html.="<p>".trim($row['special_notice'])."</p>";
               $cntr++;
          }
     
          display_xml_response("<rslt>1</rslt><cntr>".$cntr."</cntr><mrrTab><![CDATA[".$html."]]></mrrTab>");
     }
	
	
	
	function mrr_truck_tracking_messages_check_for_new()
	{
		//function moved to cron job...Jan. 2013
		/*
		$max_msg_packet=0;
		$sql="
			select next_msg_packet_id 
			from ".mrr_find_log_database_name()."truck_tracking_packets
			order by next_msg_packet_id desc 
			limit 1
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$max_msg_packet=$row['next_msg_packet_id'];
		}
		$serve_output3=mrr_peoplenet_find_data("oi_pnet_message_history",0,0,"",0,$max_msg_packet);
		*/
		display_xml_response("<rslt>1</rslt>");		
	}
	
	function mrr_plot_truck_tracking_report()
	{
		$mrr_src="";
		$rval="";
		
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$truck_id=$_POST['truck_id'];	
		
		$date_range_tracking=" and truck_tracking.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
		$truck_namer="";
				
		$sql = "
			select trucks.*
			from trucks
			where trucks.deleted = 0
				and trucks.peoplenet_tracking=1
				and trucks.id='".sql_friendly($truck_id)."'
			order by trucks.name_truck asc
		";		
		$data = simple_query($sql);
		$mn=mysqli_num_rows($data);
		if($row = mysqli_fetch_array($data))
		{
			$truck_namer=trim($row['name_truck']);		
		}
		
		$coords=0;
		$arr[0]="";
		
		$sql2 = "
     		select truck_tracking.*
     		from ".mrr_find_log_database_name()."truck_tracking
     		where truck_tracking.truck_id='".sql_friendly($truck_id) ."'
     			".$date_range_tracking."
     		group by truck_tracking.linedate desc
     	";     	
		$data2 = simple_query($sql2);
		$mn2=mysqli_num_rows($data2);		
		while($row2 = mysqli_fetch_array($data2))
		{						
			$arr[ $coords ]="".$row2['latitude'].",".$row2['longitude']."";
			$coords++;	
			
			$rval.="
				<Coordinate>
					<linedate><![CDATA[".$row2['linedate']."]]></linedate>
					<latitude><![CDATA[".$row2['latitude']."]]></latitude>
					<longitude><![CDATA[".$row2['longitude']."]]></longitude>
				</Coordinate>
			";				
		}
		
		if($coords == 1) 
		{			
			// only one address, so show exact spot, not driving directions
			$mrr_src = "https://maps.google.com/maps?f=q&q=".$arr[ 0 ]."&output=embed";	
		} 
		elseif($coords > 1)  
		{
			$mrr_src = "https://maps.google.com/maps?f=d&source=s_d&saddr=".$arr[ 0 ]."";
			for($x=1;$x < $coords; $x++)
			{					
				if($x > 1)
				{
					$mrr_src.="+to:".$arr[ $x ]."";	
				}
				else
				{
					$mrr_src.="&daddr=".$arr[ $x ]."";	
				}
			}	
			$mrr_src.="&output=embed";		
		}			
		
		display_xml_response("<rslt>1</rslt><mrrSRC><![CDATA[".$mrr_src."]]></mrrSRC><mrrNamer><![CDATA[".$truck_namer."]]></mrrNamer>".$rval."");	
	}
	
	function mrr_cust_average_pay_report()
	{		
     	$mrr_tab="";
     	$use_due_date=0;		//use the due date instead of the invoice date if value=1
     	
     	$mrr_tab.="
     		<table border='0' cellpadding='0' cellspacing='0' class='tablesorter'>
     		<thead>
     		<tr>
     			<th valign='top'><b>Customer</b></th>     			
     			<th valign='top'><b><=15 Invoices</b></th>
     			<th valign='top'><b>16-30 Invoices</b></th>
     			<th valign='top'><b>31-45 Invoices</b></th>
     			<th valign='top'><b>>=46 Invoices</b></th>
     			<th valign='top'><b>Total Invoices</b></th>     			
     			<th valign='top'><b>Tot Days</b></th>
     			<th valign='top'><b>Min Days</b></th>
     			<th valign='top'><b>Max Days</b></th>
     			<th valign='top'><b>Avg Days</b></th>
     		</tr>
     		</thead>
     		<tbody>
     	";
     	     	
     	$tot_period_15=0;
     	$tot_linker_15=0;
     	$tot_period_30=0;
     	$tot_linker_30=0;
     	$tot_period_45=0;
     	$tot_linker_45=0;
     	$tot_period_46=0;
     	$tot_linker_46=0;
     	$tot_inv_cntr=0;
     	$tot_totals_all=0;
     	
     	$tot_min_days=0;
     	$tot_max_days=0;
     	$tot_tot_days=0;
     	$tot_avg_days=0;
     	
     	$row_cntr=0;     	
     	$sql="
     		select customers.id,
     			customers.name_company,
     			customers.sicap_id
     		from customers
     		where customers.deleted=0
     			and customers.active>0
     		order by customers.name_company asc,customers.id asc
     		
     	";	//limit 50
     	$data = simple_query($sql);
          while($row = mysqli_fetch_array($data)) 
          {
          	$id = $row['id'];
          	$customer_id = $row['sicap_id'];
          	$customer_name = $row['name_company'];
          	
          	$inv_cntr=0;
          	$min_days=0;
          	$max_days=0;
          	$avg_days=0;
          	$tot_days=0;
          	
          	$period_15=0;
          	$period_30=0;
          	$period_45=0;
          	$period_46=0;
          	
          	$totals_15=0;
			$totals_30=0;
			$totals_45=0;
			$totals_46=0;          	
          	
          	if($customer_id>0 && $customer_id!=716)
          	{
          		//has a SICAP ID, so find invoice amounts...
               	$results=mrr_get_all_cust_has_paid($customer_id,$use_due_date);
     			foreach($results as $key => $value )
     			{
     				$prt=trim($key);			$tmp=trim($value);
              			if($prt=="invCnt")			$inv_cntr=$tmp;
               		if($prt=="daysMin")			$min_days=$tmp;
               		if($prt=="daysMax")			$max_days=$tmp;
               		if($prt=="daysTot")			$tot_days=$tmp;   
               		
               		if($prt=="ageOne")			$period_15=$tmp;
               		if($prt=="ageTwo")			$period_30=$tmp;
               		if($prt=="ageThree")		$period_45=$tmp;
               		if($prt=="ageFour")			$period_46=$tmp;    
               		
               		if($prt=="totOne")			$totals_15=$tmp;
     				if($prt=="totTwo")			$totals_30=$tmp;
     				if($prt=="totThree")		$totals_45=$tmp;
     				if($prt=="totFour")			$totals_46=$tmp; 	
     			}     
     			if(($period_45 > 0 && $totals_45 > 0) || ($period_46 > 0 && $totals_46 > 0))
     			{
     				$sql2 = "     				
     					update customers set
     						slow_pays='1'
     					where customers.id='".sql_friendly($id)."'
     						and override_slow_pays='0'
     					";
     				//simple_query($sql2);			//..................disabled the auto-grading for customer paying for James...June 2015.
     			}
     			/*
     			else
     			{
     				$sql2 = "     				
     					update customers set
     						slow_pays='0'
     					where customers.id='".sql_friendly($id)."'
     					";
     				//simple_query($sql2);			//..................disabled the auto-grading for customer paying for James...June 2015.
     			}
     			*/
          	}      	
          	if($inv_cntr>0)	
          	{               	         	
               	$totals_all=$totals_15 + $totals_30 + $totals_45 + $totals_46;               	
               	
               	$linker_15="$".number_format($totals_15,2)."";
               	$linker_30="$".number_format($totals_30,2)."";
               	$linker_45="$".number_format($totals_45,2)."";
               	$linker_46="$".number_format($totals_46,2)."";
               	
               	if($totals_15!=0)		$linker_15="<span class='mrr_link_like_on' onClick='mrr_api_aging_hunter(".$id.",15,\"customer_".$id."_summary\");'>$".number_format($totals_15,2)."</span>";
               	if($totals_30!=0)		$linker_30="<span class='mrr_link_like_on' onClick='mrr_api_aging_hunter(".$id.",30,\"customer_".$id."_summary\");'>$".number_format($totals_30,2)."</span>";
               	if($totals_45!=0)		$linker_45="<span class='mrr_link_like_on' onClick='mrr_api_aging_hunter(".$id.",45,\"customer_".$id."_summary\");'>$".number_format($totals_45,2)."</span>";
               	if($totals_46!=0)		$linker_46="<span class='mrr_link_like_on' onClick='mrr_api_aging_hunter(".$id.",46,\"customer_".$id."_summary\");'>$".number_format($totals_46,2)."</span>";
               	              	              	
               	$avg_days=($tot_days / $inv_cntr);
               	$mrr_tab.="
               		<tr>
               			<td valign='top'><a href='admin_customers.php?eid=".$id."' target='_blank' title='Company ID in SICAP is ".$customer_id."'>".$customer_name."</a></td>
               			<td valign='top' align='right'><span style='color:green; font-weight:bold;'>(".$period_15.")</span> ".$linker_15."</td>
               			<td valign='top' align='right'><span style='color:orange; font-weight:bold;'>(".$period_30.")</span> ".$linker_30."</td>
               			<td valign='top' align='right'><span style='color:red; font-weight:bold;'>(".$period_45.")</span> ".$linker_45."</td>
               			<td valign='top' align='right'><span style='color:red; font-weight:bold;'>(".$period_46.")</span> ".$linker_46."</td>
               			<td valign='top' align='right'>(".$inv_cntr.") $".number_format($totals_all,2)."</td>
               			<td valign='top' align='right'>".$tot_days."</td>
               			<td valign='top' align='right'>".$min_days."</td>
               			<td valign='top' align='right'>".$max_days."</td>
               			<td valign='top' align='right'>".number_format($avg_days,2)."</td>
               		</tr>
               		<tr class='customer_".$id."_summary_row customer_row'>
               			<td valign='top' colspan='10'><div id='customer_".$id."_summary'></div></td>
               		</tr>
          		"; 
          		$tot_period_15+=$period_15;
          		$tot_period_30+=$period_30;
          		$tot_period_45+=$period_45;
          		$tot_period_46+=$period_46;
          		$tot_inv_cntr+=$inv_cntr;
          		
     			$tot_linker_15+=$totals_15;     			
     			$tot_linker_30+=$totals_30;     			
     			$tot_linker_45+=$totals_45;     			
     			$tot_linker_46+=$totals_46;	
     			$tot_totals_all+=$totals_all;  
     			
     			if($row_cntr==0)
     			{
     				$tot_min_days=$min_days;
     				$tot_max_days=$max_days;	
     			}
     			
     			if($min_days < $tot_min_days)		$tot_min_days=$min_days;
     			if($max_days > $tot_max_days)		$tot_max_days=$max_days;
     			
     			$tot_tot_days+=$tot_days;  			   
     			
     			$row_cntr++;    		
     		}
     	}
     	
     	if($tot_inv_cntr > 0)	$tot_avg_days=$tot_tot_days / $tot_inv_cntr; 
     		
     	$mrr_tab.="
     				<tr>
               			<td valign='top'><b>Amount Due Total</b></td>
               			<td valign='top' align='right'><span style='color:green; font-weight:bold;'>(".$tot_period_15.")</span> $".number_format($tot_linker_15,2)."</td>
               			<td valign='top' align='right'><span style='color:orange; font-weight:bold;'>(".$tot_period_30.")</span> $".number_format($tot_linker_30,2)."</td>
               			<td valign='top' align='right'><span style='color:red; font-weight:bold;'>(".$tot_period_45.")</span> $".number_format($tot_linker_45,2)."</td>
               			<td valign='top' align='right'><span style='color:red; font-weight:bold;'>(".$tot_period_46.")</span> $".number_format($tot_linker_46,2)."</td>
               			<td valign='top' align='right'>(".$tot_inv_cntr.") $".number_format($tot_totals_all,2)."</td>
               			<td valign='top' align='right'>".$tot_tot_days."</td>
               			<td valign='top' align='right'>".$tot_min_days."</td>
               			<td valign='top' align='right'>".$tot_max_days."</td>
               			<td valign='top' align='right'>".number_format($tot_avg_days,2)."</td>
               		</tr>
     		</tbody>
     		</table>
     	";
					
          display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>");
	}

	function mrr_count_text_box_characters()
	{	//find char limit in text box and count the letters...if over limit truncate it down...display counter on interface.
		$text_box=$_POST['text_box'];
		$char_lim=$_POST['char_limit'];	
		
		$chars=strlen($text_box);
		if($chars > $char_lim && $char_lim > 0)
		{
			$text_box=substr($text_box,0,$char_lim);
			$chars=$char_lim;	
		}
				
		display_xml_response("<rslt>1</rslt><mrrTxt><![CDATA[".$text_box."]]></mrrTxt><mrrLim><![CDATA[".$chars."/".$char_lim."]]></mrrLim>");
	}
	
	function mrr_current_date_and_time()
	{
		$date=date("m/d/Y");
		$time=date("H:i");
		
		display_xml_response("<rslt>1</rslt><mrrDate><![CDATA[".$date."]]></mrrDate><mrrTime><![CDATA[".$time."]]></mrrTime>");	
	}
	
	function mrr_fetch_canned_message()
	{
		$id=$_POST['message_id'];
		$tab="";
		
		$sql="
     		select truck_tracking_canned_message.*
     		from truck_tracking_canned_message
     		where id='".sql_friendly($id)."'     		
     	";	
     	$data = simple_query($sql);
          while($row = mysqli_fetch_array($data)) 
          {
          	$tab=trim($row['canned_message']);
          }
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$tab."]]></mrrTab>");		
	}
	
	function view_comdata_log() {
		global $defaultsarray;
		
		$use_path = $defaultsarray['base_path'].'/comdata/backup/';
		
		$new_filename = "comdata_".createuuid().".csv";
		
		copy($use_path.$_POST['fname'], getcwd()."/temp/".$new_filename);
		
		display_xml_response("<rslt>1</rslt><filename><![CDATA[$new_filename]]></filename>");
	}
	
	function mrr_customer_fuel_surcharge_by_date()
	{
		$date=$_POST['date_from'];
		$tab="";
          
          global $defaultsarray;
          $no_cur_log_val=0;         
          		
		$surcharge=0;
		$sql="
     		select fuel_surcharge
     		from log_fuel_updates
     		where linedate_added>='".date("Y-m-d", strtotime($date))." 00:00:00'
     			and linedate_added<='".date("Y-m-d", strtotime($date))." 23:59:59'
     		order by id desc
     		limit 1   		
     	";	
     	$data = simple_query($sql);
          if($row = mysqli_fetch_array($data)) 
          {
          	$surcharge=$row['fuel_surcharge'];
          }
          else
          {
               $surcharge=$defaultsarray['fuel_surcharge'];
               $no_cur_log_val=1;
               
               //check for last range just in case
               $sql="
                    select fuel_surcharge
                    from log_fuel_updates
                    where linedate_added<='".date("Y-m-d", strtotime($date))." 23:59:59'
                    order by id desc
                    limit 1   		
               ";
               $data = simple_query($sql);
               if($row = mysqli_fetch_array($data))
               {
                    $surcharge=$row['fuel_surcharge'];
                    $no_cur_log_val=0;
               }    
          }
                    
          $tab.="
          		<table border='0' cellpadding='0' cellspacing='0' width='100%'>
          		<tr>
          			<td valign='top' colspan='2' align='center'><b>Fuel Surcharge for ".date("m/d/Y", strtotime($date))."</b></td>       			
          		</tr>
          		".($no_cur_log_val > 0 ? "<tr><td valign='top' colspan='2' align='center'><span class='alert'><b>WARNING: No current log value, rate may be stale.</b></span></td></tr>" : "")."
          		<tr>
          			<td valign='top'><b>National Average</b></td>
          			<td valign='top' align='right'><span class='alert'><b>$".number_format($surcharge,3)."</b></span></td>          			
          		</tr>
          		<tr>
          			<td valign='top'><b>Customer</b></td>
          			<td valign='top' align='right'><b>Surcharge</b></td>          			
          		</tr>
          	";
          
          $sql="
     		select customers.*,
     			(
     				select fuel_surcharge.fuel_surcharge 
     				from fuel_surcharge 
     				where fuel_surcharge.customer_id=customers.id 
     					and fuel_surcharge.range_lower<='".sql_friendly($surcharge)."' 
     				order by fuel_surcharge.range_lower desc,
     					fuel_surcharge.id desc 
     				limit 1
     			) as mrr_charge
     			
     		from customers
     		where deleted = 0
     			and use_fuel_surcharge > 0	
     		order by name_company asc, 
     			id asc	
     	";	//and linedate_added<=
     	$data = simple_query($sql);
     	$cntr=0;
          while($row = mysqli_fetch_array($data)) 
          {
          	$id=$row['id'];
          	$namer=$row['name_company'];
          	$charge=$row['mrr_charge'];
          	
          	$tab.="
          		<tr class='".($cntr%2==0 ? 'even': 'odd')."'>
          			<td valign='top'><a href='admin_customers.php?eid=".$id."' target='_blank'>".$namer."</a></td>
          			<td valign='top' align='right'><span class='alert'>$".number_format($charge,3)."</span></td>          			
          		</tr>
          	";
          	$cntr++;
          }
          $tab.="</table>";          
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$tab."]]></mrrTab>");		
	}
	
	function mrr_clear_switch_id()
	{
		$id=$_POST['switch_id'];	
		$old_id=0;
		$new_id=0;
		$dated="0000-00-00";
		$dispatch_id=0;
		
		$sql="
     		select *
     		from trailer_switched
     		where id='".sql_friendly($id)."'
     	";	
     	$data = simple_query($sql);
          if($row = mysqli_fetch_array($data)) 
          {
          	$old_id=$row['old_trailer_id'];
          	$new_id=$row['new_trailer_id'];
          	$dated=date("Y-m-d",strtotime($row['linedate']));
          	$dispatch_id=$row['dispatch_id'];
          }
		
		if($old_id>0)
		{			
			$sql = "
				update trailers_dropped set					
					deleted=1
				where trailer_id = '".sql_friendly($old_id)."'
					and linedate>='".$dated." 00:00:00'
					and linedate<='".$dated." 23:59:59'
					and LOCATE('Quick Trailer Drop.',notes)>0
			";
			simple_query($sql);	
			
			$sql = "
				update drivers set					
					attached_trailer_id = '".sql_friendly($old_id)."'
				where attached_trailer_id = '".sql_friendly($new_id)."'
			";
			simple_query($sql);	
			
			$sql = "
				update trucks_log set					
					trailer_id = '".sql_friendly($old_id)."',
					linedate_updated=NOW()
				where id = '".sql_friendly($dispatch_id)."'
			";
			simple_query($sql);			
		}
		
		
		$sql2="update trailer_switched set
				deleted=1 
			where id='".sql_friendly($id)."'
		";
		simple_query($sql2);
		
		
		
		display_xml_response("<rslt>1</rslt>");	
	}
	
	
	function mrr_get_current_location_for_truck_id()
	{
		$truck_id=$_POST['truck_id'];	
		$load_id=$_POST['load_id'];	
		$mode_id=0;
		if(isset($_POST['mode']))		$mode_id=(int) $_POST['mode'];	
		
		$long="0";
		$lat="0";
		$location="(Disabled)";
		$truck_name="";
				
		if($mode_id==1)
		{	//GeoTab version			
			$sql="
     			select name_truck,
     				geotab_current_location,
     				geotab_last_longitude,
     				geotab_last_latitude,
     				geotab_gps_date,
     				geotab_truck_speed,
     				geotab_last_odometer_date,
     				geotab_last_odometer_reading
     			from trucks
     			where id='".sql_friendly($truck_id)."'
     		";	
     		$data = simple_query($sql);
          	if($row = mysqli_fetch_array($data)) 
          	{
          		$long=trim($row['geotab_last_longitude']);
				$lat=trim($row['geotab_last_latitude']);
				$location="GeoTab: ".trim($row['geotab_current_location']);
				$truck_name=trim($row['name_truck']);
          	}
		}
		else
		{	//PeopleNet version			
			$res=mrr_find_only_location_of_this_truck($truck_id);
			
			$long=$res['longitude'];
			$lat=$res['longitude'];
			$location="PeopleNet: ".$res['location'];
			$truck_name=$res['truck_name'];
		}
		
		$map="";
		if($long!="0" && $lat!="0")
		{		
			$map=mrr_gen_truck_local_map_by_google($load_id,$truck_name,$lat,$long,$truck_id);
		}
		$txt="Current Position: ".$location."".$map."";	// Lat: (".$lat.") and Long: (".$long.")
		
		display_xml_response("<rslt>1</rslt><mrrText><![CDATA[".$txt."]]></mrrText>");		
	}
	
	function mrr_update_driver_payroll_change()
	{
		$id=$_POST['id'];				//drivers_employer_change
		$driver_id=$_POST['driver_id'];		
		
		//update employer change effective date first.
		$sql = "
			update driver_payroll_changes set
				deleted='1'
			where id='".sql_friendly($id)."' 
				and driver_id='".sql_friendly($driver_id)."'
		";
		simple_query($sql);	
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log['driver_id']=$driver_id;
		$mrr_activity_log["notes"]="Driver Payroll Schedule ".$id." Removed.";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,$driver_id,0,0,0,0,0,"Driver Payroll Schedule ".$id." Removed");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
				
		display_xml_response("<rslt>1</rslt>");	
	}
	function mrr_add_driver_payroll_change()
	{
		global $datasource;

		$res=0;
		
		$notes=$_POST['reason'];				//drivers_employer_change
		$driver_id=$_POST['driver_id'];		
		$date=$_POST['date'];
		if(trim($date)=="")			$date=date("m/d/Y",time());
				
		$miles=mrr_money_stripper( $_POST['miles'] );
		$miles_tm=mrr_money_stripper( $_POST['miles_tm'] );
		$miles_ch=mrr_money_stripper( $_POST['miles_ch'] );
		$miles_ch_tm=mrr_money_stripper( $_POST['miles_ch_tm'] );
		
		$hours=mrr_money_stripper( $_POST['hours'] );
		$hours_tm=mrr_money_stripper( $_POST['hours_tm'] );
		$hours_ch=mrr_money_stripper( $_POST['hours_ch'] );
		$hours_ch_tm=mrr_money_stripper( $_POST['hours_ch_tm'] );
		
		$vaca_days=mrr_money_stripper( $_POST['vaca_days'] );
		$vaca_used=mrr_money_stripper( $_POST['vaca_used'] );
		$sick_days=mrr_money_stripper( $_POST['sick_days'] );
		$sick_used=mrr_money_stripper( $_POST['sick_used'] );
		
		//update employer change effective date first.
		$sql="
			insert into driver_payroll_changes
				(id,
				user_id,
				linedate_added,
				driver_id,				
				linedate,					
				single_hour_pay,
				single_mile_pay,
				single_hour_pay_charged,
				single_mile_pay_charged,
				team_hour_pay,
				team_mile_pay,
				team_hour_pay_charged,
				team_mile_pay_charged,					
				auto_schedule,	
				vaca_days_allowed,
				sick_days_allowed,
				vaca_days_used,
				sick_days_used,				
				deleted,
				raise_notes)
			values 
				(NULL,
				'".sql_friendly($_SESSION['user_id'])."',
				NOW(),
				'".sql_friendly($driver_id)."',
				'".date("Y-m-d", strtotime($date))." 00:00:00',
				'".sql_friendly($hours)."',
				'".sql_friendly($miles)."',
				'".sql_friendly($hours_ch)."',
				'".sql_friendly($miles_ch)."',
				'".sql_friendly($hours_tm)."',
				'".sql_friendly($miles_tm)."',
				'".sql_friendly($hours_ch_tm)."',
				'".sql_friendly($miles_ch_tm)."',
				1,
				'".sql_friendly($vaca_days)."',
				'".sql_friendly($sick_days)."',
				'".sql_friendly($vaca_used)."',				
				'".sql_friendly($sick_used)."',
				0,
				'".sql_friendly($notes)."')
		";
		simple_query($sql);	
		$res=mysqli_insert_id($datasource);
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log['driver_id']=$driver_id;
		$mrr_activity_log["notes"]="Driver Payroll Schedule ".$res." Added (Auto).";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,$driver_id,0,0,0,0,0,"Driver Payroll Schedule ".$res." Added (Auto)");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
				
		display_xml_response("<rslt>".$res."</rslt>");	
	}
		
	function mrr_update_driver_employer()
	{
		$dec_id=$_POST['id'];				//drivers_employer_change
		$driver_id=$_POST['driver_id'];	
		$employer_id=$_POST['employer_id'];
		$linedate=$_POST['linedate'];
		$delete_flag=$_POST['delete_flag'];
		$stamp=date("Y-m-d",strtotime($linedate));
		
		$emp_name=mrr_fetch_set_employer($employer_id);
		$namer=mrr_fetch_driver_name($driver_id);	
		
		$moder="Changing";	
		if($delete_flag>0)
		{
			$moder="Removing";	
			
			//find old employer_id to switch back to it for this period, first.
			$old_id=0;
			$sql="
          		select old_employer_id
          		from drivers_employer_change
          		where id='".sql_friendly($dec_id)."'
          	";	
          	$data = simple_query($sql);
               if($row = mysqli_fetch_array($data)) 
               {
               	$old_id=$row['old_employer_id'];
               }
		
		     //remove it.
     		$sqlu2 = "
     				update drivers_employer_change set
     					deleted='1'
     				where id='".sql_friendly($dec_id)."'
     			";
     		simple_query($sqlu2);				
     		
     		
     		//check if there is a later change so that we only update dispatches within the range.  If no further, update all past incoming date.
     		$end_date="";
     		$sql="
          		select linedate,old_employer_id
          		from drivers_employer_change
          		where driver_id='".sql_friendly($driver_id)."'
          			and linedate>'".$stamp." 23:59:59'
          			and deleted=0
          		order by linedate asc
          		limit 1
          	";	
          	$data = simple_query($sql);
               if($row = mysqli_fetch_array($data)) 
               {
               	$end_date=$row['linedate'];
               	$end_date=date("Y-m-d",strtotime($end_date));
               }
               
               if($end_date!="")
               {	//use a range of dispatches, another date was found after this one in change records...
               	$sqlu2 = "
     				update trucks_log set
     					employer_id='".sql_friendly($old_id)."'
     				where driver_id='".sql_friendly($driver_id)."'
     					and linedate>='".$stamp." 00:00:00'
     					and linedate<'".$end_date." 00:00:00'
     			";
     			simple_query($sqlu2);
               }
               else
               {	//use everything greater than this date to change dispatches.
               	$sqlu2 = "
     				update trucks_log set
     					employer_id='".sql_friendly($old_id)."'
     				where driver_id='".sql_friendly($driver_id)."'
     					and linedate>='".$stamp." 00:00:00'
     			";
     			simple_query($sqlu2);
               }
		}
		else
		{			
		     //update employer change effective date first.
     		$sqlu2 = "
     				update drivers_employer_change set
     					linedate='".$stamp." 00:00:00'
     				where id='".sql_friendly($dec_id)."'
     			";
     		simple_query($sqlu2);				
     		
     		
     		//check if there is a later change so that we only update dispatches within the range.  If no further, update all past incoming date.
     		$end_date="";
     		$sql="
          		select linedate
          		from drivers_employer_change
          		where driver_id='".sql_friendly($driver_id)."'
          			and linedate>'".$stamp." 23:59:59'
          			and deleted=0
          		order by linedate asc
          		limit 1
          	";	
          	$data = simple_query($sql);
               if($row = mysqli_fetch_array($data)) 
               {
               	$end_date=$row['linedate'];
               	$end_date=date("Y-m-d",strtotime($end_date));
               }
               
               if($end_date!="")
               {	//use a range of dispatches, another date was found after this one in change records...
               	$sqlu2 = "
     				update trucks_log set
     					employer_id='".sql_friendly($employer_id)."'
     				where driver_id='".sql_friendly($driver_id)."'
     					and linedate>='".$stamp." 00:00:00'
     					and linedate<'".$end_date." 00:00:00'
     			";
     			simple_query($sqlu2);
               }
               else
               {	//use everything greater than this date to change dispatches.
               	$sqlu2 = "
     				update trucks_log set
     					employer_id='".sql_friendly($employer_id)."'
     				where driver_id='".sql_friendly($driver_id)."'
     					and linedate>='".$stamp." 00:00:00'
     			";
     			simple_query($sqlu2);
               }	
		}
		
		
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log['driver_id']=$driver_id;
		$mrr_activity_log["notes"]="".$moder." driver ".$namer." employer to ".$emp_name." as of ".$linedate."". ( $end_date!="" ? " thru ".$end_date."" : "")  .".";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,$driver_id,0,0,0,0,0,"".$moder." driver ".$namer." employer to ".$emp_name." as of ".$linedate."");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$moder." driver ".$namer." employer to ".$emp_name." as of ".$linedate."". ( $end_date!="" ? " thru ".$end_date."" : "")  .".]]></mrrTab>");		
	}
	
	
	function mrr_add_hot_load_tracking()
	{
		$load_id=$_POST['load_id'];	
		$dispatch_id=$_POST['dispatch_id'];	
		$stop_id=$_POST['stop_id'];
		$driver_id=0;
		$truck_id=0;
		$trailer_id=0;
		
		$nowtime=time();
		
		global $defaultsarray;		
		$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
		$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
		
		if($mph<=0)	$mph=1;
		
		$munits=5280;
		$debugger="";
		
		if($load_id>0)
		{
			$sql="
				select * 
				from load_handler
				where id='".sql_friendly($load_id)."'				
			";
			$data=simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{					
				$cust=mrr_get_all_customer_settings($row['customer_id']);			
				
				$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
				$hl_timer=$cust['hot_load_timer'];					//interval between messages
				//$hl_earriving=$cust['hot_load_email_arriving'];		//email addresses varchar
				//$hl_earrived=$cust['hot_load_email_arrived'];		//email addresses varchar
				//$hl_edeparted=$cust['hot_load_email_departed'];		//email addresses varchar
				//$hl_marriving=$cust['hot_load_email_msg_arriving'];	//email message text
				//$hl_marrived=$cust['hot_load_email_msg_arrived'];	//email message text
				//$hl_mdeparted=$cust['hot_load_email_msg_departed'];	//email message text
				$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
				$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
				$hl_r_departed=$cust['hot_load_radius_departed'];		//
     			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices	
     			
				if($dispatch_id>0)
				{
					$sql2="
						select trucks_log.*,
							(select peoplenet_tracking from trucks where trucks.id=trucks_log.truck_id) as peoplenet_active
						from trucks_log
						where trucks_log.load_handler_id='".sql_friendly($load_id)."'
							and trucks_log.id='".sql_friendly($dispatch_id)."'				
					";
				}
				else
				{
					$sql2="
						select trucks_log.*,
							(select peoplenet_tracking from trucks where trucks.id=trucks_log.truck_id) as peoplenet_active
						from trucks_log
						where trucks_log.load_handler_id='".sql_friendly($load_id)."'				
					";	
				}
				$data2=simple_query($sql2);
				while($row2=mysqli_fetch_array($data2))
				{
					$driver_id=$row2['driver_id'];
					$truck_id=$row2['truck_id'];
					$trailer_id=$row2['trailer_id'];
					$customer_id=$row2['customer_id'];
					$people_net=$row2['peoplenet_active'];
					
					$people_net_adder="";
					if($people_net>0)
					{
						$people_net_adder=",
							(select latitude from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_lat,
							(select longitude from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_long,
							(select location from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_local
						";
					}
					
										
					if($stop_id>0)
					{
						$sql3="
							select load_handler_stops.*".$people_net_adder."
								from load_handler_stops
							where load_handler_stops.load_handler_id='".sql_friendly($row['id'])."'
								and load_handler_stops.trucks_log_id='".sql_friendly($row2['id'])."'
								and load_handler_stops.id='".sql_friendly($stop_id)."'			
						";
					}
					else
					{
						$sql3="
							select load_handler_stops.*".$people_net_adder." 
								from load_handler_stops
							where load_handler_stops.load_handler_id='".sql_friendly($row['id'])."'
								and load_handler_stops.trucks_log_id='".sql_friendly($row2['id'])."'
								and load_handler_stops.deleted=0
						";
					}	
					$data3=simple_query($sql3);
					while($row3=mysqli_fetch_array($data3))
					{
						$lat=$row3['latitude'];
						$long=$row3['longitude'];
						$doner=$row3['linedate_completed'];
						$dater=$row3['linedate_pickup_eta'];
						$finish_time=(strtotime($row3['linedate_pickup_eta']) - $nowtime) / (60 * 60);	//hours left until delivery is late...
											
						$activator=1;
						
						$last_gps_lat=0;
						$last_gps_long=0;
						$gps_dist=0;
						$gps_message="";
						$has_arriving=0;
						$has_arrived=0;
						$has_departed=0;
						$miles_remain_arriving=0;
						$miles_remain_arrived=0;
						$miles_remain_departed=0;
						$approx_arriving=0;
						$approx_arrived=0;
						$approx_departed=0;
						$conard_grade=0;
						
						if($doner>'2013-01-01 00:00:00')
						{	//already completed...
							$has_arriving=1;
							$has_arrived=1;
							$has_departed=1;
							$activator=0;	
						}
						else
						{	//not completed, but has GPS point for stop             if($lat>0 && $long>0)
							$get_local=mrr_find_only_location_of_this_truck($truck_id);
							
							$last_gps_lat=$get_local['latitude'];
							$last_gps_long=$get_local['longitude'];
							$last_gps_local=$get_local['location'];
							
							if($last_gps_lat==0 && $last_gps_long==0)
							{
								$last_gps_lat=$row3['pn_lat'];
								$last_gps_long=$row3['pn_long'];	
								$last_gps_local=$row3['pn_local'];
							}
							
							$gps_message="Truck ".$get_local['truck_name']." Is ".$last_gps_local."";//$get_local['gps_location']
							//$temp_page=$get_local['temp_page']
							
							
							
							$gps_dist=mrr_distance_between_gps_points($lat,$long,$last_gps_lat,$last_gps_long);		//has MILES...
							$overall_hrs= $gps_dist / $mph;
							
							$gps_dist2=$gps_dist * $munits;					//convert distance in miles to feet....
							
							if($has_arriving==0)
							{
								if($gps_dist2 <= ($hl_r_arriving + $tolerance))								
								{
									$has_arriving=1;	//this part is done...
								}
								else
								{
									$miles_remain_arriving=($gps_dist - ($hl_r_arriving / $mph));
									$approx_arriving=$miles_remain_arriving / $mph;
								}	
							}
							if($has_arrived==0)
							{
								if($gps_dist2 <= ($hl_r_arrived + $tolerance))
								{
									$has_arrived=1;	//this part is done...
								}
								else
								{
									$miles_remain_arrived=($gps_dist - ($hl_r_arrived / $mph));
									$approx_arrived=$miles_remain_arrived / $mph;
								}	
							}
							if($has_departed==0)
							{	//leaving, so on way out and works backwards...
								if(($gps_dist2 + $tolerance) >= $hl_r_departed && $has_arrived==1)
								{
									$has_departed=1;	//this part is done...but only if arrived first...
								}
								else
								{
									$miles_remain_departed=( $gps_dist - ($hl_r_departed / $mph));
									$approx_departed=$miles_remain_departed / $mph;
								}	
							}
							
							$debugger.="
								<div>
									Load ".$load_id.", Dispatch ".$row2['id'].", Stop ".$row3['id'].": Distance ".$gps_dist." Miles (".$gps_dist2." Feet)
									, Section 1 complete=".$has_arriving." - Radius=".($hl_r_arriving + $tolerance)." Remaining ".$miles_remain_arriving." ft
									, Section 2 complete=".$has_arrived." - Radius=".($hl_r_arrived + $tolerance)." Remaining ".$miles_remain_arrived." ft
									, Section 3 complete=".$has_departed." - Radius=".($hl_r_departed + $tolerance)." Remaining ".$miles_remain_departed." ft
									.
								</div>";
							// ".$get_local['truck_name']." is ".$last_gps_local."
							
							
							if(($overall_hrs * 2) > $finish_time)		$conard_grade=mrr_encode_geofencing_grade("Epic Fail");
							elseif(($overall_hrs * 2) > $finish_time)	$conard_grade=mrr_encode_geofencing_grade("Very Late");
							elseif($overall_hrs > $finish_time)		$conard_grade=mrr_encode_geofencing_grade("Late");
							elseif(($overall_hrs * 2) < $finish_time)	$conard_grade=mrr_encode_geofencing_grade("Early");
							elseif($overall_hrs <= $finish_time)		$conard_grade=mrr_encode_geofencing_grade("On Time");	
							
							if( $lat==0 || $long==0)
     						{
     							$conard_grade=0;	//if not dispatched through peoplenet, not long,lat GPS points, so do not grade it...
     						}												
						}						
						
						$sql4="
							insert into ".mrr_find_log_database_name()."geofence_hot_load_tracking
								(id, 
               					linedate_added,
               					linedate,
               					deleted,
               					active,					
               					load_id,
               					dispatch_id,
               					stop_id,
               					driver_id,
               					truck_id,
               					trailer_id,
               					customer_id,					
               					dest_longitude,
               					dest_latitude,					
               					linedate_last_gps,
               					last_gps_longitude,
               					last_gps_latitude,					
               					dest_distance,
               					dest_message,					
               					dest_arriving,
               					dest_arrived,
               					dest_departed,					
               					dest_remaining_arriving,
               					dest_remaining_arrived,
               					dest_remaining_departed,					
               					dest_time_arriving,
               					dest_time_arrived,
               					dest_time_departed,		
               					dispatch_grade)
							values
								(NULL,
								NOW(),
								'".date("Y-m-d",strtotime($dater))."',
								0,
								'".sql_friendly($activator)."',
								'".sql_friendly($load_id)."',
								'".sql_friendly($row2['id'])."',
								'".sql_friendly($row3['id'])."',
								'".sql_friendly($driver_id)."',
								'".sql_friendly($truck_id)."',
								'".sql_friendly($trailer_id)."',
								'".sql_friendly($customer_id)."',
								'".sql_friendly($long)."',
								'".sql_friendly($lat)."',
								NOW(),
								'".sql_friendly($last_gps_long)."',
								'".sql_friendly($last_gps_lat)."',
								'".sql_friendly($gps_dist)."',
								'".sql_friendly($gps_message)."',
								'".sql_friendly($has_arriving)."',
								'".sql_friendly($has_arrived)."',
								'".sql_friendly($has_departed)."',
								'".sql_friendly($miles_remain_arriving)."',
								'".sql_friendly($miles_remain_arrived)."',
								'".sql_friendly($miles_remain_departed)."',
								'".sql_friendly($approx_arriving)."',
								'".sql_friendly($approx_arrived)."',
								'".sql_friendly($approx_departed)."',
								'".sql_friendly($conard_grade)."'								
								)	
						";						
						simple_query($sql4);						
					}
				}				
					
			}	
		}		
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log['driver_id']=$driver_id;
		$mrr_activity_log['truck_id']=$truck_id;
		$mrr_activity_log['trailer_id']=$trailer_id;
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$dispatch_id;
		$mrr_activity_log['stop_id']=$stop_id;
		
		$mrr_activity_log["notes"]="Added Hot tracking to Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".";
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[Added Hot tracking to Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".]]></mrrTab><debugger><![CDATA[".$debugger."]]></debugger>");	
	}
	function mrr_update_hot_load_tracking()
	{		
		$load_id=$_POST['load_id'];	
		$dispatch_id=$_POST['dispatch_id'];	
		$stop_id=$_POST['stop_id'];
		$debugger=mrr_run_updater_for_load_stop_dispatch($load_id,$dispatch_id,$stop_id);
		
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$dispatch_id;
		$mrr_activity_log['stop_id']=$stop_id;
		
		$mrr_activity_log["notes"]="Updated Hot tracking to Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,$dispatch_id,$stop_id,"Updated Hot tracking to Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[Updated Hot tracking from Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".]]></mrrTab><debugger><![CDATA[".$debugger."]]></debugger>");	
	}
	function mrr_get_hot_load_tracking()
	{
		$load_id=$_POST['load_id'];	
		$dispatch_id=$_POST['dispatch_id'];	
		$stop_id=$_POST['stop_id'];
		
		$tracker="N/A";
						
		$mrr_adder="";
		if($dispatch_id>0)		$mrr_adder.=" and dispatch_id='".sql_friendly($dispatch_id)."'";
		if($stop_id>0)			$mrr_adder.=" and stop_id='".sql_friendly($stop_id)."'";
				
		$sql="
			select load_handler.customer_id,
				(
					select geofencing_radius_active from customers where customers.id=load_handler.customer_id
				) as fencing_active,
				(
					select hot_load_switch from customers where customers.id=load_handler.customer_id
				) as hot_load_active
			from load_handler
			where load_handler.id='".sql_friendly($load_id)."'				
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			if($row['fencing_active'] > 0)
			{
				$tracker="<span class='mrr_link_like_on' onClick='activate_hot_tracking(".$load_id.",".$dispatch_id.",".$stop_id.");'>Turn Hot Load tracking on.</span>";
			}
			if($row['hot_load_active'] > 0)
			{
				//$tracker="<span class='mrr_link_like_on' onClick='activate_hot_tracking(".$load_id.",".$dispatch_id.",".$stop_id.");'>Turn Hot Load tracking on.</span>";
			}		
		}
		
		
		$sql="
			select * 
			from ".mrr_find_log_database_name()."geofence_hot_load_tracking
			where deleted=0
				and load_id='".sql_friendly($load_id)."'
				".$mrr_adder."					
		";
		$data=simple_query($sql);
		if(mysqli_num_rows($data) > 0)
		{			
			$tracker="
				<b>Hot Load tracking on</b>...  
				<span class='mrr_link_like_on' onClick='update_hot_tracking(".$load_id.",".$dispatch_id.",".$stop_id.");'>Update Position.</span> or 
				<span class='mrr_link_like_on' onClick='deactivate_hot_tracking(".$load_id.",".$dispatch_id.",".$stop_id.",2);'>Turn Hot Load tracking off.</span>	
			";		//2 is for remove mode to delete...
		}
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$tracker.".]]></mrrTab>");			
	}
	function mrr_remove_hot_load_tracking()
	{		
		$load_id=$_POST['load_id'];	
		$dispatch_id=$_POST['dispatch_id'];	
		$stop_id=$_POST['stop_id'];
		
		$mrr_adder="";
		if($dispatch_id>0)		$mrr_adder.=" and dispatch_id='".sql_friendly($dispatch_id)."'";
		if($stop_id>0)			$mrr_adder.=" and stop_id='".sql_friendly($stop_id)."'";
		
		
		$moder="Deactivated";
		$remover=$_POST['remove_mode'];
		if($remover>1)
		{
			$sql="
				update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
					deleted=1
				where load_id='".sql_friendly($load_id)."'
					".$mrr_adder."					
			";
			simple_query($sql);
			$moder="Removed";
		}
		else
		{
			$sql="
				update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
					active='".sql_friendly($remover)."'
				where load_id='".sql_friendly($load_id)."'
					".$mrr_adder."					
			";
			simple_query($sql);	
		}
		//...................SET FOR USER ACTION LOG............................................................................................................
		global $mrr_activity_log;
		$mrr_activity_log['load_handler_id']=$load_id;
		$mrr_activity_log['dispatch_id']=$dispatch_id;
		$mrr_activity_log['stop_id']=$stop_id;
		
		$mrr_activity_log["notes"]="Removed Hot tracking to Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,$dispatch_id,$stop_id,"Removed Hot tracking to Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		//......................................................................................................................................................
		
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$moder." Hot tracking from Load ".$load_id.", Dispatch ".$dispatch_id.", and Stop ".$stop_id.".]]></mrrTab>");	
	}
	function mrr_send_email_for_hot_load_tracking()
	{		
		$geo_id=$_POST['geo_id'];
		$sector=$_POST['geo_sector'];
		$sector=(int)$sector;
		
		$resx="Error";		
		
		$notice=mrr_send_geofencing_note($geo_id,$sector);
		$xid=$notice['sent'];
		$sect=$notice['sect'];
		$msg=$notice['msg'];
		
		if($xid > 0)	$resx="Done";
		
		display_xml_response("<rslt>1</rslt><mrrTab>".$resx."</mrrTab><sector>".$sect."</sector><msg><![CDATA[".$msg."]]></msg>");	
	}
	
	function mrr_check_up_on_geo_id()
	{
		$geo_id=$_POST['geo_id'];
		
		$truck_id=309;
		$truck_name="99999999";
		$gps_lat=32.940701;
		$gps_long=-83.782372;
		$site_lat=32.940701;
		$site_long=-83.782272;
		
		$truck_id=$_POST['truck_id'];
		$truck_name=$_POST['truck_name'];
		$site_lat=$_POST['dest_lat'];
		$site_long=$_POST['dest_long'];
		$gps_lat=$_POST['gps_lat'];
		$gps_long=$_POST['gps_long'];
			
					
		//$resx=mrr_new_geofence_hot_load_tracking_update_by_id($geo_id);
		
		$resx=mrr_test_drive_gps_distance($truck_id,$truck_name,$site_lat,$site_long,$gps_lat,$gps_long);
		
				
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$resx."]]></mrrTab>");		
	}
	function mrr_check_up_on_geotab_id()
	{
		$geo_id=$_POST['geo_id'];
		
		$truck_id=309;
		$truck_name="99999999";
		$gps_lat=32.940701;
		$gps_long=-83.782372;
		$site_lat=32.940701;
		$site_long=-83.782272;
		
		$truck_id=$_POST['truck_id'];
		$truck_name=$_POST['truck_name'];
		$site_lat=$_POST['dest_lat'];
		$site_long=$_POST['dest_long'];
		$gps_lat=$_POST['gps_lat'];
		$gps_long=$_POST['gps_long'];
			
					
		//$resx=mrr_new_geofence_hot_load_tracking_update_by_id($geo_id);
		
		$resx=mrr_test_drive_gps_distance($truck_id,$truck_name,$site_lat,$site_long,$gps_lat,$gps_long);
		
				
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$resx."]]></mrrTab>");		
	}
	
	function load_recent_pn_messages() 
	{		
		$msg_date_from=date("Y-m-d",strtotime("-1 day", time()));
		$msg_date_to=date("Y-m-d",time());
		$msg_truck_id=$_POST['truck_id'];
		$dispatch_id=$_POST['disp_id'];
		
		$sql = "
			select name_truck			
			from trucks
			where id = '".sql_friendly($msg_truck_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		$msg_truck_name=trim($row['name_truck']);
		
		$mrr_val="";
		
		if($msg_truck_id>0)
		{
			$mrr_tab_sent="";
			$mrr_tab_recv="";
			$mrr_tab_sent2="";
			$mrr_tab_recv2="";
			
			//PN version
			$mrr_tab_sent=mrr_get_messages_sent_by_truck($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0, 3);
			$mrr_tab_recv=mrr_get_messages_sent_out_to_truck($msg_date_from, $msg_date_to,  $msg_truck_id, $msg_truck_name, 0, 0, 3);
			
			//GeoTab version
			$mrr_tab_sent2=mrr_get_geotab_messages_sent_by_truck($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0, 3);
			$mrr_tab_recv2=mrr_get_geotab_messages_sent_to_truck_true($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0,3,1);
			
			
			$mrr_val= "
				<div class='pn_messages_block'>
					<table cellpadding='2' cellspacing='2' border='0' width='850'> 
						".$mrr_tab_sent."
					</table>
					<br>
					<table cellpadding='2' cellspacing='2' border='0' width='850'> 
						".$mrr_tab_recv."
					</table>
					<br>
					<table cellpadding='2' cellspacing='2' border='0' width='850'> 
						".$mrr_tab_sent2."
					</table>
					<br>
					<table cellpadding='2' cellspacing='2' border='0' width='850'> 
						".$mrr_tab_recv2."
					</table>
					<br>
					<center><span class='mrr_link_like_on' onClick='mrr_close_pn_msg_displayer(".$dispatch_id.");'>Close</span></center>
				</div>
				<div style='clear:both'></div>
			";
		}
		
		echo $mrr_val;		
	}
	
	function load_recent_phone_only_messages() 
	{		
		$msg_date_from=date("Y-m-d",strtotime("-1 day", time()));
		$msg_date_to=date("Y-m-d",time());
		$msg_truck_id=$_POST['truck_id'];
		$dispatch_id=$_POST['disp_id'];
		$load_id=$_POST['load_id'];
		
		$sql = "
			select name_truck			
			from trucks
			where id = '".sql_friendly($msg_truck_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		$msg_truck_name=trim($row['name_truck']);
		
		$mrr_val="";
		
		if($msg_truck_id>0)
		{
			$mrr_tab_sent="";
			$mrr_tab_recv="";
			
			$mrr_tab_sent=mrr_get_messages_sent_by_truck_phone_only($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0, 3,$load_id,$dispatch_id);	//
					  //mrr_get_messages_sent_by_truck($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0, 3);
			$mrr_tab_recv="";	//mrr_get_messages_sent_out_to_truck($msg_date_from, $msg_date_to,  $msg_truck_id, $msg_truck_name, 0, 0, 3);
			
			$mrr_val= "
				<div class='pn_messages_block'>
					<table cellpadding='2' cellspacing='2' border='0' width='850'> 
						".$mrr_tab_sent."
					</table>
					<br>
					<!--
					<table cellpadding='2' cellspacing='2' border='0' width='850'> 
						".$mrr_tab_recv."
					</table>
					<br>
					-->
					<center><span class='mrr_link_like_on' onClick='mrr_close_phone_msg_displayer(".$dispatch_id.");'>Close</span></center>
				</div>
				<div style='clear:both'></div>
			";
		}
		
		echo $mrr_val;		
	}
	
	function check_city_state_zip_info()
	{
		$zip=$_POST['zip_code'];	
		$state=$_POST['state'];
		$resx=mrr_find_us_zip_code_address($zip,$state);		
		display_xml_response("<rslt>1</rslt><mrrAddr><![CDATA[".$resx."]]></mrrAddr>");	
	}
			
	//added July 2013...Dispatch tasks
	function mrr_dispatcher_tasks_display()
	{
		$user_id=$_POST['user_id'];	
		
		$tab=mrr_find_current_dispatcher_tasks($user_id);
			
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$tab."]]></mrrTab>");	
	}
	
	function mrr_dispatcher_tasks_work_status()
	{
		$task_id=$_POST['task_id'];
		$status_val=$_POST['status_val'];	
		
		$sql="
			update dispatcher_tasks_work set
				active='".sql_friendly($status_val)."'
			where id='".sql_friendly($task_id)."'
		";
		simple_query($sql);
					
		display_xml_response("<rslt>1</rslt>");	
	}
	
	function mrr_dispatcher_tasks_work_update()
	{
		$task_id=$_POST['task_id'];	
		$user_id=$_POST['user_id'];
		$tcode=$_POST['task_code'];
		$tdate=$_POST['task_date'];
		$ttime=$_POST['task_time'];
		$notes=$_POST['task_note'];
		
		$sql="
			insert into dispatcher_tasks_work
				(id,
				linedate_added, 
				linedate, 
				deleted,
                    active,
                    user_id,
                    work_code,
				work,
				task_id)
			values
				(NULL,
				NOW(),
				'".date("Y-m-d H:i", strtotime("".$tdate." ".$ttime.""))."',
				0,
				1,				
				'".sql_friendly($user_id)."',
				'".sql_friendly($tcode)."',
				'".sql_friendly($notes)."',
				'".sql_friendly($task_id)."')
		";
		simple_query($sql);
			
		display_xml_response("<rslt>1</rslt>");		
	}
	
	function mrr_auto_trailer_drop_complete()
	{
		$trailer_id=$_POST['trailer_id'];	
		$sqlx="
			update trailers_dropped set
				drop_completed='1', linedate_completed=NOW()
			where deleted='0'
				and trailer_id='".sql_friendly($_POST['trailer_id'])."'
				and drop_completed='0'
		";
		simple_query($sqlx);
		
		/*
		//drop for current trailer is now completed...when attached to a dispatch.................added Jun 2013.........
		if($_POST['trailer_id'] > 0 && $old_trailer_id!=$_POST['trailer_id'])
		{
			//drop for current trailer is now completed...when attached to a dispatch.	
			$sqlx="
				update trailers_dropped set
					drop_completed='1', linedate_completed=NOW()
				where deleted='0'
					and trailer_id='".sql_friendly($_POST['trailer_id'])."'
					and drop_completed='0'
			";
			simple_query($sqlx);
		}
		//...............................................................................................................
		*/
		
		display_xml_response("<rslt>1</rslt>");		
	}
	
	//================================================================================TRACKING on AJAX Commands Below===========================================================================
	
	function mrr_graduate_load_to_master_load()
	{
		$load_id=$_POST['load_id'];
		$master_load=$_POST['master_load'];
		
		$load_flag="from Master Load to regular load";
		if($master_load>0)		$load_flag="from regular load to Master Load";
		
		$sqlx="
			update load_handler set
				master_load='".sql_friendly($master_load)."'
			where id='".sql_friendly($load_id)."'
		";
		simple_query($sqlx);
		
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_id,0,0,"Load ".$load_id." set ".$load_flag.".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		display_xml_response("<rslt>1</rslt><sql><![CDATA[".$sqlx."]]></sql>");		
	}
	
	function mrr_pn_email_processor()
	{
		$report=mrr_fetch_peoplenet_email_processor();
		$report.="<div style='margin:20px;'><b>Status Update Messages</b></div>";
		$report.=mrr_fetch_peoplenet_email_hourly_updates();
		
		display_xml_response("<rslt>1</rslt><mrrtab><![CDATA[".$report."]]></mrrtab>");	
	}
	
	
	function mrr_pn_update_incoming_message_ignore()
	{
		$msg_id=$_POST['msg_id'];
		$user_id=$_POST['user_id'];	
		
		$sqlx="
			update ".mrr_find_log_database_name()."truck_tracking_msg_history set
				no_response_needed='".sql_friendly($user_id)."'
			where id='".sql_friendly($msg_id)."'
		";
		simple_query($sqlx);
		
		display_xml_response("<rslt>1</rslt><sql><![CDATA[".$sqlx."]]></sql>");	
	}
	
	
	
	function mrr_geotab_update_incoming_message_ignore()
	{
		$msg_id=$_POST['msg_id'];
		$user_id=$_POST['user_id'];	
		
		$sqlx="
			update ".mrr_find_log_database_name()."geotab_messages_received set
				no_response_needed='".sql_friendly($user_id)."'
			where id='".sql_friendly($msg_id)."'
		";
		simple_query($sqlx);
		
		display_xml_response("<rslt>1</rslt><sql><![CDATA[".$sqlx."]]></sql>");	
	}
	
	function mrr_special_ops_copy_stops_from_load()
	{
		global $datasource;

		$sel_load=$_POST['sel_load'];
		$cur_load=$_POST['cur_load'];	
		$moder=$_POST['moder'];
		
		$stops_found=0;
		
		$base_date=date("Y-m-d");
		$base_day=date("Ymd");
		
		if($sel_load > 0 && $cur_load > 0)
		{        		
     		if($moder==1)
     		{	//copies missing stops from selected load to current load
     			    			
     			//array to capture stops to change dates
     			$fill_stops=0;
     			$fill_ids[0]=0;
     			$pre_stops=0;
     			$mid_stops=0;
     			$post_stops=0;
     			$progress=0;    			
     			     			
     			//first get current stops to fill around...     			
     			$start_date="";     			
     			$end_date="";
     			$start_local="";     			
     			$end_local="";
     			$stops=0;
     			$days_apart=mrr_find_days_between_common_load_stops($sel_load,$cur_load);	
     			$days_adder=0;
     			$first_real_id=0;    	
     			$last_real_id=0;		
     			
     			$sqlx="
          			select load_handler_stops.*
          			from load_handler_stops
          			where load_handler_stops.load_handler_id='".sql_friendly($cur_load)."'
          				and load_handler_stops.deleted=0
          			order by load_handler_stops.linedate_pickup_eta asc
          		";
          		$datax=simple_query($sqlx);
          		while($rowx=mysqli_fetch_array($datax))
          		{   //only need first stop       			
          			if($stops==0)	
          			{          				
          				$start_date=$rowx['linedate_pickup_eta'];
          				$start_local=trim($rowx['shipper_name']); 
          				
          				$first_real_id=$rowx['id']; 
          			}
          			$end_date=$rowx['linedate_pickup_eta'];
          			$end_local=trim($rowx['shipper_name']);    
          			
          			$last_real_id=$rowx['id'];      			        			
          			$stops++;
     			}     			
     			//.........................................
     			     			
     			//now find stops that are missing from selected template...
     			$sqlx="
          			select load_handler_stops.*
          			from load_handler_stops
          			where load_handler_stops.load_handler_id='".sql_friendly($sel_load)."'
          				and load_handler_stops.deleted=0
          				and load_handler_stops.master_load_include=1
          			order by load_handler_stops.linedate_pickup_eta asc
          		";
          		$datax=simple_query($sqlx);
          		while($rowx=mysqli_fetch_array($datax))
          		{               			
          			$mrr_test_notes="";
          			$prestop=0;
     				$midstop=0;
     				$poststop=0;
     				
     				$mrr_use_this_dater=$rowx['linedate_pickup_eta'];
          			
          			if($rowx['master_load_pickup_eta']!='0000-00-00 00:00:00')
          			{
          				$mrr_use_this_dater=$rowx['master_load_pickup_eta'];
          			} 
          			
          			if($start_local!=trim($rowx['shipper_name']) && $progress==0)
          			{
          				$prestop=1;			//happens before the curent first stop...
          				$pre_stops++;  
          				          				
          				$pickup=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($mrr_use_this_dater)));
               			$window_start=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_start'])));
               			$window_end=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_end'])));  
               			
               			if($rowx['linedate_appt_window_start'] < '2000-01-01 00:00:00')		$window_start="0000-00-00 00:00:00";
          				if($rowx['linedate_appt_window_end'] < '2000-01-01 00:00:00')		$window_end="0000-00-00 00:00:00";         				
          			}
          			elseif($start_local==trim($rowx['shipper_name']) && $progress==0)
          			{
          				$progress=1;			//should be the first stop... 			
          			}
          			elseif($start_local!=trim($rowx['shipper_name']) && $progress==1 && $end_local!=trim($rowx['shipper_name']))
          			{
          				$midstop=1;			//stop(s) between the first and last stop...
          				$mid_stops++;
          				               			
               			$pickup=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($mrr_use_this_dater)));
               			$window_start=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_start'])));
               			$window_end=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_end'])));        
               			
               			if($rowx['linedate_appt_window_start'] < '2000-01-01 00:00:00')		$window_start="0000-00-00 00:00:00";
          				if($rowx['linedate_appt_window_end'] < '2000-01-01 00:00:00')		$window_end="0000-00-00 00:00:00";  				
          			}
          			elseif($end_local==trim($rowx['shipper_name']) && $progress==1)
          			{
          				$progress=2;			//should be the last stop...
          			}
          			elseif($end_local!=trim($rowx['shipper_name']) && $progress==2)
          			{
          				$poststop=1;			//happens after the current last stop....
          				$post_stops++;	
          				
          				$pickup=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($mrr_use_this_dater)));
               			$window_start=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_start'])));
               			$window_end=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_end'])));   
               			
               			if($rowx['linedate_appt_window_start'] < '2000-01-01 00:00:00')		$window_start="0000-00-00 00:00:00";
          				if($rowx['linedate_appt_window_end'] < '2000-01-01 00:00:00')		$window_end="0000-00-00 00:00:00";
          			}
          			         			
          			$mrr_test_notes="Add ".$pre_stops." before + ".$mid_stops." middle + ".$post_stops." after.";
          			

          			
          			$mrr_test_notes="";
          			 
          			if($prestop==1 || $midstop==1 || $poststop==1)
          			{ 	//only add the stop if the piece is missing...               			          			
               			$sqlv = "
               				insert into load_handler_stops
               					(load_handler_id,
               					trucks_log_id,
               					shipper_name,
               					shipper_address1,
               					shipper_address2,
               					shipper_city,
               					shipper_state,
               					shipper_zip,
               					deleted,
               					linedate_added,
               					created_by_user_id,
               					stop_type_id,
               					stop_phone,
               					directions,
               					pcm_miles,
               					latitude,
               					longitude,
               					linedate_pickup_eta,
               					start_trailer_id,
               					end_trailer_id,
               					timezone_offset,
               					timezone_offset_dst,
               					appointment_window,
               					linedate_appt_window_start,
               					linedate_appt_window_end,
               					stop_grade_note)
               					
               				values ('".sql_friendly($cur_load)."',
               					0,
               					'".sql_friendly($rowx['shipper_name'])."',
               					'".sql_friendly($rowx['shipper_address1'])."',
               					'".sql_friendly($rowx['shipper_address2'])."',
               					'".sql_friendly($rowx['shipper_city'])."',
               					'".sql_friendly($rowx['shipper_state'])."',
               					'".sql_friendly($rowx['shipper_zip'])."',
               					0,
               					now(),
               					'".sql_friendly($_SESSION['user_id'])."',
               					'".sql_friendly($rowx['stop_type_id'])."',
               					'".sql_friendly($rowx['stop_phone'])."',
               					'',
               					'".sql_friendly($rowx['pcm_miles'])."',
               					'".sql_friendly($rowx['latitude'])."',
               					'".sql_friendly($rowx['longitude'])."',
               					'".sql_friendly($pickup)."',
               					'0',
               					'0',
               					'".sql_friendly($rowx['timezone_offset'])."',
               					'".sql_friendly($rowx['timezone_offset_dst'])."',
               					'".sql_friendly($rowx['appointment_window'])."',
               					'".sql_friendly($window_start)."',
               					'".sql_friendly($window_end)."',
               					'".sql_friendly($mrr_test_notes)."')
               			";		
               				//".sql_friendly($rowx['start_trailer_id'])."
               				//".sql_friendly($rowx['end_trailer_id'])."
               			simple_query($sqlv);
               			$stops_found++;
               			               			
     					$fill_ids[$fill_stops]= mysqli_insert_id($datasource);
     					$fill_stops++;
          			}
          		}
     			
     		}
     		else
     		{	//copies all stops from selected load to current load...regardless of existing stops...
         			$base_diff_days=0;
         			$stop_start="00000000";  
         			     
         			$use_today=date("Y-m-d"); 
         			$first_dated="";	
         			$new_first_date="";	
         			$days_apart=0;	
         			   			
         			
         			$sqlx="
          			select load_handler_stops.*
          			from load_handler_stops
          			where load_handler_stops.load_handler_id='".sql_friendly($sel_load)."'
          				and load_handler_stops.deleted=0
          				and load_handler_stops.master_load_include=1
          			order by load_handler_stops.linedate_pickup_eta asc
          		";
          		$datax=simple_query($sqlx);
          		while($rowx=mysqli_fetch_array($datax))
          		{          			
          			$mrr_test_notes="";
          			
          			$mrr_use_this_dater=$rowx['linedate_pickup_eta'];
          			
          			if($rowx['master_load_pickup_eta']!='0000-00-00 00:00:00')
          			{
          				$mrr_use_this_dater=$rowx['master_load_pickup_eta'];
          			}          			
          			
          			if($stops_found==0)
          			{
          				$org_date=date("Y-m-d",strtotime($mrr_use_this_dater));
          				$first_dated=$org_date." 00:00:00";         				
          				
          				$new_first_date=str_replace($org_date,$use_today,$mrr_use_this_dater);
          				$first_dated_only=date("Y-m-d",strtotime($new_first_date))." 00:00:00"; 
          				          				
          				$days_apart=strtotime($first_dated_only) - strtotime($first_dated);				//should be number of days different between new date and original first stop date (IN SECONDS)
          				$days_apart=(int) ($days_apart / (60 * 60 * 24));								//converted to days.  IF was at 5PM on 3/21/14... and today is 4/28/14...first time should be today at 5PM.
          				
          				$pickup=$new_first_date;
          				$window_start=date("Y-m-d H:i",strtotime("+".$days_apart." day", strtotime($rowx['linedate_appt_window_start'])));
          				$window_end=date("Y-m-d H:i",strtotime("+".$days_apart." day", strtotime($rowx['linedate_appt_window_end'])));
          				     
          				if($rowx['linedate_appt_window_start'] < '2000-01-01 00:00:00')		$window_start="0000-00-00 00:00:00";
          				if($rowx['linedate_appt_window_end'] < '2000-01-01 00:00:00')		$window_end="0000-00-00 00:00:00";
          				
          				$mrr_test_notes="First Stop: ".$days_apart." Days";    				
          			}
          			else
          			{          				
          				$days_adder=0;
          				$date_only=date("Y-m-d",strtotime($mrr_use_this_dater))." 00:00:00";
          				
          				$days_adder=strtotime($date_only) - strtotime($first_dated);					//offset number of days between original stop start and this one...
          				$days_adder=(int) ($days_adder / (60 * 60 * 24));								//convert to days.
          				
          				
          				//use the days difference to add to each of the other pickup times...          				
          				$pickup=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($mrr_use_this_dater)));
          				$window_start=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_start'])));
          				$window_end=date("Y-m-d H:i",strtotime("+".($days_apart + $days_adder)." day", strtotime($rowx['linedate_appt_window_end'])));
          				
          				if($rowx['linedate_appt_window_start'] < '2000-01-01 00:00:00')		$window_start="0000-00-00 00:00:00";
          				if($rowx['linedate_appt_window_end'] < '2000-01-01 00:00:00')		$window_end="0000-00-00 00:00:00";
          				
          				$mrr_test_notes="--".($days_apart+$days_adder)." Days from ".$mrr_use_this_dater."";   
          			}
          			
          			$mrr_test_notes="";
          			          			
          			$sqlv = "
          				insert into load_handler_stops
          					(load_handler_id,
          					trucks_log_id,
          					shipper_name,
          					shipper_address1,
          					shipper_address2,
          					shipper_city,
          					shipper_state,
          					shipper_zip,
          					deleted,
          					linedate_added,
          					created_by_user_id,
          					stop_type_id,
          					stop_phone,
          					directions,
          					pcm_miles,
          					latitude,
          					longitude,
          					linedate_pickup_eta,
          					start_trailer_id,
          					end_trailer_id,
          					timezone_offset,
          					timezone_offset_dst,
          					appointment_window,
          					linedate_appt_window_start,
          					linedate_appt_window_end,
          					stop_grade_note)
          					
          				values ('".sql_friendly($cur_load)."',
          					0,
          					'".sql_friendly($rowx['shipper_name'])."',
          					'".sql_friendly($rowx['shipper_address1'])."',
          					'".sql_friendly($rowx['shipper_address2'])."',
          					'".sql_friendly($rowx['shipper_city'])."',
          					'".sql_friendly($rowx['shipper_state'])."',
          					'".sql_friendly($rowx['shipper_zip'])."',
          					0,
          					now(),
          					'".sql_friendly($_SESSION['user_id'])."',
          					'".sql_friendly($rowx['stop_type_id'])."',
          					'".sql_friendly($rowx['stop_phone'])."',
          					'Added ".$days_apart." Days',
          					'".sql_friendly($rowx['pcm_miles'])."',
          					'".sql_friendly($rowx['latitude'])."',
          					'".sql_friendly($rowx['longitude'])."',
          					'".sql_friendly($pickup)."',
          					'0',
          					'0',
          					'".sql_friendly($rowx['timezone_offset'])."',
          					'".sql_friendly($rowx['timezone_offset_dst'])."',
          					'".sql_friendly($rowx['appointment_window'])."',
          					'".sql_friendly($window_start)."',
          					'".sql_friendly($window_end)."',
          					'".sql_friendly($mrr_test_notes)."')
          			";		
          				//".sql_friendly($rowx['start_trailer_id'])."
          				//".sql_friendly($rowx['end_trailer_id'])."
          			simple_query($sqlv);
          			$stops_found++;
          		}
     		}
     		
		}
		display_xml_response("<rslt>1</rslt><stops>".$stops_found."</stops>");	//	<sql><![CDATA[".$sqlx."]]></sql>
	}
	
	function mrr_preplan_auto_set_driver_for_load()
	{
		$load_id=$_POST['load_id'];
		$preplan_driver_id=$_POST['driver_id'];
		$preplan_driver_num=$_POST['driver_num'];
		
		//update all drivers
		$adder="
			preplan_driver_id='".sql_friendly($preplan_driver_id)."',
			preplan_driver2_id='".sql_friendly($preplan_driver_id)."',
			preplan_leg2_driver_id='".sql_friendly($preplan_driver_id)."',
			preplan_leg2_driver2_id='".sql_friendly($preplan_driver_id)."',
		";
		//if driver number is set, only update that driver ID slot...
		if($preplan_driver_num==1)	$adder=" preplan_driver_id='".sql_friendly($preplan_driver_id)."', ";
		if($preplan_driver_num==2)	$adder=" preplan_driver2_id='".sql_friendly($preplan_driver_id)."', ";
		if($preplan_driver_num==3)	$adder=" preplan_leg2_driver_id='".sql_friendly($preplan_driver_id)."', ";
		if($preplan_driver_num==4)	$adder=" preplan_leg2_driver2_id='".sql_friendly($preplan_driver_id)."', ";
				
		$preplan=1;
		if($preplan_driver_id == 0 && $preplan_driver_num==0)	$preplan=0;
		
		$sqlx="
			update load_handler set
				".$adder."
				preplan='".sql_friendly($preplan)."'
			
			where load_handler.id='".sql_friendly($load_id)."'
		";
		simple_query($sqlx);
		
		
		$sql="
			select (preplan_driver_id + preplan_driver2_id + preplan_leg2_driver_id + preplan_leg2_driver2_id) as planned_drivers
			from load_handler			
			where load_handler.id='".sql_friendly($load_id)."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			if($row['planned_drivers'] > 0)	
			{
				$sql="
          			update load_handler set
          				preplan='1'          			
          			where load_handler.id='".sql_friendly($load_id)."'
          		";
			}
			else
			{
				$sql="
          			update load_handler set
          				preplan='0'          			
          			where load_handler.id='".sql_friendly($load_id)."'
          		";
			}
			simple_query($sql);
		}
		
		display_xml_response("<rslt>1</rslt><ds><![CDATA[Number=".$preplan_driver_num." and ID=".$preplan_driver_id."]]></ds><sql><![CDATA[".$sqlx."]]></sql>");	//	
	}
	
	function mrr_display_complete_trailer_drop()
	{
		$drop_id=$_POST['drop_id'];	
		
		$sql="
			update trailers_dropped set
				drop_completed='1'	, linedate_completed=NOW()		
			where id='".sql_friendly($drop_id)."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");	//	<sql><![CDATA[".$sqlx."]]></sql>	
	}
	
	function mrr_display_previous_trailer_drops()
	{
		$trailer_id=$_POST['trailer_id'];	
		
		$html="";
		$drops=0;
		
		if($trailer_id>0)
		{				
			$sql="
				select *
				from trailers_dropped				
				where trailer_id='".sql_friendly($trailer_id)."'
					and deleted=0
					and drop_completed=0
				order by linedate desc
				limit 20
			";
			$data=simple_query($sql);
			$drops=mysqli_num_rows($data);
			if($drops>0)
			{
				$html.="
					<div class='section_heading'><span class='alert'><b>Trailer Dropped in ".$drops." Location(s):</b></span></div>
					<table width='100%' cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td valign='top'><b>Date</b></td>
						<td valign='top'><b>City</b></td>
						<td valign='top'><b>State</b></td>
						<td valign='top'><b>Zip</b></td>
						<td valign='top'><b>Dedicated</b></td>
						<td valign='top'><b>Notes</b></td>
						<td valign='top'><b>Completed</b></td>
					</tr>
					";
			}
			while($row=mysqli_fetch_array($data))
			{
				$html.="
					<tr>
						<td valign='top'><a href='trailer_drop.php?id=".$row['id']."' target='_blank'>".date("m/d/Y H:i",strtotime($row['linedate']))."</a></td>
						<td valign='top'>".$row['location_city']."</td>
						<td valign='top'>".$row['location_state']."</td>
						<td valign='top'>".$row['location_zip']."</td>
						<td valign='top'>".($row['dedicated_trailer'] > 0 ? "Yes" : "No")."</td>
						<td valign='top'>".$row['notes']."</td>
						<td valign='top'>".($row['drop_completed'] > 0 ? "Yes" : "No <span class='mrr_link_like_on' onClick='mrr_complete_this_trailer_drop(".$row['id'].");'>Complete</span>")."</td>
					</tr>
				";
			}
			if($drops>0)
			{
				$html.="</table>";	
			}	
			
		}
		display_xml_response("<rslt>1</rslt><mrrTab><![CDATA[".$html."]]></mrrTab><drops><![CDATA[".$drops."]]></drops>");	//	
	}
	
	function mrr_build_run_miles_by_zip()
	{
		$ziparray = explode(",",$_POST['ziplist']);
		$rslt=0;
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		$stoparray_loc1 = array();
		$stoparray_loc2 = array();
		$stoparray_st1 = array();
		$stoparray_st2 = array();
		$stoparray_hours = array();
				
		$last_zip = "";
		$stop_counter = 0;
		$counter = 0;
		$first_stop = "";
		$stop_minutes = 0;
		foreach($ziparray as $zip) 
		{			
			if($first_stop == '' && $zip != '') 	$first_stop = $zip;
			
			if($zip != '') 
			{

				
				if($last_zip=="")
				{
					$dres=mrr_process_zip_code_to_zip_code($first_stop,$zip);
					$stoparray_dist[$counter]=ceil($dres['miles']);
					$stoparray_time[$counter]=$dres['time'];
					$stoparray_loc1[$counter]=$dres['city1'];
					$stoparray_loc2[$counter]=$dres['city2'];
					$stoparray_st1[$counter]=$dres['state1'];
					$stoparray_st2[$counter]=$dres['state2'];
					
					$comp_hrs=mrr_calc_time_into_hours($dres['time']);
					
					$stoparray_hours[$counter]=$comp_hrs;
					$travel_time+=$comp_hrs;
					$traveldist+=ceil($dres['miles']);
				}
				else
				{
					$dres=mrr_process_zip_code_to_zip_code($last_zip,$zip);	
					$stoparray_dist[$counter]=ceil($dres['miles']);	
					$stoparray_time[$counter]=$dres['time'];
					$stoparray_loc1[$counter]=$dres['city1'];
					$stoparray_loc2[$counter]=$dres['city2'];
					$stoparray_st1[$counter]=$dres['state1'];
					$stoparray_st2[$counter]=$dres['state2'];
					
					$comp_hrs=mrr_calc_time_into_hours($dres['time']);
					
					$stoparray_hours[$counter]=$comp_hrs;
					$travel_time+=$comp_hrs;
					$traveldist+=ceil($dres['miles']);
				}
				
				$last_zip = $zip;
				$stop_counter++;
			}
			$counter++;
			$rslt=1;
		}
		
		
		
		
		$return_val = "
			<rslt>$rslt</rslt>
			<Miles>$traveldist</Miles>
			<TravelTime>$travel_time</TravelTime>
		";
		
		$my_cntr=count($stoparray_dist);
		for($i=0;$i < $my_cntr; $i++)
		{
			$return_val .= "
				<StopEntry>
					<StopDistance>".$stoparray_dist[$i]."</StopDistance>
					<StopLoc1>".$stoparray_loc1[$i]."</StopLoc1>
					<StopLoc2>".$stoparray_loc2[$i]."</StopLoc2>
					<StopState1>".$stoparray_st1[$i]."</StopState1>
					<StopState2>".$stoparray_st2[$i]."</StopState2>
					<StopTime>".$stoparray_time[$i]."</StopTime>
					<StopHours>".$stoparray_hours[$i]."</StopHours>
					<StopLine></StopLine>
				</StopEntry>
			";	
		}	
		
		display_xml_response($return_val);
	}
	
	function mrr_build_run_miles_by_zip_alt()
	{
		$ziparray = explode(",",$_POST['ziplist']);
		$cityarray = explode(",",$_POST['citylist']);
		$statearray = explode(",",$_POST['statelist']);
		$latarray = explode(",",$_POST['latlist']);
		$longarray = explode(",",$_POST['longlist']);
		
		$zip_cntr=count($ziparray);
		$city_cntr=count($cityarray);
		$state_cntr=count($statearray);
		$lat_cntr=count($latarray);
		$long_cntr=count($longarray);
		
				
		$rslt=0;
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		$stoparray_loc1 = array();
		$stoparray_loc2 = array();
		$stoparray_st1 = array();
		$stoparray_st2 = array();
		$stoparray_hours = array();
		
		$stoparray_lat1 = array();
		$stoparray_lat2 = array();
		$stoparray_long1 = array();
		$stoparray_long2 = array();
				
		$stop_counter = 0;
		$counter = 0;
		$stop_minutes = 0;
				
		$gps[0]['city']="";				
		$gps[0]['zip']="";	
		$gps[0]['state']="";	
		$gps[0]['lat']="";	
		$gps[0]['long']="";	
		
		$last_city = "";
		$last_state = "";
		$last_zip = "";
		$last_lat = "";
		$last_long = "";
		
		$mph=35;
		
		for($i=0; $i < $zip_cntr; $i++)
		{
			$mycity=$cityarray[$i];
			$mystate=$statearray[$i];
			$myzip=$ziparray[$i];
			$mylat=$latarray[$i];
			$mylong=$longarray[$i];
			$dist=0;
			
			if(trim($myzip)=="" && trim($mylat)!="" && trim($mylong)!="")
			{
					if($i == 0)
     				{	//start trip...0 for distance and time.
     					$stoparray_dist[$counter]=0;
     					$stoparray_time[$counter]=0;
     					$stoparray_loc1[$counter]=$mycity;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$mystate;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$comp_hrs=0;
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=0;
     					
     					$stoparray_lat1[$counter]=$mylat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$mylong;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				else
     				{
     					$dist=mrr_promiles_get_file_contents($mylat,$mylong,$last_lat,$last_long);	
     					$comp_hrs=number_format(($dist / $mph),2);
     					$comp_hrs=str_replace(",","",$comp_hrs);
     					
     					$stoparray_dist[$counter]=$dist;
     					$stoparray_time[$counter]=$comp_hrs;
     					$stoparray_loc1[$counter]=$last_city;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$last_state;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=$dist;	
     					
     					$stoparray_lat1[$counter]=$last_lat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$last_long;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				$stop_counter++;	
			}
			else
			{
				$res=mrr_get_promiles_gps_from_address('',trim($mycity),trim($mystate),trim($myzip));
     			if($res['status']==1)
     			{				
     				$mycity=$res['city'];			$gps[$i]['city']=$res['city'];
     				$mystate=$res['state'];			$gps[$i]['state']=$res['state'];	
     				$myzip=$res['zip'];				$gps[$i]['zip']=$res['zip'];
     				$mylat=$res['lat'];				$gps[$i]['lat']=$res['lat'];	
     				$mylong=$res['long'];			$gps[$i]['long']=$res['long'];
     				
     				if($i == 0)
     				{	//start trip...0 for distance and time.
     					$stoparray_dist[$counter]=0;
     					$stoparray_time[$counter]=0;
     					$stoparray_loc1[$counter]=$mycity;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$mystate;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$comp_hrs=0;
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=0;
     					
     					$stoparray_lat1[$counter]=$mylat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$mylong;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				else
     				{
     					$dist=mrr_promiles_get_file_contents($mylat,$mylong,$last_lat,$last_long);	
     					$comp_hrs=number_format(($dist / $mph),2);
     					$comp_hrs=str_replace(",","",$comp_hrs);
     					
     					$stoparray_dist[$counter]=$dist;
     					$stoparray_time[$counter]=$comp_hrs;
     					$stoparray_loc1[$counter]=$last_city;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$last_state;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=$dist;	
     					
     					$stoparray_lat1[$counter]=$last_lat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$last_long;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				$stop_counter++;										
     			}	
			}
			
			$last_city = $mycity;
			$last_state = $mystate;
			$last_zip = $myzip;
			$last_lat = $mylat;
			$last_long = $mylong;	
			$counter++;
			$rslt=1;
		}
		
		$return_val = "
			<rslt>$rslt</rslt>
			<Miles>$traveldist</Miles>
			<TravelTime>$travel_time</TravelTime>
		";
		
		$my_cntr=count($stoparray_dist);
		for($i=0;$i < $my_cntr; $i++)
		{
			$return_val .= "
				<StopEntry>
					<StopDistance>".$stoparray_dist[$i]."</StopDistance>
					<StopLoc1>".$stoparray_loc1[$i]."</StopLoc1>
					<StopLoc2>".$stoparray_loc2[$i]."</StopLoc2>
					<StopState1>".$stoparray_st1[$i]."</StopState1>
					<StopState2>".$stoparray_st2[$i]."</StopState2>
					<StopTime>".$stoparray_time[$i]."</StopTime>
					<StopHours>".$stoparray_hours[$i]."</StopHours>
					<StopLine></StopLine>
				</StopEntry>
			";	
		}	
		
		display_xml_response($return_val);
	}
	
	function mrr_build_run_miles_by_zip_alt2()
	{
		$localarray = explode(";",$_POST['locallist']);
				
		$local_cntr=count($localarray);		
				
		$rslt=0;
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		$stoparray_full_1 = array();
		$stoparray_full_2 = array();
		$stoparray_loc1 = array();
		$stoparray_loc2 = array();
		$stoparray_st1 = array();
		$stoparray_st2 = array();
		$stoparray_hours = array();
		
		$stoparray_lat1 = array();
		$stoparray_lat2 = array();
		$stoparray_long1 = array();
		$stoparray_long2 = array();
				
		$stop_counter = 0;
		$counter = 0;
		$stop_minutes = 0;
				
		$gps[0]['city']="";				
		$gps[0]['zip']="";	
		$gps[0]['state']="";	
		$gps[0]['lat']="";	
		$gps[0]['long']="";	
		
		$last_local = "";
		$last_city = "";
		$last_state = "";
		$last_zip = "";
		$last_lat = "";
		$last_long = "";
		
		$mph=35;
		$calc_errors="";
		
		for($i=0; $i < $local_cntr; $i++)
		{
			$mylocation=$localarray[$i];
			$mycity="";
			$mystate="";
			$myzip="";
			$mylat="";
			$mylong="";
			$dist=0;
			
			if(trim($mylocation)!="")
			{     			
     			$zip_only=0;
     			if(substr_count($mylocation,",") > 0)
     			{
     				$poser=strpos($mylocation,",");
     				
     				$sub1=substr($mylocation,0,$poser);
     				$sub2=substr($mylocation,$poser);
     				
     				$sub1=trim(str_replace(",","",$sub1));
     				$sub2=trim(str_replace(",","",$sub2));
     				
     				$mycity=$sub1;
     				//now get state
     				if(substr_count($sub2," ") > 0)
     				{	//state and zip code here
     					$pose2=strpos($sub2," ");
     					$sub3=substr($sub2,0,$pose2);
     					$sub4=substr($sub2,$pose2);
     					
     					$sub3=trim(str_replace(",","",$sub3));
     					$sub4=trim(str_replace(",","",$sub4));
     					     					
     					$mystate=$sub3;
     					
     					if(strlen($sub4) <= 5 && substr_count($sub4,"-")==0)
     					{
     						$myzip=$sub4;
     					}
     					elseif(strlen($sub4) <= 9 && substr_count($sub4,"-") > 0)
     					{
     						$myzip=$sub4;
     					}     					
     				}
     				else
     				{
     					$mystate=$sub2;
     				}								
     			}
     			else
     			{
     				$mycity=$mylocation;
     				if(is_numeric($mylocation))
     				{
     					$mystate="";
   						$myzip="";
   						$zip_only=1;
     				}
     				else
     				{
     					$calc_errors.="No State Found in (".$mycity.").  ";
     				}
     			}		
     			
     			if($mycity!="" && ($mystate!="" || $zip_only==1))
     			{
     				//attempt to find address in table first...avoids slower API call.
     				if($zip_only==1)
     				{
     					$gps_res=mrr_pro_miles_addr_lookup("","",$mycity);	
     				}
     				else
     				{
     					$gps_res=mrr_pro_miles_addr_lookup($mycity,$mystate,$myzip);
     				}
     				
     				
     				if($gps_res['found']==0)
     				{	//not found, so try to find the address and GPS info from ProMiles.
     				     $res=mrr_get_promiles_gps_from_address('',trim($mycity),trim($mystate),trim($myzip));
          				if($res['status']==1)
          				{				
          					//$mycity=$res['city'];			$gps[$i]['city']=$res['city'];
          					//$mystate=$res['state'];		$gps[$i]['state']=$res['state'];	
          					//$myzip=$res['zip'];			$gps[$i]['zip']=$res['zip'];
          					$mylat=$res['lat'];				$gps[$i]['lat']=$res['lat'];	
          					$mylong=$res['long'];			$gps[$i]['long']=$res['long'];
          					
          					$gps_res2=mrr_get_promiles_reverse_geocode_from_gps($mylat,$mylong);
     						$mylocation="".$gps_res2['city'].", ".$gps_res2['state']." ".$gps_res2['zip']."";
     						
     						//store so we don't have to use the API call again...for this location at least.
     						$resid=mrr_pro_miles_new_location($mylat,$mylong,$gps_res2['city'],$gps_res2['state'],$gps_res2['zip']);
     						
          				}
          				if(trim($res['error'])!="")
          				{
          					$calc_errors.=" ...Geocode Error [".trim($res['error'])."]...  ";
          				}
     				}
     				else
     				{	//found, so skip API call to find address and GPS info for location.
     					$mylat=$gps_res['latitude'];				$gps[$i]['lat']=$gps_res['latitude'];	
          				$mylong=$gps_res['longitude'];			$gps[$i]['long']=$gps_res['longitude'];	
          				
          				$mylocation="".$gps_res['city'].", ".$gps_res['state']." ".$gps_res['zip']."";
     				}
     			}        			
     			
     			if(trim($mylat)!="" && trim($mylong)!="")
     			{
     				if($i == 0)
     				{	//start trip...0 for distance and time.
     					$stoparray_dist[$counter]=0;
     					$stoparray_time[$counter]=0;
     					$stoparray_loc1[$counter]=$mycity;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$mystate;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$stoparray_full_1[$counter] =trim($mylocation);
						$stoparray_full_2[$counter] =trim($mylocation);
     					
     					$comp_hrs=0;
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=0;
     					
     					$stoparray_lat1[$counter]=$mylat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$mylong;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				else
     				{
     					$dist=mrr_promiles_get_file_contents($mylat,$mylong,$last_lat,$last_long);	
     					$comp_hrs=number_format(($dist / $mph),2);
     					$comp_hrs=str_replace(",","",$comp_hrs);
     					
     					$stoparray_dist[$counter]=$dist;
     					$stoparray_time[$counter]=$comp_hrs;
     					$stoparray_loc1[$counter]=$last_city;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$last_state;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$stoparray_full_1[$counter] =trim($last_local);
						$stoparray_full_2[$counter] =trim($mylocation);
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=$dist;	
     					
     					$stoparray_lat1[$counter]=$last_lat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$last_long;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				$stop_counter++;	
     				
     				$last_local=$mylocation;
     				$last_city = $mycity;
					$last_state = $mystate;
					$last_zip = $myzip;
					$last_lat = $mylat;
					$last_long = $mylong;	
					$counter++;
					$rslt=1;
     			}
     			else
     			{
					$stoparray_dist[$counter]=0;
					$stoparray_time[$counter]=0;
					$stoparray_loc1[$counter]="";
					$stoparray_loc2[$counter]="";
					$stoparray_st1[$counter]="";
					$stoparray_st2[$counter]="";
					
					$stoparray_full_1[$counter] ="";
					$stoparray_full_2[$counter] ="";
					
					$comp_hrs=0;
					
					$stoparray_hours[$counter]=$comp_hrs;
					$travel_time+=$comp_hrs;
					$traveldist+=0;
					
					$stoparray_lat1[$counter]="";
					$stoparray_lat2[$counter]="";
					$stoparray_long1[$counter]="";
					$stoparray_long2[$counter]="";
					$counter++;
     			}     			  			
			}		
		}
		
		$return_val = "
			<rslt>$rslt</rslt>
			<Miles>$traveldist</Miles>
			<TravelTime>$travel_time</TravelTime>
			<Errors><![CDATA[$calc_errors]]></Errors>
		";
		
		$my_cntr=count($stoparray_dist);
		for($i=0;$i < $my_cntr; $i++)
		{
			$return_val .= "
				<StopEntry>
					<StopDistance>".$stoparray_dist[$i]."</StopDistance>
					<StopLoc1><![CDATA[".$stoparray_loc1[$i]."]]></StopLoc1>
					<StopLoc2><![CDATA[".$stoparray_loc2[$i]."]]></StopLoc2>
					<StopState1><![CDATA[".$stoparray_st1[$i]."]]></StopState1>
					<StopState2><![CDATA[".$stoparray_st2[$i]."]]></StopState2>
					<StopFull1><![CDATA[".$stoparray_full_1[$i]."]]></StopFull1>
					<StopFull2><![CDATA[".$stoparray_full_2[$i]."]]></StopFull2>
					<StopTime>".$stoparray_time[$i]."</StopTime>
					<StopHours>".$stoparray_hours[$i]."</StopHours>
					<StopLine>".$i."</StopLine>
				</StopEntry>
			";	
		}	
		
		display_xml_response($return_val);
	}	
	
	function mrr_value_update_equip_value_id()
	{
		$equip_id=$_POST['equip_id'];
		$equip_value=$_POST['new_value'];	
		
		$equip_value=money_strip($equip_value);
		
		$sql="
			update equipment_history set
				equipment_value='".sql_friendly($equip_value)."'			
			where id='".sql_friendly($equip_id)."'
			";
		simple_query($sql);		
		
		$return_val = "
			<rslt>1</rslt>
			<EquipID>$equip_id</EquipID>
			<EquipValue>$equip_value</EquipValue>
		";
		display_xml_response($return_val);
	}
	
	function mrr_pro_miles_dist_calc()
	{
		$due=$_POST['due'];
		$mph=$_POST['mph'];
		$lat1=$_POST['lat1'];
		$long1=$_POST['long1'];
		$lat2=$_POST['lat2'];
		$long2=$_POST['long2'];
				
		$dist=mrr_promiles_get_file_contents($lat1,$long1,$lat2,$long2);
		
		$dist_eta=0;
		if($mph>0)	$dist_eta=$dist / $mph;
		$dist_due=$due - $dist_eta;
				
		$return_val = "
			<rslt>1</rslt>
			<mrrDist><![CDATA[".number_format($dist,2)."]]></mrrDist>
			<mrrDue><![CDATA[".number_format($dist_due,2)."]]></mrrDue>
			<mrrMPH><![CDATA[".number_format($dist_eta,2)."]]></mrrMPH>
		";
		display_xml_response($return_val);	
	}
	
	function mrr_update_pn_mileage_values()
	{
		$run=$_POST['run'];
		
		$cntrx=mrr_pull_all_active_geofencing_rows_alt_no_display(0);
		
		
		$return_val = "
			<rslt>1</rslt>
		";
		
		display_xml_response($return_val);		
	}
	function mrr_quick_message_form_display()
	{
		$load_id=$_POST['load_id'];
		$disp_id=$_POST['disp_id'];
		$driver_id=$_POST['driver_id'];
		$truck_id=$_POST['truck_id'];
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$msg_id=$_POST['msg_id'];	
		
		$mini_mode=0;
		if(!isset($_POST['mini_mode']))		$_POST['mini_mode']=0;
		$mini_mode=$_POST['mini_mode'];
		
		if($date_to=="")	$date_to=date("m/d/Y",strtotime("+1 day",time()));	
		
		$html=mrr_get_messages_by_truck_mini($truck_id, $date_from, $date_to,$driver_id,$load_id,$disp_id,$msg_id,$mini_mode);
		echo $html;
		
		
		$return_val = "
			<rslt>1</rslt>
			<mrrTab><![CDATA[".$html."]]></mrrTab>
		";
		//display_xml_response($return_val);
	}
		
	function mrr_quick_message_form_display_geotab()
	{
		$load_id=$_POST['load_id'];
		$disp_id=$_POST['disp_id'];
		$driver_id=$_POST['driver_id'];
		$truck_id=$_POST['truck_id'];
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		$msg_id=$_POST['msg_id'];	
		
		$mini_mode=0;
		if(!isset($_POST['mini_mode']))		$_POST['mini_mode']=0;
		$mini_mode=$_POST['mini_mode'];
		
		if($date_to=="")	$date_to=date("m/d/Y",strtotime("+1 day",time()));	
		
		$html=mrr_get_messages_by_truck_mini_geotab($truck_id, $date_from, $date_to,$driver_id,$load_id,$disp_id,$msg_id,$mini_mode);
		echo $html;
		
		
		$return_val = "
			<rslt>1</rslt>
			<mrrTab><![CDATA[".$html."]]></mrrTab>
		";
		//display_xml_response($return_val);
	}
	function mrr_quick_message_form_sender_geotab()
	{
		$load_id=$_POST['load_id'];
		$disp_id=$_POST['disp_id'];
		$driver_id=$_POST['driver_id'];
		$truck_id=$_POST['truck_id'];
		$msg_id=$_POST['msg_id'];
		$message=trim($_POST['message']);		
		
		//$html=mrr_quick_send_pn_truck_message($truck_id,$message,$msg_id,$driver_id,$load_id,$disp_id);
		$mesage_id=mrr_send_geotab_text_message($truck_id,$message,0,0,0);
		$html="Message has been Sent.";
		if($mesage_id=="-2")	$html="No Device. Message Not Sent.";			//no device set for this truck...skip this.)
		if($mesage_id=="-1")	$html="Blank Message, so Message Not Sent.";		//blank message....skip sending a message.
		if($mesage_id=="0")		$html="Attempted, but Message Not Sent.";		//generic fail.
		
		echo $html;
		
		$return_val = "
			<rslt>1</rslt>
			<mrrTab><![CDATA[".$html."]]></mrrTab>
		";
		//display_xml_response($return_val);
	}
	
	function mrr_quick_message_form_sender()
	{
		$load_id=$_POST['load_id'];
		$disp_id=$_POST['disp_id'];
		$driver_id=$_POST['driver_id'];
		$truck_id=$_POST['truck_id'];
		$msg_id=$_POST['msg_id'];
		$message=$_POST['message'];		
		
		$html=mrr_quick_send_pn_truck_message($truck_id,$message,$msg_id,$driver_id,$load_id,$disp_id);
		echo $html;
		
		$return_val = "
			<rslt>1</rslt>
			<mrrTab><![CDATA[".$html."]]></mrrTab>
		";
		//display_xml_response($return_val);
	}
	
	
	function search_gps_zip_codes() 
	{
		if(strlen($_GET['q']) >= 3)
     	{        		
     		//use gps_table...
     		$city_search=$_GET['q'];
     		$state_search=""; 
     		$mrr_adder="";   		
     		if(substr_count($city_search,",") > 0)
     		{
     			$local=explode(",",$city_search);     			
     			
     			$city_search=trim($local[0]);
     			$state_search=trim($local[1]);
     			
     			$mrr_adder="and state like '%".sql_friendly($state_search)."%'"; 
     		}    		
     		
     		$sql = "
     			select gps_to_zip_code.*
     				
     			from gps_to_zip_code
     			where gps_to_zip_code.deleted = 0
     				and	(
     					(
     					city like '%".sql_friendly($city_search)."%'     				
     					".$mrr_adder."
     					)
     					or 
     					(zip_code like '".sql_friendly($city_search)."%')
     					)
     			order by city, state, zip_code, id 
     			limit 100
     		";		
     		
     		$data = simple_query($sql);
     		
     		$last_city_check = '';
     		
     		while($row = mysqli_fetch_array($data)) {
     			$city_check = "$row[city], $row[state] $row[zip_code]";
     			// create a simple bit of code to check for and remove duplicates
     			if($last_city_check != $city_check) {
     				$last_city_check = $city_check;
     				
     				$lnum=0;
     				if(isset($_GET['line_number']))		$lnum=$_GET['line_number'];
     				
     				echo "$row[zip_code]|$row[city], $row[state]|".$lnum."\n";
     			}
     		}
     		
		}
	}
	
	function search_gps_zip_codes2() 
	{
		if(strlen($_GET['q']) >= 3)
     	{        		
     		//use gps_table...
     		$city_search=$_GET['q'];
     		$state_search=""; 
     		$mrr_adder="";   		
     		if(substr_count($city_search,",") > 0)
     		{
     			$local=explode(",",$city_search);     			
     			
     			$city_search=trim($local[0]);
     			$state_search=trim($local[1]);
     			
     			$mrr_adder="and state like '%".sql_friendly($state_search)."%'"; 
     		}    		
     		
     		$sql = "
     			select gps_to_zip_code.*
     				
     			from gps_to_zip_code
     			where gps_to_zip_code.deleted = 0
     				and	(
     					(
     					city like '%".sql_friendly($city_search)."%'     				
     					".$mrr_adder."
     					)
     					or 
     					(zip_code like '".sql_friendly($city_search)."%')
     					)
     			order by city, state, zip_code, id 
     			limit 100
     		";		
     		
     		$data = simple_query($sql);
     		
     		$last_city_check = '';
     		
     		while($row = mysqli_fetch_array($data)) {
     			$city_check = "$row[city], $row[state] $row[zip_code]";
     			// create a simple bit of code to check for and remove duplicates
     			if($last_city_check != $city_check) {
     				$last_city_check = $city_check;
     				
     				$lnum=0;
     				if(isset($_GET['line_number']))		$lnum=$_GET['line_number'];
     				
     				echo "$row[city], $row[state]|$row[zip_code]|".$lnum."\n";
     			}
     		}
     		
		}
	}
	
	function load_city_state_by_zip()
	{
		$zip=$_POST['zip_code'];
		$mode=$_POST['mode'];
		$city="";	
    		$state="";
    		$lat="";	
    		$long="";
		
		$cntr=0;
		$sql = "
     		select *     			
     		from gps_to_zip_code
     		where zip_code = '".sql_friendly($zip)."'     				
     			and deleted = 0
     		order by city, state, zip_code, id      		
     		";		
     	$data = simple_query($sql);
     	while($row = mysqli_fetch_array($data)) 
     	{
     		$city=trim($row['city']);	
     		$state=trim($row['state']);
     		$lat=trim($row['longitude']);	
     		$long=trim($row['latitude']);
     		
     		if((int)$long > 0)		$long=$long * -1;
     		     		
     		$cntr++;
     	}
     	
     	if($cntr==0 && $mode>0 && substr_count($zip,",") > 0)
     	{
     		$local=explode(",",$zip);     			
     			
     		$city=trim($local[0]);
     		$state=trim($local[1]);
     		     		
     		$res=mrr_get_promiles_gps_from_address('',$city,$state,'');
     		$lat=$res['lat'];	$long=$res['long'];		
     		$zip=$res['zip'];	$state=$res['state'];	$city=$res['city'];
     		
     		if($res['status'] > 0 && (int)$zip!=0)
     		{	//add it to the list for next time
     			$sqlu = "
               		insert into gps_to_zip_code 
               			(id,
               			linedate_added,
               			deleted,
               			zip_code,
               			city,
               			state,
               			latitude,
               			longitude,
               			population)
               		values 
               			(NULL,
               			NOW(),
               			0,
               			'".sql_friendly($zip)."',
               			'".sql_friendly(strtoupper($city))."',
               			'".sql_friendly(strtoupper($state))."',
               			'".sql_friendly($long)."',
               			'".sql_friendly($lat)."',
               			0)    		
               		";		
               	simple_query($sqlu);
     		}	
     	}
     	
     	if(trim($zip)=="")		$zip="00000";
     	
     	$return_val= "
     		<rslt>1</rslt>
     		<Zip><![CDATA[".$zip."]]></Zip>
			<City><![CDATA[".$city."]]></City>
			<State><![CDATA[".$state."]]></State>
			<Lat><![CDATA[".$lat."]]></Lat>
			<Long><![CDATA[".$long."]]></Long>
		";
		display_xml_response($return_val);
	}
	
	
	function mrr_deflag_master_load()
	{
		$load_id=$_POST['load_id'];
		
		$sql="
			update load_handler set
				master_load='0'		
			where id='".sql_friendly($load_id)."'
			";
		simple_query($sql);	
		
		$return_val = "
			<rslt>1</rslt>
		";
		
		display_xml_response($return_val);	
	}
	
	function mrr_get_driver_dot_info_for_load_planning()
	{
		$driver_id=$_POST['driver_id'];
		$date_from=$_POST['date_from'];	
		$date_to=$_POST['date_to'];
		$disp_hrs=$_POST['hrs_dispatched'];
		$plan_hrs=$_POST['hrs_planned'];
		
		$dres=mrr_find_driver_dot_hrs_for_planning_mrr($driver_id,$date_from,$date_to);
		
		//FLAG TALLY                  	//CLASS (meaning)
		//$dres['speeding']				//mrr_sr_speeding
		//$dres['violation_ll_hr']		//mrr_sr_11hr_rule
		//$dres['violation_l4_hr']		//mrr_sr_14hr_rule
		//$dres['violation_70_hr']		//mrr_sr_70hr_rule
		//$dres['abrupt_shutdowns']		//mrr_sr_abrupt_stop
		
		$warnings="";	
		
		if($dres['violation_70_hr'] > 0)		$warnings.="<span class='mrr_sr_70hr_rule'>70HR</span> ";
		if($dres['violation_l4_hr'] > 0)		$warnings.="<span class='mrr_sr_14hr_rule'>14HR</span> ";
		if($dres['violation_ll_hr'] > 0)		$warnings.="<span class='mrr_sr_11hr_rule'>11HR</span> ";
		if($dres['speeding'] > 0)			$warnings.="<span class='mrr_sr_speeding'>Speed</span> ";
		if($dres['abrupt_shutdowns'] > 0)		$warnings.="<span class='mrr_sr_abrupt_stop'>Shutdown</span> ";
		
		$return_val = "
			<rslt>1</rslt>
			<warnings><![CDATA[".trim($warnings)."]]></warnings>
		";
		
		display_xml_response($return_val);	
	}
	
	function mrr_get_driver_timeoff()
	{
		$driver_id=$_POST['driver_id'];
		
		$warnings="";	
		$dname="";
		
		$sql="
			select drivers_unavailable.*,
				drivers.name_driver_first,
				drivers.name_driver_last
				
			from drivers_unavailable	
				left join drivers on drivers.id=drivers_unavailable.driver_id
			where drivers_unavailable.driver_id='".sql_friendly($driver_id)."'
				and (
					(drivers_unavailable.linedate_start >='".date("Y-m-d",time())." 00:00:00' and drivers_unavailable.linedate_start <='".date("Y-m-d",strtotime("+14 days",time()))." 23:59:59')
						or
					(drivers_unavailable.linedate_end >='".date("Y-m-d",time())." 00:00:00' and drivers_unavailable.linedate_end <='".date("Y-m-d",strtotime("+14 days",time()))." 23:59:59')
						or
					(drivers_unavailable.linedate_start <='".date("Y-m-d",time())." 00:00:00' and drivers_unavailable.linedate_end >='".date("Y-m-d",time())." 23:59:59')
					)
				and drivers_unavailable.deleted=0
			order by drivers_unavailable.linedate_start asc, 
				drivers_unavailable.linedate_end asc, 
				drivers_unavailable.id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			//$dname=trim($row['name_driver_first'])." ".trim($row['name_driver_last']);
			
			$warnings.="".date("m/d/Y",strtotime($row['linedate_start']))." - ".date("m/d/Y",strtotime($row['linedate_end'])).": ".trim($row['reason_unavailable'])."<br>";		
		}
		
		
		$sql="
			select drivers.*,
				DATEDIFF(linedate_drugtest,NOW()) as expires_drug, 
				DATEDIFF(linedate_license_expires,NOW()) as expires_license,
				DATEDIFF(linedate_cov_expires,NOW()) as expires_cov
				
			from drivers
			where drivers.id='".sql_friendly($driver_id)."'
				and drivers.deleted=0
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$dname=trim($row['name_driver_first'])." ".trim($row['name_driver_last']);
			
			if($row['expires_drug'] <= 14) 		$warnings.="Medical will expire soon. ".date("m/d/Y",strtotime($row['linedate_drugtest'])).": <br>";	
			elseif($row['expires_drug'] <= 0) 		$warnings.="Medical is Expired ".date("m/d/Y",strtotime($row['linedate_drugtest'])).".<br>";	
			
			if($row['expires_license'] <= 14) 		$warnings.="License will soon expire. ".date("m/d/Y",strtotime($row['linedate_license_expires'])).": <br>";
			elseif($row['expires_license'] <= 0) 	$warnings.="Drivers License is Expired. ".date("m/d/Y",strtotime($row['linedate_license_expires']))."<br>";
			
			if($row['expires_cov'] <= 14) 		$warnings.="COV due to expire. ".date("m/d/Y",strtotime($row['linedate_cov_expires'])).": <br>";				
			elseif($row['expires_cov'] <= 0) 		$warnings.="COV is Expired.".date("m/d/Y",strtotime($row['linedate_cov_expires']))." and is Expired. <br>";	
		}
		
		$return_val = "
			<rslt>1</rslt>
			<DriverName><![CDATA[".trim($dname)."]]></DriverName>
			<warnings><![CDATA[".trim($warnings)."]]></warnings>
		";
		
		display_xml_response($return_val);		
	}
	
	function mrr_truck_in_shop_switch()
	{
		$html="";
		$equipment_type=$_POST['equipment_type'];
		$equipment_xref_id=$_POST['equipment_xref_id'];
				
		$tabler="trucks";			$namer="name_truck";
		if($equipment_type==2)	
		{
			$tabler="trailers";		$namer="trailer_name";	
		}
		
		$sql="select ".$namer.",id,in_the_shop from ".$tabler." where id='".sql_friendly($equipment_xref_id)."'";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$cur="Available";  
			$maker="In The Shop"; 
			if($row['in_the_shop'] > 0)	
			{
				$cur="In The Shop";
				$maker="Available"; 
			}
			$html="".$row[''.$namer.'']." is currently ".$cur.". <span class='mrr_link_like_on' onClick='mrr_toggle_in_the_shop(".$equipment_type.",".$row['id'].",".$row['in_the_shop'].");'>Click to place <b>".$maker."</b>.</span>";
		}
				
		$return_val = "
			<rslt>1</rslt>
			<mrrHTML><![CDATA[".$html."]]></mrrHTML>
		";
		
		display_xml_response($return_val);	
	}
	
	function mrr_truck_in_shop_switch_toggle()
	{
		$equipment_type=$_POST['equipment_type'];
		$equipment_xref_id=$_POST['equipment_xref_id'];
		$status_is=$_POST['status_is'];
				
		$tabler="trucks";		if($equipment_type==2)	$tabler="trailers";	
		
		if($status_is > 0)		$status_is=0;		else		$status_is=1;
		
		$sql="update ".$tabler." set in_the_shop='".sql_friendly($status_is)."' where id='".sql_friendly($equipment_xref_id)."'";
		simple_query($sql);	
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	
	function mrr_ajax_driver_hours_for_week()
	{
		$driver_id=$_POST['driver_id'];
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];		
		
		$mrr_tab="";
		if($driver_id > 0)
		{	
			$res=mrr_find_driver_dot_hrs_for_planning_mrr_dispatches($driver_id,$date_from,$date_to);
			//$mrr_tab.="<DrivenHrs><![CDATA[".$res['driven_hours']."]]></DrivenHrs>";
			//$mrr_tab.="<RestedHrs><![CDATA[".$res['rested_hours']."]]></RestedHrs>";
			//$mrr_tab.="<WorkedHrs><![CDATA[".$res['worked_hours']."]]></WorkedHrs>";
			$mrr_tab.="<WkDrivenHrs><![CDATA[".$res['week_driven_hours']."]]></WkDrivenHrs>";
			$mrr_tab.="<WkRestedHrs><![CDATA[".$res['week_rested_hours']."]]></WkRestedHrs>";
			$mrr_tab.="<WkWorkedHrs><![CDATA[".$res['week_worked_hours']."]]></WkWorkedHrs>";
			$mrr_tab.="<VioTen><![CDATA[".$res['violation_l0_hr']."]]></VioTen>";
			$mrr_tab.="<VioEleven><![CDATA[".$res['violation_ll_hr']."]]></VioEleven>";
			$mrr_tab.="<VioForteen><![CDATA[".$res['violation_l4_hr']."]]></VioForteen>";
			$mrr_tab.="<VioThirty><![CDATA[".$res['violation_34_hr']."]]></VioThirty>";
			$mrr_tab.="<VioSeventy><![CDATA[".$res['violation_70_hr']."]]></VioSeventy>";
			$mrr_tab.="<Violations><![CDATA[".$res['violations_dot']."]]></Violations>";
			//$mrr_tab.="<Speeding><![CDATA[".$res['speeding']."]]></Speeding>";
			//$mrr_tab.="<Shutdowns><![CDATA[".$res['abrupt_shutdowns']."]]></Shutdowns>";
			$mrr_tab.="<Num><![CDATA[".$res['num']."]]></Num>";
			$mrr_tab.="<SQL><![CDATA[".$res['sql']."]]></SQL>";
						
			$pnres=mrr_find_driver_dot_hrs_for_planning_mrr($driver_id,$date_from,$date_to);
			
			$mrr_tab.="<PNDrivenHrs><![CDATA[".$pnres['driven_hours']."]]></PNDrivenHrs>";
			$mrr_tab.="<PNRestedHrs><![CDATA[".$pnres['rested_hours']."]]></PNRestedHrs>";
			$mrr_tab.="<PNWorkedHrs><![CDATA[".$pnres['worked_hours']."]]></PNWorkedHrs>";
			$mrr_tab.="<PNWkDrivenHrs><![CDATA[".$pnres['week_driven_hours']."]]></PNWkDrivenHrs>";
			$mrr_tab.="<PNWkRestedHrs><![CDATA[".$pnres['week_rested_hours']."]]></PNWkRestedHrs>";
			$mrr_tab.="<PNWkWorkedHrs><![CDATA[".$pnres['week_worked_hours']."]]></PNWkWorkedHrs>";
			$mrr_tab.="<PNVioTen><![CDATA[".$pnres['violation_l0_hr']."]]></PNVioTen>";
			$mrr_tab.="<PNVioEleven><![CDATA[".$pnres['violation_ll_hr']."]]></PNVioEleven>";
			$mrr_tab.="<PNVioForteen><![CDATA[".$pnres['violation_l4_hr']."]]></PNVioForteen>";
			$mrr_tab.="<PNVioThirty><![CDATA[".$pnres['violation_34_hr']."]]></PNVioThirty>";
			$mrr_tab.="<PNVioSeventy><![CDATA[".$pnres['violation_70_hr']."]]></PNVioSeventy>";
			$mrr_tab.="<PNViolations><![CDATA[".$pnres['violations_dot']."]]></PNViolations>";
			$mrr_tab.="<PNSpeeding><![CDATA[".$pnres['speeding']."]]></PNSpeeding>";
			$mrr_tab.="<PNShutdowns><![CDATA[".$pnres['abrupt_shutdowns']."]]></PNShutdowns>";
			$mrr_tab.="<PNNum><![CDATA[".$pnres['num']."]]></PNNum>";
			$mrr_tab.="<PNSQL><![CDATA[".$pnres['sql']."]]></PNSQL>";						
		}
		$mrr_tab.="<Driver><![CDATA[".$driver_id."]]></Driver>";
		$mrr_tab.="<From><![CDATA[".$date_from."]]></From>";
		$mrr_tab.="<To><![CDATA[".$date_to."]]></To>";
				
		$return_val = "<rslt>1</rslt>".$mrr_tab."";		
		display_xml_response($return_val);		
	}
	
	
	//Driver Absence
	function mrr_add_driver_absense_record()
	{
		$driver_id=$_POST['driver_id'];
		$date=$_POST['date'];
          $date_to=$_POST['date_to'];
		$code=$_POST['code'];	
		$note=$_POST['note'];
          $duration=0;
          if(isset($_POST['duration']))      $duration=(int) $_POST['duration'];
		
		if($driver_id > 0 && trim($date)!="" && trim($date)==trim($date_to))
		{    //add only the single date to the list...
			$sql="insert into driver_absenses
     				(id,
     				driver_id,
     				user_id,
     				linedate_added,
     				linedate,
     				deleted,
     				driver_code,
     				duration,
     				driver_reason)
     			values
     				(NULL,
     				'".sql_friendly($driver_id)."',
     				0,
     				NOW(),
     				'".date("Y-m-d",strtotime($date))."',
     				0,
     				'".sql_friendly($code)."',
     				'".sql_friendly($duration)."',
     				'".sql_friendly($note)."')
			";
			simple_query($sql);	
		}
		elseif($driver_id > 0 && trim($date)!="" && trim($date_to)!="" && trim($date)!=trim($date_to))
          {    //adding an entire range of days to the list...
               $d1=strtotime($date." 00:00:00");
               $d2=strtotime($date_to." 00:00:00");
               $diff=($d2 -$d1)/(24*60*60);
               $diff=(int) $diff;
               if($diff < 0)       $diff=abs($diff);
               
               for($i=0; $i <= $diff; $i++)
               {
                    $sql="insert into driver_absenses
                              (id,
                              driver_id,
                              user_id,
                              linedate_added,
                              linedate,
                              deleted,
                              driver_code,
                              duration,
                              driver_reason)
                         values
                              (NULL,
                              '".sql_friendly($driver_id)."',
                              0,
                              NOW(),
                              '".date("Y-m-d",strtotime("+".$i." day",strtotime($date)))."',
                              0,
                              '".sql_friendly($code)."',
                              '".sql_friendly($duration)."',
                              '".sql_friendly($note)."')
                    ";
                    simple_query($sql);
               }               
          }
				
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);			
	}
	
	function mrr_list_driver_absense_records()
	{
		$driver_id=$_POST['driver_id'];
          
          if(isset($_POST['cal_day']))		$_GET['use_day']=$_POST['cal_day'];
          if(isset($_POST['cal_mon']))		$_GET['use_mon']=$_POST['cal_mon'];
          if(isset($_POST['cal_year']))		$_GET['use_year']=$_POST['cal_year'];
		
		if(isset($_GET['use_day']))		$_POST['use_day']=$_GET['use_day'];
     	if(isset($_GET['use_mon']))		$_POST['use_mon']=$_GET['use_mon'];
     	if(isset($_GET['use_year']))		$_POST['use_year']=$_GET['use_year'];
     	
     	if(isset($_GET['cal_day']))		$_POST['cal_day']=$_GET['cal_day'];
     	if(isset($_GET['cal_mon']))		$_POST['cal_mon']=$_GET['cal_mon'];
     	if(isset($_GET['cal_year']))		$_POST['cal_year']=$_GET['cal_year'];
     	
     	if(!isset($_POST['use_day']))		$_POST['use_day']=date("d");
     	if(!isset($_POST['use_mon']))		$_POST['use_mon']=date("m");
     	if(!isset($_POST['use_year']))	$_POST['use_year']=date("Y");
     	
     	if(!isset($_POST['cal_day']))		$_POST['cal_day']=date("d");
     	if(!isset($_POST['cal_mon']))		$_POST['cal_mon']=date("m");
     	if(!isset($_POST['cal_year']))	$_POST['cal_year']=date("Y");
		
		$cal=mrr_driver_absense_calendar($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year'],$driver_id,0);
          $cal.="<div class='clear'></div>";
		
		$selbox="";
		
		$sqlv="
			select option_values.* 
			from option_values
				left join option_cat on option_cat.id=option_values.cat_id
			where option_values.deleted=0
				and option_cat.cat_name='driver_no_show_codes'
			order by option_values.id asc
		";
		$datav=simple_query($sqlv);
		while($rowv=mysqli_fetch_array($datav))
		{
			$selbox.="<option value='".$rowv['id']."'>".$rowv['fvalue']."=".strtoupper($rowv['fname'])."</option>";
		}
						
		$mrr_tab="<table width='300' cellpadding='0' cellspacing='0' border='0' class='admin_menu2'>
				<tr>
					<td colspan='5'>
						<div class='section_heading'>Driver Absence</div>
					</td>
				</tr>
				<tr>
					<td colspan='5'>
						&nbsp;<br>
						New: 
						From <input type='text' name='driver_absense_date' id='driver_absense_date' value='".date("m/d/Y", time())."' size='10' class='mrr_date_picker'>
						To <input type='text' name='driver_absense_date_to' id='driver_absense_date_to' value='".date("m/d/Y", time())."' size='10' class='mrr_date_picker'>
               			<br>
               			Code: 
               			<select name='driver_absense_code' id='driver_absense_code'>
                         		<option value='0'>Choose Code</option>
                         		".$selbox."
                         	</select>
               			<br>
               			Duration:
               			<select name='driver_absense_duration' id='driver_absense_duration'>
                         		<option value='0' selected>Full Day</option>
                         		<option value='1'>Half Day</option>
                         	</select>
               			<br>
               			Note: <input type='text' name='driver_absense_note' id='driver_absense_note' value=\"\" style='width:250px;'>
               			<input type='button' value='Add' onClick='mrr_add_to_driver_absense(".$driver_id.");'>
               			<br>&nbsp;<br>
               			<div class='clear'></div>
					</td>
				</tr>
				<tr>
					<td colspan='5'>
						".$cal."
					</td>
				</tr>
				<tr>
					<td valign='top' width='75'><b>Date</b></td>
					<td valign='top' width='25'><b>Code</b></td>
					<td valign='top' width='50'><b>Day </b></td>
					<td valign='top'><b><i>Note</i></b></td>
					<td valign='top'>&nbsp;</td>
				</tr>
		";
		$cntr=0;
		$sql="
			select driver_absenses.*,
				option_values.fname,
				option_values.fvalue
				
			from driver_absenses
				left join option_values on option_values.id=driver_absenses.driver_code
			where driver_absenses.driver_id='".sql_friendly($driver_id)."'
				and driver_absenses.deleted=0
			order by driver_absenses.linedate desc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$duration="Full";        if($row['duration']==1)          $duration="Half";
		     
		     $mrr_tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>".date("m/d/Y",strtotime($row['linedate']))."</td>
					<td valign='top'><b>".$row['fvalue']."</b></td>
					<td valign='top'>".$duration."</td>
					<td valign='top'><i>".$row['driver_reason']."</i></td>
					<td valign='top'><span onClick='confirm_delete_absense(".$driver_id.",".$row['id'].");' class='mrr_link_like_on'><img src='images/delete_sm.gif' border='0'></span></td>
				</tr>
			";
			
			$cntr++;
		}		
		$mrr_tab.="</table>
			<script type='text/javascript'> 
				$('.mrr_date_picker').datepicker();
			</script>
		";		
		//if($cntr==0)		$mrr_tab="";
		
		$return_val = "<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>";		
		display_xml_response($return_val);	
	}
	
	function mrr_remove_driver_absense_records()
	{
		$id=$_POST['id'];
		
		$sql="update driver_absenses set deleted='1' where id='".sql_friendly($id)."'";
		simple_query($sql);	
				
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);		
	}
	
	
	//User Absence
	function mrr_add_user_absense_record()
	{
		$user_id=$_POST['user_id'];
		$date=$_POST['date'];
          $date_to=$_POST['date_to'];
		$code=$_POST['code'];	
		$note=$_POST['note'];	
		$duration=0;
		if(isset($_POST['duration']))      $duration=(int) $_POST['duration'];
		
		if($user_id > 0 && trim($date)!="" && trim($date)==trim($date_to))
		{
			$sql="insert into driver_absenses
     				(id,
     				driver_id,
     				user_id,
     				linedate_added,
     				linedate,
     				deleted,
     				driver_code,
     				duration,
     				driver_reason)
     			values
     				(NULL,
     				0,
     				'".sql_friendly($user_id)."',
     				NOW(),
     				'".date("Y-m-d",strtotime($date))."',
     				0,
     				'".sql_friendly($code)."',
     				'".sql_friendly($duration)."',
     				'".sql_friendly($note)."')
			";
			simple_query($sql);	
		}
          elseif($user_id > 0 && trim($date)!="" && trim($date_to)!="" && trim($date)!=trim($date_to))
          {    //adding an entire range of days to the list...
               $d1=strtotime($date." 00:00:00");
               $d2=strtotime($date_to." 00:00:00");
               $diff=($d2 -$d1)/(24*60*60);
               $diff=(int) $diff;
               if($diff < 0)       $diff=abs($diff);
               
               for($i=0; $i <= $diff; $i++)
               {
                    $sql="insert into driver_absenses
                              (id,
                              driver_id,
                              user_id,
                              linedate_added,
                              linedate,
                              deleted,
                              driver_code,
                              duration,
                              driver_reason)
                         values
                              (NULL,
                              0,
                              '".sql_friendly($user_id)."',
                              NOW(),
                              '".date("Y-m-d",strtotime("+".$i." day",strtotime($date)))."',
                              0,
                              '".sql_friendly($code)."',
                              '".sql_friendly($duration)."',
                              '".sql_friendly($note)."')
                    ";
                    simple_query($sql);
               }
          }
				
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);			
	}
	
	function mrr_list_user_absense_records()
	{
		$user_id=$_POST['user_id'];
          
          if(isset($_POST['cal_day']))		$_GET['use_day']=$_POST['cal_day'];
          if(isset($_POST['cal_mon']))		$_GET['use_mon']=$_POST['cal_mon'];
          if(isset($_POST['cal_year']))		$_GET['use_year']=$_POST['cal_year'];
		
		if(isset($_GET['use_day']))		$_POST['use_day']=$_GET['use_day'];
     	if(isset($_GET['use_mon']))		$_POST['use_mon']=$_GET['use_mon'];
     	if(isset($_GET['use_year']))		$_POST['use_year']=$_GET['use_year'];
     	
     	if(isset($_GET['cal_day']))		$_POST['cal_day']=$_GET['cal_day'];
     	if(isset($_GET['cal_mon']))		$_POST['cal_mon']=$_GET['cal_mon'];
     	if(isset($_GET['cal_year']))		$_POST['cal_year']=$_GET['cal_year'];
     	
     	if(!isset($_POST['use_day']))		$_POST['use_day']=date("d");
     	if(!isset($_POST['use_mon']))		$_POST['use_mon']=date("m");
     	if(!isset($_POST['use_year']))	$_POST['use_year']=date("Y");
     	
     	if(!isset($_POST['cal_day']))		$_POST['cal_day']=date("d");
     	if(!isset($_POST['cal_mon']))		$_POST['cal_mon']=date("m");
     	if(!isset($_POST['cal_year']))	$_POST['cal_year']=date("Y");
		
		$cal=mrr_driver_absense_calendar($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year'],0,$user_id);
          $cal.="<div class='clear'></div>";
		
		$selbox="";
		
		$sqlv="
			select option_values.* 
			from option_values
				left join option_cat on option_cat.id=option_values.cat_id
			where option_values.deleted=0
				and option_cat.cat_name='driver_no_show_codes'
			order by option_values.id asc
		";
		$datav=simple_query($sqlv);
		while($rowv=mysqli_fetch_array($datav))
		{
			$selbox.="<option value='".$rowv['id']."'>".$rowv['fvalue']."=".strtoupper($rowv['fname'])."</option>";
		}
						
		$mrr_tab="<table width='300' cellpadding='0' cellspacing='0' border='0' class='admin_menu2'>
				<tr>
					<td colspan='5'>
						<div class='section_heading'>User Absence</div>
					</td>
				</tr>
				<tr>
					<td colspan='5'>
						&nbsp;<br>
						New: 
						From <input type='text' name='driver_absense_date' id='driver_absense_date' value='".date("m/d/Y", time())."' size='10' class='mrr_date_picker'>
               			To <input type='text' name='driver_absense_date_to' id='driver_absense_date_to' value='".date("m/d/Y", time())."' size='10' class='mrr_date_picker'>
               			<br>
               			Code: 
               			<select name='driver_absense_code' id='driver_absense_code'>
                         		<option value='0'>Choose Code</option>
                         		".$selbox."
                         	</select>
               			<br>
               			Duration: 
               			<select name='driver_absense_duration' id='driver_absense_duration'>
                         		<option value='0' selected>Full Day</option>
                         		<option value='1'>Half Day</option>
                         	</select>
               			<br>
               			Note: <input type='text' name='driver_absense_note' id='driver_absense_note' value=\"\" style='width:250px;'>
               			<input type='button' value='Add' onClick='mrr_add_to_user_absense(".$user_id.");'>
               			<br>&nbsp;<br>
               			<div class='clear'></div>
					</td>
				</tr>
				<tr>
					<td colspan='5'>
						".$cal."
					</td>
				</tr>
				<tr>
					<td valign='top' width='75'><b>Date</b></td>
					<td valign='top' width='25'><b>Code</b></td>
					<td valign='top' width='50'><b>Day </b></td>
					<td valign='top'><b><i>Note</i></b></td>
					<td valign='top'>&nbsp;</td>
				</tr>
		";
		$cntr=0;
		$sql="
			select driver_absenses.*,
				option_values.fname,
				option_values.fvalue
				
			from driver_absenses
				left join option_values on option_values.id=driver_absenses.driver_code
			where driver_absenses.user_id='".sql_friendly($user_id)."'
				and driver_absenses.deleted=0
			order by driver_absenses.linedate desc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$duration="Full";             if($row['duration']==1)       $duration="Half";
               
               $mrr_tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>".date("m/d/Y",strtotime($row['linedate']))."</td>
					<td valign='top'><b>".$row['fvalue']."</b></td>
					<td valign='top'>".$duration."</td>
					<td valign='top'><i>".$row['driver_reason']."</i></td>
					<td valign='top'><span onClick='confirm_delete_absense(".$user_id.",".$row['id'].");' class='mrr_link_like_on'><img src='images/delete_sm.gif' border='0'></span></td>
				</tr>
			";
			
			$cntr++;
		}		
		$mrr_tab.="</table>
			<script type='text/javascript'> 
				$('.mrr_date_picker').datepicker();
			</script>
		";		
		//if($cntr==0)		$mrr_tab="";
		
		$return_val = "<rslt>1</rslt><mrrTab><![CDATA[".$mrr_tab."]]></mrrTab>";		
		display_xml_response($return_val);	
	}
	
	function mrr_remove_user_absense_records()
	{
		$id=$_POST['id'];
		
		$sql="update driver_absenses set deleted='1' where id='".sql_friendly($id)."'";
		simple_query($sql);	
				
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);		
	}
	
	
	
	function mrr_disp_cost_calc_viewer()
	{
		$id=$_POST['dispatch_id'];
		
		global $defaultsarray;
		
		$mrr_cost=0;
		$mrr_calc="";
		$load_id=0;
		$return_val="";
		
		$sql="
			select *				
			from trucks_log
			where id='".sql_friendly($id)."'
		";
		$data=simple_query($sql);
		$row=mysqli_fetch_array($data);
		if(mysqli_num_rows($data) == 0)
		{
			$return_val = "<rslt>0</rslt>";		
			display_xml_response($return_val);			
		}
		
		$load_id=$row['load_handler_id'];
		$mrr_cost = get_dispatch_cost($id);
				
		if($load_id==0 || $return_val!="")
		{
			$return_val = "<rslt>0</rslt>";		
			display_xml_response($return_val);		
		}
		else
		{			
			$rtot=0;
			
			// get any variable expenses
     		$sql = "
     			select *
     			
     			from dispatch_expenses
     			where dispatch_id = '".sql_friendly($id)."'
     				and deleted = 0
     		";
     		$data_expenses = simple_query($sql);
     		
     		$variable_expenses_total = 0;
     		while($row_expense = mysqli_fetch_array($data_expenses)) {
     			$variable_expenses_total += $row_expense['expense_amount'];
     		}
     		
     		
     		
     		//Daily Cost
     		$daily_cost = ($row['daily_cost'] > 0 ? $row['daily_cost'] : get_daily_cost($row['truck_id'], $row['trailer_id']));
     		
     		
			$sql2="
				select *				
				from load_handler
				where id='".sql_friendly($load_id)."'
			";
			$data2=simple_query($sql2);
			$row2=mysqli_fetch_array($data2);	
			
			$fuel_per_mile = $row2['actual_fuel_charge_per_mile'];
			
					
			$avg_mpg = ($row['avg_mpg'] > 0 ? $row['avg_mpg'] : $defaultsarray['average_mpg']);
     		
     		$tractor_maint_per_mile = ($row['tractor_maint_per_mile'] > 0 ? $row['tractor_maint_per_mile'] : $defaultsarray['tractor_maint_per_mile']);
     		$trailer_maint_per_mile = ($row['trailer_maint_per_mile'] > 0 ? $row['trailer_maint_per_mile'] : $defaultsarray['trailer_maint_per_mile']);
     		
     		$mrr_other_per_mile=0;		
     		
     		$tires_per_mile = ($row['tires_per_mile'] > 0 ? $row['tires_per_mile'] : $defaultsarray['tires_per_mile']);
     		$accidents_per_mile = ($row['accidents_per_mile'] > 0 ? $row['accidents_per_mile'] : $defaultsarray['truck_accidents_per_mile']);
     		$mile_exp_per_mile = ($row['mile_exp_per_mile'] > 0 ? $row['mile_exp_per_mile'] : $defaultsarray['mileage_expense_per_mile']);
     		
     		$trailer_mile_exp_per_mile=0;
     		//$trailer_mile_exp_per_mile = ($row['trailer_exp_per_mile'] > 0 ? $row['trailer_exp_per_mile'] : $defaultsarray['trailer_mile_exp_per_mile']);
     		//$trailer_mile_exp_per_mile = $row['trailer_exp_per_mile'];		//$defaultsarray['trailer_mile_exp_per_mile']
     		
     		$misc_per_mile = ($row['misc_per_mile'] > 0 ? $row['misc_per_mile'] : $defaultsarray['misc_expense_per_mile']);
     		$mrr_other_per_mile=$tires_per_mile + $accidents_per_mile + $mile_exp_per_mile + $misc_per_mile + $trailer_mile_exp_per_mile;
			
			$total_maint_per_mile = $tractor_maint_per_mile + $trailer_maint_per_mile + $mrr_other_per_mile;
					
			$stored_labor_per_mile = $row['labor_per_mile'];
			if($stored_labor_per_mile>0)
			{
				$total_per_mile = $stored_labor_per_mile + $total_maint_per_mile + $fuel_per_mile;
			}
			else
			{
				$total_per_mile = $defaultsarray['labor_per_mile'] + $total_maint_per_mile + $fuel_per_mile;
			}
			
			
			$deadhead_cost = $total_per_mile * $row['miles_deadhead'];
			$breakeven_otr = $total_per_mile * $row['miles'] + $deadhead_cost + ($daily_cost * $row['daily_run_otr']);
			$labor_per_hour = ($row['labor_per_hour'] > 0 ? $row['labor_per_hour'] : $defaultsarray['labor_per_hour']);
			$breakeven_hourly = ($row['loaded_miles_hourly'] + $row['miles_deadhead_hourly']) * ($fuel_per_mile + $total_maint_per_mile) + ($row['hours_worked'] * $labor_per_hour) + ($daily_cost * $row['daily_run_hourly']);
			
			$total_cost = $breakeven_otr + $breakeven_hourly + $variable_expenses_total;
						
			$total_miles =  $row['miles_deadhead'] + $row['miles'];
			
			$total_hourly=($row['hours_worked'] * $labor_per_hour);
			
			$mrr_labor=$total_hourly + ($total_miles * $stored_labor_per_mile);
			
			/*
				DC Diff = ".(get_daily_cost($row['truck_id'], $row['trailer_id']) - $row['daily_cost'])."<br>
				<b>DC Diff * OTR = ".($row['daily_run_otr'] * (get_daily_cost($row['truck_id'], $row['trailer_id']) - $row['daily_cost']))."</b><br><br>
				
				<br>				
				Days Run Hourly: ".$row['daily_run_otr']."<br>
				B.E.OTR Hourly Fuel:  ".$row['loaded_miles_hourly']." x (".$fuel_per_mile." + ".$total_maint_per_mile.") = ".$row['loaded_miles_hourly'] * ($fuel_per_mile + $total_maint_per_mile)."<br>
				B.E. OTR Hourly Labor: ".$row['hours_worked']." x ".$labor_per_hour." = ".($row['hours_worked'] * $labor_per_hour)."<br>
				B.E. OTR Hourly DayCost: ".$daily_cost ." x ".$row['daily_run_hourly']." = ".($daily_cost * $row['daily_run_hourly'])."<br>
				Break Even Hourly OTR =  ".$breakeven_hourly."<br>
				<br>				
				Days Run OTR: ".$row['daily_run_otr']."<br>
				B.E.OTR DH Cost:  ".$total_per_mile." x ".$row['miles_deadhead']." = ".$total_per_mile * $row['miles_deadhead']."<br>
				B.E. OTR Reg Cost: ".$total_per_mile." x ".$row['miles']." = ".$total_per_mile * $row['miles']."<br>
				B.E. OTR DayCost: ".$daily_cost ." x ".$row['daily_run_otr']." = ".$daily_cost * $row['daily_run_otr']."<br>
				Break Even OTR =  ".$breakeven_otr."<br>
				<br>
				
				<b>".$variable_expenses_total."  +  ".$breakeven_hourly."  +  ".$breakeven_otr."  =  ".$total_cost."</b><br>
				<br><br>
				fuel: ".($fuel_per_mile * $total_miles)."<br>
				Daily Cost: $daily_cost<br>
				Current Labor: ".($defaultsarray['labor_per_mile'] * $total_miles)."<br>
				<span style='color:#aaa'>Labor (stored): ".($stored_labor_per_mile * $total_miles)."</span><br>
				Maint: ".($total_maint_per_mile * $total_miles)."<br>
				Total for Dispatch: <b>".$total_cost."</b><br>
			*/
			
			
			//$rtot+=$daily_cost;
     		$rtot+=$variable_expenses_total;
			$rtot+=$breakeven_otr;
			$rtot+=$breakeven_hourly;
						
			$mileage_tot=0;
			$mileage_tot+=$fuel_per_mile;
			$mileage_tot+=$stored_labor_per_mile;
			$mileage_tot+=$tractor_maint_per_mile;
			$mileage_tot+=$trailer_maint_per_mile;
			$mileage_tot+=$tires_per_mile;
			$mileage_tot+=$mile_exp_per_mile;
			$mileage_tot+=$misc_per_mile;
			$mileage_tot+=$accidents_per_mile;
			
			$mrr_calc="
			<div style='border:1px solid #CCCC00; padding:5px;'>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr>
				<td valign='top' style='background-color:#ffffee;' align='right'>	
					<b>General Per Mile Settings</b><br>				
					Fuel per Mile: $".number_format($fuel_per_mile,2)."<br>
					Labor per Mile: $".number_format($stored_labor_per_mile,2)."<br>
				</td>
				<td valign='top' style='background-color:#ffffee;' align='right'>
					Truck per Mile: $".number_format($tractor_maint_per_mile,2)."<br>	
					Trailer per Mile: $".number_format($trailer_maint_per_mile,2)."<br>	
					Tires per Mile: $".number_format($tires_per_mile,2)."<br>	
				</td>
				<td valign='top' style='background-color:#ffffee;' align='right'>					
					Mileage Exp. per Mile: $".number_format($mile_exp_per_mile,2)."<br>	
					Misc. Exp. per Mile: $".number_format($misc_per_mile,2)."<br>	
					Accidents per Mile: $".number_format($accidents_per_mile,2)."<br>	
				</td>				
			</tr>
			<tr>
				<td valign='top'><hr></td>
				<td valign='top'><hr></td>
				<td valign='top'><hr></td>
			</tr>
			<tr>
				<td valign='top' style='background-color:#ffffee;' align='right'>
					Average Miles Per Gallon ".number_format($avg_mpg,2)."
				</td>
				<td valign='top' style='background-color:#ffffee;' align='right'>
					&nbsp;
				</td>
				<td valign='top' style='background-color:#ffffee;' align='right'>					
					Total per Mile: $".number_format($mileage_tot,2)."	
				</td>				
			</tr>
			<tr>
				<td valign='top'><hr></td>
				<td valign='top'><hr></td>
				<td valign='top'><hr></td>
			</tr>
			<tr>
				<td valign='top' width='35%'>         			
          			<table cellpadding='0' cellspacing='0' border='0'>
          			<tr style='background-color:#ffffff;'>
          				<td valign='top' align='left'>
          					 ".$row['miles']." Miles  <br>
          					x $".number_format($total_per_mile,2)." Per Mile	
          				</td>
          				<td valign='top' align='right'>$".number_format( ($total_per_mile * $row['miles']) ,2)."</td>
          			<tr>
          			<tr>
          				<td valign='top' align='left'>
          					 ".$row['miles_deadhead']." Miles DeadHead <br>
          					x $".number_format($total_per_mile,2)." Per Mile
          				</td>
          				<td valign='top' align='right'>+ $".number_format( ($total_per_mile * $row['miles_deadhead']) ,2)."</td>
          			<tr> 
          			<tr style='background-color:#ffffff;'>
          				<td valign='top' align='left'>
          					$".number_format($daily_cost,2)."  Daily Cost<br>
          					x ".$row['daily_run_otr']." Days Run OTR 
          				</td>
          				<td valign='top' align='right'>+ $".number_format( ($daily_cost * $row['daily_run_otr']) ,2)."</td>
          			<tr>           			
          			<tr><td colspan='2'><hr></td></tr>	
          			<tr> 
          				<td valign='top' align='left'>Break Even OTR</td>
          				<td valign='top' align='right'>$".number_format($breakeven_otr,2)."</td>
          			<tr>		
          			</table>				
				</td>
				
				
				<td valign='top' width='35%'>         			
          			<table cellpadding='0' cellspacing='0' border='0'>
          			<tr style='background-color:#ffffff;'>
          				<td valign='top' align='left'>
          					".$row['hours_worked']." Hours Worked <br> 
          					x $".number_format($labor_per_hour,2)." Per Hour
          				</td>
          				<td valign='top' align='right'>$".number_format(($row['hours_worked'] * $labor_per_hour),2)."</td>
          			<tr>
          			<tr>
          				<td valign='top' align='left'>
          					".($row['loaded_miles_hourly'] + $row['miles_deadhead_hourly'])." Hourly Miles <br> 
          					x $".number_format( ($fuel_per_mile + $total_maint_per_mile) ,2)." Per Mile 
          				</td>
          				<td valign='top' align='right'>+ $".number_format( (($row['loaded_miles_hourly'] + $row['miles_deadhead_hourly']) * ($fuel_per_mile + $total_maint_per_mile)) ,2)."</td>
          			<tr>
          			<tr style='background-color:#ffffff;'>
          				<td valign='top' align='left'>
          					$".number_format($daily_cost,2)."  Daily Cost  <br>          					
          					x ".$row['daily_run_hourly']." Days Run OTR
          				</td>
          				<td valign='top' align='right'>+ $".number_format(($row['daily_run_hourly'] * $daily_cost),2)."</td>
          			<tr>         			
          			<tr><td colspan='2'><hr></td></tr>	
          			<tr>
          				<td valign='top' align='left'>Break Even Hourly</td>
          				<td valign='top' align='right'>$".number_format($breakeven_hourly,2)."</td>
          			<tr>		
          			</table>				
				</td>
				
				
				<td valign='top' width='30%'>         			
          			<table cellpadding='0' cellspacing='0' border='0'>
          			<tr>
          				<td valign='top' colspan='2' align='right'><b>Dispatch Cost Calculator</b></td>
          			<tr>
          			<tr>
          				<td valign='top' align='left'>Cost (Function)</td>
          				<td valign='top' align='right'>$".number_format($mrr_cost,2)."</td>
          			<tr>
          			<tr><td colspan='2'><hr></td></tr>		
          			
          			<tr style='background-color:#ffffff;'>
          				<td valign='top' align='left'>Variable Expenses</td>
          				<td valign='top' align='right'>$".number_format($variable_expenses_total,2)."</td>
          			<tr>
          			<tr>
          				<td valign='top' align='left'>+ Break Even OTR</td>
          				<td valign='top' align='right'>$".number_format($breakeven_otr,2)."</td>
          			<tr>
          			<tr style='background-color:#ffffff;'>
          				<td valign='top' align='left'>+ Break Even Hourly</td>
          				<td valign='top' align='right'>$".number_format($breakeven_hourly,2)."</td>
          			<tr>
          			
          			<tr><td colspan='2'><hr></td></tr>	
          			<tr>
          				<td valign='top' align='left'>Cost (Formula)</td>
          				<td valign='top' align='right'>$".number_format($rtot,2)."</td>
          			<tr>		
          			</table>				
				</td>			
			</tr>	
						
			</table>
			</div>
			";
						
			/*			
			<tr>
				<td valign='top' align='left'>Daily Cost</td>
				<td valign='top' align='right'>$".number_format($daily_cost,2)."</td>
			<tr>
			<tr>
				<td valign='top' align='left'>
					
					Maint per Mile Tot = ".$total_maint_per_mile."<br><br>
				
					Fuel per Mile: ".$fuel_per_mile."<br>
					Maint per Mile Tot = ".$total_maint_per_mile."<br>	
									
					Other per Mile: ".$mrr_other_per_mile."<br>					
					<b>Total per Mile = ".$total_per_mile."</b><br>
					<b>Total Miles = ".$total_miles."</b><br>
				</td>
				<td valign='top' align='right'>$".number_format(($total_miles * $total_per_mile),2)."</td>
			<tr>
			<tr>
				<td valign='top' align='left'>				
					<b>Total per Hour = ".$labor_per_hour."</b><br>
					<b>Total Hours = ".$row['hours_worked']."</b><br>
				</td>
				<td valign='top' align='right'>$".number_format($total_hourly,2)."</td>
			<tr>			
			*/
			
			$return_val = "<rslt>1</rslt><disp>".$id."</disp><DispCost><![CDATA[$".number_format($mrr_cost,2)."]]></DispCost><DispCalc><![CDATA[".$mrr_calc."]]></DispCalc>";		
			display_xml_response($return_val);	
		}
	}
	
	function mrr_update_mr_unit_local_snooze()
     {
          $maint_id=(int) $_POST['maint_id'];
          $days=(int) $_POST['delay'];
                    
          if($days > 0)
          {
               $use_date=date("Y-m-d",strtotime("+".$days." days",time()));
               $sql="
                    insert into mr_unit_locations
                         (id,linedate_added,maint_id,truck_id,trailer_id,mr_location,user_id)
                    values 
                         (NULL, '".$use_date." 08:00:00', '".sql_friendly($maint_id)."', '0', '0', 'SNOOZE: ".$days." days delay', '".sql_friendly($_SESSION['user_id'])."')
               ";
               simple_query($sql);
          }
          else 
          {
               $sql="
                    update mr_unit_locations set
                        deleted=1
                    where maint_id='".sql_friendly($maint_id)."' and mr_location like 'SNOOZE:%'
                    order by id desc
                    limit 1
               ";
               simple_query($sql);
          }    
     
          $return_val = "<rslt>1</rslt>";
          display_xml_response($return_val);
     }
	
	function mrr_update_maint_request_unit_location()
	{
		$maint_id=(int) $_POST['maint_id'];
		$type_id=(int) $_POST['etype_id'];
		$unit_id=(int) $_POST['item'];	
		$mr_location=trim($_POST['mr_location']);
				
		$truck_id=0;
		$trailer_id=0;
		
		if($type_id==1 || $type_id==58)		$truck_id=$unit_id;
		if($type_id==2 || $type_id==59)		$trailer_id=$unit_id;
		
		mrr_add_mr_unit_locations($maint_id,$truck_id,$trailer_id,$mr_location);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	function mrr_update_maint_request_unit_location_alt()
	{
		$maint_id=(int) $_POST['maint_id'];
		$type_str=trim($_POST['etype_id']);
		$unit_str=trim($_POST['item']);	
		$mr_location=trim($_POST['mr_location']);
		
		$unit_id=0;
		$type_id=0;
		if(strtolower($type_str)=="truck")	
		{
			$type_id=1;
			$sql2="
				select id				
				from trucks
				where name_truck='".sql_friendly($unit_str)."'
			";
			$data2=simple_query($sql2);
			if($row2=mysqli_fetch_array($data2))
			{
				$unit_id=$row2['id'];
			}
		}
		if(strtolower($type_str)=="trailer")	
		{
			$type_id=2;
			$sql2="
				select id				
				from trailers
				where trailer_name='".sql_friendly($unit_str)."'
			";
			$data2=simple_query($sql2);
			if($row2=mysqli_fetch_array($data2))	
			{
				$unit_id=$row2['id'];
			}
		}	
		
		$truck_id=0;
		$trailer_id=0;
		
		if($type_id==1 || $type_id==58)		$truck_id=$unit_id;
		if($type_id==2 || $type_id==59)		$trailer_id=$unit_id;
		
		mrr_add_mr_unit_locations($maint_id,$truck_id,$trailer_id,$mr_location);
          
          mrr_send_mr_msg_quick_note($maint_id,$mr_location);		
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	function mrr_send_mr_msg_note()
     {
          global $defaultsarray;
          
          $id=(int) $_POST['id'];
          $type=trim($_POST['type']);
          $unit=trim($_POST['unit']);
          $time=trim($_POST['time']);
          $user=trim($_POST['user']);
          $note=trim($_POST['note']);
          $desc="";
          
          if($id>0) 
          {
               $sql2="
				select maint_requests.equip_type,
				    maint_requests.ref_id,
				    maint_requests.maint_desc			
				from maint_requests
				where maint_requests.id='".sql_friendly($id)."'
			";
			$data2=simple_query($sql2);
			if($row2=mysqli_fetch_array($data2))
               {
                    $desc=trim($row2['maint_desc']);
                    
                    if($row2['equip_type']==1 || $row2['equip_type']==58)
                    {
                         $type="Truck";
                         $sql1="
                              select name_truck
                               from trucks 
                               where id='".sql_friendly($row2['ref_id'])."'
                         ";
                         $data1=simple_query($sql1);
                         if($row1=mysqli_fetch_array($data1))
                         {
                              $unit=trim($row1['name_truck']);
                         }                         
                    }
                    elseif($row2['equip_type']==2 || $row2['equip_type']==59)
                    {
                         $type="Trailer";
                         $sql1="
                              select trailer_name
                               from trailers 
                               where id='".sql_friendly($row2['ref_id'])."'
                         ";
                         $data1=simple_query($sql1);
                         if($row1=mysqli_fetch_array($data1))
                         {
                              $unit=trim($row1['trailer_name']);
                         }    
                    }
               }
     
          }
          
          $send_to=$defaultsarray['company_email_address'];
          $subject="New Maintenance Request Note Added";
     
          $msg1="New note added for MR:  ".$type." ".$unit." \r\n Added ".$time." by ".$user." - \r\n ".$note." \r\n MR ".$id.": ".$desc." \r\n";
          $msg2="<b>New note added for MR:  ".$type." ".$unit."</b> <br><br>Added ".$time." by ".$user." - <br><br>".$note."<br><br><a href='https://trucking.conardtransportation.com/maint.php?id=".$id."' target='_blank'>MR ".$id."</a>: ".$desc." <br><br>";
     
          //mrr_trucking_sendMail($defaultsarray['special_email_monitor'],'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          
          mrr_trucking_sendMail($send_to,'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          mrr_trucking_sendMail("conardmaintenance@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     
          ////mrr_trucking_sendMail("disenberger22@gmail.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          //mrr_trucking_sendMail("Shamm@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          //mrr_trucking_sendMail("dconard@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          //mrr_trucking_sendMail("jgriffith@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          
          $return_val = "<rslt>1</rslt>";
          display_xml_response($return_val);
     }
	
     
     function mrr_pull_accident_list()
     {
          $id=(int) $_POST['maint_id'];                    
          $cntr=0;
          
          $tab="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";     
          $tab.="<tr>                        
                        <td valign='top'><b>Date</b></td>
                        <td valign='top'><b>Accident#</b></td>
                        <td valign='top'><b>Desc</b></td>
                    </tr>";   
          
          $sql = "
          	select *
			from accident_reports
			where maint_id = '".sql_friendly($id)."' and deleted=0 and maint_id > 0
			order by id desc
          ";
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
               $tab.="
                    <tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
                        <td valign='top'>".date("m/d/Y",strtotime($row['accident_date']))."</td>
                        <td valign='top'><a href='accident_trucks.php?id=".$row['id']."' target='_blank'>".trim($row['accident_number'])."</a></td>                        
                        <td valign='top'>".$row['accident_desc']."</td>                        
                    </tr>
               ";
               
               /*     
               $driver_id=$row['driver_id'];
               $truck_id=$row['truck_id'];
               $trailer_id=$row['trailer_id'];
               $dispatch_id=$row['dispatch_id'];
               $load_id=$row['load_id'];
               $accident_date=$row['accident_date'];
               $record_date=$row['linedate_added'];
               $claim_date=$row['claim_date'];
               $insurance_claim=$row['insurance_claim'];
               $insurance_covered=$row['insurance_covered'];
               $reviewed=$row['reviewed'];
               $insurance_company=$row['insurance_company'];
               $accident_desc=$row['accident_desc'];
               $accident_cost=$row['accident_cost'];
               $accident_deductable=$row['accident_deductable'];
               $accident_downtime=$row['accident_downtime'];
               $injury_desc=$row['injury_desc'];
               $injury_cost=$row['injury_cost'];
               $injury_deductable=$row['injury_deductable'];
               $injury_downtime=$row['injury_downtime'];
               $driver_desc=$row['driver_desc'];
               $driver_cost=$row['driver_cost'];
               $driver_deductable=$row['driver_deductable'];
               $driver_downtime=$row['driver_downtime'];
               $maint_id=$row['maint_id'];
               $active=$row['active'];
               $completed_date=$row['completed_date'];
               $notes_and_updates=$row['notes_and_updates'];
               
               */     
               $cntr++;
          }
          $tab.="</table>";
          if($cntr==0)        $tab="";
     
          $return_val = "<rslt>1</rslt><Listing><![CDATA[".$tab."]]></Listing>";
          display_xml_response($return_val);
     }
	
	function mrr_update_maint_truck_inspect_item()
	{
		$id=(int) $_POST['inspect_id'];
		
		$sectid=(int) $_POST['sect_id'];
		$subid=(int) $_POST['sub_id'];	
		
		$ans_ok=(int) $_POST['ans_ok'];
		$ans_nr=(int) $_POST['ans_repairs'];
		$ans_rd=trim($_POST['ans_date']);
		$ans_nt=trim($_POST['ans_note']);
				
		//clear old entry
		$sql = "
			update maint_inspect_truck_entries set
				deleted=1
			where inspect_id='".sql_friendly($id)."'
				and sect_id='".sql_friendly($sectid)."'
				and sub_id='".sql_friendly($subid)."'
		";			
		simple_query($sql);
		if($sectid > 0 && $subid > 0)
		{
     		$sql = "
     			insert into maint_inspect_truck_entries
     				(id,
     				inspect_id,
     				sect_id,
     				sub_id,
     				okay_value,
     				repairs,
     				repairs_date,
     				repairs_notes,
     				deleted)
     			values
     				(NULL,
     				'".sql_friendly($id)."',
     				'".sql_friendly($sectid)."',
     				'".sql_friendly($subid)."',
     				'".sql_friendly($ans_ok)."',
     				'".sql_friendly($ans_nr)."',
     				'".($ans_rd!="" ? "".date("Y-m-d",strtotime($ans_rd))."" : "0000-00-00")." 00:00:00',
     				'".sql_friendly($ans_nt)."',
     				0)
     		";	
     		simple_query($sql);
		}
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	
	function mrr_update_maint_truck_inspection()
	{
		$id=$_POST['inspect_id'];
		//$=$_POST[''];
		
		$no_pass=1;			//if set, the inspection cannot be passed until the Repair Code has a value...if checkbox is selected.
		$no_pass2=1;
		$res_code=0;
		
		$meets_396_19_items=$_POST['meets_396_19_items'];
			
		$inspect_name=trim($_POST['inspector_name']);		
          $inspect_local=trim($_POST['inspector_local']);
          
          $id_type=$_POST['unit_type_id'];
          $id_num=trim($_POST['unit_type_number']);     
          
          $brake["brake_left_steering"]=$_POST['brake_left_steering'];
          $brake["brake_right_steering"]=$_POST['brake_right_steering'];
          $brake["brake_left_front"]=$_POST['brake_left_front'];
          $brake["brake_right_front"]=$_POST['brake_right_front'];
          $brake["brake_left_rear"]=$_POST['brake_left_rear'];
          $brake["brake_right_rear"]=$_POST['brake_right_rear'];
          
          $tread["tread_lso"]=$_POST['tread_lso'];
		$tread["tread_rso"]=$_POST['tread_rso'];
		$tread["tread_lfo"]=$_POST['tread_lfo'];
		$tread["tread_lfi"]=$_POST['tread_lfi'];
		$tread["tread_rfi"]=$_POST['tread_rfi'];
		$tread["tread_rfo"]=$_POST['tread_rfo'];
		$tread["tread_lro"]=$_POST['tread_lro'];
		$tread["tread_lri"]=$_POST['tread_lri'];
		$tread["tread_rri"]=$_POST['tread_rri'];
		$tread["tread_rro"]=$_POST['tread_rro'];
          
          $passed=$_POST['passed'];
                    
		mrr_update_truck_inspection($id,0,0,0,$brake,$tread,0,'',$id_type,$id_num,$inspect_name,$inspect_local,$passed,$meets_396_19_items);
		
		$passes=0;
		$fails=0;
		$skipped=0;
		$repairs=0;
		$notapps=0;
			
		$sql = "
          	select *
          	from maint_inspect_truck_entries
          	where deleted=0
          		and inspect_id='".sql_friendly($id)."'
          	order by sect_id asc, sub_id asc
          ";
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {     		
          	if($row['okay_value']==1)	$passes++;		//pass total
          	elseif($row['okay_value']==2)	$fails++;			//fail total
          	elseif($row['okay_value']==3)	$notapps++;		//NA total
          	elseif($row['okay_value']==0)	$skipped++;		//Skipped total
          	
          	if($row['repairs']==1)		$repairs++;		
          }
		
		$res_code=1;
		if($skipped > 0)						{	$passed=0;	$res_code=0;	}			//skipped answers, so needs to be completed.
		if($fails > 0 && $fails > $repairs)		{	$passed=0;	$res_code=0;	}			//failed lines...not repaired successfully.  Not passed.
		if(($passes + $notapps) ==0 || $passes==0)	{	$passed=0;	$res_code=0;	}			//no items passed (or N/A values)... or none passed (ALL N/A values) which should be impossible.
		
		if($passed==0)
		{
			mrr_pass_truck_inspection($id,$passed,0,0);					//does not pass...
		}
		
		$sql = "
			select passed,truck_id,maint_id
			from maint_inspect_trucks
			where id = '".sql_friendly($id)."'
		";
		$data= simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			if($passed > 0 && $row['passed']==0)
			{	//now passes...update trailer page datestamps for last PMI and/or FED insepctions.
				mrr_pass_truck_inspection($id,$passed,$row['maint_id'],$row['truck_id']);	
			}	
		}
				
		display_xml_response("<rslt>".$res_code."</rslt>");	
	}
	
	function mrr_update_maint_trailer_inspection()
	{
		$id=$_POST['inspect_id'];
		//$=$_POST[''];
		
		$no_pass=0;			//if set, the inspection cannot be passed until the Repair Code has a value...if checkbox is selected.
		
		$gen["qualify_section_396_19"]=$_POST['qualify_section_396_19'];
		$gen["qualify_section_396_25"]=$_POST['qualify_section_396_25'];
		
          $ckbxs["inspect_ck_reg"]=$_POST['inspect_ck_reg'];
          $ckbxs["inspect_ck_body"]=$_POST['inspect_ck_body'];
          $ckbxs["inspect_ck_frame"]=$_POST['inspect_ck_frame'];
          $ckbxs["inspect_ck_rear"]=$_POST['inspect_ck_rear'];
          $ckbxs["inspect_ck_susp"]=$_POST['inspect_ck_susp'];
          $ckbxs["inspect_ck_brake"]=$_POST['inspect_ck_brake'];
          $ckbxs["inspect_ck_wheel"]=$_POST['inspect_ck_wheel'];
          $ckbxs["inspect_ck_tires"]=$_POST['inspect_ck_tires'];
          $ckbxs["inspect_ck_light"]=$_POST['inspect_ck_light'];
          $ckbxs["inspect_ck_decal"]=$_POST['inspect_ck_decal'];     
          
          $ckbxs["inspect_ck_bpm_items"]=$_POST['inspect_ck_bpm_items'];
          $ckbxs["inspect_ck_cpm_items"]=$_POST['inspect_ck_cpm_items'];
          $ckbxs["inspect_ck_annual"]=$_POST['inspect_ck_annual'];	
          $ckbxs["inspect_ck_attach"]=$_POST['inspect_ck_attach'];
          
          $cds["inspect_cd_reg"]=$_POST['inspect_cd_reg'];
          $cds["inspect_cd_body"]=$_POST['inspect_cd_body'];
          $cds["inspect_cd_frame"]=$_POST['inspect_cd_frame'];
          $cds["inspect_cd_rear"]=$_POST['inspect_cd_rear'];
         	$cds["inspect_cd_susp"]=$_POST['inspect_cd_susp'];
          $cds["inspect_cd_brake"]=$_POST['inspect_cd_brake'];
          $cds["inspect_cd_wheel"]=$_POST['inspect_cd_wheel'];
          $cds["inspect_cd_tires"]=$_POST['inspect_cd_tires'];
          $cds["inspect_cd_light"]=$_POST['inspect_cd_light'];
          $cds["inspect_cd_decal"]=$_POST['inspect_cd_decal'];
          
         	$cds["inspect_cd_bpm_items"]=$_POST['inspect_cd_bpm_items'];
         	$cds["inspect_cd_cpm_items"]=$_POST['inspect_cd_cpm_items'];
         	$cds["inspect_cd_annual"]=$_POST['inspect_cd_annual'];
         	$cds["inspect_cd_attach"]=$_POST['inspect_cd_attach'];
         	
          $brake["brake_left_front"]=$_POST['brake_left_front'];
          $brake["brake_right_front"]=$_POST['brake_right_front'];
          $brake["brake_left_rear"]=$_POST['brake_left_rear'];
          $brake["brake_right_rear"]=$_POST['brake_right_rear'];
          
		$tread["tread_lfo"]=$_POST['tread_lfo'];
		$tread["tread_lfi"]=$_POST['tread_lfi'];
		$tread["tread_rfi"]=$_POST['tread_rfi'];
		$tread["tread_rfo"]=$_POST['tread_rfo'];
		$tread["tread_lro"]=$_POST['tread_lro'];
		$tread["tread_lri"]=$_POST['tread_lri'];
		$tread["tread_rri"]=$_POST['tread_rri'];
		$tread["tread_rro"]=$_POST['tread_rro'];
		
		mrr_update_trailer_inspection($id,0,0,$ckbxs,$cds,$gen,$tread,$brake);	//should only update an existing one...not a new one.
						
		$passed=$_POST['passed'];
		/**/
		//PMI
		if($ckbxs["inspect_ck_reg"] > 0 && $cds["inspect_cd_reg"]==0)			$no_pass++;
          if($ckbxs["inspect_ck_body"] > 0 && $cds["inspect_cd_body"]==0)			$no_pass++;
          if($ckbxs["inspect_ck_frame"] > 0 && $cds["inspect_cd_frame"]==0)		$no_pass++;
          if($ckbxs["inspect_ck_rear"] > 0 && $cds["inspect_cd_rear"]==0)			$no_pass++;
          if($ckbxs["inspect_ck_susp"] > 0 && $cds["inspect_cd_susp"]==0)			$no_pass++;
          if($ckbxs["inspect_ck_brake"] > 0 && $cds["inspect_cd_brake"]==0)		$no_pass++;
          if($ckbxs["inspect_ck_wheel"] > 0 && $cds["inspect_cd_wheel"]==0)		$no_pass++;
          if($ckbxs["inspect_ck_tires"] > 0 && $cds["inspect_cd_tires"]==0)		$no_pass++;
          if($ckbxs["inspect_ck_light"] > 0 && $cds["inspect_cd_light"]==0)		$no_pass++;
          if($ckbxs["inspect_ck_decal"] > 0 && $cds["inspect_cd_decal"]==0)		$no_pass++;
          
		//FED...other three don't matter or have no code on actual form.
		if($ckbxs["inspect_ck_annual"] > 0 && $cds["inspect_cd_annual"]==0)		$no_pass++;
		
		$bpm_sum=0;
		$bpm_sum+=$ckbxs["inspect_ck_reg"];
          $bpm_sum+=$ckbxs["inspect_ck_body"];
          $bpm_sum+=$ckbxs["inspect_ck_frame"];
          $bpm_sum+=$ckbxs["inspect_ck_rear"];
          $bpm_sum+=$ckbxs["inspect_ck_susp"];
          $bpm_sum+=$ckbxs["inspect_ck_brake"];
          $bpm_sum+=$ckbxs["inspect_ck_wheel"];
          $bpm_sum+=$ckbxs["inspect_ck_tires"];
          $bpm_sum+=$ckbxs["inspect_ck_light"];
          $bpm_sum+=$ckbxs["inspect_ck_decal"];
          
          $no_pass2=0;
		if(($ckbxs["inspect_ck_annual"]==0 && $bpm_sum==0) || ($bpm_sum > 0 && $bpm_sum < 10))	$no_pass2++;	//not all of the B-PM boxes are checked or neither C-PM or B-PM are checked
		if($passed==0)		$no_pass2=0;
		
		$res_code=1;
		if($no_pass > 0 || $no_pass2 > 0)	
		{
			$passed=0;
			$res_code=0;
		}
		
		if($passed==0)
		{
			mrr_pass_trailer_inspection($id,$passed,0,0);					//does not pass...
		}
		
		$sql = "
			select passed,trailer_id,maint_id
			from maint_inspect_trailers
			where id = '".sql_friendly($id)."'
		";
		$data= simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			if($passed > 0 && $row['passed']==0)
			{	//now passes...update trailer page datestamps for last PMI and/or FED insepctions.
				mrr_pass_trailer_inspection($id,$passed,$row['maint_id'],$row['trailer_id']);	
			}	
		}
				
		display_xml_response("<rslt>".$res_code."</rslt>");	
	}
	
	
	//Invoicing for Maint Requests...
	function mrr_make_sicap_invoice()
	{
		$new_inv_id=0;
		$inventory_id=0;
		$new_inv_rep="";
		
		global $defaultsarray;
		$labor_rate=floatval($defaultsarray['maint_labor_rate']);
		$markup_val=floatval($defaultsarray['maint_invoice_markup']);
		
		$id=$_POST['req_id'];
		$cust_id=$_POST['cust_id'];
		$sicap_id=0;
		$cust_name="";
		
		$tn_tax_rate=0.0975;
					
		$coa_trailer_number[0]="";					$coa_trailer_cat[0]="";									$coa_trailers=0;		
		$coa_trailer_number[$coa_trailers]="77500-";		$coa_trailer_cat[$coa_trailers]="Repairs";					$coa_trailers++;
		$coa_trailer_number[$coa_trailers]="77600-";		$coa_trailer_cat[$coa_trailers]="Tires";					$coa_trailers++;	
		//$coa_trailer_number[$coa_trailers]="77800-";		$coa_trailer_cat[$coa_trailers]="Wash";					$coa_trailers++;
		//$coa_trailer_number[$coa_trailers]="77485-";		$coa_trailer_cat[$coa_trailers]="Accidents";				$coa_trailers++;	
		
		$coa_truck_number[0]="";						$coa_truck_cat[0]="";									$coa_trucks=0;		
		$coa_truck_number[$coa_trailers]="41000-";		$coa_truck_cat[$coa_trailers]="Income";						$coa_trucks++;
		$coa_truck_number[$coa_trailers]="46000-";		$coa_truck_cat[$coa_trailers]="Discount";					$coa_trucks++;
		$coa_truck_number[$coa_trailers]="58800-";		$coa_truck_cat[$coa_trailers]="Fuel";						$coa_trucks++;
		$coa_truck_number[$coa_trailers]="65000-";		$coa_truck_cat[$coa_trailers]="Layover Expense";				$coa_trucks++;
		$coa_truck_number[$coa_trailers]="67000-";		$coa_truck_cat[$coa_trailers]="Lease Drivers";				$coa_trucks++;
		$coa_truck_number[$coa_trailers]="68270-";		$coa_truck_cat[$coa_trailers]="Lumper";						$coa_trucks++;
		$coa_truck_number[$coa_trailers]="74500-";		$coa_truck_cat[$coa_trailers]="Repairs";					$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="75500-";		$coa_truck_cat[$coa_trailers]="Stop off";					$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="67100-";		$coa_truck_cat[$coa_trailers]="Panther Bonus";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="77950-";		$coa_truck_cat[$coa_trailers]="Rental";						$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="78000-";		$coa_truck_cat[$coa_trailers]="Lease";						$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="78050-";		$coa_truck_cat[$coa_trailers]="Rental Mileage";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="78100-";		$coa_truck_cat[$coa_trailers]="Lease Mileage";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="79000-";		$coa_truck_cat[$coa_trailers]="Weigh Ticket";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="74000-";		$coa_truck_cat[$coa_trailers]="Accidents";					$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="74900-";		$coa_truck_cat[$coa_trailers]="Cleaning";					$coa_trucks++;
		
		$truck_default_coa=6;
		$trailer_default_coa=0;
		
		$api = new sicap_api_connector();
		$api_item_id = new sicap_api_connector();
		
		$sql = "
			select *
			from customers
			where id = '".sql_friendly($cust_id)."'
		";
		$data= simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$email_invoice=$row['email_invoice'];
			
			$sicap_customer_id=$row['sicap_id'];
			$cust_name=trim($row['name_company']);
			if($sicap_customer_id==0)		$sicap_customer_id = $api->getCustomerIDByName($cust_name);
			
			$cust_name=str_replace("&","and",$cust_name);
			if($sicap_customer_id==0)		$sicap_customer_id = $api->getCustomerIDByName($cust_name);		
						
			$new_inv_rep.="<p>".$cust_name." [".$sicap_customer_id."]</p>";
			
			$api->clearParams();
						
			$api->addParam("ShippingName",$cust_name);
			$api->addParam('CustomerID',$sicap_customer_id);
			//$api->addParam("ShippingAddress1",(trim($row['billing_address1'])!="" ? trim($row['billing_address1']) : $row['address1']));
			//$api->addParam("ShippingAddress2",(trim($row['billing_address2'])!="" ? trim($row['billing_address2']) : $row['address2']));
			//$api->addParam("ShippingCity",(trim($row['billing_city'])!="" ? trim($row['billing_city']) : $row['city']));
			//$api->addParam("ShippingState",(trim($row['billing_state'])!="" ? trim($row['billing_state']) : $row['state']));
			//$api->addParam("ShippingZip",(trim($row['billing_zip'])!="" ? trim($row['billing_zip']) : $row['zip']));	
						
               if($row['email_invoice']) 
               {
               	$api->addParam('ToBeEmailed',1);
               } 
               else 
               {
               	$api->addParam('ToBePrinted',1);
               }
               
               $api->addParam('CustomerPO','Service Invoice');	
               $api->addParam('InvoiceNotes','Service Invoice');	
               			
			$sql = "
     			select equip_type,ref_id,linedate_scheduled,linedate_completed,cost,down_time_hours,odometer_reading,maint_desc
     			from maint_requests
     			where id = '".sql_friendly($id)."'
     			order by id asc
     		";
     		$data= simple_query($sql);
     		if($row = mysqli_fetch_array($data)) 
     		{
     			$coa="";	
     			
     			$work_date=date("m/d/Y");		if($row['linedate_scheduled'] != '0000-00-00 00:00:00')	$work_date=date("m/d/Y",strtotime($row['linedate_scheduled']));
     			$comp_date=date("m/d/Y");		if($row['linedate_completed'] != '0000-00-00 00:00:00')	$comp_date=date("m/d/Y",strtotime($row['linedate_completed']));
     			
     			$type="trucks";	
     			$name="name_truck";	
     			if($row['equip_type']==2 || $row['equip_type']==59)
     			{
     				$type="trailers";
     				$name="trailer_name";	
     			}
     			$sql2 = "
     				select ".$name." as my_coa_name
     				from ".$type."
     				where id = '".sql_friendly($row['ref_id'])."'
     			";
     			$data2= simple_query($sql2);
     			if($row2 = mysqli_fetch_array($data2)) 
     			{
     				$coa=mrr_make_numeric2(trim($row2['my_coa_name']));	
     			}
     			$new_inv_rep.="<p>COA: <b>".$coa."</b></p>";			
     			/*
     			$cntr=0;
     			$arr_ids[0]=0;
     			$arr_coa[0]="";
     			$arr_num[0]="";
     			 
     			$this_id=0;
     			$this_name="";
     			$this_number="";
     			
     			
     			//find all coas for this unit.
     			if($row['equip_type']==2 || $row['equip_type']==59)
     			{
     				$results=mrr_sicap_get_accounts_for_trailer_name($coa,0);
     			}
     			else
     			{
     				$results=mrr_sicap_get_accounts_for_truck_name($coa,0);
     			}
     			
     			//$results=str_replace("<ChartEntry>","",$results);
     			//$results=str_replace("</ChartEntry>","",$results);
     			  			
          		foreach($results as $key1 => $value1 )
     			{
     				$prt1=trim($key1);		$tmp1=trim($value1);
     				foreach($tmp1 as $key => $value )
          			{
          				$prt=trim($key);		$tmp=trim($value);
          				if($prt=="ID")			$this_id=$tmp;
          				if($prt=="Name")		$this_name=trim($tmp);     				
          				if($prt=="Number")	
          				{
          					$this_number=trim($tmp);
          					
          					$arr_ids[$cntr]=$this_id;
          					$arr_coa[$cntr]=$this_name;
          					$arr_num[$cntr]=$this_number;
          					
          					$cntr++;
          					
          					$this_id=0;
          					$this_name="";
          					$this_number="";
          				}
          			}
     			}	
     			
     			$new_inv_rep.="<MRR>".trim($results)."</MRR>";
     			
     			for($i=0; $i < $cntr; $i++)
     			{
     				$new_inv_rep.="COA FOUND ".$i." [".$arr_ids[$i]."] ".$arr_num[$i].": ".$arr_coa[$i].".<br>";	
     			}	
     			
     			if($type=="trailers")
     			{
     				$use_coa_index=$trailer_default_coa;
     				
     				for($i=0; $i < $coa_trailers; $i++)
     				{     					
     					$new_inv_rep.="COA FOUND ".$i." [".$coa_trailer_number[$i]."".$coa."] ".$coa_trailer_cat[$i].".<br>";	
     				}
     			}
     			else
     			{
     				$use_coa_index=$truck_default_coa;
     				
     				for($i=0; $i < $coa_trucks; $i++)
     				{     					
     					$new_inv_rep.="COA FOUND ".$i." [".$coa_truck_number[$i]."".$coa."] ".$coa_truck_cat[$i].".<br>";	
     				}
     			}
     			*/
     			
     			//get inventory item for SICAP invoice...will be used for all items on this maint request based on trailer or truck repairs. 
     			$inventory_id = 0;
     			if($type=="trailers")
     			{
     				$inventory_id = $api_item_id->getInventoryIDByName($coa."-Trailer Repairs");
     				
     				if($inventory_id==0)		$inventory_id=1161;
     			}
     			else
     			{
     				$inventory_id = $api_item_id->getInventoryIDByName($coa."-Truck Repairs");	
     			}
     			
     			
     			$counter = 0;
     			$make_total=0;
     			
     			$use_markup=1;
     			if($markup_val > 0)		$use_markup+=$markup_val;
     			//$use_markup=1;     			     			
     			
     			$new_inv_rep.="<p>Line Items:</p>";
     			//now get the line items for this request...
     			$sql2 = "
     				select *
     				from maint_line_items
     				where deleted=0 
     					and active>0
     					and ref_id='".sql_friendly($id)."'
     				order by id asc
     			";
     			$data2= simple_query($sql2);
     			while($row2 = mysqli_fetch_array($data2)) 
     			{
     				$desc=trim($row2['lineitem_desc']);
     				$part_desc=$desc;	
     				
     				$qty=$row2['quantity'];
     				
     				
     				$desc2=$qty;
     				if($row2['cat_id'] > 0)	$desc2.=" ".option_value_text($row2['cat_id'],2);
     				$desc2.=" ".trim($row2['make']);
     				$desc2.=" ".trim($row2['model']);
     				if($row2['location_front'] > 0)	$desc2.=" ".option_value_text($row2['location_front'],2);
     				if($row2['location_left'] > 0)	$desc2.=" ".option_value_text($row2['location_left'],2);
     				if($row2['location_top'] > 0)		$desc2.=" ".option_value_text($row2['location_top'],2);
     				if($row2['location_inside'] > 0)	$desc2.=" ".option_value_text($row2['location_inside'],2);
     				
     				$hours=$row2['down_time_hours'];
     				$value=$row2['item_cost'];
     				
     				if(trim($desc2) !="" && trim($desc2)!="0")
     				{
     					$new_inv_rep.="<br>".trim($desc2).".";	//details used...display them
     					$part_desc=trim($desc2);	
     				}
     				else
     				{
     					$new_inv_rep.="<br>".trim($desc).".";	//not used, so just show the basic description
     				}
     				$test_cat=option_value_text($row2['cat_id'],2);
     				$test_cat=strtolower($test_cat);
     				     				
     				$use_coa_index=$truck_default_coa;
     				if($type=="trailers")	
     				{
     					$use_coa_index=$trailer_default_coa;
     					if(substr_count($test_cat,"tires") > 0)		$use_coa_index=1;
     				}
     				
     				//$labor_rate=floatval($defaultsarray['maint_labor_rate']);
					//$markup_val=floatval($defaultsarray['maint_invoice_markup']);
     						
     				
     				if($hours > 0)
     				{
          				$counter++;
     					$api->addParam("Item_".$counter."_ID", $inventory_id);
     					$api->addParam("Item_".$counter."_Price", ($value * $use_markup));
     					$api->addParam("Item_".$counter."_Qty", $qty);
     					$api->addParam("Item_".$counter."_QtyShipped", $qty);
     					$api->addParam("Item_".$counter."_Desc", "Labor: ".trim($desc)."");	//(".number_format(($hours * $labor_rate),2)." hrs at $".number_format($labor_rate,2)."/hr)=$".number_format($hours,2)."
     		
     					$make_total+=($value * $use_markup);
     				}
     				else
     				{
     					$counter++;
     					$api->addParam("Item_".$counter."_ID", $inventory_id);
     					$api->addParam("Item_".$counter."_Price", ($value * $use_markup));
     					$api->addParam("Item_".$counter."_Qty", $qty);
     					$api->addParam("Item_".$counter."_QtyShipped", $qty);
     					$api->addParam("Item_".$counter."_Desc", "Parts: ".$part_desc."");			// Price=$".number_format($value,2)."
     		
     					$make_total+=($value * $use_markup);	
     				}
     				
     				if($value > 0)		$new_inv_rep.=" Price=$".number_format(($value * $use_markup),2)."";
     				if($hours > 0)		$new_inv_rep.=" Labor=$".number_format(($value * $use_markup),2)."";	    				
     			}   
     			
     			if($tn_tax_rate > 0 && $make_total > 0)
				{	//compute sales tax and add as a line item on hte invoice.
					$sales_tax= $make_total * $tn_tax_rate;     	
					
					$new_inv_rep.="<br>TN Sales Tax=$".number_format(($sales_tax),2)."";
					
					$counter++;
     				$api->addParam("Item_".$counter."_ID", 1758);
     				$api->addParam("Item_".$counter."_Price", $sales_tax);
     				$api->addParam("Item_".$counter."_Qty", 1);
     				$api->addParam("Item_".$counter."_QtyShipped", 1);
     				$api->addParam("Item_".$counter."_Desc", "TN Sales Tax");			// Price=$".number_format($value,2)."
					
					$make_total+=$sales_tax;		
				}
     			
     			//now make invoice...
     			if($make_total > 0)	
     			{     				
     				//$comp_date	
     				$api->addParam("PickUp", '');
					$api->addParam("ItemCount", $counter);	
					
					$api->addParam("InvoiceDate", date("m/d/Y"));
               		$api->addParam("InvoiceDateCustomer", date("m/d/Y"));
               		$api->addParam("ShipDate", $comp_date);
               		
               		//$api->addParam("TNSalesTax", $sales_tax);               		
               		
               		$api->command = "update_invoice";
	
					if($_SERVER['REMOTE_ADDR'] == '173.10.208.206') {
						//$api->debug_post = true;
						//$api->show_output = true;
					}
	
					$rslt = $api->execute();
					$new_inv_id=(int) $rslt->InvoiceNumber;
					
					mrr_set_sicap_maint_invoice($id, $new_inv_id , $cust_id);					
     			}	
     		}
			
		}
		else
		{
			$new_inv_rep.="<p>No Customer</p>";	
		}
		
		display_xml_response("<rslt>1</rslt><InvoiceID>".$new_inv_id."</InvoiceID><InventoryItem>".$inventory_id."</InventoryItem><CustID>".$cust_id."</CustID><InvoiceTab><![CDATA[".$new_inv_rep."]]></InvoiceTab>");		
	}
	function mrr_kill_sicap_invoice()
	{
		$id=$_POST['req_id'];
		mrr_kill_sicap_maint_invoice($id);
		
		display_xml_response("<rslt>1</rslt>");		
	}
	
	//Invoicing for Trailers...
	function mrr_make_sicap_invoice_trailer()
	{
		$new_inv_id=0;
		$inventory_id=0;
		$new_inv_rep="";
		
		global $defaultsarray;
		
		//$id=$_POST['id'];
		$cust_id=$_POST['cust_id'];
		$date_from=$_POST['date_from'];
		$date_to=$_POST['date_to'];
		
		$sicap_id=0;
		$cust_name="";
		
		$coa_id=1672;				$coa_number="45000";									$coa_label="Misc Income - Trailer Parking";		
		$inventory_id = 1284;		$item_name="Misc Income - Trailer Parking";					$item_desc="Misc Income from Trailer Parking reimbursement";
				
		$bill_rate=trim($defaultsarray['trailer_billing_rate']);
		if(!is_numeric($bill_rate))		$bill_rate=100.00;
		
		$api = new sicap_api_connector();
		$api_item_id = new sicap_api_connector();
		
		$sql = "
			select *
			from customers
			where id = '".sql_friendly($cust_id)."'
		";
		$data= simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$email_invoice=$row['email_invoice'];
			
			$sicap_customer_id=$row['sicap_id'];
			$cust_name=trim($row['name_company']);
			if($sicap_customer_id==0)		$sicap_customer_id = $api->getCustomerIDByName($cust_name);
			
			$cust_name=str_replace("&","and",$cust_name);
			if($sicap_customer_id==0)		$sicap_customer_id = $api->getCustomerIDByName($cust_name);		
						
			$new_inv_rep.="<p>".$cust_name." [".$sicap_customer_id."]</p>";
			
			
			$api->clearParams();
						
			$api->addParam("ShippingName",$cust_name);
			$api->addParam('CustomerID',$sicap_customer_id);
						
               if($row['email_invoice']) {
               	$api->addParam('ToBeEmailed',1);
               } else {
               	$api->addParam('ToBePrinted',1);
               }
               
               $counter = 0;
     		$make_total=0;   
     		$last_date="";         			
			
			$sql = "
     			select trailers_dropped.*,
     				(select trailers.trailer_name from trailers where trailers.id=trailers_dropped.trailer_id) as trailer_name
     			from trailers_dropped
     			where trailers_dropped.customer_id = '".sql_friendly($cust_id)."'
     				and trailers_dropped.invoice_pending>0
     				and trailers_dropped.deleted=0
     			order by trailers_dropped.trailer_id asc, trailers_dropped.linedate_completed asc
     		";
     		$data= simple_query($sql);
     		while($row = mysqli_fetch_array($data)) 
     		{
     			//$coa="";	
     			
     			$date1=date("m/d/Y");		
     			$date2=date("m/d/Y");		
     			     			
     			if($row['linedate'] != '0000-00-00 00:00:00')				$date1=date("m/d/Y",strtotime($row['linedate']));
     			
     			if($last_date=="")		$last_date=$date1;
     			
     			if($row['linedate_completed'] != '0000-00-00 00:00:00')
     			{
     				$date2=date("m/d/Y",strtotime($row['linedate_completed']));	
     				if(date("Y-m-d",strtotime($row['linedate_completed'])) >= date("Y-m-d",strtotime($last_date)))
     				{
     					$last_date=$date2;  	
     				}
     			}		
     			     			
     			$trailer_id=$row['trailer_id'];
     			$city=trim($row['location_city']);			$city=str_replace(",","",$city);
     			$state=trim($row['location_state']);		$state=str_replace(",","",$state);
     			 			     			
     			$local="";
     			
     			$item_desc="".$date1." to ".$date2.": Trailer ".trim($row['trailer_name'])." - Dropped in ".$city.", ".$state.".";
     			    			
     			//$inventory_id = 1284;		$item_name="Misc Income - Trailer Parking";					$item_desc="Misc Income from Trailer Parking reimbursement";
     			//$inventory_id = $api_item_id->getInventoryIDByName("".$coa."");	//Lease Drivers - #".$coa."".$rent_lab."
          				     				
          		$qty=$row['invoice_pending'];	
     			
     			$amnt=$qty * $bill_rate;
     			
     			$counter++;
          		$api->addParam("Item_".$counter."_ID", $inventory_id);
          		$api->addParam("Item_".$counter."_Price", $amnt);
          		$api->addParam("Item_".$counter."_Qty", $qty);
          		$api->addParam("Item_".$counter."_QtyShipped", $qty);
          		$api->addParam("Item_".$counter."_Desc", "Labor: ".trim($item_desc)."");	
          		
          		$new_inv_rep.="
          			<br>".$counter.". ".$qty." x ".$bill_rate." = ".$amnt.". ".$item_desc."
          		";
          		
          		$make_total+=$amnt;
     			
     			//now make invoice...
     			if($make_total > 0)	
     			{
     				//$comp_date	
     				$api->addParam("PickUp", $local);
					$api->addParam("ItemCount", $counter);	
					
					$api->addParam("InvoiceDate", $last_date);
               		$api->addParam("InvoiceDateCustomer", $last_date);
               		$api->addParam("ShipDate", $last_date);
               		
               		$api->addParam('CustomerPO','');	//Trailer Parking Invoice
               		
               		$api->addParam('InvoiceNotes','Trailer Parking Invoice from '.$date1.' through '.$date2.'');	// '.$local.'
               		
               		$api->command = "update_invoice";
               			
					$rslt = $api->execute();
					$new_inv_id=(int) $rslt->InvoiceNumber;
					
					mrr_set_sicap_trailer_invoice_all($new_inv_id);					
     			}	
     		}	//end while loop		
		}
		else
		{
			$new_inv_rep.="<p>No Customer</p>";	
		}
		
		display_xml_response("<rslt>1</rslt><InvoiceID>".$new_inv_id."</InvoiceID><InventoryItem>".$inventory_id."</InventoryItem><CustID>".$cust_id."</CustID><InvoiceTab><![CDATA[".$new_inv_rep."]]></InvoiceTab>");		
	}
	function mrr_kill_sicap_invoice_trailer()
	{
		$id=$_POST['id'];							
		mrr_kill_sicap_trailer_invoice($id);			//ID is Drop ID for trailer
		
		display_xml_response("<rslt>1</rslt>");		
	}
	function mrr_kill_sicap_invoice_trailer_all()
	{
		$id=$_POST['id'];
		mrr_kill_sicap_trailer_invoice_all($id);		//ID is invoice ID
		
		display_xml_response("<rslt>1</rslt>");		
	}
	
	
	//Invoicing for Timesheets...
	function mrr_make_sicap_invoice_timesheet()
	{
		$new_inv_id=0;
		$inventory_id=0;
		$new_inv_rep="";
		
		global $defaultsarray;
		$regular_hrly_rate=floatval($defaultsarray['carlex_regular_hrly_rate']);
		$sunday_hrly_rate=floatval($defaultsarray['carlex_sunday_hrly_rate']);
          
          $shuttle_mileage_fuel_rate=0.00;
          $shuttle_total_miles=0;
		
		$id=$_POST['id'];
		$cust_id=$_POST['cust_id'];
		$sicap_id=0;
		$cust_name="";
					
		$coa_trailer_number[0]="";					$coa_trailer_cat[0]="";									$coa_trailers=0;		
		$coa_trailer_number[$coa_trailers]="77500-";		$coa_trailer_cat[$coa_trailers]="Repairs";					$coa_trailers++;
		$coa_trailer_number[$coa_trailers]="77600-";		$coa_trailer_cat[$coa_trailers]="Tires";					$coa_trailers++;	
		//$coa_trailer_number[$coa_trailers]="77800-";		$coa_trailer_cat[$coa_trailers]="Wash";					$coa_trailers++;
		//$coa_trailer_number[$coa_trailers]="77485-";		$coa_trailer_cat[$coa_trailers]="Accidents";				$coa_trailers++;	
		
		$coa_truck_number[0]="";						$coa_truck_cat[0]="";									$coa_trucks=0;		
		$coa_truck_number[$coa_trailers]="41000-";		$coa_truck_cat[$coa_trailers]="Income";						$coa_trucks++;
		$coa_truck_number[$coa_trailers]="46000-";		$coa_truck_cat[$coa_trailers]="Discount";					$coa_trucks++;
		$coa_truck_number[$coa_trailers]="58800-";		$coa_truck_cat[$coa_trailers]="Fuel";						$coa_trucks++;
		$coa_truck_number[$coa_trailers]="65000-";		$coa_truck_cat[$coa_trailers]="Layover Expense";				$coa_trucks++;
		$coa_truck_number[$coa_trailers]="67000-";		$coa_truck_cat[$coa_trailers]="Lease Drivers";				$coa_trucks++;
		$coa_truck_number[$coa_trailers]="68270-";		$coa_truck_cat[$coa_trailers]="Lumper";						$coa_trucks++;
		$coa_truck_number[$coa_trailers]="74500-";		$coa_truck_cat[$coa_trailers]="Repairs";					$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="75500-";		$coa_truck_cat[$coa_trailers]="Stop off";					$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="67100-";		$coa_truck_cat[$coa_trailers]="Panther Bonus";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="77950-";		$coa_truck_cat[$coa_trailers]="Rental";						$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="78000-";		$coa_truck_cat[$coa_trailers]="Lease";						$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="78050-";		$coa_truck_cat[$coa_trailers]="Rental Mileage";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="78100-";		$coa_truck_cat[$coa_trailers]="Lease Mileage";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="79000-";		$coa_truck_cat[$coa_trailers]="Weigh Ticket";				$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="74000-";		$coa_truck_cat[$coa_trailers]="Accidents";					$coa_trucks++;
		//$coa_truck_number[$coa_trailers]="74900-";		$coa_truck_cat[$coa_trailers]="Cleaning";					$coa_trucks++;
		
		$truck_default_coa=6;
		$trailer_default_coa=0;
		
		$api = new sicap_api_connector();
		$api_item_id = new sicap_api_connector();
		
		$sql = "
			select *
			from customers
			where id = '".sql_friendly($cust_id)."'
		";
		$data= simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$email_invoice=$row['email_invoice'];
			
			$sicap_customer_id=$row['sicap_id'];
			$cust_name=trim($row['name_company']);
			if($sicap_customer_id==0)		$sicap_customer_id = $api->getCustomerIDByName($cust_name);
			
			$cust_name=str_replace("&","and",$cust_name);
			if($sicap_customer_id==0)		$sicap_customer_id = $api->getCustomerIDByName($cust_name);		
						
			$new_inv_rep.="<p>".$cust_name." [".$sicap_customer_id."]</p>";
			
			$api->clearParams();
						
			$api->addParam("ShippingName",$cust_name);
			$api->addParam('CustomerID',$sicap_customer_id);
			//$api->addParam("ShippingAddress1",(trim($row['billing_address1'])!="" ? trim($row['billing_address1']) : $row['address1']));
			//$api->addParam("ShippingAddress2",(trim($row['billing_address2'])!="" ? trim($row['billing_address2']) : $row['address2']));
			//$api->addParam("ShippingCity",(trim($row['billing_city'])!="" ? trim($row['billing_city']) : $row['city']));
			//$api->addParam("ShippingState",(trim($row['billing_state'])!="" ? trim($row['billing_state']) : $row['state']));
			//$api->addParam("ShippingZip",(trim($row['billing_zip'])!="" ? trim($row['billing_zip']) : $row['zip']));	
						
               if($row['email_invoice']) {
               	$api->addParam('ToBeEmailed',1);
               } else {
               	$api->addParam('ToBePrinted',1);
               }
               
               $local_id=1;
               $shuttle_mileage_fuel_rate=0.0000;
               $shuttle_total_miles=0;
               
               $sqlt="
                     select trucks_log_shuttle_routes.id,
                         option_values.dummy_val                 
                     from trucks_log_shuttle_routes
                         left join option_values on option_values.id=trucks_log_shuttle_routes.option_id      
                     where trucks_log_shuttle_routes.deleted=0 
                         and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($id)."'
               ";
               $datat=simple_query($sqlt);
               while($rowt = mysqli_fetch_array($datat))
               {
                    $tmiles=trim($rowt['dummy_val']);
                    $tmiles=str_replace("$","",$tmiles);
                    $tmiles=str_replace(",","",$tmiles);
                    $tmiles=str_replace(" ","",$tmiles);
                    
                    if(is_numeric($tmiles) && $tmiles > 0)
                    {
                         $tmiles=round($tmiles,2);
                         $shuttle_total_miles+=$tmiles;
                    }
               }
               $new_inv_rep.="<br>Query is ".$sqlt."...<br>";
                              			
			$sql = "
     			select *
     			from timesheets
     			where id = '".sql_friendly($id)."'
     			order by id asc
     		";
     		$data= simple_query($sql);
     		if($row = mysqli_fetch_array($data)) 
     		{
     			//$coa="";	
     			
     			$date1=date("m/d/Y");		if($row['linedate_start'] != '0000-00-00 00:00:00')	$date1=date("m/d/Y",strtotime($row['linedate_start']));
     			$date2=date("m/d/Y");		if($row['linedate_end'] != '0000-00-00 00:00:00')		$date2=date("m/d/Y",strtotime($row['linedate_end']));
               
                    $shuttle_mileage_fuel_rate=$row['shuttle_mileage_fuel_rate'];
                    
     			$load_id=$row['load_id'];
     			$disp_id=$row['trucks_log_id'];
     			
     			/*
     			$type="trucks";	
     			$name="name_truck";	
     			if($row['equip_type']==2 || $row['equip_type']==59)
     			{
     				$type="trailers";
     				$name="trailer_name";	
     			}
     			$sql2 = "
     				select ".$name." as my_coa_name
     				from ".$type."
     				where id = '".sql_friendly($row['ref_id'])."'
     			";
     			$data2= simple_query($sql2);
     			if($row2 = mysqli_fetch_array($data2)) 
     			{
     				$coa=mrr_make_numeric2(trim($row2['my_coa_name']));	
     			}
     			*/
     			
     			//$new_inv_rep.="<p>COA: <b>".$coa."</b></p>";			
     			
     			//get inventory item for SICAP invoice...will be used for all items on this maint request based on trailer or truck repairs. 
     			$inventory_id = 0;
     			/*
     			if($type=="trailers")
     			{
     				$inventory_id = $api_item_id->getInventoryIDByName($coa."-Trailer Repairs");
     				
     				if($inventory_id==0)		$inventory_id=1161;
     			}
     			else
     			{
     				$inventory_id = $api_item_id->getInventoryIDByName($coa."-Truck Repairs");	
     			}
     			*/
     			
     			$counter = 0;
     			$make_total=0;
     			
     			//$use_markup=1;
     			//if($markup_val > 0)		$use_markup+=$markup_val;
     			//$use_markup=1;   
     			  			     			
     			$local="";
     			if($cust_id==1601 || $cust_id==2233 || $cust_id==2031)
     			{
     				if($row['location_id']==1)		$local="Carlex Glass-Nashville Plant";
     				if($row['location_id']==2)		$local="Carlex Glass-Lebanon Plant";
     			}
     			elseif($cust_id==1687)
     			{
     				if($row['location_id']==1)		$local="Vietti-Nashville";
     				if($row['location_id']==2)		$local="Vietti-Lebanon";	
     			}
     			
     			if($cust_id==1601 || $cust_id==2233 || $cust_id==2031)
     			{	//For Carlex  ...show only summary section 
     				
          			$route_adder="";
          			$routes=0;
          			$route_arr[0]=0;
          			$route_lab[0]="";
          			$route_miles[0]=0;
                         $route_rate[0]=0;
          			$route_ratehr[0]=0;
          			$route_add[0]="";
          			$route_addhr[0]="";
          			$route_addhrc[0]="";
                    
                         $coa="";
                         $inventory_id=0;
          			
          			$hourly_route=145;    
               		//$use_pay_rate=option_value_text($hourly_route,2);		//grab from the NONE - Switching ONLY rate.
                         $use_pay_rate=$regular_hrly_rate;
                         //$use_pay_rate=$sunday_hrly_rate;
                         
          			if($row['location_id']==1)		$local="Carlex Glass-Nashville Plant";
     				if($row['location_id']==2)	{	$local="Carlex Glass-Lebanon Plant";		$local_id=2;	}
          			
          			$sql1a = "
          				select *
          				from option_values
          				where deleted=0 and cat_id=23 and id!=145
          				order by zorder asc, fname asc, id asc
          			";
          			$data1a= simple_query($sql1a);
          			while($row1a = mysqli_fetch_array($data1a)) 
          			{
          				$route_arr[$routes]=$row1a['id'];	
          				$route_lab[$routes]=trim($row1a['fname']);	
          				$route_rate[$routes]=trim($row1a['fvalue']);	
          				$route_ratehr[$routes]=$use_pay_rate;	
          				$route_add[$routes]="route_".sql_friendly($row1a['id'])."";
          				$route_addhr[$routes]="route_".sql_friendly($row1a['id'])."_hours";
          				$route_addhrc[$routes]="route_".sql_friendly($row1a['id'])."_hours_conard";
          				                                      				
          				$route_adder.="
          					(
                                     	select COUNT(*) 
                                     	from trucks_log_shuttle_routes 
                                     	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                     		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                     		and trucks_log_shuttle_routes.deleted = 0 
                                     		and trucks_log_shuttle_routes.option_id = '".sql_friendly($row1a['id'])."'
                                     ) as route_".sql_friendly($row1a['id']).",    
                                     (
                                     	select SUM(trucks_log_shuttle_routes.hours - trucks_log_shuttle_routes.lunch_break) 
                                     	from trucks_log_shuttle_routes 
                                     	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                     		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                     		and trucks_log_shuttle_routes.deleted = 0 
                                     		and trucks_log_shuttle_routes.option_id = '".sql_friendly($row1a['id'])."'
                                     ) as route_".sql_friendly($row1a['id'])."_hours,         
                                     (
                                     	select SUM(trucks_log_shuttle_routes.conard_hours - trucks_log_shuttle_routes.lunch_break) 
                                     	from trucks_log_shuttle_routes 
                                     	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                     		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                     		and trucks_log_shuttle_routes.deleted = 0 
                                     		and trucks_log_shuttle_routes.option_id = '".sql_friendly($row1a['id'])."'
                                     ) as route_".sql_friendly($row1a['id'])."_hours_conard,   				
          				";          				
          				$routes++;
          			}        			
          			$trucks=0;
          			$truck_arr[0]=0; 
          			
          			$new_inv_rep="";
          			
          			$sql1a = "
          				select distinct(trucks_log_shuttle_routes.truck_id)
          				from trucks_log_shuttle_routes
          				where trucks_log_shuttle_routes.deleted=0 
          					and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($id)."'
          				order by trucks_log_shuttle_routes.linedate_start asc,
          					trucks_log_shuttle_routes.linedate_end asc,
          					trucks_log_shuttle_routes.linedate_from asc,
          					trucks_log_shuttle_routes.linedate_to asc,
          					trucks_log_shuttle_routes.id asc
          			";
          			$data1a= simple_query($sql1a);
          			while($row1a = mysqli_fetch_array($data1a)) 
          			{         			
          				$truck_arr[$trucks]=$row1a['truck_id'];		
          				
          				$sql2 = "
          					select trucks.id,
                                          trucks.name_truck as truck_name,
                                          trucks.rental as rental,
                                          trucks.leased_from as leased_from,
                                          ".$route_adder."
                                          (
                                          	select COUNT(*)
                                          	from trucks_log_shuttle_routes
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."'
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id
                                            		and trucks_log_shuttle_routes.deleted = 0
                                            		and trucks_log_shuttle_routes.is_sunday > 0
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as route_145_sun,
                                          (
                                          	select SUM(trucks_log_shuttle_routes.hours - trucks_log_shuttle_routes.lunch_break)
                                          	from trucks_log_shuttle_routes
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."'
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id
                                            		and trucks_log_shuttle_routes.deleted = 0
                                            		and trucks_log_shuttle_routes.is_sunday > 0
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as hours_145_sun,
                                          (
                                          	select SUM(trucks_log_shuttle_routes.conard_hours - trucks_log_shuttle_routes.lunch_break)
                                          	from trucks_log_shuttle_routes
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."'
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id
                                            		and trucks_log_shuttle_routes.deleted = 0
                                            		and trucks_log_shuttle_routes.is_sunday > 0
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as cconard_145_sun,
                                          (
                                          	select COUNT(*) 
                                          	from trucks_log_shuttle_routes 
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."'  
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                            		and trucks_log_shuttle_routes.deleted = 0
                                            		and trucks_log_shuttle_routes.is_sunday = 0
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as route_145,
                                          (
                                          	select SUM(trucks_log_shuttle_routes.hours - trucks_log_shuttle_routes.lunch_break) 
                                          	from trucks_log_shuttle_routes 
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                            		and trucks_log_shuttle_routes.deleted = 0
                                            		and trucks_log_shuttle_routes.is_sunday = 0
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as hours_145,
                                          (
                                          	select SUM(trucks_log_shuttle_routes.conard_hours - trucks_log_shuttle_routes.lunch_break) 
                                          	from trucks_log_shuttle_routes 
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                            		and trucks_log_shuttle_routes.deleted = 0
                                            		and trucks_log_shuttle_routes.is_sunday = 0
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as cconard_145 
                              	from trucks 
                                   where trucks.id ='".sql_friendly($row1a['truck_id'])."'
                                   order by trucks.name_truck asc
               			";
               					/*
               					(
                                          	select SUM((trucks_log_shuttle_routes.hours - trucks_log_shuttle_routes.lunch_break) * trucks_log_shuttle_routes.pay_charged_per_hour)
                                          	from trucks_log_shuttle_routes 
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                            		and trucks_log_shuttle_routes.deleted = 0 
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as pay_145,
                                          (
                                          	select SUM((trucks_log_shuttle_routes.conard_hours - trucks_log_shuttle_routes.lunch_break) * trucks_log_shuttle_routes.pay_charged_per_hour)
                                          	from trucks_log_shuttle_routes 
                                          	where trucks_log_shuttle_routes.timesheet_id = '".sql_friendly($id)."' 
                                            		and trucks_log_shuttle_routes.truck_id = trucks.id 
                                            		and trucks_log_shuttle_routes.deleted = 0 
                                            		and trucks_log_shuttle_routes.option_id = '".sql_friendly($hourly_route)."'
                                          ) as cpay_145,               					
               					*/
               			$data2= simple_query($sql2);
               			while($row2 = mysqli_fetch_array($data2)) 
               			{          				
          					//get chart/inventory item....
                         		$coa=mrr_make_numeric2(trim($row2['truck_name']));
                    			$rental=$row2['rental'];
                              	$rent_lab="";
                              	if($rental > 0)
                              	{
                              		$rent_lab=" (Rental)";
                              		if(trim($row2['leased_from'])!="")		$rent_lab=" (Rental) ".trim($row2['leased_from'])."";
                              	}     				
                    			
                    			$inventory_id = $api_item_id->getInventoryIDByName("".$coa."");	//Lease Drivers - #".$coa."".$rent_lab."
                    			//if($inventory_id==0)	$api_item_id->getInventoryIDByName("".$coa."");	
                    			               				
                    			//route_145   
                    			//$qty2=$row2['hours_145'];            				     				
                    			$qty=floatval($row2['hours_145']);		//cconard_145
                    
                                   //$qty2_sun=$row2['hours_145_sun'];
                                   $qty_sun=floatval($row2['hours_145_sun']);	//cconard_145_sun
                    				
                    			//if(substr_count($qty2,".25") > 0 || substr_count($qty2,".3") > 0 || substr_count($qty2,".5") > 0 || substr_count($qty2,".6") > 0 || substr_count($qty2,".75") > 0)		$qty++;
                    
                                   $use_pay_rate=$regular_hrly_rate;
                                   
                    			$labor=$use_pay_rate;
                    			$labor_sun=$sunday_hrly_rate;
                    			
          					if($row['location_id']==1)
          					{	//NASHVILLE INVOICES
          						$offset_hrs=0;
          						$offset_pay=0;
          						
          						//extract all the shuttle run hourly rates.
          						for($x=0;$x < $routes; $x++)
                    				{
                    					if((int) ($row2[''.trim($route_add[$x]).'']) > 0)
                    					{
                    						//$qty1=floatval($row2[''.trim($route_add[$x]).'']);
                    						$qtyhr=floatval($row2[''.trim($route_addhr[$x]).'']);
                    						//$qty3=floatval($row2[''.trim($route_addhrc[$x]).'']);
                    						
                    						//$labor1=trim($route_rate[$x]);
                    						$laborhr=trim($route_ratehr[$x]);
                    						
                    						//$route_arr[$x] ID here
                    						
                    						//$counter++;
                    						//$api->addParam("Item_".$counter."_ID", $inventory_id);
                    						//$api->addParam("Item_".$counter."_Price", $labor);
                    						//$api->addParam("Item_".$counter."_Qty", $qty);
                    						//$api->addParam("Item_".$counter."_QtyShipped", $qty);
                    						//$api->addParam("Item_".$counter."_Desc", "Shuttle Movements from ".$route_lab[$x]."");	
                    		          		//$make_total+=($labor * $qty);        
                    		          		                    		          		
                    		          		$offset_hrs+=$qtyhr;     
                    		          		$offset_pay+=($qtyhr * $laborhr);
                                             }               					
                    				}
                    				                    				
                    				//$qty-=$offset_hrs;		//Adjust the total hours worked section by the hours in the shuttle runs (if any).
                    				
                    				                    				
                    				//Hours Worked section....
                    				$main_hrs_worked=0;
                    				
                    				$counter++;
                    				$api->addParam("Item_".$counter."_ID", $inventory_id);
                    				$api->addParam("Item_".$counter."_Price", $labor);
                    				$api->addParam("Item_".$counter."_Qty", $qty);
                    				$api->addParam("Item_".$counter."_QtyShipped", $qty);
                    				$api->addParam("Item_".$counter."_Desc", "Total # of Hours (REGULAR) Worked");	
                    		          $make_total+=($labor * $qty);
                    		          $main_hrs_worked=($labor * $qty);
                              
                                        $counter++;
                                        $api->addParam("Item_".$counter."_ID", $inventory_id);
                                        $api->addParam("Item_".$counter."_Price", $labor_sun);
                                        $api->addParam("Item_".$counter."_Qty", $qty_sun);
                                        $api->addParam("Item_".$counter."_QtyShipped", $qty_sun);
                                        $api->addParam("Item_".$counter."_Desc", "Total # of Hours (SUNDAY) Worked");
                                        $make_total+=($labor_sun * $qty_sun);
                                        $main_hrs_worked=($labor_sun * $qty_sun);
                    				
                    				/*              				
                    				$qty=0;				
                    				$labor=73.00;               				
                    				$counter++;
                    				$api->addParam("Item_".$counter."_ID", $inventory_id);
                    				$api->addParam("Item_".$counter."_Price", $labor);
                    				$api->addParam("Item_".$counter."_Qty", $qty);
                    				$api->addParam("Item_".$counter."_QtyShipped", $qty);
                    				$api->addParam("Item_".$counter."_Desc", "Total # of Hours (Sunday / Holiday) Worked");	
                    		          $make_total+=($labor * $qty);
                    				*/
                    				
                    				//Truck #, U, Description,                              		Quantity, Price,         Amount
                         			//827702   0  Total # of Hours (REGULAR) Worked         		40        $62.00      $2,480.00
                         			//827702   0  Total # of Hours (Sunday / Holiday) Worked		0		$73.00		  0.00
                         			//==============================================================================================
                         			//============Shuttle Movements=================================================================
                         			//827702   0  Shuttle Movements from Lebanon to Lebanon		1         $10.00		$10.00
                         			//827702   0  Shuttle Movements from Lebanon to Murfreesboro	2         $50.00	    $100.00
                         			//827702   0  Shuttle Movements from Lebanon to Nashville		3         $45.00	    $135.00
                         			//----------------------------------------------------------------------------------------------
                         			// 
                    				
                    				$first_route=0;
                    				
                    				for($x=0;$x < $routes; $x++)
                    				{
                    					if((int) ($row2[''.trim($route_add[$x]).'']) > 0)
                    					{
                    						//$qty2=($row2[''.trim($route_add[$x]).'']);
                    					
                    						$qty=floatval($row2[''.trim($route_add[$x]).'']);
                    						$qtyhr=floatval($row2[''.trim($route_addhr[$x]).'']);
                    						//$qtyhr=floatval($row2[''.trim($route_addhrc[$x]).'']);
                    						
                    						//if(substr_count($qty2,".25") > 0 || substr_count($qty2,".3") > 0 || substr_count($qty2,".5") > 0 || substr_count($qty2,".6") > 0 || substr_count($qty2,".75") > 0)		$qty++;
                    						
                    						$labor=trim($route_rate[$x]);
                    						$laborhr=trim($route_ratehr[$x]);
                    						
                    						//$route_arr[$x] ID here
                    						
                    						$counter++;
                    						$api->addParam("Item_".$counter."_ID", $inventory_id);
                    						$api->addParam("Item_".$counter."_Price", $labor);
                    						$api->addParam("Item_".$counter."_Qty", $qty);
                    						$api->addParam("Item_".$counter."_QtyShipped", $qty);
                    						$api->addParam("Item_".$counter."_Desc", "Shuttle Movements from ".$route_lab[$x]."");	
                    						
                    						$make_total+=($labor * $qty);  
                    						
                    						$counter++;
                    						$api->addParam("Item_".$counter."_ID", $inventory_id);
                    						$api->addParam("Item_".$counter."_Price", $laborhr);
                    						$api->addParam("Item_".$counter."_Qty", $qtyhr);
                    						$api->addParam("Item_".$counter."_QtyShipped", $qtyhr);
                    						$api->addParam("Item_".$counter."_Desc", "Hours for ".$route_lab[$x]."");	
                    						
                    		          		$make_total+=($laborhr * $qtyhr);  
                    		          		
                    		          		
                    		          		$gtotal=(($labor * $qty) + ($laborhr * $qtyhr));
                    		          		if($first_route==0)		$gtotal+=$main_hrs_worked;
                    		          		
                    		          		//subtotal line
                    		          		$counter++;
                    						$api->addParam("Item_".$counter."_ID", $inventory_id);
                    						$api->addParam("Item_".$counter."_Price", $gtotal);
                    						$api->addParam("Item_".$counter."_Qty", 0);
                    						$api->addParam("Item_".$counter."_QtyShipped", 0);
                    						$api->addParam("Item_".$counter."_Desc", "".$route_lab[$x]." Subtotal");	
                    						
                    						
                    						//blank line
                    						$counter++;
                    						$api->addParam("Item_".$counter."_ID", $inventory_id);
                    						$api->addParam("Item_".$counter."_Price", 0);
                    						$api->addParam("Item_".$counter."_Qty", 1);
                    						$api->addParam("Item_".$counter."_QtyShipped", 1);
                    						$api->addParam("Item_".$counter."_Desc", "-");	
                    		          		
                    		          		$first_route++;           		          		
                    					}               					
                    				}
          					}          			
          					elseif($row['location_id']==2)
          					{    //LEBANON INVOICES
                                        $offset_hrs=0;
                                        $offset_pay=0;
                                        /*
                                         * //defined before the split for Nashville vs Lebanon... to get the hours and pay from the Non-Switching routes.
                                         //route_145              				     				
                                        $qty=floatval($row2['hours_145']);		//cconard_145
                                        $qty_sun=floatval($row2['hours_145_sun']);	//cconard_145_sun
                                        $use_pay_rate=$regular_hrly_rate;                                        
                                        $labor=$use_pay_rate;
                                        $labor_sun=$sunday_hrly_rate; 
                                         
                                         */
                                        $mrr_spec_shuttle_run_hrs=0;                                     
                                        
                                        
                              
                                        $main_hrs_worked=0;
                                        $labor_worked=90.00;
                                        $tot_hrs_worked=0;
                                        
                              
                                        //extract all the shuttle run hourly rates.
                                        for($x=0;$x < $routes; $x++)
                                        {
                                             if((int) ($row2[''.trim($route_add[$x]).'']) > 0)
                                             {
                                                  //$qty2=($row2[''.trim($route_add[$x]).'']);
                                        
                                                  $qtyx=floatval($row2[''.trim($route_add[$x]).'']);
                                        
                                                  //if(substr_count($qty2,".25") > 0 || substr_count($qty2,".3") > 0 || substr_count($qty2,".5") > 0 || substr_count($qty2,".6") > 0 || substr_count($qty2,".75") > 0)		$qty++;
                                        
                                                  $laborx=trim($route_rate[$x]);
                                        
                                                  //$route_arr[$x] ID here
                                                  
                                                  $qtyhr=floatval($row2[''.trim($route_addhr[$x]).'']);
                                                  $laborhr=trim($route_ratehr[$x]);    
                                                                                                    
                                                  if($qtyhr > 0)
                                                  {    //add the hrs that are within a shuttle run.  Rare, but if they are present, add them in.
                                                       $mrr_spec_shuttle_run_hrs+=$qtyhr;
                                                  }                                                                                                  
                                        
                                                  $counter++;
                                                  $api->addParam("Item_".$counter."_ID", $inventory_id);
                                                  $api->addParam("Item_".$counter."_Price", $laborx);
                                                  $api->addParam("Item_".$counter."_Qty", $qtyx);
                                                  $api->addParam("Item_".$counter."_QtyShipped", $qtyx);
                                                  $api->addParam("Item_".$counter."_Desc", "Shuttle Movements from ".$route_lab[$x]."");
                                                  $make_total+=($laborhr * $qtyhr);
     
                                                  $offset_hrs+=$qtyhr;
                                                  $offset_pay+=($laborhr * $qtyhr);
     
                                                  $main_hrs_worked+=$qtyhr;      //(($labor * $qty) + ($laborhr * $qtyhr))
                                                  $labor_worked=$laborhr;
                                             }
                                        }
                                        $tot_hrs_worked = ($main_hrs_worked * $labor_worked);
                                        
                    				//Hours Worked section....
                    				//$counter++;
                    				//$api->addParam("Item_".$counter."_ID", $inventory_id);
                    				//$api->addParam("Item_".$counter."_Price", $labor_worked);
                    				//$api->addParam("Item_".$counter."_Qty", $main_hrs_worked);
                    				//$api->addParam("Item_".$counter."_QtyShipped", $main_hrs_worked);
                    				//$api->addParam("Item_".$counter."_Desc", "Total # of Hours (REGULAR) Worked");	
                    		          //$make_total+=$tot_hrs_worked;
                              
                              
                              
                              
                              
                                        //Hours Worked section....
                                        $main_hrs_worked=0;
                              
                                        $counter++;
                                        $api->addParam("Item_".$counter."_ID", $inventory_id);
                                        $api->addParam("Item_".$counter."_Price", $labor);
                                        $api->addParam("Item_".$counter."_Qty", ($qty + $mrr_spec_shuttle_run_hrs));
                                        $api->addParam("Item_".$counter."_QtyShipped", ($qty + $mrr_spec_shuttle_run_hrs));
                                        $api->addParam("Item_".$counter."_Desc", "Total # of Hours (REGULAR) Worked");
                                        $make_total+=($labor * ($qty + $mrr_spec_shuttle_run_hrs));
                                        $main_hrs_worked=($labor * ($qty + $mrr_spec_shuttle_run_hrs));
                              
                                        $counter++;
                                        $api->addParam("Item_".$counter."_ID", $inventory_id);
                                        $api->addParam("Item_".$counter."_Price", $labor_sun);
                                        $api->addParam("Item_".$counter."_Qty", $qty_sun);
                                        $api->addParam("Item_".$counter."_QtyShipped", $qty_sun);
                                        $api->addParam("Item_".$counter."_Desc", "Total # of Hours (SUNDAY) Worked");
                                        $make_total+=($labor_sun * $qty_sun);
                                        $main_hrs_worked=($labor_sun * $qty_sun);
                    				
                    				/*              				
                    				$qty=0;				
                    				$labor=73.00;               				
                    				$counter++;
                    				$api->addParam("Item_".$counter."_ID", $inventory_id);
                    				$api->addParam("Item_".$counter."_Price", $labor);
                    				$api->addParam("Item_".$counter."_Qty", $qty);
                    				$api->addParam("Item_".$counter."_QtyShipped", $qty);
                    				$api->addParam("Item_".$counter."_Desc", "Total # of Hours (Sunday / Holiday) Worked");	
                    		          $make_total+=($labor * $qty);
                    				*/
                    				
                    				//Truck #, U, Description,                              		Quantity, Price,         Amount
                         			//827702   0  Total # of Hours (REGULAR) Worked         		40        $62.00      $2,480.00
                         			//827702   0  Total # of Hours (Sunday / Holiday) Worked		0		$73.00		  0.00
                         			//==============================================================================================
                         			//============Shuttle Movements=================================================================
                         			//827702   0  Shuttle Movements from Lebanon to Lebanon		1         $10.00		$10.00
                         			//827702   0  Shuttle Movements from Lebanon to Murfreesboro	2         $50.00	    $100.00
                         			//827702   0  Shuttle Movements from Lebanon to Nashville		3         $45.00	    $135.00
                         			//----------------------------------------------------------------------------------------------
                         			// 
                    				
                                        
                                        /*  remove if testing is good
                    				for($x=0;$x < $routes; $x++)
                    				{
                    					if((int) ($row2[''.trim($route_add[$x]).'']) > 0)
                    					{
                    						//$qty2=($row2[''.trim($route_add[$x]).'']);
                    					
                    						$qty=floatval($row2[''.trim($route_add[$x]).'']);
                    						
                    						//if(substr_count($qty2,".25") > 0 || substr_count($qty2,".3") > 0 || substr_count($qty2,".5") > 0 || substr_count($qty2,".6") > 0 || substr_count($qty2,".75") > 0)		$qty++;
                    						
                    						$labor=trim($route_rate[$x]);
                    						                              
                    						//$route_arr[$x] ID here
                    						
                    						$counter++;
                    						$api->addParam("Item_".$counter."_ID", $inventory_id);
                    						$api->addParam("Item_".$counter."_Price", $labor);
                    						$api->addParam("Item_".$counter."_Qty", $qty);
                    						$api->addParam("Item_".$counter."_QtyShipped", $qty);
                    						$api->addParam("Item_".$counter."_Desc", "Shuttle Movements from ".$route_lab[$x]."");	
                    		          		$make_total+=($labor * $qty);               		          		
                    					}               					
                    				}	              				
                    				
                                        ....remove to here if testign is good... */
                    				
                    				
                    			}	//end of location 2 (Lebanon)	
                    			
                    		}    //end of each truck COA set   			
               			
               			//blank line
    						$counter++;
    						$api->addParam("Item_".$counter."_ID", $inventory_id);
    						$api->addParam("Item_".$counter."_Price", 0);
    						$api->addParam("Item_".$counter."_Qty", 1);
    						$api->addParam("Item_".$counter."_QtyShipped", 1);
    						$api->addParam("Item_".$counter."_Desc", "-");	
               				          				
               			$trucks++;
               		}   //end of each truck 
                         
                         if($shuttle_total_miles > 0 && $shuttle_mileage_fuel_rate>0)
                         {    //$shuttle_mileage_fuel_rate * $shuttle_total_miles   ....Added on 12/11/2019 by MRR for Dale.
                              $counter++;
                              $api->addParam("Item_" . $counter . "_ID", $inventory_id);
                              $api->addParam("Item_" . $counter . "_Price", $shuttle_mileage_fuel_rate);
                              $api->addParam("Item_" . $counter . "_Qty", $shuttle_total_miles);
                              $api->addParam("Item_" . $counter . "_QtyShipped", $shuttle_total_miles);
                              $api->addParam("Item_" . $counter . "_Desc", "Shuttle Mileage");
                              $make_total += ($shuttle_mileage_fuel_rate * $shuttle_total_miles);                              
                         }
                         
          			/*
          			$new_inv_rep.="<p>Line Items:</p>";
          			//now get the line items for this request...
          			$sql2 = "
          				select trucks_log_shuttle_routes.*,
          					(select CONCAT(drivers.name_driver_first, ' ', drivers.name_driver_last) from drivers where drivers.id=trucks_log_shuttle_routes.driver_id) as driver_name,
          					trucks.name_truck as truck_name,
          					trucks.rental as rental,
          					trucks.leased_from as leased_from,
          					(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as route_display
          				from trucks_log_shuttle_routes
          					left join trucks on trucks.id=trucks_log_shuttle_routes.truck_id
          				where trucks_log_shuttle_routes.deleted=0 
          					and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($id)."'
          				order by trucks.name_truck asc,
          					trucks_log_shuttle_routes.linedate_start asc,
          					trucks_log_shuttle_routes.linedate_end asc,
          					trucks_log_shuttle_routes.linedate_from asc,
          					trucks_log_shuttle_routes.linedate_to asc,
          					trucks_log_shuttle_routes.id asc
          			";
          			$data2= simple_query($sql2);
          			while($row2 = mysqli_fetch_array($data2)) 
          			{
          				$sub_load=$row2['load_id'];
          				$sub_disp=$row2['trucks_log_id']; 				
          				
          				$cust_id=$row2['customer_id'];
          				
          				$route_rate=0;
          				$route="";
          				if($row2['option_id'] > 0 && $row2['option_id']!=145)		$route=": ".trim($row2['route_display']);
          				
          				$disp_date=date("m/d/Y", strtotime($row2['linedate_from']));
          				//$disp_date.=" From ".date("H:i", strtotime($row2['linedate_start']))." To ".date("H:i", strtotime($row2['linedate_end']))."";
          				$disp_date.=" From ".date("H:i", strtotime($row2['linedate_from']))." To ".date("H:i", strtotime($row2['linedate_to']))."";
          				
          				$use_pay_rate=$row2['pay_rate_hours'];
     					$use_pay_rate=option_value_text(145,2);		//grab from the NONE - Switching ONLY rate.
     					
     					if($cust_id==1687)		$use_pay_rate=option_value_text(159,2);		//grab from the NONE - Switching ONLY rate.
     					
     					//pay_charged_per_hour
     					//pay_charged_per_mile
     					
          				
          				$labor=($row2['hours'] - $row2['lunch_break']) * $use_pay_rate;
          				//$labor=$row2['conard_hours'] * $use_pay_rate;
          				
          				$all_miles=($row2['miles'] + $row2['miles_deadhead']);
               			//if($all_miles > 0)			$labor+=($all_miles * $row2['pay_rate']);
               			
               			if($row2['route_rate'] > 0)		$route_rate=$row2['route_rate'];
               			     				
          				$coa=mrr_make_numeric2(trim($row2['truck_name']));
          				$rental=$row2['rental'];
                    		$rent_lab="";
                    		if($rental > 0)
                    		{
                    			$rent_lab=" (Rental)";
                    			if(trim($row2['leased_from'])!="")		$rent_lab=" (Rental) ".trim($row2['leased_from'])."";
                    		}     				
          				
          				$inventory_id = $api_item_id->getInventoryIDByName("".$coa."");	//Lease Drivers - #".$coa."".$rent_lab."
          				//if($inventory_id==0)	$api_item_id->getInventoryIDByName("".$coa."");	
          				     				
          				$qty=1;		
          				
          				$driver="";		if($row2['driver_id'] > 0)		$driver=". ".trim($row2['driver_name']);
          				
          				$desc="".$disp_date."".$driver."";					//".$route."
          				
          				
     					//standard labor items and shuttle lines....more details.
     					if($labor > 0)
          				{
               				$counter++;
          					$api->addParam("Item_".$counter."_ID", $inventory_id);
          					$api->addParam("Item_".$counter."_Price", $labor);
          					$api->addParam("Item_".$counter."_Qty", $qty);
          					$api->addParam("Item_".$counter."_QtyShipped", $qty);
          					$api->addParam("Item_".$counter."_Desc", "Labor: ".trim($desc)."");	
          		
          					$make_total+=$labor;
          				}
          				if($route_rate > 0)
          				{
          					$counter++;
          					$api->addParam("Item_".$counter."_ID", $inventory_id);
          					$api->addParam("Item_".$counter."_Price", $route_rate);
          					$api->addParam("Item_".$counter."_Qty", $qty);
          					$api->addParam("Item_".$counter."_QtyShipped", $qty);
          					$api->addParam("Item_".$counter."_Desc", "Shuttle: ".$disp_date."".$driver."".$route."");			
          		
          					$make_total+=$route_rate;	
          				}
          				
          				if($labor > 0)			$new_inv_rep.=" Labor=$".number_format($labor,2)."";
          				if($route_rate > 0)		$new_inv_rep.=" Shuttle=$".number_format($route_rate,2)."";	    				
          			}   
          			*/		
     			}
     			else
     			{	//Vietti  $cust_id==1687  ...and everyone else until they all have special needs.
          			     			
          			//$carlex_labor=0;			
          			//$carlex_holiday=0;		
          			
          			$new_inv_rep.="<p>Line Items:</p>";
          			//now get the line items for this request...
          			$sql2 = "
          				select trucks_log_shuttle_routes.*,
          					(select CONCAT(drivers.name_driver_first, ' ', drivers.name_driver_last) from drivers where drivers.id=trucks_log_shuttle_routes.driver_id) as driver_name,
          					trucks.name_truck as truck_name,
          					trucks.rental as rental,
          					trucks.leased_from as leased_from,
          					(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as route_display
          				from trucks_log_shuttle_routes
          					left join trucks on trucks.id=trucks_log_shuttle_routes.truck_id
          				where trucks_log_shuttle_routes.deleted=0 
          					and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($id)."'
          				order by trucks_log_shuttle_routes.linedate_start asc,
          					trucks_log_shuttle_routes.linedate_end asc,
          					trucks_log_shuttle_routes.linedate_from asc,
          					trucks_log_shuttle_routes.linedate_to asc,
          					trucks_log_shuttle_routes.id asc
          			";
          			$data2= simple_query($sql2);
          			while($row2 = mysqli_fetch_array($data2)) 
          			{
          				$sub_load=$row2['load_id'];
          				$sub_disp=$row2['trucks_log_id']; 				
          				
          				$cust_id=$row2['customer_id'];
          				
          				$route_rate=0;
          				$route="";
          				if($row2['option_id'] > 0 && $row2['option_id']!=145)		$route=": ".trim($row2['route_display']);
          				
          				$disp_date=date("m/d/Y", strtotime($row2['linedate_from']));
          				//$disp_date.=" From ".date("H:i", strtotime($row2['linedate_start']))." To ".date("H:i", strtotime($row2['linedate_end']))."";
          				$disp_date.=" From ".date("H:i", strtotime($row2['linedate_from']))." To ".date("H:i", strtotime($row2['linedate_to']))."";
          				
          				$use_pay_rate=$row2['pay_rate_hours'];
     					$use_pay_rate=option_value_text(145,2);		//grab from the NONE - Switching ONLY rate.
     					
     					if($cust_id==1687)		$use_pay_rate=option_value_text(159,2);		//grab from the NONE - Switching ONLY rate.
     					
     					//pay_charged_per_hour
     					//pay_charged_per_mile
     					
          				
          				$labor=($row2['hours'] - $row2['lunch_break']) * $use_pay_rate;
          				//$labor=$row2['conard_hours'] * $use_pay_rate;
          				
          				$all_miles=($row2['miles'] + $row2['miles_deadhead']);
               			//if($all_miles > 0)			$labor+=($all_miles * $row2['pay_rate']);
               			
               			if($row2['route_rate'] > 0)		$route_rate=$row2['route_rate'];
               			     				
          				$coa=mrr_make_numeric2(trim($row2['truck_name']));
          				$rental=$row2['rental'];
                    		$rent_lab="";
                    		if($rental > 0)
                    		{
                    			$rent_lab=" (Rental)";
                    			if(trim($row2['leased_from'])!="")		$rent_lab=" (Rental) ".trim($row2['leased_from'])."";
                    		}     				
          				
          				$inventory_id = $api_item_id->getInventoryIDByName("".$coa."");	//Lease Drivers - #".$coa."".$rent_lab."
          				//if($inventory_id==0)	$api_item_id->getInventoryIDByName("".$coa."");	
          				     				
          				$qty=1;		
          				
          				$driver="";		if($row2['driver_id'] > 0)		$driver=". ".trim($row2['driver_name']);
          				
          				$desc="".$disp_date."".$driver."";					//".$route."
          				
          				
     					//standard labor items and shuttle lines....more details.
     					if($labor > 0)
          				{
               				$counter++;
          					$api->addParam("Item_".$counter."_ID", $inventory_id);
          					$api->addParam("Item_".$counter."_Price", $labor);
          					$api->addParam("Item_".$counter."_Qty", $qty);
          					$api->addParam("Item_".$counter."_QtyShipped", $qty);
          					$api->addParam("Item_".$counter."_Desc", "Labor: ".trim($desc)."");	
          		
          					$make_total+=$labor;
          				}
          				if($route_rate > 0)
          				{
          					$counter++;
          					$api->addParam("Item_".$counter."_ID", $inventory_id);
          					$api->addParam("Item_".$counter."_Price", $route_rate);
          					$api->addParam("Item_".$counter."_Qty", $qty);
          					$api->addParam("Item_".$counter."_QtyShipped", $qty);
          					$api->addParam("Item_".$counter."_Desc", "Shuttle: ".$disp_date."".$driver."".$route."");			
          		
          					$make_total+=$route_rate;	
          				}
          				
          				if($labor > 0)			$new_inv_rep.=" Labor=$".number_format($labor,2)."";
          				if($route_rate > 0)		$new_inv_rep.=" Shuttle=$".number_format($route_rate,2)."";	    				
          			}   
     			}
     			     			
     			//now make invoice...
     			if($make_total > 0)	
     			{
     				//$comp_date	
     				$api->addParam("PickUp", $local);
					$api->addParam("ItemCount", $counter);	
					
					$api->addParam("InvoiceDate", $date2);
               		$api->addParam("InvoiceDateCustomer", $date2);
               		$api->addParam("ShipDate", $date2);
               		               		
               		if(($cust_id==1601 || $cust_id==2233 || $cust_id==2031) && $local_id==1)
               		{	//Nashville
               			//Address ID=4157
               			$api->addParam('CustomerAddrID',4157);	
               		}
               		if(($cust_id==1601 || $cust_id==2233 || $cust_id==2031) && $local_id==2)
               		{	//Lebanon
               			//Address ID=4158
               			$api->addParam('CustomerAddrID',4158);	
               		}
               		
               		$api->addParam('CustomerPO','Timesheet Invoice');	
               		
               		$api->addParam('InvoiceNotes','Timesheet Invoice '.$local.' from '.$date1.' through '.$date2.'');	
               		
               		$api->command = "update_invoice";
               			
					$rslt = $api->execute();
					$new_inv_id=(int) $rslt->InvoiceNumber;
					
					mrr_set_sicap_timesheet_invoice($id, $new_inv_id);					
     			}	
     		}
			
		}
		else
		{
			$new_inv_rep.="<p>No Customer</p>";	
		}
		
		$rval_added="<InvoiceTab><![CDATA[".$new_inv_rep."]]></InvoiceTab><InvoiceRate><![CDATA[".$shuttle_mileage_fuel_rate."]]></InvoiceRate><InvoiceMiles><![CDATA[".$shuttle_total_miles."]]></InvoiceMiles>";
          		
		display_xml_response("<rslt>1</rslt><InvoiceID>".$new_inv_id."</InvoiceID><InventoryItem>".$inventory_id."</InventoryItem><CustID>".$cust_id."</CustID>".$rval_added."");		
	}
	function mrr_kill_sicap_invoice_timesheet()
	{
		$id=$_POST['id'];
		mrr_kill_sicap_timesheet_invoice($id);
		
		display_xml_response("<rslt>1</rslt>");		
	}

     function mrr_set_miles_timesheet_invoice()
     {
          $id=$_POST['id'];
          $rate=trim($_POST['rate']);        //shuttle_mileage_fuel_rate
     
          $rate=str_replace("$","",$rate);
          $rate=str_replace(",","",$rate);
          $rate=str_replace(" ","",$rate);
          
          $sql="
               update timesheets set
                    shuttle_mileage_fuel_rate='".sql_friendly($rate)."'
               where id='".sql_friendly($id)."'
          ";
          simple_query($sql);
     
          display_xml_response("<rslt>1</rslt>");
     }
     function mrr_get_miles_timesheet_invoice()
     {
          $id=$_POST['id'];
          $rate="0.00";      //shuttle_mileage_fuel_rate
                    
          $sql="
                    select shuttle_mileage_fuel_rate 
                    from timesheets
                    where id='".sql_friendly($id)."'
               ";
          $data=simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {
               $rate=$row['shuttle_mileage_fuel_rate'];
          }
          
          display_xml_response("<rslt>1</rslt><rate>".$rate."</rate>");
     }
     function mrr_calc_miles_timesheet_invoice()
     {
          $id=$_POST['id'];
          $miles=0.00;       
          
          $sql="
                select trucks_log_shuttle_routes.id,
                    option_values.dummy_val                 
                from trucks_log_shuttle_routes
                    left join option_values on option_values.id=trucks_log_shuttle_routes.option_id      
                where trucks_log_shuttle_routes.deleted=0 
                    and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($id)."'
               ";
          $data=simple_query($sql);
          while($row = mysqli_fetch_array($data))
          {
               $route_miles=trim($row['dummy_val']);
               $route_miles=str_replace("$","",$route_miles);
               $route_miles=str_replace(",","",$route_miles);
               $route_miles=str_replace(" ","",$route_miles);
               
               
               if(is_numeric($route_miles) && $route_miles > 0)
               {
                    $route_miles=round($route_miles,2);
                    $miles=$miles + $route_miles;
               }
          }
          
          display_xml_response("<rslt>1</rslt><miles>".$miles."</miles>");
     }
		
	function mrr_update_stop_fault_grade()
	{
		$id=$_POST['stop_id'];
		$grade=$_POST['grade_id'];
		$fault=$_POST['fault_id'];
		
		$cust_id=$_POST['cust_id'];
		$driver_id=$_POST['driver_id'];
		$truck_id=$_POST['truck_id'];
		$trailer_id=$_POST['trailer_id'];
		
		$note=trim($_POST['stop_reason']);
				
		$sql="
			update load_handler_stops set 
				stop_grade_id='".sql_friendly($grade)."',
				stop_grade_note='".sql_friendly($note)."',				
				grade_fault_customer_id='".sql_friendly($cust_id)."',
				grade_fault_driver_id='".sql_friendly($driver_id)."',
				grade_fault_truck_id='".sql_friendly($truck_id)."',
				grade_fault_trailer_id='".sql_friendly($trailer_id)."',				
				grade_fault_id='".sql_friendly($fault)."'				
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);	
				
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);		
	}
	
	function mrr_update_dispatch_flat_costs()
	{
		$id=$_POST['disp_id'];
		$cost=money_strip($_POST['cost']);
		$fuel=money_strip($_POST['fuel']);
				
		$sql="
			update trucks_log set 
				flat_cost_rate='".sql_friendly($cost)."',
				flat_cost_fuel_rate='".sql_friendly($fuel)."'				
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);	
				
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);		
	}
	
	
	//IM application 
	function display_dispatch_im_msgs()
	{
		$msgs="<div class='im_msg_box'>";		
		$msgs.="<table width='100%' cellpadding='0 cellspacing='0' border='0'>";	
		$cntr=0;	
		$sound=0;
		
		$cur_time=date("H:i:s",time());
		
		$sql = "
			select dispatch_im.*,
				(TIME_TO_SEC(DATE_FORMAT(dispatch_im.linedate_added,'%H:%i:%s')) - TIME_TO_SEC('".$cur_time."')) as differ,
				(select users.username from users where users.id=dispatch_im.user_id) as from_username,
				(select users.username from users where users.id=dispatch_im.to_user_id) as to_username			
			from dispatch_im
			where (dispatch_im.to_user_id = '0' or dispatch_im.to_user_id = '".sql_friendly($_SESSION['user_id'])."' or dispatch_im.user_id = '".sql_friendly($_SESSION['user_id'])."')
				and dispatch_im.deleted = '0'
				and dispatch_im.linedate_added >='".date("Y-m-d",time())." 00:00:00'
			order by dispatch_im.linedate_added desc,id desc
			limit 10
		";
		$data= simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$del_opt="";
			$from="".trim($row['from_username']).": ";
			if($_SESSION['user_id']==$row['user_id'])	
			{
				$from="<b>".trim($row['from_username'])."</b>: ";
				$del_opt="<span class='alert' onClick='mrr_dispatch_im_msg_kill(".$row['id'].");' style='cursor:pointer;'><b>X</b></span>";
			}
			else
			{
				if(abs($row['differ']) <= 30)		$sound=1;		//trigger sound file to play...in JQuery	
			}
			$toread="";
			if($row['to_user_id'] > 0)	$toread="<b>(To ".trim(strtoupper($row['to_username'])).") -- </b>";
			
			//$row['im_read']
						
			if($cntr > 0)	$msgs.="<tr><td valign='top' colspan='2'><hr></td></tr>";
						
			$msgs.="
				<tr>
					<td valign='top'>".$del_opt." ".$from."</td>
					<td valign='top' align='right'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
				</tr>
				<tr>
					<td valign='top' colspan='2'>".$toread."".trim($row['im_msg'])."</td>
				</tr>
			";					
			$cntr++;
		}
		$msgs.="</table>";
		$msgs.="</div>";	
		
		$return_val = "<rslt>1</rslt><DispMsg><![CDATA[".$msgs."]]></DispMsg><DispSound><![CDATA[".$sound."]]></DispSound>";	//	<SQL><![CDATA[".$sql."]]></SQL>
		display_xml_response($return_val);	
	}
	function add_dispatch_im_msg()
	{
		$id=$_POST['user_id'];
		$note=trim($_POST['im_msg']);
		
		$sql = "
			insert into dispatch_im 
				(id,
				linedate_added,
				user_id,
				to_user_id,
				im_read,
				im_msg,				
				deleted)
			values 
				(NULL,
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($id)."',
				0,
				'".sql_friendly(trim($note))."',
				0)
		";
		simple_query($sql);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);			
	}
	function kill_dispatch_im_msg()
	{
		$id=$_POST['msg_id'];
		
		$sql = "update dispatch_im set deleted='".sql_friendly($_SESSION['user_id'])."' where id='".sql_friendly($id)."'";
		simple_query($sql);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);			
	}
	
	function mrr_bypass_geotab_location_updater()
	{
		$id=(int) $_POST['id'];
		$hrs=(int) $_POST['hrs'];
		
		$dater=date("Y-m-d",time())." 00:00:00";
		if($hrs > 0)	$dater=date("Y-m-d H:i:s",strtotime("+".$hrs." hours",time()))."";	
		if($hrs < 0)	$dater=date("Y-m-d H:i:s",strtotime("-".abs($hrs)." hours",time()))."";	
		
		$sql = "update trucks set geotab_last_location_date='".$dater."' where id='".sql_friendly($id)."'";
		simple_query($sql);		
		
		$return_val = "<rslt>1</rslt><ID><![CDATA[".$id."]]></ID><HRS><![CDATA[".$hrs."]]></HRS><SQL><![CDATA[".$sql."]]></SQL>";		
		display_xml_response($return_val);	
	}
	
	function ajax_mrr_find_truck_tracking_dispatch_record_all()
	{		
		$id=$_POST['id'];
		$tab=mrr_find_truck_tracking_dispatch_record_all($id,0,0);
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp>";		
		display_xml_response($return_val);	
	}
	
	function mrr_auto_complete_dispatch_from_report()
	{
		$id=$_POST['id'];	
		$done=$_POST['done'];	
		
		$sql = "
			update trucks_log set
				dispatch_completed='".sql_friendly($done)."'
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	
	//Switching/Shuttle TIMESHEETS...added Dec 2015
	function kill_cust_timesheets()
	{
		$id=$_POST['id'];	
		
		$sql = "
			update timesheets set
				deleted='1'
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
		//$myid=mysql_insert_id();
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	function add_cust_timesheets()
	{
		global $datasource;

		$id=$_POST['id'];
		$cust_id=$_POST['customer_id'];
		$start_date=$_POST['start_date'];
		$end_date=$_POST['end_date'];
		//$inv_date=$_POST['invoice_date'];
		$location_id=$_POST['location_id'];
		$runs=$_POST['runs'];
		
		$load_id=0;	//(int) $_POST['load_id'];
		$disp_id=0;	//(int) $_POST['disp_id'];
		
		$datetime1="0000-00-00 00:00:00";		if(trim($start_date)!="")	$datetime1=date("Y-m-d H:i:s",strtotime($start_date))."";	
		$datetime2="0000-00-00 00:00:00";		if(trim($end_date)!="")		$datetime2=date("Y-m-d H:i:s",strtotime($end_date))."";
		//$datetime3="0000-00-00 00:00:00";	if(trim($inv_date)!="")		$datetime3=date("Y-m-d H:i:s",strtotime($inv_date))."";
		
		if($id==0)
		{
     		$sql = "
     			insert into timesheets
     				(id,
     				customer_id,
     				user_id,
     				trucks_log_id,
     				load_id,
     				linedate_added,	
     				linedate_start,
     				linedate_end,	
     				shuttle_runs,
     				location_id,		
     				deleted)
     			values
     				(NULL,
     				'".sql_friendly($cust_id)."',
     				'".sql_friendly($_SESSION['user_id'])."',
     				'".sql_friendly($disp_id)."',
     				'".sql_friendly($load_id)."',
     				NOW(),
     				'".sql_friendly($datetime1)."',
     				'".sql_friendly($datetime2)."',
     				'".sql_friendly($runs)."',
     				'".sql_friendly($location_id)."',
     				0)
     		";
     		simple_query($sql);
     		$id=mysqli_insert_id($datasource);
		}
		else
		{
			//trucks_log_id='".sql_friendly($disp_id)."',
			//load_id='".sql_friendly($load_id)."',
			$sql = "
     			update timesheets set   		
     				
     				linedate_start='".sql_friendly($datetime1)."',
     				linedate_end='".sql_friendly($datetime2)."',
     				shuttle_runs='".sql_friendly($runs)."',
     				location_id='".sql_friendly($location_id)."'
     				
     			where id = '".sql_friendly($id)."'
     		";
     		simple_query($sql);
		}
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$id."]]></Disp>";		
		display_xml_response($return_val);	
	}
	function list_cust_timesheets()
	{
		$cust_id=$_POST['cust_id'];
		if($cust_id==0)
		{
			$return_val = "<rslt>1</rslt><Disp></Disp>";		
			display_xml_response($return_val);	
			
			return;	
		}
		
		$mode=$_POST['mode'];		// show all timesheets, not invoiced, or only invoiced.
		
		$tab="
			<div style='height:400px; overflow:scroll;'>
			<table class='table table-bordered well'>
			<thead>
			<tr>
				<th>Edit</th>
				<th>Date From</th>
				<th>Date To</th>
				<th>Location</th>
				<th>Shuttle Runs</th>
				<th>Invoice ID</th>
				<th>Invoiced</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
			<tbody>
		";
		
		$cntr=0;
		
		$sql = "
			select timesheets.*,
				(select users.username from users where users.id=timesheets.user_id) as myuser
			from timesheets
			where timesheets.deleted=0 
				and timesheets.customer_id='".sql_friendly($cust_id)."'
				".($mode==1 ? " and timesheets.invoice_id = 0" : "")."
				".($mode==2 ? " and timesheets.invoice_id > 0" : "")."
			order by timesheets.linedate_end desc,
					timesheets.linedate_start desc,					
					timesheets.id desc
			
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{	
			$local="";
			if($row['location_id']==1)		$local="Nashville";		//Carlex-
			if($row['location_id']==2)		$local="Lebanon";		//Carlex-
			
			$tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top'><button class='btn btn-success' onClick='select_cust_timesheets(".$row['id'].");'><span class='glyphicon glyphicon-pencil'></span>&nbsp;</button></td>
					<td valign='top'>".($row['linedate_start']!="0000-00-00 00:00:00"  ? "".date("m/d/Y", strtotime($row['linedate_start']))."" : "")."</td>
					<td valign='top'>".($row['linedate_end']!="0000-00-00 00:00:00"  ? "".date("m/d/Y", strtotime($row['linedate_end']))."" : "")."</td>
					<td valign='top'>".trim($local)."</td>
					<td valign='top'>".$row['shuttle_runs']."</td>
					<td valign='top'>".($row['invoice_id'] > 0  ? "<a href='https://trucking.conardtransportation.com/accounting/invoice.php?invoice_id=".$row['invoice_id']."' target='_blank'>".$row['invoice_id']."</a>" : "")."</td>
					<td valign='top'>".($row['linedate_invoiced']!="0000-00-00 00:00:00"  ? "".date("m/d/Y", strtotime($row['linedate_invoiced']))."" : "")."</td>
					<td valign='top'>".trim($row['myuser'])."</td>	
					<td valign='top'><button class='btn btn-danger' onClick='kill_cust_timesheets(".$row['id'].");'><span class='glyphicon glyphicon-trash'></span>&nbsp;</button></td>		
				</tr>
			";
			//<td valign='top'>".($row['load_id'] > 0  ? "<a href='manage_load.php?load_id=".$row['load_id']."' target='_blank'>".$row['load_id']."</a>" : "")."</td>
			//<td valign='top'>".($row['trucks_log_id'] > 0  ? "<a href='add_entry_truck.php?id=".$row['trucks_log_id']."' target='_blank'>".$row['trucks_log_id']."</a>" : "")."</td>
			$cntr++;
		}
		$tab.="</tbody></table></div>";	
		
		if($cntr==0)		$tab="";
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp>";		
		display_xml_response($return_val);		
	}
	
	
	function form_timesheets_entries()
	{		
		$id=$_POST['timesheet'];
		if($id==0)
		{
			$return_val = "<rslt>1</rslt><Disp></Disp><entries>0</entries>";		
			display_xml_response($return_val);	
			
			return;	
		}
		
		/* get the driver list */
     	$sql = "
     		select *
     		
     		from drivers
     		where deleted = 0
     			and active>0
     		order by active desc, name_driver_last, name_driver_first
     	";
     	$data_drivers = simple_query($sql);
     	
     	/* get the truck list */
     	$sql = "
     		select *
     		
     		from trucks
     		where deleted = 0
     			and active>=0
     		order by active desc, name_truck
     	";
     	$data_trucks = simple_query($sql);
     			
     	/* get the traier list */
     	$sql = "
     		select *
     		
     		from trailers
     		where deleted = 0
     			and active>=0
     		order by active desc, trailer_name
     	";
     	$data_trailers = simple_query($sql);
		
		
		$cntr=0;
		$start_date="";
		$days=0;
		$runs=0;
		$saved=0;
		$my_cust="";
		$my_cust_id=0;
		
		$sql = "
			select *,
				(select name_company from customers where customers.id=timesheets.customer_id) as cust_name,
				(select count(*) from trucks_log_shuttle_routes where trucks_log_shuttle_routes.timesheet_id=timesheets.id and trucks_log_shuttle_routes.deleted=0) as sheet_cntr
			from timesheets
			where id='".sql_friendly($id)."'
		";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$start_date=date("m/d/Y",strtotime($row['linedate_start']));
			if($row['linedate_start']!="0000-00-00 00:00:00" && $row['linedate_end']!="0000-00-00 00:00:00")
			{
				$days=(strtotime($row['linedate_end']) - strtotime($row['linedate_start']))/(60*60*24);				
				$days++;
			}	
			$runs=$row['shuttle_runs'];
			$saved=$row['sheet_cntr'];
			if($saved > 0)	
			{
				$days=0;
				$runs=0;
			}
			if($row['customer_id'] > 0)			$my_cust=substr(trim($row['cust_name']),0,6);
			$my_cust_id=$row['customer_id'];
		}		
				
		$tab="";
		
		//number of days to enter....
		for($i=0; $i < $days; $i++) 
		{				
			$use_date=date("m/d/Y");
			if($start_date!="")
			{
				$use_date=date("m/d/Y",strtotime("+".$i." day",strtotime($start_date)));	
			}
     			
     		mysqli_data_seek($data_drivers, 0);
			mysqli_data_seek($data_trucks, 0);
			mysqli_data_seek($data_trailers, 0);
     		//".($cntr%2==0 ? "even" : "odd")."
			$tab.="
				<tr class='odd'>
          			<td valign='top'>
          			       <input name='switch_".$cntr."_shuttle_date' id='switch_".$cntr."_shuttle_date' class='date_picker_rates' value='".$use_date."' style='width:80px;'>
          			       <select name='switch_".$cntr."_wkday' id='switch_".$cntr."_wkday'>
							<option value='0' selected>Regular</option>
							<option value='1'>Sundays</option>
						  </select>
          			</td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_time1' id='conard_".$cntr."_shuttle_time1' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='switch_".$cntr."_shuttle_time1' id='switch_".$cntr."_shuttle_time1' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='switch_".$cntr."_shuttle_time2' id='switch_".$cntr."_shuttle_time2' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_time2' id='conard_".$cntr."_shuttle_time2' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_lunch_break' id='conard_".$cntr."_lunch_break' value='0.00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_days_run' id='conard_".$cntr."_days_run' value='0.00' style='width:50px;'></td>
          			
          			<td valign='top'>
          				<select name='driver_".$cntr."_id' id='driver_".$cntr."_id' onChange='mrr_change_driver_truck(".$cntr.");'>
							<option value='0'>Select Driver</option>
			";
			
							while($row_driver = mysqli_fetch_array($data_drivers)) 
							{ 
								$tab.="<option value='$row_driver[id]'>".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
							}
							
			$tab.="		</select>
          			</td>
          			<td valign='top'>
          				<select name='truck_".$cntr."_id' id='truck_".$cntr."_id'>
          					<option value='0'>Select Truck</option>
          	";
							
							while($row_truck = mysqli_fetch_array($data_trucks)) 
							{ 
								$tab.="<option value='$row_truck[id]'>".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
							}
							
			$tab.="		</select>
          			</td>
          			<td valign='top'>
          				<select name='trailer_".$cntr."_id' id='trailer_".$cntr."_id'>
							<option value='0'>Trailer {Optional}</option>
			";
			
							while($row_trailer = mysqli_fetch_array($data_trailers)) 
							{ 
								$tab.="<option value='$row_trailer[id]'>".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
							}
							
			$tab.="		</select>
          			</td>              			
          			<td valign='top'><input name='conard_".$cntr."_shuttle_miles' id='conard_".$cntr."_shuttle_miles' value='0' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_deadhead' id='conard_".$cntr."_shuttle_deadhead' value='0' style='width:50px;'></td>
          			<td valign='top'>".mrr_build_option_box('switch_shuttle_rates',($my_cust_id==1687 ? "159" : "145"),'switch_'.$cntr.'_shuttle_route_id',true,true," onChange='mrr_change_route_rate(".$cntr.");'")."</td>
          			<td valign='top' nowrap>$<input name='switch_".$cntr."_shuttle_rate' id='switch_".$cntr."_shuttle_rate' style='width:50px; background-color:#eeeeee' value='0.00' readonly></td>
               	</tr>
			";			
			$cntr++;
		}	
		//always show one day...
		if($days==0) 
		{				
			$use_date=date("m/d/Y",strtotime($start_date));
			
			mysqli_data_seek($data_drivers, 0);
			mysqli_data_seek($data_trucks, 0);
			mysqli_data_seek($data_trailers, 0);
          	
			$tab.="
				<tr class='odd'>
          			<td valign='top'>
          			   <input name='switch_".$cntr."_shuttle_date' id='switch_".$cntr."_shuttle_date' class='date_picker_rates' value='".$use_date."' style='width:80px;'>
          			   <select name='switch_".$cntr."_wkday' id='switch_".$cntr."_wkday'>
							<option value='0' selected>Regular</option>
							<option value='1'>Sundays</option>
					   </select>
          			</td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_time1' id='conard_".$cntr."_shuttle_time1' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='switch_".$cntr."_shuttle_time1' id='switch_".$cntr."_shuttle_time1' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='switch_".$cntr."_shuttle_time2' id='switch_".$cntr."_shuttle_time2' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_time2' id='conard_".$cntr."_shuttle_time2' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_lunch_break' id='conard_".$cntr."_lunch_break' value='0.00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_days_run' id='conard_".$cntr."_days_run' value='0.00' style='width:50px;'></td>
          			
          			<td valign='top'>
          				<select name='driver_".$cntr."_id' id='driver_".$cntr."_id' onChange='mrr_change_driver_truck(".$cntr.");'>
							<option value='0'>Select Driver</option>
			";
			
							while($row_driver = mysqli_fetch_array($data_drivers)) 
							{ 
								$tab.="<option value='$row_driver[id]'>".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
							}
							
			$tab.="		</select>
          			</td>
          			<td valign='top'>
          				<select name='truck_".$cntr."_id' id='truck_".$cntr."_id'>
          					<option value='0'>Select Truck</option>
          	";
							
							while($row_truck = mysqli_fetch_array($data_trucks)) 
							{ 
								$tab.="<option value='$row_truck[id]'>".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
							}
							
			$tab.="		</select>
          			</td>
          			<td valign='top'>
          				<select name='trailer_".$cntr."_id' id='trailer_".$cntr."_id'>
							<option value='0'>Trailer {Optional}</option>
			";
			
							while($row_trailer = mysqli_fetch_array($data_trailers)) 
							{ 
								$tab.="<option value='$row_trailer[id]'>".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
							}
							
			$tab.="		</select>
          			</td>              			
          			<td valign='top'><input name='conard_".$cntr."_shuttle_miles' id='conard_".$cntr."_shuttle_miles' value='0' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_deadhead' id='conard_".$cntr."_shuttle_deadhead' value='0' style='width:50px;'></td>
          			<td valign='top'>".mrr_build_option_box('switch_shuttle_rates',($my_cust_id==1687 ? "159" : "145"),'switch_'.$cntr.'_shuttle_route_id',true,true," onChange='mrr_change_route_rate(".$cntr.");'")."</td>
          			<td valign='top' nowrap>$<input name='switch_".$cntr."_shuttle_rate' id='switch_".$cntr."_shuttle_rate' style='width:50px; background-color:#eeeeee' value='0.00' readonly></td>
               	</tr>
			";			
			$cntr++;
		}
		
		//add shuttle runs to the list...
		for($i=0; $i < $runs; $i++) 
		{				
			$use_date=date("m/d/Y",strtotime($start_date));			
				
     		mysqli_data_seek($data_drivers, 0);
			mysqli_data_seek($data_trucks, 0);
			mysqli_data_seek($data_trailers, 0);
			
			//".($cntr%2==0 ? "even" : "odd")."
			$tab.="
				<tr class='even'>
          			<td valign='top'>
          			   <input name='switch_".$cntr."_shuttle_date' id='switch_".$cntr."_shuttle_date' class='date_picker_rates' value='".$use_date."' style='width:80px;'>
          			   <select name='switch_".$cntr."_wkday' id='switch_".$cntr."_wkday'>
							<option value='0' selected>Regular</option>
							<option value='1'>Sundays</option>
					   </select>
          			</td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_time1' id='conard_".$cntr."_shuttle_time1' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='switch_".$cntr."_shuttle_time1' id='switch_".$cntr."_shuttle_time1' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='switch_".$cntr."_shuttle_time2' id='switch_".$cntr."_shuttle_time2' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_time2' id='conard_".$cntr."_shuttle_time2' value='00:00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_lunch_break' id='conard_".$cntr."_lunch_break' value='0.00' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_days_run' id='conard_".$cntr."_days_run' value='0.00' style='width:50px;'></td>
          			
          			<td valign='top'>
          				<select name='driver_".$cntr."_id' id='driver_".$cntr."_id' onChange='mrr_change_driver_truck(".$cntr.");'>
							<option value='0'>Select Driver</option>
			";
			
							while($row_driver = mysqli_fetch_array($data_drivers)) 
							{ 
								$tab.="<option value='$row_driver[id]'>".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
							}
							
			$tab.="		</select>
          			</td>
          			<td valign='top'>
          				<select name='truck_".$cntr."_id' id='truck_".$cntr."_id'>
          					<option value='0'>Select Truck</option>
          	";
							
							while($row_truck = mysqli_fetch_array($data_trucks)) 
							{ 
								$tab.="<option value='$row_truck[id]'>".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
							}
							
			$tab.="		</select>
          			</td>
          			<td valign='top'>
          				<select name='trailer_".$cntr."_id' id='trailer_".$cntr."_id'>
							<option value='0'>Trailer {Optional}</option>
			";
			
							while($row_trailer = mysqli_fetch_array($data_trailers)) 
							{ 
								$tab.="<option value='$row_trailer[id]'>".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
							}
							
			$tab.="		</select>
          			</td>              			
          			<td valign='top'><input name='conard_".$cntr."_shuttle_miles' id='conard_".$cntr."_shuttle_miles' value='0' style='width:50px;'></td>
          			<td valign='top'><input name='conard_".$cntr."_shuttle_deadhead' id='conard_".$cntr."_shuttle_deadhead' value='0' style='width:50px;'></td>
          			<td valign='top'>".mrr_build_option_box('switch_shuttle_rates',($my_cust_id==1687 ? "159" : "145"),'switch_'.$cntr.'_shuttle_route_id',true,true," onChange='mrr_change_route_rate(".$cntr.");'")."</td>
          			<td valign='top' nowrap>$<input name='switch_".$cntr."_shuttle_rate' id='switch_".$cntr."_shuttle_rate' style='width:50px; background-color:#eeeeee' value='0.00' readonly></td>
               	</tr>
			";			
			$cntr++;
		}	
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp><entries>".$cntr."</entries>";		
		display_xml_response($return_val);		
	}
	
	function find_truck_for_driver()
	{
		$id=$_POST['id'];	
		$value=0;
		
		$sql = "select attached_truck_id from drivers where id='".sql_friendly($id)."'";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{			
			$value=$row['attached_truck_id'];	
		}
		if($value==0)
		{
			$sql = "select attached2_truck_id from drivers where id='".sql_friendly($id)."'";
			$data=simple_query($sql);
			if($row = mysqli_fetch_array($data)) 
			{			
				$value=$row['attached2_truck_id'];	
			}	
		}
		$return_val = "<rslt>1</rslt><TruckID><![CDATA[".$value."]]></TruckID>";		
		display_xml_response($return_val);	
	}
	function find_switch_shuttle_rates()
	{
		$id=$_POST['id'];	
		$value="0.00";
		
		$sql = "select fvalue from option_values where option_values.id='".sql_friendly($id)."'";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{			
			$value=floatval($row['fvalue']);	
		}
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$value."]]></Disp>";		
		display_xml_response($return_val);		
	}
	
	
	
	function kill_switch_shuttle_rates()
	{
		global $datasource;

		$id=$_POST['id'];	
		
		$sql = "
			update trucks_log_shuttle_routes set
				deleted='1'
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
		$myid=mysqli_insert_id($datasource);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	function add_switch_shuttle_rates()
	{
		global $datasource;

	     $id=$_POST['id'];
		$dater=$_POST['date'];
		$time1=$_POST['time1'];
		$time2=$_POST['time2'];
		$route=$_POST['route_id'];
		
		$cust_id=$_POST['cust_id'];
		if($route==0 && $cust_id==1687)	$route=159;
		elseif($route==0)				$route=145;
		
		$rate=money_strip($_POST['rate']);
		
		$conard1=$_POST['conard1'];
		$conard2=$_POST['conard2'];
		$otr=$_POST['otr'];
		
		$lunch=$_POST['lunch'];
		$location_id=0;	//$_POST['location_id'];
		
		$miles=$_POST['miles'];
		$dhmiles=$_POST['dhmiles'];
		$driver_id=$_POST['driver_id'];
		$truck_id=$_POST['truck_id'];
		$trailer_id=$_POST['trailer_id'];
		
		$sundays=$_POST['sundays'];
		
		
		$datetime1=date("Y-m-d H:i",strtotime($dater." ".$time1)).":00";
		$datetime2=date("Y-m-d H:i",strtotime($dater." ".$time2)).":00";
		
		$datetime3=date("Y-m-d H:i",strtotime($dater." ".$conard1)).":00";
		$datetime4=date("Y-m-d H:i",strtotime($dater." ".$conard2)).":00";
		
		$hours=((strtotime($datetime2) - strtotime($datetime1))/(60*60));		//company (carlex)
		$hours2=((strtotime($datetime4) - strtotime($datetime3))/(60*60));		//conard version
		
		$pay_charged_per_mile=0;
		$pay_charged_per_hour=0;
		
		$miles_tot=$miles + $dhmiles;
		$res=mrr_get_driver_pay_rates_by_id($driver_id);
		$pay_rate=$res['miles'];				//$res['miles_team']=$per_mile_team;
		$pay_rate_hours=$res['hours'];		//$res['hours_team']=$per_hour_team;
		$pay_charged_per_mile=$res['miles2'];	//$res['miles_team2']=$per_mile_team2;
		$pay_charged_per_hour=$res['hours2'];	//$res['hours_team2']=$per_hour_team2;
		
		if($driver_id==371)
		{	//test driver
			$pay_rate="0.50";				
			$pay_rate_hours="10.00";	
			
			$pay_charged_per_mile="0.52";
			$pay_charged_per_hour="20.00";	
		}
		
		/*
		if($cust_id==1687)
		{	// && $route==159
			$pay_rate="0.50";				
			$pay_rate_hours=get_option_text_by_id(159);	
			
			$pay_charged_per_mile="0.52";
			$pay_charged_per_hour="20.00";
		}
		*/
		//use default cost...
		//$pay_rate_hours=get_option_text_by_id(145);		
		
		//mrr_get_truck_cost($truck_id=0)
		
		$cost=get_daily_cost($truck_id, $trailer_id);
		$cost=$otr * $cost;		
		
		$sql = "
			insert into trucks_log_shuttle_routes
				(id,
				user_id,
				customer_id,
				timesheet_id,
				
				driver_id,
				truck_id,
				trailer_id,
				
				load_id,				
				trucks_log_id,
				option_id,
				route_rate,	
								
				linedate_from,
				linedate_to,	
				hours,
				
				linedate_start,
				linedate_end,	
				conard_hours,	
				cost,				
				days_run,	
				pay_rate,
				pay_rate_hours,
				
				pay_charged_per_hour,
				pay_charged_per_mile,
				
				miles,
				miles_deadhead,
				lunch_break,
				location_id,
				is_sunday,
										
				linedate_added,
				deleted)
			values
				(NULL,
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($cust_id)."',
				'".sql_friendly($id)."',
				
				'".sql_friendly($driver_id)."',
				'".sql_friendly($truck_id)."',
				'".sql_friendly($trailer_id)."',
				
				'0',
				'0',
				'".sql_friendly($route)."',
				'".sql_friendly($rate)."',
				
				'".$datetime1."',
				'".$datetime2."',
				'".sql_friendly($hours)."',
				
				'".$datetime3."',
				'".$datetime4."',
				'".sql_friendly($hours2)."',
				'".sql_friendly($cost)."',
				'".sql_friendly($otr)."',
				'".sql_friendly($pay_rate)."',
				'".sql_friendly($pay_rate_hours)."',
				
				'".sql_friendly($pay_charged_per_hour)."',
				'".sql_friendly($pay_charged_per_mile)."',
				
				'".sql_friendly($miles)."',
				'".sql_friendly($dhmiles)."',
				'".sql_friendly($lunch)."',
				'".sql_friendly($location_id)."',				
				'".sql_friendly($sundays)."',
				NOW(),
				0)
		";
		simple_query($sql);
		$myid=mysqli_insert_id($datasource);
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$myid."]]></Disp>";		
		display_xml_response($return_val);	
	}
	function list_switch_shuttle_rates()
	{
		$id=$_POST['id'];
		if($id==0)
		{
			$return_val = "<rslt>1</rslt><Disp></Disp>";		
			display_xml_response($return_val);	
			
			return;	
		}
		
		$cust_id=0;
		$show_invoicer="";
		$sql = "
			select *
			from timesheets
			where id='".sql_friendly($id)."'
		";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{			
			if($row['invoice_id'] == 0)	
			{
				$show_invoicer="
					<p>
						<button onclick='add_switch_shuttle_rates();' class='btn btn-success'><span class='glyphicon glyphicon-plus'></span> Add to Time Sheet</button>
						<button onclick='add_timesheet_invoice(".$row['id'].");' class='btn btn-primary'><span class='glyphicon glyphicon-floppy-disk'></span> Create Timesheet Invoice</button>
					</p>
				";
			}
			else
			{
				$show_invoicer="
					<p>
						<button onclick='add_switch_shuttle_rates();' class='btn btn-success'><span class='glyphicon glyphicon-plus'></span> Add to Time Sheet</button>
						
						<a href='https://trucking.conardtransportation.com/accounting/invoice.php?invoice_id=".$row['invoice_id']."' target='_blank'><b>Invoice ".$row['invoice_id']."</b></a>...
						created on ".date("m/d/Y H:i", strtotime($row['linedate_invoiced'])).".
						<button onclick='timesheet_invoice_del(".$row['id'].");' class='btn btn-danger'><span class='glyphicon glyphicon-floppy-remove'></span> Remove Invoice</button>
					</p>
				";
			}
			
			$cust_id=$row['customer_id'];
		}
		
		
		$tab="
			".$show_invoicer."
			<table class='table table-bordered well'>
			<thead>
			<tr>
				<th>Date</th>
				<th>Conard Time In</th>
				<th>Carlex Time In</th>
				<th>Carlex Time Out</th>
				<th>Conard Time Out</th>		
				<th>Lunch Break</th>
				<th>Days Run</th>
				<th>Driver</th>	
				<th>Truck</th>
				<th>Trailer</th>												
				<th>Miles</th>
				<th>Deadhead</th>					
				<th>Shuttle Route</th>					
				<th>Shuttle Rate</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
          	<tbody>
		";
          
          global $defaultsarray;
          $regular_hrly_rate=(int)$defaultsarray['carlex_regular_hrly_rate'];
          $sunday_hrly_rate=(int)$defaultsarray['carlex_sunday_hrly_rate'];
          
		$tot_hours2=0;
		$tot_rate2=0;
		$tot_hours=0;
		$tot_rate=0;
		$ctot=0;
		$gtot=0;
		
		$bill_cntr=0;
		$bill_tot=0;
		
		$cntr=0;
		/*		
				load_id,				
				trucks_log_id,				
				
		*/
		$sql = "
			select trucks_log_shuttle_routes.* ,
				(select trucks.name_truck from trucks where trucks.id=trucks_log_shuttle_routes.truck_id) as mytruck,
				(select trailers.trailer_name from trailers where trailers.id=trucks_log_shuttle_routes.trailer_id) as mytrailer,
				(select CONCAT(drivers.name_driver_first, ' ' ,drivers.name_driver_last) from drivers where drivers.id=trucks_log_shuttle_routes.driver_id) as mydriver,
				
				(select users.username from users where users.id=trucks_log_shuttle_routes.user_id) as myuser,
				(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myname,
				(select option_values.fvalue from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myval
			from trucks_log_shuttle_routes
			where trucks_log_shuttle_routes.deleted=0 
				and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($id)."'
			order by trucks_log_shuttle_routes.linedate_from,
					trucks_log_shuttle_routes.linedate_to,
					trucks_log_shuttle_routes.id
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{			
			$these_hours=($row['hours'] - $row['lunch_break']);
			$tot_hours+=$these_hours;
			$tot_rate+=$row['route_rate'];
			$sundays=$row['is_sunday'];
						
			$this_cost=$row['cost'];
			$this_pay=0;
			
			$all_miles=($row['miles'] + $row['miles_deadhead']);
			//$this_pay=$all_miles * $row['pay_rate'];
			
               $day_marker="";
			$use_pay_rate=$row['pay_rate_hours'];
			$use_pay_rate=$regular_hrly_rate;  //option_value_text(145,2);		//grab from the NONE - Switching ONLY rate.
			if($sundays > 0)
			{
                    $use_pay_rate=$sunday_hrly_rate;
                    $day_marker="<span style='color:purple;' title='Sunday hours use a different rate than Regular hours'><b><i>Sunday Hourly Rate...</i></b></span> ";
               }
   
			if($cust_id==1687)		$use_pay_rate=option_value_text(159,2);		//Vietti Foods only
			
			$this_pay+=($these_hours * $use_pay_rate);	//$row['conard_hours']...for the invoice, not payroll.
						
			$tot_rate2+=$this_cost;
			
			$use_pay_hours=$row['conard_hours'];
			//$use_pay_hours=$these_hours;
			//$use_pay_hours=($row['conard_hours'] - $row['lunch_break']);
			$tot_hours2+=$use_pay_hours;
						
			$ctot+=$this_cost;
			$gtot+=($this_pay + $row['route_rate']);
			
			$local="";
			//if($row['location_id']==1)	$local="Carlex-Nashville";
			//if($row['location_id']==2)	$local="Carlex-Lebanon";
			
			$classer="odd";
			if($row['option_id'] > 0 && $row['option_id'] !=145 && $cust_id!=1687)	
			{
				$classer="even";
			}		
			$tab.="
				<tr class='".$classer."'>
					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>
					<td valign='top'>".date("H:i", strtotime($row['linedate_start']))."</td>
					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>
					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>
					<td valign='top'>".date("H:i", strtotime($row['linedate_end']))."</td>
					<td valign='top' align='right'>".number_format($row['lunch_break'],2)."</td>	
					<td valign='top' align='right'>".number_format($row['days_run'],2)."</td>	
					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>
					
					<td valign='top'>".($row['truck_id'] > 0 ? trim($row['mytruck']) : "")."</td>	
					<td valign='top'>".($row['trailer_id'] > 0 ? trim($row['mytrailer']) : "")."</td>
					<td valign='top' align='right'>".$row['miles']."</td>	
					<td valign='top' align='right'>".$row['miles_deadhead']."</td>	
					<td valign='top'>".trim($row['myname'])."</td>	
					<td valign='top' align='right'>$".number_format($row['route_rate'],2)."</td>
					<td valign='top'><button class='btn btn-danger' onClick='kill_switch_shuttle_rates(".$row['id'].");'><span class='glyphicon glyphicon-trash'></span>&nbsp;</button></td>
				</tr>	
				<tr class='".$classer."' style='font-style: italic;'>	
					<td valign='top'>Conard Cost:</td>											
					<td valign='top'>Pay Rate</td>	
					<td valign='top'>$".number_format($row['pay_rate'],2)."/mi</td>
					<td valign='top'>$".number_format($row['pay_rate_hours'],2)."/hr</td>				
					<td valign='top' align='right'>Conard ".number_format($row['conard_hours'],2)." Hrs</td>
					<td valign='top' align='right' colspan='2'>Daily Cost $".number_format($this_cost,2)."</td>	
					<td valign='top' align='right'>Miles ".($row['miles'] + $row['miles_deadhead'])."</td>
					
					<td valign='top'><b>Invoice:</b></td>		
					<td valign='top' align='right'>Hrs ".number_format($these_hours,2)."</td>		
					<td valign='top' align='right' colspan='2'>&nbsp;</td>				
					<td valign='top' align='right' colspan='2'>Time $".number_format($this_pay,2)." + Route $".number_format($row['route_rate'],2)."</td>					
					<td valign='top' align='right' colspan='2'><b>".number_format(($this_pay + $row['route_rate']),2)."<b></td>				
				</tr>
			";
				//<td valign='top'>".trim($row['myuser'])."</td>	".trim($local)."
			
			if($row['truck_id'] > 0)
			{
     			if($row['option_id'] > 0 && $row['option_id'] !=145)	
     			{
     				//Shuttle Routes
     				$tab.="	
          				<tr class='".$classer." mrr_alert' style='font-style: italic;'>	
          					<td valign='top'>&nbsp;</td>											
          					<td valign='top'>Not for Payroll: </td>	
          					<td valign='top'>$".number_format($row['route_rate'],2)."</td>
          					<td valign='top'>67000-".mrr_make_numeric2(trim($row['mytruck']))."</td>				
          					<td valign='top' align='right'>".trim($row['myname'])."</td>
          					<td valign='top' align='right' colspan='3'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>          					
          					<td valign='top' colspan='4'>Shuttle Run: Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."</td>				
          					<td valign='top' align='right' colspan='2'>$".number_format(($row['route_rate']),2)."</td>					
          					<td valign='top' align='right' colspan='2'>&nbsp;</td>				
          				</tr>
          			";	
          			
          			//$bill_tot+=($row['route_rate']);
          			//$bill_cntr++;					
     			}
     			else
     			{
     				//"Carlex" Hours (Non-Shuttle)
     				$tab.="	
          				<tr class='".$classer."' style='font-style: italic;'>	
          					<td valign='top'>&nbsp;</td>											
          					<td valign='top'>Payroll Preview: </td>	
          					<td valign='top'>".($sundays > 0 ? "<span style='color:purple;' title='Sunday hours use a different rate than Regular hours'><b><i>$".number_format($use_pay_rate,2)."/hr</i></b></span>" : "$".number_format($use_pay_rate,2)."/hr")."</td>
          					<td valign='top'>67000-".mrr_make_numeric2(trim($row['mytruck']))."</td>				
          					<td valign='top' align='right'>".$day_marker."".number_format($use_pay_hours,2)." Hrs</td>
          					<td valign='top' align='right' colspan='3'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>          					
          					<td valign='top' colspan='4'>Time Sheet: Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."</td>				
          					<td valign='top' align='right' colspan='2'>$".number_format(($use_pay_rate * $use_pay_hours),2)."</td>					
          					<td valign='top' align='right' colspan='2'>&nbsp;</td>				
          				</tr>
          			";	
          			
          			$bill_tot+=($use_pay_rate * $use_pay_hours);
          			$bill_cntr++;	
     			}	
			}
			else
			{	//no truck = no chart                 
				$tab.="	
          				<tr class='".$classer." mrr_alert' style='font-style: italic;'>	
          					<td valign='top'>&nbsp;</td>											
          					<td valign='top'>Payroll Preview: </td>	
          					<td valign='top'>$".number_format($use_pay_rate,2)."/hr</td>
          					<td valign='top'>".$day_marker."ERROR</td>
          					<td valign='top' align='right'>".number_format($use_pay_hours,2)." Hrs</td>
          					<td valign='top' align='right' colspan='3'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>          					
          					<td valign='top' colspan='4'>No Account Found. (See Truck.)</td>				
          					<td valign='top' align='right' colspan='2'>$".number_format(($use_pay_rate * $use_pay_hours),2)."</td>					
          					<td valign='top' align='right' colspan='2'>&nbsp;</td>				
          				</tr>
          		";	
          			
          		//$bill_tot+=($use_pay_rate * $use_pay_hours);
          		//$bill_cntr++;	
			}
			$cntr++;
		}
		$tab.="
				<tr>
					<td valign='top' colspan='5'>".$cntr."</td>
					<td valign='top' colspan='2' align='right'>Cost $".number_format(($ctot),2)."</td>
					
					<td valign='top' align='right' title='This is the net gain.'><b>$".number_format(($gtot - $ctot),2)."</b></td>
					<td valign='top' colspan='2' align='right'>".number_format(($tot_hours),2)." Hrs</td>
					<td valign='top' colspan='2' align='right'>&nbsp;</td>
					<td valign='top' colspan='3' align='right' title='This is the amount to bill customer..and will go on the invoice.'><b>$".number_format(($gtot),2)."</b></td>					
				</tr>
				<tr>
					<td valign='top' colspan='4'>".$bill_cntr." Payroll Item(s)</td>
					<td valign='top' align='right'>".number_format(($tot_hours2),2)." Hrs</td>
					<td valign='top' colspan='5' align='right'><span class='odd'>Total (Regular Hourly Rates) $".number_format($bill_tot,2)."</span></td>
					<td valign='top' colspan='2' align='right'><span class='even' title='Shuttle Route part of total'><i>$".number_format(($gtot - $bill_tot),2)."</i></span></td>
					<td valign='top' colspan='3' align='right'>&nbsp;</td>					
				</tr>
				</tbody>
			";	
		$tab.="</table>";	
		
		if($cntr==0)		$tab="".$show_invoicer."";
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp>";		
		display_xml_response($return_val);		
	}	
	
	function mrr_accident_reports_item_adder()
	{
		global $datasource;

		$id=$_POST['id'];
		$desc=trim($_POST['desc']);
		$cost=trim(money_strip($_POST['cost']));
		$catid=$_POST['catid'];
		
		if($cost=="")		$cost="0.00";
		
		$sql="
			insert into accident_reports_itemized
				(id,
				accident_id,
				cat_id,
				linedate_added,
				user_id,
				desc_note,
				cost,				
				deleted)
			values
				(NULL,
				'".sql_friendly($id)."',
				'".sql_friendly($catid)."',
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($desc)."',
				'".sql_friendly($cost)."',			
				0)
		";
		simple_query($sql);
		$newid=mysqli_insert_id($datasource);
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$newid."]]></Disp>";		
		display_xml_response($return_val);	
	}
	function mrr_accident_reports_item_lister()
	{
		$id=$_POST['id'];
		
		$cntr=0;
		$tab="";
		
		$cost1=0;
		$cost2=0;
		$cost3=0;
		
		$sql="
			select *
			from accident_reports
			where id='".sql_friendly($id)."'
		";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$cost1=$row['accident_cost'];
			$cost2=$row['injury_cost'];
			$cost3=$row['driver_cost'];
		}
		
		$last_sub="";
		$sub_tot=0;
		$tot=0;		
		
		
		$tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";	
		$tab.="<thead>
				<tr>
					<th>Category</th>
					<th>Added By</th>
					<th>Date Added</th>
					<th>Notes/Description</th>
					<th align='right'>Cost</th>
					<th align='right'>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			";
		$sql="
			select accident_reports_itemized.*,
				(select option_values.fname from option_values where option_values.id=accident_reports_itemized.cat_id) as my_cat,
				(select option_values.fvalue from option_values where option_values.id=accident_reports_itemized.cat_id) as my_cat2,
				(select username from users where users.id=accident_reports_itemized.user_id) as my_user
			from accident_reports_itemized
			where accident_reports_itemized.deleted=0
				and accident_reports_itemized.accident_id='".sql_friendly($id)."'
			order by accident_reports_itemized.cat_id asc,
				accident_reports_itemized.linedate_added asc,
				accident_reports_itemized.id asc
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{			
			//$row['my_cat2']		$row['user_id']	$row['deleted']	$row['accident_id']			
			$cur_sub=trim("".($row['cat_id'] > 0 ? $row['my_cat'] : "Uncategorized")."");
			
			if(trim($last_sub)!="" && $last_sub!=$cur_sub)
			{
				$tab.="
     				<tr>
     					<td valign='top'>&nbsp;</td>
     					<td valign='top'><b>".$last_sub."</b></td>
     					<td valign='top'>&nbsp;</td>
     					<td valign='top'><b>Subtotal</b></td>
     					<td valign='top' align='right'><b>$".number_format($sub_tot,2)."</b></td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr>
				";	
								
				$last_sub="";
				$sub_tot=0;				
			}
						
			
			$sub_tot+=$row['cost'];
			$tot+=$row['cost'];		
			$last_sub=$cur_sub;
			
			$tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>".$cur_sub."</td>
					<td valign='top'>".$row['my_user']."</td>
					<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
					<td valign='top'>".$row['desc_note']."</td>
					<td valign='top' align='right'>$".number_format($row['cost'],2)."</td>
					<td valign='top' align='right'><span onclick='mrr_remove_ac_item(".$row['id'].");'><img src='images/delete_sm.gif' border='0'></span></td>
				</tr>
			";			
			$cntr++;
		}
		$tab.="
			<tr>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>".$last_sub."</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>Subtotal</b></td>
				<td valign='top' align='right'><b>$".number_format($sub_tot,2)."</b></td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>Total Itemized</b></td>
				<td valign='top' align='right'><b>$".number_format($tot,2)."</b></td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top' colspan='6'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>+ Accident Cost</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'><b>$".number_format($cost1,2)."</b></td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>+ Injury Cost</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'><b>$".number_format($cost2,2)."</b></td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>+ Driver Cost</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'><b>$".number_format($cost3,2)."</b></td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top' colspan='6'>&nbsp;</td>
			</tr>
			<tr>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><b>Grand Total</b></td>
				<td valign='top' align='right'><b>$".number_format(($tot + $cost1 + $cost2 + $cost3),2)."</b></td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
			</tbody>
			</table>
		";	
		
		if($cntr==0)		$tab="";
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp>";		
		display_xml_response($return_val);	
	}
	function mrr_accident_reports_item_remover()
	{
		$id=$_POST['id'];
		
		$sql="
			update accident_reports_itemized set
				deleted='1'
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	function mrr_accident_email_log()
	{
		$id=$_POST['id'];
		
		$cntr=0;
		$tab="";			
		
		$tab.="<h3>Emailed History Log:</h3>";
		$tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";	
		$tab.="<thead>
				<tr>
					<th>Date</th>
					<th>Email Address</th>
					<th>Email Name</th>
					<th>Subject</th>
					<th>Username</th>
					<th align='right'>Email&nbsp;</th>
				</tr>
			</thead>
			<tbody>
		";
		$email_type=357;		//Accident Report
		$sql="	
			select gen_email_log.*,
               	(select users.username from users where users.id=gen_email_log.user_id) as user_name
               from gen_email_log
               where gen_email_log.deleted=0	
               	and gen_email_log.email_type='".(int) $email_type."'
               	and gen_email_log.accident_id='".sql_friendly($id)."'
               order by gen_email_log.id desc
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{			
			$tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top' nowrap><span style='cursor:pointer; color:#0000CC;' onClick='mrr_toggle_email_log(".$row['id'].");'><b>+/-</b></span> ".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
					<td valign='top'>".trim($row['email_to'])."</td>
					<td valign='top'>".trim($row['email_name'])."</td>
					<td valign='top'>".trim($row['email_subject'])."</td>				
					<td valign='top'>".trim($row['user_name'])."</td>
					<td valign='top' align='right'>".($row['email_sent'] > 0 ? "Sent" : "Fail")."&nbsp;</td>
				</tr>
				<tr class='".($cntr%2==0 ? "even" : "odd")." email_log_id_".$row['id']." all_email_logs'>
					<td valign='top'>&nbsp;</td>
					<td valign='top' colspan='4'>".trim($row['email_body'])."</td>
					<td valign='top' align='right'>&nbsp;</td>
				</tr>
			";		
			$cntr++;
		}
		
		$tab.="
			</tbody>
			</table>
		";	
		
		//if($cntr==0)		$tab="";
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp><SQL><![CDATA[".$sql."]]></SQL>";		
		display_xml_response($return_val);	
	}	
	function mrr_accident_email_app()
	{
		$id=$_POST['id'];
          $user_id=(int) $_SESSION['user_id'];
		//$email_type=357;		//Accident Report
          global $defaultsarray;
		
          $pre_fill_email="";      if($user_id==23)    $pre_fill_email=$defaultsarray['special_email_monitor'];
          $pre_fill_name="";       if($user_id==23)    $pre_fill_name="Lord Vader";
          $pre_fill_subj="";       if($user_id==23)    $pre_fill_subj="Testing ZIP File attachment";
          $pre_fill_msg="";        if($user_id==23)    $pre_fill_msg="This is just a test from Accident ".$id."... look for ZIP file.";
          
		$tab="
			<h3>Send Email Accident Report Information:</h3>
			<table cellpadding='0' cellspacing='0' border='0' width='950'>				
			<tr>
				<td valign='top'><b>Email Address:</b></td>
				<td valign='top'><input type='text' name='accident_email_addr' id='accident_email_addr' value=\"".$pre_fill_email."\" style='width:300px; text-align:left;'></td>
				<td valign='top' align='right'><button type='button' class='btn btn-primary' onClick='mrr_send_out_accident_email_msg();'><span class='glyphicon glyphicon-envelope'></span> Send</button></td>
			</tr>
			<tr>
				<td valign='top'><b>Email Name:</b></td>
				<td valign='top'><input type='text' name='accident_email_name' id='accident_email_name' value=\"".$pre_fill_name."\" style='width:300px; text-align:left;'> (Optional)</td>
				<td valign='top'></td>
			</tr>
			<tr>
				<td valign='top'><b>Email Subject:</b></td>
				<td valign='top' colspan='2'><input type='text' name='accident_email_sub' id='accident_email_sub' value=\"".$pre_fill_subj."\" style='width:300px; text-align:left;'></textarea></td>
			</tr>
			<tr>
				<td valign='top'><b>Email Message:</b></td>
				<td valign='top' colspan='2'><textarea name='accident_email_msg' id='accident_email_msg' wrap='virtual' rows='3' cols='100'>".$pre_fill_msg."</textarea></td>
			</tr>
		";	
		if($id > 0) 
		{
               $tab .= "<tr>";
               $tab .= "<td valign='top'><b>ZIP Attachment(s):</b></td>";
               $tab .=   "<td valign='top' colspan='2'>";
               
               $files=0;
		     $sql = "select * from attachments where section_id=11 and xref_id='".$id."' order by linedate_added desc";
               $data = simple_query($sql);
               while($row = mysqli_fetch_array($data))
               {
                    $tab .= "".($files+1).". Attach/Zip <label> <input type='checkbox' name='mrr_file_".$files."' id='mrr_file_".$files."' value='".$row['fname']."'> ".$row['public_name']."</label><br>";
                    $files++;
               }                  
               $tab .=   "</td>";
               $tab .= "</tr>";
               $tab .= "</table><input type='hidden' name='mrr_attatched_email_files' id='mrr_attatched_email_files' value='".$files."'><br>Zipped to ".getcwd()."";
               //
               //<br>Zipped to ".sys_get_temp_dir()."   result was "C:\Users\TRUCKI~1.COM\AppData\Local\Temp"
          }
		if($id==0)		$tab="<b>You must save the Accident Truck Report in order to use this feature.</b>";
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp>";		
		display_xml_response($return_val);	
	}
	function mrr_accident_email_app_send()
	{
		global $defaultsarray;
		$from_email=trim($defaultsarray['company_email_address']);
		$from_name=trim($defaultsarray['company_name']);
		
		$id=$_POST['id'];
		$email_type=357;		//Accident Report
		
		$user_id=(int) $_SESSION['user_id'];
		
		$email_to=trim(strip_tags($_POST['msg_to']));
		$email_name=trim(strip_tags($_POST['msg_name']));
		
		$email_sub=trim(strip_tags($_POST['msg_sub']));
		$email_msg=trim(strip_tags($_POST['msg_body']));
		
		$email_prt0=trim(strip_tags($_POST['msg_sec0']));
		$email_prt1=trim(strip_tags($_POST['msg_sec1']));
		$email_prt2=trim(strip_tags($_POST['msg_sec2']));
		$email_prt3=trim(strip_tags($_POST['msg_sec3']));
          
          //create ZIP file to add attachmnet(s) to the email.
		$zip_list=trim($_POST['msg_zip']);
		$zip_path="/documents/";
          $zip_file_name="";
          $mock_zipper="";
          $add_to_header="";
          $zipped="";
          
		if($zip_list!="")
          {
               $zip_files=array();
               $good_files=0;
               $zip_arr = explode(";",$zip_list);
               for($i=0; $i < count($zip_arr); $i++)
               {
                    if(trim($zip_arr[$i])!="" && trim($zip_arr[$i])!="; ")
                    {
                         //$random_hash = md5(uniqid(time()));
                         
                         $test_file=str_replace(" ","%20",trim($zip_arr[$i]));
     
                         $zip_files[$good_files]=$zip_path."".trim($test_file);
                         if(substr_count(strtolower($zip_arr[$i]),".jpg") > 0 || substr_count(strtolower($zip_arr[$i]),".jpeg") > 0 ||
                              substr_count(strtolower($zip_arr[$i]),".png") > 0 || substr_count(strtolower($zip_arr[$i]),".gif") > 0)
                         {
                              $mock_zipper.="
                                    <br>File ".$good_files.". <img src='https://trucking.conardtransportation.com".$zip_path."".$test_file."' alt='https://trucking.conardtransportation.com".$zip_path."".$test_file."'>
                              ";
                         }     
                         else
                         {
                              $mock_zipper.="
                                    <br>File ".$good_files.". <a href='https://trucking.conardtransportation.com".$zip_path."".$test_file."'>https://trucking.conardtransportation.com".$zip_path."".$test_file."</a>
                              ";
                         }
     
                         //$add_to_header.='\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"';
                         //$add_to_header.="";
                         //$add_to_header.="";
                         //$add_to_header.="";
                         //$add_to_header.="";
                         
                         $good_files++;
                    }                    
               }
               
               if($good_files > 0)
               {
                    $zip_file_name="Conard_Incident_".$id."_".time().".zip";          //_".uniqid()."
                    //$rslt = ccs_zip_files($files, 'temp/testzip'.time().'.zip');   //from Chris's example.
                    $zipped=mrr_zip_files($zip_files,"temp/".$zip_file_name,false);
               }               
          }
				
				
		$sql="
			select email, name_first, name_last
			from users
			where id='".sql_friendly($user_id)."'
		";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$from_email=$row['email'];
			$from_name=trim($row['name_first']." ".$row['name_last']);
		}
		$from_email = 'noreply@conardtransportation.com';
		
		if($email_sub=="")		$email_sub="Conard Accident Report ".$id."";
		$msg1="";
		$msg2="";	
		
			
		$sql="
			select accident_reports.*,
				(select name_driver_first from drivers where drivers.id=accident_reports.driver_id) as driver_fname,
				(select name_driver_first from drivers where drivers.id=accident_reports.driver_id) as driver_lname,
				(select name_truck from trucks where trucks.id=accident_reports.truck_id) as truck_name,
				(select trailer_name from trailers where trailers.id=accident_reports.trailer_id) as trailer_name
			from accident_reports
			where accident_reports.id='".sql_friendly($id)."'
		";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{			
			$msg1.="<b>".$email_sub.":</b><br>";
			$msg1.="Date: ".date("m/d/Y",strtotime($row['accident_date'])).": Accident No. ".$row['accident_number']."<br>";
			if($row['driver_id'] > 0)	$msg1.="Driver: ".trim($row['driver_fname']." ".$row['driver_fname'])."<br>";
			if($row['truck_id'] > 0)		$msg1.="Truck: ".trim($row['truck_name'])."<br>";
			if($row['trailer_id'] > 0)	$msg1.="Trailer: ".trim($row['trailer_name'])."<br>";
			$msg1.="<br><hr><br>";
			$msg1.="".$email_msg."<br><br>";
			if($email_prt0!="")		$msg1.="<b>Accident Description:</b><br>".$email_prt0."<br><br>";
			if($email_prt1!="")		$msg1.="<b>Injury Description:</b><br>".$email_prt1."<br><br>";
			if($email_prt2!="")		$msg1.="<b>Driver's Description:</b><br>".$email_prt2."<br><br>";
			if($email_prt3!="")		$msg1.="<b>Notes and Updates:</b><br>".$email_prt3."<br><br>";
						
			$msg2=$msg1;
			$msg2=str_replace("<br>"," \n ",$msg2);
			$msg2=trim(strip_tags($msg2));
		}
		$sent=0;
		if(trim($msg1)!="" && trim($email_to)!="")
		{
		     if(trim($mock_zipper)!="")
               {
                    $msg1.="<br><br>Files: ".trim($mock_zipper)."<br><br>";
                    $msg2.="\n \n Files: ".strip_tags(trim($mock_zipper))."";
               }		     
		     if($zip_file_name!="")
               {
                    $msg1.="<br><br><a href='https://trucking.conardtransportation.com/temp/".$zip_file_name."' target='_blank'>Download ZIP file of Attachment(s): https://trucking.conardtransportation.com/temp/".$zip_file_name.".</a><br><br>".$zip_list."";
                    $msg2.="\n \n Download ZIP Attachment(s) here: https://trucking.conardtransportation.com/temp/".$zip_file_name."";
               }
		     
		     $sent=mrr_trucking_sendMail($email_to,$email_name,$from_email,$from_name,"","",$email_sub,$msg2,$msg1,0,$add_to_header);       //$zip_file_name
		}
		
		$sql = "
			insert into gen_email_log
				(id,
				user_id,
				linedate_added,
									
				accident_id,	
				email_type,
				
				email_to,	
				email_name,
				email_subject,
				email_body,
				email_sent,
					
				deleted)
			values
				(NULL,
				'".sql_friendly($user_id)."',
				NOW(),
				
				'".sql_friendly($id)."',
				'".sql_friendly($email_type)."',
				
				'".sql_friendly(trim($email_to))."',
				'".sql_friendly(trim($email_name))."',
				'".sql_friendly(trim($email_sub))."',
				'".sql_friendly(trim($msg1))."',
				'".sql_friendly($sent)."',
				
				0)
		";
		simple_query($sql);	
		
		$return_val = "<rslt>1</rslt><Sent>".$sent."</Sent><Zippy><![CDATA[".$zipped."".$mock_zipper."]]></Zippy>";		
		display_xml_response($return_val);	
	}
	
	
	function mrr_set_driver_raise_dispatches()
	{
		$driver_id=$_POST['driver_id'];
		$date_start=$_POST['date_start'];	
		
		$rep=mrr_backprocess_payroll_raise_dispatches($driver_id,$date_start,1);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);	
	}
	
	function mrr_get_avail_driver_summary()
	{
		//global $defaultsarray;
		
		$driver_id=$_POST['driver_id'];
		$tab="";
		$tab1="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
		$tab2="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
		$tab3="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
				
		//$coa_db=trim($defaultsarray['accounting_database_name']);	
		$cntr1=0;
		$tab1.="<tr><td valign='top'><b>First Name</b></td><td valign='top'><b>Last Name</b></td><td valign='top'><b>Tag</b></td><td valign='top'><b>Reference</b></td><td valign='top'><b>Location</b></td></tr>";
		$sql = "
			select drivers.*
			
			from drivers
			where drivers.deleted = 0 
				and drivers.active > 0
				".($driver_id > 0 ? "and drivers.id='".sql_friendly($driver_id)."'" : "" )."
				and drivers.id!=405
				and drivers.id!=345
				and drivers.id!=371
				and drivers.shuttle_runner=0
				and drivers.night_shifter=0
				
			order by drivers.name_driver_last asc, 
				drivers.name_driver_first asc, 
				drivers.id asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$refer="N/A";
			$tag_mode="TBD";
			$location="Unknown...";
			$show_me=0;
			$res=mrr_find_driver_load_use($row['id']);
			//$res['preplanned']=0;
			//$res['next_preplan']="";	
			//$res['next_preplan_id']="";
			
			//$res['last_dispatch_id']="";
			//$res['last_dispatch']="";
			
			//$res['next_dispatch_id']="";
			//$res['next_dispatch']="";	
			//$res['dispatches']=0;
			if($res['preplanned']==0 && $res['dispatches']==0)
			{
				$refer="".$res['last_dispatch_id']."";
				$location="".$res['last_dispatch']."";	
				$tag_mode="<span class='mrr_link_like_on' title='Available...no more loads or dispatches assigned to this driver.' style='color:#00CC00;'>Available</span>";
				$show_me=1;
			}
			elseif($res['preplanned']==0 && $res['dispatches']==1)
			{
				$refer="".$res['next_dispatch_id']."";
				$location="".$res['next_dispatch']."";	
				$tag_mode="<span class='mrr_link_like_on' title='Driver is on last dispatch, and will be available after it is completed.' style='color:#CC0000;'>Final</span>";
				$show_me=1;
			}
			elseif($res['preplanned']==1 && $res['dispatches']==0)
			{
				$refer="".$res['next_preplan_id']."";
				$location="".$res['next_preplan']."";	
				$tag_mode="<span class='mrr_link_like_on' title='Driver is preplanned for one more load, and may need a new load.' style='color:#FFCC00;'>Preplan</span>";
				$show_me=1;
			}
			
			//if($cntr1==0)		$show_me=1;
			
			if($show_me > 0)
			{
     			$tab1.="
     				<tr>
     					<td valign='top'>".trim($row['name_driver_first'])."</td>
     					<td valign='top'>".trim($row['name_driver_last'])."</td>
     					<td valign='top'>".trim($tag_mode)."</td>
     					<td valign='top'>".trim($refer)."</td>
     					<td valign='top'>".trim($location)."</td>
     				</tr>
     			";
				$cntr1++;
			}	
		}
		
		//Shuttle Runs
		$tab2.="<tr><td valign='top'><b>First Name</b></td><td valign='top'><b>Last Name</b></td></tr>";	
		$sql = "
			select *
			
			from drivers
			where deleted = 0 and active > 0
				".($driver_id > 0 ? "and id='".sql_friendly($driver_id)."'" : "" )."
				and shuttle_runner > 0
				
			order by name_driver_last asc, name_driver_first asc, id asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{			
			$tab2.="<tr><td valign='top'>".trim($row['name_driver_first'])."</td><td valign='top'>".trim($row['name_driver_last'])."</td></tr>";			
		}	
		
		//Night Shift
		$tab3.="<tr><td valign='top'><b>First Name</b></td><td valign='top'><b>Last Name</b></td></tr>";
		$sql = "
			select *
			
			from drivers
			where deleted = 0 and active > 0
				".($driver_id > 0 ? "and id='".sql_friendly($driver_id)."'" : "" )."
				and night_shifter > 0
				
			order by name_driver_last asc, name_driver_first asc, id asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$tab3.="<tr><td valign='top'>".trim($row['name_driver_first'])."</td><td valign='top'>".trim($row['name_driver_last'])."</td></tr>";	
		}
		
		
		
		
		$tab1.="</table>";
		$tab2.="</table>";
		$tab3.="</table>";
		
		$tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
		$tab.="<tr>";
		$tab.=	"<td valign='top' width='70%' align='center'><b>Driver Available Summary</b></td>";
		$tab.=	"<td valign='top' width='15%' align='center'><b>Shuttle Run Drivers</b></td>";
		$tab.=	"<td valign='top' width='15%' align='center'><b>Night Shift Drivers</b></td>";
		$tab.="</tr>";
		$tab.="<tr>";
		$tab.=	"<td valign='top'>".$tab1."</td>";
		$tab.=	"<td valign='top'>".$tab2."</td>";
		$tab.=	"<td valign='top'>".$tab3."</td>";
		$tab.="</tr>";
		$tab.="</table>";		
		
		$return_val = "<rslt>1</rslt><Disp><![CDATA[".$tab."]]></Disp>";		
		display_xml_response($return_val);			
	}

     function mrr_rate_sheet_upload_mover()
     {
          getcwd();
          $target_dir = "../incoming_rate_sheets/";
          chdir($target_dir);
          $mode=(int) $_POST['mode'];
          $file=trim($_POST['file']);
          $rep="";
          
          if($mode > 0 && $file!="")
          {
               $sub_dir="";
               if($mode == 1)      $sub_dir="completed/";
               if($mode == 2)      $sub_dir="hold/";
               if($mode == 3)      $sub_dir="problem/";
     
               $source_file = $target_dir . $file;
               $dest_file = $target_dir . $sub_dir . $file;
     
               if(rename($source_file, $dest_file))
               {
                    $rep="File moved from ".$source_file." to ".$dest_file.".";
               }
               else
               {
                    $rep="File could not be moved from ".$source_file." to ".$dest_file.".";
                    unlink($source_file);
               }
          }
          
          $return_val = "<rslt>1</rslt><Report><![CDATA[".$rep."]]></Report>";
          display_xml_response($return_val);
     }

     function mrr_ooic_auto_update_route()
     {
          global $defaultsarray;
          $id=(int) $_POST['id'];
          $rate=trim($_POST['rate']);          
          
          $rep="";
          
          $new_ooic_rate="";
          $rt_ooic_rate=0;
          $disp_id=0;
          if(isset($_POST['dispatch_id']))        $disp_id=(int) $_POST['dispatch_id'];
          if(isset($_POST['rt_ooic_rate']))       $rt_ooic_rate=$_POST['rt_ooic_rate'];
          $driver_ooic_rate=(int)$defaultsarray['ooic_rate_load_percentage'];
                    
          if($id > 0)
          {
               $rate=str_replace("$","",$rate);
               $rate=str_replace(",","",$rate);
               $rate=floatval(trim($rate));
               
               //route rate (and maybe flat_cost_fuel_rate) updated...
               $sqlu = "
                         update option_values set
                              fvalue='".sql_friendly($rate)."'
                         where id='".sql_friendly($id)."'
                    ";
               simple_query($sqlu);               
               
               if($rt_ooic_rate > 0 && $driver_ooic_rate > 0)
               {    //set the Bill Customer amount (minus the Switch Exp) for 70%... where 70 is in hte defaults table.                
                    $new_ooic_rate=(($rt_ooic_rate - $rate) * $driver_ooic_rate / 100);    
                    
                    if($disp_id > 0) 
                    {
                         $sqlu = "
				        update trucks_log set
					       flat_cost_rate='".sql_friendly($new_ooic_rate)."'			
				          where id='".sql_friendly($disp_id)."'
			          ";
                         simple_query($sqlu);
                    }                    
               }
               
               $rep="Updating Route Rate to $".number_format($rate,2)." for Route/Option ID ".$id.".";
          }
          
          $return_val = "<rslt>1</rslt><Report><![CDATA[".$rep."]]></Report><DispID>".$disp_id."</DispID><NewRate><![CDATA[".number_format($new_ooic_rate,2)."]]></NewRate>";
          display_xml_response($return_val);
     }
	function mrr_ooic_auto_update()
	{
		$id=(int) $_POST['id'];
		$rate=trim($_POST['rate']);
		$rep="";
		
		if($id > 0)
		{
			$rate=str_replace("$","",$rate);
			$rate=str_replace(",","",$rate);
			
			//flat_cost_rate (and maybe flat_cost_fuel_rate) updated...			
			$sqlu = "
				update trucks_log set
					flat_cost_rate='".sql_friendly($rate)."',
					flat_cost_rate_lock='1'			
				where  id='".sql_friendly($id)."'
			";
			simple_query($sqlu);
			
			$rep="Updating Rate to $".number_format($rate,2)." for Dispatch ".$id.".";
		}
		
		$return_val = "<rslt>1</rslt><Report><![CDATA[".$rep."]]></Report>";		
		display_xml_response($return_val);	
	}
     function mrr_ooic_auto_update_misc()
     {
          $id=(int) $_POST['id'];
          $amnt=trim($_POST['amnt']);
          $desc=trim($_POST['desc']);
          $rep="";
          
          if($id > 0)
          {
               $amnt=str_replace("$","",$amnt);
               $amnt=str_replace(",","",$amnt);
               
               //Amnt will be used to add to the payroll OOIC Alt report.
               $sqlu = "
                         update driver_ooic_misc_exp set
                              misc_amount='".sql_friendly($amnt)."',
                              misc_desc='".sql_friendly($desc)."'
                         where  id='".sql_friendly($id)."'
                    ";
               simple_query($sqlu);
               
               $rep="Updating Amnt to $".number_format($amnt,2)." for Driver Pay Entry ".$id.".";
          }
          
          $return_val = "<rslt>1</rslt><Report><![CDATA[".$rep."]]></Report>";
          display_xml_response($return_val);
     }
	function mrr_ooic_auto_emailer()
	{
		global $defaultsarray;
		
		$html="";
		
		$driver_id=(int) $_POST['driver_id'];
		//$html=trim($_POST['html']);	
		
		//$html=str_replace("<input type='text' name='update_rate_","<input type='text' disabled name='update_rate_",$html);
		//$html=str_replace("<input type='button' value='Reload Report' onClick='mrr_reload_ooic_report();'>","",$html);
		//$html=str_replace("<input type='button' value='E-Mail Driver' onClick='mrr_email_ooic_report(".$driver_id.");'>","",$html);
		
		//$html=str_replace("type='button'","type='hidden'",$html);
		//$html=str_replace("input","input disabled",$html);
		
		$tmp_html="";
				
		$rep="No Driver detected. (".$driver_id.")";
		$res=0;
		if($driver_id > 0)
		{
			$sql = "
				select *				
				from drivers
				where id='".sql_friendly($driver_id)."'	
			";
			$data = simple_query($sql);
			if($row = mysqli_fetch_array($data)) 
			{
				$name=trim($row['name_driver_first'])." ".trim($row['name_driver_last']);
				$email=trim($row['driver_email']);
				
				//to prevent the HTML section from having more than the current driver's info in hte email, pull the section from the table.
				$html_new="";
				$sqlx = "
					select *				
					from driver_ooic_payroll_emails
					where deleted=0 and driver_id='".sql_friendly($driver_id)."' and driver_email='".sql_friendly($email)."'	
					order by id desc
				";
				$datax = simple_query($sqlx);
				if($rowx = mysqli_fetch_array($datax)) 
				{
					$html_new=trim($rowx['driver_content']);
					
					$html="<center><span class='section_heading'>Owner Operator Report for ".$name." (".date("m/d/Y",strtotime($rowx['linedate_start']))." through ".date("m/d/Y",strtotime($rowx['linedate_end']))."):</span><br></center>";
					$html.="<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left'>";
					$html.=$html_new;
					$html.="</table>";
					
					$html=str_replace("<input type='text' name='update_rate_","<input type='text' disabled name='update_rate_",$html);
					$html=str_replace("<input type='button' value='Reload Report' onClick='mrr_reload_ooic_report();'>","",$html);
					$html=str_replace("<input type='button' value='E-Mail Driver' onClick='mrr_email_ooic_report(".$driver_id.");'>","",$html);
		
					$html=str_replace("type='button'","type='hidden'",$html);
					$html=str_replace("input","input disabled",$html);
					
					$htmlx=$html;
					$htmlx=str_replace("</td>"," \t </td>",$htmlx);
					$htmlx=str_replace("</tr>"," \r\n </tr>",$htmlx);
					$htmlx=str_replace("</table>"," \r\n </table>",$htmlx);
					$htmlx=strip_tags($htmlx);
										
					
					$subject="Owner Operator Report for ".$name."";				
					
					$msg1="To ".$name.", \r\n \r\n Here is your latest Owner Operator Payroll Report: \r\n \r\n ".$htmlx." \r\n \r\n";
					$msg2="To ".$name.", <br><br>Here is your latest Owner Operator Payroll Report: <br><br>".$html."<br><br>";
					
					//$email=$defaultsarray['special_email_monitor'];
					mrr_trucking_sendMail($email,'Owner Operator Report',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$htmlx,$html);
					
					$rep="Driver ".$driver_id." found. Name=".$name." | E-Mail=".$email.".";
					$res=1;
				}
				else
				{
					$rep="Driver ".$driver_id." found. Name=".$name." | E-Mail=".$email.".  NOT SENT...";
					$res=0;	
				}
				/*					
				$pos1=strpos($html,"admin_drivers.php?id=".$driver_id."",0);		$pos1-=17;
				$pos2=strpos($html,"admin_drivers.php?id=",($pos1 + 50));			$pos2-=17;	//find the next Driver section.
				
				if($pos1 > 0 && $pos2 <= 0)
				{	// on last driver...so use the summary marker.
					//Use this instead...<tr class='next_driver_reg'><td colspan='14'><hr><br>SUMMARY:</td></tr>
					
					$pos2=strpos($html,"SUMMARY:",($pos1 + 50));					$pos2-=50;	//find the next Driver section.
				}		
				
				if($pos1 > 0 && $pos2 > 0 && $pos2 > $pos1)
				{
					$tmp_html=substr($html,$pos1,($pos2 - $pos1));
					$tmp_html="<table><tr><td colspan='3'>".$tmp_html."&nbsp;</td></tr></table>";
					$htmlx=$tmp_html;
					$htmlx=str_replace("</tr>","\r\n</tr>",$htmlx);
					$htmlx=str_replace("</table>","\r\n</table>",$htmlx);
					$htmlx=strip_tags($htmlx);
					
					$subject="Owner Operator Report for ".$name."";				
					
					$msg1="To ".$name.", \r\n \r\n Here is your latest Owner Operator Payroll Report: \r\n \r\n ".$htmlx." \r\n \r\n";
					$msg2="To ".$name.", <br><br>Here is your latest Owner Operator Payroll Report: <br><br>".$tmp_html."<br><br>";
									
					$email=$defaultsarray['special_email_monitor'];
					mrr_trucking_sendMail($email,'Owner Operator Report',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$htmlx,$html);
					
					$rep="Driver ".$driver_id." found. Name=".$name." | E-Mail=".$email.".";
					$res=1;
				}
				else
				{
					$rep="Driver ".$driver_id." found. Name=".$name." | E-Mail=".$email.".  NOT SENT...  POS1=".$pos1." and POS2=".$pos2.".";
					$res=0;	
				}
				*/
			}
			else
			{
				$rep="Driver ".$driver_id." not found, or has no email address.";
				$res=0;	
			}
			
		}
		$return_val = "<rslt>".$res."</rslt><Report><![CDATA[".$rep."]]></Report><TmpHTML><![CDATA[".$tmp_html."]]></TmpHTML><HTML><![CDATA[".$html."]]></HTML>";		//
		display_xml_response($return_val);	
	}
	
	function mrr_update_load_bill_cust_amnt()
     {
          $id=(int) $_POST['load_id'];
          $amnt=$_POST['bill_cust'];
          
          if($id > 0 && $amnt > 0)
          {
               $sqlu = "update load_handler set actual_bill_customer='".sql_friendly($amnt)."' where id='".sql_friendly($id)."'";
               simple_query($sqlu);
          }
     }
	function mrr_payroll_api()
	{		
		$mode=$_POST['mode'];
				
		$rep="";
		if($mode==3 && 1==2)
		{
			$first_name=trim($_POST['first_name']);
			$last_name=trim($_POST['last_name']);
			
			$rep.="<br><hr><br>";
			
			$rep.="<b>Test Drive for ".$first_name." ".$last_name.":</b>";
			
			$play=mrr_payroll_check_api_get_employee($first_name,$last_name);
			$rep.="<br>".$play."<br>";	
			$rep.= "<br><hr><br>";	
		}	
			
		
		$return_val = "<rslt>1</rslt><Report><![CDATA[".$rep."]]></Report>";		
		display_xml_response($return_val);		
	}
	
	function search_coa_chart()
	{
		global $defaultsarray;
		
		$coa_db=trim($defaultsarray['accounting_database_name']);	
		if($coa_db=="")		return;
		
		// use a union, the first part will scan for all matches starting with the letter, so it shows up in the search list first
		// the second part of the query will search for any part of the search term anywhere in the chart name, which will be shown below
		// the first set, as the first letter search should take priority
		$sql = "
			select chart_name,
				chart_number
			
			from ".$coa_db.".chart
			where chart_name like '".sql_friendly($_GET['q'])."%'
				and deleted = 0
				and active = 1
				and chart_number < '85000-OH'
			union all 
			
			select chart_name,
				chart_number
			
			from ".$coa_db.".chart
			where chart_name like '%".sql_friendly($_GET['q'])."%'
				and deleted = 0
				and active = 1
				and chart_number < '85000-OH'
			
			group by chart.chart_name
			limit 100
		";
		$data = simple_query($sql);
		
		$shown_array = array();
		while($row = mysqli_fetch_array($data)) {
			if(!in_array($row['chart_name'], $shown_array)) {
				$shown_array[] = $row['chart_name'];
				echo "$row[chart_name]|$row[chart_number]\n";
			}
		}	
	}
	
	function mrr_change_user_pg_usage()
     {
          $pg_type=(int) $_POST['pg_type'];
          $pg_id=(int) $_POST['pg_id'];
          $user=(int) $_POST['user_id'];
                    
          if($user==0)        $user=$_SESSION['user_id'];     
     
          mrr_user_page_editing_del($pg_type,$pg_id,$user);
     
          $return_val = "<rslt>1</rslt><PgType><![CDATA[".$pg_type."]]></PgType><PgID><![CDATA[".$pg_id."]]></PgID><User><![CDATA[".$user."]]></User>";
          display_xml_response($return_val);
     }
     
     function mrr_show_detention_notes()
     {
          $load_id=(int) $_POST['load_id'];
          
          $dcntr=0;     
          $last_note_here="";     
          $det_note_table="<table cellpadding='1' cellspacing='1' border='0' width='100%'>";
          $det_note_table.="
                  <tr>
                      <td valign='top'><b>Note</b></td>
                      <td valign='top'><b>Added</b></td>
                      <td valign='top'><b>User</b></td>
                  </tr>
           ";
          $sqld = "
              select load_detention_notes.*,
                    (select username from users where users.id=load_detention_notes.user_id) as mrr_user                        
              from load_detention_notes
              where load_detention_notes.deleted = 0
                    and load_detention_notes.load_id='".sql_friendly($load_id)."'                            
              order by load_detention_notes.linedate_added desc, 
                    load_detention_notes.id desc
          ";
          $datad = simple_query($sqld);
          while($rowd = mysqli_fetch_array($datad))
          {
               $det_note_table.="
                  <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                      <td valign='top'>".trim($rowd['detention_note'])."</td>
                      <td valign='top'>".date("m/d/Y H:i",strtotime($rowd['linedate_added']))."</td>
                      <td valign='top'>".trim($rowd['mrr_user'])."</td>
                  </tr>
                ";
          
               if($dcntr==0)     $last_note_here=trim($rowd['detention_note']);
               $dcntr++;
          }
          $det_note_table.="</table>";
     
          $return_val = "<rslt>1</rslt><mrrTab><![CDATA[".$det_note_table."]]></mrrTab><mrrLast><![CDATA[".$last_note_here."]]></mrrLast>";
          display_xml_response($return_val);
     }
     function mrr_add_detention_note()
     {
			global $datasource;
			$note=trim($_POST['note']);
          $load_id=(int) $_POST['load_id'];
          //$user=(int) $_SESSION['user_id'];
     
          $sql="
			insert into load_detention_notes
				(id,
				load_id,
				linedate_added,
				user_id,
				detention_note,				
				deleted)
			values
				(NULL,
				'".sql_friendly($load_id)."',
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($note)."',			
				0)
		";
          simple_query($sql);
          $note_id=mysqli_insert_id($datasource);
     
          if($note_id==0)
          {
               $return_val = "<rslt>0</rslt><Note><![CDATA[".$note."]]></Note><SQL><![CDATA[".$sql."]]></SQL>";
               display_xml_response($return_val); 
          }
     
          $return_val = "<rslt>1</rslt><Note><![CDATA[".$note."]]></Note><SQL><![CDATA[".$sql."]]></SQL>";
          display_xml_response($return_val);
     }
     
     function mrr_mark_off_internal_task()
     {
          $id=(int) $_POST['id'];
          $user=(int) $_SESSION['user_id'];
          
          $sql="
			update internal_tasks_checked set
			     user_id='".sql_friendly($user)."',
			     done_date=NOW()
			where id='".sql_friendly($id)."' 
		";
          simple_query($sql);
     
          $return_val = "<rslt>1</rslt><SQL><![CDATA[".$sql."]]></SQL>";
          display_xml_response($return_val);
     }
     function mrr_clear_internal_task()
     {
          $id=(int) $_POST['id'];
          
          $sql="
               update internal_tasks_checked set
                    user_id='0',
                    done_date='0000-00-00 00:00:00'
               where id='".sql_friendly($id)."' 
          ";
          simple_query($sql);
          
          $return_val = "<rslt>1</rslt><SQL><![CDATA[".$sql."]]></SQL>";
          display_xml_response($return_val);
     }
     function mrr_set_internal_task()
     {
          $id=(int) $_POST['id'];
          $dater=trim($_POST['date']);
          
          $sql="
                    update internal_tasks_checked set
                         cur_date='".date("Y-m-d",strtotime($dater))." 00:00:00'
                    where id='".sql_friendly($id)."' 
               ";
          simple_query($sql);
          
          $return_val = "<rslt>1</rslt><SQL><![CDATA[".$sql."]]></SQL>";
          display_xml_response($return_val);
     }
	
     //new mini-inventory section operations...
     function mrr_update_inv_part_qty()
     {
          $id=(int) $_POST['id'];
          $mode=(int) $_POST['mode'];
          $value=(int) $_POST['value'];
          
          $use_col="";
          if($mode==1)        $use_col="qty_ordered";
          if($mode==2)        $use_col="qty_received";
          if($mode==3)        $use_col="qty_used";          
     
          $sql="
                    update inventory_log set
                         ".$use_col."='".sql_friendly($value)."'
                    where id='".sql_friendly($id)."' 
               ";
          simple_query($sql);
     
          $return_val = "<rslt>1</rslt><SQL><![CDATA[".$sql."]]></SQL>";
          display_xml_response($return_val);
     }
     
     
	//custom mini-menu items	
	function kill_mini_menu_item()
	{
		$id=$_POST['item_id'];
		
		$sql = "update user_menu_custom set deleted='1' where id='".sql_friendly($id)."'";
		simple_query($sql);
		
		$return_val = "<rslt>1</rslt>";		
		display_xml_response($return_val);			
	}


     function mrr_show_truck_info_validation()
     {
          $truck_id=(int) $_POST['truck_id'];
          
          $vin = trim($_POST['vin']);
          $plate = trim($_POST['plate']);
          $device = trim($_POST['device']);
          $serial = trim($_POST['serial']);
          $prepass = trim($_POST['prepass']);
          $tablet = trim($_POST['tablet']);
          $zenduit = trim($_POST['zenduit']);
          
          $dcntr=0;
          $last_note_here="";
          $det_note_table="<table cellpadding='1' cellspacing='1' border='0' width='100%'>";
          $det_note_table.="
                       <tr>
                           <td valign='top'><b>Truck</b></td>
                           <td valign='top'><b>Info</b></td>
                           <td valign='top'><b>Duplicated</b></td>
                       </tr>
                ";
          $sqld = "
                   select trucks.vin,
                         trucks.license_plate_no,
                         trucks.geotab_device_id,
                         trucks.geotab_device_serial,
                         trucks.prepass,
                         trucks.tablet_imei,
                         trucks.zenduit_drid,
                         trucks.name_truck,
                         trucks.id                    
                   from trucks
                   where trucks.deleted = 0 and trucks.active>0              
                         and trucks.id!='".sql_friendly($truck_id)."'
                         and (
                              trucks.vin='".sql_friendly($vin)."'
                              or trucks.license_plate_no='".sql_friendly($plate)."'
                              or trucks.geotab_device_id='".sql_friendly($device)."'
                              or trucks.geotab_device_serial='".sql_friendly($serial)."'
                              or trucks.prepass='".sql_friendly($prepass)."'
                              or trucks.tablet_imei='".sql_friendly($tablet)."'
                              or trucks.zenduit_drid='".sql_friendly($zenduit)."'
                         )                            
                   order by trucks.name_truck asc, 
                         trucks.id asc
               ";
          $datad = simple_query($sqld);
          while($rowd = mysqli_fetch_array($datad))
          {
               if($vin == trim($rowd['vin']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>VIN</td>
                           <td valign='top'>".trim($rowd['vin'])."</td>
                       </tr>
                     ";
               }
               if($plate == trim($rowd['license_plate_no']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>License Plate</td>
                           <td valign='top'>".trim($rowd['license_plate_no'])."</td>
                       </tr>
                     ";
               }
               if($device == trim($rowd['geotab_device_id']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>GeoTab Device ID</td>
                           <td valign='top'>".trim($rowd['geotab_device_id'])."</td>
                       </tr>
                     ";
               }
               if($serial == trim($rowd['geotab_device_serial']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>GeoTab Serial No.</td>
                           <td valign='top'>".trim($rowd['geotab_device_serial'])."</td>
                       </tr>
                     ";
               }
               if($prepass == trim($rowd['prepass']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>Prepass</td>
                           <td valign='top'>".trim($rowd['prepass'])."</td>
                       </tr>
                     ";
               }
               if($tablet == trim($rowd['tablet_imei']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>Tablet IMEI</td>
                           <td valign='top'>".trim($rowd['tablet_imei'])."</td>
                       </tr>
                     ";
               }
               if($zenduit == trim($rowd['zenduit_drid']))
               {
                    $det_note_table.="
                       <tr style='background-color:#".($dcntr%2==0 ? "eeeeee" : "ffffff").";'>
                           <td valign='top'><a href='admin_trucks.php?id=".$rowd['id']."' target='_blank'>".trim($rowd['name_truck'])."</a></td>
                           <td valign='top'>Zenduit DRID</td>
                           <td valign='top'>".trim($rowd['zenduit_drid'])."</td>
                       </tr>
                     ";
               }
               
               $dcntr++;
          }
          $det_note_table.="</table>";
          
          $return_val = "<rslt>1</rslt><mrrTab><![CDATA[".$det_note_table."]]></mrrTab><mrrCntr>".$dcntr."</mrrCntr>";
          display_xml_response($return_val);
     }
	
	//mrr_log_page_loads($mrr_micro_seconds_start_ajax,"?cmd=".$_GET['cmd']."");		//make tracking log entry
	
	
	//add user action to log.............................................................................................................................................................................................................
	$mrr_activity_log_user=(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0');
 	$mrr_activity_log_self=(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
	$mrr_activity_log_query=(isset($_GET['cmd']) ? $_GET['cmd'] : '');
	$mrr_activity_log_refer="";		//Use prototype below
	
	mrr_set_user_action_log($mrr_activity_log_user,$mrr_activity_log_self,$mrr_activity_log_query,$mrr_activity_log_refer,
				$mrr_activity_log['driver_id'],$mrr_activity_log['truck_id'],$mrr_activity_log['trailer_id'],$mrr_activity_log['load_handler_id'],$mrr_activity_log['dispatch_id'],$mrr_activity_log['stop_id'],$mrr_activity_log['notes']);
	//   mrr_set_user_action_log($mrr_activity_log_user,$mrr_activity_log_self,$mrr_activity_log_query,$mrr_activity_log_refer,	$driver,	$truck,	$trailer,	$load,	$dispatch,		$stop,	"Note goes Here...");   //Prototype
	//...................................................................................................................................................................................................................................
?>