<?php
// 建议对数据目录禁止访问
// 数据存储方案：csv文件

class Model
{
    const DATA_DIR =  __DIR__ . '/data';
    private $key;
    private $filename;

    public function __construct($key)
    {

        $filename = self::DATA_DIR . '/' . $key . '.csv';
        $this->key = $key;
        $this->filename = $filename;
    }

    static public function get_key()
    {
        $key = substr(md5(microtime(true)), 0, 6);
        // $key = tempnam($dir, '');
        $filename = self::DATA_DIR . '/' . $key . '.csv';
        file_put_contents($filename, '');
        return $key;
    }

    static public function clear()
    {
        $files = scandir(self::DATA_DIR);
        foreach ($files as $file) {
            if (!is_dir(self::DATA_DIR . '/' . $file)) {
                unlink(self::DATA_DIR . '/' . $file);
            }
        }
    }

    public function data()
    {
        $data = array();
        if ($this->is_lock()) {
            return null;
        } else {
            if (($fp = fopen($this->filename, 'r')) !== false) {
                while (($row = fgetcsv($fp)) !== false) {
                    array_push($data, $row);
                }
                fclose($fp);
            }
            unlink($this->filename);
            return $data;
        }
    }

    public function update($data)
    {
        if (!$this->is_lock()) {
            if (($fp = fopen($this->filename, 'a')) !== false) {
                fputcsv($fp, $data);
                fclose($fp);
            }
        }
    }

    public function is_lock()
    {
        return !file_exists($this->filename);
    }
}
