<?php
/*
 * @Description  : 地区缓存
 * @Author       : https://github.com/skyselang
 * @Date         : 2020-09-22
 * @LastEditTime : 2020-10-12
 */

namespace app\common\cache;

use think\facade\Cache;

class RegionCache
{
    /**
     * 缓存key
     *
     * @param integer $region_id 地区id
     * 
     * @return string
     */
    public static function key($region_id = 0)
    {
        $key = 'region:' . $region_id;

        return $key;
    }

    /**
     * 缓存有效时间
     *
     * @param integer $expire 有效时间
     * 
     * @return integer
     */
    public static function exp($expire = 0)
    {
        if (empty($expire)) {
            $expire = 30 * 24 * 60 * 60 + mt_rand(0, 9999);
        }

        return $expire;
    }

    /**
     * 缓存设置
     *
     * @param integer $region_id 地区id
     * @param array   $region    地区信息
     * @param integer $expire    有效时间
     * 
     * @return array 地区信息
     */
    public static function set($region_id = 0, $region = [], $expire = 0)
    {
        $key = self::key($region_id);
        $val = $region;
        $exp = $expire ?: self::exp();
        Cache::set($key, $val, $exp);

        return $val;
    }

    /**
     * 缓存获取
     *
     * @param integer $region_id 地区id
     * 
     * @return array 地区信息
     */
    public static function get($region_id = 0)
    {
        $key = self::key($region_id);
        $res = Cache::get($key);

        return $res;
    }

    /**
     * 缓存删除
     *
     * @param integer $region_id 地区id
     * 
     * @return bool
     */
    public static function del($region_id = 0)
    {
        $key = self::key($region_id);
        $res = Cache::delete($key);

        return $res;
    }
}
