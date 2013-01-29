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

M.block_ejsapp_file_browser.init_tree = function(Y, expand_all, version, htmlid) {
    Y.use('yui2-treeview', function(Y) {
        if (version < 2012120300) { //Moodle 2.3 or lower
            var tree = new YAHOO.widget.TreeView(htmlid);
        }
        else {                      //Moodle 2.4 or higher
            var tree = new Y.YUI2.widget.TreeView(htmlid);
        }   

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
* Defines the javascript code for refreshing the EJSApp File Browser block.    .
*
*/
M.block_ejsapp_file_browser.init_reload = function(Y, url, version, htmlid){
    var handleSuccess = function(o) {
        div.innerHTML = o.responseText;
    };
    var handleFailure = function(o) {
        /*failure handler code*/
    }
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    };
    var url_complete = url + htmlid;
    var button = Y.one("#refreshEJSAppFBBut");
    button.on("click", function (e) { 
        if (version < 2012120300) { //Moodle 2.3 or lower
            div = YAHOO.util.Dom.get(htmlid);
        }
        else {                      //Moodle 2.4 or higher
            div = Y.YUI2.util.Dom.get(htmlid);
        } 
        Y.use('yui2-connection', function(Y) { //Moodle 2.3 or lower
            if (version < 2012120300) {
                YAHOO.util.Connect.asyncRequest('GET', url_complete, callback);
            }
            else {                             //Moodle 2.4 or higher
                Y.YUI2.util.Connect.asyncRequest('GET', url_complete, callback);
            }
        });       
    });
};