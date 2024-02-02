<?php

$lang = array();


include 'dict-frontend.php';
include 'dict-posts.php';

$lang += [
    /* navegación y barra lateral */
    'nav_overview'=> 'Vista general',
    'nav_new' => 'Nuevo',
    'nav_preferences' => 'Preferencias',
    'nav_general' => 'General',
    'nav_system' => 'Sistema',
    /* contenidos */
    'nav_pages' => 'páginas',
    'nav_snippets' => 'snippets',
    /* tienda */
    'nav_shop' => 'tienda',
    'nav_products' => 'productos',
    'nav_price_groups' => 'Grupos de precios',
    'nav_features' => 'características',
    'nav_filter' => 'filtro',
    'nav_orders' => 'pedidos',
    'nav_payment_shipping' => 'Pagos y Envíos',
    'nav_delivery_areas' => 'Zonas de entrega',
    'nav_business_details' => 'detalles del negocio',
    /* blog */
    'nav_blog' => 'blog',
    /* eventos */
    'nav_events' => 'eventos',
    'nav_bookings' => 'reservas',
    'nav_comments' => 'comentarios',
    /* usuario */
    'nav_user' => 'usuario',
    'nav_usergroups' => 'grupos de usuarios',
    /* addons */
    'nav_addons' => 'addons',
    /* buzón de correo */
    'nav_inbox' => 'bandeja de entrada',
    'nav_mailbox' => 'Correos electrónicos',
    /* categorías */
    'categories_select_show' => 'Mostrar categorías',
    'categories_select_hide' => 'Ocultar categorías',
    /* bandeja de entrada */
    'label_new_email' => 'Crear nuevo email',
    'label_time_created' => 'Creado',
    'label_time_lastedit' => 'Actualizado',
    'label_time_sent' => 'Enviado',
    'recipients' => 'Destinatarios',
    'label_all_users' => 'A todos los usuarios',
    'label_marketing_users' => 'Destinatarios del boletín',
    'label_admins' => 'A administradores',
    'label_form_status_new' => 'Crear nuevo email',
    'label_form_status_edit' => 'Editar email',
    'label_subject' => 'Asunto',
    'label_text' => 'Texto',
    'label_status_public' => 'Público',
    'label_status_ghost' => 'Invisible',
    'label_status_private' => 'Privado',
    'label_status_draft' => 'Borrador',
    'label_status_redirect' => 'Redirigir',
    'label_search' => 'Buscar',

    'btn_send_email' => 'Enviar correo electrónico',
    'btn_close' => 'volver',

    'items_per_page' => 'elementos por página',

    'btn_sort_asc' => 'ascendente',
    'btn_sort_desc' => 'descendente',
    'btn_sort_linkname' => 'Nombre de enlace',
    'btn_sort_edit' => 'Último editado',

    'btn_new_group' => 'Nuevo grupo',
    'btn_new_value' => 'Nuevo valor',

    'pagination_page' => 'página',

    'missing_title' => 'Sin título ...'
];

$lang += [
    'update_msg_alpha' => 'Hay una actualización del canal Alpha disponible para descargar. Esta actualización no debe cargarse en el entorno productivo.',
    'update_msg_beta' => 'Hay una actualización del canal Beta disponible para descargar. Esta actualización no debe cargarse en el entorno productivo.',
    'update_msg_stable' => 'Hay una actualización disponible para descargar.',
    'msg_update_available' => '<b>¡Hay una actualización disponible!</b><br>Recuerde hacer una copia de seguridad de sus datos antes de instalar.',
    'msg_no_update_available' => 'La versión instalada está actualizada.',
    'msg_update_modus_activated' => '<b>El modo de actualización está activo</b><br>No se puede llamar al frontend en este momento.',
    'msg_after_update' => 'Vaya al directorio <a href="/install/">instalación</a> y verifique si la base de datos se actualizó correctamente.',
    'btn_choose_this_update' => 'Elegir esta actualización',
    'alert_filter_updated' => 'Los filtros han sido actualizados'
];

/* tn_ = topNav */

$lang['tn_dashboard'] = "Panel de control";
$lang['tn_dashboard_desc'] = "Todo de un vistazo";
$lang['tn_moduls'] = "Complementos";
$lang['tn_moduls_desc'] = "Acceder a los módulos y temas instalados";
$lang['tn_pages'] = "Páginas";
$lang['tn_pages_desc'] = "Editar contenido, crear/eliminar páginas ...";
$lang['tn_posts'] = "Blog";
$lang['tn_posts_desc'] = "Crear y administrar publicaciones, galerías...";
$lang['tn_events'] = "Eventos";
$lang['tn_events_desc'] = "Crear y gestionar eventos...";
$lang['tn_filebrowser'] = "Cargas";
$lang['tn_filebrowser_desc'] = "Cargar y administrar imágenes, gráficos y archivos";
$lang['tn_usermanagement'] = "Usuario";
$lang['tn_usermanagement_desc'] = "Administrar visitantes, moderadores y administradores";
$lang['tn_system'] = "Sistema";
$lang['tn_system_desc'] = "Configuraciones, módulos de texto y copia de seguridad de datos";
$lang['tn_contents'] = "Contenido";
$lang['tn_contents_desc'] = "Crear/editar/eliminar páginas, plantillas de texto, canales RSS ...";
$lang['tn_comments'] = 'Comentarios';
$lang['tn_reactions'] = 'Reacciones';
$lang['tn_reactions_desc'] = 'Comentarios, calificaciones y confirmaciones de asistencia a eventos';
$lang['tn_shop'] = "Tienda";

