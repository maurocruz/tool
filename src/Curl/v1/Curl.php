<?php

declare(strict_types=1);

namespace Plinct\Tool\Curl\v1;

class Curl
{
  /**
   * @var false|resource
   */
  private $handle;
  /**
   * @var array|null
   */
  private ?array $headers = null;
	/**
	 * @var string
	 */
	private string $url;

	private $info;

  /**
   *
   */
  public function __construct(string $url = '')
  {
		$this->url = $url;
    $this->handle = curl_init($url);
  }

	/**
	 * @param string $header
	 * @return Curl
	 */
	public function setHeaders(string $header): Curl
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
	  $this->url = $url;
    curl_setopt($this->handle, CURLOPT_URL, $url);
    return $this;
  }

  /**
   * @return false|resource
   */
  public function getInfo()
  {
    return $this->info;
  }

  /**
   * @param string $token
   * @return $this
   */
  public function authorizationBear(string $token): Curl
  {
    if($token) $this->setHeaders("Authorization: Bearer $token");
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
	 * @param array|null $params
	 * @return $this
	 */
	public function get(array $params = null): Curl {
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "GET");
		if ($params) curl_setopt($this->handle, CURLOPT_URL, $this->url."?".http_build_query($params));
		return $this;
	}

	/**
	 * @param array $params
	 * @param array|null $FILES
	 * @return $this
	 */
	public function post(array $params, array $FILES = null ): Curl
	{
		if ($FILES) {
			foreach ($FILES as $key => $value) {
				foreach ($value['error'] as $index => $error) {
					if ($error === 0) {
						$params["{$key}[$index]"] = curl_file_create(
							$value['tmp_name'][$index],
							$value['type'][$index],
							$value['name'][$index]
						);
					}
				}
			}

			$this->setHeaders('Content-Type: multipart/form-data');
			$this->setHeaders('User-Agent: '.$_SERVER['HTTP_USER_AGENT']);
		}

		curl_setopt($this->handle, CURLOPT_POST, true);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, $params);
		return $this;
	}

	/**
	 * @param $data
	 * @return $this
	 */
	public function put($data): Curl {
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($data));
		return $this;
	}

	/**
  * @param $params
  * @return $this
  */
	public function delete($params): Curl
	{
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($this->handle, CURLOPT_URL, $this->url."?".http_build_query($params));
		return $this;
	}

  /**
   * @return string
   */
  public function ready(): string {
    if ($this->headers) {
      curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->headers);
    }
    // for localhost
    if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == "::1") $this->connectWithLocalhost();
		// execute
	  $exec = curl_exec($this->handle);
		// get info
		$this->info = curl_getinfo($this->handle);
		// returns
    if (curl_error($this->handle) !== '' && $exec === false) {
      $return  = curl_error($this->handle);
    } else if (is_string($exec)) {
      $return = $exec;
    }
		// close
    curl_close($this->handle);
		//
    return $return ?? '';
  }
}
