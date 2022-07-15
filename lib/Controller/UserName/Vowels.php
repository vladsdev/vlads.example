<?php

namespace Vlads\Example\Controller\UserName;

use Bitrix\Main\Engine\Controller;
use Vlads\Example\UserHelper;

/**
 * Class UserName
 */
class Vowels extends Controller
{
	public function getAction( $userId): string
	{
		return UserHelper::getUserNameVowels($userId);
	}
}