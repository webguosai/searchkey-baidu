<h1 align="center">search baidu key</h1>

<p align="center">
<a href="https://packagist.org/packages/webguosai/searchkey-baidu"><img src="https://poser.pugx.org/webguosai/searchkey-baidu/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/webguosai/searchkey-baidu"><img src="https://poser.pugx.org/webguosai/searchkey-baidu/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/webguosai/searchkey-baidu"><img src="https://poser.pugx.org/webguosai/searchkey-baidu/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/webguosai/searchkey-baidu"><img src="https://poser.pugx.org/webguosai/searchkey-baidu/license" alt="License"></a>
</p>


## 运行环境

- php >= 5.6
- composer

## 安装

```Shell
composer require webguosai/searchkey-baidu -vvv
```

## 使用
* 实例
```php
$baidu = new \Webguosai\SearchKeyBaidu();
```

* 获取加载js
```php
$baidu->getScript($id);
```
* 查询
```php
try {
    $baidu->query($pathId, $siteId, $cookie, $limit);
} catch (Exception $e) {
    echo $e->getMessage();
}
```

## License

MIT
