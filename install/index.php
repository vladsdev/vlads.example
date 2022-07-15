<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\File;

Loc::loadMessages(__FILE__);

class vlads_example extends CModule
{
	var $MODULE_ID = 'vlads.example';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('VLADS_EXAMPLE_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('VLADS_EXAMPLE_MODULE_DESC');
		$this->PARTNER_NAME = Loc::getMessage('VLADS_EXAMPLE_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('VLADS_EXAMPLE_PARTNER_URI');

		$this->exclusionAdminFiles = [
			'..',
			'.',
			'menu.php',
			'operation_description.php',
			'task_description.php',
		];
	}

	public function UnInstallDB(): void
	{
		Option::delete($this->MODULE_ID);
	}

	public function InstallFiles($arParams = [])
	{
		$path = $this->GetPath() . '/install/components';

		if (Directory::isDirectoryExists($path))
		{
			CopyDirFiles($path, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
		}

		if (Directory::isDirectoryExists($path = $this->GetPath() . '/admin'))
		{
			CopyDirFiles(
				$this->GetPath() . '/install/admin/',
				$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
			); //если есть файлы для копирования
			if ($dir = opendir($path))
			{
				while (false !== $item = readdir($dir))
				{
					if (in_array($item, $this->exclusionAdminFiles))
					{
						continue;
					}
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $item,
						'<'
						. '?php require $_SERVER[\'DOCUMENT_ROOT\'] . \''
						. $this->GetPath(true)
						. '/admin/'
						. $item
						. '\';?'
						. '>'
					);
				}
				closedir($dir);
			}
		}

		if (Directory::isDirectoryExists($path = $this->GetPath() . '/install/files'))
		{
			$this->copyArbitraryFiles();
		}

		return true;
	}

	function UnInstallFiles()
	{
		Directory::deleteDirectory(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $this->MODULE_ID . '/'
		);

		if (Directory::isDirectoryExists($path = $this->GetPath() . '/admin'))
		{
			DeleteDirFiles(
				$_SERVER['DOCUMENT_ROOT'] . $this->GetPath() . '/install/admin/',
				$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
			);
			if ($dir = opendir($path))
			{
				while (false !== $item = readdir($dir))
				{
					if (in_array($item, $this->exclusionAdminFiles))
					{
						continue;
					}
					File::deleteFile(
						$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item
					);
				}
				closedir($dir);
			}
		}

		if (Directory::isDirectoryExists($path = $this->GetPath() . '/install/files'))
		{
			$this->deleteArbitraryFiles();
		}

		return true;
	}

	function copyArbitraryFiles()
	{
		$rootPath = $_SERVER['DOCUMENT_ROOT'];
		$localPath = $this->GetPath() . '/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object)
		{
			$destPath = $rootPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
			($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
		}
	}

	function deleteArbitraryFiles()
	{
		$rootPath = $_SERVER['DOCUMENT_ROOT'];
		$localPath = $this->GetPath() . '/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object)
		{
			if (!$object->isDir())
			{
				$file = str_replace($localPath, $rootPath, $object->getPathName());
				File::deleteFile($file);
			}
		}
	}

	private function GetPath($notDocumentRoot = false)
	{
		if ($notDocumentRoot)
		{
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		}
		else
		{
			return dirname(__DIR__);
		}
	}

	function DoInstall()
	{
		global $APPLICATION;

		ModuleManager::registerModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage('VLADS_EXAMPLE_INSTALL'),
			$this->GetPath() . '/install/step.php'
		);
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		$this->UnInstallFiles();

		if ($request['savedata'] !== 'Y')
		{
			$this->UnInstallDB();
		}

		ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage('VLADS_EXAMPLE_UNINSTALL'),
			$this->GetPath() . '/install/unstep.php'
		);
	}
}
