<?php

if ( ! defined('ABSPATH') ) {
    die('Direct access not permitted.');
}


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}





// Crowjob registration
function custom_cron_schedule($schedules) {
	$schedules['every_four_days'] = array(
		'interval' => 4 * DAY_IN_SECONDS, 
		'display'  => __('Cada 4 días'),
	);

	return $schedules;
}


// Event schedule
if (!wp_next_scheduled('my_custom_cron_event')) {
	wp_schedule_event(time(), 'every_four_days', 'my_custom_cron_event');
}

// Luego vinculamos la acción del evento a la función que queremos que se ejecute
add_action('my_custom_cron_event', 'check_links_function');

// Finalmente, la función que queremos que se ejecute
function check_links_function() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'cronjobs_broke_links'; // Reemplaza 'nombre_tabla' con el nombre de tu tabla

    if(!$table_name){
        $charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'cronjobs_broke_links'; // Reemplaza 'nombre_tabla' con el nombre que desees para tu tabla

		$sql = "CREATE TABLE $table_name (
			id INT NOT NULL AUTO_INCREMENT,
			post_url VARCHAR(255) NOT NULL,
			post_title VARCHAR(255) NOT NULL,
			estado VARCHAR(255) NOT NULL,
			url_link VARCHAR(255) NOT NULL,
			ID_link VARCHAR(255) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
    }
    elseif($table_name['id'] != 0){
        //Code to incorporate data in the table of crobjobs//

						/*						
						global $wpdb;

						$table_name = $wpdb->prefix . 'cronjobs_broke_links'; // Reemplaza 'nombre_tabla' con el nombre de tu tabla

						$datos = array(
						'post_url' => $url,
						'post_title' => get_the_title($post),
						'estado' => $estado,
						'url_link' => get_permalink($post->ID),
						'ID_link' => $post->ID,
						);

						$wpdb->insert($table_name, $datos);

						*/
        }
/*
        $datos = array(
            'post_url' => $url,
            'post_title' => get_the_title($post),
            'estado' => $estado,
            'url_link' => get_permalink($post->ID),
            'ID_link' => $post->ID,
        );

        $wpdb->insert($table_name, $datos);
*/

}




class Enlaces_Table extends WP_List_Table {
    
	
	function get_data() {

        $columnas_data = array();
		
		
		$args = array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);

		$all_posts_pages = get_posts($args);

        // Crea un array vacío para almacenar las URLs
		$post_urls = array();
		$post_title_url = array();
		$post_estado = array();
		$url_link = array();

		foreach($all_posts_pages as $post){
			setup_postdata($post);
			

			
			// Puedes acceder a otros datos del post de esta manera:
			// $post->post_content, $post->post_date, etc.



			$content = $post->post_content;
			$pattern = '/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU';
			preg_match_all($pattern, $content, $matches);

			if (!empty($matches[2])) {
				foreach ($matches[2] as $url) {


					/*
					$response = wp_remote_get($url, array('method' => 'HEAD'));

					if (is_wp_error($response)) {
						echo "No se pudo obtener el código de estado del enlace.";
					} else {
						$status_code = wp_remote_retrieve_response_code($response);
						echo "El enlace devuelve el código de estado: " . $status_code;
					}
					*/

					$status_code = 404;

					if ( strpos( $url, 'http://' ) === 0 ) {
						$estado = 'Insecure link';
					} elseif(substr($url, 0, 4) != 'http' && ! strpos($url, 'http') !== false) {
						$estado = 'Protocolo no especificado';
					} elseif(substr($url, 0, 4) == 'http' && ! wp_http_validate_url($url)) {
						$estado = '404 Not Found';
					} elseif(! wp_http_validate_url($url)) {
						$estado = 'Enlace malformado';
					} else {
						$estado = '';
					}



					if($estado != null){
						$columnas_data[] = array(
							'post_url' => $post_urls[] = $url,
							'post_title' => $post_title_url[] = get_the_title($post),
							'estado' => $post_estado[] = $estado,
							'url' => $url_link[] = get_permalink($post->ID),
							'ID' => $post->ID,
						);



					}

				}
			}



			//			echo $post->post_content.'<br>';
			

			/*			
				$columnas_data[] = array(
				'post_url' => $post_urls[] = $url,
				'post_title' => $post_title_url[] = get_the_title($post),
				'estado' => $post_estado[] = $estado,
				'url' => $url_link[] = get_permalink($post->ID),
				'ID' => $post->ID,
			);
			*/


		}




/*		global $wpdb;
		
*/     			//Codigo para crear base de datos// 


		
		wp_reset_postdata();


		// Cambia 'my_table' por el nombre de tu tabla
/*		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}cronjobs_broke_links", OBJECT );

		if ( $results ){
			foreach ( $results as $result ) {
				echo $result->id.'<br>';
				echo $result->post_url.'<br>';
				echo $result->post_title.'<br>';
				echo $result->estado.'<br>';
				echo $result->url_link.'<br>';
				echo $result->ID_link.'<br>';
			}
		} else {
			echo 'No se encontraron resultados';
		}
*/				//Devuelve datos de la base de datos cronjobs


		
        $resultados = get_option('va_comprobar_enlaces', $columnas_data);
        return $resultados;
    }



    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Esto añade las casillas de verificación
			'post_url' => 'URL',
			'estado' => 'Estado',
            'post_title' => 'Origen',
        );
        return $columns;
    }
    
    function prepare_items() {
        $columns = $this->get_columns();
        $this->_column_headers = array($columns);
		$this->items = $this->get_data();	
    }
    
    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'post_title':	
                return '<a href="' . $item['url'] . '" title="' . $item['url'] . '"><strong>' . $item['post_title'] . '</strong></a>';
            case 'post_url':
				return '<a href="' . $item['post_url'] . '" title="' . $item['post_url'] . '"><strong>' . $item['post_url'] . '</strong></a>';
            case 'estado':
                return '<strong style="color: orange;">'.$item['estado'].'</strong>';
            default:
                return print_r($item, true);
        }
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="post[]" value="%s" />', $item['ID']);
    }
	

	

}


function va_menu(){
    add_menu_page('Verificador Avanzado de Enlaces', 'Verificador de Enlaces', 'manage_options', 'verificador-enlaces', 'va_admin_page', 'dashicons-admin-links', 6);
}

function va_admin_page(){
    $enlacesTable = new Enlaces_Table();
    $enlacesTable->prepare_items();
    echo '<div class="wrap">';
    echo '<h2>Verificador Avanzado de Enlaces</h2>';
    $enlacesTable->display();
    echo '</div>';
}






function va_comprobar_enlaces() {
    // Este es el código que chequea los enlaces...
    // Actualiza la opción 'va_resultados' con los resultados...
}
