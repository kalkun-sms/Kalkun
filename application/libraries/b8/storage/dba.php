<?php

/* Copyright (C) 2006-2019 Tobias Leupold <tobias.leupold@gmx.de>

   This file is part of the b8 package

   This program is free software; you can redistribute it and/or modify it
   under the terms of the GNU Lesser General Public License as published by
   the Free Software Foundation in version 2.1 of the License.

   This program is distributed in the hope that it will be useful, but
   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
   License for more details.

   You should have received a copy of the GNU Lesser General Public License
   along with this program; if not, write to the Free Software Foundation,
   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
*/

namespace b8\storage;

/**
 * A Berkeley DB (DBA) storage backend
 *
 * @license LGPL 2.1
 * @package b8
 * @author Tobias Leupold <tobias.leupold@gmx.de>
 */

class dba extends storage_base
{

    private $db = null;

    protected function setup_backend(array $config)
    {
        if (! isset($config['resource'])
            || gettype($config['resource']) !== 'resource'
            || get_resource_type($config['resource']) !== 'dba') {

            throw new \Exception(dba::class . ": No valid DBA resource passed");
        }
        $this->db = $config['resource'];
    }

    protected function fetch_token_data(array $tokens)
    {
        $data = [];

        foreach ($tokens as $token) {
            // Try to the raw data in the format "count_ham count_spam"
            $count = dba_fetch($token, $this->db);

            if ($count !== false) {
                // Split the data by space characters
                $split_data = explode(' ', $count);

                // As an internal variable may have just one single value, we have to check for this
                $count_ham  = isset($split_data[0]) ? (int) $split_data[0] : null;
                $count_spam = isset($split_data[1]) ? (int) $split_data[1] : null;

                // Append the parsed data
                $data[$token] = [ \b8\b8::KEY_COUNT_HAM  => $count_ham,
                                  \b8\b8::KEY_COUNT_SPAM => $count_spam ];
            }
        }

        return $data;
    }

    private function assemble_count_value(array $count)
    {
        // Assemble the count data string
        $count_value = $count[\b8\b8::KEY_COUNT_HAM] . ' ' . $count[\b8\b8::KEY_COUNT_SPAM];
        // Remove whitespace from data of the internal variables
        return(rtrim($count_value));
    }

    protected function add_token(string $token, array $count)
    {
        return dba_insert($token, $this->assemble_count_value($count), $this->db);
    }

    protected function update_token(string $token, array $count)
    {
        return dba_replace($token, $this->assemble_count_value($count), $this->db);
    }

    protected function delete_token(string $token)
    {
        return dba_delete($token, $this->db);
    }

    protected function start_transaction()
    {
        return;
    }

    protected function finish_transaction()
    {
        return;
    }

}
