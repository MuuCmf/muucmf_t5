<?php

namespace addons\database\library;

use Exception;
use PDO;
use ZipArchive;

class Backup
{

    private $host = '';
    private $user = '';
    private $name = '';
    private $pass = '';
    private $port = '';
    private $tables = ['*'];
    private $ignoreTables = [];
    private $db;
    private $ds = "\n";

    public function __construct($host = NULL, $user = NULL, $name = NULL, $pass = NULL, $port = 3306)
    {
        if ($host !== NULL) {
            $this->host = $host;
            $this->name = $name;
            $this->port = $port;
            $this->pass = $pass;
            $this->user = $user;
        }
        $this->db = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->name . '; port=' . $port, $this->user, $this->pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        $this->db->exec('SET NAMES "utf8"');
    }

    /**
     * 设置备份表
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        if ($table) {
            $this->tables = is_array($table) ? $table : explode(',', $table);
        }
        return $this;
    }

    /**
     * 设置忽略备份的表
     * @param $table
     * @return $this
     */
    public function setIgnoreTable($table)
    {
        if ($table) {
            $this->ignoreTables = is_array($table) ? $table : explode(',', $table);
        }
        return $this;
    }

    public function backup($backUpdir = 'download/')
    {
        $sql = $this->_init();
        $zip = new ZipArchive();
        $date = date('YmdHis');
        if (!is_dir($backUpdir)) {
            @mkdir($backUpdir, 0755);
        }
        $name = "backup-{$this->name}-{$date}";
        $filename = $backUpdir . $name . ".zip";

        if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
            throw new Exception("Could not open <$filename>\n");
        }
        $zip->addFromString($name . ".sql", $sql);
        $zip->close();
    }

    private function _init()
    {
        # COUNT
        $ct = 0;
        # CONTENT
        $sqldump = '';
        # COPYRIGHT & OPTIONS
        $sqldump .= "-- SQL Dump by Erik Edgren\n";
        $sqldump .= "-- version 1.0\n";
        $sqldump .= "-- http://erik-edgren.nu/ (swedish blog)\n";
        $sqldump .= "--\n";
        $sqldump .= "-- SQL Dump created: " . date('F jS, Y \@ g:i a') . "\n\n";
        $sqldump .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";";
        $sqldump .= "\n\n\n\n-- --------------------------------------------------------\n\n\n\n";
        $tables = $this->db->query("SHOW TABLES");
        # LOOP: Get the tables
        foreach ($tables AS $table) {
            # COUNT
            $ct++;
            /** ** ** ** ** **/
            # DATABASE: Count the rows in each tables
            $count_rows = $this->db->prepare("SELECT * FROM " . $table[0]);
            $count_rows->execute();
            $c_rows = $count_rows->columnCount();
            # DATABASE: Count the columns in each tables
            $count_columns = $this->db->prepare("SELECT COUNT(*) FROM " . $table[0]);
            $count_columns->execute();
            $c_columns = $count_columns->fetchColumn();
            /** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** ** **/
            # MYSQL DUMP: Remove tables if they exists
            $sqldump .= "--\n";
            $sqldump .= "-- Remove the table if it exists\n";
            $sqldump .= "--\n\n";
            $sqldump .= "DROP TABLE IF EXISTS `" . $table[0] . "`;\n\n\n";
            /** ** ** ** ** **/
            # MYSQL DUMP: Create table if they do not exists
            $sqldump .= "--\n";
            $sqldump .= "-- Create the table if it not exists\n";
            $sqldump .= "--\n\n";
            # LOOP: Get the fields for the table
            foreach ($this->db->query("SHOW CREATE TABLE " . $table[0]) AS $field) {
                $sqldump .= str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $field['Create Table']);
            }
            # MYSQL DUMP: New rows
            $sqldump .= ";\n\n\n";
            /** ** ** ** ** **/
            # CHECK: There are one or more columns
            if ($c_columns != 0) {
                # MYSQL DUMP: List the data for each table
                $sqldump .= "--\n";
                $sqldump .= "-- List the data for the table\n";
                $sqldump .= "--\n\n";
                # MYSQL DUMP: Insert into each table
                $sqldump .= "INSERT INTO `" . $table[0] . "` (";
                # ARRAY
                $rows = Array();
                # LOOP: Get the tables
                foreach ($this->db->query("DESCRIBE " . $table[0]) AS $row) {
                    $rows[] = "`" . $row[0] . "`";
                }
                $sqldump .= implode(', ', $rows);
                $sqldump .= ") VALUES\n";
                # COUNT
                $c = 0;
                # LOOP: Get the tables
                foreach ($this->db->query("SELECT * FROM " . $table[0]) AS $data) {
                    # COUNT
                    $c++;
                    /** ** ** ** ** **/
                    $sqldump .= "(";
                    # ARRAY
                    $cdata = Array();
                    # LOOP
                    for ($i = 0; $i < $c_rows; $i++) {
                        $new_lines = preg_replace('/\s\s+/', '\r\n\r\n', addslashes($data[$i]));
                        $cdata[] = "'" . $new_lines . "'";
                    }
                    $sqldump .= implode(', ', $cdata);
                    $sqldump .= ")";
                    $sqldump .= ($c % 600 != 0 ? ($c_columns != $c ? ',' : ';') : '');
                    # CHECK
                    if ($c % 600 == 0) {
                        $sqldump .= ";\n\n";
                    } else {
                        $sqldump .= "\n";
                    }
                    # CHECK
                    if ($c % 600 == 0) {
                        $sqldump .= "INSERT INTO " . $table[0] . "(";
                        # ARRAY
                        $rows = Array();
                        # LOOP: Get the tables
                        foreach ($this->db->query("DESCRIBE " . $table[0]) AS $row) {
                            $rows[] = "`" . $row[0] . "`";
                        }
                        $sqldump .= implode(', ', $rows);
                        $sqldump .= ") VALUES\n";
                    }
                }
            }
        }
        return $sqldump;

    }

}