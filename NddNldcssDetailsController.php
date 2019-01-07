<?php

class NddNldcssDetailsController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {

        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('admin', 'create', 'update', 'fetchRingId', 'fetchCssId', 'fetchAllCssId', 'checkcssdeltanips', 'exportDeltaNipTxt', 'exportRemovalDeltaNipTxt', 'fetchHopCnt', 'setMGMHost', 'getNipByHostname', 'GetNipByLoopback','GetHopCount'),
            //'expression' => 'CHelper::isAccess("UPLOAD_NLD_CSS") || CHelper::isAccess("AG1_LINK_MANAGER") || CHelper::isAccess("NDD_DOWNLOADER") ',
            ),
            array('allow',
                'actions' => array('regenerate'),
            //'expression' => 'CHelper::isAccess("REGEN_ASR920_NIPS")',
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * @return array of default behaviour
     */
    public function behaviors() {
        return array(
            'exportableGrid' => array(
                'class' => 'application.components.ExportableGridBehavior',
                'filename' => 'NLD_CSS' . date('Y-m-d H:i:s') . '.csv',
                'csvDelimiter' => ',', //i.e. Excel friendly csv delimiter
        ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }
    
    public function getHopCount($arr,$hopcnt){
        $newArr = array();
        $hostArr = array();
        $hopArr = array();
        $dev_hopcnt=$hopcnt;
        
        for($i=0;$i<sizeof($arr);$i++){
            $css_hostname_hop = explode('=>', $arr[$i]);
            if(substr($css_hostname_hop[1],0,3)=='MW-'){
                $hostname = substr($css_hostname_hop[1],3);
                $type='MW';
                $hostnameDtls = Yii::app()->db->createCommand('select t.microwave_hostname from ndd_output_master t left join ndd_request_master t1 
            on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where t.host_name="'.$hostname.'" and t1.is_disabled=0')->queryRow();
                $master_hostname = $hostnameDtls['microwave_hostname'];
            }
            else{
               $type='fiber';
               $hostname = $css_hostname_hop[1];
               $master_hostname ='';
            }
            //print_r($hostArr);
            if($type=='MW'){
                if(substr($master_hostname,8,3)!='PAR'){
                    $position = array_search($master_hostname,$hostArr);
                
                    if ($position !== false) {
                       $dev_hopcnt=$hopArr[$position];
                    }
                }
                else{
                    $dev_hopcnt=$hopcnt;
                }
                $dev_hopcnt+=3;
            }
            else{
                if(count($hopArr)>0)
                $dev_hopcnt=max($hopArr);
                $dev_hopcnt+=1;
            }
            $newArr[$i]['hostname'] = $hostname;
            $newArr[$i]['hopcount'] = $dev_hopcnt;
            $hostArr[$i]=$hostname;
            $hopArr[]=$dev_hopcnt;
        }
     
        return $newArr;
    }
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new NddNldcssDetails;        
        // Uncomment the following line if AJAX validation is needed
        $this->performAjaxValidation($model);

        if (isset($_POST['NddNldcssDetails'])) {
            $importData = array();
            $continue_tosave = TRUE;
            $model->attributes = $_POST['NddNldcssDetails'];
            $css_ring_idArr = explode('-', $_POST['NddNldcssDetails']['css_ring_id'],2);
            $model->css_ring = $css_ring_idArr[0];
            $model->css_ring_id = $css_ring_idArr[1];
            $model->is_active = 1;
            $model->created_by = Yii::app()->session['login']['user_id'];
            $model->created_at = date('Y-m-d');
            /*if($_POST['NddNldcssDetails']['css_ring_type']=='Incomplete Ring'){
            
            }
            else if($_POST['NddNldcssDetails']['css_ring_type']=='Microwave'){
            
            }*/
            //else if($_POST['NddNldcssDetails']['css_ring_type']=='Incomplete Ring' || $_POST['NddNldcssDetails']['css_ring_type']=='Complete Ring'){
            if($_POST['NddNldcssDetails']['css_ring_type']=='Complete Ring' || $_POST['NddNldcssDetails']['css_ring_type']=='Incomplete Ring' || $_POST['NddNldcssDetails']['css_ring_type']=='Microwave'){    
                //direction1    
                if ($_POST['NddNldcssDetails']['direction1'] != '') {
                    
                    $css_hostname_seq_Arr = explode(',', $_POST['NddNldcssDetails']['direction1']);
                    $hopcnt = $_POST['NddNldcssDetails']['hop_cnt1'];
                    $loopCnt = 0;
                    $hopcountArr = $this->getHopCount($css_hostname_seq_Arr,$hopcnt);
                    
                    foreach ($css_hostname_seq_Arr as $key => $css_seq_value) {
                        $css_hostname_hop = explode('=>', $css_seq_value);
                        $host_type = 'fiber';
                        if(substr($css_hostname_hop[1],0,3)=='MW-'){
                            $hostname = substr($css_hostname_hop[1],3);
                            $host_type = 'MW';
                            //if($loopCnt==0)
                            //$hopcnt = $hopcnt+3;                            
                            $model->css_ring_type = 'Microwave';
                        }
                        else{
                            $hostname = $css_hostname_hop[1];
                            //$hopcnt = $hopcnt+1;
                            $model->css_ring_type = $_POST['NddNldcssDetails']['css_ring_type'];
                        }
                        $hopcnt = $hopcountArr[$loopCnt]['hopcount'];
                        //$hostname = $css_hostname_hop[1];                        
                        $model->css_sequence_in_ring = $css_hostname_hop[0];
                        $model->css_hostname = $hostname;
                        $hostnameDtls = Yii::app()->db->createCommand('select t.east_ag1_ngbr_hostname,t.west_ag1_ngbr_hostname,t.host_name,t.fiber_microwave,t.enode_b_sapid,t.facid,t.gne_id,microwave_hostname,t.loopback0_ipv4 from ndd_output_master t 
                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where t.host_name="'.$hostname.'" and t1.is_disabled=0')->queryRow();
                        //$hostnameDtls = CommonUtility::fetchhostnameDtls($model->css_hostname, 'CSS');
                        $model->css_sapid = $hostnameDtls['enode_b_sapid'];
                        $model->css_facid = $hostnameDtls['facid'];
                        $model->css_neid = $hostnameDtls['gne_id'];
                        if($hostnameDtls['fiber_microwave']=='Microwave'){
                            $model->css_master = $hostnameDtls['microwave_hostname'];
                        }
                        else{
                            $model->css_master = $hostnameDtls['east_ag1_ngbr_hostname'];
                        }
                        if($hopcnt<=8)
                            $model->pdf_done = 1;
                        else
                            $model->pdf_done = 0;
                        $model->css_pair = $_POST['NddNldcssDetails']['css_ring_id'];
                        //print_r($hostnameDtls);
                        //exit;
                        $model->css_loopback = $hostnameDtls['loopback0_ipv4'];                        
                        //get ptp lb details for css
                        $cssPTPDetails = $this->getCssPTPDetails($model->css_hostname, $model->css_sapid);
                        $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                        $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                        
                        $masterhostnameDtls = CommonUtility::fetchhostnameDtls($model->css_master, 'CSS');
                        $mastercssPTPDetails = $this->getCssPTPDetails($model->css_master, $masterhostnameDtls['sapid']);
                        $model->css_master_lb301 = $masterhostnameDtls[0];
                        $model->css_master_lb300 = $masterhostnameDtls[1];
                        
                        //$PTP_flow_direction = $this->getPTPflow($model->east_ag1_hostname, $model->west_ag1_hostname, $model->css_ring, $model->css_ring_id);
                        $model->css_ptp_direction = 'E';
                        $model->css_region = $cssPTPDetails[2];
                        $model->nip_type = 'W';
                        $model->hopcount = $hopcnt;
                        $model->split = 'yes';
                        $result1 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model->css_hostname}' AND to_host_name = '{$model->css_master}' ")->queryRow();
                        if (!empty($result1)) {
                            $model->css_master_ranip = $result1['to_addr'];
                        }
                        $result2 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$model->css_master}' AND to_host_name = '{$model->css_hostname}' ")->queryRow();

                        if (!empty($result2)) {
                            $model->css_master_ranip = $result2['from_addr'];
                        }
                        $importData[] = $model->getAttributes();
                        $loopCnt++;
                    }
                }
                if ($_POST['NddNldcssDetails']['direction2'] != '') {
                    $css_hostname_seq_Arr = explode(',', $_POST['NddNldcssDetails']['direction2']);
                    $hopcnt = $_POST['NddNldcssDetails']['hop_cnt2'];
                    $loopCnt = 0;
                    $hopcountArr = $this->getHopCount($css_hostname_seq_Arr,$hopcnt);
                    foreach ($css_hostname_seq_Arr as $key => $css_seq_value) {
                        $css_hostname_hop = explode('=>', $css_seq_value);
                        $host_type = 'fiber';
                        if(substr($css_hostname_hop[1],0,3)=='MW-'){
                            $hostname = substr($css_hostname_hop[1],3);
                            $host_type = 'MW';
                            $model->css_ring_type = 'Microwave';
                            //$hopcnt = $hopcnt+3;
                        }
                        else{
                            $hostname = $css_hostname_hop[1];
                            $model->css_ring_type = $_POST['NddNldcssDetails']['css_ring_type'];
                            //$hopcnt = $hopcnt+1;
                        }
                        $hopcnt = $hopcountArr[$loopCnt]['hopcount'];
                        //$hopcnt = $css_hostname_hop[0];
                        $model->css_sequence_in_ring = $css_hostname_hop[0];
                        $model->css_hostname = $hostname;
                        //$hostnameDtls = CommonUtility::fetchhostnameDtls($model->css_hostname, 'CSS');
                        $hostnameDtls = Yii::app()->db->createCommand('select t.east_ag1_ngbr_hostname,t.west_ag1_ngbr_hostname,t.host_name,t.loopback0_ipv4,t.ring_spur_type,t.fiber_microwave,t.enode_b_sapid,t.facid,t.gne_id,microwave_hostname from ndd_output_master t 
                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where t.host_name="'.$hostname.'" and t1.is_disabled=0')->queryRow();
                        $model->css_sapid = $hostnameDtls['enode_b_sapid'];
                        $model->css_facid = $hostnameDtls['facid'];
                        $model->css_neid = $hostnameDtls['gne_id'];
                        if($hostnameDtls['fiber_microwave']=='Microwave'){
                            $model->css_master = $hostnameDtls['microwave_hostname'];
                        }
                        else{
                            if(strtolower($hostnameDtls['ring_spur_type'])=='spur')
                            $model->css_master = $hostnameDtls['east_ag1_ngbr_hostname'];
                            else
                            $model->css_master = $hostnameDtls['west_ag1_ngbr_hostname'];
                        }
                        if($hopcnt<=8)
                            $model->pdf_done = 1;
                        else
                            $model->pdf_done = 0;
                        $model->css_pair = $_POST['NddNldcssDetails']['css_ring_id'];
                        $model->css_loopback = $hostnameDtls['loopback0_ipv4'];
                        
                        //get ptp lb details for css
                        $cssPTPDetails = $this->getCssPTPDetails($model->css_hostname, $model->css_sapid);
                        
                        $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                        $model->css_ptp_master_lb300 = $cssPTPDetails[1];                       
                        
                        
                        $masterhostnameDtls = CommonUtility::fetchhostnameDtls($model->css_master, 'CSS');
                        $mastercssPTPDetails = $this->getCssPTPDetails($model->css_master, $masterhostnameDtls['sapid']);
                        $model->css_master_lb301 = $masterhostnameDtls[0];
                        $model->css_master_lb300 = $masterhostnameDtls[1];
                        
                        //$PTP_flow_direction = $this->getPTPflow($model->east_ag1_hostname, $model->west_ag1_hostname, $model->css_ring, $model->css_ring_id);
                        $model->css_ptp_direction = 'W';
                        $model->css_region = $cssPTPDetails[2];
                        $model->nip_type = 'W';
                        $model->hopcount = $hopcnt;
                        $model->split = 'yes';
                        $result1 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model->css_hostname}' AND to_host_name = '{$model->css_master}' ")->queryRow();
                        if (!empty($result1)) {
                            $model->css_master_ranip = $result1['to_addr'];
                        }
                        $result2 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$model->css_master}' AND to_host_name = '{$model->css_hostname}' ")->queryRow();

                        if (!empty($result2)) {
                            $model->css_master_ranip = $result2['from_addr'];
                        }
                        $importData[] = $model->getAttributes();
                        $loopCnt++;
                    }
                } 
            }

            if (!$model->validate()) {
                $validationErrors[] = $model->getErrors();
                $continue_tosave = false;
            }

            if (!empty($importData) && $continue_tosave) {
                $transaction = $model->getDbConnection()->beginTransaction();
                $count = 0;
                try {
                    $query = $model->commandBuilder->createMultipleInsertCommand($model->tableName(), $importData);
                    $query->execute();
                    $transaction->commit();
                    Yii::app()->user->setFlash('success', "Request submitted successfully.");
                    $this->redirect('admin', array('model' => $model));
                } catch (Exception $ex) {
                    $transaction->rollback();
                    echo "Status: Failed\n";
                    throw $ex;
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function getMasterSlaveLb($model, $type) {
        $css_ptp_direction = $model->css_ptp_direction;
        $maxval = $regiondata = '';
        $portHost = '';

        $res = Yii::app()->db->createCommand("Select * from ndd_nldcss_details where css_ring_id = '{$model->css_ring_id}' AND is_active = 1 AND nip_type = '{$model->nip_type}'")->queryAll();
        foreach ($res as $val) {
            $maxSeq[] = $val['css_sequence_in_ring'];
        }

        if ($css_ptp_direction == "E") {
            $maxval = max($maxSeq);
        } elseif ($css_ptp_direction == "W") {
            $maxval = min($maxSeq);
        }


        $regiondata = RegionDataProvider::getDeviceRegion($model['css_hostname']);

        $result = Yii::app()->db->createCommand("SELECT 
                                                east_ag1_ngbr_hostname,
                                                west_ag1_ngbr_hostname,
                                                east_ag1_ngbr_sapid,
                                                west_ag1_ngbr_sapid,
                                                enode_b_sapid
                                            FROM
                                                ndd_output_master t
                                                    INNER JOIN
                                                ndd_request_master tr ON (t.request_id = tr.request_id
                                                    AND t.enode_b_sapid = tr.sapid)
                                                    AND tr.is_disabled = 0
                                            WHERE
                                                t.host_name = '{$model['css_hostname']}'")->queryRow();

        if (!empty($result)) {
            if ($css_ptp_direction == 'E') {

                if ($type == 'i') {
                    $masterSlaveLb['X/X'] = '0/0/12';
                    $masterSlaveLb['X/Y'] = '0/0/13';
                } else {
                    $masterSlaveLb['X/X'] = '0/0/10';
                    $masterSlaveLb['X/Y'] = '0/0/11';
                }
                $type_m = substr(trim($result['east_ag1_ngbr_hostname']), 8, 3);
                $type_s = substr(trim($result['west_ag1_ngbr_hostname']), 8, 3);
                if ($type_m == 'PAR') {
                    $device_type = 'AG1';
                } else if ($type_m == 'ESR') {
                    $device_type = 'CSS';
                }
                if ($type_s == 'PAR') {
                    $dev_type_slave = 'AG1';
                } else if ($type_s == 'ESR') {
                    $dev_type_slave = 'CSS';
                }
                $masterSlaveLb['master'] = $this->getCssPTPDetails($result['east_ag1_ngbr_hostname'], $result['east_ag1_ngbr_sapid']);
                $masterSlaveLb['slave'] = $this->getCssPTPDetails($result['west_ag1_ngbr_hostname'], $result['west_ag1_ngbr_sapid']);
//CHelper::dump($model->css_sequence_in_ring);

                if ($model->css_sequence_in_ring == $maxval) {
                    $seq_no = $maxval - 1;

                    $masterhost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$model->css_ring_id}' AND css_sequence_in_ring = '{$seq_no}' AND is_active = 1")->queryRow();

                    $result1 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model['css_hostname']}' AND to_host_name = '{$masterhost['css_hostname']}' ")->queryRow();
                    //CHelper::dump($model['css_hostname']);
                    // CHelper::Debug($masterhost['css_hostname']);
                    if (!empty($result1)) {
                        $masterSlaveLb['master_ranip'] = $result1['to_addr'];
                    }
                    $result2 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$masterhost['css_hostname']}' AND to_host_name = '{$model['css_hostname']}' ")->queryRow();

                    if (!empty($result2)) {
                        $masterSlaveLb['master_ranip'] = $result2['from_addr'];
                    }
                } else {
                    $result1 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model['css_hostname']}' AND to_host_name = '{$result['east_ag1_ngbr_hostname']}' ")->queryRow();
                    if (!empty($result1)) {
                        $masterSlaveLb['master_ranip'] = $result1['to_addr'];
                    }
                    $result2 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$result['east_ag1_ngbr_hostname']}' AND to_host_name = '{$model['css_hostname']}' ")->queryRow();
                    if (!empty($result2)) {
                        $masterSlaveLb['master_ranip'] = $result2['from_addr'];
                    }
                    $result3 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model['css_hostname']}' AND to_host_name = '{$result['west_ag1_ngbr_hostname']}' ")->queryRow();
                    if (!empty($result3)) {
                        $masterSlaveLb['slave_ranip'] = $result3['to_addr'];
                    }
                    $result4 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$result['west_ag1_ngbr_hostname']}' AND to_host_name = '{$model['css_hostname']}' ")->queryRow();
                    if (!empty($result4)) {
                        $masterSlaveLb['slave_ranip'] = $result4['from_addr'];
                    }
                }
                //$masterSlaveLb['slave_ranip'] = CommonUtility::getLoopback0BySiteType($result['west_ag1_ngbr_hostname'], $dev_type_slave);
            } else if ($css_ptp_direction == 'W') {
                $masterSlaveLb['X/X'] = '';
                $masterSlaveLb['X/Y'] = '';
                if ($type == 'i') {
                    $masterSlaveLb['X/X'] = '0/0/13';
                    $masterSlaveLb['X/Y'] = '0/0/12';
                } else {
                    $masterSlaveLb['X/X'] = '0/0/11';
                    $masterSlaveLb['X/Y'] = '0/0/10';
                }
                $type_s = substr(trim($result['east_ag1_ngbr_hostname']), 8, 3);
                $type_m = substr(trim($result['west_ag1_ngbr_hostname']), 8, 3);
                $masterSlaveLb['master'] = $this->getCssPTPDetails($result['west_ag1_ngbr_hostname'], $result['west_ag1_ngbr_sapid']);
                $masterSlaveLb['slave'] = $this->getCssPTPDetails($result['east_ag1_ngbr_hostname'], $result['east_ag1_ngbr_sapid']);

                if ($model->css_sequence_in_ring == $maxval) {
                    $seq_no = $maxval + 1;
                    $masterhost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$model->css_ring_id}' AND css_sequence_in_ring = '{$seq_no}' AND is_active = 1")->queryRow();
                    $result1 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model['css_hostname']}' AND to_host_name = '{$masterhost['css_hostname']}' ")->queryRow();
                    if (!empty($result1)) {
                        $masterSlaveLb['master_ranip'] = $result1['to_addr'];
                    }
                    $result2 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$masterhost['css_hostname']}' AND to_host_name = '{$model['css_hostname']}' ")->queryRow();
                    if (!empty($result2)) {
                        $masterSlaveLb['master_ranip'] = $result2['from_addr'];
                    }
                } else {
                    $result1 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model['css_hostname']}' AND to_host_name = '{$result['west_ag1_ngbr_hostname']}' ")->queryRow();
                    if (!empty($result1)) {
                        $masterSlaveLb['master_ranip'] = $result1['to_addr'];
                    }
                    $result2 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$result['west_ag1_ngbr_hostname']}' AND to_host_name = '{$model['css_hostname']}' ")->queryRow();
                    if (!empty($result2)) {
                        $masterSlaveLb['master_ranip'] = $result2['from_addr'];
                    }
                    $result3 = Yii::app()->db->createCommand("SELECT to_addr FROM ndd_ran_wan WHERE from_host_name = '{$model['css_hostname']}' AND to_host_name = '{$result['east_ag1_ngbr_hostname']}' ")->queryRow();
                    if (!empty($result3)) {
                        $masterSlaveLb['slave_ranip'] = $result3['to_addr'];
                    }
                    $result4 = Yii::app()->db->createCommand("SELECT from_addr FROM ndd_ran_wan WHERE from_host_name = '{$result['east_ag1_ngbr_hostname']}' AND to_host_name = '{$model['css_hostname']}' ")->queryRow();
                    if (!empty($result4)) {
                        $masterSlaveLb['slave_ranip'] = $result4['from_addr'];
                    }
                }
            }
        }