/* Links and Buttons */

$lang['back_to_page'] = "a la página de inicio";
$lang['show_help'] = "Mostrar ayuda";
$lang['logout'] = "Cerrar sesión";
$lang['edit'] = "Editar";
$lang['btn_edit_page'] = "Editar página";
$lang['update'] = "Actualizar";
$lang['delete'] = "Eliminar";
$lang['delete_selected'] = "Eliminar datos seleccionados";
$lang['preview'] = "Vista previa";
$lang['submit'] = "Ir";
$lang['duplicate'] = "Duplicar";
$lang['new'] = "Nuevo";
$lang['save_duplicate'] = "Guardar una copia";
$lang['choose'] = "Seleccionar";
$lang['display'] = "Mostrar";
$lang['save'] = "Guardar";
$lang['discard_changes'] = "Descartar";
$lang['descargar'] = "Descargar";
$lang['new_user'] = "Nuevo usuario";
$lang['edit_user'] = "Editar usuario";
$lang['list_user'] = "Todos los usuarios";
$lang['customize_user'] = "Personalizar usuario";

$lang['edit_groups'] = "Editar grupos";
$lang['legend_choose_group'] = "Seleccionar grupo";
$lang['legend_groups_data'] = "Datos del grupo de usuarios";
$lang['label_group_name'] = "Nombre del grupo";
$lang['label_group_description'] = "Descripción";
$lang['label_group_add_user'] = "Agregar/eliminar usuario";
$lang['label_public_group'] = "Grupo público";
$lang['label_hidden_group'] = "Grupo oculto";

$lang['manage_files'] = "Administrar archivos";
$lang['go_to_upload'] = "Subir archivos";

$lang['page_list'] = "Todas las páginas";
$lang['page_edit'] = "Editar página";
$lang['page_customize'] = "Personalizar páginas";
$lang['page_index'] = "Índice";
$lang['new_page'] = "Nueva página";
$lang['textlib'] = "Bloques de texto";
$lang['snippets'] = "Plantillas de texto";
$lang['filename'] = "Nombre de archivo";

$lang['shortcode'] = 'Código corto';
$lang['shortcode_replacement'] = 'Reemplazo';

$lang['reactions_comments'] = 'Comentarios';
$lang['reactions_votings'] = 'Reseñas';
$lang['reactions_events'] = 'Eventos';

$lang['save_new_page'] = "Guardar";
$lang['update_page'] = "Actualizar";
$lang['delete_page'] = "Eliminar página";

$lang['save_new_user'] = "Guardar usuario";
$lang['update_user'] = "Actualizar usuario";
$lang['delete_user'] = "Eliminar usuario";

$lang['system_preferences'] = "Preferencias";
$lang['system_mail'] = "Correo electrónico";
$lang['system_language'] = "Idioma";
$lang['system_default_language'] = 'Idioma predeterminado';
$lang['system_deactivate_languages'] = 'Ocultar idiomas';
$lang['system_images'] = "Imágenes/Cargas";
$lang['system_textlib'] = "Módulos de texto";
$lang['system_design'] = "Diseño y distribución";
$lang['system_modul_preferences'] = "Preferencias del módulo";
$lang['system_backup'] = "Copia de seguridad de datos";
$lang['system_update'] = "Actualizar";
$lang['system_statistics'] = "Estadísticas";
$lang['system_misc'] = "Varios";
$lang['activate_logfile'] = "Activar archivo de registro";
$lang['anonymize_ip'] = "Anonimizar direcciones IP";
$lang['activate_xml_sitemap'] = "Activar mapa del sitio XML";
$lang['select_logfile'] = "Seleccionar archivo de registro";
$lang['logfile_hits'] = "Entradas";

$lang['option_nothing_selected'] = "Seleccionar...";

$lang['pagination_forward'] = "Adelante";
$lang['pagination_backward'] = "Hacia atrás";

$lang['btn_files'] = "Archivos";
$lang['btn_images'] = "Imágenes";

$lang['btn_mod_enable'] = "Habilitar";
$lang['btn_mod_disable'] = "Desactivar";
$lang['btn_install'] = "Instalar";

$lang['btn_start_index'] = "Índice";
$lang['btn_bulk_index'] = "Índice múltiple";
$lang['btn_bulk_update'] = "Múltiples actualizaciones";
$lang['btn_update_page_index'] = "Volver a indexar la página";
$lang['btn_update_page_content'] = "Actualizar contenido";

$lang['btn_snippets_all'] = 'Todos';
$lang['btn_snippets_system'] = 'Sistema';
$lang['btn_snippets_own'] = 'Propio';

$lang['btn_bookings'] = 'Reservas';
$lang['btn_submit_variant'] = 'Guardar como variante';

