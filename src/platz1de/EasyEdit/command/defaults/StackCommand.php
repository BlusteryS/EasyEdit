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
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use Throwable;

class StackCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/stack", "Stack the selected area", "easyedit.command.paste");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		$count = (int) ($args[0] ?? 1);

		try {
			$selection = SelectionManager::getFromPlayer($player->getName());
			/** @var Cube $selection */
			Selection::validate($selection, Cube::class);
		} catch (Throwable) {
			Messages::send($player, "no-selection");
			return;
		}

		StackTask::queue(new StackedCube($selection->getPlayer(), $selection->getWorldName(), $selection->getPos1(), $selection->getPos2(), VectorUtils::moveVectorInSight($player->getLocation(), new Vector3(0, 0, 0), $count)), $player->getPosition());
	}

	/**
	 * @return CommandParameter[][]
	 */
	public function getCommandOverloads(): array
	{
		return [
			[
				CommandParameter::standard("count", AvailableCommandsPacket::ARG_TYPE_INT),
			]
		];
	}
}