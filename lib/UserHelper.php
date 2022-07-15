<?php

namespace Vlads\Example;

use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;

/**
 * Class UserHelper
 */
class UserHelper
{
	private const VOWEL_LOWERCASE_LETTER_LIST = [
		'а',
		'о',
		'у',
		'э',
		'и',
		'ы',
		'е',
		'ё',
		'я',
		'ю',
		'a',
		'e',
		'i',
		'o',
		'u',
		'y',
	];

	private static function getLetters(): string
	{
		$result = implode('',self::VOWEL_LOWERCASE_LETTER_LIST);
		$result .= mb_strtoupper($result);

		return $result;
	}

	/**
	 * @param int $userId
	 *
	 * @throws SystemException
	 * @return string|null
	 */
	public static function getUserNameVowels(int $userId): string
	{
		if ($userId > 0)
		{
			$res = UserTable::getList(
				[
					'filter' => [
						'=ID' => $userId,
					],
					'select' => [
						'NAME',
						'LAST_NAME',
						'SECOND_NAME',
					],
					'cache' => [
						'ttl' => 86400,
					]
				]
			);
			if ($user = $res->fetch())
			{
				$fullName = implode(
					'',
					[
						$user['NAME'],
						$user['LAST_NAME'],
						$user['SECOND_NAME'],
					]
				);

				$result = preg_replace('/[^' . static::getLetters() . ']/u', '', $fullName);
			}
			else
			{
				throw new SystemException('User not found');
			}
		}
		else
		{
			throw new SystemException('Empty userId');
		}

		return $result;
	}
}
