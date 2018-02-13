<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 15:02
 */

namespace sinri\ark\io;


use sinri\ark\core\ArkHelper;

class WebInputHelper
{
    const METHOD_ANY = "ANY";//since v2.1.3 for TreeRouter

    const METHOD_HEAD = "HEAD";//since v1.3.0
    const METHOD_GET = "GET";//since v1.3.0
    const METHOD_POST = "POST";//since v1.3.0
    const METHOD_PUT = "PUT";//since v1.3.0
    const METHOD_DELETE = "DELETE";//since v1.3.0
    const METHOD_OPTION = "OPTION";//since v1.3.0
    const METHOD_PATCH = "PATCH";//since v1.3.0
    const METHOD_CLI = "cli";//since v1.3.0

    const IP_TYPE_V4 = "IPv4";
    const IP_TYPE_V6 = "IPv6";

    protected $headerHelper;
    protected $ipHelper;
    protected $rawPostBody;
    protected $rawPostBodyParsedAsJson;

    public function __construct()
    {
        $this->headerHelper = new WebInputHeaderHelper();
        $this->ipHelper = new WebInputIPHelper();
        $this->rawPostBody = file_get_contents('php://input');
        $this->rawPostBodyParsedAsJson = @json_decode($this->rawPostBody, true);
    }

    /**
     * @return WebInputHeaderHelper
     */
    public function getHeaderHelper()
    {
        return $this->headerHelper;
    }

    /**
     * @return WebInputIPHelper
     */
    public function getIpHelper()
    {
        return $this->ipHelper;
    }

    /**
     * @return bool|string
     */
    public function getRawPostBody()
    {
        return $this->rawPostBody;
    }

    /**
     * @return mixed
     */
    public function getRawPostBodyParsedAsJson()
    {
        return $this->rawPostBodyParsedAsJson;
    }

    public function readRequest($name, $default = null, $regex = null, &$error = null)
    {
        $value = ArkHelper::readTarget($_REQUEST, $name, $default, $regex, $error);
        try {
            $content_type = $this->headerHelper->getHeader("CONTENT-TYPE", null, '/^application\/json/');
            if (
                $content_type !== null
                //preg_match('/^application\/json(;.+)?$/', $content_type)
            ) {
                if (is_array($this->rawPostBodyParsedAsJson)) {
                    $value = ArkHelper::readTarget($this->rawPostBodyParsedAsJson, $name, $default, $regex, $error);
                }
            }
        } catch (\Exception $exception) {
            // actually do nothing.
        }
        return $value;
    }

    public function readHeader($name, $default = null, $regex = null)
    {
        return $this->headerHelper->getHeader($name, $default, $regex);
    }

    public function readSession($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SESSION, $name, $default, $regex);
    }

    public function readGet($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_GET, $name, $default, $regex);
    }

    public function readPost($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_POST, $name, $default, $regex);
    }

    public function visitorIP($proxyIPs = [])
    {
        return $this->ipHelper->detectVisitorIP($proxyIPs);
    }

    public function requestMethod()
    {
        $method = $this->readServer('REQUEST_METHOD');
        if ($method !== null) {
            $method = strtoupper($method);
            return $method;
        }
        return ArkHelper::isCLI() ? self::METHOD_CLI : false;
    }

    public function readServer($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SERVER, $name, $default, $regex);
    }
}