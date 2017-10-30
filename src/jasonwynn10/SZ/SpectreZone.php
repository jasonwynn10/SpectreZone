<?php
declare(strict_types=1);
namespace jasonwynn10\SZ;

use pocketmine\level\generator\Generator;
use pocketmine\plugin\PluginBase;

class SpectreZone extends PluginBase {
	public function onLoad() : void {
		Generator::addGenerator(SpectreZoneGenerator::class, $this->getDescription()->getName());
	}
	public function onEnable() : void {
		//
	}
}