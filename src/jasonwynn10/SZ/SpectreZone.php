<?php
declare(strict_types=1);
namespace jasonwynn10\SZ;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\generator\Generator;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class SpectreZone extends PluginBase implements Listener {
	/** @var Config $zones */
	private $zones;
	/** @var int $levelId */
	private $levelId;

	public function onLoad() : void {
		Generator::addGenerator(SpectreZoneGenerator::class, $this->getDescription()->getName());
	}
	public function onEnable() : void {
		$this->saveDefaultConfig();
		$this->zones = new Config($this->getDataFolder()."zones.json", Config::JSON);
		foreach($this->getServer()->getLevels() as $level) {
			if($level->getProvider()->getGenerator() === "Spectre Zone") {
				$this->levelId = $level->getId();
				break;
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		//TODO: register recipe for specter key crafting
	}

	/**
	 * @priority MONITOR
	 * @ignoreCancelled false
	 *
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event) {
		if($event->getAction() === $event::RIGHT_CLICK_AIR) {
			$item = $event->getItem();
			if($item->getName() === TextFormat::BLUE."Spectre Key") {
				$tag = $item->getNamedTagEntry("valid_key");
				if($tag !== null) {
					$this->teleportPlayerToSpecter($event->getPlayer());
				}else{
					$event->getPlayer()->sendMessage(TextFormat::RED."Invalid Spectre Key Detected!");
				}
			}
		}
	}

	/**
	 * @api
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function teleportPlayerToSpecter(Player $player) : bool {
		$zoneData = $this->zones->get($player->getLowerCaseName(), null);
		if(!isset($zoneData)){
			$zoneData = $this->getNewZone();
			$this->zones->set($player->getLowerCaseName(), $zoneData);
		}
		$level = $this->getServer()->getLevel($this->levelId);
		if(!isset($level))
			return false;
		return $player->teleport(new Position($zoneData["x"], $zoneData["y"], $zoneData["z"], $level));
	}

	/**
	 * @return array|null
	 */
	private function getNewZone() : ?array {
		$zonesArr = $this->zones->getAll();
		for ($i = 0;; $i++) {
			$existing = [];
			foreach($zonesArr as $player => $data) {
				if(abs($data["x"]) === $i and abs($data["z"]) <= $i) {
					$existing[] = [$data["x"], $data["z"]];
				}elseif(abs($data["z"]) === $i and abs($data["x"]) <= $i) {
					$existing[] = [$data["x"], $data["z"]];
				}
			}
			$zones = [];
			foreach($existing as $XZ) {
				$zones[$XZ[0]][$XZ[1]] = true;
			}
			if (count($zones) === max(1, 8 * $i)) {
				continue;
			}

			if ($ret = self::findEmptyZoneSquared(0, $i, $zones)) {
				list($X, $Z) = $ret;
				return ["x" => $X, "y" => 2, "z" => $Z];
			}
			for ($a = 1; $a < $i; $a++) {
				if ($ret = self::findEmptyZoneSquared($a, $i, $zones)) {
					list($X, $Z) = $ret;
					return ["x" => $X, "y" => 2, "z" => $Z];
				}
			}
			if ($ret = self::findEmptyZoneSquared($i, $i, $zones)) {
				list($X, $Z) = $ret;
				return ["x" => $X, "y" => 2, "z" => $Z];
			}
		}
		return null;
	}

	/**
	 * @param int $a
	 * @param int $b
	 * @param array[] $zones
	 * @return array|null
	 */
	private static function findEmptyZoneSquared(int $a, int $b, array $zones) : ?array {
		if (!isset($zones[$a][$b])) return array($a, $b);
		if (!isset($zones[$b][$a])) return array($b, $a);
		if ($a !== 0) {
			if (!isset($zones[-$a][$b])) return array(-$a, $b);
			if (!isset($zones[$b][-$a])) return array($b, -$a);
		}
		if ($b !== 0) {
			if (!isset($zones[-$b][$a])) return array(-$b, $a);
			if (!isset($zones[$a][-$b])) return array($a, -$b);
		}
		if ($a | $b === 0) {
			if (!isset($zones[-$a][-$b])) return array(-$a, -$b);
			if (!isset($zones[-$b][-$a])) return array(-$b, -$a);
		}
		return null;
	}
}