$lang['btn_new_feature'] = 'Nueva característica';
$lang['btn_new_option'] = 'Nueva opción';

/* Headlines */

$lang['h_usermanagement'] = "Administrar usuarios";
$lang['h_filebrowser'] = "Administrar archivos";
$lang['h_pages'] = "Administrar páginas";
$lang['h_system'] = "Sistema y configuración";

$lang['h_username'] = "Nombre de usuario";
$lang['h_registerdate'] = "Fecha";
$lang['h_realname'] = "Nombre";
$lang['h_email'] = "Correo electrónico";
$lang['h_status'] = "Estado";
$lang['h_action'] = "Acción";
$lang['h_search_user'] = "Buscar usuario";
$lang['h_latest_user'] = "Los últimos usuarios";

$lang['h_page_sort'] = "Clasificación";
$lang['h_page_linkname'] = "Nombre del enlace";
$lang['h_page_title'] = "Título";
$lang['h_page_status'] = "Estado";
$lang['h_page_hits'] = "Clics";

$lang['h_modus_editpage'] = "Editar página";
$lang['h_modus_newpage'] = "Crear nueva página";
$lang['h_modus_duplicate'] = "Página duplicada";
$lang['h_last_edit'] = "última actualización";

$lang['h_modus_edituser'] = "Editar usuario";
$lang['h_modus_newuser'] = "Crear nuevo usuario";

$lang['h_group_id'] = "ID";
$lang['h_group_name'] = "Nombre del grupo";
$lang['db_user'] = "Usuario";



/* Forms and Labels */

$lang['tab_info'] = "Información";
$lang['tab_info_description'] = "Información sobre visualización y clasificación";

$lang['tab_user_info'] = "Información";
$lang['tab_user_info_description'] = "Estado de usuario, grupos y estadísticas";

$lang['tab_contact'] = "Detalles de contacto";
$lang['tab_contact_description'] = "Detalles de contacto";

$lang['tab_psw'] = "Gestión de contraseñas y derechos";
$lang['tab_psw_description'] = "Cambiar contraseña";

$lang['tab_content'] = "Contenido";
$lang['tab_content_description'] = "Contenido";

$lang['tab_extracontent'] = "Contenido opcional";
$lang['tab_extracontent_description'] = "Contenido para la visualización adicional";

$lang['tab_meta'] = "Metaetiquetas";
$lang['tab_meta_description'] = "Metainformación: palabras clave, etc.";

$lang['tab_head'] = "Encabezado (Código/HTML)";
$lang['tab_head_description'] = "Otra información del encabezado";

$lang['tab_addons'] = "Complementos";

$lang['tab_page_preferences'] = "Preferencias";
$lang['tab_page_preferences_description'] = "Plantillas...";

$lang['tab_posts'] = 'Publicaciones';

$lang['lastedit'] = "Última actualización";
$lang['filesize'] = "Tamaño de archivo";
$lang['date_of_change'] = "Cambiar fecha";
$lang['filename'] = "Nombre";

$lang['label_comment'] = "Escribir comentario";

$lang['files'] = "Archivos";
$lang['images'] = "Imágenes";
$lang['thumbnails'] = "Miniaturas";
$lang['thumbnail'] = "Miniatura";
$lang['browse_files'] = "Buscar archivos en el disco";
$lang['browse_images'] = "Buscar en el disco sólo imágenes";
$lang['clear_list'] = "Borrar lista";
$lang['upload'] = "Iniciar carga";
$lang['upload_img_legend'] = "Subir imágenes";
$lang['upload_files_legend'] = "Subir archivos";
$lang['upload_target_files'] = "Agregar a archivos";
$lang['upload_target_images'] = "Agregar a imágenes";
$lang['upload_complete'] = "Los archivos han sido cargados";
$lang['upload_destination'] = "Seleccionar carpeta de destino";
$lang['create_new_folder'] = 'Crear carpeta';
$lang['delete_folder'] = 'Eliminar carpeta';
$lang['confirm_delete_folder'] = '¿Está seguro de que desea eliminar esta carpeta? Se eliminará todo el contenido de la carpeta.';

$lang['backup_db_content'] = "Base de datos para páginas, contenido y configuración, etc.";
$lang['backup_db_user'] = "Base de datos de datos del usuario";
$lang['start_backup'] = "Iniciar descarga";

$lang['counter_active'] = "Activar contador";
$lang['counter_inactive'] = "Desactivar contador";

$lang['f_page_position'] = "Posición";
$lang['f_page_order'] = "Pedido";
$lang['f_homepage'] = "Página de inicio";
$lang['f_mainpage'] = "Navegación principal";
$lang['f_subpage'] = "Subpágina de...";
$lang['f_page_sort'] = "Ordenar ID";
$lang['f_page_linkname'] = "Nombre del enlace";
$lang['f_page_permalink'] = "Enlace permanente";
$lang['f_page_permalink_short'] = "Enlace permanente";
$lang['f_page_permalink_short'] = "Enlace corto";
$lang['f_page_classes'] = "Clases";
$lang['f_page_hash'] = "Hash";
$lang['f_page_redirect'] = "Redireccionar";
$lang['btn_redirect'] = "Redirecciones";
$lang['legend_redirect'] = "Redirecciones";
$lang['f_page_funnel_uri'] = "URI del embudo";
$lang['f_page_title'] = "Título";
$lang['f_page_status'] = "Estado";

