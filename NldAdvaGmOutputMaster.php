<?php

/**
 * This is the model class for table "nld_adva_gm_output_master".
 *
 * The followings are the available columns in table 'nld_adva_gm_output_master':
 * @property string $id
 * @property string $sapid
 * @property string $facid
 * @property string $neid
 * @property string $loopback
 * @property string $hostname
 * @property string $gm_id
 * @property string $adva_id
 * @property string $link_no
 * @property string $seq_no
 * @property string $port_towards_adva
 * @property string $ptp_flow
 * @property integer $is_active
 * @property integer $created_by
 * @property string $created_at
 * @property integer $modified_by
 * @property string $modified_at
 */
class NldAdvaGmOutputMaster extends CActiveRecord
{
	public $select_gm_link;
	public $select_hops;
	public $topology_post;
	public $error;
	public $ptp_flow;
	public $ptp_master_host;
	public $ptp_master_seq = 0;
	public $coloag1 = 0;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return NldAdvaGmOutputMaster the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'nld_adva_gm_output_master';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('sapid, facid, neid, loopback, hostname, link_no, seq_no, device_type, loopback_300, loopback_301', 'required'),
			array('is_active, created_by, modified_by', 'numerical', 'integerOnly'=>true),
			array('sapid, facid, neid, loopback, hostname, link_no, seq_no, port_towards_adva, ptp_flow', 'length', 'max'=>100),
			array('gm_id, adva_id', 'length', 'max'=>10),
			array('id, 	directly_connected_ag1, sapid, facid, neid, loopback, hostname, gm_id, adva_id, ptp_master, link_no, seq_no, port_towards_adva, ptp_flow, master_hostname, slave_hostname, master_loopback_300,master_loopback_301,slave_loopback_300,slave_loopback_301, created_at, modified_at', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, sapid, facid, neid, loopback, hostname, gm_id, adva_id, link_no, seq_no, port_towards_adva, ptp_flow, master_hostname, is_active, created_by, created_at, modified_by, modified_at', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'sapid' => 'Sapid',
			'facid' => 'Facid',
			'neid' => 'Neid',
			'loopback' => 'Loopback',
			'hostname' => 'Ag1 MGM Hostname',
			'device_type' => 'Device Type',
			'gm_id' => 'Gm',
			'adva_id' => 'Adva',
			'ptp_master' => 'AG1 Ptp Master',
			'link_no' => 'Link No',
			'seq_no' => 'Seq No',
			'port_towards_adva' => 'Port Towards adva',
			'ptp_flow' => 'PTP flow for CSS',
			'master_hostname' => 'master_hostname',
			'is_active' => 'Is Active',
			'created_by' => 'Created By',
			'created_at' => 'Created At',
			'modified_by' => 'Modified By',
			'modified_at' => 'Modified At',
			'select_gm_link' => 'Select Links',
			'select_hops'	=> 'Select Hops'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.sapid',$this->sapid,true);
		$criteria->compare('t.facid',$this->facid,true);
		$criteria->compare('t.neid',$this->neid,true);
		$criteria->compare('t.loopback',$this->loopback,true);
		$criteria->compare('t.hostname',$this->hostname,true);
		$criteria->compare('t.device_type',$this->device_type,true);
		$criteria->compare('t.gm_id',$this->gm_id,true);
		$criteria->compare('t.adva_id',$this->adva_id,true);
		$criteria->compare('t.link_no',$this->link_no,true);
		$criteria->compare('t.seq_no',$this->seq_no,true);
		$criteria->compare('t.port_towards_adva',$this->port_towards_adva,true);
		$criteria->compare('t.ptp_flow',$this->ptp_flow,true);
		//$criteria->compare('t.is_active', 1, true);
		$criteria->compare('t.created_by',$this->created_by);
		$criteria->compare('t.created_at',$this->created_at,true);
		$criteria->compare('t.modified_by',$this->modified_by);
		$criteria->compare('t.modified_at',$this->modified_at,true);

		if($this->adva_id)
			$criteria->join = ' JOIN nld_adva_details n on n.id = '.$this->adva_id.' AND n.id = t.adva_id AND n.is_active = 1 ';
		else
			$criteria->join = ' JOIN nld_adva_details n on n.id = t.adva_id AND n.is_active = 1 ';

		$criteria->addCondition(' NOT EXISTS (SELECT 1 from ndd_asr_920_gm_outputmaster where ag2_hostname = t.hostname AND is_active = 1)');
		$criteria->addCondition('t.is_active = 1');

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function select_gm_link($post_data = false){
		if(!$post_data){
			$post_data = $_POST;
		}
		if(isset($post_data['gmNode']) && isset($post_data['nodes']) ){
			$gm_hostname = $post_data['gmNode'];
			$gm_nodes = (array)json_decode($post_data['nodes']);
			$options[''] = 'SELECT';
			foreach ($gm_nodes as $key => $value) {
				$key_explode = explode('_', $key);
				$options[$key] = '('.$key_explode[2].')'.$gm_hostname.' => '.$key_explode[1];
			}

			return $options;
		}else{
			return array();
		}
	}

	public function clocking_logic($insert_gm = false, $_model, $coloag1){
			if($insert_gm){
				$ag1_hostname_details = ClockingDelta::model()->getDeviceData("'".$this->hostname."'", true);
				
				if($ag1_hostname_details && is_array($ag1_hostname_details) && !empty($ag1_hostname_details) && isset($ag1_hostname_details[0]) && substr($this->hostname, 8, 3) == 'PAR'){
					
					$this->sapid = $ag1_hostname_details[0]['sapid'];
					$this->facid = $ag1_hostname_details[0]['facid'];
					$this->neid = $ag1_hostname_details[0]['neid'];
					$this->loopback = $ag1_hostname_details[0]['loopback'];
					$this->device_type = 'AG1';
					$gm_slave = explode('_', $this->select_gm_link);
					$this->link_no = $gm_slave[2];
					$this->seq_no = '1';
					$this->created_by = Yii::app()->session['login']['user_id'];
            		$this->created_at = date('Y-m-d');

					$this->slave_hostname = $gm_slave[1];

					if($ag1_hostname_details[0]['west_neighbour_hostname'] == $gm_slave[1]){
						$this->master_hostname = $ag1_hostname_details[0]['east_neighbour_hostname'];
					}elseif($ag1_hostname_details[0]['east_neighbour_hostname'] == $gm_slave[1]){
						$this->master_hostname = $ag1_hostname_details[0]['west_neighbour_hostname'];
					}else{
						$this->master_hostname = $ag1_hostname_details[0]['east_neighbour_hostname'];
					}

					//echo('<pre>');print_r($ag1_hostname_details[0]);print_r($gm_slave);exit();
					$ip_region = RegionDataProvider::getDeviceRegion($this->hostname);
					
					$ag1_hostname_gm_slave = ClockingDelta::model()->getDeviceData("'".$this->slave_hostname."'", true);

					try {
						//get PTP Slave_lb301 and ptp_master_lb300 FOR COLO AG1 PAIR
						$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($this->hostname, $this->sapid, $ip_region);

						if (!empty($ag1ptpLb300Lb301Arr)) {
							$this->loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
							$this->loopback_300 = $ag1ptpLb300Lb301Arr['ptp_master_lb300'];
						}// END IF

						/* For west ag1 loopback 300 & 301*/
						$ip_region = RegionDataProvider::getDeviceRegion($this->slave_hostname);

						//get PTP Slave_lb301 and ptp_master_lb300 FOR COLO AG1 PAIR
						$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($this->slave_hostname, $ag1_hostname_gm_slave[0]['sapid'], $ip_region);

						if (!empty($ag1ptpLb300Lb301Arr)) {
							$this->slave_loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
							$this->slave_loopback_300 = $ag1ptpLb300Lb301Arr['ptp_master_lb300'];
						}
						//echo('<pre>'); print_r($this->attributes); exit();
						Yii::log('', CLogger::LEVEL_ERROR, json_encode($this->attributes));
						if($this->save()){
							return true;
						}else{
							throw new Exception('AG1 ('.$this->hostname.') gm could not be saved in database');
						}// END IF
						//echo('<pre>');print_r($this->attributes);exit();
					} catch (Exception $e) {
						$this->error = $e->getMessage();
						return false;
					}// END TRY CATCH

				}elseif (substr($this->hostname, 8, 3) == 'AAR') {
					try {
						$ag2_details = $this->get_ag2_gm_details($this->hostname);
						//echo('<pre>');print_r($ag2_details);exit();
						if($ag2_details){
							$this->sapid = $ag2_details['sapid'];
							$this->facid = $ag2_details['facid'];
							$this->neid = $ag2_details['neid'];
							$this->loopback = $ag2_details['loopback'];
							$this->device_type = 'AG1';
							$gm_slave = explode('_', $this->select_gm_link);
							$this->link_no = $gm_slave[2];
							$this->seq_no = '1';
							$this->created_by = Yii::app()->session['login']['user_id'];
			        		$this->created_at = date('Y-m-d');

							$this->slave_hostname = $gm_slave[1];
							$this->master_hostname = $ag2_details['east_neighbour_hostname'];
							//$ip_region = RegionDataProvider::getDeviceRegion($this->hostname);
							$this->loopback_301 = 'a';//$ag2_details['ptp_slave_lb301'];
							$this->loopback_300 = 'a';//$ag2_details['ptp_slave_lb300'];
							$this->slave_loopback_301 = $ag2_details['ptp_slave_lb301'];
							$this->slave_loopback_300 = $ag2_details['ptp_master_lb300'];
						}// END IF
//echo "<pre>";print_r($this->attributes);exit();
						if($ag2_details && $this->save()){
							return true;
						}else{
							throw new Exception('AG1 ('.$this->hostname.') gm could not be saved in database');
						}// END IF
					} catch (Exception $e) {
						$this->error = $e->getMessage();
						return false;
					}// END TRY CATCH
				}else{
					$this->error = 'AG1 gm details not found in database';
					return false;
				}
			}else{
				$hops_selected = $_POST['NldAdvaGmOutputMaster']['select_hops'];

				$_hops = explode(' => ', $hops_selected);
				$_explode_gm_host = explode(')', $_hops[0]);
				$link_no = str_replace('(', '', $_explode_gm_host[0]);

				unset($_hops[0]);
				$hostname_in = '"'.implode('","', $_hops).'"';
				$master_slave = array();
				$ptp_master = $this->ptp_master;
				//CHelper::debug($_hops);
				$this->deactivate_ag1($hostname_in);
				
				if(!empty($_hops)){
					foreach ($_hops as $key => $value) {
						
						if(!isset($_hops[$key-1])){
							if($ptp_master){
								$master_slave[$value]['master_hostname'] = $this->ptp_master_host;
							}else{
								$master_slave[$value]['master_sapid'] = $this->sapid;
								$master_slave[$value]['master_hostname'] = $this->hostname;
							}// END IF
						}else{
							$master_slave[$value]['master_hostname'] = $_hops[$key-1];
						}// END IF

						if(isset($_hops[$key+1])){
							$master_slave[$value]['slave_hostname']  = $_hops[$key+1];	
						}else{
							$master_slave[$value]['slave_hostname'] = '';
						}
					}
				}else{
					$this->error = 'Please Select Hops';
					return false;
				}
Yii::log('', CLogger::LEVEL_ERROR, json_encode($master_slave));
				$ag1_hostname_details = ClockingDelta::model()->getDeviceData($hostname_in, true, false, $master_slave);
				
				if($ag1_hostname_details){
                                    $hopcount =0;
					foreach ($ag1_hostname_details as $key1 => $value1) {
						$value1['link_no'] = $link_no;
						if($ptp_master && $this->ptp_master_seq){
							$value1['seq_no'] = $this->ptp_master_seq + 1;
							$this->ptp_master_seq = $this->ptp_master_seq +1;
						}else{
							if(substr($this->hostname, 8, 3) == 'AAR' || $coloag1 == 1){
								$value1['seq_no'] = array_search($value1['hostname'], $_hops) + 2;
                                                                $hopcount +=2;
							}
							else{
								$value1['seq_no'] = array_search($value1['hostname'], $_hops) + 1;
                                                                $hopcount +=1;
							}// END IF
						}
						$value1['gm_id'] = $this->id;
						$value1['device_type'] = 'AG1';
						$value1['adva_id'] = $this->adva_id;
                                                $value1['ptp_flow'] = $hopcount;
						$value1['directly_connected_ag1'] = $_hops[1];
						$insert_slave_ag1[] = $value1;
					}
				}else{
					$this->error = 'AG1 details not found in database';
					return false;
				}

				try {
					
					usort($insert_slave_ag1, function($a, $b) {
					    return $a['seq_no'] - $b['seq_no'];
					});
Yii::log('', CLogger::LEVEL_ERROR, json_encode($insert_slave_ag1));
    				foreach ($insert_slave_ag1 as $key2 => $value2) {
    					$this->unsetAttributes();
    					$this->setIsNewRecord(true);
						$this->hostname = $value2['hostname']; 
						$this->sapid = $value2['sapid'];
						$this->facid = $value2['facid'];
						$this->neid = $value2['neid'];
						$this->loopback = $value2['loopback'];
						$this->link_no = $value2['link_no'];
						$this->seq_no = $value2['seq_no'];
						$this->gm_id = $value2['gm_id'];
						$this->device_type = $value2['device_type'];
						$this->adva_id = $value2['adva_id'];
						$this->ptp_master = $ptp_master;
                                                //$this->ptp_flow = $value2['ptp_flow'];
						$this->is_active = 1;
						$this->master_hostname = $value2['master_hostname'];
						$this->slave_hostname = $value2['slave_hostname'];
						$this->directly_connected_ag1 = $value2['directly_connected_ag1'];
						$this->created_by = Yii::app()->session['login']['user_id'];
            			$this->created_at = date('Y-m-d');

						$ip_region = RegionDataProvider::getDeviceRegion($this->hostname);

						//get PTP Slave_lb301 and ptp_master_lb300 FOR COLO AG1 PAIR
						$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($this->hostname, $this->sapid, $ip_region);

						if (!empty($ag1ptpLb300Lb301Arr)) {
							$this->loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
							$this->loopback_300 = $ag1ptpLb300Lb301Arr['ptp_master_lb300'];
						}

						/* For east ag1 loopback 300 & 301*/
						$ip_region = RegionDataProvider::getDeviceRegion($this->master_hostname);

						//get PTP Slave_lb301 and ptp_master_lb300 FOR COLO AG1 PAIR
						$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($this->master_hostname, $value2['master_sapid'], $ip_region);

						if (!empty($ag1ptpLb300Lb301Arr)) {
							$this->master_loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
							$this->master_loopback_300 = $ag1ptpLb300Lb301Arr['ptp_master_lb300'];
						}

						/* For west ag1 loopback 300 & 301*/
						$ip_region = RegionDataProvider::getDeviceRegion($this->slave_hostname);

						//get PTP Slave_lb301 and ptp_master_lb300 FOR COLO AG1 PAIR
						$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($this->slave_hostname, $value2['slave_sapid'], $ip_region);

						if (!empty($ag1ptpLb300Lb301Arr)) {
							$this->slave_loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
							$this->slave_loopback_300 = $ag1ptpLb300Lb301Arr['ptp_master_lb300'];
						}

						if(!$this->save()){
							throw new Exception('AG1 ('.$this->hostname.') gm could not be saved in database');
						}// END IF

					}// END FOREACH
					

                    $success = true;
                    return true;
                } catch (Exception $ex) {
                    $this->error = $ex->getMessage();
                    return false;
                }// END TRY CATCH

			}// END IF

	}// END FUNCTION

	public function get_ag2_gm_details($ag2_hostname){

		$sql = "SELECT ag2_hostname as hostname, ag2_sapid as sapid, ag2_facid as facid, 
				ag2_neid as neid, ag2_loopback as loopback, '' as link_no, 
				othr_ag2_hostname as east_neighbour_hostname , '' as west_neighbour_hostname,
				ptp_slave_lb301 as ptp_slave_lb301, ptp_master_lb300 as ptp_master_lb300
				FROM ndd_asr_920_gm_outputmaster WHERE ag2_hostname = '{$ag2_hostname}'";
		$result = Yii::app()->db->createCommand($sql)->queryRow();

		if(!empty($result)){
			return $result;
		}else{
			return false;
		}// END IF

	}// END FUNCTION
        public function update_ptp_flow_logic_new($link_no,$hopArr,$extend,$ptpextend){
            
            $connection = Yii::app()->db;
            $hop_ptp = explode("($link_no)",$hopArr);
            
            $hop_reached = explode("=>",$hop_ptp[1]);
            $hopcount =0;
                $ptpHop ='';
                if($extend!=0){
                    $getPTP = "SELECT t.id,t.ptp_flow from nld_adva_gm_output_master t where t.id='".$extend."' AND t.is_active = 1";
                    $ptpHop = $connection->createCommand($getPTP)->queryRow();
                    if(!empty($ptpHop) && $ptpHop['ptp_flow']!='')
                        $hopcount =$ptpHop['ptp_flow'];
                    
                }
                if($ptpextend!=0){
                    $getPTP = "SELECT t.id,t.ptp_flow from nld_adva_gm_output_master t where t.id='".$ptpextend."' AND t.is_active = 1";
                    $ptpHop = $connection->createCommand($getPTP)->queryRow();
                    if(!empty($ptpHop) && $ptpHop['ptp_flow']!='')
                        $hopcount =$ptpHop['ptp_flow'];
                    
                }
                //echo $extend.'='.$ptpextend.'='.$hopcount;
                
                //print_R($hop_reached);
                //exit;
                for($i=0;$i<sizeof($hop_reached);$i++){
                    $host = trim($hop_reached[$i]);                
                    if($extend==0 && $i==0){
                        //echo "if".$i."==".$host."=".$extend."<br>";
                        $result = Yii::app()->db->createCommand("SELECT id FROM ndd_asr_920_gm_outputmaster WHERE ag2_hostname = '{$host}' AND is_active = 1")->queryRow();
                        if (!empty($result)) {
                            $hopcount+=2;
                        }
                        else{
                            $hopcount+=1;
                        }
                    }
                    else {
                        //echo "if".$i."==".$host."=".$extend."<br>";
                        if($i>0){
                            $result = Yii::app()->db->createCommand("SELECT id FROM ndd_asr_920_gm_outputmaster WHERE ag2_hostname = '{$host}' AND is_active = 1")->queryRow();
                            if (!empty($result)) {
                                $hopcount+=2;
                            }
                            else{
                                $hopcount+=1;
                            }
                        }
                    }   
                    //}
                    //echo 'dd='.$hopcount.'<br>';
                    if($hopcount<=8){
                        
                        if(substr($host,8,3)=='AAR'){
                            $sql1 = "SELECT t.id from nld_adva_gm_output_master t where t.hostname='".$host."' AND t.is_active = 1";
                        }
                        else{
                            $sql1 = "SELECT t.id from nld_adva_gm_output_master t inner join ndd_ag1_outputmaster nao on nao.hostname = t.hostname 
				inner join ndd_ag1_input nai on nao.input_id = nai.id AND nai.is_active = 1 where t.hostname='".$host."' AND t.is_active = 1
				group by nai.link_no, nai.link_sequence_no";
                        }
                        
                        /*$sql1 = "SELECT t.id from nld_adva_gm_output_master t 
				inner join ndd_ag1_outputmaster nao on nao.hostname = t.hostname 
				inner join ndd_ag1_input nai on nao.input_id = nai.id 
				AND t.link_no = nai.link_no AND nai.is_active = 1
				where t.hostname='".$host."' AND t.is_active = 1
				group by nai.link_no, nai.link_sequence_no";*/
                        $id_order = $connection->createCommand($sql1)->queryRow();
                        //echo $sql1."<br>";
                        //print_R($id_order);
                        if(!empty($id_order) && $extend!=$id_order['id']){
                            if($ptpextend!=$id_order['id']){
                            //$sql = "update nld_adva_gm_output_master t set ptp_flow =$hopcount where t.link_no = ".$link_no." AND hostname='".$host."' AND t.is_active = 1 ";
                            //$connection->createCommand($upd)->queryRow();
                            
                            $sql = "update nld_adva_gm_output_master t set ptp_flow ='".$hopcount."',modified_at='".date("Y-m-d H:i:s")."' where t.id='".$id_order['id']."' ";
                            $command = $connection->createCommand($sql);
                            $command->execute();
                            //echo $sql.'<br>';
                            }
                        }
                        
                    }
                    else{
                        break;
                    }
                }   //exit;         
        }
	public function update_ptp_flow_logic($ptp_flow, $link_no){
		
		$connection = Yii::app()->db;

		if($ptp_flow == '-1'){
			//$set_a = "(select (count(1)+1) from nld_adva_gm_output_master where link_no = ".$link_no." and is_active = 1)";
			$order_by = "DESC";
		}else{
			$order_by = "ASC";
			//$set_a = 0;
		}// END IF

		$sql1 = "SELECT GROUP_CONCAT(z.id) as id_order from (SELECT t.id from nld_adva_gm_output_master t 
				inner join ndd_ag1_outputmaster nao on nao.hostname = t.hostname 
				inner join ndd_ag1_input nai on nao.input_id = nai.id 
				AND t.link_no = nai.link_no AND nai.is_active = 1
				where t.link_no = ".$link_no." AND t.is_active = 1
				group by nai.link_no, nai.link_sequence_no
				order by nai.link_sequence_no ".$order_by.") z";

		$id_order = $connection->createCommand($sql1)->queryRow();
		if(!empty($id_order) && isset($id_order['id_order'])){
			$idOrder = $id_order['id_order'];
		}else{
			return false;
		}

		$sql = "set @a = 0;
				update nld_adva_gm_output_master 
				set ptp_flow = @a:=@a+1
				where id in
				(".$idOrder.")
				order by FIELD(id, ".$idOrder.")";

        
		$command = $connection->createCommand($sql);
		$command->execute();
		//exit($sql);
	}

