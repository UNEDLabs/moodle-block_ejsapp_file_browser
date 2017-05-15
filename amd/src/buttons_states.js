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
 * Actions associated to the buttons "Show" and "Hide" of the block.
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    var t = {
        init: function(reproducing) {
            $("#hide_blockly").click(function() {
                $("#blocklyControl").hide();
            });

            $("#show_blockly").click(function() {
                $("#blocklyControl").show();
            });

            $("#hide_interaction").click(function() {
                $("#captureInteraction").hide();
            });

            $("#show_interaction").click(function() {
                $("#captureInteraction").show();
            });

            $("#stopCaptureBut").prop('disabled', true);
            if (!reproducing) {
                $("#resetCaptureBut").prop('disabled', true);
                $("#stepCaptureBut").prop('disabled', true);
            } else {
                $("#startCaptureBut").prop('disabled', true);
                $("#playCaptureBut").prop('disabled', true);
            }

            $("#startCaptureBut").click(function() {
                $("#startCaptureBut").prop('disabled', true);
                $("#stopCaptureBut").prop('disabled', false);
                $("#resetCaptureBut").prop('disabled', false);
                $("#playCaptureBut").prop('disabled', true);
                $("#stepCaptureBut").prop('disabled', true);
            });

            $("#stopCaptureBut").click(function() {
                $("#startCaptureBut").prop('disabled', false);
                $("#stopCaptureBut").prop('disabled', true);
                $("#resetCaptureBut").prop('disabled', false);
            });

            $("#resetCaptureBut").click(function() {
                $("#startCaptureBut").prop('disabled', false);
                $("#stopCaptureBut").prop('disabled', true);
                $("#resetCaptureBut").prop('disabled', true);
                $("#playCaptureBut").prop('disabled', false);
            });

            $("#playCaptureBut").click(function() {
                $("#startCaptureBut").prop('disabled', true);
                $("#stopCaptureBut").prop('disabled', true);
                $("#resetCaptureBut").prop('disabled', false);
                $("#playCaptureBut").prop('disabled', true);
                $("#stepCaptureBut").prop('disabled', false);
            });
        }
    };
    return t;
});