$lang['f_page_type_of_use'] = "Tipo de uso";
$lang['type_of_use_normal'] = "Página normal";
$lang['type_of_use_search'] = "Buscar";
$lang['type_of_use_profile'] = "Perfil";
$lang['type_of_use_register'] = "Registro";
$lang['type_of_use_password'] = "Restablecer contraseña";
$lang['type_of_use_sitemap'] = "Mapa del sitio";
$lang['type_of_use_404'] = "404 (Página no encontrada)";
$lang['type_of_use_display_post'] = "Mostrar publicación";
$lang['type_of_use_display_product'] = "Mostrar producto";
$lang['type_of_use_display_event'] = "Mostrar evento";
$lang['type_of_use_imprint'] = "Impresión";
$lang['type_of_use_privacy_policy'] = "Privacidad";
$lang['type_of_use_legal'] = "Aviso legal";
$lang['type_of_use_checkout'] = "Ver carrito de compras";
$lang['type_of_use_orders'] = "Mostrar pedidos";

$lang['legend_structured_pages'] = "páginas organizadas";
$lang['legend_unstructured_pages'] = "páginas individuales";
$lang['legend_all_pages'] = "todas las páginas";

$lang['f_page_status_puplic'] = "Público";
$lang['f_page_status_private'] = "Privado";
$lang['f_page_status_draft'] = "Borrador";
$lang['f_page_status_ghost'] = "Invisible";

$lang['f_meta_author'] = "Autor";
$lang['f_meta_date'] = "Fecha";
$lang['f_meta_keywords'] = "Palabras clave";
$lang['f_meta_description'] = "Descripción";
$lang['f_meta_robots'] = "Robots";
$lang['f_meta_enhanced'] = "Otro (HTML)";

$lang['f_head_styles'] = "Estilos (CSS)";
$lang['f_head_enhanced'] = "Otro (HTML)";

$lang['f_page_language'] = "Idioma";

$lang['f_page_template'] = "Plantilla propia";
$lang['use_standard'] = "Usar estándar";

$lang['f_page_modul'] = "Módulo";
$lang['f_page_modul_query'] = "Consulta de módulo";
$lang['f_page_authorized_admins'] = "Permitir edición";

$lang['f_user_id'] = "ID de usuario";
$lang['f_user_nick'] = "Nombre de usuario";
$lang['f_user_registerdate'] = "Fecha de registro";
$lang['f_user_status'] = "Estado";
$lang['f_user_groups'] = "Grupo";
$lang['f_user_drm'] = "Gestión de derechos (ACP)";
$lang['f_user_firstname'] = "Nombre";
$lang['f_user_lastname'] = "Apellido";
$lang['f_user_mail'] = "Correo electrónico";

$lang['f_user_company'] = "Empresa";
$lang['f_user_street'] = "Calle";
$lang['f_user_street_nbr'] = "No.";
$lang['f_user_zipcode'] = "Código postal";
$lang['f_user_city'] = "Ciudad";

$lang['f_user_psw'] = "Contraseña";
$lang['f_user_psw_new'] = "Nueva contraseña";
$lang['f_user_psw_reconfirmation'] = "Reconfirmar contraseña";

$lang['f_user_newsletter'] = "Boletín";
$lang['f_user_newsletter_none'] = "Sin boletín";
$lang['f_user_newsletter_html'] = "Como HTML";
$lang['f_user_newsletter_text'] = "En formato de texto";

$lang['f_user_psw_description'] = "Complete sólo si se va a cambiar la contraseña<br />o se va a asignar una nueva contraseña.";

$lang['f_user_select_waiting'] = "Esperando verificación";
$lang['f_user_select_verified'] = "Verificado";
$lang['f_user_select_paused'] = "Suspendido temporalmente";
$lang['f_user_select_deleted'] = "Eliminado";
$lang['f_administrators'] = "Administradores";

$lang['label_activate_posts'] = "Activar módulo de blog, eventos o tienda...";

$lang['label_position_top'] = "Esta página es un...";
$lang['label_single_page'] = "... página única";
$lang['label_portal_page'] = "... página de inicio";
$lang['label_mainnav_page'] = "... parte de la navegación principal";
$lang['label_position_sub'] = "Esta página es una subpágina de ...";

$lang['label_custom_id'] = "ID personalizado";
$lang['label_custom_classes'] = "Clases";