	public function DownloadNip($textContent, $nip_type, $nip_id = false, $completeNip = false){
		
		$sql = "SELECT t.*, n.adva_hostname, n.adva_sapid FROM nld_adva_gm_output_master t 
				JOIN nld_adva_details n on n.id = t.adva_id AND n.is_active = 1
				WHERE t.id = ".$nip_id." AND t.is_active = 1";
		$query = Yii::app()->db->createCommand($sql)->queryRow();
		$query_data = isset($query)?$query:false;
		
		if($nip_type == 'MGM' && $query_data){
			
			$criteria = new CDbCriteria;
			$criteria->select = ' id, hostname, sapid ';
			$criteria->condition = ' t.gm_id = ' .$query_data['id'].' AND t.seq_no = 2 AND t.master_hostname = "'.$query_data['hostname'].'" AND t.is_active = 1';
			$directly_connected_ag1 = $this->findAll($criteria);
	
			if(!$directly_connected_ag1){
				return false;
			}

			$ip_region = RegionDataProvider::getDeviceRegion($query_data['hostname']);
			
			$wan_ip = NddNldPtpWanIpMaster::model()->doLookupWanIpMaster($query_data['hostname'], $query_data['sapid'], $query_data['adva_hostname'], $query_data['adva_sapid'], $ip_region);
			
			if(!$wan_ip || !is_array($wan_ip) || empty($wan_ip) || !isset($wan_ip['ag1_css_ip']) || !isset($wan_ip['adva920_ip']) || empty($wan_ip['ag1_css_ip']) || empty($wan_ip['adva920_ip'])){
				return false;
			}

			$content_replace['{ag1-hostname}'] = $query_data['hostname'];
			$content_replace['{adva-hostname}'] = $query_data['adva_hostname'];
			$content_replace['{ag1-loopback-300}'] = $query_data['loopback_300'];
			$content_replace['{ag1-loopback-301}'] = $query_data['loopback_301'];
			$content_replace['{ag1-port-towards-adva}'] = $query_data['port_towards_adva'];
			$content_replace['{ptp-nld-wan-ip-ag1}'] = $wan_ip['ag1_css_ip'];
			$content_replace['{ptp-nld-wan-ip-ag1-plus1}'] = $wan_ip['adva920_ip'];
			
			$ag1_clock_input_source3 = '';
			$ip_route_from_ag1 = '';
			$interface_port_slave = '';
			
			$increament = 2;
			$directly_connected_count = count($directly_connected_ag1);
			
			if($directly_connected_count < 2 && substr($query_data['master_hostname'], 8, 3) == 'PAR'){
				
				$port_towards_master = $this->ag1_bdi_logic($query_data['hostname'], $query_data['master_hostname']);

				if(!isset($port_towards_master['is_colo'])){
					$ag1_clock_input_source3 .= 'network-clock input-source '.$increament.' interface TenGigabitEthernet'.$port_towards_master['port_towards'].PHP_EOL;
					$increament = $increament + 1;
				}
			}

			foreach ($directly_connected_ag1 as $key => $value) {
				
				$port_and_bid[$value['hostname']]  = $this->ag1_bdi_logic($query_data['hostname'], $value['hostname']);
				
				$wan_ip_logic = $this->wan_ip_logic($query_data['hostname'], $value['hostname']);

				$gm_slave_core_wan = $wan_ip_logic;
				
				$ip_region = RegionDataProvider::getDeviceRegion($value['hostname']);

				$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['hostname'], $value['sapid'], $ip_region);
				$slave_loopback = isset($ag1ptpLb300Lb301Arr['ptp_slave_lb301'])?$ag1ptpLb300Lb301Arr['ptp_slave_lb301']:'';
				/*if(empty($port_and_bid[$value['hostname']]) || empty($port_and_bid[$value['hostname']]['port_towards']) || empty($port_and_bid[$value['hostname']]['bdi_port'])){
					return false;
				}else{*/
					$ag1_clock_input_source3 .= 'network-clock input-source '.$increament.' interface TenGigabitEthernet'.$port_and_bid[$value['hostname']]['port_towards'].PHP_EOL;

					$interface_port_slave .= 'interface TenGigabitEthernet'.$port_and_bid[$value['hostname']]['port_towards'].PHP_EOL.'synchronous mode'.PHP_EOL.'!'.PHP_EOL;

					$ip_route_from_ag1 .= 'ip route '.$slave_loopback.' 255.255.255.255 '.$port_and_bid[$value['hostname']]['bdi_port'].' '.$gm_slave_core_wan.PHP_EOL;
					$increament = $increament + 1;
				//}// END IF

			}// END FOR

			$content_replace['{interface-port-slave}'] = $interface_port_slave;
			if($directly_connected_count < 2 && substr($query_data['master_hostname'], 8, 3) == 'PAR' && !isset($port_towards_master['is_colo'])){
				$content_replace['{interface-port-master}'] = PHP_EOL.'interface TenGigabitEthernet'.$port_towards_master['port_towards'].PHP_EOL.'synchronous mode'.PHP_EOL.'!'.PHP_EOL;
			}else{
				$content_replace['{interface-port-master}'] = '';
			}

			$management_ip = NddNldPtpMgmtIpMaster::model()->doLookupPtpMgmtIpv4($query_data['adva_hostname'], $query_data['adva_sapid'], $query_data['hostname'], $query_data['sapid'], $ip_region);
			
			$content_replace['{ag1-management-ip}'] = isset($management_ip['ag1_css_ip'])?$management_ip['ag1_css_ip']:'';
			$content_replace['{ag1-clock-input-source3}'] = $ag1_clock_input_source3;
			$ip_route_from_ag1 .= $this->directly_connected_css_logic($query_data['hostname']);
			$content_replace['{ip-route-from-ag1}'] = $ip_route_from_ag1;
			$content_replace['{nn-value}'] = NddNnValueConfiguration::model()->getNNValueBySapID($query_data['sapid']);
			$content_replace['{ag1-loopback0}'] = $query_data['loopback'];
			
			$nipFilename = $query_data['hostname'].'_gm_nip';
			
		}elseif($nip_type  == 'AG1-SLAVE' && $query_data){
			
			$criteria = new CDbCriteria;
			$criteria->condition = 'ptp_master = :id AND master_hostname = :hostname AND is_active = 1';
			$criteria->params = array(':hostname' => $query_data['hostname'], ':id' => $query_data['id']);

			$ptp_slave_data = $this->findAll($criteria);
			$ptp_slave_count = count($ptp_slave_data);
			
			$port_and_bid['master']  = $this->ag1_bdi_logic($query_data['hostname'], $query_data['master_hostname']);

			$content_replace['{ag1-hostname}'] = $query_data['hostname'];
			$content_replace['{ag1-loopback-300}'] = $query_data['loopback_300'];
			$content_replace['{ag1-loopback-301}'] = $query_data['loopback_301'];
			$content_replace['{ag1-port-towards-master}'] = $port_and_bid['master']['port_towards'];

			$content_replace['{bdi-btw-current-master}'] = $port_and_bid['master']['bdi_port'];
			
			$wan_ip_logic_master = $this->wan_ip_logic($query_data['hostname'], $query_data['master_hostname']);
			$master_core_wan =  $wan_ip_logic_master;
			$content_replace['{ag1-master-core-wan-ip}'] = $master_core_wan;

			if(substr($query_data['master_hostname'], 8, 3) == 'AAR'){
				$content_replace['{ip-route-from-ag1-master}'] = '';
				$content_replace['{ag1-master-300-ip}'] = $master_core_wan;
			}
			else{
				$content_replace['{ip-route-from-ag1-master}'] = PHP_EOL."ip route ".$query_data['master_loopback_300']." 255.255.255.255 ".$port_and_bid['master']['bdi_port']." ".$master_core_wan.PHP_EOL;
				$content_replace['{ag1-master-300-ip}'] = $query_data['master_loopback_300'];
			}

			/* Slave logic*/
			if($ptp_slave_count < 1 && !empty($query_data['slave_hostname'])){
				$port_and_bid['slave']  = $this->ag1_bdi_logic($query_data['hostname'], $query_data['slave_hostname']);
				$wan_ip_logic_slave = $this->wan_ip_logic($query_data['hostname'], $query_data['slave_hostname']);

				$slave_core_wan = $wan_ip_logic_slave;
				
				$content_replace['{interface-port-slave}'] = PHP_EOL."interface TenGigabitEthernet".$port_and_bid['slave']['port_towards'].PHP_EOL."synchronous mode".PHP_EOL."!".PHP_EOL;
			
				$content_replace['{ip-route-from-ag1-slave}'] = PHP_EOL."ip route ".$query_data['slave_loopback_301']." 255.255.255.255 ".$port_and_bid['slave']['bdi_port']." ".$slave_core_wan."".PHP_EOL;

				$content_replace['{network-clock-ag1-slave}'] =  PHP_EOL."network-clock input-source 2 interface TenGigabitEthernet".$port_and_bid['slave']['port_towards']."".PHP_EOL;
				
			}elseif($ptp_slave_count > 0){
				$content_replace['{interface-port-slave}'] = '';
				$content_replace['{network-clock-ag1-slave}'] = '';
				$content_replace['{ip-route-from-ag1-slave}'] = '';
				
				foreach ($ptp_slave_data as $key => $value) {
						
					$port_and_bid['slave']  = $this->ag1_bdi_logic($query_data['hostname'], $value['hostname']);
					$wan_ip_logic_slave = $this->wan_ip_logic($query_data['hostname'], $value['hostname']);

					$slave_core_wan = $wan_ip_logic_slave;

					$content_replace['{interface-port-slave}'] .= PHP_EOL."interface TenGigabitEthernet".$port_and_bid['slave']['port_towards'].PHP_EOL."synchronous mode".PHP_EOL."!".PHP_EOL;
					$ip_region = RegionDataProvider::getDeviceRegion($value['hostname']);
					$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['hostname'], $value['sapid'], $ip_region);
					if($ag1ptpLb300Lb301Arr){
						$slave_loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
					}
					$content_replace['{ip-route-from-ag1-slave}'] .= PHP_EOL."ip route ".$slave_loopback_301." 255.255.255.255 ".$port_and_bid['slave']['bdi_port']." ".$slave_core_wan."".PHP_EOL;

					$content_replace['{network-clock-ag1-slave}'] .=  PHP_EOL."network-clock input-source 2 interface TenGigabitEthernet".$port_and_bid['slave']['port_towards']."".PHP_EOL;
				
				}// END FOR

			}elseif($ptp_slave_count < 1 && empty($query_data['slave_hostname'])){
				$content_replace['{interface-port-slave}'] = '';
				$content_replace['{network-clock-ag1-slave}'] = '';
				$content_replace['{ip-route-from-ag1-slave}'] = '';
				$slave_hostname = '';
				$sapid='';
				$westsql = "select west_neighbour_hostname from ndd_ag1_input where east_neighbour_hostname='".$query_data['master_hostname']."' and hostname='".$query_data['hostname']."' and (status=1 or is_active=1)  order by id desc";
				$westquery = Yii::app()->db->createCommand($westsql)->queryRow();
				$west_query_data = isset($westquery)?$westquery:false;
				
				if($west_query_data['west_neighbour_hostname']==''){
					$eastsql = "select east_neighbour_hostname from ndd_ag1_input where west_neighbour_hostname='".$query_data['master_hostname']."' and hostname='".$query_data['hostname']."' and (status=1 or is_active=1) order by id desc";
					$eastquery = Yii::app()->db->createCommand($eastsql)->queryRow();
					$east_query_data = isset($eastquery)?$eastquery:false;
					$slave_hostname = $east_query_data['east_neighbour_hostname'];
				}else{
					$slave_hostname = $west_query_data['west_neighbour_hostname'];
				}
				$sapid = CommonUtility::getSapid($slave_hostname);
				$port_and_bid['slave']  = $this->ag1_bdi_logic($query_data['hostname'], $slave_hostname);
				if($port_and_bid['slave']['port_towards']!=""){
					$wan_ip_logic_slave = $this->wan_ip_logic($query_data['hostname'], $slave_hostname);
					$slave_core_wan = $wan_ip_logic_slave;
					$content_replace['{interface-port-slave}'] .= PHP_EOL."interface TenGigabitEthernet".$port_and_bid['slave']['port_towards'].PHP_EOL."synchronous mode".PHP_EOL."!".PHP_EOL;
					$ip_region = RegionDataProvider::getDeviceRegion($slave_hostname);
                                        if(substr($slave_hostname,8,3)!='AAR' && substr($slave_hostname,8,3)!='CCR'){
                                            $ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($slave_hostname, $sapid, $ip_region);
                                            if($ag1ptpLb300Lb301Arr){
                                                    $slave_loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
                                            }
                                            $content_replace['{ip-route-from-ag1-slave}'] .= PHP_EOL."ip route ".$slave_loopback_301." 255.255.255.255 ".$port_and_bid['slave']['bdi_port']." ".$slave_core_wan."".PHP_EOL;
                                        }
                                        $content_replace['{network-clock-ag1-slave}'] .=  PHP_EOL."network-clock input-source 2 interface TenGigabitEthernet".$port_and_bid['slave']['port_towards']."".PHP_EOL;
				}else{
					$content_replace['{interface-port-slave}'] = '';
					$content_replace['{network-clock-ag1-slave}'] = '';
					$content_replace['{ip-route-from-ag1-slave}'] = '';
				}	
			}else{
				$content_replace['{interface-port-slave}'] = '';
				$content_replace['{network-clock-ag1-slave}'] = '';
				$content_replace['{ip-route-from-ag1-slave}'] = '';
			}// END IF
			/* Slave logic END */

