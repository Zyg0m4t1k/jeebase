<script>
$('#div_alert').showAlert({message: '|| !! Mise à jour importante de Février 2019. Bien lire la documentation !!! ||', level: 'danger'});
</script>
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
require_once __DIR__ . '/../../../core/php/core.inc.php';

function jeebase_update() {
    $cron = cron::byClassAndFunction('jeebase', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
	foreach (jeebase::byType('jeebase', true) as $jeebase) {
		try {
			if($jeebase->getConfiguration("type") == "scenario") {
				$jeebase->remove();
				continue;
			}
			$dels = array('batterie','time');
			foreach($dels as $del) {
				$jeebaseCmd = $jeebase->getCmd(null, $del);
				if ( is_object($jeebaseCmd) ) {
					$jeebaseCmd->remove();
				}				
			}
			$cms = array('bat','frequence');
			foreach ($jeebase->getCmd('info') as $cmd) {
				if (in_array($cmd->getLogicalId(),$cms)){
					echo $cmd->getName();
					$cmd->setType('info');
					$cmd->setSubType('string');			
					$cmd->save();						
				}
			}
			$jeebase->save();
			
		} catch (Exception $e) {
		}
	}
}

function jeebase_remove() {
    $cron = cron::byClassAndFunction('jeebase', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }	
}


?>
