<?php
/**
 * @Author: jack ann
 * @Date:   2019-08-13 16:18:22
 * @Last Modified by:   sogubaby
 * @Last Modified time: 2019-08-13 17:48:04
 */
namespace dbdictionary;

use think\Db;

/**
 *
 */
class Database
{
    public function selectTables()
    {
        $database = config('database.database');
        $tables   = Db::query('show tables');
        //循环取得所有表的备注及表中列消息
        foreach ($tables as $k => $v) {
            $table_name               = array_values($v)[0];
            $tables[$k]['TABLE_NAME'] = $table_name;
            $sql                      = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.TABLES ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$table_name}'  AND table_schema = '{$database}'";
            $table_result = Db::query($sql);
            foreach ($table_result as $value) {
                $tables[$k]['TABLE_COMMENT'] = $value['TABLE_COMMENT'];
            }
            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$table_name}' AND table_schema = '{$database}'";
            $fields       = array();
            $field_result = Db::query($sql);
            foreach ($field_result as $value) {
                $fields[] = $value;
            }
            $tables[$k]['COLUMN'] = $fields;
        }
        return $tables;
    }
}