$lang['label_title'] = "Título";
$lang['label_description'] = "Descripción";
$lang['label_keywords'] = "Palabras clave";
$lang['label_text'] = "Texto";
$lang['label_value'] = "Valor";
$lang['label_alt'] = "Alt";
$lang['label_url'] = "URL";
$lang['label_url_name'] = "Nombre";
$lang['label_url_title'] = "Título";
$lang['label_url_classes'] = "Clases";
$lang['label_priority'] = "Prioridad";
$lang['label_license'] = "Licencia";
$lang['label_credits'] = "Derechos de autor";
$lang['label_version'] = 'Versión';
$lang['label_notes'] = "Notas";
$lang['label_color'] = "Color";
$lang['missing_value'] = 'valor faltante';
$lang['label_filename'] = 'Nombre de archivo';
$lang['label_content'] = 'Contenido';
$lang['label_groups'] = 'Grupos';
$lang['label_classes'] = 'Clases';
$lang['label_password'] = 'Contraseña';
$lang['label_password_reset'] = 'Restablecer contraseña';
$lang['label_filter'] = "Filtro";
$lang['label_filter_reset'] = "Restablecer filtro";
$lang['label_type'] = "Tipo";
$lang['label_overwrite_existing_files'] = "Sobrescribir archivos existentes";

$lang['label_ready_to_install'] = 'Listo para instalar';
$lang['remember_me'] = 'Recordarme';

$lang['label_deleted_resources'] = 'Recursos eliminados';

$lang['label_missing_img_alt_tags'] = 'Faltan etiquetas alternativas (&lt;img&gt;)';
$lang['label_missing_img_title_tags'] = 'Faltan etiquetas de título (&lt;img&gt;)';
$lang['label_missing_link_title_tags'] = 'Faltan etiquetas de título (&lt;href&gt;)';
$lang['label_missing_h1'] = 'Falta H1';
$lang['label_missing_h2'] = 'Falta H2';
$lang['label_missing_title'] = 'Falta título de página';
$lang['label_missing_meta_description'] = 'Falta meta descripción';

$lang['label_active_theme'] = 'Tema activo';
$lang['label_installed_themes'] = 'Temas instalados';
$lang['label_image_selected'] = 'imágenes seleccionadas';

$lang['label_show_entries'] = 'Mostrando %s de %s publicaciones';
$lang['label_show_events'] = 'Mostrando %s de %s eventos';
$lang['label_show_products'] = 'Mostrando %s de %s productos';

$lang['label_data_submited'] = 'Enviado';
$lang['label_data_lastedit'] = 'Última edición';
$lang['label_data_releasedate'] = 'Publicado';

/* tienda/envío */
$lang['label_shipping'] = 'Envío';
$lang['label_shipping_costs_flat'] = 'Costos de envío fijos';
$lang['label_shipping_costs_cat'] = 'Categorías de costos de envío';
$lang['label_shipping_costs_no_cat'] = 'Sin categoría';
$lang['label_shipping_costs_cat1'] = 'Costos de envío categoría 1';
$lang['label_shipping_costs_cat2'] = 'Costos de envío categoría 2';
$lang['label_shipping_costs_cat3'] = 'Costos de envío categoría 3';
$lang['label_shipping_costs_by_weight'] = 'Costos de envío por peso';

$lang['label_shipping_mode'] = 'Cálculo del costo de envío';
$lang['label_shipping_mode_flat'] = 'Cobrar sólo la tarifa plana de envío';
$lang['label_shipping_mode_cats'] = 'La categoría más cara del carrito de compras determina los costos de envío';

$lang['label_shipping_mode_digital'] = 'Este artículo no será entregado';
$lang['label_shipping_mode_deliver'] = 'Este artículo se está entregando';

$lang['label_tax_currency'] = 'Impuestos y moneda';
$lang['label_paid_methods'] = 'Métodos de pago';
$lang['label_paid_bank_transfer'] = 'Transferencia';
$lang['label_paid_invoice'] = 'Factura';
$lang['label_paid_cash'] = 'Pago en efectivo';
$lang['label_paid_paypal'] = 'PayPal';
$lang['label_paid_costs'] = 'Costos';

$lang['label_order_nbr'] = 'Número de pedido';
$lang['label_order_date'] = 'Fecha';

$lang['label_status_order'] = 'Estado del pedido';
$lang['status_order_received'] = 'Recibido';
$lang['status_order_completed'] = 'Completado';
$lang['status_order_canceled'] = 'Cancelado';

$lang['label_status_paid'] = 'Pago';
$lang['status_paid_open'] = 'Abrir';
$lang['status_paid_paid'] = 'Pagado';

$lang['label_status_shipping'] = 'Envío';
$lang['status_shipping_prepared'] = 'Preparado';
$lang['status_shipping_shipped'] = 'Enviado';

$lang['label_default_sorting'] = 'Clasificación predeterminada';
$lang['set_sorting_default'] = 'Predeterminado';
$lang['set_sorting_topseller'] = 'Más vendido';
$lang['set_sorting_name'] = 'Nombre';
$lang['set_sorting_price_asc'] = 'Precio - el más bajo primero';
$lang['set_sorting_price_desc'] = 'Precio - el más alto primero';

$lang['btn_send_mail_to_admin'] = 'Enviarme por correo electrónico';

/* zonas de entrega */
$lang['label_add_delivery_country'] = 'Agregar país';

/* datos comerciales */
$lang['label_business_address'] = 'Dirección comercial';
$lang['label_tax_number'] = 'Número fiscal';


/* Preferencias */

