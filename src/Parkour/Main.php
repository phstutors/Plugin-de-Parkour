<?php

namespace Parkour;

use _64FF00\PurePerms\PurePerms;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\level\sound\TNTPrimeSound;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener{

    /**
     * @var array
     */
    protected $damage;
    protected $blocks;
    private $posicao;

    /**
     * @param int $damage
     */
    public function setDamage(int $damage) {
        $this->damage = $damage;
    }
    public function onEnable(){

        $this->getServer()->getPluginManager()->RegisterEvents($this, $this);
        $this->getLogger()->info("Plugin de Parkour By PhsTutors");
    }


    public function onMove(PlayerMoveEvent $ev)
    {


        $p = $ev->getPlayer();
        $block = $p->getLevel()->getBlock($p->floor()->subtract(0, 1));
        $sound = new TNTPrimeSound($p);
        $item1 = Item::get(131, 0, 1)->setCustomName("§l§cRESPAWN POINT\n§r§f(Clique para voltar no ultimo SavePoint!)");
        $item2 = Item::get(421, 0, 1)->setCustomName("§l§cSAIR DO PARKOUR\n§r§f(Sair do Parkour!)");

        if ($p->getLevel()->getName() == "SkyWarsRedeSky") {

            if ($block->getId() == 87) {
                $p->getInventory()->clearAll();
                $p->getInventory()->setItem(3, $item1);
                $p->getInventory()->setItem(5, $item2);
                $p->sendPopup("§l§fINICIASSE O PARKOUR");
            } elseif ($block->getId() == 88) {

                $x = $block->getX();
                $y = $block->getY() + 2;
                $z = $block->getZ();
                $vector = new Vector3($x, $y, $z);
                $this->posicao = $vector;
                $center = $vector;
                $p->sendPopup("§l§fFIZESSE UM RESPAWN POINT");
                $particle = new HeartParticle($center, mt_rand(0, 3), mt_rand(0, 3), mt_rand(0, 3), mt_rand(0, 3));
                $level = $p->getLevel();
                for ($yaw = 3, $y = $center->y; $y < $center->y + 3; $yaw += (M_PI * 1) / 20, $y += 1 / 20) {
                    $x = sin($yaw) + $center->x;
                    $z = cos($yaw) + $center->z;
                    $particle->setComponents($x, $y, $z);
                    $level->addParticle($particle);
                }

            } elseif ($block->getId() == 121) {
                $p->sendMessage("§7[§5SkyHunters§7] §fPARABENS POR CONCLUIR O PARKOUR\nVOCÊ GANHOU ALGUMAS RECOMPENSAS!!!");
                $p->sendPopup("§5PARABÉNS!!!");
                $p->getLevel()->addSound($sound, [$p]);
                EconomyAPI::getInstance()->addMoney($p, 5);
                $Tempx = $p->getLevel()->getSafeSpawn()->getY();;
                $Tempy = $p->getLevel()->getSafeSpawn()->getY();;
                $Tempz = $p->getLevel()->getSafeSpawn()->getY();;
                $vector2 = new Vector3($Tempx, $Tempy, $Tempz);
                $this->posicao = $vector2;
                $x = $block->getX();
                $y = $block->getY() + 3;
                $z = $block->getZ();
                $vector = new Vector3($x, $y, $z);
                // Onde tem +3 voce pode diminuir para +2 ou +1 para diminuir onde vai ficar a particula quando o player completa o parkour
                $center = new Vector3($p->getX(), $p->getY() + 3, $p->getZ());
                $radius = 1;
                $particles = array(new FlameParticle($center), new HeartParticle($center), new RedstoneParticle($center), new PortalParticle($center));
                $rand = $particles[array_rand($particles)];
                $particle = $rand;

                for($a = 0; $a < 100; $a++){
                    $pitch = (mt_rand() / mt_getrandmax() - 0.7) * M_PI;
                    $yaw = mt_rand() / mt_getrandmax() * 2 * M_PI;
                    $yi = -sin($pitch);
                    $delta = cos($pitch);
                    $xi = -sin($yaw) * $delta;
                    $zi = cos($yaw) * $delta;
                    $vector = new Vector3($xi, $yi, $zi);
                    $pi = $center->add($vector->normalize()->multiply($radius));
                    $particle->setComponents($pi->x, $pi->y + 0.5, $pi->z);
                    $p->getLevel()->addParticle($particle, [$p]);
                    $sound2 = new GhastShootSound($p);
                    $p->getLevel()->addSound($sound2);
                }
                $p->addEffect(Effect::getEffect(1)->setAmplifier(2)->setDuration(9999));
                if ($p->hasPermission("parkour.racing")) {
                    $cmd = "setgroup {$p->getName()} MVP+";
                    Server::getInstance()->getCommandMap()->dispatch(new ConsoleCommandSender(), $cmd);
                } else {
                    $p->sendMessage("§7[§5SkyHunters§7] §fVoce não pode ganhar tag parkour porque você ja tem tag §5especial!!!");
                }
            }
        }
    }
public function onInteract(PlayerInteractEvent $ev){

        $hand = $ev->getPlayer()->getItemInHand();
        $p = $ev->getPlayer();
            if($hand->getId() == 131 && $hand->getCustomName() == "§l§cRESPAWN POINT\n§r§f(Clique para voltar no ultimo SavePoint!)"){
                $p->teleport($this->posicao);
            }
elseif($hand->getId() == 421 && $hand->getCustomName() == "§l§cSAIR DO PARKOUR\n§r§f(Sair do Parkour!)"){

            $p->teleport($this->getServer()->getLevelByName("SkyWarsRedeSky")->getSafeSpawn());
            $p->getInventory()->clearAll();
            $p->sendMessage("Você saiu do parkour");
            $Tempx = $p->getLevel()->getSafeSpawn()->getY();;
             $Tempy = $p->getLevel()->getSafeSpawn()->getY();;
            $Tempz = $p->getLevel()->getSafeSpawn()->getY();;
            $vector2 = new Vector3($Tempx, $Tempy, $Tempz);
            $this->posicao = $vector2;
}
}


    public function onQuit(PlayerQuitEvent $ev){
        $p = $ev->getPlayer();
        $name = $p->getName();
        if($p->hasPermission("parkour.race")){
            $cmd = "setgroup ".$p->getName()." Player";
            Server::getInstance()->getCommandMap()->dispatch(new ConsoleCommandSender(), $cmd);
        }
    }

}