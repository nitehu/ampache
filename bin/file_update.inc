<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPLv3)
 * Copyright 2001 - 2019 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

define('NO_SESSION','1');
define('CLI', 1);

$path = dirname(__FILE__);
$prefix = realpath($path . '/../');
require_once $prefix . '/lib/init.php';

ob_end_flush();

$options = getopt("cagv");
$operation = false;

if (array_key_exists('c', $options)) {
    $operations_string .= "\n\t- " . T_('Cleaning deleted file');
    $operation = 'clean';
}
if (array_key_exists('a', $options)) {
    $operations_string .= "\n\t- " . T_('Adding a new file');
    $operation = 'add';
}
if (array_key_exists('g', $options)) {
    $operations_string .= "\n\t- " . T_('Gather art for this file');
    $operation = 'gather';
}
if (array_key_exists('v', $options)) {
    $operations_string .= "\n\t- " . T_('Verifying file details');
    $operation = 'verify';
}

if ($operation) {
    echo "\nperforming: " . $operation . "\n";
    foreach ($_SERVER['argv'] as $items) {
        $song_id = Song::find(array('file' => ob_html_strip($items)));
        if (($song_id) && $operation == 'clean') {
            $song = new Song($object);
            $song->remove_from_disk();
        }
        if (($song_id) && $operation == 'add') {
            $song = new Song($song_id);
        }
        if (($song_id) && $operation == 'gather') {
            $song = new Song($song_id);
            Catalog::gather_art_item('album', $song->album, true, true);
        }
        if (($song_id) && $operation == 'verify') {
            Catalog::update_single_item('song', $song_id, true);
        }
    }
} else {
    usage();
}
ob_start("ob_html_strip",'1024',true);

function ob_html_strip($string) {

    //$string = preg_replace("/update_txt\('.+'\);update_txt\('(.+)','.+'\);/","$1",$string);
    //$string = preg_replace("/update_.+/","",$string);
    $string = str_replace('<br />', "\n", $string);
    $string = strip_tags($string);
    $string = html_entity_decode($string);
    $string = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/","\n",$string);
    $string = trim($string);
    return $string;

} // ob_html_strip

function usage() {
    echo "\n";
    echo "----------------------------------------------------------\n\t\t";
    echo T_("Usage:");
    echo "\n\033[32m";
    echo T_("file_update.inc [-c|-v|-a|-g] [FILEPATH]") . "\033[0m (\033[31m!\033[0m)";
    echo "\033[0m\n";
    echo "----------------------------------------------------------";
    echo "\n";
    echo "-c\t";
    echo T_('Cleans Catalogs from orphaned entries');
    echo "\n-v\t";
    echo T_('Verifies Catalog entries and updates them if related files have new information');
    echo "\n-a\t";
    echo T_('Adds new media to Catalogs');
    echo "\n-g\t";
    echo T_('Gathers media Art');
    echo "\n";
    echo "----------------------------------------------------------";
    echo "\n";
}