$lang['label_maintenance_code'] = "Habilitar modo de mantenimiento (establecer código)";
$lang['f_prefs_descriptions'] = "Descripciones";
$lang['f_prefs_user'] = "Preferencias de usuario";
$lang['f_prefs_registration'] = "Permitir nuevos registros";
$lang['f_prefs_showloginform'] = "Mostrar formulario de inicio de sesión";
$lang['f_prefs_uploads'] = "Cargas";
$lang['f_prefs_layout'] = "Diseño y distribución";
$lang['f_prefs_custom_fields'] = "Campos personalizados";

$lang['f_prefs_pagename'] = "Nombre de la página";
$lang['f_prefs_pagetitle'] = "Título de la página";
$lang['f_prefs_pagesubtitle'] = "Subtítulo";
$lang['f_prefs_pagedescription'] = "Descripción";

$lang['f_prefs_default_publisher'] = 'Editor predeterminado';
$lang['f_prefs_publisher_mode'] = 'Sobrescribir';

$lang['f_prefs_imagesuffix'] = "Finales (gráficos)";
$lang['f_prefs_filesuffix'] = "Terminales (otros archivos)";

$lang['f_prefs_maxfilesize'] = "Tamaño máximo de archivo (KB)";
$lang['f_prefs_maximage'] = "Tamaño máximo (píxeles)";
$lang['f_prefs_showfilesize'] = "Mostrar tamaños de archivos";
$lang['f_prefs_uploads_remain_unchanged'] = "Subir archivos sin cambios";

$lang['themes_templates'] = 'Temas y plantillas';
$lang['f_prefs_active_template'] = "Tema seleccionado";

$lang['f_prefs_userstyles_off'] = "Evitar la selección de visitantes";
$lang['f_prefs_userstyles_on'] = "Permitir la selección por parte de los visitantes. Sólo afecta al tema predeterminado seleccionado. Si a una página se le ha asignado su propia plantilla, esta selección no se ve afectada.";
$lang['f_prefs_userstyles_overwrite'] = "Permitir la selección por visitante. La plantilla seleccionada por el visitante sobrescribe la configuración de plantilla de una página. Si los archivos necesarios están disponibles.";

$lang['f_prefs_global_header'] = "Información de encabezado global (HTML)";

$lang['select_filesize_yes'] = "Mostrar tamaños de archivos";
$lang['select_filesize_no'] = "No mostrar tamaños de archivos";

$lang['page_thumbnail'] = "Miniatura";
$lang['page_thumbnail_default'] = "Miniatura predeterminada";
$lang['page_thumbnail_prefix'] = "Prefijo de miniatura";
$lang['page_favicon'] = "Favicon";
$lang['rss_offset'] = "Búfer de tiempo RSS (segundos)";

$lang['prefs_mailer_name'] = "Nombre del remitente";
$lang['prefs_mailer_adr'] = "Dirección de correo electrónico";
$lang['prefs_mail_type'] = "Tipo de correo";
$lang['prefs_mail_type_smtp'] = "Usar SMTP";
$lang['prefs_mail_type_mail'] = "Usar la función PHP mail()";
$lang['prefs_mail_return_path'] = "Ruta de retorno";
$lang['prefs_mail_use_return_path'] = "Usar la ruta de retorno.";
$lang['prefs_mailer_smtp_host'] = "Host SMTP";
$lang['prefs_mailer_smtp_port'] = "Puerto SMTP";
$lang['prefs_mailer_smtp_encryption'] = "Cifrado";
$lang['prefs_mailer_smtp_authentication'] = "Autenticación";
$lang['prefs_mailer_smtp_username'] = "Nombre de usuario";
$lang['prefs_mailer_smtp_password'] = "Contraseña";
$lang['prefs_mailer_send_test'] = "Enviar correo electrónico de prueba";
$lang['prefs_mailer_send_test_success'] = "El correo electrónico ha sido enviado";
$lang['prefs_mail_type_smtp_desc'] = 'Para usar SMTP, necesita crear un archivo <code>config_smtp.php</code> en la carpeta <code>/content/</code>.';

$lang['prefs_nbr_page_versions'] = "Número de versiones de página";
$lang['prefs_pagesort_minlength'] = "Longitud mínima de la cadena &quot;orden&quot; ";

$lang['prefs_comments_mode_1'] = "Todos los comentarios deben ser aprobados por un administrador";
$lang['prefs_comments_mode_2'] = "Los comentarios aparecen inmediatamente";
$lang['prefs_comments_mode_3'] = "Desactivar la función de comentarios";
$lang['prefs_comments_auth_1'] = 'Sólo los usuarios registrados pueden comentar';
$lang['prefs_comments_auth_2'] = 'Los usuarios deben proporcionar su nombre y correo electrónico';
$lang['prefs_comments_auth_3'] = 'Permitir todos los comentarios (no recomendado)';
$lang['prefs_comments_autoclose_time'] = 'Cerrar comentarios después de X segundos';
$lang['label_comment_auto'] = 'Comentarios - automatizados';
$lang['label_comment_auth'] = 'Comentarios - Autorización';
$lang['label_comment_mode'] = 'Comentarios - Modo';
$lang['label_comments'] = 'Comentarios';
$lang['label_filter_by_status'] = 'Filtrar por estado';
$lang['label_filter_comments_by_page'] = 'Comentarios de páginas';
$lang['label_filter_comments_by_posts'] = 'Comentarios de publicaciones';
$lang['label_all_comments'] = 'Todos los comentarios';
$lang['label_comments_status1'] = 'Aún no publicado';
$lang['label_comments_status2'] = 'Comentarios públicos';
$lang['label_comments_max_entries'] = 'Número máximo de publicaciones por hilo';
$lang['label_comments_max_level'] = 'Profundidad máxima de un hilo';

