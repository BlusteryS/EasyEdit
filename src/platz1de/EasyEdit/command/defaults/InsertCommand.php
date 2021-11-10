<?php

namespace platz1de\EasyEdit\command\defaults;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\selection\ClipBoardManager;
use platz1de\EasyEdit\selection\LinkedBlockListSelection;
use platz1de\EasyEdit\task\selection\InsertTask;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use Throwable;

class InsertCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/insert", "Insert the Clipboard", "easyedit.command.paste");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		try {
			$selection = ClipBoardManager::getFromPlayer($player->getName());
		} catch (Throwable) {
			Messages::send($player, "no-clipboard");
			return;
		}

		InsertTask::queue(new LinkedBlockListSelection($player->getName(), $player->getWorld()->getFolderName(), $selection), $player->getPosition());
	}

	/**
	 * @return CommandParameter[][]
	 */
	public function getCommandOverloads(): array
	{
		return [];
	}
}