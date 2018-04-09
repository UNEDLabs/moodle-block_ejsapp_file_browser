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

            $("#stopCaptureButton").prop('disabled', true);
            if (!reproducing) {
                $("#resetCaptureButton").prop('disabled', true);
                $("#stepCaptureButton").prop('disabled', true);
            } else {
                $("#startCaptureButton").prop('disabled', true);
                $("#playCaptureButton").prop('disabled', true);
            }

            $("#startCaptureButton").click(function() {
                _model.startCapture();
                $("#startCaptureButton").prop('disabled', true);
                $("#stopCaptureButton").prop('disabled', false);
                $("#resetCaptureButton").prop('disabled', false);
                $("#playCaptureButton").prop('disabled', true);
                $("#stepCaptureButton").prop('disabled', true);
            });

            $("#stopCaptureButton").click(function() {
                _model.saveText('recording','rec',JSON.stringify(_model.stopCapture()));
                $("#startCaptureButton").prop('disabled', false);
                $("#stopCaptureButton").prop('disabled', true);
                $("#resetCaptureButton").prop('disabled', false);
            });

            $("#resetCaptureButton").click(function() {
                _model.resetCapture();
                $("#startCaptureButton").prop('disabled', false);
                $("#stopCaptureButton").prop('disabled', true);
                $("#resetCaptureButton").prop('disabled', false);
                $("#playCaptureButton").prop('disabled', false);
            });

            $("#playCaptureButton").click(function() {
                _model.readText(null,'.rec',function(content) {
                    _model.playCapture(JSON.parse(content),function() {
                        $("#startCaptureButton").disabled=false;
                        $("#playCaptureButton").disabled=false;
                        $("#stepCaptureButton").disabled=false;
                        window.alert("End of reproduction");
                    });
                });
                $("#startCaptureButton").prop('disabled', true);
                $("#stopCaptureButton").prop('disabled', true);
                $("#resetCaptureButton").prop('disabled', false);
                $("#playCaptureButton").prop('disabled', true);
                $("#stepCaptureButton").prop('disabled', false);
            });

            $("#stepCaptureButton").change(function() {
                var stepCapt;
                if (stepCaptureButton.value >= 0) {
                    stepCapt = stepCaptureButton.value + 1;
                } else {
                    stepCapt = 1 + 1.8 * stepCaptureButton.value / 8;
                }
                _model.changeCaptureStep(stepCapt);
            });
        }
    };
    return t;
});