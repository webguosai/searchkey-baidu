<?php

namespace Webguosai;

use Exception;
use Webguosai\HttpClient;

class SearchKeyBaidu
{
    protected $keyName = 'baidu_query_id';

    /**
     * 获取官方js
     *
     * @param $id
     * @return string
     */
    public function getScript($id)
    {
        $jsPath = __DIR__ . '/count.js';
        if (file_exists($jsPath)) {
            // 获取官方代码
            $code = file_get_contents($jsPath);

            $queryParams = $this->keyName . '=' . $this->getVisitorId();

            return str_replace(['{{queryParams}}', '{{id}}'], [$queryParams, $id], $code);
        } else {
            // 获取图片的形式
            return $this->getScriptImage($id);
        }
    }

    /**
     * 获取js加载代码
     *
     * @param string $id 统计代码id
     * @return string
     */
    public function getScriptImage($id)
    {
        $path = 'https://hm.baidu.com/hm.gif?';

        $referer   = $_SERVER['HTTP_REFERER'];

        $page = $this->getFullUrl();
        if (strpos($page, '?') === false) {
            $page .= '?';
        } else {
            $page .= '&';
        }
        $page .= $this->keyName . '=' . $this->getVisitorId();

        $query = [
            'cc' => 1,
            'ck' => 1,
            'cl' => '24-bit',
            'ds' => '1920x1080',
            'vl' => 937,
            'et' => 0,
            'ja' => 0,
            'ln' => 'zh-cn',
            'lo' => 0,
            'lt' => 1659328397,
            'rnd' => 1350187136,
            'si' => $id,
            'v' => '1.2.96',
            'lv' => 2,
            'sn' => 61881,
            'r' => 0,
            'ww' => 1033,
            'ct' => '!!',
//            // 落地页地址
            'u'   => ($page),//urlencode
//            // 来源地址
            'su'  => ($referer),//urlencode
        ];

        $url = $path . http_build_query($query);

        return "setTimeout('img=new Image;img.src=\"{$url}\";', 0);";
    }

    /**
     * 查询
     *
     * @param int $pathId 路径中的那个id
     * @param int $siteId 站点id
     * @param string $cookie cookie
     * @param int $limit 条数
     * @return array
     * @throws Exception
     */
    public function query($pathId, $siteId, $cookie, $limit = 100)
    {
        $url = 'https://tongji.baidu.com/web5/' . $pathId . '/ajax/post';

        $data     = [
            'siteId'     => $siteId,
            'order'      => 'start_time,desc',
            'offset'     => 0,
            'pageSize'   => $limit,
            'tab'        => 'visit',
            'timeSpan'   => 14,
            'indicators' => 'start_time,source,access_page,searchword,ip',
            'anti'       => 0,
            'reportId'   => 4,
            'method'     => 'trend/latest/a',
            'queryId'    => '',
        ];
        $response = (new HttpClient(['timeout' => 5]))->post($url, $data, [
            'cookie' => $cookie,
        ]);

//        dump($response->headers);
        if ($response->ok()) {
            $data = $response->json();

            if ($data['status'] === 0) {
                $ret = [];

                foreach ($data['data']['items'][0] as $key => $value) {
                    $searchKey = $value[0]['detail']['from_word'];
                    $page      = $value[0]['detail']['accessPage'];;

                    $rex = '#' . $this->keyName . '=([^$]+?)$#i';
                    if (preg_match($rex, $page, $mat)) {
                        //有些会显示--，过滤掉
                        if ($searchKey != '--') {
                            $ret[] = [
                                'page'      => $page,
                                'visitMark' => $mat[1],    //访客标识
                                'searchKey' => $searchKey, //搜索词
                            ];
                        }
                    }
                }

                return $ret;
            }

            throw new Exception($data['msg']);
        }

        throw new Exception($response->getErrorMsg());
    }

    protected function getVisitorId()
    {
        $ip        = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        return $this->shortText($ip . $userAgent);
    }


    protected function getFullUrl()
    {
        if ('cli' === php_sapi_name()) {
            return '';
        }

        $page = 'http';

        if ($_SERVER['HTTPS'] == 'on') {
            $page .= 's';
        }
        $page .= '://';

        if ($_SERVER['SERVER_PORT'] != '80') {
            $page .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $page .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        return $page;
    }

    protected function shortText($text)
    {
        $x = sprintf("%u", crc32($text));

        $show = '';
        while ($x > 0) {
            $s = $x % 62;
            if ($s > 35) {
                $s = chr($s + 61);
            } elseif ($s > 9 && $s <= 35) {
                $s = chr($s + 55);
            }
            $show .= $s;
            $x    = floor($x / 62);
        }
        return $show;
    }
}
