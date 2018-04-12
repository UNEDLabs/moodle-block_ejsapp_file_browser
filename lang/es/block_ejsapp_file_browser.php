<?php
// This file is part of the Moodle block "EJSApp File Browser"
//
// EJSApp File Browser is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp File Browser is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp File Browser has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.

/**
 * Spanish labels for the ejsapp_file_browser block
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['title_of_the_block'] = 'Navegador EJSApp de archivos';
$string['managemyfiles'] = 'Gestionar archivos';
$string['sharefiles'] = 'Compartir archivos';
$string['pluginname'] = 'Navegador EJSApp de "archivos privados"';
$string['privatefiles'] = 'Archivos privados';

$string['blockly_legend'] = 'Blockly';
$string['show_blockly_options'] = 'Mostrar';
$string['hide_blockly_options'] = 'Ocultar';
$string['run_code'] = 'Ejecutar código';
$string['save_code'] = 'Grabar código';
$string['load_code'] = 'Cargar código';

$string['capture_legend'] = 'Grabaci&oacute;n';
$string['show_capture_options'] = 'Mostrar';
$string['hide_capture_options'] = 'Ocultar';
$string['start_capture'] = 'Comenzar';
$string['stop_capture'] = 'Parar';
$string['reset_capture'] = 'Resetear';
$string['play_capture'] = 'Cargar';
$string['change_speed'] = 'Velocidad:';

// Strings in settings.php.
$string['auto_refresh_header_config'] = 'Configurar la propiedad de auto-refresco del bloque';
$string['auto_refresh'] = 'Frecuencia de auto-refresco';
$string['auto_refresh_description'] = 'Tiempo en milisegundos. Escriba "0" para deshabilitar el auto-refresco.';

// Strings for capabilities.
$string['ejsapp_file_browser:addinstance'] = 'Añadir un nuevo bloque de ficheros privados para EJSApp';
$string['ejsapp_file_browser:myaddinstance'] = 'Añadir un nuevo bloque de ficheros privados para EJSApp al &Aacute;rea personal';

// Strings for shared_files_usr.php and share_files.php.
$string['files_users_selection'] = 'Seleccione los ficheros y usuarios';
$string['files_selection'] = 'Seleccione los archivos a compartir';
$string['select_share_files'] = 'Compartir archivo/s';
$string['share'] = 'Compartir';
$string['shared_files'] = 'Archivos compartidos';
$string['continue'] = 'Continuar';
$string['you_share_file'] = 'Ha compartido el archivo';
$string['you_share_files'] = 'Ha compartido los archivos';
$string['with_participants'] = 'Con los participantes';
$string['no_file_selected'] = 'No hay archivos seleccionados';
$string['full_message_1'] = ' quiere compartir algunos ficheros contigo: ' . "\r\n" . "\n";
$string['full_message_2'] = "\r\n" . "\n" . 'Puedes aceptar (';
$string['full_message_3'] = '), rechazar (';
$string['full_message_4'] = ') o ignorar esta solicitud.';
$string['full_message_html_1'] = ' quiere compartir algunos ficheros contigo: ';
$string['full_message_html_2'] = 'Puedes ';
$string['full_message_html_3'] = ' aceptar ';
$string['full_message_html_4'] = ' rechazar ';
$string['full_message_html_5'] ='o ignorar esta solicitud.';
$string['small_message_1'] = 'Quiero compartir estos ficheros contigo: ';
$string['small_message_2'] = 'Aceptar';
$string['small_message_3'] = 'Rechazar';

// Strings for action.php.
$string['full_message_accepted'] = ' ha aceptado tus ficheros.';
$string['full_html_message_accepted'] = ' ha aceptado tus ficheros.';
$string['small_message_accepted'] = 'Acabo de aceptar tus ficheros.';
$string['full_message_rejected'] = ' ha rechazado tus ficheros.';
$string['full_html_message_rejected'] =  ' ha rechazado tus ficheros.';
$string['small_message_rejected'] = 'Acabo de rechazar tus ficheros.';