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
$global_channel_name="OVWEBSERVER";
$channel_folder="ovwebserver";
$init_page="ojovoz.php";
$main_page="ojovoz.php";
$master_pass="ojovoz";
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
$mail_server="{address:port/pop3/notls}";
$max_messages_from_inbox=10;
//auto create email addresses?
$auto_create_email=false;
//main crono channel, used to link from tag channel
$main_crono_channel=1;
//
$tag_channel_name="Tags";
//names of tag modes (a comma-separated list)
$tag_modes=array("Tags frecuentes,Tags populares","Frequent tags,Popular tags");
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
//language: texts and labels.
//
$alias_prompt="Tu nombre";
$text_input_prompt="Escribe tus comentarios";
$publish_button_label="Publicar tus comentarios";
$tag_toolbox_1_time[0]="1 vez"; //frequent tags
$tag_toolbox_1_time[1]="1 participante"; //popular tags
$tag_toolbox_n_times[0]=" veces"; //frequent tags
$tag_toolbox_n_times[1]=" participantes"; //popular tags
$descriptor_toolbox_1_time="1 caso";
$descriptor_toolbox_n_times=" casos";
$reset_button_text="Limpiar";
$search_button_text="?";
$search_address_text="Buscar dirección";
$address_not_found_text="Dirección no encontrada";
$search_tag_text="Buscar tag";
$edit_contents_text="Estudiar:";
$enter_edit_button_text="Entrar";
$edit_channel_button_text="Guardar cambios";
$confirm_delete_message_text="Borrar mensaje?";
$configuration_text=utf8_decode("Configuración");
$move_to_text="mover a";
$tag_input_text="Tags (separados por comas):";
$suggestion_text="Sugerencias";
$click_to_locate_text="Hacer click para localizar...";
$locate_button_text="Localizar";
$rotate_photo_text=utf8_decode("Girar 90 grados");
$delete_photo_text="Borrar foto";
$delete_audio_text="Borrar audio";
$delete_video_text="Borrar video";
$delete_message_text="Borrar mensaje";
$describe_button_text="describe";
$describe_all_button_text="describe";
$copy_to_blog_label="Copiar a Editorial";
$edit_study_channel_contents_text="Editar";
$study_text="Editorial";
$tag_cloud_title="";
$descriptor_cloud_title="";
$thumbnail_text[0]="first";
$thumbnail_text[1]="middle";
$thumbnail_text[2]="last";
//
//mapping
//
$google_maps_api_key="your google maps api key";
$mapbox_api_key="your mapbox api key";
$mapbox_id="your mapbox id";
$has_map=true;
$map_channel_name="";
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
$default_latitude=-10.71667;
$default_longitude=38.8;
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
$servpath="/path/to/html/docs/";
$ffmpeg_path="/path/to/ffmpeg/";
$sample_rate="22050";
//titles-languages
$ov_languages="Español,English";
$ov_color_combination_titles[0]=array("cambiar a página con fondo blanco","cambiar a página con fondo azul","cambiar a página con fondo color crema","cambiar a página con fondo negro");
$ov_color_combination_titles[1]=array("change to white background","change to blue background","change to beige background","change to black background");
$ov_menu_ids="$default_channel_id,-3";
$ov_menu_titles=array("Inicio,Mapa","Home,Map");
$ov_menu_ids_study="-1";
$ov_menu_titles_study=array("Inicio,Tags","Home,Tags");
$ov_text_font="Geneva, Arial, Helvetica, sans-serif";
$ov_text_font_size="4";
$ov_text_font_size_header="1.0";
$ov_rss_feed_title=array("RSS","RSS");
$ov_current_crono_text=array("Area:","Area:");
$ov_choose_crono_text=array("Elija un área:","Choose an area:");
$ov_choose_other_crono_text=array("Elija otra área","Choose another area");
$ov_current_child_text=array("Grupo:","Group:");
$ov_choose_child_text=array("Elige un grupo:","Choose a group:");
$ov_choose_other_child_text=array("Ver otro grupo","Choose a different group");
$ov_tags_mode_text=array("","");
$ov_tags_other_mode_text=array("Cambiar a","Change to");
$ov_tag_modes_explanation[0]=array("El número junto a cada tag indica cuántas veces ha sido usada.","The number beside each tag indicates the times it has been used.");
$ov_tag_modes_explanation[1]=array("El número junto a cada tag indica cuántos emisores la han usado.","The number beside each tag indicates how many participants have used it.");
$ov_message_sender_text=array("Mensaje enviado por","Message sent by");
$ov_message_datetime_text=array("Mensaje enviado","Message sent");
$ov_message_date_text=array("el","on");
$ov_message_time_text=array("a las","at");
$ov_day_month_prep=array("de","of");
$ov_month_year_prep=array("de","of");
$ov_image_title_text=array("Palabras clave:","Tags:");
$ov_show_player=true;
$ov_audio_link_text=array("Escuchar grabación","Listen to recording");
$ov_video_link_text=array("Ver video","Watch video");
$ov_no_messages_text=array("aún no tiene mensajes.","still has no messages.");
$ov_page_title_prefix=array("Estás en el canal","You are in channel");
$ov_tag_page_title_prefix=array("Palabras clave:","Tags:");
$ov_about_page_title_prefix=array("Acerca de","About");
$ov_goto_page_button_label=array("Ir al mes seleccionado","Go to selected month");
$ov_skip_menu_link_title=array("Ir al contenido.","Jump to content.");
$ov_non_descripted_mesage_text=array("Sin descripción","Undefined");
$ov_page_filter_prefix=array("Palabras seleccionadas:","Chosen tags:");
$ov_days_prefix=array("Días: ","Days: ");
$ov_locales=array("es_ES.ISO8859-1","en_EN");
$ov_comments_page_text=array("Agregar comentario","Add comment");
$ov_comments_list_text=array("Comentarios:","Comments:");
$ov_comment_sender_text=array("Comentario enviado por","Comment sent by");
$ov_comment_alias=array("Alias","Alias");
$ov_comment_password=array("Contraseña","Password");
$ov_comment_text=array("Comentario","Comment");
$ov_add_comment_button=array("Publicar","Publish");
$ov_comment_back_link=array("Volver","Back");
$ov_comment_wrong_password=array("Error en el comentario","Wrong password");
$ov_no_comments_text=array("Añadir comentario","Add comment");
$ov_1_comments_text=array("Un comentario","One comment");
$ov_n_comments_text=array("comentarios","comments");
$ov_tag_input_text=array("Palabras clave (separadas por comas): ","Tags (separated by commas): ");
$ov_edit_message_text=array("Texto del mensaje:","Message text:");
$ov_delete_message_text=array("Borrar mensaje","Delete message");
$ov_edit_channel_button_text=array("Guardar cambios","Save changes");
$ov_delete_photo_text=array("Borrar foto","Delete photo");
$ov_delete_audio_text=array("Borrar grabación","Delete audio");
$ov_delete_video_text=array("Borrar video","Delete video");
$ov_rotate_photo_text=array("Girar 90 grados","Rotate 90 degrees");
$ov_confirm_delete_message_text=array("Borrar mensaje?","Delete message?");
$ov_photo_is_published_text=array("Publicar foto","Publish photo");
$ov_locate_message_text=array("Localizar mensaje","Locate message");
$ov_current_location_text=array("Dirección actual:","Current address:");
$ov_location_none_text=array("Ninguna","None");
$ov_type_new_location_text=array("Nueva dirección:","New address:");
$ov_search_new_location_text=array("Dirección de búsqueda","Search address");
$ov_confirm_new_location_text=array("Confirmar","Confirm");
$ov_go_to_previous_page_text=array("Volver a la página anterior","Go back to previous page");
$ov_next_page_text=array("Siguiente página","Next page");
$ov_previous_page_text=array("Página anterior","Previous page");
$ov_search_text=array("Buscar","Search");
$ov_deselect_tags=array("Limpiar selección","Clear selection");
$ov_share_page_text=array("compartir","share");
$ov_message_pending_approval=array("Mensaje pendiente de aprobación","Message pending approval");
$ov_approve_message_text=array("Aprobado","Approved");
$ov_map_dates_between=array("Entre","Between");
$ov_map_dates_and=array("y","and");
$ov_map_dates_button=array("Buscar","Search");
//android
$smtp_server="smtp.yourserver.net";
$smtp_server_port="578";
//RSS
$rss_description="";
$rss_language="en";
$rss_max_messages=50;
?>