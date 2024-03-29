<?php
/**
 * @Author: jack ann
 * @Date:   2019-08-13 16:18:22
 * @Last Modified by:   angaozhao
 * @Last Modified time: 2019-08-15 10:19:13
 */
namespace dbdictionary;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Db;

/**
 *
 */
class Dictionary
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
    /**
     * [outputHTML 输出html]
     * @AuthorHTL
     * @DateTime  2019-08-14T10:47:51+0800
     * @return    [type]                   [description]
     */
    public function outputHTML()
    {
        $tables = $this->selectTables();
        $html   = '';
        $title  = '数据字典';
        foreach ($tables as $v) {
            //$html .= '<p><h2>'. $v['TABLE_COMMENT'] . ' </h2>';
            $html .= '<table  border="1" cellspacing="0" cellpadding="0" align="center">';
            $html .= '<caption>' . $v['TABLE_NAME'] . '  ' . $v['TABLE_COMMENT'] . '</caption>';
            $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th>
            <th>允许非空</th>
            <th>自动递增</th><th>索引</th><th>备注</th></tr>';
            $html .= '';
            foreach ($v['COLUMN'] as $f) {
                $html .= '<tr><td class="c1">' . $f['COLUMN_NAME'] . '</td>';
                $html .= '<td class="c2">' . $f['COLUMN_TYPE'] . '</td>';
                $html .= '<td class="c3"> ' . $f['COLUMN_DEFAULT'] . '</td>';
                $html .= '<td class="c4"> ' . $f['IS_NULLABLE'] . '</td>';
                $html .= '<td class="c5">' . ($f['EXTRA'] == 'auto_increment' ? '是' : ' ') . '</td>';
                $html .= '<td class="c6"> ' . $f['COLUMN_KEY'] . '</td>';
                $html .= '<td class="c7"> ' . $f['COLUMN_COMMENT'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></p>';
        }
        //输出
        $file = '<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>' . $title . '</title>
        <style>
        body,td,th {font-family:"宋体"; font-size:12px;padding:0 0 0 5px;}
        table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;}
        table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }
        table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;}
        table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;}
        .c1{ width: 120px;}
        .c2{ width: 120px;}
        .c3{ width: 70px;}
        .c4{ width: 80px;}
        .c5{ width: 80px;}
        .c6{ width: 70px;}
        .c7{ width: 270px;}
        </style>
        </head>
        <body>';
        $file .= '<h1 style="text-align:center;">' . $title . '</h1>';
        $file .= $html;
        $file .= '</body></html>';
        echo $file;
    }
    /**
     * [outputExcel 输出Excel]
     * @AuthorHTL
     * @DateTime  2019-08-14T11:16:24+0800
     * @return    [type]                   [description]
     */
    public function outputExcel()
    {
        $tables = $this->selectTables();
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        // 设置标题
        $spreadsheet->getActiveSheet()->setTitle('数据字典');
        // Add title
        $spreadsheet->setActiveSheetIndex(0);
        $table_offset = 1;
        foreach ($tables as $table) {
            $table_name_raw = $table_offset;
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadsheet->getActiveSheet()->getStyle("A" . $table_name_raw . ":B" . $table_name_raw)->getFont()->setBold(true)->setSize(14);
            $spreadsheet->getActiveSheet()->setCellValue('A' . $table_name_raw, $table['TABLE_NAME'])
                ->setCellValue('B' . $table_name_raw, $table['TABLE_COMMENT']);
            $column_name_raw = $table_name_raw + 1;
            $spreadsheet->getActiveSheet()->setCellValue('A' . $column_name_raw, '字段名');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $column_name_raw, '数据类型');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $column_name_raw, '默认值');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $column_name_raw, '允许非空');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $column_name_raw, '自动递增');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $column_name_raw, '索引');
            $spreadsheet->getActiveSheet()->setCellValue('G' . $column_name_raw, '备注');
            $column_raw = $column_name_raw + 1;
            foreach ($table['COLUMN'] as $column) {
                // Add data
                $spreadsheet->getActiveSheet()
                    ->setCellValue('A' . $column_raw, $column['COLUMN_NAME'])
                    ->setCellValue('B' . $column_raw, $column['COLUMN_TYPE'])
                    ->setCellValue('C' . $column_raw, $column['COLUMN_DEFAULT'])
                    ->setCellValue('D' . $column_raw, $column['IS_NULLABLE'])
                    ->setCellValue('E' . $column_raw, $column['EXTRA'])
                    ->setCellValue('F' . $column_raw, $column['COLUMN_KEY'])
                    ->setCellValue('G' . $column_raw, $column['COLUMN_COMMENT']);
                $column_raw++;
            }
            $table_offset = $column_raw + 1;
        }
        // $spreadsheet->setActiveSheetIndex(0);
        // $class = "\PhpOffice\PhpSpreadsheet\Writer\Xls";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        //禁止缓存
        header('Cache-Control: max-age=0');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition: attachment;filename="数据字典.xls"'); //要生成的表名
        header("Content-Transfer-Encoding:binary");
        $writer->save('php://output'); 
    }
}
