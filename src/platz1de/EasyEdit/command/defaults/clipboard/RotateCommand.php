<?php

namespace platz1de\EasyEdit\command\defaults\clipboard;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\command\KnownPermissions;
use platz1de\EasyEdit\task\DynamicStoredRotateTask;
use platz1de\EasyEdit\utils\ArgumentParser;
use pocketmine\player\Player;

class RotateCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/rotate", [KnownPermissions::PERMISSION_CLIPBOARD]);
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		DynamicStoredRotateTask::queue($player->getName(), ArgumentParser::getClipboard($player));
	}
}