//          CHelper::debug($masterSlaveLb);
        return $masterSlaveLb;
    }

    public function actionFetchRingId() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];
        $ag1PairsId = array();
        $ag1PairsId[] = array('sel' => 'Select', 'val' => '');
        $ag1PairsIdResult = Yii::app()->db->createCommand('SELECT distinct pair_id, circle_id '
                        . 'FROM `tbl_device_pair` '
                        . 'WHERE (east_parent_node_hostname = "' . $eastAg1Host . '"  and west_parent_node_hostname="' . $westAg1Host . '") '
                        . 'OR (east_parent_node_hostname = "' . $westAg1Host . '"  and west_parent_node_hostname="' . $eastAg1Host . '") '
                        . 'and device_pair_type = "AG1"')->queryAll();

        foreach ($ag1PairsIdResult as $v1) {
            $ag1PairsId[] = array('val' => $v1['pair_id'], 'sel' => $v1['pair_id']);
        }
        echo json_encode($ag1PairsId);
    }

    public function actionFetchHopCnt() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];

        $resultEast = Yii::app()->db->createCommand("SELECT ptp_flow FROM nld_adva_gm_output_master WHERE hostname = '{$eastAg1Host}' AND is_active = 1")->queryRow();
        if (!empty($resultEast)) {
            $hopcnt['East'] = $resultEast['ptp_flow'];
        }

        $resultWest = Yii::app()->db->createCommand("SELECT ptp_flow FROM nld_adva_gm_output_master WHERE hostname = '{$westAg1Host}' AND is_active = 1")->queryRow();
        if (!empty($resultWest)) {
            $hopcnt['West'] = $resultWest['ptp_flow'];
        }

        echo json_encode($hopcnt);
    }
    public function getHopCnt($hostname) {
        //$eastAg1Host = $_REQUEST['easthostname'];
        //$westAg1Host = $_REQUEST['westhostname'];
        //$hostname = '';
        $result = Yii::app()->db->createCommand("SELECT seq_no,ptp_flow,gm_id,master_hostname,slave_hostname FROM nld_adva_gm_output_master WHERE hostname = '{$hostname}' AND is_active = 1")->queryRow();
        
        if (!empty($resultEast) && $resultEast['gm_id']==0) {
            $hopcnt['East'] = 1;
        }
        else{
            
        }

        /*$resultWest = Yii::app()->db->createCommand("SELECT ptp_flow FROM nld_adva_gm_output_master WHERE hostname = '{$westAg1Host}' AND is_active = 1")->queryRow();
        if (!empty($resultWest)) {
            $hopcnt['West'] = $resultWest['ptp_flow'];
        }*/

        echo json_encode($hopcnt);
    }
    
    public function getHopFrmMaster($hostname,$retArr){
        $retArr[] = $hostname;
        //echo '<br>'.$hostname;
        $result = Yii::app()->db->createCommand("SELECT seq_no,ptp_flow,gm_id,master_hostname FROM nld_adva_gm_output_master WHERE hostname = '{$hostname}' AND is_active = 1")->queryRow();
        if (!empty($result)) {
            if($result['gm_id']!=0){
                //$retArr[] = $hostname;
                $retArr = $this->getHopFrmMaster($result['master_hostname'],$retArr);
            }
            elseif($result['gm_id']==0){
                //continue;
                
            }
        }
        else{
            
        }
        return $retArr;
    }
    
    public function getHopFrmSlave($hostname,$retArr=array()){
        $retArr[] = $hostname;
        //echo '<br>'.$hostname;
        $result = Yii::app()->db->createCommand("SELECT seq_no,ptp_flow,gm_id,slave_hostname FROM nld_adva_gm_output_master WHERE hostname = '{$hostname}' AND is_active = 1")->queryRow();
        if (!empty($result)) {
            if($result['gm_id']!=0){
                //$retArr[] = $hostname;
                //$retArr[] = $this->getHopFrmSlave($result['slave_hostname'],$retArr);
                $retArr = $this->getHopFrmSlave($result['slave_hostname'],$retArr);
            }
            else if($result['gm_id']==0){
                //return $retArr;
            }
        }
        else{
            
        }
        return $retArr;
    }
    public function actionFetchCssId() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];
        $ag1_pair_id = $_REQUEST['ag1_pair_id'];
        $ring_type = $_REQUEST['ring_type'];
        $hop_cnt = 4;

        $cssPairsId = array();
        $cssPairsId[] = array('sel' => 'Select', 'val' => '');
        //$cssPairsIdResult = Yii::app()->db->createCommand('select  distinct css_ring_id , t.id,t.css_ring,t.enode_b_sapid,t.facid,t.gne_id,t.loopback0_ipv4,t.host_name,t.takeoff_device_type,t.ring_spur_type,t.css_ring_id,t.css_ring , t.css_sequence_in_ring,t.east_ag1_hostname,t.east_ag1_ngbr_hostname,t.west_ag1_hostname,t.west_ag1_ngbr_hostname,t.final_takeoff_point,t.microwave_takeoff_point,t.microwave_hostname from ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") or (t.west_ag1_hostname="' . $eastAg1Host . '" and t.east_ag1_hostname="' . $westAg1Host . '") ) and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring')->queryAll();
        if($ring_type=='Incomplete Ring'){
            $cssPairsIdResult = Yii::app()->db->createCommand('select distinct css_ring_id , new_css_ring_id, t.css_ring from ndd_output_master t '
                    . 'left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) '
                    . 'where ((t.east_ag1_hostname="' . $eastAg1Host . '" OR t.west_ag1_hostname="' . $westAg1Host . '") or '
                    . '(t.west_ag1_hostname="' . $eastAg1Host . '" OR t.east_ag1_hostname="' . $westAg1Host . '") ) AND t.ring_spur_type="'.$ring_type.'" '
                    . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring')->queryAll();
        }
        else if($ring_type=='Microwave'){
            $cssPairsIdResult = Yii::app()->db->createCommand('select distinct css_ring_id , new_css_ring_id, t.css_ring from ndd_output_master t '
                    . 'left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) '
                    . 'where (t.microwave_hostname="' . $eastAg1Host . '" OR t.microwave_hostname="' . $westAg1Host . '") AND t.fiber_microwave="microwave" AND t.ring_spur_type="Spur" '
                    . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring')->queryAll();
        }
        else{
            $cssPairsIdResult = Yii::app()->db->createCommand('select distinct css_ring_id , new_css_ring_id, t.css_ring from ndd_output_master t '
                    . 'left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) '
                    . 'where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") or '
                    . '(t.west_ag1_hostname="' . $eastAg1Host . '" and t.east_ag1_hostname="' . $westAg1Host . '") ) AND t.ring_spur_type="'.$ring_type.'" '
                    . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring')->queryAll();
        }
        

        foreach ($cssPairsIdResult as $v1) {
            if (!empty($v1['css_ring_id']))
                $cssPairsId[] = array('val' => $v1['css_ring'] . '-' . $v1['css_ring_id'], 'sel' => $v1['css_ring'] . '-' . $v1['css_ring_id']);
            if (!empty($v1['new_css_ring_id'])) {
                $cssPairsId[] = array('val' => $v1['css_ring'] . '-' . $v1['new_css_ring_id'], 'sel' => $v1['css_ring'] . '-' . $v1['new_css_ring_id']);
            }
        }

        echo json_encode($cssPairsId);
    }
    
    public function actionFetchAllCssId() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];
        $ring_type = $_REQUEST['ring_type'];
        $css_ring_id_no = $_REQUEST['css_ring_id'];
        $css_ring_idArr = explode('-', $css_ring_id_no,2);
        $css_ring_no = $css_ring_idArr[1];
        $hop1 = ($_REQUEST['hopcnt1'])?$_REQUEST['hopcnt1']:0;
        $hop2 = ($_REQUEST['hopcnt2'])?$_REQUEST['hopcnt2']:0;
        $dir1=$dir2='';
        if($hop1!=0 && $hop2!=0){
            if($ring_type=="Incomplete Ring"){
                $cssids = Yii::app()->db->createCommand('select t.host_name, t.east_ag1_hostname,t.west_ag1_hostname,t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                    t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                    on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where (t.east_ag1_hostname="'.$eastAg1Host.'" OR t.west_ag1_hostname="'.$westAg1Host.'" OR t.west_ag1_hostname="'.$eastAg1Host.'" OR t.east_ag1_hostname="'.$westAg1Host.'") AND t.css_ring_id="'.$css_ring_no.'" and t1.is_disabled=0 and t.css_ring="'.$css_ring_idArr[0].'" and (t.fiber_microwave="Fiber") and (t.ring_spur_type="Incomplete Ring") order by t.css_sequence_in_ring')->queryAll();
            }
            else if($ring_type=="Microwave"){
                $cssids = Yii::app()->db->createCommand('select t.host_name,t.microwave_hostname, t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                    on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where (t.microwave_hostname="'.$eastAg1Host.'") and t1.is_disabled=0 and (t.fiber_microwave="microwave") and (t.ring_spur_type="Spur") order by t.css_sequence_in_ring')->queryAll();
                $cssidswest = Yii::app()->db->createCommand('select t.host_name,t.microwave_hostname, t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                    on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where (t.microwave_hostname="'.$westAg1Host.'") and t1.is_disabled=0 and (t.fiber_microwave="microwave") and (t.ring_spur_type="Spur") order by t.css_sequence_in_ring')->queryAll();
            }
            else{            
                $cssids = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.east_ag1_hostname="'.$eastAg1Host.'" and t.west_ag1_hostname="'.$westAg1Host.'" AND t.css_ring_id="'.$css_ring_no.'" and t1.is_disabled=0 and t.css_ring="'.$css_ring_idArr[0].'" and (t.fiber_microwave="Fiber") and (t.ring_spur_type="Complete Ring") order by t.css_sequence_in_ring')->queryAll();
            }
            $total = count($cssids);
            //$dir1 = $dir2 ='';
            
            if($total>0){
                if($ring_type=="Incomplete Ring"){
                    if($cssids[0]['east_ag1_hostname']!='')
                        $dir = 'east';
                    else{
                        $dir = 'west';
                        $cssids = array_reverse($cssids);
                    }
                    for ($key=0;$key<sizeof($cssids);$key++) {
                        $value = $cssids[$key];
                        
                        if($key==0 && $value['css_sequence_in_ring']>1)
                            $dir = 'west';
                        if($value['fiber_microwave']=='Microwave')
                            $hostnameval = 'MW-'.$value['host_name'];
                        else
                            $hostnameval = $value['host_name'];
                        if($dir == 'east'){
                            $dir1[] = $value['css_sequence_in_ring'].'=>'.$hostnameval;
                        }
                        else{
                            $dir2[] = $value['css_sequence_in_ring'].'=>'.$hostnameval;
                        }
                        $qry1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                        $pos = $key+1;
                        array_splice($cssids, $pos, 0, $qry1);                    
                    }
                }
                else if($ring_type=="Microwave"){
                    for ($key=0;$key<sizeof($cssids);$key++) {
                        $value = $cssids[$key];
                            
                        $dir1[] = $value['css_sequence_in_ring'].'=>MW-'.$value['host_name'];
                        
                        $qry1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                        $pos = $key+1;
                        array_splice($cssids, $pos, 0, $qry1);                    
                    }
                    
                    for ($key=0;$key<sizeof($cssidswest);$key++) {
                        $value = $cssidswest[$key];
                            
                        $dir2[] = $value['css_sequence_in_ring'].'=>MW-'.$value['host_name'];
                        
                        $qry1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                        $pos = $key+1;
                        array_splice($cssidswest, $pos, 0, $qry1);                    
                    }                    
                }
                else{
                    //$pieces = array_chunk($cssids, ceil($total / 2));
                    if($total%2==1 && $hop2<$hop1){
                        //to swap directions
                        $cnt = floor($total/2);
                        $pieces2 = array_slice($cssids, $cnt); 
                        $pieces1 = array_slice($cssids, 0, $cnt);
                        $pieces2 = array_reverse($pieces2);
                    }
                    else{
                        $pieces = array_chunk($cssids, ceil($total / 2));
                        $pieces1 = $pieces[0];
                        $pieces2 = $pieces[1];
                        $pieces2 = array_reverse($pieces2);
                    }
                    for ($key=0;$key<sizeof($pieces1);$key++) {
                        $value = $pieces1[$key];
                        if($value['fiber_microwave']=='Microwave'){
                            $hop1=$hop1+3;
                            $dir1[] = $value['css_sequence_in_ring'].'=>MW-'.$value['host_name'];
                        }
                        else{
                            $hop1++;
                            $dir1[] = $value['css_sequence_in_ring'].'=>'.$value['host_name'];
                        }
                        $qry1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                        $pos = $key+1;
                        array_splice($pieces1, $pos, 0, $qry1);                        
                    }

                    //for ($key=sizeof($pieces[1])-1;$key>=0;$key--) {
                    for ($key=0;$key<sizeof($pieces2);$key++) {    
                        $value = $pieces2[$key];
                        if($value['fiber_microwave']=='Microwave'){
                            $hop2=$hop2+3;
                            $dir2[] = $value['css_sequence_in_ring'].'=>MW-'.$value['host_name'];
                        }
                        else{
                            $hop2++;
                            $dir2[] = $value['css_sequence_in_ring'].'=>'.$value['host_name'];
                        }
                        $qry1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                    where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                        $pos = $key+1;
                        array_splice($pieces2, $pos, 0, $qry1);
                    }
                }
            }
            $response = array('dir1'=> $dir1,'dir2'=>$dir2);
            //print_R($dir1);
            echo json_encode($response);
        } 
        
    }
    
    public function actionFetchAllCssIdOld() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];
        $css_ring_id_no = $_REQUEST['css_ring_id'];
        $css_ring_idArr = explode('-', $css_ring_id_no);
        $css_ring_no = $css_ring_idArr[0];
        //$css_ring_id = $css_ring_idArr[1];  //comment by Swati 
        //Added new for handle more then - case  --Swati Chavan 
        $str='';
        for($k=1; $k<count($css_ring_idArr);$k++){
            $str .=$css_ring_idArr[$k].'-' ; 
        }
        $css_ring_id = substr($str, 0, -1);
       
        //Added new for handle more then - case  --Swati Chavan 
        
        $host = '';
        $hop_cnt = '';

        $data = $this->getPTPflowForId($eastAg1Host, $westAg1Host, $css_ring_no, $css_ring_id);

        $temp = explode('_', $data);
        $PTP_flow_direction = $temp[1];
        $host = $temp[0];

        if ($PTP_flow_direction == 'W') {
            $res = Yii::app()->db->createCommand("Select seq_no FROM nld_adva_gm_output_master WHERE hostname = '{$host}' AND is_active = 1")->queryRow();
        } else {
            $res = Yii::app()->db->createCommand("Select seq_no FROM nld_adva_gm_output_master WHERE hostname = '{$host}' AND is_active = 1")->queryRow();
        }

        $hop_cnt = 8 - $res['seq_no'];

        $cssPairsId = array();
        $cssPairsId[] = array('sel' => 'Select', 'val' => '');
        if ($PTP_flow_direction == 'W') {
            if (strlen($css_ring_id) > 9) { //new_css_ring_id = ''
                $where = 'where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") OR (t.east_ag1_hostname="' . $westAg1Host . '" and t.west_ag1_hostname="' . $eastAg1Host . '")  )  '
                        . 'and new_css_ring_id ="' . $css_ring_id . '" and css_ring="' . $css_ring_no . '" '
                        . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring  DESC';
            } else {
                $where = 'where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") OR (t.east_ag1_hostname="' . $westAg1Host . '" and t.west_ag1_hostname="' . $eastAg1Host . '")  )  '
                        . 'and css_ring_id ="' . $css_ring_id . '"  and css_ring="' . $css_ring_no . '" '
                        . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring  DESC';
            }
            $cssPairsIdResult = Yii::app()->db->createCommand('select  distinct css_ring_id , t.id,t.css_ring,t.enode_b_sapid,'
                            . 't.facid,t.gne_id,t.loopback0_ipv4,t.host_name,t.takeoff_device_type,'
                            . 't.ring_spur_type,t.css_ring_id,t.css_ring , t.css_sequence_in_ring,t.east_ag1_hostname,t.east_ag1_ngbr_hostname,t.west_ag1_hostname,'
                            . 't.west_ag1_ngbr_hostname,t.final_takeoff_point,t.microwave_takeoff_point,t.microwave_hostname '
                            . 'from ndd_output_master t '
                            . 'left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) '
                            . $where)->queryAll();
            
        } else {
            if (strlen($css_ring_id) > 9) {
                $where = ' where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") '
                        . 'or (t.west_ag1_hostname="' . $eastAg1Host . '" and t.east_ag1_hostname="' . $westAg1Host . '") ) '
                        . 'and new_css_ring_id ="' . $css_ring_id . '"  and css_ring="' . $css_ring_no . '" '
                        . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring';
            } else {
                $where = ' where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") '
                        . 'or (t.west_ag1_hostname="' . $eastAg1Host . '" and t.east_ag1_hostname="' . $westAg1Host . '") ) '
                        . 'and css_ring_id ="' . $css_ring_id . '" and css_ring="' . $css_ring_no . '" '
                        . 'and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring';
            }
            $cssPairsIdResult = Yii::app()->db->createCommand('select  distinct css_ring_id , t.id,t.css_ring,t.enode_b_sapid,t.facid,'
                            . 't.gne_id,t.loopback0_ipv4,t.host_name,t.takeoff_device_type,t.ring_spur_type,t.css_ring_id,t.css_ring , t.css_sequence_in_ring,t.east_ag1_hostname,t.east_ag1_ngbr_hostname,'
                            . 't.west_ag1_hostname,t.west_ag1_ngbr_hostname,t.final_takeoff_point,t.microwave_takeoff_point,t.microwave_hostname '
                            . 'from ndd_output_master t '
                            . 'left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id)'
                            . $where)->queryAll();
            
        }
        //$cssPairsIdResult = Yii::app()->db->createCommand('select distinct css_ring_id , t. css_ring from ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where ((t.east_ag1_hostname="' . $eastAg1Host . '" and t.west_ag1_hostname="' . $westAg1Host . '") or (t.west_ag1_hostname="' . $eastAg1Host . '" and t.east_ag1_hostname="' . $westAg1Host . '") ) and t1.is_disabled=0 order by ring_spur_type, css_ring_id, css_sequence_in_ring')->queryAll();

        $csswithinString = '';
        $csswithOutString = '';
        $withOutHopCntHostname = array();
        $i = 0;
        foreach ($cssPairsIdResult as $cssPairsValue) {
            $i++;
            if ($i <= $hop_cnt) {
                $csswithinString .= $cssPairsValue['css_sequence_in_ring'] . '.' . $cssPairsValue['host_name'] . '=>';
            } else {

                $csswithOutString .= $cssPairsValue['css_sequence_in_ring'] . '.' . $cssPairsValue['host_name'] . '=>';
            }
        }

        $csswithinString = rtrim($csswithinString, "=>");
        $csswithOutString = rtrim($csswithOutString, "=>");
        $withinHopCntHostname[] = array('val' => $csswithinString, 'sel' => $csswithinString);
        $withOutHopCntHostname[] = array('val' => $csswithOutString, 'sel' => $csswithOutString);

        echo json_encode(array("withinHopCntHostname" => $withinHopCntHostname, "withOutHopCntHostname" => $withOutHopCntHostname));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['NddNldcssDetails'])) {
            $model->attributes = $_POST['NddNldcssDetails'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        $this->loadModel($id)->delete();
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('NddNldcssDetails');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new NddNldcssDetails('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['NddNldcssDetails']))
            $model->attributes = $_GET['NddNldcssDetails'];

        if ($this->isExportRequest()) {
            $this->exportCSV($model->search(), $model->attributeNames());
        }

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return NddNldcssDetails the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = NddNldcssDetails::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param NddNldcssDetails $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'ndd-nldcss-details-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionCheckcssdeltanips() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];
        $css_ring_id_no = $_REQUEST['css_ring_id'];
        $MGM_host = $_REQUEST['MGM_host'];
        $nip_type = $_REQUEST['nip_type'];
        if ($css_ring_id_no != '') {
            $temp = explode('-', $css_ring_id_no);
        }

        if ($nip_type == 'W/O') {
            $r = Yii::app()->db->createCommand("SELECT 
                                            id
                                        FROM
                                            ndd_nldcss_details
                                        WHERE
                                            (east_ag1_hostname = '$eastAg1Host'
                                                AND west_ag1_hostname = '$westAg1Host') OR (east_ag1_hostname = '$westAg1Host'
                                                AND west_ag1_hostname = '$eastAg1Host')
                                                AND css_hostname = '$MGM_host' AND css_ring_id = '$temp[1]'  AND is_active = 1")->queryAll();
        } else {

            $r = Yii::app()->db->createCommand("SELECT 
                                            id
                                        FROM
                                            ndd_nldcss_details
                                        WHERE
                                            (east_ag1_hostname = '$eastAg1Host'
                                                AND west_ag1_hostname = '$westAg1Host') OR (east_ag1_hostname = '$westAg1Host'
                                                AND west_ag1_hostname = '$eastAg1Host')
                                                AND css_ring_id = '$temp[1]'  AND is_active = 1")->queryAll();
        }

        if (count($r) > 0) {
            if (CHelper::isAccess("REGEN_ASR920_NIPS")) {
                echo json_encode(array('response' => 'regenerate'));
            } else {
                echo json_encode(array('response' => 'exists'));
            }
        } else {
            echo json_encode(array('response' => ''));
        }
        die;
    }

    private function getCssPTPDetails($ag1hostname, $ag1sapid) {
        $region = $ptpSlaveLB301 = $ptpMasterLB300 = '';
        $region = RegionDataProvider::getDeviceRegion($ag1hostname);
        $ptpLb300Lb301Arr = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($ag1hostname, $ag1sapid, $region);

        if (!empty($ptpLb300Lb301Arr)) {
            $ptpSlaveLB301 = $ptpLb300Lb301Arr['ptp_slave_lb301'];
            $ptpMasterLB300 = $ptpLb300Lb301Arr['ptp_master_lb300'];
        }
        return array($ptpSlaveLB301, $ptpMasterLB300, $region);
    }

    public function actionExportDeltaNipTxt() {
        $id = Yii::app()->getRequest()->getQuery('id');
        $type = Yii::app()->getRequest()->getQuery('type');
        $split = Yii::app()->getRequest()->getQuery('split');
        if($split=='yes'){
            $this->generateSplitDeltaNIP($id, $type);
        }
        else{
            $this->generateDeltaNIP($id, $type);
        }
    }

    public function actionExportRemovalDeltaNipTxt() {
        $id = Yii::app()->getRequest()->getQuery('id');
        $type = Yii::app()->getRequest()->getQuery('type');
        $this->generateRemovalDeltaNIP($id, $type);
    }

    public function actionRegenerate() {
        $eastAg1Host = $_REQUEST['easthostname'];
        $westAg1Host = $_REQUEST['westhostname'];
        $css_ring_id_no = $_REQUEST['css_ring_id'];
        $nip_type = $_REQUEST['nip_type'];
        $MGM_host = $_REQUEST['MGM_host'];
        $transaction = Yii::app()->db->beginTransaction();
        try {
            if ($css_ring_id_no != '') {
                $temp = explode('-', $css_ring_id_no);
            }
            if ($nip_type == 'W/O') {
                Yii::app()->db->createCommand("UPDATE ndd_nldcss_details 
                                        SET 
                                            is_active = 0
                                        WHERE
                                            (east_ag1_hostname = '{$eastAg1Host}'
                                                AND west_ag1_hostname = '{$westAg1Host}')
                                                    OR (east_ag1_hostname = '{$westAg1Host}'
                                                AND west_ag1_hostname = '{$eastAg1Host}')
                                                AND css_ring_id = '{$temp[1]}'
                                                AND is_active = 1
                                                AND css_hostname = '{$MGM_host}'
                                                AND nip_type = '{$nip_type}'")->query();
            } else {
                Yii::app()->db->createCommand("UPDATE ndd_nldcss_details 
                                        SET 
                                            is_active = 0
                                        WHERE
                                            (east_ag1_hostname = '{$eastAg1Host}'
                                                AND west_ag1_hostname = '{$westAg1Host}')
                                                    OR (east_ag1_hostname = '{$westAg1Host}'
                                                AND west_ag1_hostname = '{$eastAg1Host}')
                                                AND css_ring_id = '{$temp[1]}'
                                                AND is_active = 1
                                                AND nip_type = '{$nip_type}'")->query();
            }
            $transaction->commit();
            echo json_encode(array('response' => 'success'));
            exit;
        } catch (Exception $e) {
            $transaction->rollback();
            echo $e->getMessage();
            echo json_encode(array('response' => 'error'));
            exit;
        }
    }
    
    public function generateSplitDeltaNIP($id, $type, $mergedNip = false) {
        $resWanIP = array();
        $CSS_lb = array();
        $portHost = '';
        $model = new NddNldcssDetails();
        $results = $model->findbyPk($id);     
        $slaves = $this->getSlaveDetails($results['css_hostname']);
        //$cssPTPDetails = $this->getCssPTPDetails($results['css_hostname'], $results['css_sapid']);
        
        if (!empty($results)) {
            $css_ring_type = $results['css_ring_type'];
            $replArr = array();
            if($css_ring_type=='Microwave'){               
                $cssResult = Yii::app()->db->createCommand("SELECT host_name,enode_b_sapid,microwave_takeoff_point,region FROM ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) WHERE microwave_hostname = '".$results['css_master']."' AND host_name='".$results['css_hostname']."' and t1.is_disabled=0")->queryRow();
                $criteria = new CDbCriteria;
                $criteria->select = 'to_port,from_port';
                $criteria->condition = " from_modified_sapid = '{$cssResult['microwave_takeoff_point']}' AND to_modified_sapid ='{$results['css_sapid']}' ";
                $criteria->limit = 1;
                //$replArr['microwave_to_port']= 'gigabit';
                $microwave_data = NddMicrowaveAddr::model()->findAll($criteria);
                
                /*echo "SELECT host_name,enode_b_sapid,microwave_takeoff_point,region FROM ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) WHERE microwave_hostname = '".$parentHost."' AND host_name='".$results['css_hostname']."' and t1.is_disabled=0 limit 1";
                echo "<br>select to_port,from_port from_modified_sapid = '{$cssResult['microwave_takeoff_point']}' AND to_modified_sapid ='{$results['css_sapid']}'";
                print_R($microwave_data);exit;*/
                if(!empty($microwave_data)){
                    $replArr['microwave_to_port']= $microwave_data[0]['to_port'];
                    $replArr['microwave_from_port']= $microwave_data[0]['from_port'];
                    
                }
                //echo "select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master_hostname']."' AND from_modified_sapid='".$cssResult['microwave_takeoff_point']."' AND to_host_name='".$results['css_hostname']."'";exit;
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master']."' AND from_modified_sapid='".$cssResult['enode_b_sapid']."' AND to_host_name='".$results['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr['ranwan_ip'] = $ranwanResult1['from_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$results['css_master']."' AND to_modified_sapid='".$cssResult['microwave_takeoff_point']."' AND from_host_name='".$results['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr['ranwan_ip'] = $ranwanResult1['to_addr'];
                    }
                }
            }
            if ($results['css_master_lb300'] == '') {
                $hostnameDtls = CommonUtility::fetchhostnameDtls($results['css_master'], 'CSS');
                $cssPTPDetails = $this->getCssPTPDetails($hostnameDtls['hostname'], $hostnameDtls['sapid']);
                $replArr['master_lb301'] = $cssPTPDetails[0];
                $replArr['master_lb300'] = $cssPTPDetails[1];
                //echo "Update ndd_nldcss_details SET css_master_lb300 = '{$cssPTPDetails[0]}',css_master_lb301 = '{$cssPTPDetails[0]}' where id = '$id'";
                $res = Yii::app()->db->createCommand("Update ndd_nldcss_details SET css_master_lb300 = '{$cssPTPDetails[1]}',css_master_lb301 = '{$cssPTPDetails[0]}' where id = '$id'")->execute();
            }
            else{
                $replArr['master_lb301'] = $results['css_master_lb301'];
                $replArr['master_lb300'] = $results['css_master_lb300'];
                $replArr['slave_lb301'] = $results['css_ptp_slave_lb301'];
            }
            //$replArr['master_lb300'] = $ranwanResult1['to_addr'];
            $suffix = $results['css_sapid'];
            $reportFilename = 'Css_' . $suffix . '_ptp_' . $p . 'delta_nip.txt';

            $textContent = $this->renderPartial('//nddNldcssDetails/asr_920_delta_splitnip_nld_css_html' , array('model' => $results, 'type' => $type,'replArr'=>$replArr,'slaveArr'=>$slaves), true);
            $textContent = CommonUtility::convertCSSNIPHtmlToText($textContent, $results['css_sapid'], 'ISO-8859-1');
            
            //$outputMode = 'F';            
            if (!is_dir(NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath())) {
                @mkdir(NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath(), 0777, true);
            }

            if (!$return_text) {
                $reportFilepath = NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath() . DIRECTORY_SEPARATOR . $reportFilename;
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($reportFilepath));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($textContent));
                echo $textContent;
                exit();
            }else{
                return $textContent;
            }
        }
        return false;
    }
    
    public function generateDeltaNIP($id, $type, $mergedNip = false) {
        $resWanIP = array();
        $CSS_lb = array();
        $portHost = '';
        $model = new NddNldcssDetails();
        $results = $model->findbyPk($id);

        if ($results['nip_type'] == 'W') {
            if ($results['css_ptp_direction'] == 'W') {
                $res = Yii::app()->db->createCommand("SELECT min(css_sequence_in_ring) FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W' AND is_active = 1")->queryRow();
                $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['min(css_sequence_in_ring)']}' AND nip_type = 'W' AND is_active = 1")->queryRow();
            } else {
                $res = Yii::app()->db->createCommand("SELECT max(css_sequence_in_ring) FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W' AND is_active = 1")->queryRow();
                $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['max(css_sequence_in_ring)']}' AND nip_type = 'W' AND is_active = 1")->queryRow();
            }
            $html = 'asr_920_delta_nip_nld_css_html';
        } else {
            if ($results['css_ptp_direction'] == 'E') {
                $res = Yii::app()->db->createCommand("SELECT MAX(CAST(css_sequence_in_ring AS UNSIGNED)) as seq_no FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W/O' AND is_active = 1")->queryRow();
                $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['seq_no']}' AND nip_type = 'W/O' AND is_active = 1")->queryRow();
            } else {
                $res = Yii::app()->db->createCommand("SELECT MIN(CAST(css_sequence_in_ring AS UNSIGNED)) as seq_no FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W/O' AND is_active = 1")->queryRow();
                $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['seq_no']}' AND nip_type = 'W/O' AND is_active = 1")->queryRow();
            }

            $resultMGM = Yii::app()->db->createCommand("SELECT 
                                                    nm.enode_b_sapid
                                                FROM
                                                    ndd_output_master nm
                                                        INNER JOIN
                                                    ndd_request_master nr ON (nm.request_id = nr.request_id
                                                        AND nm.enode_b_sapid = nr.sapid
                                                        AND nr.is_disabled = 0)
                                                WHERE
                                                    nm.host_name = '{$results['CSS_MGM_hostname']}'")->queryRow();
            $MGMsapid = $resultMGM['enode_b_sapid'];
            $ip_region = RegionDataProvider::getDeviceRegion($results['CSS_MGM_hostname']);
            $resWanIP = NddNldPtpWanIpMaster::model()->doLookupWanIpMaster($results['CSS_MGM_hostname'], $MGMsapid, $results['adva_hostname'], $results['adva_sapid'], $ip_region);
            $CSS_lb = $this->getCssPTPDetails($results['CSS_MGM_hostname'], $MGMsapid);

            if ($results['dev_type'] == 'SLAVE') {
                $html = 'asr_920_delta_nip_hopcnt_MGMcss_html';
            } else {
                $management_ip = NddNldPtpMgmtIpMaster::model()->doLookupPtpMgmtIpv4($results['adva_hostname'], $results['adva_sapid'], $results['CSS_MGM_hostname'], $MGMsapid, $ip_region);
                $ranIP = NddRanLb::model()->findbyattributes(array('host_name' => $results['CSS_MGM_hostname']));
                $ranLB = $ranIP->ipv4;
                $html = 'asr_920_delta_nip_hopcnt_css_html';
            }
        }

        $CSS_MASTER_SLAVE_LB = $this->getMasterSlaveLb($results, $type);

        if ($model->css_master_lb301 == '' || $model->css_master_lb300 == '' || $model->css_slave_lb301 == '' || $model->css_slave_lb300 == '' || $model->css_master_ranip == '' || $model->css_slave_lanip == '') {
            $res = Yii::app()->db->createCommand("Update ndd_nldcss_details SET css_master_lb300 = '{$CSS_MASTER_SLAVE_LB['master'][1]}',css_master_lb301 = '{$CSS_MASTER_SLAVE_LB['master'][0]}',css_slave_lb301 = '{$CSS_MASTER_SLAVE_LB['slave'][1]}',css_slave_lb300 = '{$CSS_MASTER_SLAVE_LB['slave'][0]}', css_master_ranip = '{$CSS_MASTER_SLAVE_LB['master_ranip']}', css_slave_ranip = '{$CSS_MASTER_SLAVE_LB['slave_ranip']}' where id = '$id'")->execute();
        }


        $textContent = '';
        if (!empty($results)) {

            $suffix = $results['css_hostname'];
            $reportFilename = 'CSS_' . $suffix . '_ptp_' . 'delta_nip.txt';
            if ($results['nip_type'] == 'W') {
                $textContent = $this->renderPartial('//nddNldcssDetails/' . $html, array('model' => $results, 'type' => $type, 'CSS_MASTER_SLAVE_LB' => $CSS_MASTER_SLAVE_LB, 'portHost' => $portHost), true);
                $textContent = CommonUtility::convertCSSNIPHtmlToText($textContent, $results['css_sapid'], 'ISO-8859-1');
            } else {
                $textContent = $this->renderPartial('//nddNldcssDetails/' . $html, array('model' => $results, 'type' => $type, 'CSS_MASTER_SLAVE_LB' => $CSS_MASTER_SLAVE_LB, 'WANIP' => $resWanIP, 'CSS_lb' => $CSS_lb, 'ranLB' => $ranLB, 'management_ip' => $management_ip, 'portHost' => $portHost), true);
                $textContent = CommonUtility::convertCSSNIPHtmlToText($textContent, $results['css_sapid'], 'ISO-8859-1');
            }

            if ($mergedNip) {
                return $textContent;
            }

            //$outputMode = 'F';            
            if (!is_dir(NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath())) {
                @mkdir(NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath(), 0777, true);
            }

            $reportFilepath = NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath() . DIRECTORY_SEPARATOR . $reportFilename;
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($reportFilepath));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($textContent));
            echo $textContent;
            exit();
        }
        return false;
    }

    public function generateRemovalDeltaNIP($id, $type) {
        $model = new NddNldcssDetails();
        $results = $model->findbyPk($id);
        $textContent = '';
        if (!empty($results)) {

            if ($results['css_ptp_direction'] == 'E') {
                if ($type == 'i') {
                    $masterSlaveLb['X/X'] = '0/0/12';
                    $masterSlaveLb['X/Y'] = '0/0/13';
                } else {
                    $masterSlaveLb['X/X'] = '0/0/10';
                    $masterSlaveLb['X/Y'] = '0/0/11';
                }
            } else {
                if ($type == 'i') {
                    $masterSlaveLb['X/X'] = '0/0/13';
                    $masterSlaveLb['X/Y'] = '0/0/12';
                } else {
                    $masterSlaveLb['X/X'] = '0/0/11';
                    $masterSlaveLb['X/Y'] = '0/0/10';
                }
            }

            if ($results['nip_type'] == 'W') {
                if ($results['css_ptp_direction'] == 'W') {
                    $res = Yii::app()->db->createCommand("SELECT min(css_sequence_in_ring) FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W'   AND is_active = 1")->queryRow();
                    $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['min(css_sequence_in_ring)']}' AND nip_type = 'W'   AND is_active = 1")->queryRow();
                } else {
                    $res = Yii::app()->db->createCommand("SELECT max(css_sequence_in_ring) FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W'   AND is_active = 1")->queryRow();
                    $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['max(css_sequence_in_ring)']}' AND nip_type = 'W'   AND is_active = 1")->queryRow();
                }
            } else {
                if ($results['css_ptp_direction'] == 'E') {
                    $res = Yii::app()->db->createCommand("SELECT MAX(CAST(css_sequence_in_ring AS UNSIGNED)) as seq_no FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W/O'   AND is_active = 1")->queryRow();
                    $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['seq_no']}' AND nip_type = 'W/O'   AND is_active = 1")->queryRow();
                } else {
                    $res = Yii::app()->db->createCommand("SELECT MAX(CAST(css_sequence_in_ring AS UNSIGNED)) as seq_no FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND nip_type = 'W/O'   AND is_active = 1")->queryRow();
                    $portHost = Yii::app()->db->createCommand("SELECT css_hostname FROM ndd_nldcss_details WHERE css_ring_id = '{$results['css_ring_id']}' AND css_sequence_in_ring = '{$res['seq_no']}' AND nip_type = 'W/O'   AND is_active = 1")->queryRow();
                }
            }

            $suffix = $results['css_hostname'];
            $reportFilename = 'Removal_CSS_' . $suffix . '_ptp_' . 'delta_nip.txt';
            if ($results['nip_type'] == 'W') {
                $html = 'removal_asr_920_nld_css_html';
                $textContent = $this->renderPartial('//nddNldcssDetails/' . $html, array('model' => $results, 'type' => $type, 'masterSlaveLb' => $masterSlaveLb, 'portHost' => $portHost), true);
                $textContent = CommonUtility::convertCSSNIPHtmlToText($textContent, $results['css_sapid'], 'ISO-8859-1');
            } else {
                $html = 'removal_asr_920_nip_hopcnt_css_html';
                $textContent = $this->renderPartial('//nddNldcssDetails/' . $html, array('model' => $results, 'type' => $type, 'masterSlaveLb' => $masterSlaveLb, 'portHost' => $portHost), true);
                $textContent = CommonUtility::convertCSSNIPHtmlToText($textContent, $results['css_sapid'], 'ISO-8859-1');
            }

            //$outputMode = 'F';            
            if (!is_dir(NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath())) {
                @mkdir(NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath(), 0777, true);
            }

            $reportFilepath = NddOutputMaster::model()->getNIPShowRunReportsDownloadDirPath() . DIRECTORY_SEPARATOR . $reportFilename;
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($reportFilepath));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($textContent));
            echo $textContent;
            exit();
        }
        return false;
    }

    public function getPTPflow($east_host, $west_host, $css_ring_no = false, $css_ring_id = false) {
        $ptp_flow = '';

        $resultEast = Yii::app()->db->createCommand("Select seq_no FROM nld_adva_gm_output_master WHERE hostname = '{$east_host}' AND is_active = 1")->queryRow();

        $resultWest = Yii::app()->db->createCommand("Select seq_no FROM nld_adva_gm_output_master WHERE hostname = '{$west_host}' AND is_active = 1")->queryRow();

        if ($css_ring_no && $css_ring_id) {
            $and_where = ' AND t.css_ring_id = "' . $css_ring_id . '" AND t.css_ring = "' . $css_ring_no . '" ';
        } else {
            $and_where = '';
        }

        $result = Yii::app()->db->createCommand('select t.east_ag1_hostname, t.west_ag1_hostname from ndd_output_master t INNER JOIN ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) '
                        . 'where ((t.east_ag1_hostname="' . $east_host . '" and t.west_ag1_hostname="' . $west_host . '") OR (t.east_ag1_hostname="' . $west_host . '" and t.west_ag1_hostname="' . $east_host . '")) ' . $and_where)->queryRow();
        //echo "<pre>";print_r($result);print_r($resultEast);print_r($resultWest);exit();
        if ($resultEast < $resultWest) {
            if (($result['east_ag1_hostname'] == $east_host) && ($result['west_ag1_hostname'] == $west_host)) {
                return $ptp_flow = 'E';
            } else {
                return $ptp_flow = 'W';
            }
        } else {
            if (($result['west_ag1_hostname'] == $west_host) && ($result['east_ag1_hostname'] == $east_host)) {
                return $ptp_flow = 'W';
            } else {
                return $ptp_flow = 'E';
            }
        }
    }

    public function getPTPflowForId($east_host, $west_host, $css_ring_no = false, $css_ring_id = false) {
        $ptp_flow = '';

        $resultEast = Yii::app()->db->createCommand("Select seq_no FROM nld_adva_gm_output_master WHERE hostname = '{$east_host}' AND is_active = 1")->queryRow();

        $resultWest = Yii::app()->db->createCommand("Select seq_no FROM nld_adva_gm_output_master WHERE hostname = '{$west_host}' AND is_active = 1")->queryRow();

        if ($css_ring_no && $css_ring_id) {
            if (strlen($css_ring_id) > 9) {
                $and_where = ' AND t.new_css_ring_id = "' . $css_ring_id . '" AND t.css_ring = "' . $css_ring_no . '" ';
            } else {
                $and_where = ' AND t.css_ring_id = "' . $css_ring_id . '" AND t.css_ring = "' . $css_ring_no . '" ';
            }
        } else {
            $and_where = '';
        }

        $result = Yii::app()->db->createCommand('select t.east_ag1_hostname, t.west_ag1_hostname from ndd_output_master t INNER JOIN ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) '
                        . 'where ((t.east_ag1_hostname="' . $east_host . '" and t.west_ag1_hostname="' . $west_host . '") OR (t.east_ag1_hostname="' . $west_host . '" and t.west_ag1_hostname="' . $east_host . '")) ' . $and_where)->queryRow();
        //  echo "<pre>";print_r($result);print_r($resultEast);print_r($resultWest);

        if ($resultEast < $resultWest) {
            if (($result['east_ag1_hostname'] == $east_host) && ($result['west_ag1_hostname'] == $west_host)) {
                return $ptp_flow = $east_host . '_E';
            } else {
                return $ptp_flow = $east_host . '_W';
            }
        } else {
            if (($result['west_ag1_hostname'] == $west_host) && ($result['east_ag1_hostname'] == $east_host)) {
                return $ptp_flow = $west_host . '_W';
            } else {
                return $ptp_flow = $west_host . '_E';
            }
        }
    }

    public function actionSetMGMHost() {
        $temp = explode('.', $_POST['option']);
        $mgmhost = explode('=>', $temp[1]);
        $MGM_device = $mgmhost[0];
        $resultMGM = Yii::app()->db->createCommand("select t.enode_b_sapid from ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where t.host_name = '{$MGM_device}'")->queryRow();

        $MGM_device_sapid = $resultMGM['enode_b_sapid'];
        echo json_encode(array("MGM_device_sapid" => $MGM_device_sapid, "MGM_device" => $MGM_device));
    }

    public static function getNipByHostname($hostname, $loopback0, $deviceModel,$console) {

        $criteria = new CDbCriteria();
        $criteria->select = "`id`, `css_hostname`, `css_loopback`";
        $criteria->condition = "css_hostname =:css_hostname AND css_loopback =:css_loopback AND is_active = 1";
        $criteria->params = array(':css_hostname' => $hostname, ':css_loopback' => $loopback0);
        $ptpRecord = NddNldcssDetails::model()->find($criteria);
        if($console &&  count($ptpRecord) > 0){
            return 1;
        }
        $textContent = '';
        if (count($ptpRecord) > 0) {
            $nddNldCssPtp = new NddNldcssDetailsController('');
            $textContent = $nddNldCssPtp->generateDeltaNIP($ptpRecord['id'], $deviceModel, true);
        }
               
        return $textContent;
    }

    public function actionGetNipByLoopback() {
        $loopback0 = Yii::app()->getRequest()->getQuery('loopback');

        $devResult = Yii::app()->db->createCommand("Select device_type from built_router_css_master where Loopback0 = '{$loopback0}'")->queryRow();
        if (!empty($devResult)) {
            $temp = explode('-', $devResult['device_type']);
            $deviceType = $temp[1];
        }
        $criteria = new CDbCriteria();
        $criteria->select = "`id`, `css_hostname`, `css_loopback`";
        $criteria->condition = "css_loopback =:css_loopback AND is_active = 1";
        $criteria->params = array(':css_loopback' => $loopback0);
        $ptpRecord = NddNldcssDetails::model()->find($criteria);
        $textContent = '';
        if (count($ptpRecord) > 0) {
            $textContent = $this->generateDeltaNIP($ptpRecord['id'], $deviceType, false);
        }
        // return $textContent;
    }
    public function getSlaveDetails($hostname){
        $criteria = new CDbCriteria;
        $criteria->select = '*';
        $criteria->condition = " css_master = '{$hostname}' AND is_active ='1' AND pdf_done=1";
        //$criteria->limit = 1;
        //$replArr['microwave_to_port']= 'gigabit';
        //$microwave_data = NddMicrowaveAddr::model()->findAll($criteria);
        $slaves = NddNldcssDetails::model()->findAll($criteria);
        $i=0;
        $replArr = array();
        foreach($slaves as $slave){
            $replArr[$i]['hostname']= $slave['css_hostname'];
            $replArr[$i]['hopcount']= $slave['hopcount'];
            $replArr[$i]['slave_lb301'] = $slave['css_ptp_slave_lb301'];
            if($slave['css_ring_type']=='Microwave'){
                $replArr[$i]['type']= 'Microwave';                
                $cssResult = Yii::app()->db->createCommand("SELECT host_name,enode_b_sapid,microwave_takeoff_point,east_ag1_ngbr_hostname,west_ag1_ngbr_hostname,region,microwave_hostname FROM ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) WHERE microwave_hostname = '".$hostname."' AND host_name='".$slave['css_hostname']."' and t1.is_disabled=0")->queryRow();
                $criteria = new CDbCriteria;
                $criteria->select = 'to_port,from_port';
                $criteria->condition = " from_modified_sapid = '{$cssResult['microwave_takeoff_point']}' AND to_modified_sapid ='{$slave['css_sapid']}' ";
                $criteria->limit = 1;
                //$replArr['microwave_to_port']= 'gigabit';
                $microwave_data = NddMicrowaveAddr::model()->findAll($criteria);             
                if(!empty($microwave_data)){
                    $replArr[$i]['microwave_to_port']= $microwave_data[0]['to_port'];
                    $replArr[$i]['microwave_from_port']= $microwave_data[0]['from_port'];                    
                }
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$slave['css_master']."' AND to_host_name='".$slave['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr[$i]['css_slave_ranip'] = $ranwanResult1['to_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$slave['css_master']."' AND from_host_name='".$slave['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr[$i]['css_slave_ranip'] = $ranwanResult1['from_addr'];
                    }
                }
                /*$slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($cssResult['microwave_hostname'],$cssResult['microwave_takeoff_point'],$cssResult['region']);
                
                $replArr[$i]['css_slave_lb301'] = $slavelb301Ips['ptp_slave_lb301'];
                $replArr[$i]['css_master_ranip'] = $slave['css_master_ranip'];*/
                //$replArr[$i]['css_slave_lb300'] = $slavelb301Ips['ptp_slave_lb300'];
            }
            else{
                $replArr[$i]['type']= 'Fiber';
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$slave['css_master']."' AND to_host_name='".$slave['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr[$i]['css_slave_ranip'] = $ranwanResult1['to_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$slave['css_master']."' AND from_host_name='".$slave['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr[$i]['css_slave_ranip'] = $ranwanResult1['from_addr'];
                    }
                }
                
                //$replArr[$i]['css_slave_ranip'] = $slave['css_master_ranip'];
                /*$replArr[$i]['css_slave_lb301'] = $slave['css_ptp_slave_lb301'];
                $replArr[$i]['css_master_ranip'] = $slave['css_master_ranip'];*/
            }
            $i++;
        }
        return $replArr;
    }
}