			$ip_route_slave_css = $this->directly_connected_css_logic($query_data['hostname']);
			
			$content_replace['{ip-route-from-css-slave}'] = $ip_route_slave_css;

			$nipFilename = $query_data['hostname'].'_slave_nip';
		
		}elseif($nip_type  == 'REMOVAL' && $query_data){

			$port_and_bid['master']  = $this->ag1_bdi_logic($query_data['hostname'], $query_data['master_hostname']);
			$ag1_removal_network_clock = PHP_EOL;
			$ag1_removal_network_clock_master = '';

			if($query_data['gm_id'] == 0){
				$criteria = new CDbCriteria;
				$criteria->select = ' id, hostname, sapid ';
				$criteria->condition = ' t.gm_id = ' .$query_data['id'].' AND t.seq_no = 2 AND t.master_hostname = "'.$query_data['hostname'].'" AND t.is_active = 1';
				$directly_connected_ag1 = $this->findAll($criteria);
		
				if(!$directly_connected_ag1){
					return false;
				}
				$increament = 1;
				
				$directly_connected_count = count($directly_connected_ag1);

				if($directly_connected_count < 2 && substr($query_data['master_hostname'], 8, 3) == 'PAR' && !isset($port_and_bid['master']['is_colo']) ){
				
					$ag1_removal_network_clock_master = "no network-clock input-source ".$increament." interface TenGigabitEthernet".$port_and_bid['master']['port_towards'].PHP_EOL;
					$increament = $increament + 1;
				}

				foreach ($directly_connected_ag1 as $key => $value) {
					
					$port_and_bid['slave']  = $this->ag1_bdi_logic($query_data['hostname'], $value['hostname']);
					$ag1_removal_network_clock .= "no network-clock input-source ".$increament." interface TenGigabitEthernet".$port_and_bid['slave']['port_towards'].PHP_EOL;
					$increament = $increament + 1;
				}// END FOR

			}else{
				$criteria = new CDbCriteria;
				$criteria->condition = 'ptp_master = :id AND master_hostname = :hostname AND is_active = 1';
				$criteria->params = array(':hostname' => $query_data['hostname'], ':id' => $query_data['id']);

				$ptp_slave_data = $this->findAll($criteria);
				$ptp_slave_count = count($ptp_slave_data);

				/* Slave logic*/
				if($ptp_slave_count < 1 && !empty($query_data['slave_hostname'])){
					$port_and_bid['slave']  = $this->ag1_bdi_logic($query_data['hostname'], $query_data['slave_hostname']);
					$ag1_removal_network_clock = PHP_EOL."no network-clock input-source 2 interface TenGigabitEthernet".$port_and_bid['slave']['port_towards'].PHP_EOL;
				}elseif($ptp_slave_count > 0){
					$ag1_removal_network_clock = PHP_EOL;
					foreach ($ptp_slave_data as $key => $value) {
						$port_and_bid['slave']  = $this->ag1_bdi_logic($query_data['hostname'], $value['hostname']);
						$ag1_removal_network_clock .= "no network-clock input-source 2 interface TenGigabitEthernet".$port_and_bid['slave']['port_towards'].PHP_EOL;
					}
				}else{
					$ag1_removal_network_clock = '';
				}// END IF
				$ag1_removal_network_clock_master = "no network-clock input-source 1 interface TenGigabitEthernet".$port_and_bid['master']['port_towards'].PHP_EOL;
				/* SLave logic END*/
				
			}	

			//$content_replace['{ag1-port-towards-master}'] = $port_and_bid['master']['port_towards'];
			$content_replace['{ag1-removal-network-clock-master}'] = $ag1_removal_network_clock_master;//"no network-clock input-source 1 interface ".$port_and_bid['master']['port_towards'];
			$content_replace['{ag1-removal-network-clock}'] = $ag1_removal_network_clock;

			$nipFilename = $query_data['hostname'].'_removal_nip';
		}else{
			exit('No Data Found');
		}
		
		$finalNipString = strtr($textContent, $content_replace);
		$docType = 'TXT';
                
                if($completeNip)
                {
                    return $finalNipString;
                }
                
                $finalNipString = str_replace('{BLOCK-SEPARATOR}', '', $finalNipString);

		CommonUtility::downloadNipFile($nipFilename, $finalNipString, $docType);

	}// END FUNCTION

	public function directly_connected_css_logic($ag1_hostname){
		$sql = 'SELECT o.enode_b_sapid, o.host_name, o.css_ring_id 
				FROM ndd_output_master o
				JOIN ndd_request_master r on r.request_id = o.request_id and o.enode_b_sapid = r.sapid and r.is_disabled = 0 
				WHERE (o.east_ag1_ngbr_hostname = "'.$ag1_hostname.'" or o.west_ag1_ngbr_hostname = "'.$ag1_hostname.'" ) AND o.fiber_microwave = "Fiber" AND o.ring_spur_type = "Complete Ring" ';
		$ip_route_from_ag1 = '';
		
		$resultSet = Yii::app()->db->createCommand($sql)->queryAll();
		/* For east ag1 loopback 300 & 301*/
		$ip_region = RegionDataProvider::getDeviceRegion($ag1_hostname);

		if($resultSet){
			$ip_route_from_ag1 .= PHP_EOL;
			foreach ($resultSet as $key => $value) {
				$wan_ip_logic_slave = $this->wan_ip_logic_css($ag1_hostname, $value['host_name']);

				//get PTP Slave_lb301 and ptp_master_lb300 FOR COLO AG1 PAIR
				$ag1ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['host_name'], $value['enode_b_sapid'], $ip_region);

				if (!empty($ag1ptpLb300Lb301Arr)) {
					$slave_loopback_301 = $ag1ptpLb300Lb301Arr['ptp_slave_lb301'];
				}else{
					$slave_loopback_301 = '';
				}

				//	Directly connected css and having port migration
				//	Has port connectivity instead of BDI connectivity
				//	Logic by Abhilash

				$cssringMigrateData = commonUtility::getCssRingMigrationData($ag1_hostname, $value['css_ring_id']);
				$cssPort = '';

				if (isset($cssringMigrateData['ring_id']) && !empty($cssringMigrateData['ring_id'])) {
					if ($cssringMigrateData['east_ag1'] == $ag1_hostname) $cssPort = $cssringMigrateData['east_port'];
					elseif ($cssringMigrateData['west_ag1'] == $ag1_hostname) $cssPort = $cssringMigrateData['west_port'];
				}
				else {
					$css_bdi = $this->css_bdi_logic($ag1_hostname, $value['host_name']);
					if($css_bdi['bdi_port']){
						$cssPort = "BDI".$css_bdi['bdi_port'];
					}
				}

				if ($cssPort != '') {
					$ip_route_from_ag1 .= 'ip route '.$slave_loopback_301.' 255.255.255.255 '.$cssPort.' '.$wan_ip_logic_slave.PHP_EOL;
				}
			}// END FOREACH
		}// END IF
		return $ip_route_from_ag1;
	}
       
    public function getGmData(){
        $sql= "SELECT id, sapid, hostname, gm_id, seq_no FROM ".$this->tableName()." WHERE  is_active = 1";
    
        $resultSet = Yii::app()->db->createCommand($sql)->queryAll();
        return $resultSet;
    }

    public function ag1_bdi_logic($ag1_hostname, $neighbour_ag1_host){
    	$criteria = new CDbCriteria;
	    $criteria->alias = "naom";
	    //$criteria->select = "nddAg1Input.east_interface, nddAg1Input.west_interface , nddAg1Input.east_neighbour_hostname, nddAg1Input.west_neighbour_hostname, nddAg1Input.hostname, nddAg1Input.router_type2 ";
	    //$criteria->join = "INNER JOIN ndd_ag1_input AS nddAg1Input ON (naom.input_id = nddAg1Input.id AND nddAg1Input.is_active = 1)";
	    $criteria->with = "nddAg1Input";
	    $criteria->condition = "nddAg1Input.hostname=:ag1_hostname AND naom.input_id = nddAg1Input.id AND nddAg1Input.is_active = 1";
	    $criteria->params = array(":ag1_hostname" =>$ag1_hostname);
	    $criteria->order = "naom.id ASC";
	    $results = NddAg1Outputmaster::model()->findAll($criteria);

	    $return_array['port_towards'] = '';
	    $return_array['bdi_port'] = '';
	    
	    $coloag1 = false;
	    $colo_port = false;
	    $neighbour_self_port = false;

	    $criteria1 = new CDbCriteria;
	    $criteria1->alias = "naom";
	    //$criteria1->select = "nddAg1Input.router_type2, nddAg1Input.east_interface, nddAg1Input.west_interface, nddAg1Input.east_neighbour_hostname, nddAg1Input.west_neighbour_hostname, naom.hostname";
	    //$criteria1->join = "INNER JOIN ndd_ag1_input AS nai ON (naom.input_id = nai.id AND nai.is_active = 1)";
	    $criteria1->with = "nddAg1Input";
	    $criteria1->condition = "nddAg1Input.hostname=:ag1_hostname";
	    $criteria1->params = array(":ag1_hostname" =>$neighbour_ag1_host);
	    $criteria1->order = "naom.id ASC";
	    $results1 = NddAg1Outputmaster::model()->findAll($criteria1);
		
		if (!empty($results1)) 
	    {
	        foreach ($results1 as $record1) 
	        {
	        	
	        	if(substr(trim($record1->nddAg1Input->router_type2), 0, 5) == 'AG1-C')
	        	{
	        		$coloag1 = true;
	        	}

	        	if($ag1_hostname == $record1->nddAg1Input->east_neighbour_hostname){
	        		
	        		if($coloag1){
	        			$colo_port = substr($record1->nddAg1Input->east_interface, 5);	
	        		}else{
	        			$neighbour_self_port = substr($record1->nddAg1Input->east_interface, 5);
	        		}
	        	}elseif ($ag1_hostname == $record1->nddAg1Input->west_neighbour_hostname) {
	        		if($coloag1){
	        			$colo_port = substr($record1->nddAg1Input->west_interface, 5);
	        		}else{
	        			$neighbour_self_port = substr($record1->nddAg1Input->west_interface, 5);
	        		}
	        	}

	        }// END FOREACH
	    }// END IF


	    if (!empty($results)) 
	    {
	        foreach ($results as $record) 
	        {
	        	if(substr(trim($record->nddAg1Input->router_type2), 0, 5) == 'AG1-C'){
	        		$coloag1 = true;
	        		$return_array['is_colo'] = 1;
	        	}
                        
	        	if($neighbour_ag1_host == $record->nddAg1Input->east_neighbour_hostname){
                            //commented for port towards ag1 issue
                            /*if($record->east_ngbr_core_bdi==""){
                                continue;
                            }*/
                            $return_array['port_towards'] = substr($record->nddAg1Input->east_interface, 5);
                            if($coloag1 && $colo_port){
                                            $self_port = $colo_port;
                            }elseif($neighbour_self_port && $neighbour_self_port != $return_array['port_towards'] && ($neighbour_self_port == '0/2/0' || $return_array['port_towards'] == '0/2/0')){
                                    $self_port = '0/2/0';
                            }else{
                                    $self_port = $return_array['port_towards'];	
                            }
                            if(isset($record->east_ngbr_core_bdi) && !empty($record->east_ngbr_core_bdi)){
                                if($record->east_ngbr_core_bdi!='')
                                    $return_array['bdi_port'] = "BDI".$record->east_ngbr_core_bdi;
                            }else{
                                $return_array['bdi_port'] = $this->findAG1BDI($self_port, $coloag1);
                            }
                            //$return_array['bdi_port'] = $this->findAG1BDI($self_port, $coloag1);
	        	}elseif ($neighbour_ag1_host == $record->nddAg1Input->west_neighbour_hostname) {
                            //commented for port towards ag1 issue
                            /*if($record->west_ngbr_core_bdi==""){
                                continue;
                            }*/
                            $return_array['port_towards'] = substr($record->nddAg1Input->west_interface, 5);

                            if($coloag1 && $colo_port){
                                            $self_port = $colo_port;
                            }elseif($neighbour_self_port && $neighbour_self_port != $return_array['port_towards'] && ($neighbour_self_port == '0/2/0' || $return_array['port_towards'] == '0/2/0')){
                                    $self_port = '0/2/0';
                            }else{
                                    $self_port = $return_array['port_towards'];	
                            }
                            if(isset($record->west_ngbr_core_bdi) && !empty($record->west_ngbr_core_bdi)){
                                if($record->west_ngbr_core_bdi!='')
                                    $return_array['bdi_port'] = "BDI".$record->west_ngbr_core_bdi;
                            }else{
                                $return_array['bdi_port'] = $this->findAG1BDI($self_port, $coloag1);
                            }
//	        		$return_array['bdi_port'] = $this->findAG1BDI($self_port, $coloag1);
	        	}

	        }// END FOREACH

	    }// END IF

	    return $return_array;

    }// END FUNCTION

    public function css_bdi_logic($ag1_hostname, $neighbour_css_host){
    	/*
    		Date - 2017-08-01
			changed in bdi port as per port change logic implemented in infra nip
			Logic - BDI port is decided based on the port change logic in table tbl_ag1_slot_changed
		*/
    	$criteria = new CDbCriteria;
	    $criteria->select = "east_ag1_hostname, west_ag1_hostname, e_ngbr_remport, w_ngbr_remport";
	    $criteria->condition = "host_name=:ag1_hostname AND fiber_microwave = 'Fiber' AND ring_spur_type = 'Complete Ring'";
	    $criteria->join = 'INNER JOIN ndd_request_master n on n.sapid = enode_b_sapid and n.is_disabled = 0';
	    $criteria->params = array(":ag1_hostname" =>$neighbour_css_host);
	    $results = NddOutputMaster::model()->findAll($criteria);

	    $return_array = array('port_towards' => '', 'bdi_port' => '');
	    if($results){
	    	foreach ($results as $key => $value) {
	    		if($value['east_ag1_hostname'] == $ag1_hostname){
					$port = explode('/',$value['e_ngbr_remport']);
					$res = preg_match("/[0-9]+/", $port[0],$result);
					array_shift($port);
					$return_array['port_towards'] = $result[0].'/'.implode('/',$port);
	    			//$return_array['port_towards'] = substr($value['e_ngbr_remport'], 15);
	    			//$return_array['bdi_port'] = $this->findCSSBDI($return_array['port_towards']););
	    			$bdi_port = $this->findCSSBDI($return_array['port_towards']);
	    			$bdi_port = str_replace('BDI', '', $bdi_port);
	    			$return_array['bdi_port'] = CommonUtility::getvlanforchangedport($bdi_port, $ag1_hostname, $neighbour_css_host);

	    		}elseif($value['west_ag1_hostname'] == $ag1_hostname){
	    			//$return_array['port_towards'] = substr($value['w_ngbr_remport'], 15);
					$port = explode('/',$value['w_ngbr_remport']);
					$res = preg_match("/[0-9]+/", $port[0],$result);
					array_shift($port);
					//echo '<<'.implode('/',$port).'/'.$result[0].'<br>';
					$return_array['port_towards'] = $result[0].'/'.implode('/',$port);
					//$return_array['bdi_port'] = $this->findCSSBDI($return_array['port_towards']);
					$bdi_port = $this->findCSSBDI($return_array['port_towards']);
	    			$bdi_port = str_replace('BDI', '', $bdi_port);
	    			$return_array['bdi_port'] = CommonUtility::getvlanforchangedport($bdi_port, $ag1_hostname, $neighbour_css_host);
	    		}// END IF
	    	}// END FOR
	    }// END IF
	    return $return_array;
    }

    public function ag2_port_logic($ag1_hostname, $ag2_hostname){

    	$criteria = new CDbCriteria;
	    $criteria->select = "ag2_interface";
	    $criteria->condition = "ag1_hostname=:ag1_hostname AND ag2_hostname=:ag2_hostname";
	    $criteria->params = array(":ag1_hostname" => $ag1_hostname, ":ag2_hostname" => $ag2_hostname);

	    $results = NddAg1Ag2Ports::model()->findAll($criteria);
    	
    	if($results){
    		return array('port_towards' => $results[0]['ag2_interface']);
    	}

    	return array('port_towards' => '');
    }

    public function deactivate_ag1($hostname_in){
    	$sql = 'UPDATE  nld_adva_gm_output_master set is_active = 0  where hostname in ('.$hostname_in.')';
        
        $update1 = Yii::app()->db->createCommand($sql);
        $update1->execute();
    	
    }// END FUNCTION

    public function ptp_flow_logic(){
		
		
		$hostname_in = "'".implode("', '", $_POST['selected_host'])."'";

		$sql = "SELECT nao.hostname, nagm.ptp_flow 
				from ndd_ag1_outputmaster nao
				join ndd_ag1_input nai on nao.input_id = nai.id AND nao.link_no = nai.link_no AND nai.is_active = 1 
				LEFT JOIN nld_adva_gm_output_master nagm on nagm.hostname = nao.hostname and nagm.is_active = 1
				where nao.link_no = ".$_POST['link_no']."
				order by nai.link_sequence_no";

		$resultSet = Yii::app()->db->createCommand($sql)->queryAll();

		if($resultSet && is_array($resultSet) && empty($resultSet)){
			return false;
		}else{
			return $resultSet;
		}// END IF

    }// END IF
    
    function removeGMG($mgm){
        $connection = Yii::app()->db;
        
        $sql1 = "SELECT id, adva_id FROM ".$this->tableName()." WHERE is_active=1 AND hostname='$mgm'";
        $getMGM = $connection->createCommand($sql1);
        $mgmData = $getMGM->queryRow();
        
        $sql = "UPDATE ".$this->tableName()." SET is_active=0 WHERE hostname='$mgm'";
        $update = $connection->createCommand($sql);
        $update->execute();
        
        if(isset($mgmData['id']) && $mgmData['id'] != ''){
            $sql = "UPDATE ".$this->tableName()." SET is_active=0 WHERE gm_id='".$mgmData['id']."'";
            $update1 = $connection->createCommand($sql);
            $update1->execute();
            
            $sql = "UPDATE nld_adva_details SET is_active=0 WHERE id='".$mgmData['adva_id']."'";
            $update1 = $connection->createCommand($sql);
            $update1->execute();
        }
        return true;
    }

    function wan_ip_logic($ag1_hostname, $ag1_neig_hostname){
    	$criteria = new CDbCriteria;
	    $criteria->select = "from_host_name, to_host_name, from_addr, to_addr";
	    $criteria->condition = "((from_host_name = :ag1_hostname AND to_host_name = :ag1_neig_hostname) OR (from_host_name = :ag1_neig_hostname AND to_host_name = :ag1_hostname))";
	    $criteria->params = array(":ag1_hostname" => $ag1_hostname, ":ag1_neig_hostname" => $ag1_neig_hostname);
	    $results = NddCoreWan::model()->findAll($criteria);
	    
	    if(!empty($results)){
	    	if($ag1_hostname == $results[0]['from_host_name']){
	    		$core_wan = $results[0]['to_addr'];
	    	}elseif ($ag1_hostname == $results[0]['to_host_name']) {
	    		$core_wan = $results[0]['from_addr'];
	    	}
	    	return $core_wan;
	    }else{
	    	return false;
	    }// END IF
    }// END FUNCTION

    public function wan_ip_logic_css($ag1_hostname, $css_neig_hostname){
    	$criteria = new CDbCriteria;
	    $criteria->select = "from_host_name, to_host_name, from_addr, to_addr";
	    $criteria->condition = "((from_host_name = :ag1_hostname AND to_host_name = :css_neig_hostname) OR (from_host_name = :css_neig_hostname AND to_host_name = :ag1_hostname))";
	    $criteria->params = array(":ag1_hostname" => $ag1_hostname, ":css_neig_hostname" => $css_neig_hostname);
	    $results = NddRanWan::model()->findAll($criteria);

	    if(!empty($results)){
	    	if($ag1_hostname == $results[0]['from_host_name']){
	    		$core_wan = $results[0]['to_addr'];
	    	}elseif ($ag1_hostname == $results[0]['to_host_name']) {
	    		$core_wan = $results[0]['from_addr'];
	    	}
	    	return $core_wan;
	    }else{
	    	return false;
	    }// END IF
    }

    public function findAG1BDI($port, $is_colo = false) {
        if($is_colo){
        	switch ($port) {
	            case '0/1/0':
	                $BDI = 'BDI202';
	                break;
	            case '0/0/0':
	                $BDI = 'BDI200';
	                break;
	            case '0/2/0':
	                $BDI = 'BDI204';
	                break;
	            default:
	                $BDI = $port;
	        }
        }else{
        	$BDI = '';
	        switch ($port) {
	            case '0/1/0':
	                $BDI = 'BDI372';
	                break;
	            case '0/0/0':
	                $BDI = 'BDI370';
	                break;
	            case '0/2/0':
	                $BDI = 'BDI374';
	                break;
	            case '0/3/0':
	                $BDI = 'BDI376';
	                break;
                    case '0/4/0':
                        $BDI = 'BDI356';
                        break;
                    case '0/4/8':
                        $BDI = 'BDI370';
                        break;
                    case '0/5/8':
                        $BDI = 'BDI372';
                        break;
	            default:
	                $BDI = $port;
	        }	
        }
        
        return $BDI;
    }

    public function findCSSBDI($port) {
        $BDI = '';
        switch($port){
	        case '0/1/0':
	            $BDI = 'BDI370';
	            break;
	        case '0/0/0':
	            $BDI = $port;
	            break;
	        case '0/4/0':
	            $BDI = 'BDI356';
	            break;
	        case '0/4/1':
	            $BDI = 'BDI350';
	            break;
	        case '0/4/2':
	            $BDI = 'BDI357';
	            break;
	        case '0/4/3':
	            $BDI = 'BDI355';
	            break;
	        case '0/4/4':
	            $BDI = 'BDI354';
	            break;
	        case '0/4/5':
	            $BDI = 'BDI353';
	            break;
	        case '0/4/6':
	            $BDI = 'BDI352';
	            break;
	        case '0/4/7':
	            $BDI = 'BDI351';
	            break;
	        case '0/3/0':
	            $BDI = 'BDI366';
	            break;
	        case '0/3/1':
	            $BDI = 'BDI368';
	            break;
	        case '0/3/2':
	            $BDI = 'BDI367';
	            break;
	        case '0/3/3':
	            $BDI = 'BDI365';
	            break;
	        case '0/3/4':
	            $BDI = 'BDI364';
	            break;
	        case '0/3/5':
	            $BDI = 'BDI363';
	            break;
	        case '0/3/6':
	            $BDI = 'BDI362';
	            break;
	        case '0/3/7':
	            $BDI = 'BDI361';
	            break;
	        case '0/2/0':
	            $BDI = 'BDI338';
	            break;
	        case '0/2/1':
	            $BDI = 'BDI337';
	            break;
	        case '0/2/2':
	            $BDI = 'BDI336';
	            break;
	        case '0/2/3':
	            $BDI = 'BDI335';
	            break;
	        case '0/2/4':
	            $BDI = 'BDI334';
	            break;
	        case '0/2/5':
	            $BDI = 'BDI333';
	            break;
	        case '0/2/6':
	            $BDI = 'BDI332';
	            break;
	        case '0/2/7':
	            $BDI = 'BDI331';
	            break;
	        case '0/1/0':
	            $BDI = 'BDI328';
	            break;
	        case '0/1/1':
	            $BDI = 'BDI327';
	            break;
	        case '0/1/2':
	            $BDI = 'BDI326';
	            break;
	        case '0/1/3':
	            $BDI = 'BDI325';
	            break;
	        case '0/1/4':
	            $BDI = 'BDI324';
	            break;
	        case '0/1/5':
	            $BDI = 'BDI323';
	            break;
	        case '0/1/6':
	            $BDI = 'BDI322';
	            break;
	        case '0/1/7':
	            $BDI = 'BDI321';
	            break;
	                   
	        case '0/5/0':
	            $BDI = 'BDI348';
	            break;
	        case '0/5/1':
	            $BDI = 'BDI347';
	            break;
	        case '0/5/2':
	            $BDI = 'BDI346';
	            break;
	        case '0/5/3':
	            $BDI = 'BDI345';
	            break;
	        case '0/5/4':
	            $BDI = 'BDI344';
	            break;
	        case '0/5/5':
	            $BDI = 'BDI343';
	            break;
	        case '0/5/6':
	            $BDI = 'BDI342';
	            break;
	        case '0/5/7':
	            $BDI = 'BDI341';
	            break;
	        case '0/0/11':
				$BDI = 'BDI362';
				break;
	        default:
	            $BDI = $port ;
                        
        }
        
        return $BDI ;
    }
}
