<?php

namespace platz1de\EasyEdit\command\defaults;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\selection\Cube;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionManager;
use platz1de\EasyEdit\selection\StackedCube;
use platz1de\EasyEdit\task\selection\StackTask;
use platz1de\EasyEdit\utils\VectorUtils;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Throwable;

class StackCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/stack", "Stack the selected area", "easyedit.command.paste", "//stack <count>");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		$count = $args[0] ?? 1;

		try {
			$selection = SelectionManager::getFromPlayer($player->getName());
			/** @var Cube $selection */
			Selection::validate($selection, Cube::class);
		} catch (Throwable $exception) {
			Messages::send($player, "no-selection");
			return;
		}

		StackTask::queue(new StackedCube($selection->getPlayer(), $selection->getWorldName(), $selection->getPos1(), $selection->getPos2(), VectorUtils::moveVectorInSight($player->getLocation(), new Vector3(0, 0, 0), (int) $count)), $player->getPosition());
	}
}