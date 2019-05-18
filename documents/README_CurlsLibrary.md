### CurlsLibrary
- Pendant <pendant59@qq.com>

- 默认配置
```
# 默认请求头 Default header
$header_default = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Content-Type: application/x-www-form-urlencoded',
];

# Json请求头 Default Json header
$header_json_default = [
    'Content-Type: application/json;charset=utf-8',
];

# POST upload file 请求头  
protected static $header_file_default = [
    'content-type: multipart/form-data;charset=utf-8',
];

# 默认cURL设置 Default config
$default_config = [
    'CURLOPT_SSL_VERIFYPEER' => false,
    'CURLOPT_SSL_VERIFYHOST' => false,
    'CURLOPT_FOLLOWLOCATION' => 1,
    'CURLOPT_RETURNTRANSFER' => 1,
    'CURLOPT_HEADER'         => false,
    'CURLOPT_CONNECTTIMEOUT' => 5,
    'CURLOPT_TIMEOUT'        => 7,
    'CURLOPT_USERAGENT'      => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
];
```

- GET 方法
```
/**
 * Curl Get
 * GET 单次curl请求
 * @param string $url                 带协议的请求地址
 * @param array $header               自定义header
 * @param array $self_config          自定义cURL设置
 * @param bool $single                默认true 返回请求结果，false 返回cURL句柄
 * @return array|false|resource
 */
public static function getRequest(
                            string $url, 
                            array $header = [], 
                            array $self_config = [], 
                            bool $single = true
                            ) {}
                            
                            
/**
 * Curl_multi Get
 * GET 并发curl请求
 * 不支持Windows系统的集成环境
 * @param array $urls                   带协议的请求地址
 * @param array $header                 自定义header 仅支持一维关联数组(通用header) 或 二维索引数组(每个请求都有一个header)
 * @param array $self_config            自定义cURL设置 仅支持一维关联数组(通用self_config)
 * @return array
 */
public static function getMultiRequests(
                            array $urls, 
                            array $header = [], 
                            array $self_config = []
                            ) {}
                            
```

- POST 方法
```
/**
 * Curl Post
 * POST 单次curl请求
 * @param string $url                   带协议的请求地址
 * @param array $data                   参数数组
 * @param bool $json                    是否将参数转为json格式传输
 * @param array $header                 自定义header
 * @param array $self_config            自定义cURL设置
 * @return array
 */
public static function postRequest(
                            string $url, 
                            array $data, 
                            bool $json = false, 
                            array $header = [], 
                            array $self_config = []
                            ) {}
                            
                            

/**
 * Curl multi post
 * POST 并发curl请求
 * 不支持Windows系统的集成环境
 * @param array $urls               带协议的请求地址
 * @param array $data               参数数组
 * @param array $header             自定义header 仅支持一维关联数组(通用header) 或 二维索引数组(每个请求都有一个header)
 * @param array $self_config        自定义cURL设置 仅支持一维关联数组(通用self_config)
 * @return array
 */
public static function postMultiRequests(
                            array $urls, 
                            array $data, 
                            array $header = [], 
                            array $self_config = []
                            ) {}
```
### 示例

- GET
```
$result = CurlsLibrary::getRequest('https://api.github.com/');


# header的格式(该header仅做演示，请求github加上这个header是没用的)
$header = [
    'Authorization' =>  123456789'   
];
$result = CurlsLibrary::getRequest('https://api.github.com/', $header);

```
- GET multi
```
$result= CurlsLibrary::getMultiRequests( ['https://api.github.com/','https://api.github.com/users/pendant59'] );

# 当前这一批次请求共用一个header的格式(该header仅做演示，请求github加上这个header是没用的)
$header = [
    'Authorization' =>  'a123456789'
];

# 当前每一个请求都有一个单独的header (注意：count($header) == count($urls) 一个url对应一个header)
$header = [
    ['Authorization' =>  'a123456789'],
    ['Authorization' =>  'qweasd6789'],
];

$result = CurlsLibrary::getMultiRequests(['https://api.github.com/','https://api.github.com/users/pendant59'], $header);

```

- POST 
```
$data = [
    'username' => 'test', 
    'password' => '123123'
    ];

# 一般的post请求    
$result = CurlsLibrary::postRequest('http://xxxxx/v1/login', $data);

# 将参数数组($data)转义成jsong格式传输   
$result = CurlsLibrary::postRequest('http://xxxxx/v1/login', $data, true);

```

- POST multi
```
$data = [
    [
        'json'=>false,              # 是否需要转化成json格式
        'data'=> [
            'username'=>'test',
            'password'=>'123456'
        ]
    ],
    [
        'json'=>true,              # 是否需要转化成json格式
        'data'=> [
            'username'=>'admin',
            'password'=>'123456'
        ]
    ]
];

$urls = [
    'http://xxxx/v1/login', 
    'http://xxxx/v1/login'
];
    
$result = CurlsLibrary::postMultiRequests($urls, $data);

```