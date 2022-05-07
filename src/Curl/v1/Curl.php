<?php

declare(strict_types=1);

namespace Plinct\Tool\Curl\v1;

class Curl
{
	/**
	 * @var string
	 */
	private string $method;
  /**
   * @var false|resource
   */
  private $handle;
  /**
   * @var array|null
   */
  private ?array $headers = null;
  /**
   * @var bool|string
   */
  private $exec = false;

  /**
   *
   */
  public function __construct(string $url = '')
  {
    $this->handle = curl_init($url);
    curl_setopt($this->handle, CURLOPT_HEADER, false);
  }

	/**
	 * @param string $header
	 * @return Curl
	 */
	public function addHeaders(string $header): Curl
	{
		$this->headers[] = $header;
		return $this;
	}

  /**
   * @param string $url
   * @return $this
   */
  public function setUrl(string $url): Curl
  {
    curl_setopt($this->handle, CURLOPT_URL, $url);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getInfo()
  {
    if (!$this->exec) $this->execute();
    return curl_getinfo($this->handle);
  }

    /**
     * @param string $method
     * @return $this
     */
    public function method(string $method): Curl
    {
			$this->method = strtoupper($method);

			if($this->method == "POST") {
				curl_setopt($this->handle, CURLOPT_POST, true);
			} else {
				curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
			}
        return $this;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function authorizationBear(string $token): Curl
    {
        if($token) curl_setopt($this->handle, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        return $this;
    }

    /**
     * @return $this
     */
    public function returnWithJson(): Curl
    {
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        return $this;
    }

    /**
     * @return $this
     */
    public function connectWithLocalhost(): Curl
    {
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, false);
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function params(array $params): Curl
    {
      if ($params) {
				if ($this->method == "POST") {
					curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($params));
				} else {
					curl_setopt($this->handle, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_SLASHES));
				}
      }
      return $this;
    }

	/**
	 * @param $data
	 * @return $this
	 */
		public function post($data): Curl
		{
			curl_setopt($this->handle, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($this->handle, CURLOPT_POST, true);
			return $this;
		}

	/**
	 * @param $data
	 * @return void
	 */
		public function put($data)
		{
			curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($data));
		}

    /**
     *
     */
    private function execute(): void
    {
        $this->exec = curl_exec($this->handle);
    }

    /**
     * @return string
     */
    public function ready(): string
    {
        if ($this->headers) {
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->headers);
        }

        // for localhost
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == "::1") $this->connectWithLocalhost();

        if (!$this->exec) $this->execute();

        if (curl_error($this->handle) !== '' && $this->exec === false) {
            $return  = curl_error($this->handle);
        } else {
            $return = $this->exec;
        }
        curl_close($this->handle);

        return $return;
    }
}
