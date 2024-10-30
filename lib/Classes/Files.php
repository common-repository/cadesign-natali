<?php

namespace Cadesign\NataliApi;

class Files
{
	private static $domain = 'https://static.natali37.ru/';
	private static $content = '/wp-content/';
	private static $files = [];

	static function saveFiles($arFiles)
	{
		$localFiles = [];
		foreach ($arFiles as $file)
		{
			$localFiles[crc32($file["url"])] = self::getFileID($file["url"]);
		}

		self::$files = $localFiles;

		return $localFiles;
	}

	static function getFileByUrl($url)
	{
		return self::$files[crc32($url)];
	}

	private static function getFileID($file)
	{
		if ($existId = self::findByName($file))
		{
			return $existId;
		}

		return media_sideload_image( $file, 0, null, 'id' );
	}

	private static function download($file, $localFile)
	{
		$pathToCopy = $_SERVER['DOCUMENT_ROOT'] . self::$content . $localFile;
		self::createPath($pathToCopy);
		copy($file, $pathToCopy);

		return self::createPostAttachment($pathToCopy);
	}

	private static function getLocalImgUrl($file)
	{
		return str_replace(self::$domain, '', $file);
	}

	private static function createPath(string $pathToCopy)
	{
		$arPath = explode('/', $pathToCopy);
		array_pop($arPath);
		$dir = implode('/', $arPath);

		mkdir($dir, 0755, true);
	}

	private static function createPostAttachment(string $localFile)
	{
		$filetype = wp_check_filetype(basename($localFile), null);

		$wp_upload_dir = wp_upload_dir();
		$attachment = [
			'guid' => $wp_upload_dir['url'] . '/' . basename($localFile),
			'post_mime_type' => $filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($localFile)),
			'post_content' => '',
			'post_status' => 'inherit'
		];

		return wp_insert_attachment($attachment, $localFile);
	}

	private static function findByName($localFile)
	{
		$arFile = explode('/', $localFile);

		[$fileName, $fileExt] = explode('.', array_pop($arFile));
		$find = new \WP_Query([
			'post_type' => 'attachment',
			'name' => $fileName
		]);

		if ($find->have_posts())
		{
			$attachments = $find->get_posts();
			foreach ($attachments as $attach)
			{
				return $attach->ID;
			}
		}

		return false;
	}
}