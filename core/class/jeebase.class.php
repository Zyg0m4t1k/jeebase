<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';
if (!class_exists('ZiBase')) {
	require_once __DIR__ . '/../../3rdparty/zibase.php';
}

class jeebase extends eqLogic {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */
	
	public static $_widgetPossibility = array('custom' => true);
	
		
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'jeebase_php';
		$return['state'] = 'nok';
		$pid = trim( shell_exec ('ps ax | grep "jeebase/3rdparty/listen.php" | grep -v "grep" | wc -l') );
		if ($pid != '' && $pid != '0') {
		  $return['state'] = 'ok';
		  
		} else {
		}
		$return['launchable'] = 'ok';
		if (config::byKey('locale_ip', 'jeebase') == '' || config::byKey('zibase_ip', 'jeebase') == '') {
		  $return['launchable'] = 'nok';
		  $return['launchable_message'] = __('Erreur de configuration', __FILE__);
		}		
		return $return;		
	}
	
	public static function deamon_start() {
	    self::deamon_stop();
		$file_path = realpath(__DIR__ . '/../../3rdparty');	
		$ip_locale = config::byKey('locale_ip', 'jeebase');
		$ip_zibase = config::byKey('zibase_ip', 'jeebase');
		$cmd = 'php ' . $file_path . '/listen.php -a ' . $ip_zibase . ' -b ' . $ip_locale;
		$result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('jeebase_php') . ' 2>&1 &');
				
		$deamon_info = self::deamon_info();	
		$i = 0;
		while ($i < 30) {
		  $deamon_info = self::deamon_info();
		  if ($deamon_info['state'] == 'ok') {
			break;
		  }
		  sleep(1);
		  $i++;
		}
		if ($i >= 30) {
		  log::add('jeebase', 'error', 'Impossible de lancer le démon de jeebase');
		  return false;
		}
	}
	
	public static function deamon_stop() {
		exec('kill $(ps aux | grep "jeebase/3rdparty/listen.php" | awk \'{print $2}\')');
		$deamon_info = self::deamon_info();
		if ($deamon_info['state'] == 'ok') {
		  sleep(1);
		  exec('kill -9 $(ps aux | grep "jeebase/3rdparty/listen.php" | awk \'{print $2}\')');
		}
		$deamon_info = self::deamon_info();
		if ($deamon_info['state'] == 'ok') {
		  sleep(1);
		  exec('sudo kill -9 $(ps aux | grep "jeebase/3rdparty/listen.php" | awk \'{print $2}\')');
		}	
		$zibase = new ZiBase(config::byKey('zibase_ip', 'jeebase'));
		$zibase->deregisterListener(config::byKey('locale_ip', 'jeebase'));	
	}	
	
    public function setInfoToJeedom($_options) {
		log::add('jeebase', 'debug',' setInfoToJeedom ');
		$eqLogic = jeebase::byLogicalId( $_options['id'],  'jeebase') ;	
		$changed = false;	
		if ( is_object($eqLogic) ) {
			$id = $_options['id'];
			$zibase = new ZiBase(config::byKey('zibase_ip', 'jeebase'));
			$info = $zibase->getSensorInfo($id);
			$eqLogic->checkAndUpdateCmd("time", $info[0]->format("d/m/Y H:i:s"));
			log::add('jeebase', 'debug',' Info sonde pour ' . $eqLogic->getName() . ' ' . print_r($info,true));
			if ($eqLogic->getConfiguration('type_sonde') == 'temperature') { 
				$eqLogic->checkAndUpdateCmd("temperature", $info[1]/10);
				$eqLogic->checkAndUpdateCmd("humidity", $info[2]); 						
			} elseif ($eqLogic->getConfiguration('type_sonde') == 'light') { 
				$eqLogic->checkAndUpdateCmd("luminosite", $info[1]);
			} elseif ($eqLogic->getConfiguration('type_sonde') == 'power') { 
				$eqLogic->checkAndUpdateCmd("powerTotal", $info[1]);
				$eqLogic->checkAndUpdateCmd("powerInstant", $info[2]);
			} elseif ($eqLogic->getConfiguration('type_sonde') == 'rain') { 
				$eqLogic->checkAndUpdateCmd("PluieInstant", $info[1]);
				$eqLogic->checkAndUpdateCmd("PluieTotale", $info[2]);
			} elseif ($eqLogic->getConfiguration('type_sonde') == 'wind') { 
				$eqLogic->checkAndUpdateCmd("vitesse", $info[1]);
				$eqLogic->checkAndUpdateCmd("orientation", $info[2]);
			}			
		} else {
			$jeebase = jeebase::byTypeAndSearhConfiguration( 'jeebase', $_options['id']);
			if ( count($jeebase) > 0) {
				foreach ($jeebase as $eq) {
					if($eq->getConfiguration("type") == "other") {
						log::add('jeebase', 'debug', 'name ' . $eq->getName());
						$cmds = $eq->getCmd();
						foreach($cmds as $cmd) {
							if($cmd->getConfiguration('id') == $_options['id']) {
								$cmd->execCmd();
								log::add('jeebase', 'debug', 'Cmd Name ' . $cmd->getName() . ' lancee. Off: ' . $eq->getConfiguration('off') . ' RAZ: ' . $eq->getConfiguration('raz'));							
//								if ($eq->getConfiguration('off') == '' && $eq->getConfiguration('raz') != '') {
//									log::add('jeebase', 'debug', 'Creation du cron');
//									$eq->setConfiguration('refresh',cron::convertDateToCron(strtotime("now") + 60 * $eq->getConfiguration('raz') +60));
//									$eq->save();
//								}							
								return;
							}
						}
					}
				}
			}			
		}
	}

	public function setStateToJeedom($_options) {
		$jeebase = jeebase::byLogicalId( $_options['id'],  'jeebase') ;
		$changed = false;
	 	if ( is_object($jeebase) ) {
			$id = $_options['id'];
			$zibase = new ZiBase(config::byKey('zibase_ip', 'jeebase'));
			if (preg_match('#^Z[A-Z][0-9]*#',$id)) {
				$id = substr($id, 1);
				$etat = $zibase->getState($id,true);
			} else {
				log::add('jeebase', 'debug',' Info module pour ' . $jeebase->getName());
				$etat = $zibase->getState($id);	
			}				
			$jeebase->checkAndUpdateCmd('etat',$etat);			
		} else {
			$jeebase = jeebase::byTypeAndSearhConfiguration( 'jeebase', $_options['id']);
			if ( count($jeebase) > 0) {
				foreach ($jeebase as $eq) {
					if($eq->getConfiguration("type") == "other") {
						log::add('jeebase', 'debug', 'name ' . $eq->getName());
						$cmds = $eq->getCmd();
						foreach($cmds as $cmd) {
							if($cmd->getConfiguration('id') == $_options['id']) {
								$cmd->execCmd();
								log::add('jeebase', 'debug', 'Cmd Name ' . $cmd->getName() . ' lancee. Off: ' . $eq->getConfiguration('off') . ' RAZ: ' . $eq->getConfiguration('raz'));							
//								if ($eq->getConfiguration('off') == '' && $eq->getConfiguration('raz') != '') {
//									log::add('jeebase', 'debug', 'Creation du cron');
//									$eq->setConfiguration('refresh',cron::convertDateToCron(strtotime("now") + 60 * $eq->getConfiguration('raz') +60));
//									$eq->save();
//								}							
								return;
							}
						}
					}
				}
			}			
		}
	}	
	
