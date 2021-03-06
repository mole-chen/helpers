<?php
namespace mole\helpers;

/**
 * Some commonly used functions.
 *
 * It's used for every projects.
 *
 * @author Jin Chen <jmole.chen@gmail.com>
 * @since 1.0
 */
class BaseUtils
{
    /**
     * Build url for API request.
     *
     * If the path index of :xx and params has key `xx`, the path will be replace by the params[xx] value.
     *
     * ```php
     * Utils::buildUrl('http://localhost/rest', '/user/{id}', ['id' => 1, 'status' => 0]);
     * //=> http://localhost/rest/user/1?status=0
     * ```
     *
     * @param string $baseUrl http://localhost/rest
     * @param string $path /user/{id}
     * @param array $params query params for url.
     * @return string
     */
    public static function buildUrl($baseUrl, $path, $params = [])
    {
        if (!is_array($params)) {
            $params = [];
        }

        $path = preg_replace_callback('/\{(\w+)\}/', function($matches) use (&$params) {
            if (isset($params[$matches[1]])) {
                $tmp = $params[$matches[1]];
                unset($params[$matches[1]]);
                return urlencode($tmp);
            }

            return '';
        }, $path);

        $path = trim(preg_replace('#/{2,}#', '/', $path), '/');
        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }

        return rtrim($baseUrl, '/') . '/' . rtrim($path);
    }

    /**
     * Returns a value indicating whether the give value is "empty".
     * The value is considered "empty", if one of the following conditions is satisfied:
     *
     * - it is `null`,
     * - an empty string (`''`),
     * - a string containing only whitespace characters,
     * - or an empty array.
     *
     * @param mixed $value
     * @return boolean if the value is empty
     */
    public static function isEmpty($value)
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }

    /**
     * Base64 encode for url safe.
     *
     * @param string $data
     * @return string
     */
    public static function urlSafeB64Encode($data)
    {
        $b64 = base64_encode($data);
        $b64 = str_replace(
            ['+', '/', '\r', '\n', '='],
            ['-', '_'],
            $b64
        );
        return $b64;
    }

    /**
     * Base64 decode for url safe.
     *
     * @param string $b64
     * @return string
     */
    public static function urlSafeB64Decode($b64)
    {
        $b64 = str_replace(['-', '_'], ['+', '/'], $b64);
        return base64_decode($b64);
    }

    /**
     * @param string $year
     * @param string $month
     * @return array
     */
    public static function monthStartAndEnd($year = null, $month = null)
    {
        $year = $year === null ? date('Y') : $year;
        $month = $month === null ? date('m') : $month;
        $start = date('Y-m-d', strtotime(sprintf('%s-%s-01', $year, $month)));
        $end = date('Y-m-d', strtotime('-1 day', strtotime('+1 month', strtotime($start))));

        return [
            $start,
            $end
        ];
    }

    /**
     * Get real IP.
     *
     * @return string user IP address
     */
    public static function getIp()
    {
        $vars = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];
        foreach ($vars as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);

                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return 'UNKNOWN';
    }
}
