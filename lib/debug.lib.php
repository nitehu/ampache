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
declare(strict_types=1);

/*
 * check_php
 * check for required modules
 * @return bool
 */
function check_php(): bool
{
    if (
        check_php_version() &&
        check_php_hash() &&
        check_php_hash_algo() &&
        check_php_pdo() &&
        check_php_pdo_mysql() &&
        check_php_session() &&
        check_php_json() &&
        check_php_safemode()
    ) {
        return true;
    }

    return false;
}

/*
 * check_php_version
 * check for required php version
 * @return bool
 */
function check_php_version(): bool
{
    if (floatval(phpversion()) < 7.1) {
        return false;
    }

    return true;
}

/*
 * check_php_hash
 * check for required function exists
 * @return bool
 */
function check_php_hash(): bool
{
    return function_exists('hash_algos');
}

/*
 * check_php_hash_algo
 * check for required function exists
 * @return bool
 */
function check_php_hash_algo(): bool
{
    return function_exists('hash_algos') ? in_array('sha256', hash_algos()) : false;
}

/*
 * check_php_json
 * check for required function exists
 * @return bool
 */
function check_php_json(): bool
{
    return function_exists('json_encode');
}

/*
 * check_php_curl
 * check for required function exists
 * @return bool
 */
function check_php_curl(): bool
{
    return function_exists('curl_version');
}

/*
 * check_php_session
 * check for required function exists
 * @return bool
 */
function check_php_session(): bool
{
    return function_exists('session_set_save_handler');
}

/*
 * check_php_pdo
 * check for required function exists
 * @return bool
 */
function check_php_pdo(): bool
{
    return class_exists('PDO');
}

/*
 * check_php_pdo_mysql
 * check for required function exists
 * @return bool
 */
function check_php_pdo_mysql(): bool
{
    return class_exists('PDO') ? in_array('mysql', PDO::getAvailableDrivers()) : false;
}

/*
 * check_mbstring_func_overload
 * check for required function exists
 * @return bool
 */
function check_mbstring_func_overload(): bool
{
    if (ini_get('mbstring.func_overload') > 0) {
        return false;
    }

    return true;
}

/**
 * check_config_values
 * checks to make sure that they have at least set the needed variables
 * @param array $conf
 * @return bool
 */
function check_config_values($conf): bool
{
    if (!$conf['database_hostname']) {
        return false;
    }
    if (!$conf['database_name']) {
        return false;
    }
    if (!$conf['database_username']) {
        return false;
    }
    /* Don't check for password to support mysql socket auth
     * if (!$conf['database_password']) {
        return false;
    }*/
    if (!$conf['session_length']) {
        return false;
    }
    if (!$conf['session_name']) {
        return false;
    }
    if (!isset($conf['session_cookielife'])) {
        return false;
    }
    if (!isset($conf['session_cookiesecure'])) {
        return false;
    }
    if (isset($conf['debug'])) {
        if (!isset($conf['log_path'])) {
            return false;
        }
    }

    return true;
} // check_config_values

/**
 * check_php_memory
 * This checks to make sure that the php memory limit is withing the
 * recommended range, this doesn't take into account the size of your
 * catalog.
 * @return bool
 */
function check_php_memory(): bool
{
    $current_memory = ini_get('memory_limit');
    $current_memory = substr($current_memory, 0, strlen((string) $current_memory) - 1);

    if ((int) ($current_memory) < 48) {
        return false;
    }

    return true;
} // check_php_memory

/**
 * check_php_timelimit
 * This checks to make sure that the php timelimit is set to some
 * semi-sane limit, IE greater then 60 seconds
 * @return bool
 */
function check_php_timelimit(): bool
{
    $current = (int) (ini_get('max_execution_time'));

    return ($current >= 60 || $current == 0);
} // check_php_timelimit

/**
 * check_safe_mode
 * Checks to make sure we aren't in safe mode
 * @return bool
 */
function check_php_safemode(): bool
{
    if (ini_get('safe_mode')) {
        return false;
    }

    return true;
}

/**
 * check_override_memory
 * This checks to see if we can manually override the memory limit
 * @return bool
 */
