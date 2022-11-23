<?php
//csrf tracer
require_once('./model.php');

$k = strtolower(trim(isset($_GET['k']) ? $_GET['k'] : ''));

$key = pathinfo($k, PATHINFO_FILENAME);

if (!empty($key)) {
    $model = new Model($key);
    if (!$model->is_lock()) {
        $model->update(get_client_info());
    }
}

$ext = pathinfo($k, PATHINFO_EXTENSION);

render($ext);

function render($ext)
{
    if (!empty($ext)) {
        $image_ext = ['jpeg', 'jpg', 'png', 'gif', 'wbmp'];
        $video_ext = ['mp4', 'm4v'];
        $audio_ext = ['mp3'];
        if (in_array($ext, $image_ext)) {
            // 创键空白图像并添加一些文本
            $im = imagecreatetruecolor(125, 125);
            $text_color = imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 5, 20, 52, 'I Got U :)', $text_color);
            header('Content-Type: ' . ext_content_type($ext));
            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                    imagejpeg($im);
                    break;
                case 'png':
                    imagepng($im);
                    break;
                case 'gif':
                    imagegif($im);
                    break;
                case 'wbmp':
                    imagewbmp($im);
                    break;
                default:
                    die('Unexpected Error');
            }
            // 释放内存
            imagedestroy($im);
        } elseif (in_array($ext, $video_ext)) {
            switch ($ext) {
                case 'mp4':
                case 'm4v':
                    $filename = __DIR__ . '/render/1.mp4';
                    render_stream($filename);
                    break;
                default:
                    die('Unexpected Error');
            }
        } elseif (in_array($ext, $audio_ext)) {
            switch ($ext) {
                case 'mp3':
                    $filename = __DIR__ . '/render/1.mp3';
                    render_stream($filename);
                    break;
                default:
                    die('Unexpected Error');
            }
        } else {
            die("Unsupported Extension Format");
        }
    } else {
        exit('I Got U');
    }
}


function render_stream($filename)
{
    $size = filesize($filename);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    header("Content-type: " . ext_content_type($ext));
    header("Accept-Ranges: bytes");
    if (isset($_SERVER['HTTP_RANGE'])) {
        header("HTTP/1.1 206 Partial Content");
        list($name, $range) = explode("=", $_SERVER['HTTP_RANGE']);
        list($begin, $end) = explode("-", $range);
        if ($end == 0) {
            $end = $size - 1;
        }
    } else {
        $begin = 0;
        $end = $size - 1;
    }
    header("Content-Length: " . ($end - $begin + 1));
    // header("Content-Disposition: filename=" . basename($filename));
    header("Content-Range: bytes " . $begin . "-" . $end . "/" . $size);
    $fp = fopen($filename, 'rb');
    fseek($fp, $begin);
    while (!feof($fp)) {
        $p = min(1024, $end - $begin + 1);
        $begin += $p;
        echo fread($fp, $p);
    }
    fclose($fp);
}

/**
 * 通过文件名判断并获取OSS服务文件上传时文件的contentType
 * @param fileName 文件名
 * @return 文件的contentType
 */
function ext_content_type(String $ext)
{
    $ext = strtolower($ext);
    if ("bmp" === $ext) {
        return "image/bmp";
    }
    if ("wbmp" == $ext) {
        return "image/wbmp";
    }
    if ("gif" === $ext) {
        return "image/gif";
    }
    if ("jpeg" === $ext || "jpg" === $ext  || "png" === $ext) {
        return "image/jpeg";
    }
    if ("html" === $ext) {
        return "text/html";
    }
    if ("txt" === $ext) {
        return "text/plain";
    }
    if ("vsd" === $ext) {
        return "application/vnd.visio";
    }
    if ("ppt" === $ext || "pptx" === $ext) {
        return "application/vnd.ms-powerpoint";
    }
    if ("doc" === $ext || "docx" === $ext) {
        return "application/msword";
    }
    if ("xls" === $ext || "xlsx" === $ext) {
        return "application/msexcel";
    }
    if ("csv" === $ext) {
        return "application/csv";
    }
    if ("xml" === $ext) {
        return "text/xml";
    }
    if ("mp4" === $ext) {
        return "video/mp4";
    }
    if ("avi" === $ext) {
        return "video/x-msvideo";
    }
    if ("mov" === $ext) {
        return "video/quicktime";
    }
    if ("mpeg" === $ext || "mpg" == $ext) {
        return "video/mpeg";
    }
    if ("wm" === $ext) {
        return "video/x-ms-wmv";
    }
    if ("flv" === $ext) {
        return "video/x-flv";
    }
    if ("mkv" === $ext) {
        return "video/x-matroska";
    }
    if ("mp3" == $ext) {
        return "audio/mp3";
    }

    //默认返回类型
    return "video/x-msvideo";
}

function get_ip()
{
    foreach (array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ) as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                //会过滤掉保留地址和私有地址段的IP，例如 127.0.0.1会被过滤
                //也可以修改成正则验证IP
                if ((bool) filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 |
                        FILTER_FLAG_NO_PRIV_RANGE |
                        FILTER_FLAG_NO_RES_RANGE
                )) {
                    return $ip;
                }
            }
        }
    }
    return null;
}

function get_client_info()
{
    $useragent = $_SERVER['HTTP_USER_AGENT'];

    $clientip = get_ip();

    $client_info = array(
        "ua" => $useragent,
        "ip" => $clientip,
    );

    return $client_info;
}
