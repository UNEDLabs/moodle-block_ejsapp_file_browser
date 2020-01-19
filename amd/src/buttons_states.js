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
    var SELECTORS = {
        // Block
        HIDEINTERACTION: "#hide_interaction",
        SHOWINTERACTION: "#show_interaction",
        CAPTUREINTERACTION: "#captureInteraction",
        STOPCAPTURE: "#stopCaptureButton",
        RESETCAPTURE: "#resetCaptureButton",
        STEPCAPTURE: "#stepCaptureButton",
        STARTCAPTURE: "#startCaptureButton",
        PLAYCAPTURE: "#playCaptureButton",
        // Share files.php
        SHARECHECKBOXES: "input.usercheckbox",
        CHECKALLBUTTON: "#checkall",
        CHECKNONEBUTTON: "#checknone"
    };

    var t = {
        init: function(reproducing) {
            $(SELECTORS.HIDEINTERACTION).click(function() {
                $(SELECTORS.CAPTUREINTERACTION).hide();
            });

            $(SELECTORS.SHOWINTERACTION).click(function() {
                $(SELECTORS.CAPTUREINTERACTION).show();
            });

            $(SELECTORS.STOPCAPTURE).prop('disabled', true);
            if (!reproducing) {
                $(SELECTORS.RESETCAPTURE).prop('disabled', true);
                $(SELECTORS.STEPCAPTURE).prop('disabled', true);
            } else {
                $(SELECTORS.STARTCAPTURE).prop('disabled', true);
                $(SELECTORS.PLAYCAPTURE).prop('disabled', true);
            }

            $(SELECTORS.STARTCAPTURE).click(function() {
                _model.startCapture();
                $(SELECTORS.STARTCAPTURE).prop('disabled', true);
                $(SELECTORS.STOPCAPTURE).prop('disabled', false);
                $(SELECTORS.RESETCAPTURE).prop('disabled', false);
                $(SELECTORS.PLAYCAPTURE).prop('disabled', true);
                $(SELECTORS.STEPCAPTURE).prop('disabled', true);
            });

            $(SELECTORS.STOPCAPTURE).click(function() {
                _model.saveText('recording', 'rec', JSON.stringify(_model.stopCapture()));
                $(SELECTORS.STARTCAPTURE).prop('disabled', false);
                $(SELECTORS.STOPCAPTURE).prop('disabled', true);
                $(SELECTORS.RESETCAPTURE).prop('disabled', false);
            });

            $(SELECTORS.RESETCAPTURE).click(function() {
                _model.resetCapture();
                $(SELECTORS.STARTCAPTURE).prop('disabled', false);
                $(SELECTORS.STOPCAPTURE).prop('disabled', true);
                $(SELECTORS.RESETCAPTURE).prop('disabled', true);
                $(SELECTORS.PLAYCAPTURE).prop('disabled', false);
            });

            $(SELECTORS.PLAYCAPTURE).click(function() {
                _model.readText(null, '.rec', function(content) {
                    _model.playCapture(JSON.parse(content), function() {
                        $(SELECTORS.STARTCAPTURE).disabled = false;
                        $(SELECTORS.PLAYCAPTURE).disabled = false;
                        $(SELECTORS.STEPCAPTURE).disabled = false;
                        window.alert("End of reproduction");
                    });
                });
                $(SELECTORS.STARTCAPTURE).prop('disabled', true);
                $(SELECTORS.STOPCAPTURE).prop('disabled', true);
                $(SELECTORS.RESETCAPTURE).prop('disabled', false);
                $(SELECTORS.PLAYCAPTURE).prop('disabled', true);
                $(SELECTORS.STEPCAPTURE).prop('disabled', false);
            });

            $(SELECTORS.STEPCAPTURE).change(function() {
                var stepCapt;
                if (stepCaptureButton.value >= 0) {
                    stepCapt = stepCaptureButton.value + 1;
                } else {
                    stepCapt = 1 + 1.8 * stepCaptureButton.value / 8;
                }
                _model.changeCaptureStep(stepCapt);
            });
        },

        shareFilesWithUsers: function() {
            $(SELECTORS.CHECKALLBUTTON).on('click', function() {
                $(SELECTORS.SHARECHECKBOXES).prop('checked', true);
            });

            $(SELECTORS.CHECKNONEBUTTON).on('click', function() {
                $(SELECTORS.SHARECHECKBOXES).prop('checked', false);
            });
        }
    };
    return t;
});