function check_override_memory(): bool
{
    /* Check memory */
    $current_memory = ini_get('memory_limit');
    $current_memory = substr($current_memory, 0, strlen((string) $current_memory) - 1);
    $new_limit      = ($current_memory + 16) . "M";

    /* Bump it by 16 megs (for getid3)*/
    if (!ini_set('memory_limit', $new_limit)) {
        return false;
    }

    // Make sure it actually worked
    $new_memory = ini_get('memory_limit');

    if ($new_limit != $new_memory) {
        return false;
    }

    return true;
}

/**
 * check_override_exec_time
 * This checks to see if we can manually override the max execution time
 * @return bool
 */
function check_override_exec_time(): bool
{
    $current = ini_get('max_execution_time');
    set_time_limit($current + 60);

    if ($current == ini_get('max_execution_time')) {
        return false;
    }

    return true;
}

/**
 * check_upload_size
 * This checks to see if max upload size is not too small
 */
function check_upload_size(): bool
{
    $upload_max = return_bytes(ini_get('upload_max_filesize'));
    $post_max   = return_bytes(ini_get('post_max_size'));
    $mini       = 20971520; // 20M

    return (($upload_max >= $mini || $upload_max <= 0) && ($post_max >= $mini || $post_max <= 0));
}

function check_php_int_size(): bool
{
    return (PHP_INT_SIZE > 4);
}

function check_php_zlib(): bool
{
    return function_exists('gzcompress');
}

function check_php_simplexml(): bool
{
    return function_exists('simplexml_load_string');
}

function check_php_gd(): bool
{
    return (extension_loaded('gd') || extension_loaded('gd2'));
}

/**
 * @param string $val
 */
function return_bytes($val): string
{
    $val  = trim((string) $val);
    $last = strtolower((string) $val[strlen((string) $val) - 1]);
    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
            // intentional fall through
        case 'm':
            $val *= 1024;
            // intentional fall through
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

function check_dependencies_folder(): bool
{
    return file_exists(AmpConfig::get('prefix') . '/lib/vendor');
}

/**
 * check_config_writable
 * This checks whether we can write the config file
 * @return bool
 */
function check_config_writable(): bool
{
    // file eixsts && is writable, or dir is writable
    return ((file_exists(AmpConfig::get('prefix') . '/config/ampache.cfg.php') && is_writable(AmpConfig::get('prefix') . '/config/ampache.cfg.php'))
        || (!file_exists(AmpConfig::get('prefix') . '/config/ampache.cfg.php') && is_writeable(AmpConfig::get('prefix') . '/config/')));
}

function check_htaccess_channel_writable(): bool
{
    return ((file_exists(AmpConfig::get('prefix') . '/channel/.htaccess') && is_writable(AmpConfig::get('prefix') . '/channel/.htaccess'))
        || (!file_exists(AmpConfig::get('prefix') . '/channel/.htaccess') && is_writeable(AmpConfig::get('prefix') . '/channel/')));
}

function check_htaccess_rest_writable(): bool
{
    return ((file_exists(AmpConfig::get('prefix') . '/rest/.htaccess') && is_writable(AmpConfig::get('prefix') . '/rest/.htaccess'))
        || (!file_exists(AmpConfig::get('prefix') . '/rest/.htaccess') && is_writeable(AmpConfig::get('prefix') . '/rest/')));
}

function check_htaccess_play_writable(): bool
{
    return ((file_exists(AmpConfig::get('prefix') . '/play/.htaccess') && is_writable(AmpConfig::get('prefix') . '/play/.htaccess'))
        || (!file_exists(AmpConfig::get('prefix') . '/play/.htaccess') && is_writeable(AmpConfig::get('prefix') . '/play/')));
}

/**
 * debug_result
 * Convenience function to format the output.
 * @param string|boolean $status
 */
function debug_result($status = false, $value = null, $comment = ''): string
{
    $class = $status ? 'success' : 'danger';

    if (!$value) {
        $value = $status ? T_('OK') : T_('Error');
    }

    return '<button type="button" class="btn btn-' . $class . '">' . scrub_out($value) .
        '</span> <em>' . $comment . '</em></button>';
}

/**
 * debug_wresult
 *
 * Convenience function to format the output.
 */
function debug_wresult($status = false, $value = null, $comment = ''): string
{
    $class = $status ? 'success' : 'warning';

    if (!$value) {
        $value = $status ? T_('OK') : T_('WARNING');
    }

    return '<button type="button" class="btn btn-' . $class . '">' . scrub_out($value) .
        '</span> <em>' . $comment . '</em></button>';
}
