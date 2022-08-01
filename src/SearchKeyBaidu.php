<?php

namespace Webguosai;

use Exception;

class SearchKeyBaidu
{
    protected $keyName = 'baidu_find_mark_id';

    /**
     * 获取js加载代码
     *
     * @param string $id 统计代码id
     * @return string
     */
    public function getScript($id)
    {
        $path = 'https://hm.baidu.com/hm.gif?';

        $ip        = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $referer   = $_SERVER['HTTP_REFERER'];

        $page = $this->getFullUrl();
        if (strpos($page, '?') === false) {
            $page .= '?';
        } else {
            $page .= '&';
        }
        $page .= $this->keyName . '=' . $this->shortText($ip . $userAgent);

        $query = [
            'si'  => $id,
            'cc'  => '1',
            'ck'  => '1',
            'cl'  => '32-bit',
            'ds'  => '768x1024',
            'vl'  => '946',
            'et'  => '0',
            'ja'  => '1',
            'ln'  => 'zh-cn',
            'lo'  => '0',
            'rnd' => '872961019',
            'v'   => '1.2.76',
            'lv'  => '2',
            'sn'  => '17865',
            'r'   => '0',
            'ww'  => '654',
            'ct'  => '!!',
            'tt'  => '111',
            // 落地页地址
            'u'   => ($page),//urlencode
            // 来源地址
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

        $client   = new \Webguosai\HttpClient([
            'timeout' => 5,
        ]);
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
        $response = $client->post($url, $data, [
            'cookie' => $cookie,
        ]);

        if ($response->ok()) {
            $data = $response->json();

            if ($data['status'] === 0) {
                $ret = [];

                foreach ($data['data']['items'][1] as $key => $value) {
                    $searchKey = $value[3];
                    $page      = $value[2];

                    $rex = '#' . $this->keyName . '=([^$]+?)$#i';
                    if (preg_match($rex, $page, $mat)) {
                        //有些会显示--，过滤掉
                        if ($searchKey != '--') {
                            $ret[] = [
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

    protected function getFullUrl()
    {
        if ('cli' === php_sapi_name()) {
            return '';
        }

        $pageURL = 'http';

        if ($_SERVER['HTTPS'] == 'on') {
            $pageURL .= 's';
        }
        $pageURL .= '://';

        if ($_SERVER['SERVER_PORT'] != '80') {
            $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }

        return $pageURL;
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
