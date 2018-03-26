<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:13
 */

namespace sinri\ark\io;


class ArkWebOutput
{
    const AJAX_JSON_CODE_OK = "OK";
    const AJAX_JSON_CODE_FAIL = "FAIL";

    const CONTENT_TYPE_JSON = "application/json";
    const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';

    const CHARSET_UTF8 = "UTF-8";

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getCurrentHTTPCode()
    {
        return http_response_code();
    }

    /**
     * @param $httpCode
     * @return int
     */
    public function sendHTTPCode($httpCode)
    {
        return http_response_code($httpCode);
    }

    /**
     * @param $contentType
     * @param null $charSet
     */
    public function setContentTypeHeader($contentType, $charSet = null)
    {
        header("Content-Type: " . $contentType . ($charSet !== null ? '; charset=' . $charSet : ''));
    }

    /**
     * @param $anything
     * @param int $options
     * @param int $depth
     */
    public function json($anything, $options = 0, $depth = 512)
    {
        echo json_encode($anything, $options, $depth);
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
     * 文件通过非直接方式下载
     * @param $file
     * @param null $down_name
     * @param null $content_type
     * @return bool
     * @throws \Exception
     */
    public function downloadFileIndirectly($file, $content_type = null, $down_name = null)
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
            $content_type = self::CONTENT_TYPE_OCTET_STREAM;
        }
        if ($content_type === self::CONTENT_TYPE_OCTET_STREAM) {
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