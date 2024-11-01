<?php
/**
 * Plugin Name: Vremenska prognoza
 * Author: Haris Hadzimehmedagic
 * Author URI: Plugin Author Link
 * Description: Vremenska prognoza - prikazivanje vremenske prognoze za određeni grad, dockovanu na top stranice
 * Version: 1.0
 * License: 1.0
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
*/



register_activation_hook(__FILE__, "VremenskaPrognoza_Aktivacija");
function VremenskaPrognoza_Aktivacija() {
    global $wpdb;
	$imetable = 'prognoza';
		$sql = "CREATE TABLE `$imetable` (";
        $sql .= " `id` int(11) NOT NULL auto_increment, ";
		$sql .= " `api_kljuc` varchar(500) NOT NULL, ";
		$sql .= " PRIMARY KEY `id` (`id`) ";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		require_once(ABSPATH . '/wp-admin/includes/upgrade.php' );
        $sql .= "INSERT INTO 'prognoza' VALUES (1, 'NemaAPI');";
		dbDelta($sql);
}


add_action("admin_menu", "VremenskaPrognoza_Meni");
function VremenskaPrognoza_Meni(){
    add_menu_page("Prognoza", "Prognoza Opcije", 'administrator', "prognoza-opcije", "VremenskaPrognoza_Postavke");
}


function VremenskaPrognoza_Postavke(){
    wp_register_style( 'glavnicss', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_style( 'glavnicss' );
    global $wpdb;
    echo '<h3> Prognoza podešavanja </h3>';
    echo '<form action="'.esc_url($_SERVER['REQUEST_URI']).'"method="post">';
    echo '<input type="text" name="apikljuc" placeholder="Unesi novi API kljuc" /></br></br>';
    echo '<button class="button" type="submit" name="registracija_kljuca">Registruj API kljuc</button>';
    

    $centr = $wpdb->get_var('SELECT api_kljuc FROM prognoza');
    echo '<p>';
    echo esc_html("Trenutni API kljuc: ".$centr);
    echo '</form>';
    
    if (isset($_POST['registracija_kljuca'])) {
		$kljuc = sanitize_text_field( $_POST["apikljuc"] );
        $kljucic = ($wpdb->update('prognoza', array( 'api_kljuc' => $kljuc),array('id' => 1),array('%s')));
        if ($kljucic === FALSE || $kljucic < 1) {
            $wpdb->insert('prognoza', array( 'api_kljuc' => $kljuc), array('%s'));
        }
        echo "<meta http-equiv='refresh' content='0'>";
    }  
};

add_shortcode("prognoza", "VremenskaPrognoza_Aktiviraj");
function VremenskaPrognoza_Aktiviraj(){
    global $wpdb;
    $api = $wpdb->get_var('SELECT api_kljuc FROM prognoza');
    if ($api == FALSE) {echo '<p>API kod nije unešen.';} else {
        $link = 'http://api.openweathermap.org/data/2.5/weather?q=Visoko,BA&units=metric&appid='.$api;
        $jsonfile = wp_remote_get($link);
        if( is_array($jsonfile) ) {
            $header = $jsonfile['headers'];
            $body = $jsonfile['body'];
            $resp = json_decode($body);
            $temperatura = $resp->main->temp;
            $pritisak = $resp->main->pressure;
            echo esc_html('Vremenska prognoza za grad Visoko: ');
            echo '</br>';
            echo esc_html('Trenutna temperatura: '.$temperatura);
            echo '</br>';
            echo esc_html('Trenutni pritisak: '.$pritisak);
          }

        
    }
}

?>


