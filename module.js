// This file is part of the Moodle block "EJSApp File Browser"
//
// EJSApp File Browser is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either moodle_version 3 of the License, or
// (at your option) any later moodle_version.
//
// EJSApp File Browser is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp File Browser has been developed by:
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain

/** 
 * Javascript code
 * 
 * @package    block
 * @subpackage ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  
 */

M.block_ejsapp_file_browser = {};

M.block_ejsapp_file_browser.init_tree = function(Y, expand_all, moodle_version, htmlid) {
    Y.use('yui2-treeview', function(Y) {
        if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
            YAHOO = Y.YUI2;
        } 
        var tree = new YAHOO.widget.TreeView(htmlid);

        tree.subscribe("clickEvent", function(node, event) {
            // we want normal clicking which redirects to url
            return false;
        });

        if (expand_all) {
            tree.expandAll();
        }

        tree.render();
    });
};

/**
* Defines the javascript code for manually refreshing the EJSApp File Browser block.    .
*
*/
M.block_ejsapp_file_browser.init_reload = function(Y, url, moodle_version, htmlid){
    var handleSuccess = function(o) {
        div.innerHTML = o.responseText;
        M.block_ejsapp_file_browser.init_tree(Y, false, moodle_version, htmlid);
    };
    var handleFailure = function(o) {
        /*failure handler code*/
    };
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    };
    if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
        YAHOO = Y.YUI2;
    }
    var refreshBut = Y.one("#refreshEJSAppFBBut");
    refreshBut.on("click", function (e) {
        div = YAHOO.util.Dom.get(htmlid);
        Y.use('yui2-connection', function(Y) {
            YAHOO.util.Connect.asyncRequest('GET', url, callback);
        });       
    });
};

/**
 * Defines the javascript code for automatically refreshing the EJSApp File Browser block.    .
 *
 */
M.block_ejsapp_file_browser.init_autoreload = function(Y, url, moodle_version, htmlid){
    var handleSuccess = function(o) {
        div.innerHTML = o.responseText;
        M.block_ejsapp_file_browser.init_tree(Y, false, moodle_version, htmlid);
    };
    var handleFailure = function(o) {
        /*failure handler code*/
    };
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    };
    if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
        YAHOO = Y.YUI2;
    }
    setInterval(function() {autoRefresh()},4000);
    function autoRefresh() {
        div = YAHOO.util.Dom.get(htmlid);
        Y.use('yui2-connection', function(Y) {
            YAHOO.util.Connect.asyncRequest('GET', url, callback);
        });
    }
};