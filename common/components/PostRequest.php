<?php
namespace common\components;

use Yii;
use yii\base\Configurable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use yii\base\InvalidParamException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class PostRequest implements Configurable
{
    private $uri;
    private $formParams = [];
    /** @var $client GuzzleClient */
    private $client;
    private $proxy;

    //region Getters&Setters
    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        if (!is_string($uri)) {
            throw new InvalidParamException('URI must be a string!');
        }
        $this->uri = $uri;
    }

    /**
     * @return array
     */
    public function getFormParams()
    {
        return $this->formParams;
    }

    /**
     * @param array $formParams
     */
    public function setFormParams($formParams)
    {
        if (!is_array($formParams)) {
            throw new InvalidParamException('Form params must be array!');
        }
        $this->formParams = $formParams;
    }
    /**
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        if (!is_string($proxy)) {
            throw new InvalidParamException('Proxy must be a string!');
        }
        $this->proxy = $proxy;
    }
    //endregion
    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }
        }
        $this->client = new GuzzleClient();

    }

    private function paramsMapper()
    {
        $map = [
            'proxy' => 'proxy'
        ];
        $result = [];

    }
    public function send()
    {
        $onRedirect = function(
            RequestInterface $request,
            ResponseInterface $response,
            UriInterface $uri
        ) {
            echo 'Redirecting to ' . $uri . "\n";
        };

        try {
            $response = $this->client->request('POST',$this->getUri(),[
                'form_params' => $this->getFormParams(),
                'proxy' => $this->getProxy(),
                //'debug' => true,
                'allow_redirects' => [
                    'on_redirect'     => $onRedirect,
                    'track_redirects' => true
                ]
            ]);
            print 'Status: '  . $response->getStatusCode() . PHP_EOL;
            $body = $response->getBody();
            //print $body . PHP_EOL;
        } catch (RequestException $e) {
            print "RequestException handled!" . PHP_EOL;
            if ($e->hasResponse()) {
                echo "hasResponse! Code: " . $e->getResponse()->getStatusCode() . PHP_EOL;
                echo $e->getResponse()->getBody() . PHP_EOL;
            }
            else {
                echo "No response!" . PHP_EOL;
            }

        }
    }

    public function onRedirect()
    {
        $onRedirect = function(
            RequestInterface $request,
            ResponseInterface $response,
            UriInterface $uri
        ) {
            echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";
        };

        return $onRedirect;
    }

}