$lang['label_carts'] = 'Carritos';
$lang['carts_deactivated'] = 'Desactivar carritos de compras';
$lang['carts_for_registered'] = 'Activar carritos de compras para usuarios registrados';
$lang['carts_for_all'] = 'Habilitar carritos de compras para todos los usuarios';

$lang['customize_database'] = 'Personalizar base de datos';
$lang['migrate_database'] = 'Migrar base de datos';

$lang['yes'] = "Sí";
$lang['no'] = "No";

$lang['labels'] = 'Etiquetas';
$lang['categories'] = 'Categorías';

$lang['category_name'] = 'Nombre';
$lang['category_priority'] = 'Prioridad';
$lang['category_thumbnail'] = 'Miniatura';
$lang['category_description'] = 'Descripción';

$lang['no_image'] = 'Sin imagen';

$lang['prefs_cms_domain'] = 'Dominio CMS';
$lang['prefs_cms_ssl_domain'] = 'Dominio SSL';
$lang['prefs_cms_base'] = 'URI base';
$lang['alert_prefs_cms_domain'] = 'Falta configuración: ' . $lang['prefs_cms_domain'];
$lang['alert_prefs_cms_base'] = 'Falta la configuración: ' . $lang['prefs_cms_base'];

$lang['cache'] = 'Caché';
$lang['cache_lifetime'] = 'Duración de la caché (segundos)';
$lang['compile_check'] = 'Compilación de verificación';
$lang['delete_cache'] = 'Eliminar caché';

$lang['acp_session_lifetime'] = 'Duración de la sesión ACP (segundos)';


$lang['label_datetime_settings'] = 'Configuración de fecha y hora';
$lang['label_datetime_timezone'] = 'Zona horaria';
$lang['label_datetime_dateformat'] = 'Formato de fecha';
$lang['label_datetime_timeformat'] = 'Formato de hora';

$lang['label_datetime_today'] = 'Hoy';
$lang['label_datetime_yesterday'] = 'Ayer';

/* Rights Management */

$lang['drm_description'] = "El usuario puede realizar las siguientes acciones";
$lang['drm_administrator'] = "El usuario es administrador";
$lang['drm_administrator_desc'] = "<strong>¡Atención!</strong> Esta opción permite al usuario acceder al ACP (es decir, al área de administración)";
$lang['drm_pages'] = "Crear páginas";
$lang['drm_editpages'] = "Editar todas las páginas";
$lang['drm_editownpages'] = "Editar sólo páginas propias";
$lang['drm_user'] = "Administrar usuarios";
$lang['drm_user_desc'] = "<strong>¡Atención!</strong> Si esta opción está activada, el usuario puede cambiar todos los permisos. Esto, por supuesto, también se aplica a sus propios derechos de usuario.";
$lang['drm_system'] = "Realizar ajustes";
$lang['drm_files'] = "Subir archivos";
$lang['drm_sensitive_files'] = "Subir archivos confidenciales";
$lang['drm_sensitive_files_desc'] = "<strong>¡Atención!</strong> Esta opción permite al usuario instalar/desinstalar temas, módulos y complementos.";
$lang['drm_no_access'] = "No tiene los derechos de acceso necesarios para editar estos registros";
$lang['drm_moderator'] = "Moderador";
$lang['drm_user_can_publish'] = "El usuario puede publicar";
$lang['upload_addons_deactivated'] = 'La función de carga de complementos está deshabilitada. Esta función sólo se puede activar a través del archivo config.php.';


/* Messages */

$lang['msg_support_addon_not_activated'] = "Un complemento de soporte está instalado pero no activado.";
$lang['msg_community_edition'] = "Gracias por utilizar la edición comunitaria de SwiftyEdit. Descubra aquí cómo puede apoyar el desarrollo.";

$lang['msg_sql_changes'] = "Número de registros actualizados: %d";
$lang['msg_user_exists'] = "Este nombre de usuario ya existe";
$lang['msg_user_mandatory'] = "El nombre de usuario es un campo obligatorio";
$lang['msg_usermail_exists'] = "Ya existe un usuario con esta dirección de correo electrónico";
$lang['db_changed'] = "La base de datos ha sido actualizada";
$lang['db_record_changed'] = "El registro ha sido actualizado";
$lang['db_not_changed'] = "La base de datos no fue actualizada";
$lang['msg_user_updated'] = "El usuario ha sido actualizado";
$lang['msg_new_user_saved'] = "Se ha guardado el nuevo usuario";
$lang['msg_user_deleted'] = "El usuario ha sido eliminado";

