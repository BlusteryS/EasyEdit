<?php

namespace platz1de\EasyEdit\selection;

use Closure;
use Exception;
use pocketmine\level\format\Chunk;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Utils;
use RuntimeException;

class Sphere extends Selection
{
	/**
	 * Sphere constructor.
	 * @param string       $player
	 * @param string       $level
	 * @param Vector3|null $pos1
	 * @param int|null     $radius
	 * @param bool         $piece
	 */
	public function __construct(string $player, string $level = "", ?Vector3 $pos1 = null, ?int $radius = 0, bool $piece = false)
	{
		$pos2 = new Vector3($radius); //This is not optimal, but currently needed...
		parent::__construct($player, $level, $pos1, $pos2, $piece);
	}

	/**
	 * @param Position $place
	 * @return Chunk[]
	 */
	public function getNeededChunks(Position $place): array
	{
		$radius = $this->pos2->getX();
		$chunks = [];
		//TODO: yea this is a square (change this)
		for ($x = ($this->pos1->getX() - $radius - 1) >> 4; $x <= ($this->pos1->getX() + $radius + 1) >> 4; $x++) {
			for ($z = ($this->pos1->getZ() - $radius - 1) >> 4; $z <= ($this->pos1->getZ() + $radius + 1) >> 4; $z++) {
				$this->getLevel()->loadChunk($x, $z);
				$chunks[] = $this->getLevel()->getChunk($x, $z);
			}
		}
		return $chunks;
	}

	/**
	 * @return Vector3
	 */
	public function getRealSize(): Vector3
	{
		return new Vector3($this->getRadius() * 2, $this->getRadius() * 2, $this->getRadius() * 2);
	}

	/**
	 * @return Vector3
	 */
	public function getCubicStart(): Vector3
	{
		return $this->getPos1()->subtract($this->getRadius(), $this->getRadius(), $this->getRadius());
	}

	/**
	 * @param Vector3 $place
	 * @param Closure $closure
	 * @return void
	 * @noinspection StaticClosureCanBeUsedInspection
	 */
	public function useOnBlocks(Vector3 $place, Closure $closure): void
	{
		Utils::validateCallableSignature(function (int $x, int $y, int $z): void { }, $closure);
		$radius = $this->pos2->getX();
		$radiusSquared = $radius ** 2;
		for ($x = -$radius; $x <= $radius; $x++) {
			for ($z = -$radius; $z <= $radius; $z++) {
				for ($y = -$radius; $y <= $radius; $y++) {
					if(($x ** 2) + ($y ** 2) + ($z ** 2) <= $radiusSquared){
						$closure($this->pos1->getX() + $x, $this->pos1->getY() + $y, $this->pos1->getZ() + $z);
					}
				}
			}
		}
	}

	protected function update(): void
	{
		// don't mess everything up
	}

	/**
	 * @param Vector3 $pos
	 */
	public function setPos(Vector3 $pos): void
	{
		$this->pos1 = $pos;
	}

	/**
	 * @param int $radius
	 */
	public function setRadius(int $radius): void
	{
		$this->pos2 = new Vector3($radius);
	}

	/**
	 * @return int
	 */
	public function getRadius(): int
	{
		return $this->pos2->getX();
	}

	/**
	 * @return string
	 */
	public function serialize(): string
	{
		return igbinary_serialize([
			"player" => $this->player,
			"level" => is_string($this->level) ? $this->level : $this->level->getName(),
			"minX" => $this->pos1->getX(),
			"minY" => $this->pos1->getY(),
			"minZ" => $this->pos1->getZ(),
			"maxX" => $this->pos2->getX(),
			"maxY" => $this->pos2->getY(),
			"maxZ" => $this->pos2->getZ()
		]);
	}

	public function unserialize($serialized): void
	{
		$data = igbinary_unserialize($serialized);
		$this->player = $data["player"];
		try {
			$this->level = Server::getInstance()->getLevelByName($data["level"]) ?? $data["level"];
		} catch (RuntimeException $exception) {
			$this->level = $data["level"];
		}
		$this->pos1 = new Vector3($data["minX"], $data["minY"], $data["minZ"]);
		$this->pos2 = new Vector3($data["maxX"], $data["maxY"], $data["maxZ"]);
	}
}