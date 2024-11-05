<?php
declare(strict_types=1);
namespace Plinct\Tool;

use Exception;

class OpenGraph
{
	private string $sitename;
	private string $representativeImage;
	private string $url;
	private ?string $logo = null;
	/**
	 * @var array|null
	 */
	private ?array $items = null;

	public function __construct(string $sitename, string $representativeImage, $url)
	{
		$this->sitename = $sitename;
		$this->representativeImage = $representativeImage;
		$this->url = $url;
	}

	/**
	 * @param string $property
	 * @param string $content
	 * @return $this
	 */
	public function addItem(string $property, string $content): OpenGraph
	{
		$this->items[] = "<meta property='og:$property' content='$content'>";
		return $this;
	}

	public function setLogo(string $logo): static
	{
		$this->logo = $logo;
		return $this;
	}
	/**
	 * @param string $title
	 * @param string $description
	 * @param string $type
	 * @param string|null $image
	 * @param string|null $url
	 * @return $this
	 * @throws Exception
	 */
	public function basic(string $title, string $description, string $type = 'article', string $image = null, string $url = null): OpenGraph
	{
		$image = $image ?? $this->representativeImage;
		$url = $url ?? $this->url;

		$this->addItem('site_name', $this->sitename);
		$this->addItem('title', $title);
		$this->addItem('description', $description);
		$this->addItem('type', $type);
		$this->addItem('image', $image);
		$this->addItem('url', $url);
		if ($this->logo) {
			$this->addItem('logo', $this->logo);
		}

		// Twitter Meta Tags
		$this->items[] = "<meta property='twitter:card' content='summary_large_image'>";
		$this->items[] = "<meta property='twitter:domain' content='{$_SERVER['HTTP_HOST']}'>";
		$this->items[] = "<meta property='twitter:url' content='$url'>";
		$this->items[] = "<meta property='twitter:title' content='$title'>";
		$this->items[] = "<meta property='twitter:image' content='$image'>";

		return $this;
	}

	/**
	 * @return array
	 */
	public function ready(): array
	{
		return $this->items;
	}
}
