<?php

namespace platz1de\EasyEdit\command\defaults;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\pattern\functional\SmoothPattern;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionManager;
use platz1de\EasyEdit\task\selection\SetTask;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use Throwable;

class SmoothCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/smooth", "Smooth the selected Area", "easyedit.command.set");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		try {
			$selection = SelectionManager::getFromPlayer($player->getName());
			Selection::validate($selection);
		} catch (Throwable) {
			Messages::send($player, "no-selection");
			return;
		}

		SetTask::queue($selection, new Pattern([new SmoothPattern([])]), $player->getPosition());
	}

	/**
	 * @return CommandParameter[][]
	 */
	public function getCommandOverloads(): array
	{
		return [];
	}
}