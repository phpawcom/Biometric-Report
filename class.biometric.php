<?php
/********************************************************************************
*                         Fingerprint device Report, version 1                  *
*        Applicable for IN01 A Biometric Access Control Terminal, ZKSOFTWARE    *
*                             Written By Abdulaziz Al Rashdi                    *
*                   http://www.alrashdi.co  |  https://github.com/phpawcom      *
*********************************************************************************/
class biometric {
	public $dateFormat = 'm/d/Y';
	private $adb;
	private $userRecords = array();
	private $totalHours = 0;
	public function __construct($db, $uid, $pwd){
		try {
			$this->adb = new PDO('odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ='.$db.'; Uid='.$uid.'; Pwd='.$pwd.';');
			$this->adb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->adb->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch(Exception $e){
			var_dump($e->getMessage()); exit;
		}
	}
	public function __destruct(){
		$this->adb = null;
	}
	public function readUserData($selector, $userid, array $dateRange = array()){
		if(is_object($this->adb)){
			$calc = array();
			$dateRange = count($dateRange) == 2? $this->formatDate($dateRange) : array();
			try {
				$query = $this->adb->query('SELECT CHECKINOUT.CHECKTIME, CHECKINOUT.*, USERINFO.'.$selector.', USERINFO.Name
	FROM CHECKINOUT INNER JOIN USERINFO ON CHECKINOUT.USERID = USERINFO.USERID where '.(count($dateRange) == 2? '(((CHECKINOUT.CHECKTIME)>=#'.$dateRange[0].'#) AND ((CHECKINOUT.CHECKTIME)<=#'.$dateRange[1].'#) ) AND ' : false).' USERINFO.'.$selector.' = \''.$userid.'\' ');
				foreach($query->fetchAll(PDO::FETCH_UNIQUE) as $row){
					$day = substr($row['CHECKTIME'], 0, -9);
					$dtime = substr($row['CHECKTIME'], 10);
					if(!is_array($calc[$day][$row['CHECKTYPE']])){ $calc[$day][$row['CHECKTYPE']] = array(); }
					array_push($calc[$day][$row['CHECKTYPE']], $dtime);
				}
				foreach($calc as $day => $data){
					$this->userRecords[$day] = (float) 0;
					if(is_array($data['I']) && is_array($data['O']) && count($data['I']) == count($data['O'])){
						foreach($data['I'] as $j => $in){
							$this->userRecords[$day] += strtotime($day.' '.$calc[$day]['O'][$j]) - strtotime($day.' '.$in);
						}
					}elseif(is_array($data['I']) && is_array($data['O']) && count($data['I']) > 0 &&  count($data['O']) > 0){
						$this->userRecords[$day] = strtotime($day.' '.end($calc[$day]['O'])) - strtotime($day.' '.reset($calc[$day]['I']));
					}else{
						$this->userRecords[$day] = -1;
					}
					if($this->userRecords[$day] > 0){
						$this->userRecords[$day] = $this->userRecords[$day]/3600;
						$this->totalHours += $this->userRecords[$day];
					}
				}
			}catch(Expection $e){
				var_dump($e->getMessage()); exit;
			}
		}else exit;
		return $this;
	}
	public function getDaysList(){
		return $this->userRecords;
	}
	public function getTotalHours($decimal = 2){
		return $decimal > 0? round($this->totalHours, $decimal) : $this->totalHours;
	}
	public function formatDate(array $dateRange){
		return array(date($this->$dateFormat[0], strtotime($dateRange[0])), date($this->$dateFormat[1], strtotime($dateRange[1])));
	}
}
?>
