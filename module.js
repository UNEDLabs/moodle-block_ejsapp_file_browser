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
 * Javascript code
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_ejsapp_file_browser = {};

/**
 * Rendering function for showing the tree with files.
 * @param Y
 * @param expand
 * @param htmlid
 */
M.block_ejsapp_file_browser.init_tree = function(Y, expand, htmlid) {
    Y.use('yui2-treeview', function(Y) {
        var tree = new Y.YUI2.widget.TreeView(htmlid);

        tree.subscribe("clickEvent", function(node, event) {
            // we want normal clicking which redirects to url
            return false;
        });

        if (expand) {
            tree.expandAll();
        }

        tree.render();
    });
};

/**
 * Defines the javascript code for manually refreshing the EJSApp File Browser block.    .
 * @param Y
 * @param url
 * @param htmlid
 */
M.block_ejsapp_file_browser.init_reload = function(Y, url, htmlid) {
    var handleSuccess = function(o) {
        var div = Y.YUI2.util.Dom.get(htmlid);
        div.innerHTML = o.responseText;
        M.block_ejsapp_file_browser.init_tree(Y, false, htmlid);
    };
    var handleFailure = function() {
        // Failure handler code.
    };
    var callback = {
        success: handleSuccess,
        failure: handleFailure
    };
    var refreshBut = Y.one("#refreshEJSAppFBBut");
    refreshBut.on("click", function() {
        Y.use('yui2-connection', function(Y) {
            Y.YUI2.util.Connect.asyncRequest('GET', url, callback);
        });
    });
};

/**
 * Defines the javascript code for automatically refreshing the EJSApp File Browser block.    .
 * @param Y
 * @param url
 * @param htmlid
 * @param frequency
 */
M.block_ejsapp_file_browser.init_auto_refresh = function(Y, url, htmlid, frequency) {
    if (frequency > 0) {
        var handleSuccess = function(o) {
            var div = Y.YUI2.util.Dom.get(htmlid);
            div.innerHTML = o.responseText;
            M.block_ejsapp_file_browser.init_tree(Y, false, htmlid);
        };
        var handleFailure = function() {
            // Failure handler code.
        };
        var callback = {
            success: handleSuccess,
            failure: handleFailure
        };
        setInterval(function() {autoRefresh()}, frequency);
        /**
         * Refreshes the tree automatically
         */
        function autoRefresh() {
            Y.use('yui2-connection', 'yui2-dom', function(Y) {
                Y.YUI2.util.Connect.asyncRequest('GET', url, callback);
            });
        }
    }
};