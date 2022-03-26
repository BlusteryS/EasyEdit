<?php

namespace platz1de\EasyEdit\command\defaults\generation;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\command\KnownPermissions;
use platz1de\EasyEdit\selection\Cylinder;
use platz1de\EasyEdit\task\editing\selection\pattern\SetTask;
use platz1de\EasyEdit\utils\ArgumentParser;
use pocketmine\player\Player;

class CylinderCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/cylinder", [KnownPermissions::PERMISSION_GENERATE, KnownPermissions::PERMISSION_EDIT], ["/cy"]);
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		ArgumentParser::requireArgumentCount($args, 3, $this);
		SetTask::queue(Cylinder::aroundPoint($player->getName(), $player->getWorld()->getFolderName(), $player->getPosition(), (float) $args[0], (int) $args[1]), ArgumentParser::parseCombinedPattern($player, $args, 2), $player->getPosition());
	}
}