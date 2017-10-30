<?php
declare(strict_types=1);
namespace jasonwynn10\SZ;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\Generator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class SpectreZoneGenerator extends Generator {
	/** @var ChunkManager $level */
	protected $level;
	/** @var string[] $settings */
	protected $settings;
	/** @var int $width */
	protected $width = 25;
	/** @var int $height */
	protected $height = 25;
	/** @var int $separation */
	protected $separation = 100;

	const ZONE = 0;
	const SEPARATION = 1;

	/**
	 * SpectreZoneGenerator constructor.
	 * @param array $settings
	 */
	public function __construct(array $settings = []) {
		parent::__construct($settings);
		$this->settings = $settings;
		if(array_key_exists("width", $settings["preset"])) {
			$this->width = $settings["preset"]["width"];
		}
		if(array_key_exists("height", $settings["preset"])) {
			$this->height = $settings["preset"]["height"];
		}
		if(array_key_exists("separation", $settings["preset"])) {
			$this->separation = $settings["preset"]["separation"];
		}
	}

	/**
	 * @param ChunkManager $level
	 * @param Random $random
	 */
	public function init(ChunkManager $level, Random $random) {
		$this->level = $level;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return self::class;
	}

	/**
	 * @return Vector3
	 */
	public function getSpawn(): Vector3 {
		return new Vector3(1, 2, 1);
	}

	/**
	 * @return array
	 */
	public function getSettings(): array {
		return $this->settings;
	}
	/**
	 * @param int $x
	 * @param int $z
	 * @return \SplFixedArray
	 */
	public function getShape(int $x, int $z) {
		$totalSize = $this->width + $this->separation;
		if ($x >= 0) {
			$X = $x % $totalSize;
		} else {
			$X = $totalSize - abs($x % $totalSize);
		}
		if ($z >= 0) {
			$Z = $z % $totalSize;
		} else {
			$Z = $totalSize - abs($z % $totalSize);
		}
		$startX = $X;
		$shape = new \SplFixedArray(256);
		for ($z = 0; $z < 16; $z++, $Z++) {
			if ($Z === $totalSize) {
				$Z = 0;
			}
			if ($Z < $this->width) {
				$typeZ = self::ZONE;
			} elseif ($Z === $this->width or $Z === ($totalSize-1)) {
				$typeZ = self::SEPARATION;
			} else {
				$typeZ = self::SEPARATION;
			}
			for ($x = 0, $X = $startX; $x < 16; $x++, $X++) {
				if ($X === $totalSize)
					$X = 0;
				if ($X < $this->width) {
					$typeX = self::ZONE;
				} elseif ($X === $this->width or $X === ($totalSize-1)) {
					$typeX = self::SEPARATION;
				} else {
					$typeX = self::SEPARATION;
				}
				if ($typeX === $typeZ) {
					$type = $typeX;
				} elseif ($typeX === self::ZONE) {
					$type = $typeZ;
				} elseif ($typeZ === self::ZONE) {
					$type = $typeX;
				} else {
					$type = self::SEPARATION;
				}
				$shape[($z << 4)| $x] = $type;
			}
		}
		return $shape;
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 */
	public function generateChunk(int $chunkX, int $chunkZ) {
		$shape = $this->getShape($chunkX << 4, $chunkZ << 4);
		//TODO
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 */
	public function populateChunk(int $chunkX, int $chunkZ) {}
}