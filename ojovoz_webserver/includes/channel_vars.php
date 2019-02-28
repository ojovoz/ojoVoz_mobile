<?
include_once "init_database.php";
$dbh=initDB();

function GetDefaultChannelIDVar($dbh) {
	$query="SELECT value FROM global WHERE global_variable = 'default_channel_id'";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$ret = $row[0];
	return $ret;
}

//general variables
$global_channel_name="ovwebserver";
$channel_folder="ovwebserver";
$init_page="ovwebserver.php";
$main_page="ovwebserver.php";
$master_pass="xxx";
$edit_page="edit_channel.php";
//this is the ID of the channel
//that appears by default when the main page
//is viewed.
$default_channel_id=GetDefaultChannelIDVar($dbh);
//comma separated values. IDs of the channels that are excluded from crono, such as the forum or the media channel.
//this variable can be -1 if no channels are to be excluded
$channels_excluded_from_crono="-1"; 
//ID of the media channel. can be -1 if there is no media channel.
$media_channel_id=-1;
//comma separated list that indicates the order in which
//channels appear in the drop-down list.
//can be empty value
$channel_order="";
//this variable determines whether a randomly
//chosen channel's inbox will be checked each
//time a crono channel is accessed.
//useful for projects with many crono channels
$crono_random_check=true;
//time zone of the area where the project is being made.
$time_zone=-8;
//maximum number of tags that appear in the tag cloud.
$max_tags_in_cloud=50;
//read tags from subject of message?
$get_tags_from_subject=false;
//default user name (when uploading pictures)
$default_user_name="web_upload";
//
$mail_server="{x.x.x.x:110/pop3/notls}";
$max_messages_from_inbox=10;
//auto create email addresses?
$auto_create_email=false;
//main crono channel, used to link from tag channel
$main_crono_channel=1;
//
$tag_channel_name="Tags";
//
//discard short audios (probably trash)
$discard_short_audio=true;
$min_audio_size=1000; //bytes
//
//colors and sizes
//
$form_color="#CC0000";
$map_background_color="#FFFFFF";
$map_text_color="#CC0000";
$map_tag_color="#000000"; 
$map_descriptor_color="#000000"; 
$map_legend_color="000000";
$map_data_color="000099";
$font_size="1.0";
$text_font_size="1.2";
$title_font_size="5.0";
$data_font_size="1.0";
$legend_font_size="2";
$tag_min_size="0.7";
$tag_max_size="2.0";
$tag_min_size_tag_page="0.9";
$tag_max_size_tag_page="3.5";
$thumbnail_width=160;
$thumbnail_height=120;
$max_image_width_1=360;
$max_image_width_2=520;
$max_image_width_edit=348;
$video_width=320;
$video_height=255;
$audio_width=520;
$audio_height=25;
$edit_video_width=280;
$edit_video_height=210;
$edit_audio_width=348;
$edit_audio_height=25;
$tag_page_tag_color="#000000";
$tag_page_tag_hilite_color="#000000";
$tag_page_background_color="#FFFFFF";
$tag_page_hilite_size=1.3;
$tag_page_line_height=3.5;
$ov_form_font_size=1.0;
//
//mapping
//
$google_maps_api_key=""; //fuck off google
$mapbox_api_key="xxx";
$mapbox_id="xxx";
$has_map=true;
$map_channel_name="#r0g";
$max_markers_on_map=500;
$max_tags_on_map=50;
$show_tags_in_map=true;
$map_tag_mode=0; //0=frequent, 1=popular
$show_descriptors_in_map=false;
$map_channel_width="100%"; //size of the map that appears in non-map channels... in pixels (px) or percentage (%)
$map_channel_height="360px";
$show_legend_in_map=true;
$prefered_city="";
$use_prefered_city=true;
$default_latitude=41.390205;
$default_longitude=2.154007;
$get_reverse_geocoding=false;
$static_map_width=480;
$static_map_height=360;
//
$channel_mail_prefix="";
$get_date_from_exif=false;
$get_user_from_message_subject=true;
//
//conversion
$convert_to_mp3=true;
$servpath="path/to/htmldocs";
$ffmpeg_path="path/to/ffmpeg";
$sample_rate="22050";
//titles-languages
$ov_languages="English,Español";
$ov_menu_ids="$default_channel_id,-3";
$ov_menu_titles=array("Home,Map","Inicio,Mapa");
$ov_text_font="Geneva, Arial, Helvetica, sans-serif";
$ov_text_font_size="4";
$ov_text_font_size_header="1.0";
$ov_rss_feed_title=array("RSS","RSS");
$ov_current_crono_text=array("Group:","Grupo:");
$ov_choose_crono_text=array("Choose a group:","Elige un grupo:");
$ov_choose_other_crono_text=array("Choose different group","Elige otro grupo");
$ov_current_child_text=array("Participant:","Participante:");
$ov_choose_child_text=array("Choose a participant:","Elige un participante:");
$ov_choose_other_child_text=array("Choose another participant","Elige otro participante:");
$ov_tags_mode_text=array("","");
$ov_tags_other_mode_text=array("Change to","Cambiar a");
$ov_message_sender_text=array("","");
$ov_message_datetime_text=array("","");
$ov_message_date_text=array("","");
$ov_message_time_text=array("at","a las");
$ov_day_month_prep=array("of","de");
$ov_month_year_prep=array("of","de");
$ov_image_title_text=array("Tags:","Palabras clave:");
$ov_show_player=true;
$ov_audio_link_text=array("Listen to recording","Escuchar grabación");
$ov_video_link_text=array("Watch video","Ver vídeo");
$ov_no_messages_text=array("still has no messages.","aún no tiene mensajes.");
$ov_page_title_prefix=array("You are in channel","Estás en el canal");
$ov_tag_page_title_prefix=array("Tags:","Palabras clave:");
$ov_about_page_title_prefix=array("About","Acerca de");
$ov_goto_page_button_label=array("Go to selected month","Ir al mes seleccionado");
$ov_skip_menu_link_title=array("Jump to content.","Ir al contenido.");
$ov_non_descripted_mesage_text=array("Undefined","No definido");
$ov_page_filter_prefix=array("Selected tags:","Palabras clave seleccionadas:");
$ov_days_prefix=array("Days: ","Días: ");
$ov_locales=array("en_EN","es_ES");
$ov_comments_page_text=array("Add comment","Agregar comentario");
$ov_comments_list_text=array("Comments:","Comentarios:");
$ov_comment_sender_text=array("Comment sent by","Comentario enviado por");
$ov_comment_alias=array("Alias","Alias");
$ov_comment_password=array("Password","Contraseña");
$ov_comment_text=array("Comment","Comentario");
$ov_add_comment_button=array("Publish","Publicar");
$ov_comment_back_link=array("Back","Volver");
$ov_comment_wrong_password=array("Wrong password","Contraseña incorrecta");
$ov_no_comments_text=array("Add comment","Añadir comentario");
$ov_1_comments_text=array("One comment","Un comentario");
$ov_n_comments_text=array("comments","comentarios");
$ov_tag_input_text=array("Tags (separated by commas): ","Palabras clave (separadas por comas)");
$ov_edit_message_text=array("Message text:","Texto del mensaje:");
$ov_delete_message_text=array("Delete message","Borrar mensaje");
$ov_edit_channel_button_text=array("Save changes","Guardar cambios");
$ov_delete_photo_text=array("Delete picture","Borrar foto");
$ov_delete_audio_text=array("Delete audio","Borrar audio");
$ov_delete_video_text=array("Delete video","Borrar vídeo");
$ov_rotate_photo_text=array("Rotate 90 degrees","Girar 90 grados");
$ov_confirm_delete_message_text=array("Delete message?","Borrar mensaje?");
$ov_photo_is_published_text=array("Publish image","Publicar foto");
$ov_locate_message_text=array("Locate message","Localizar mensaje");
$ov_current_location_text=array("Current address:","Dirección actual:");
$ov_location_none_text=array("None","Ninguna");
$ov_type_new_location_text=array("New address:","Nueva dirección:");
$ov_search_new_location_text=array("Search address","Buscar dirección");
$ov_confirm_new_location_text=array("Confirm","Confirmar");
$ov_go_to_previous_page_text=array("Go back to previous page","Ir a la página anterior");
$ov_next_page_text=array("Next page","Siguiente página");
$ov_previous_page_text=array("Previous page","Página anterior");
$ov_search_text=array("Search","Buscar");
$ov_deselect_tags=array("Clear selection","Limpiar selección");
$ov_share_page_text=array("share","compartir");
$ov_message_pending_approval=array("Message pending approval","Mensaje pendiente de aprobación");
$ov_approve_message_text=array("Approved","Aprobado");
$ov_map_dates_between=array("Between","Entre");
$ov_map_dates_and=array("and","y");
$ov_map_dates_button=array("Search","Buscar");
//android
$smtp_server="xxx";
$smtp_server_port="578";
$multimedia_subject="xxx";
//RSS
$rss_description="ovwebserver";
$rss_language="en";
$rss_max_messages=50;
?>