//	public static function deregislistener() {
//		$zibase = new ZiBase(config::byKey('zibase_ip', 'jeebase'));
//		$zibase->deregisterListener(config::byKey('locale_ip', 'jeebase'));			
//	}
	
	public static function pull($_eqLogic_id = null) {
		if(config::byKey('zibase_ip', 'jeebase') == "" || count(self::byType('jeebase')) == 0) {
			log::add('jeebase', 'debug',' Veuillez configurer les options. IP de la Zibase vide ou non correcte? Avez-vous synchronisé?');
			return;
		}
		$zibase = new ZiBase(config::byKey('zibase_ip', 'jeebase'));
		foreach(self::byType('jeebase') as $eqLogic) {
			if ($eqLogic->getIsEnable() != 1) {
				continue;
			}
			switch ($eqLogic->getConfiguration('type')) {
				case 'module':
					$id = $eqLogic->getConfiguration('id');
					if (preg_match('#^Z[A-Z][0-9]*#',$id)) {
						$id = substr($id, 1);
						$etat = $zibase->getState($id,true);
					} else {
						$etat = $zibase->getState($id);	
						log::add('jeebase', 'debug',' Info module pour ' . $eqLogic->getName() . ' ' . $etat);
					}				
					$eqLogic->checkAndUpdateCmd('etat',$etat);
					break;
				case 'sonde':
					$id = $eqLogic->getConfiguration('sonde_os');
					$info = $zibase->getSensorInfo($id);
					if(is_numeric($info[0]) && (int)$info[0] == $info[0]) {
						$eqLogic->checkAndUpdateCmd("time", $info[0]->format("d/m/Y H:i:s"));
					}
					log::add('jeebase', 'debug',' Info sonde pour ' . $eqLogic->getName() . ' ' . print_r($info,true));
					if ($eqLogic->getConfiguration('type_sonde') == 'temperature') { 
						$eqLogic->checkAndUpdateCmd("temperature", $info[1]/10);
						$eqLogic->checkAndUpdateCmd("humidity", $info[2]); 						
					} elseif ($eqLogic->getConfiguration('type_sonde') == 'light') { 
						$eqLogic->checkAndUpdateCmd("luminosite", $info[1]);
					} elseif ($eqLogic->getConfiguration('type_sonde') == 'power') { 
						$eqLogic->checkAndUpdateCmd("powerTotal", $info[1]);
						$eqLogic->checkAndUpdateCmd("powerInstant", $info[2]);
					} elseif ($eqLogic->getConfiguration('type_sonde') == 'rain') { 
						$eqLogic->checkAndUpdateCmd("PluieInstant", $info[1]);
						$eqLogic->checkAndUpdateCmd("PluieTotale", $info[2]);
					} elseif ($eqLogic->getConfiguration('type_sonde') == 'wind') { 
						$eqLogic->checkAndUpdateCmd("vitesse", $info[1]);
						$eqLogic->checkAndUpdateCmd("orientation", $info[2]);
					}					
					break;
				case 'sensor':
					$id = $eqLogic->getConfiguration('id');
					if (preg_match('#^Z[A-Z][0-9]*#',$id)) {
						$info = $zibase->getSensorInfo($id);
						log::add('jeebase', 'debug',' Info sensor pour ' . $eqLogic->getName() . ' ' . print_r($info,true));
						$id = substr($id, 1);
						$etat = $zibase->getState($id,true);
					} else {
						$info = $zibase->getSensorInfo($id);
						log::add('jeebase', 'debug',' Info sensor pour ' . $eqLogic->getName() . ' ' . print_r($info,true));						
						$etat = $zibase->getState($id);	
					}
					$eqLogic->checkAndUpdateCmd('etat',$etat);		
					break;					
			}
		}
	}
	
	
