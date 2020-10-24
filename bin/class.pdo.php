<?php

/**
 * class PDO สำหรับ GCMS
 * สงวนลิขสิทธ์ ห้ามซื้อขาย ให้นำไปใช้ได้ฟรีเท่านั้น
 * @package class.pdo.php
 * @author กรกฎ วิริยะ (http://www.goragod.com)
 */
class sql
{
    /**
     * @var string
     */
    protected $vesion = "8-10-56";
    /**
     * @var int
     */
    protected $time = 0;
    /**
     * PDO instance
     *
     * @var PDO
     */
    protected $connection = false;
    /**
     * set debug (1 = debug ใช้งานขณะ ทดสอบเท่านั้น, 0 = no debug)
     *
     * @var string
     */
    public $debug = 0;

    /**
     * __construct($server, $username, $password, $dbname, $driver = DATABASE_DRIVER)
     * inintial database class
     * สำเร็จคืนค่า true
     * ไม่สำเร็จคืนค่า false
     *
     * @param string $server Database server
     * @param string $username Database username
     * @param string $password Database password
     * @param string $dbname Database name
     * @param string $driver [optional] Database engine default mysql
     *
     * @return boolean
     */
    public function __construct($server, $username, $password, $dbname, $driver = DATABASE_DRIVER)
    {
        $options = array();
        $options[PDO::ATTR_PERSISTENT] = true;
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        if ($driver == 'mysql') {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
        }
        try {
            $sql = "$driver:host=$server".($dbname == '' ? '' : ";dbname=$dbname");
            $this->connection = new PDO($sql, $username, $password, $options);

            return true;
        } catch (PDOException $e) {
            $this->debug('Error : '.$e->getMessage());

            return false;
        }
    }

    /**
     * __destruct()
     * จบ class
     * สำเร็จคืนค่า true
     * ไม่สำเร็จคืนค่า false
     *
     * @return int
     */
    public function __destruct()
    {
        $this->connection = null;
    }

    /**
     * connection()
     * อ่านค่า resource connection
     *
     * @return int
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * close()
     * ยกเลิก mysql
     * สำเร็จคืนค่า true
     * ไม่สำเร็จคืนค่า false
     *
     * @return boolean
     */
    public function close()
    {
        $this->connection = null;
    }

    /**
     * Version()
     * อ่านเวอร์ชั่นของ class
     *
     * @return string
     */
    public function Version()
    {
        return $this->vesion;
    }

