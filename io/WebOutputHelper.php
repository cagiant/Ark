<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:13
 */

namespace sinri\ark\io;


class WebOutputHelper
{
    const AJAX_JSON_CODE_OK = "OK";
    const AJAX_JSON_CODE_FAIL = "FAIL";

    public function __construct()
    {
    }

    /**
     * @param $anything
     */
    public function json($anything)
    {
        echo json_encode($anything);
    }

    /**
     * @param string $code OK or FAIL
     * @param mixed $data
     */
    public function jsonForAjax($code = self::AJAX_JSON_CODE_OK, $data = '')
    {
        echo json_encode(["code" => $code, "data" => $data]);
    }

    /**
     * @param $templateFile
     * @param array $params
     * @throws \Exception
     */
    public function displayPage($templateFile, $params = [])
    {
        extract($params);
        if (!file_exists($templateFile)) {
            throw new \Exception("Template file [{$templateFile}] not found.");
        }
        /** @noinspection PhpIncludeInspection */
        require $templateFile;
    }

    /**
     * 文件下载
     * @param $file
     * @param null $down_name
     * @param null $content_type
     * @return bool
     * @throws \Exception
     */
    public function downloadFileAsName($file, $content_type = null, $down_name = null)
    {
        if (!file_exists($file)) {
            throw new \Exception("No such file there", 404);
        }

        if ($down_name !== null && $down_name !== false) {
            $suffix = substr($file, strrpos($file, '.')); //获取文件后缀
            $down_name = $down_name . $suffix; //新文件名，就是下载后的名字
        } else {
            $k = pathinfo($file);
            $down_name = $k['filename'] . '.' . $k['extension'];
        }

        $fp = fopen($file, "r");
        $file_size = filesize($file);

        if ($content_type === null) {
            $content_type = 'application/octet-stream';
        }
        if ($content_type === 'application/octet-stream') {
            $content_disposition = 'attachment; filename=' . $down_name;
        } else {
            $content_disposition = 'inline';
        }

        // Headers
        header("Content-Type: " . $content_type);
        header("Accept-Ranges: bytes");
        header("Accept-Length:" . $file_size);
        header("Content-Disposition: " . $content_disposition);
        $buffer = 1024;
        $file_count = 0;

        // Write to client
        while (!feof($fp) && $file_count < $file_size) {
            $file_con = fread($fp, $buffer);
            $file_count += $buffer;
            echo $file_con;
        }
        fclose($fp);
        return true;
    }
}