//	public static function cron() {
//		$eqs = jeebase::byTypeAndSearhConfiguration( 'jeebase', 'custom');
//		if(count($eqs) > 0){
//			foreach ($eqs as $jeebase) {
//				$autorefresh = $jeebase->getConfiguration('refresh');
//				log::add('jeebase', 'debug',' Cron pour ' . $jeebase->getName());
//				if ($jeebase->getIsEnable() == 1 && $jeebase->getConfiguration('type_eq') == 'custom') {
//					try {
//						$c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
//						if ($c->isDue()) {
//							log::add('jeebase', 'debug',' launch refresh ');
//							try {
//								 $cmd = $jeebase->getCmd(null , 'off');
//								 if (is_object($cmd)) {
//									 $cmd->execCmd();
//								 }							
//								
//							} catch (Exception $exc) {
//								log::add('jeebase', 'error', __('Erreur pour ', __FILE__) . $jeebase->getHumanName() . ' : ' . $exc->getMessage());
//							}
//						}
//					} catch (Exception $exc) {
//						log::add('jeebase', 'error', __('Expression cron non valide pour ', __FILE__) . $jeebase->getHumanName() . ' : ' . $autorefresh);
//					}
//				}
//			}
//		}
//	}	
	
	public function syncWithZibase($_options = false) {
        if( config::byKey('zibase_url', 'jeebase') != ''){
        	$url=config::byKey('zibase_url', 'jeebase');
        }else{
        	$url="https://zibase.net";
        }
			
		
		$parsed_json = json_decode(file_get_contents($url."/api/get/ZAPI.php?zibase=".config::byKey('zibase_id', 'jeebase')."&token=".config::byKey('zibase_token', 'jeebase')."&service=get&target=home"),true);
		$modules = $parsed_json['body']['actuators'];
		$sensors = $parsed_json['body']['sensors'];
		$sondes = $parsed_json['body']['probes'];
		$scenarios = $parsed_json['body']['scenarios'];
		
		foreach ($scenarios as $scenario) {
			$id = $scenario['id'];
			$eqLogic = jeebase::byLogicalId( 'scenario_' . $id,  'jeebase');
			if ( !is_object($eqLogic) ) {
				$eqLogic = new eqLogic();
				$eqLogic->setEqType_name('jeebase');
				$eqLogic->setLogicalId('scenario_' . $id);
				$eqLogic->setIsEnable(1);
				$eqLogic->setIsVisible(1);				
			}
			$eqLogic->setName($scenario['name']);
			$eqLogic->setConfiguration('type','scenario');
			$eqLogic->setConfiguration('id', $scenario['id']);
			$eqLogic->save();
			
			$jeebaseCmd = $eqLogic->getCmd(null, 'launch');
			if (!is_object($jeebaseCmd)) {
				$jeebaseCmd = new jeebaseCmd();
				$jeebaseCmd->setLogicalId('launch');
				$jeebaseCmd->setName(__('Lancer', __FILE__));
				$jeebaseCmd->setEqLogic_id($eqLogic->id);
			}		
			
			$jeebaseCmd->setType('action');
			$jeebaseCmd->setSubType('other');
			$jeebaseCmd->save();				
		}
		
		foreach ($modules as $module) {
			if ($module['protocol'] == 6) {
				$id = 'Z' . $module['id'];
			} else {
				$id = $module['id'];
				
			}
			
			$eqLogic = jeebase::byLogicalId( $id,  'jeebase') ;
			if ( !is_object($eqLogic) ) {	
				$eqLogic = new eqLogic();
				$eqLogic->setEqType_name('jeebase');
				$eqLogic->setIsEnable(1);
				$eqLogic->setIsVisible(1);
			}
			$eqLogic->setName($module['name']);
			$eqLogic->setConfiguration('type','module');
			$eqLogic->setConfiguration('protocole', $module['protocol']);
			$eqLogic->setConfiguration('id', $module['id']);
			$eqLogic->setLogicalId($id);
			$eqLogic->save();
			$data = array();
			$data = array("id"=> $module['id'],"protocole"=> $module['protocol']);
			$eqLogic->loadCmdFromConf($eqLogic->getConfiguration('type'),$data);			
				
			if ($module['slider'] == 1) { 
				$jeebaseCmd = $eqLogic->getCmd(null, 'slider');
				if ( !is_object($jeebaseCmd) ) {
					$jeebaseCmd = new jeebaseCmd();
					$jeebaseCmd->setName(__('Slider', __FILE__));
					$jeebaseCmd->setLogicalId('slider');
					$jeebaseCmd->setEqLogic_id($eqLogic->getId());					
				}
				$jeebaseCmd->setType('action');
				$jeebaseCmd->setSubType('slider');
				$jeebaseCmd->save();
			}
		}
		
		foreach ($sensors as $sensor) {
			if ($sensor['protocol'] == 6) {
				$id = 'Z' . $sensor['id'];
			} else {
				$id = $sensor['id'];
			}	
			$eqLogic = jeebase::byLogicalId( $id,  'jeebase') ;
			if ( !is_object($eqLogic) ) {
				$eqLogic = new eqLogic();
				$eqLogic->setEqType_name('jeebase');
				$eqLogic->setIsEnable(1);
				$eqLogic->setIsVisible(1);
				$eqLogic->setLogicalId($id);				
			}
			$eqLogic->setName($sensor['name']);
			$eqLogic->setConfiguration('type','sensor');
			$eqLogic->setConfiguration('protocole', $sensor['protocol']);
			$eqLogic->setConfiguration('id', $sensor['id']);					
			$eqLogic->save();
			$data = array();
			$data = array("id"=> $sensor['id'],"protocole"=> $sensor['protocol']);
			$eqLogic->loadCmdFromConf($eqLogic->getConfiguration('type'),$data);			
			
		}
		
		foreach ($sondes as $sonde) {
			$eqLogic = jeebase::byLogicalId( $sonde['id'],  'jeebase') ;
			if ( !is_object($eqLogic) ) {
				$eqLogic = new eqLogic();
				$eqLogic->setEqType_name('jeebase');
				$eqLogic->setIsEnable(1);
				$eqLogic->setName($sonde['name']);
				$eqLogic->setLogicalId($sonde['id']);
			}
			if($sonde['type'] == "temperature") {
				$eqLogic->setConfiguration('type','sonde');
				$eqLogic->setConfiguration('sonde_os',$sonde['id']);
				$eqLogic->setConfiguration('type_sonde','temperature');			
				$eqLogic->setCategory('heating', 1);
				$eqLogic->save();
				$eqLogic->loadCmdFromConf($sonde['type']);			
			} elseif($sonde['type'] == "light") {
				$eqLogic->setConfiguration('type','sonde');
				$eqLogic->setConfiguration('sonde_os',$sonde['id']);
				$eqLogic->setConfiguration('type_sonde','light');
				$eqLogic->setCategory('heating', 1);
				$eqLogic->save();
				$eqLogic->loadCmdFromConf($sonde['type']);			
			} elseif($sonde['type'] == "power") {
				$eqLogic->setConfiguration('type','sonde');
				$eqLogic->setConfiguration('sonde_os',$sonde['id']);
				$eqLogic->setConfiguration('type_sonde','power');
				$eqLogic->setCategory('heating', 1);
				$eqLogic->save();
				$eqLogic->loadCmdFromConf($sonde['type']);			
					
			} elseif($sonde['type'] == "rain") {
				$eqLogic->setConfiguration('type','sonde');
				$eqLogic->setConfiguration('type_sonde','rain');
				$eqLogic->setConfiguration('sonde_os',$sonde['id']);
				$eqLogic->setCategory('heating', 1);
				$eqLogic->save();
				$eqLogic->loadCmdFromConf($sonde['type']);
			} elseif($sonde['type'] == "wind") {
				$eqLogic->setConfiguration('type','sonde');
				$eqLogic->setConfiguration('sonde_os',$sonde['id']);
				$eqLogic->setConfiguration('type_sonde','wind');
				$eqLogic->setCategory('heating', 1);
				$eqLogic->setObject_id($id);
				$eqLogic->save();
				$eqLogic->loadCmdFromConf($sonde['type']);
			}
		}	
	}
	

    /*     * *********************Methode d'instance************************* */

    public function preUpdate() {

	}
	
	 public function preRemove() {
		$cron = cron::byClassAndFunction('jeebase', 'launchAction', array('eq_id' => intval($this->getId())));
		if (is_object($cron)) {
			$cron->remove();
		}	
	 }	
	  public function preSave() {
		  
	  }
	    
	  
	  
	public function loadCmdFromConf($type,$data = false) {
	
		if (!is_file(__DIR__ . '/../config/devices/' . $type . '.json')) {
			log::add('ioscloud','debug', 'no file' . $type);
			return;
		}
		$content = file_get_contents(__DIR__ . '/../config/devices/' . $type . '.json');
		
		if (!is_json($content)) {
			log::add('ioscloud','debug', 'no json content');
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$jeebaseCmd = $this->getCmd(null, $command['logicalId']);
			if ( !is_object($jeebaseCmd) ) {
				$jeebaseCmd = new jeebaseCmd();
				$jeebaseCmd->setName(__($command['name'], __FILE__));
				$jeebaseCmd->setLogicalId($command['logicalId']);
				$jeebaseCmd->setEqLogic_id($this->getId());					
			}
			if($data) {
				$command['configuration'] = $data;
			}
			utils::a2o($jeebaseCmd, $command);
			$jeebaseCmd->save();
		}
	}	  
	  
	  
	 public function postSave() {

		if($this->getConfiguration("type") == "sensor") {
			$data = array();
			$data = array("id"=> $this->getConfiguration("id"),"protocole"=> $this->getConfiguration("protocole"));
			$this->loadCmdFromConf($this->getConfiguration('type'),$data);
		 
		 }
		 if($this->getConfiguration("type") == "module") {
			$data = array();
			$data = array("id"=> $this->getConfiguration("id"),"protocole"=> $this->getConfiguration("protocole"));	
			$this->loadCmdFromConf($this->getConfiguration('type'),$data);		 
//			if ($module['slider'] == 1) { 
//				$jeebaseCmd = $eqLogic->getCmd(null, 'slider');
//				if ( !is_object($jeebaseCmd) ) {
//					$jeebaseCmd = new jeebaseCmd();
//					$jeebaseCmd->setName(__('Slider', __FILE__));
//					$jeebaseCmd->setLogicalId('slider');
//					$jeebaseCmd->setEqLogic_id($eqLogic->getId());					
//				}
//				$jeebaseCmd->setType('action');
//				$jeebaseCmd->setSubType('slider');
//				$jeebaseCmd->save();
//			}
		 }
		 
		if($this->getConfiguration("type") == "sonde") {
			$this->loadCmdFromConf($this->getConfiguration('type_sonde'));
		}
		if($this->getConfiguration("type") == "other") {
			$this->loadCmdFromConf($this->getConfiguration('type'));
		}		
		
		
	}
	 
	 
	
	public function postUpdate() {
		
		switch ($this->getConfiguration("type")) {
			case "sensor":
			case "module":$this->setLogicalId($this->getConfiguration("id"));break;
			case "sonde":$this->setLogicalId($this->getConfiguration("sonde_os"));break;
			
		}
		$this->save(true);		
		
		
		if($this->getConfiguration("type") == "other" ) {

			$jeebaseCmd = $this->getCmd(null, 'on');
			if (is_object($jeebaseCmd)) {
				$jeebaseCmd->setConfiguration('id',$this->getConfiguration("on"));
				$jeebaseCmd->save();
				
			}
			$jeebaseCmd = $this->getCmd(null, 'off');
			if (is_object($jeebaseCmd)) {
				$jeebaseCmd->setConfiguration('id',$this->getConfiguration("off"));
				$jeebaseCmd->save();
				
			}			
			
			if($this->getIsEnable() == 1) {
				$cron = cron::byClassAndFunction('jeebase', 'launchAction', array('eq_id' => intval($this->getId())));
				if (is_object($cron)) {
					$cron->remove();
				}
							
			}
		}
	}
	
	public function deleteDataZibase() {
		$eqLogics = eqLogic::byType('jeebase');
		foreach ( $eqLogics as $eqLogic) {
			$eqLogic->remove();
			
		}
	}
}