    /**
     * tableExists($table)
     * ตรวจสอบว่ามีตาราง $table อยู่หรือไม่
     * คืนค่า true ถ้ามี
     * ไม่มีคืนค่า false
     *
     * @return boolean
     */
    public function tableExists($table)
    {
        try {
            $query = $this->connection->query("SELECT * FROM `$table` LIMIT 1");
            $this->time++;

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * fieldExists($table, $field)
     * ตรวจสอบฟิลด์ในตาราง
     * คืนค่า true หากมีฟิลด์นี้อยู่
     * ไม่พบคืนค่า false
     *
     * @return boolean
     */
    public function fieldExists($table, $field)
    {
        try {
            $sql = "SHOW COLUMNS FROM `$table`";
            $query = $this->connection->query($sql);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $this->time++;
            $field = strtolower($field);
            foreach ($result as $row) {
                if (strtolower($row['Field']) == $field) {
                    return true;
                }
            }
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * basicSearch($table, $fields, $values)
     * ค้นหา $values ที่ $fields บนตาราง $table
     * ไม่พบคืนค่า false
     * พบคืนค่ารายการที่พบเพียงรายการเดียว
     *
     * @param string $table ชื่อตาราง
     * @param array or string $fields ชื่อฟิลด์
     * @param array or string $values ข้อความค้นหาในฟิลด์ที่กำหนด ประเภทเดียวกันกับ $fields
     *
     * @return array
     * @return boolean
     */
    public function basicSearch($table, $fields, $values)
    {
        try {
            $keys = array();
            $datas = array();
            if (is_array($fields)) {
                foreach ($fields as $i => $field) {
                    $keys[] = "`$field`=:$field";
                    if (is_array($values)) {
                        $datas[":$field"] = $values[$i];
                    } else {
                        $datas[":$field"] = $values;
                    }
                }
            } else {
                if (is_array($values)) {
                    $ks = array();
                    foreach ($values as $value) {
                        $ks[] = '?';
                        $datas[] = $value;
                    }
                    $keys[] = "`$fields` IN (".implode(',', $ks).")";
                } else {
                    $keys[] = "`$fields`=:$fields";
                    $datas[":$fields"] = $values;
                }
            }
            $sql = "SELECT * FROM `$table` WHERE ".implode(' OR ', $keys)." LIMIT 1";
            $query = $this->connection->prepare($sql);
            $query->execute($datas);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $this->time++;

            return sizeof($result) == 0 ? false : $result[0];
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * getRec($table, $id)
     * อ่านค่า record ที่ id=$id
     * ไม่พบคืนค่า false
     * พบคืนค่ารายการที่พบเพียงรายการเดียว
     *
     * @param string $table ชื่อตาราง
     * @param int $id id ที่ต้องการอ่าน
     *
     * @return array
     * @return boolean
     */
    public function getRec($table, $id)
    {
        try {
            $sql = "SELECT * FROM `$table` WHERE `id`=".(int) $id." LIMIT 1";
            $query = $this->connection->query($sql);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $this->time++;

            return sizeof($result) == 0 ? false : $result[0];
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * add($table, $recArr)
     * เพิ่มข้อมูลลงบน $table
     * สำเร็จ คืนค่า id ที่เพิ่ม
     * ไม่สำเร็จ คืนค่า false
     *
     * @param string $table ชื่อตาราง
     * @param array $recArr ข้อมูลที่ต้องการบันทึก
     *
     * @return int
     * @return boolean
     */
    public function add($table, $recArr)
    {
        try {
            $keys = array();
            $values = array();
            foreach ($recArr as $key => $value) {
                $keys[] = $key;
                $values[":$key"] = $value;
            }
            $sql = "INSERT INTO `".$table.'` (`'.implode('`,`', $keys);
            $sql .= "`) VALUES (:".implode(",:", $keys).");";
            $query = $this->connection->prepare($sql);
            $query->execute($values);
            $this->time++;

            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * edit($table, $id, $recArr)
     * แก้ไขข้อมูล
     *
     * @param $table string ชื่อตาราง
     * @param $idArr int=id ที่ต้องการแก้ไข, array=ค้นหารายการที่ต้องการ
     * @param $recArr array ข้อมูลที่ต้องการบันทึก
     *
     * @return boolean true success else false
     */
    public function edit($table, $idArr, $recArr)
    {
        try {
            $keys = array();
            $values = array();
            foreach ($recArr as $key => $value) {
                $keys[] = "`$key`=:$key";
                $values[":$key"] = $value;
            }
            if (is_array($idArr)) {
                $datas = array();
                foreach ($idArr as $key => $value) {
                    $datas[] = "`$key`=:$key";
                    $values[":$key"] = $value;
                }
                $where = sizeof($datas) == 0 ? '' : implode(' AND ', $datas);
            } else {
                $id = (int) $idArr;
                $where = $id == 0 ? '' : '`id`=:id';
                $values[':id'] = $id;
            }
            if ($where == '' || sizeof($keys) == 0) {
                return false;
            } else {
                $sql = "UPDATE `$table` SET ".implode(",", $keys)." WHERE $where LIMIT 1";
                $query = $this->connection->prepare($sql);
                $query->execute($values);
                $this->time++;

                return true;
            }
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * delete($table, $id)
     * ลบ เร็คคอร์ดรายการที่ $id
     * สำเร็จ คืนค่าว่าง
     * ไม่สำเร็จคืนค่าข้อความผิดพลาด
     *
     * @param string $table ชื่อตาราง
     * @param int $id id ที่ต้องการลบ
     *
     * @return string
     */
    public function delete($table, $id)
    {
        try {
            $sql = "DELETE FROM `$table` WHERE `id`=".(int) $id." LIMIT 1;";
            $result = $this->connection->query($sql);
            $this->time++;

            return '';
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * query($sql)
     * query ข้อมูล แบบไม่ต้องการผลตอบกลับ
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จ คืนค่า false
     *
     * @param string $sql query string
     *
     * @return boolean
     */
    public function query($sql)
    {
        try {
            $result = $this->connection->query($sql);
            $this->time++;

            return true;
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * customQuery($sql)
     * query ข้อมูล ด้วย sql ที่กำหนดเอง
     * คืนค่าผลการทำงานเป็น record ของข้อมูลทั้งหมดที่ตรงตามเงื่อนไข
     * ไม่พบข้อมูลคืนค่าเป็น array ว่างๆ
     *
     * @param string $sql query string
     *
     * @return array
     */
    public function customQuery($sql)
    {
        try {
            $query = $this->connection->query($sql);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $this->time++;

            return $result;
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return array();
        }
    }

    /**
     * lastId($table)
     * อ่าน id ล่าสุดของตาราง
     *
     * @param string $table ชื่อตาราง
     *
     * @return int
     */
    public function lastId($table)
    {
        try {
            $sql = "SHOW TABLE STATUS LIKE '$table'";
            $query = $this->connection->query($sql);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $this->time++;

            return (int) $result[0]['Auto_increment'];
        } catch (PDOException $e) {
            $this->debug("Error in $sql Message : ".$e->getMessage());

            return false;
        }
    }

    /**
     * unlock()
     * ยกเลิกการล๊อคตารางทั้งหมดที่ล๊อคอยู่
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จ คืนค่า false
     *
     * @return boolean
     */
    public function unlock()
    {
        return $this->query('UNLOCK TABLES');
    }

    /**
     * lock($table)
     * ล๊อคตาราง
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จ คืนค่า false
     *
     * @param string $table ชื่อตาราง
     *
     * @return boolean
     */
    public function lock($table)
    {
        return $this->query("LOCK TABLES $table");
    }

    /**
     * setReadLock($table)
     * ล๊อคตารางสำหรับอ่าน
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จ คืนค่า false
     *
     * @param string $table ชื่อตาราง
     *
     * @return boolean
     */
    public function setReadLock($table)
    {
        return $this->lock("`$table` READ");
    }

    /**
     * setWriteLock($table)
     * ล๊อคตารางสำหรับเขียน
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จ คืนค่า false
     *
     * @param string $table ชื่อตาราง
     *
     * @return boolean
     */
    public function setWriteLock($table)
    {
        return $this->lock("`$table` WRITE");
    }

    /**
     * sql_clean($value)
     * ตรวจสอบและลบข้อความที่ไม่ต้องการของ mysql
     *
     * @param string $value ข้อความ
     *
     * @return string
     */
    public function sql_clean($value)
    {
        if (function_exists("get_magic_quotes_gpc") || ini_get('magic_quotes_sybase')) {
            $value = stripslashes($value);
        }

        return $value;
    }

    /**
     * sql_quote($value)
     * เติม string ด้วย /
     *
     * @param string $value ข้อความ
     *
     * @return string
     */
    public function sql_quote($value)
    {
        return $this->sql_clean(str_replace('\\\\', '&#92;', $value));
    }

    /**
     * sql_trim($value)
     * ลบช่องว่างหัวท้ายออกจากข้อความ และ เติม string ด้วย /
     *
     * @param string $value ข้อความ
     *
     * @return string
     */
    public function sql_trim($value)
    {
        return $this->sql_quote(trim($value));
    }

    /**
     * sql_trim_str($value)
     * ลบช่องว่างหัวท้ายออกจากข้อความ และ เติม string ด้วย / และ แปลงอักขระ HTML
     *
     * @param string $value ข้อความ
     *
     * @return string
     */
    public function sql_trim_str($value)
    {
        return $this->sql_quote(htmlspecialchars(trim($value)));
    }

    /**
     * sql_str($value)
     * เติม string ด้วย / และ แปลงอักขระ HTML
     *
     * @param string $value ข้อความ
     *
     * @return string
     */
    public function sql_str($value)
    {
        return $this->sql_quote(htmlspecialchars($value));
    }

    /**
     * sql_mktimetodate($mktime)
     * แปลงวันที่ ในรูป mktime เป้นวันที่ของ mysql ในรูป Y-m-d
     *
     * @param int $mktime วันที่ในรูป mktime
     *
     * @return string
     */
    public function sql_mktimetodate($mktime)
    {
        return date("Y-m-d", $mktime);
    }

    /**
     * sql_mktimetodatetime($mktime)
     * แปลงวันที่ ในรูป mktime เป้นวันที่และเวลาของ mysql เช่น Y-m-d H:i:s
     *
     * @param int $mktime วันที่ในรูป mktime
     *
     * @return string
     */
    public function sql_mktimetodatetime($mktime)
    {
        return date("Y-m-d H:i:s", $mktime);
    }

    /**
     * sql_date2date($date, $short = true)
     * แปลงวันที่ในรูป Y-m-d เป็นวันที่แบบสั้นและเวลา เช่น 1 มค. 2555 12:00:00
     * @param string $date วันที่ในรูป Y-m-d h:i:s
     * @param boolean $short true (default) เดือนแบบสั้น, false เดือนแบบยาว
     * @param boolean $time true (default) คืนค่า เวลาด้วย (ถ้ามี)
     *
     * @return string
     */
    public function sql_date2date($date, $short = true, $time = true)
    {
        global $lng;
        preg_match('/([0-9]+){0,4}-([0-9]+){0,2}-([0-9]+){0,2}(\s([0-9]+){0,2}:([0-9]+){0,2}:([0-9]+){0,2})?/', $date, $match);
        if ((int) $match[1] == 0) {
            return '';
        } else {
            $month = $short ? $lng['MONTH_SHORT'] : $lng['MONTH_LONG'];

            return $match[3].' '.$month[(int) $match[2] - 1].' '.((int) $match[1] + $lng['YEAR_OFFSET']).($time ? $match[4] : '');
        }
    }

    /**
     * sql_datetime2mktime($date)
     * แปลงวันที่และเวลาของ sql เป็น mktime
     * คืนค่าเวลาในรูป mktime
     *
     * @param string $date วันที่ในรูป Y-m-d H:i:s
     *
     * @return int
     */
    public function sql_datetime2mktime($date)
    {
        preg_match('/([0-9]+){0,4}-([0-9]+){0,2}-([0-9]+){0,2}\s([0-9]+){0,2}:([0-9]+){0,2}:([0-9]+){0,2}/', $date, $match);

        return mktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
    }

    /**
     * timer_start()
     * เริ่มต้นจับเวลาการประมวลผล
     *
     * @return boolean
     */
    public function timer_start()
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $this->time_start = $mtime[1] + $mtime[0];
        $this->time = 0;

        return true;
    }

    /**
     * timer_stop()
     * จบการจับเวลา
     * คืนค่าเวลาที่ใช้ไป (msec)
     *
     * @return int
     */
    public function timer_stop()
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $time_end = $mtime[1] + $mtime[0];
        $time_total = $time_end - $this->time_start;

        return round($time_total, 10);
    }

    /**
     * query_count()
     * จำนวน query ทั้งหมดที่ทำงาน
     *
     * @return int
     */
    public function query_count()
    {
        return $this->time;
    }

    /**
     * debug($sql)
     * @param string $sql ข้อความที่จะแสดง (error)
     */
    private function debug($text)
    {
        if ($this->debug == 1) {
            echo preg_replace(array('/\r/', '/\n/', '/\t/'), array('', ' ', ' '), $text);
        }
    }
}
