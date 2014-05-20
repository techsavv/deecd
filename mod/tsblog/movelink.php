<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * This page allows a user to change a links position in the list
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @package tsblog
 */

require_once("../../config.php");
require_once("locallib.php");

$link = required_param('link', PARAM_INT);
$down = required_param('down', PARAM_INT);
$returnurl = required_param('returnurl', PARAM_RAW);

if (!$link = $DB->get_record('tsblog_links', array('id'=>$link))) {
    print_error('invalidlink', 'tsblog');
}
if (!$tsblog = $DB->get_record("tsblog", array("id"=>$link->tsblogid))) {
    print_error('invalidblog', 'tsblog');
}
if (!$cm = get_coursemodule_from_instance('tsblog', $link->tsblogid)) {
    print_error('invalidcoursemodule');
}

require_sesskey();

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

$tsbloginstance = $link->tsbloginstancesid ? $DB->get_record('tsblog_instances', array('id'=>$link->tsbloginstancesid)) : null;
tsblog_require_userblog_permission('mod/tsblog:managelinks', $tsblog, $tsbloginstance, $context);

$params = array();
if ($tsblog->global) {
    $where = "tsbloginstancesid = ? ";
    $params[] = $link->tsbloginstancesid;
} else {
    $where = "tsblogid = ? ";
    $params[] = $link->tsblogid;
}

// Get the max sort order
$maxsortorder = $DB->get_field_sql("SELECT MAX(sortorder) FROM {tsblog_links} WHERE $where", $params);

if ($down == 1) { // Move link down
    if ($link->sortorder != $maxsortorder) {
        $sql = "UPDATE {tsblog_links} SET sortorder = ?
                WHERE $where AND sortorder = ?";

        $DB->execute($sql, array_merge(array($link->sortorder), $params, array($link->sortorder+1)));

        $sql = "UPDATE {tsblog_links} SET sortorder = ?
                WHERE id = ? ";

        $DB->execute($sql, array($link->sortorder+1, $link->id));
    }
} else { // Move link up
    if ($link->sortorder != 1) {
        $sql = "UPDATE {tsblog_links} SET sortorder = ?
                WHERE $where AND sortorder = ?";

        $DB->execute($sql, array_merge(array($link->sortorder), $params, array($link->sortorder-1)));

        $sql = "UPDATE {tsblog_links} SET sortorder = ?
                WHERE id = ? ";

        $DB->execute($sql, array($link->sortorder-1, $link->id));
    }
}

redirect($returnurl);
