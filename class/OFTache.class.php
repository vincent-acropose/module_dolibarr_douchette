<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * 
 */
class OFTache extends CommonObject {

	public $id;
	public $status;
	public $fk_user;
	public $fk_of;
	public $fk_post;
	public $total_time;

	public $table_element = "douchetteOf";

	// Status
	const OPEN = 0;
	const PARTIALCLOSE = 1;
	const FINALCLOSE = 2;
	
	public function __construct($db) {
		$this->db = $db;
		$this->total_time = 0;
	}

	public function open() {
		$this->status = self::OPEN;

		return $this->create();
	}

	public function close($type, $temps) {
		switch ($type) {
			case 1:
				$this->status = self::PARTIALCLOSE;
				if ($temps == "0:0:0") {
					if ($this->getTotalTime() == -1) {
						return -1;
					}
				}
				else {
					$temps = explode(":", $temps);
					$this->total_time = $temps[2];
					$this->total_time += $temps[1]*60;
					$this->total_time += $temps[0]*3600;
				}
				break;

			case 2:
				$this->status = self::FINALCLOSE;

				if ($temps == "0:0:0") {
					if ($this->getTotalTime() == -1 || $this->getPartialTime() == -1) {
						return -1;
					}
				}
				else {
					$temps = explode(":", $temps);
					$this->total_time = $temps[2];
					$this->total_time += $temps[1]*60;
					$this->total_time += $temps[0]*3600;
				}
				break;
			
			default:
				return -1;
				break;
		}

		$this->create();

		if ($this->status == self::FINALCLOSE) {
			$total_time = round($this->total_time/3600, "2");
			$sql = "UPDATE " . MAIN_DB_PREFIX . "asset_workstation_of SET nb_hour_real=" . $total_time . " WHERE rowid=" . $this->fk_post;

			return $this->request($sql, 1);
		}
	}

	public function setNextId() {
		$id = $this->request("SELECT MAX(rowid) AS rowid FROM ".MAIN_DB_PREFIX.$this->table_element);
		$id != -1 ? $id = $id->rowid : $this->errors = "Une erreur est survenu lors de la création de la formation: Impossible de récupérer le bon ID";

		is_null($id) ? $this->id = 1 : $this->id = $id+1;
	}

	public function create() {
		$this->setNextId();
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (rowid, fk_user, fk_of, fk_post, datec, total_time, fk_statut) VALUES (".$this->id.", ".$this->fk_user.", '".$this->fk_of."', '".$this->fk_post."', NOW(), ".$this->total_time.", ".$this->status.")";

		return $this->request($sql, 1);
	}

	public function getTotalTime() {
		$sql = "SELECT MAX(datec) datec FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE fk_of='".$this->fk_of."' AND fk_post='".$this->fk_post."' AND fk_user=".$this->fk_user." AND fk_statut=".self::OPEN;

		$result = $this->request($sql);
		if($result) {
			$datec = new DateTime(date($result->datec));
			$total_time = $datec->diff(new DateTime());
			$this->total_time += $total_time->format("%s");
			$this->total_time += $total_time->format("%i")*60;
			$this->total_time += $total_time->format("%h")*3600;
		}

		else {
			return -1;
		}
	}

	public function getPartialTime() {
		$sql = "SELECT total_time FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE fk_of='".$this->fk_of."' AND fk_post='".$this->fk_post."' AND fk_user=".$this->fk_user." AND fk_statut=".self::PARTIALCLOSE;

		$result = $this->request($sql, 0, "*");
		if($result) {
			foreach ($result as $total) {
				$this->total_time += (int)$total['total_time'];
			}
		}

		else {
			return -1;
		}
	}

	/**
	 * function request 
	 * 		$request => Requête à effectué sur la base de donnée
	 * 		$type => 0:(SELECT), 1:(INSERT, UPDATE, DELETE)
	 */
	public function request($request, $type=0, $line=1) {

		switch ($type) {
			case 0:
				$result = $this->db->query($request);

				if ($result) {
					if ($line == 1) {
						return $this->db->fetch_object($result);
					}
					else {
						return $result;
					}
				}
				else {
					return -1;
				}

				break;

			case 1:
				return $this->db->query($request);
				break;
			
			default:
				return -1;
				break;
		}

	}
}

?>