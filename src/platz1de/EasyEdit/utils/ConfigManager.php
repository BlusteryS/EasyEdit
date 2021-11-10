<?php

namespace platz1de\EasyEdit\utils;

use platz1de\EasyEdit\EasyEdit;
use platz1de\EasyEdit\listener\ToggleableEventListener;
use platz1de\EasyEdit\thread\input\ConfigInputData;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use UnexpectedValueException;

class ConfigManager
{
	private const CONFIG_VERSION = "1.2.2";

	private static bool $commandCompletion;
	/**
	 * @var int[]
	 */
	private static array $heightIgnored = [];
	private static string $bedrockConversionDataSource;
	private static string $javaConversionDataSource;

	public static function load(): void
	{
		$config = EasyEdit::getInstance()->getConfig();

		if (($current = (string) $config->get("config-version", "1.0")) !== self::CONFIG_VERSION) {
			$cMajor = explode(".", $current)[0];
			$gMajor = explode(".", self::CONFIG_VERSION)[0];

			if ($cMajor === $gMajor) {
				//Updating the config while remaining current values
				$new = EasyEdit::getInstance()->getResource("config.yml");
				if ($new === null || ($data = stream_get_contents($new)) === false) {
					throw new UnexpectedValueException("Couldn't read update data");
				}
				fclose($new);

				if (($old = file_get_contents($config->getPath())) === false) {
					throw new UnexpectedValueException("Couldn't read current data");
				}

				//Allow different line endings
				$newConfig = preg_split("/\r\n|\n|\r/", $data);
				$oldConfig = preg_split("/\r\n|\n|\r/", $old);
				if ($newConfig === false || $oldConfig === false) {
					throw new AssumptionFailedError("Failed to split strings");
				}

				//We can't just use yaml_parse as we want to preserve comments
				foreach ($config->getAll() as $key => $value) {
					if ($key === "config-version") {
						continue;
					}
					$position = array_filter($newConfig, static function (string $line) use ($key): bool {
						return str_starts_with($line, $key . ":");
					});
					$oldPosition = array_filter($oldConfig, static function (string $line) use ($key): bool {
						return str_starts_with($line, $key . ":");
					});
					if (count($position) === 1) {
						$newConfig[key($position)] = $oldConfig[key($oldPosition)];
					}
				}

				file_put_contents($config->getPath(), implode(PHP_EOL, $newConfig));

				EasyEdit::getInstance()->getLogger()->notice("Your config was updated to the newest Version");
			} else {
				//We can't update for major releases
				copy($config->getPath(), $config->getPath() . ".old");
				unlink($config->getPath());
				EasyEdit::getInstance()->saveDefaultConfig();

				EasyEdit::getInstance()->getLogger()->warning("Your config was replaced with a newer Version");
			}

			$config->reload();
		}

		self::$commandCompletion = self::mustGetBool($config, "enable-command-completion", true);
		if (self::$commandCompletion) {
			ToggleableEventListener::enableCommandSuggestions();
		}

		self::$heightIgnored = array_map(static function (string $block): int {
			return BlockParser::getBlock($block)->getId();
		}, self::mustGetStringArray($config, "height-ignored-blocks", []));

		self::$bedrockConversionDataSource = self::mustGetString($config, "bedrock-convert-data", "");
		self::$javaConversionDataSource = self::mustGetString($config, "java-convert-data", "");

		ConfigInputData::from(self::$heightIgnored, self::$bedrockConversionDataSource, self::$javaConversionDataSource);
	}

	/**
	 * @param Config $config
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	private static function mustGetString(Config $config, string $key, string $default): string
	{
		$data = $config->get($key);
		if (!is_string($data)) {
			EasyEdit::getInstance()->getLogger()->warning("Your config value for " . $key . " is invalid, expected string array");
			return $default;
		}
		return $data;
	}

	/**
	 * @param Config   $config
	 * @param string   $key
	 * @param string[] $default
	 * @return string[]
	 */
	private static function mustGetStringArray(Config $config, string $key, array $default): array
	{
		$data = $config->get($key);
		if (!is_array($data) || array_filter($data, 'is_string') !== $data) {
			EasyEdit::getInstance()->getLogger()->warning("Your config value for " . $key . " is invalid, expected string array");
			return $default;
		}
		return $data;
	}

	/**
	 * @return int[]
	 */
	public static function getHeightIgnored(): array
	{
		return self::$heightIgnored;
	}
}