class jeebaseCmd extends cmd {
	
    public function dontRemoveCmd() {
        return true;
    }
	
	public function execute($_options = array()) {
		if ($this->getType() != 'action') {
			return;
		}	
			
		$eqLogic = $this->getEqLogic();
		if($eqLogic->getConfiguration("type") == "other") {
			switch ($this->getLogicalId()) {
				case 'on' : 
					$eqLogic->checkAndUpdateCmd('etat',1);
					break;
				case 'off' : 
					$eqLogic->checkAndUpdateCmd('etat',0);
					break;
			}
			return;			
		}
		
		$zibase = new ZiBase(config::byKey('zibase_ip', 'jeebase'));

		log::add('jeebase','debug', 'message :' .  $this->getConfiguration('id') . ' ZbAction::ON ' . ' ' . $this->getConfiguration('protocole'));
		if ($this->getLogicalId() == 'on') {
			log::add('jeebase','debug', 'message :' .  $this->getConfiguration('id') . ' ZbAction::ON ' . ' ' . $this->getConfiguration('protocole'));
			$zibase->sendCommand($this->getConfiguration('id'), ZbAction::ON, $this->getConfiguration('protocole'));
		} elseif ($this->getLogicalId() == 'off') {
			$zibase->sendCommand($this->getConfiguration('id'), ZbAction::OFF, $this->getConfiguration('protocole'));
		} elseif ($this->getLogicalId() == 'slider') {
			 $zibase->sendCommand($this->getConfiguration('id'), ZbAction::DIM_BRIGHT, $this->getConfiguration('protocole'), $_options['slider']);
		}		
    }

}

?>