$lang['confirm_delete_data'] = "¿Está seguro de que desea eliminar este registro?";
$lang['confirm_delete_user'] = "¿Está seguro de que desea eliminar este usuario?";
$lang['confirm_delete_file'] = "¿Está seguro de que desea eliminar este archivo?";
$lang['confirm_delete_usergroup'] = "¿Está seguro de que desea eliminar este grupo de usuarios?";

$lang['msg_psw_error'] = "Las contraseñas no coinciden.";
$lang['msg_psw_changed'] = "La contraseña ha sido cambiada.";

$lang['backup_description'] = "Aquí puede descargar las bases de datos a su computadora local. Para poder traer los datos nuevamente al sistema, deben copiarse a los directorios respectivos a través de FTP.";

$lang['msg_file_delete'] = "El archivo ha sido eliminado.";
$lang['msg_file_delete_error'] = "El archivo no se pudo eliminar.";
$lang['msg_entry_delete'] = "La entrada ha sido eliminada.";
$lang['msg_data_delete'] = "Se han borrado los datos";

$lang['msg_page_saved'] = "La página ha sido guardada";
$lang['msg_page_saved_error'] = "Se produjo un error al guardar la página";
$lang['msg_page_deleted'] = "La página ha sido eliminada";
$lang['msg_page_updated'] = "La página ha sido actualizada";
$lang['msg_error_deleting_sub_pages'] = 'No puedes eliminar páginas con subpáginas. Para hacer esto, primero debe eliminar todas las subpáginas contenidas en el mismo.';



$lang['alert_no_htaccess'] = 'No hay ningún archivo .htaccess.';
$lang['alert_not_writable'] = 'El archivo o directorio no tiene los permisos de escritura necesarios:';
$lang['alert_no_modules'] = 'Aún no hay módulos instalados.';

$lang['alert_no_page_title'] = 'Sin título de página';
$lang['alert_no_page_description'] = 'Sin descripción de página';
$lang['alert_prefs_thumbnails'] = 'La información del tamaño de la miniatura está incompleta';

$lang['alert_no_plugins'] = 'Aún no hay complementos instalados.';

$lang['dir_must_be_writable'] = 'El directorio %s debe poder escribirse.';
$lang['section_is_beta'] = '<strong>Tenga en cuenta:</strong><br>Esta sección está en versión beta y aún no se ha probado exhaustivamente.';
$lang['msg_nothing_to_install'] = 'Aún no se han cargado complementos para su instalación.';

$lang['msg_no_help_doc'] = 'Desafortunadamente, todavía no hay instrucciones para esto';
$lang['msg_no_entries_so_far'] = 'No hay entradas aquí todavía';

$lang['section_is_danger_zone'] = '<strong>Consejos de seguridad (incompletos)</strong><ul>
<li>Sube archivos sólo si estás seguro de que no contienen código malicioso</li>
<li>Para probar nuevos complementos, es recomendable utilizar un sitio de prueba o de prueba</li>
<li>Comprueba los permisos de lectura y escritura del sistema</li>
<li>Deshabilitar funciones PHP innecesarias y críticas (exec, shell_exec, passthru, show_source...)</li>
</ul>';

/* System */

$lang['txtlib_welcome'] = "Introducción (Portal)";
$lang['txtlib_welcome_desc'] = "El texto introductorio en la página de inicio";

$lang['txtlib_agreement'] = "Texto de confirmación";
$lang['txtlib_agreement_desc'] = "Los visitantes deben aceptar este texto antes de poder abrir una cuenta.";

$lang['txtlib_extra_content'] = "Contenido adicional";
$lang['txtlib_extra_content_desc'] = "Este texto/código aparecerá permanentemente en la sección Contenido adicional de su página.";

$lang['txtlib_page_footer'] = "Texto de pie de página";
$lang['txtlib_page_footer_desc'] = "Este texto/código aparecerá permanentemente en el área del pie de página de su página.";

$lang['txtlib_no_access'] = "Acceso denegado...";
$lang['txtlib_no_access_desc'] = "Este mensaje aparece cuando un visitante intenta acceder a una página no autorizada";

$lang['txtlib_account_confirm'] = "Cuenta confirmada...";
$lang['txtlib_account_confirm_desc'] = "Este mensaje aparece cuando un visitante ha activado su cuenta.";

$lang['txtlib_account_confirm_mail'] = "Enlace de confirmación (correo electrónico)";
$lang['txtlib_account_confirm_mail_desc'] = "Plantilla de correo electrónico: se enviará como un correo electrónico (de confirmación).";

/* personalizar páginas */

$lang['legend_custom_fields'] = "Campos personalizados";
$lang['custom_field_name'] = "Nombre";
$lang['add_custom_field'] = "Agregar campo personalizado";
$lang['delete_custom_field'] = "Eliminar campo";
$lang['delete_custom_field_desc'] = "Si elimina un campo, los datos que contiene (todas las páginas) se perderán irremediablemente.";
$lang['no_custom_fields'] = "Aún no hay campos personalizados";
