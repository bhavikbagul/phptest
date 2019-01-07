<?php
class NddMetroCssRingSplitDetailController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
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
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view','create','delete','admin','fetchCssRingId','fetchag1pairdetails','fetsplitcss','downloadNip','exportDeltaNipTxt','getSlaveDetails','fetchCSSRings'),
//				'users'=>array('*'),
                            'expression' => 'CHelper::isAccess("UPLOAD_NLD_CSS") || CHelper::isAccess("AG1_LINK_MANAGER") || CHelper::isAccess("NDD_DOWNLOADER") ',
			),
//			array('allow', // allow authenticated user to perform 'create' and 'update' actions
//				'actions'=>array('create','update'),
//				'users'=>array('@'),
//			),
//			array('allow', // allow admin user to perform 'admin' and 'delete' actions
//				'actions'=>array('admin','delete'),
//				'users'=>array('@'),
//			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new NddMetroCssRingSplitDetail;                
                //print_R($res);
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['NddMetroCssRingSplitDetail']))
		{
//                    print_r($_POST['NddMetroCssRingSplitDetail']);exit;
                        $ts = new CDbExpression('now()');
			$model->attributes=$_POST['NddMetroCssRingSplitDetail'];
                        $hopcnt_east = $_POST['NddMetroCssRingSplitDetail']['hopcnt_east'];
                        $hopcnt_west = $_POST['NddMetroCssRingSplitDetail']['hopcnt_west'];
                        $id = $_POST['NddMetroCssRingSplitDetail']['ag2_pairs'];
                        $criteria = new CDbCriteria;
                        $criteria->select = "id,ag2_1_sapid,ag2_1_facid,ag2_1_neid,ag2_1_loopback,ag2_1_hostname,ag2_1_gm,ag2_2_sapid,ag2_2_facid,ag2_2_neid,ag2_2_loopback,ag2_2_hostname,ag2_2_gm";
                        $criteria->condition = "id=:id";
                        $criteria->params = array('id' => $id);
                        $r = NddAg2PairMaster::model()->find($criteria);                        
                        $ag2_1_hostname = $r->ag2_1_hostname;
                        $ag2_2_hostname = $r->ag2_2_hostname;
                        //get ring details
                        $ring = trim($_POST['NddMetroCssRingSplitDetail']['ag1_rings']);
                        $ringArr = explode(",", $ring);
                        $Ag1ringNo = $ringArr[0];
                        $Ag1ringId = $ringArr[1];
                        
                        $ag1Pairs = trim($_POST['NddMetroCssRingSplitDetail']['ag1_pairs']);
                        $ag1PairArr = explode(",", $ag1Pairs);
                        $ag1_1_hostname = $ag1PairArr[0];
                        $ag1_2_hostname = $ag1PairArr[1];
                        $gm1 = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_gm_hn']);
                        $gm2 = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_gm_hn']);
                        
                        if($_POST['NddMetroCssRingSplitDetail']['css_ring_type']=='Microwave'){
                            $r1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where (t.microwave_hostname="'.$ag1_1_hostname.'" '
                            . 'OR t.microwave_hostname="'.$ag1_2_hostname.'") and t1.is_disabled=0 and (t.fiber_microwave="microwave") and (t.ring_spur_type="Spur") order by t.css_sequence_in_ring')->queryAll();
                            
                        }
                        else{
                            $cssRingId = trim($_POST['NddMetroCssRingSplitDetail']['css_ring_id']);
                            //$cssRingIdArr = explode("-", $cssRingId,2);
                            $cssringdetails = explode('-', $cssRingId);
                            $last = array_pop($cssringdetails);
                            $cssRingIdArr = array(implode('-', $cssringdetails), $last);
                            $cssRingId = $cssRingIdArr[0];
                            $cssringNo = $cssRingIdArr[1];
                            if($_POST['NddMetroCssRingSplitDetail']['css_ring_type']=='Incomplete Ring'){
                                $model->updateAll(array('is_active' => 0), 'is_active = "1" AND css_ring_id = "' . $cssRingId.'" AND css_ring_no="'.$cssringNo.'" AND css_ring_type="Incomplete Ring"');                            

                                if($cssRingId!=''){
                                    $r1 = Yii::app()->db->createCommand('select t.*,
                                    t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                                    on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                                    where t.css_ring_id="'.$cssRingId.'" and t1.is_disabled=0 and t.css_ring="'.$cssringNo.'" and (t.fiber_microwave="Fiber") and (t.ring_spur_type="Incomplete Ring") order by t.css_sequence_in_ring')->queryAll();
                                }
                            }
                            else{
                                $model->updateAll(array('is_active' => 0), 'is_active = "1" AND css_ring_id = "' . $cssRingId.'" AND css_ring_no="'.$cssringNo.'" AND css_ring_type="Complete Ring"');                            
                                if($cssRingId!=''){
                                    $r1 = Yii::app()->db->createCommand('select t.*,
                                    t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                                    on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                                    where t.css_ring_id="'.$cssRingId.'" and t1.is_disabled=0 and t.css_ring="'.$cssringNo.'" and (t.fiber_microwave="Fiber") and (t.ring_spur_type="Complete Ring") order by t.css_sequence_in_ring')->queryAll();
                                }
                            }
                        }                        
                        
                        if ($r1!=null){
                            if($_POST['NddMetroCssRingSplitDetail']['css_ring_type']=='Incomplete Ring'){
                                $dir = 'east';
                                $j=0;
                                $csshostnameArr = array();
                                for($key=0;$key<sizeof($r1);$key++){
                                    $value = $r1[$key];
                                    if($j==0 && $value['css_sequence_in_ring']>1){
                                        $dir = 'west';
                                        $hopFrm = $hopcnt_west;
                                        //$hopcount = $hopFrm;
                                    }
                                    elseif($j==0 && $value['css_sequence_in_ring']=1){
                                        $dir = 'east';
                                        $hopFrm = $hopcnt_east;
                                        //$hopcount = $hopFrm;
                                    }
                                    $piece1Count = count($r1);
                                    $model->id = null;
                                    $model->css_ring_type = $_POST['NddMetroCssRingSplitDetail']['css_ring_type'];
                                    $model->downloadable=1;
                                    $model->is_spur = 0;
                                    if($dir == 'west')
                                        $model->clock_source_on = 'W';
                                    else
                                        $model->clock_source_on = 'E';
                                    $model->ag2_1_hostname = $ag2_1_hostname;
                                    $model->ag2_2_hostname = $ag2_2_hostname;
                                    $model->ag1_1_hostname = $ag1_1_hostname;
                                    $model->ag1_2_hostname = $ag1_2_hostname;
                                    $model->ag1_1_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_sapid']);
                                    $model->ag1_2_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_sapid']);                                    
                                    $model->ag1_1_gm = $value['east_ag1_hostname'];                                    
                                    $model->ag1_2_gm = $ag1_2_hostname;
                                    $cssPTPDetails = $this->getCssPTPDetails($value['host_name'], $value['enode_b_sapid']);
                                    $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                                    $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                                    if($model->css_ptp_master_lb300=="" || $model->css_ptp_slave_lb301==""){
                                        $model->downloadable=0;
                                    }
                                    $model->ag1_ring_id = $Ag1ringId;
                                    $model->ag1_ring_no = $Ag1ringNo;
                                    $model->css_sapid = $value['enode_b_sapid'];
                                    $model->css_facid = $value['facid'];
                                    $model->css_neid = $value['gne_id'];
                                    $model->css_loopback = $value['loopback0_ipv4'];
                                    $model->css_hostname = $value['host_name'];
                                    $csshostnameArr[] = $value['host_name'];
                                    $hopcountArr = $this->getHopCount($csshostnameArr,$hopFrm);
                                    if($dir == 'west' && $j==0)
                                        $model->css_master_hostname = $value['west_ag1_ngbr_hostname'];
                                    else
                                        $model->css_master_hostname = $value['east_ag1_ngbr_hostname'];
                                    
                                    if(substr($value['west_ag1_ngbr_hostname'],8,3)=='ESR')
                                        $model->css_slave_hostname = $value['west_ag1_ngbr_hostname'];
                                    else
                                        $model->css_slave_hostname = '';
                                    if($value['fiber_microwave']=='Microwave'){
                                        $addHopCnt = 3;
                                        $model->css_ring_type = $value['fiber_microwave'];
                                        $model->css_master_hostname = $value['microwave_hostname'];
                                        $model->updateAll(array('is_active' => 0), 'is_active = "1" AND ag2_1_hostname = "' . $ag2_1_hostname.'" AND ag2_2_hostname = "' . $ag2_2_hostname.'" AND css_hostname="'.$value['host_name'].'"');
                                    }
                                    else{
                                        $addHopCnt = 1;
                                    }
                                    $maxHopCount = 8;
                                    $hopcount = $hopcountArr[$key]['hopcount'];
                                    //$hopcount = $key+$hopFrm+$addHopCnt;
                                    $model->split='yes';
                                    $model->hopcount=$hopcount;
                                    if($maxHopCount<($hopcount)){
                                        $model->downloadable=0;
                                    }
                                    if($piece1Count == ($key+1)){
                                        $model->is_spur = 1;
                                    }                                    
                                    $model->css_seq_no = $value['css_sequence_in_ring'];
                                    $model->css_ring_id = $value['css_ring_id'];
                                    $model->css_ring_no = $value['css_ring'];
                                    $model->css_region = $value['region'];
                                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$model->css_master_hostname."' AND to_host_name='".$model->css_hostname."'")->queryRow();
                                    if(!empty($ranwanResult1)){
                                        $model->css_master_ranip = $ranwanResult1['from_addr'];
                                        $model->css_slave_ranip = $ranwanResult1['to_addr'];
                                    }
                                    else{
                                        $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$model->css_master_hostname."' AND from_host_name='".$model->css_hostname."'")->queryRow();
                                        if(!empty($ranwanResult1)){
                                            $model->css_master_ranip = $ranwanResult1['to_addr'];
                                            $model->css_slave_ranip = $ranwanResult1['from_addr'];
                                        }
                                    }
                                    if($model->css_master_ranip=="" || $model->css_slave_ranip==""){
                                        $model->downloadable=0;
                                    }
                                    //get master loopback 300 if css is directly connected too ag1
                                    if($value['fiber_microwave']=='Microwave'){
                                        $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['microwave_hostname'],$value['microwave_takeoff_point'],$value['region']);
                                        if(!empty($lb300Ips)){
                                            $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                            $model->css_slave_lb301 = '';
                                            $model->is_spur = 1;
                                        }
                                    }
                                    else{
                                        if($dir == 'west' && $j==0)
                                        $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                        else
                                        $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                        $lb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                        if(!empty($lb300Ips)){
                                            $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                            if($piece1Count == ($key+1)){
                                                $model->css_slave_lb301 = '';
                                            }else{
                                                $model->css_slave_lb301 = $lb301Ips['ptp_slave_lb301'];
                                                $model->is_spur = 1;
                                            }
                                        }
                                    }    
                                    $model->is_active = 1;
                                    $model->created_by = Yii::app()->session['login']['user_id'];
                                    $model->created_at = $ts;
                                    $model->isNewRecord = true;
                                    $model->save();
                                    $j++;
                                    
                                    $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                                    $pos = $key+1;
                                    array_splice($r1, $pos, 0, $qry1);    
                                }
                            }
                            else if($_POST['NddMetroCssRingSplitDetail']['css_ring_type']=='Microwave'){
                                $csshostnameArr = array();
                                $i=0;
                                foreach($r1 as $key => $value){
                                    $model->updateAll(array('is_active' => 0), 'is_active = "1" AND ag2_1_hostname = "' . $ag2_1_hostname.'" AND ag2_2_hostname = "' . $ag2_2_hostname.'" AND css_hostname="'.$value['host_name'].'"');
                                    $csshostnameArr[] = $value['host_name'];
                                    $model->id = null;
                                    $model->css_ring_type = $_POST['NddMetroCssRingSplitDetail']['css_ring_type'];
                                    $model->downloadable=1;
                                    $model->clock_source_on = 'W';
                                    $model->ag2_1_hostname = $ag2_1_hostname;
                                    $model->ag2_2_hostname = $ag2_2_hostname;
                                    $model->ag1_1_hostname = $ag1_1_hostname;
                                    $model->ag1_2_hostname = $ag1_2_hostname;
                                    $model->ag1_1_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_sapid']);
                                    $model->ag1_2_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_sapid']);                                    
                                    $model->ag1_1_gm = $value['east_ag1_hostname'];                                    
                                    $model->ag1_2_gm = $ag1_2_hostname;
                                    $cssPTPDetails = $this->getCssPTPDetails($value['host_name'], $value['enode_b_sapid']);
                                    $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                                    $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                                    if($model->css_ptp_master_lb300=="" || $model->css_ptp_slave_lb301==""){
                                        $model->downloadable=0;
                                    }
                                    $model->is_spur = (strtolower($value['ring_spur_type']) == 'spur' ? 1 : 0);
                                    $model->ag1_ring_id = $Ag1ringId;
                                    $model->ag1_ring_no = $Ag1ringNo;
                                    $model->css_sapid = $value['enode_b_sapid'];
                                    $model->css_facid = $value['facid'];
                                    $model->css_neid = $value['gne_id'];
                                    $model->css_loopback = $value['loopback0_ipv4'];
                                    $model->css_hostname = $value['host_name'];
                                    if($value['microwave_hostname'] == $ag1_1_hostname){
                                        $model->css_master_hostname = $ag1_1_hostname;
                                        $model->css_slave_hostname = '';
                                        $hopFrm = $hopcnt_east;
                                        $model->clock_source_on = 'E';
                                    }
                                    else if($value['microwave_hostname'] == $ag1_2_hostname){
                                        $model->css_master_hostname = $ag1_2_hostname;
                                        $model->css_slave_hostname = '';
                                        $hopFrm = $hopcnt_west;
                                    }
                                    $maxHopCount = 8;
                                    $hopcountArr = $this->getHopCount($csshostnameArr,$hopFrm);
                                    $hopcount = $hopcountArr[$i]['hopcount'];
                                    //$hopcount = $key+$hopFrm+3;
                                    $model->split='yes';
                                    $model->hopcount=$hopcount;
                                    if($maxHopCount<($hopcount)){
                                        $model->downloadable=0;
                                    }                                    
                                    $model->css_seq_no = $value['css_sequence_in_ring'];
                                    $model->css_ring_id = $value['css_ring_id'];
                                    $model->css_ring_no = $value['css_ring'];
                                    $model->css_region = $value['region'];
                                    
                                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$model->css_master_hostname."' AND to_host_name='".$model->css_hostname."'")->queryRow();
                                    if(!empty($ranwanResult1)){
                                        $model->css_master_ranip = $ranwanResult1['from_addr'];
                                        $model->css_slave_ranip = $ranwanResult1['to_addr'];
                                    }
                                    else{
                                        $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$model->css_master_hostname."' AND from_host_name='".$model->css_hostname."'")->queryRow();
                                        if(!empty($ranwanResult1)){
                                            $model->css_master_ranip = $ranwanResult1['to_addr'];
                                            $model->css_slave_ranip = $ranwanResult1['from_addr'];
                                        }
                                    }
                                    if($model->css_master_ranip=="" || $model->css_slave_ranip==""){
                                        $model->downloadable=0;
                                    }
                                    
                                    $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['microwave_hostname'],$value['microwave_takeoff_point'],$value['region']);
                                    if(!empty($lb300Ips)){
                                        $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                        $model->css_slave_lb301 = '';                                            
                                    }
                                        
                                    $model->is_active = 1;
                                    $model->created_by = Yii::app()->session['login']['user_id'];
                                    $model->created_at = $ts;
                                    $model->isNewRecord = true;
                                    $model->save();
                                    $i++;
                                }
                            }
                            else{
                                if($gm1=='' && $gm2==''){
                                    $pieces = array_chunk($r1, ceil(count($r1) / 2));
                                    $piece1Count = count($pieces[0]);
                                    $key1last = end($pieces[0]);
                                    $hopcount = $hopcnt_east;
                                    $csshostnameArr = array();
                                    for($key=0;$key<sizeof($pieces[0]);$key++){
                                        $value = $pieces[0][$key];
                                        $hopFrm = $hopcnt_east;
                                        $csshostnameArr[] = $value['host_name'];
                                        $hopcountArr = $this->getHopCount($csshostnameArr,$hopFrm);
                                        $model->id = null;
                                        $model->css_ring_type = $_POST['NddMetroCssRingSplitDetail']['css_ring_type'];
                                        $model->downloadable=1;
                                        $model->is_spur = 0;
                                        $model->clock_source_on = 'E';
                                        $model->ag2_1_hostname = $ag2_1_hostname;
                                        $model->ag2_2_hostname = $ag2_2_hostname;
                                        $model->ag1_1_hostname = $ag1_1_hostname;
                                        $model->ag1_2_hostname = $ag1_2_hostname;
                                        $model->ag1_1_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_sapid']);
                                        $model->ag1_2_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_sapid']);
                                        $model->ag1_1_gm = $value['east_ag1_hostname'];
                                        $maxHopCount = 8;
                                        if($value['fiber_microwave']=='Microwave'){
                                            $addHopCnt = 3;
                                            $model->css_ring_type = $value['fiber_microwave'];
                                            $model->css_master_hostname = $value['microwave_hostname'];
                                            $model->updateAll(array('is_active' => 0), 'is_active = "1" AND ag2_1_hostname = "' . $ag2_1_hostname.'" AND ag2_2_hostname = "' . $ag2_2_hostname.'" AND css_hostname="'.$value['host_name'].'"');
                                        }
                                        else{
                                            $addHopCnt = 1;
                                            $model->css_master_hostname = $value['east_ag1_ngbr_hostname'];
                                        }
                                        //$hopcount += $addHopCnt;
                                        $hopcount = $hopcountArr[$key]['hopcount'];
                                        $model->split='yes';
                                        $model->hopcount=$hopcount;
                                        if($maxHopCount<($hopcount)){
                                            $model->downloadable=0;
                                        }
                                        $model->ag1_2_gm = $ag1_2_hostname;

                                        $cssPTPDetails = $this->getCssPTPDetails($value['host_name'], $value['enode_b_sapid']);
                                        $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                                        $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                                        if($model->css_ptp_master_lb300=="" || $model->css_ptp_slave_lb301==""){
                                            $model->downloadable=0;
                                        }

                                        //$model->is_spur = (strtolower($value['ring_spur_type']) == 'spur' ? 1 : 0);
                                        $model->is_spur = 0;    
                                        $model->ag1_ring_id = $Ag1ringId;
                                        $model->ag1_ring_no = $Ag1ringNo;

                                        $model->css_sapid = $value['enode_b_sapid'];
                                        $model->css_facid = $value['facid'];
                                        $model->css_neid = $value['gne_id'];
                                        $model->css_loopback = $value['loopback0_ipv4'];

                                        $model->css_hostname = $value['host_name'];                                        
                                        if($key1last['host_name'] == $value['host_name']){
                                            $model->css_slave_hostname = '';
                                            $model->is_spur = 1;
                                        }else{
                                            if($value['ring_spur_type']=='Spur' && $value['west_ag1_ngbr_hostname']==''){
                                                $model->is_spur = 1;
                                                $model->css_slave_hostname = '';
                                            }
                                            else
                                            $model->css_slave_hostname = $value['west_ag1_ngbr_hostname'];
                                        }
                                        $model->css_seq_no = $value['css_sequence_in_ring'];
                                        $model->css_ring_id = $value['css_ring_id'];
                                        $model->css_ring_no = $value['css_ring'];
                                        $model->css_region = $value['region'];

                                        $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$model->css_master_hostname."' AND to_host_name='".$model->css_hostname."'")->queryRow();
                                        if(!empty($ranwanResult1)){
                                            $model->css_master_ranip = $ranwanResult1['from_addr'];
                                            $model->css_slave_ranip = $ranwanResult1['to_addr'];
                                        }
                                        else{
                                            $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$model->css_master_hostname."' AND from_host_name='".$model->css_hostname."'")->queryRow();
                                            if(!empty($ranwanResult1)){
                                                $model->css_master_ranip = $ranwanResult1['to_addr'];
                                                $model->css_slave_ranip = $ranwanResult1['from_addr'];
                                            }
                                        }
                                        if($model->css_master_ranip=="" || $model->css_slave_ranip==""){
                                            $model->downloadable=0;
                                        }
                                        //get master loopback 300 if css is directly connected too ag1
                                        if($value['fiber_microwave']=='Microwave'){
                                            $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['microwave_hostname'],$value['microwave_takeoff_point'],$value['region']);
                                            if(!empty($lb300Ips)){
                                                $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                $model->css_slave_lb301 = '';
                                                $model->is_spur = 1;
                                            }
                                        }
                                        else{
                                            if ($value['east_ag1_hostname'] == $value['east_ag1_ngbr_hostname']) {
                                                $eastAg1Hostname = $value['east_ag1_hostname'];
                                                $r = Yii::app()->db->createCommand('select ag1_hostname,ag1_master_hostname,ag1_ptp_master_lb300,ag2_ring_id,ag1_seq_no from ndd_asr920gm_ag1_output_master where ag1_hostname = "' . $eastAg1Hostname . '" and is_active = 1')->queryAll();
                                                $master = $r[0]['ag1_hostname'];
                                                $master_lb300 = $r[0]['ag1_ptp_master_lb300'];
                                                $model->css_master_lb300 = $master_lb300;

                                                //slave lb301
                                                $slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                                if(!empty($slavelb301Ips)){
                                                    $model->css_slave_lb301 = $slavelb301Ips['ptp_slave_lb301'];
                                                }
                                            } else {
                //                                    $model->css_master_lb300 = NULL;
                                                $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                                $lb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                                if(!empty($lb300Ips)){
                                                    $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                    if($piece1Count == ($key+1)){
                                                        $model->css_slave_lb301 = '';
                                                        $model->is_spur = 1;
                                                    }else{
                                                        $model->css_slave_lb301 = $lb301Ips['ptp_slave_lb301'];
                                                    }
                                                }
                                            }
                                        }
                                        

                                        $model->is_active = 1;
                                        $model->created_by = Yii::app()->session['login']['user_id'];
                                        $model->created_at = $ts;
                                        $model->isNewRecord = true;
                                        $model->save();
                                        
                                        $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                                        $pos = $key+1;
                                        array_splice($pieces[0], $pos, 0, $qry1);
                                    }
                                    $pieces2 = array_reverse($pieces[1]);
                                    $piece2Count = count($pieces2);
                                    $key2last = end($pieces2);
                                    //foreach ($pieces2 as $key => $value) {
                                    $hopcount = $hopcnt_west;
                                    $csshostnameArr = array();
                                    for($key=0;$key<sizeof($pieces2);$key++){
                                        $value = $pieces2[$key];
                                        $hopFrm = $hopcnt_west;
                                        $csshostnameArr[] = $value['host_name'];
                                        $hopcountArr = $this->getHopCount($csshostnameArr,$hopFrm);
                                        $model->css_ring_type = $_POST['NddMetroCssRingSplitDetail']['css_ring_type'];
                                        $model->id = null;
                                        $model->downloadable=1;
                                        $model->is_spur = 0;
                                        $model->clock_source_on = 'W';
                                        $model->ag2_1_hostname = $ag2_1_hostname;
                                        $model->ag2_2_hostname = $ag2_2_hostname;

                                        $model->ag1_1_hostname = $ag1_1_hostname;
                                        $model->ag1_2_hostname = $ag1_2_hostname;

                                        $model->ag1_1_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_sapid']);
                                        $model->ag1_2_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_sapid']);

                                        $model->ag1_2_gm = $value['west_ag1_hostname'];
                                        $maxHopCount = 8;
                                        if($value['fiber_microwave']=='Microwave'){
                                            $addHopCnt = 3;
                                            $model->css_ring_type = $value['fiber_microwave'];
                                            $model->css_master_hostname = $value['microwave_hostname'];
                                            $model->updateAll(array('is_active' => 0), 'is_active = "1" AND ag2_1_hostname = "' . $ag2_1_hostname.'" AND ag2_2_hostname = "' . $ag2_2_hostname.'" AND css_hostname="'.$value['host_name'].'"');
                                        }
                                        else{
                                            $addHopCnt = 1;
                                            $model->css_master_hostname = $value['west_ag1_ngbr_hostname'];
                                        }
                                        //$hopcount = $key+$hopFrm+$addHopCnt;
                                        //$hopcount += $addHopCnt;
                                        $hopcount = $hopcountArr[$key]['hopcount'];
                                        $model->split='yes';
                                        $model->hopcount=$hopcount;
                                        if($maxHopCount<($hopcount)){
                                            $model->downloadable=0;
                                        }
                                        $model->ag1_1_gm =  $ag1_1_hostname;

                                        $cssPTPDetails = $this->getCssPTPDetails($value['host_name'], $value['enode_b_sapid']);
                                        $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                                        $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                                        if($model->css_ptp_master_lb300=="" || $model->css_ptp_slave_lb301==""){
                                            $model->downloadable=0;
                                        }
                                        $model->ag1_ring_id = $Ag1ringId;
                                        $model->ag1_ring_no = $Ag1ringNo;

                                        $model->css_sapid = $value['enode_b_sapid'];
                                        $model->css_facid = $value['facid'];
                                        $model->css_neid = $value['gne_id'];
                                        $model->css_loopback = $value['loopback0_ipv4'];

                                        $model->css_hostname = $value['host_name'];
                                        if($value['fiber_microwave']=='Microwave'){
                                            $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['microwave_hostname'],$value['microwave_takeoff_point'],$value['region']);
                                            if(!empty($lb300Ips)){
                                                $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                $model->css_slave_lb301 = '';
                                                $model->is_spur = 1;
                                            }
                                        }
                                        else{
                                            if($key2last['host_name'] == $value['host_name']){
                                                $model->css_slave_hostname = '';
                                                $model->is_spur = 1;
                                            }else{
                                                if($value['ring_spur_type']=='Spur' && $value['east_ag1_ngbr_hostname']==''){
                                                    $model->is_spur = 1;
                                                    $model->css_slave_hostname = '';
                                                }
                                                else
                                                $model->css_slave_hostname = $value['east_ag1_ngbr_hostname'];
                                            }
                                        }
                                        $model->css_seq_no = $value['css_sequence_in_ring'];
                                        $model->css_ring_id = $value['css_ring_id'];
                                        $model->css_ring_no = $value['css_ring'];
                                        $model->css_region = $value['region'];

                                        $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$model->css_master_hostname."' AND to_host_name='".$model->css_hostname."'")->queryRow();
                                        if(!empty($ranwanResult1)){
                                            $model->css_master_ranip = $ranwanResult1['from_addr'];
                                            $model->css_slave_ranip = $ranwanResult1['to_addr'];
                                        }
                                        else{
                                            $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$model->css_master_hostname."' AND from_host_name='".$model->css_hostname."'")->queryRow();
                                            if(!empty($ranwanResult1)){
                                                $model->css_master_ranip = $ranwanResult1['to_addr'];
                                                $model->css_slave_ranip = $ranwanResult1['from_addr'];
                                            }
                                        }    
                                        if($model->css_master_ranip=="" || $model->css_slave_ranip==""){
                                            $model->downloadable=0;
                                        }
                                        //get master loopback 300 if css is directly connected to ag1
                                        if ($value['west_ag1_hostname'] == $value['west_ag1_ngbr_hostname']) {
                                            $westAg1Hostname = $value['west_ag1_hostname'];
                                            $res = Yii::app()->db->createCommand('select ag1_hostname,ag1_master_hostname,ag1_ptp_master_lb300,ag2_ring_id,ag1_seq_no from ndd_asr920gm_ag1_output_master where ag1_hostname = "' . $westAg1Hostname . '" and is_active = 1')->queryAll();
                                            $master = $res[0]['ag1_hostname'];
                                            $master_2_lb300 = $res[0]['ag1_ptp_master_lb300'];
                                            $model->css_master_lb300 = $master_2_lb300;    
                                            $slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                            if(!empty($slavelb301Ips)){
                                                $model->css_slave_lb301 = $slavelb301Ips['ptp_slave_lb301'];
                                            }
                                        } else {
                                            $lb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                            $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                            if(!empty($lb300Ips)){
                                                $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                if($piece2Count==($key+1)){
                                                    $model->css_slave_lb301 ='';
                                                    $model->is_spur = 1;
                                                }else{
                                                    $model->css_slave_lb301 = $lb301Ips['ptp_slave_lb301'];
                                                }
                                            }
                                        }
                                        $model->is_active = 1;
                                        $model->created_by = Yii::app()->session['login']['user_id'];
                                        $model->created_at = $ts;
                                        $model->isNewRecord = true;
                                        $model->save();
                                        $r1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                                        $pos = $key+1;
                                        array_splice($pieces2, $pos, 0, $r1);
                                    }
                                }

                                if($gm1!='' || $gm2!=''){
                                $quantity = count($r1);
                                if($gm1!=''){
                                    $pieces = array_chunk($r1, ceil($quantity / 2));
                                    $collection1 = $pieces[0];
                                    $collection2 = $pieces[1];
                                }
                                if($gm2!=''){
                                    $collection1 = array_slice($r1, 0, intval($quantity / 2), true);
                                    $collection2 = array_diff_key($r1, $collection1);
                                    $collection2 = array_values($collection2);
                                }
                                $piece1Count = count($collection1);
                                $key1last = end($collection1);
                                $hopcount = $hopcnt_east;
                                $csshostnameArr = array();
                                for($key=0;$key<sizeof($collection1);$key++){
                                    $value = $collection1[$key];
                                    $hopFrm = $hopcnt_east;
                                    $csshostnameArr[] = $value['host_name'];
                                    $hopcountArr = $this->getHopCount($csshostnameArr,$hopFrm);
                                    $model->id = null;
                                    $model->css_ring_type = $_POST['NddMetroCssRingSplitDetail']['css_ring_type'];
                                    $model->downloadable=1;
                                    $model->is_spur = 0;
                                    $model->clock_source_on = 'E';
                                    $model->ag2_1_hostname = $ag2_1_hostname;
                                    $model->ag2_2_hostname = $ag2_2_hostname;
                                    $model->ag1_1_hostname = $ag1_1_hostname;
                                    $model->ag1_2_hostname = $ag1_2_hostname;
                                    $model->ag1_1_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_sapid']);
                                    $model->ag1_2_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_sapid']);

                                    if($value['east_ag1_ngbr_hostname']== $gm1 || $value['east_ag1_ngbr_hostname']== $gm2){
                                        $model->ag1_1_gm = $value['east_ag1_ngbr_hostname'];
                                        $maxHopCount = 8;
                                    }else{
                                        $maxHopCount = 5;
                                    }
                                    if($value['fiber_microwave']=='Microwave'){
                                            $addHopCnt = 3;
                                            $model->css_ring_type = $value['fiber_microwave'];
                                            $model->css_master_hostname = $value['microwave_hostname'];
                                            $model->updateAll(array('is_active' => 0), 'is_active = "1" AND ag2_1_hostname = "' . $ag2_1_hostname.'" AND ag2_2_hostname = "' . $ag2_2_hostname.'" AND css_hostname="'.$value['host_name'].'"');
                                    }
                                    else{
                                            $addHopCnt = 1;
                                            $model->css_master_hostname = $value['east_ag1_ngbr_hostname'];
                                    }
                                    //$hopcount += $addHopCnt;
                                    $hopcount = $hopcountArr[$key]['hopcount'];
                                    $model->split='yes';
                                    $model->hopcount=$hopcount;
                                    if($maxHopCount<($hopcount)){
                                        $model->downloadable=0;
                                    }
                                    $model->ag1_2_gm = $ag1_2_hostname;

                                    $cssPTPDetails = $this->getCssPTPDetails($value['host_name'], $value['enode_b_sapid']);
                                    $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                                    $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                                    if($model->css_ptp_master_lb300=="" || $model->css_ptp_slave_lb301==""){
                                        $model->downloadable=0;
                                    }

                                    $model->ag1_ring_id = $Ag1ringId;
                                    $model->ag1_ring_no = $Ag1ringNo;
                                    $model->css_sapid = $value['enode_b_sapid'];
                                    $model->css_facid = $value['facid'];
                                    $model->css_neid = $value['gne_id'];
                                    $model->css_loopback = $value['loopback0_ipv4'];
                                    $model->css_hostname = $value['host_name'];                                    
                                    if($key1last['host_name'] == $value['host_name']){
                                        $model->css_slave_hostname = '';
                                        $model->is_spur = 1;
                                    }else{
                                        if($value['ring_spur_type']=='Spur' && $value['west_ag1_ngbr_hostname']==''){
                                                $model->is_spur = 1;
                                                $model->css_slave_hostname = '';
                                            }
                                            else
                                        $model->css_slave_hostname = $value['west_ag1_ngbr_hostname'];
                                    }
                                    $model->css_seq_no = $value['css_sequence_in_ring'];
                                    $model->css_ring_id = $value['css_ring_id'];
                                    $model->css_ring_no = $value['css_ring'];
                                    $model->css_region = $value['region'];
                                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$model->css_master_hostname."' AND to_host_name='".$model->css_hostname."'")->queryRow();
                                    if(!empty($ranwanResult1)){
                                        $model->css_master_ranip = $ranwanResult1['from_addr'];
                                        $model->css_slave_ranip = $ranwanResult1['to_addr'];
                                    }
                                    else{
                                        $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$model->css_master_hostname."' AND from_host_name='".$model->css_hostname."'")->queryRow();
                                        if(!empty($ranwanResult1)){
                                            $model->css_master_ranip = $ranwanResult1['to_addr'];
                                            $model->css_slave_ranip = $ranwanResult1['from_addr'];
                                        }
                                    }
                                    if($model->css_master_ranip=="" || $model->css_slave_ranip==""){
                                        $model->downloadable=0;
                                    }
                                    //get master loopback 300 if css is directly connected too ag1
                                    if($value['fiber_microwave']=='Microwave'){
                                            $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['microwave_hostname'],$value['microwave_takeoff_point'],$value['region']);
                                            if(!empty($lb300Ips)){
                                                $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                $model->css_slave_lb301 = '';
                                                $model->is_spur = 1;
                                            }
                                        }
                                        else{
                                        if ($value['east_ag1_hostname'] == $value['east_ag1_ngbr_hostname']) {
                                            $eastAg1Hostname = $value['east_ag1_hostname'];
                                            $r = Yii::app()->db->createCommand('select ag1_hostname,ag1_master_hostname,ag1_ptp_master_lb300,ag2_ring_id,ag1_seq_no from ndd_asr920gm_ag1_output_master where ag1_hostname = "' . $eastAg1Hostname . '" and is_active = 1')->queryAll();
                                            $master = $r[0]['ag1_hostname'];
                                            $master_lb300 = $r[0]['ag1_ptp_master_lb300'];
                                            $model->css_master_lb300 = $master_lb300;
                                            if($piece1Count == ($key+1)){
                                                    $model->is_spur = 1;
                                            }
                                            //slave lb301
                                            $slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                            if(!empty($slavelb301Ips)){
                                                $model->css_slave_lb301 = $slavelb301Ips['ptp_slave_lb301'];
                                            }
                                        } else {
        //                                    $model->css_master_lb300 = NULL;
                                            $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                            $lb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                            if(!empty($lb300Ips)){
                                                $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                if($piece1Count == ($key+1)){
                                                    $model->css_slave_lb301 = '';
                                                    $model->is_spur = 1;
                                                }else{
                                                    $model->css_slave_lb301 = $lb301Ips['ptp_slave_lb301'];
                                                }
                                            }
                                        }
                                        }        
                                    $model->is_active = 1;
                                    $model->created_by = Yii::app()->session['login']['user_id'];
                                    $model->created_at = $ts;
                                    $model->isNewRecord = true;
                                    $r1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                                    $pos = $key+1;
                                    array_splice($collection1, $pos, 0, $r1);
                                    /*foreach($r1 as $row){
                                            $collection1[] = $row;
                                        }*/
                                    $model->save();
                                } 
                                $pieces2 = array_reverse($collection2);
                                $piece2Count = count($pieces2);
                                $key2last = end($pieces2);
                                //foreach ($pieces2 as $key => $value) {
                                $hopcount = $hopcnt_west;
                                $csshostnameArr = array();
                                for($key=0;$key<sizeof($pieces2);$key++){
                                    $value = $pieces2[$key];
                                    $csshostnameArr[] = $value['host_name'];
                                    $hopFrm = $hopcnt_west;
                                    $hopcountArr = $this->getHopCount($csshostnameArr,$hopFrm);
                                    
                                    $model->id = null;
                                    $model->css_ring_type = $_POST['NddMetroCssRingSplitDetail']['css_ring_type'];
                                    $model->downloadable=1;
                                    $model->is_spur = 0;
                                    $model->clock_source_on = 'W';
                                    $model->ag2_1_hostname = $ag2_1_hostname;
                                    $model->ag2_2_hostname = $ag2_2_hostname;

                                    $model->ag1_1_hostname = $ag1_1_hostname;
                                    $model->ag1_2_hostname = $ag1_2_hostname;

                                    $model->ag1_1_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_1_sapid']);
                                    $model->ag1_2_sapid = trim($_POST['NddMetroCssRingSplitDetail']['ag1_2_sapid']);

                                    if($value['west_ag1_ngbr_hostname']== $gm1 || $value['west_ag1_ngbr_hostname']== $gm2){
                                        $model->ag1_2_gm = $value['west_ag1_ngbr_hostname'];
                                        $maxHopCount = 8;
                                    }else{
                                        $maxHopCount = 5;
                                    }
                                    if($value['fiber_microwave']=='Microwave'){
                                            $addHopCnt = 3;
                                            $model->css_ring_type = $value['fiber_microwave'];
                                            $model->css_master_hostname = $value['microwave_hostname'];
                                            $model->updateAll(array('is_active' => 0), 'is_active = "1" AND ag2_1_hostname = "' . $ag2_1_hostname.'" AND ag2_2_hostname = "' . $ag2_2_hostname.'" AND css_hostname="'.$value['host_name'].'"');
                                    }
                                    else{
                                            $addHopCnt = 1;
                                            $model->css_master_hostname = $value['west_ag1_ngbr_hostname'];
                                    }
                                    $hopcount = $hopcountArr[$key]['hopcount'];
                                    //$hopcount += $addHopCnt;
                                    $model->split='yes';
                                    $model->hopcount=$hopcount;
                                    if($maxHopCount<($hopcount)){
                                        $model->downloadable=0;
                                    }
                                    $model->ag1_1_gm =  $ag1_1_hostname;
                                    $cssPTPDetails = $this->getCssPTPDetails($value['host_name'], $value['enode_b_sapid']);
                                    $model->css_ptp_slave_lb301 = $cssPTPDetails[0];
                                    $model->css_ptp_master_lb300 = $cssPTPDetails[1];
                                    if($model->css_ptp_master_lb300=="" || $model->css_ptp_slave_lb301==""){
                                        $model->downloadable=0;
                                    }
                                    $model->ag1_ring_id = $Ag1ringId;
                                    $model->ag1_ring_no = $Ag1ringNo;

                                    $model->css_sapid = $value['enode_b_sapid'];
                                    $model->css_facid = $value['facid'];
                                    $model->css_neid = $value['gne_id'];
                                    $model->css_loopback = $value['loopback0_ipv4'];

                                    $model->css_hostname = $value['host_name'];
                                    
                                    if($key2last['host_name'] == $value['host_name']){
                                        $model->css_slave_hostname = '';
                                        $model->is_spur=1;
                                    }else{
                                        if($value['ring_spur_type']=='Spur' && $value['east_ag1_ngbr_hostname']==''){
                                                $model->is_spur = 1;
                                                $model->css_slave_hostname = '';
                                            }
                                            else
                                        $model->css_slave_hostname = $value['east_ag1_ngbr_hostname'];
                                    }
                                    $model->css_seq_no = $value['css_sequence_in_ring'];
                                    $model->css_ring_id = $value['css_ring_id'];
                                    $model->css_ring_no = $value['css_ring'];
                                    $model->css_region = $value['region'];
                                    
                                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$model->css_master_hostname."' AND to_host_name='".$model->css_hostname."'")->queryRow();
                                    if(!empty($ranwanResult1)){
                                        $model->css_master_ranip = $ranwanResult1['from_addr'];
                                        $model->css_slave_ranip = $ranwanResult1['to_addr'];
                                    }
                                    else{
                                        $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$model->css_master_hostname."' AND from_host_name='".$model->css_hostname."'")->queryRow();
                                        if(!empty($ranwanResult1)){
                                            $model->css_master_ranip = $ranwanResult1['to_addr'];
                                            $model->css_slave_ranip = $ranwanResult1['from_addr'];
                                        }
                                    }
                                    if($model->css_master_ranip=="" || $model->css_slave_ranip==""){
                                        $model->downloadable=0;
                                    }
                                    //get master loopback 300 if css is directly connected to ag1
                                    if($value['fiber_microwave']=='Microwave'){
                                            $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['microwave_hostname'],$value['microwave_takeoff_point'],$value['region']);
                                            if(!empty($lb300Ips)){
                                                $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                                $model->css_slave_lb301 = '';
                                                $model->is_spur = 1;
                                            }
                                        }
                                        else{
                                    if ($value['west_ag1_hostname'] == $value['west_ag1_ngbr_hostname']) {
                                        $westAg1Hostname = $value['west_ag1_hostname'];
                                        $res = Yii::app()->db->createCommand('select ag1_hostname,ag1_master_hostname,ag1_ptp_master_lb300,ag2_ring_id,ag1_seq_no from ndd_asr920gm_ag1_output_master where ag1_hostname = "' . $westAg1Hostname . '" and is_active = 1')->queryAll();
                                        $master = $res[0]['ag1_hostname'];
                                        $master_2_lb300 = $res[0]['ag1_ptp_master_lb300'];
                                        $model->css_master_lb300 = $master_2_lb300;
                                        //slave lb301
                                        $slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                        if(!empty($slavelb301Ips)){
                                            $model->css_slave_lb301 = $slavelb301Ips['ptp_slave_lb301'];
                                        }
                                    } else {
                                        $lb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
                                        $lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
                                        if(!empty($lb300Ips)){
                                            $model->css_master_lb300 = $lb300Ips['ptp_master_lb300'];
                                            if($piece2Count==($key+1)){
                                                $model->css_slave_lb301 ='';
                                                $model->is_spur = 1;
                                            }else{
                                                $model->css_slave_lb301 = $lb301Ips['ptp_slave_lb301'];
                                            }
                                        }
                                    }
                                        }
                                    
                                    $model->is_active = 1;
                                    $model->created_by = Yii::app()->session['login']['user_id'];
                                    $model->created_at = $ts;
                                    $model->isNewRecord = true;
                                    $model->save();
                                    $r1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                                    $pos = $key+1;
                                    array_splice($pieces2, $pos, 0, $r1);
                                }
                            }
                            
                        }
                        $this->redirect('admin',array('model'=>$model));
                    }
                }
		$this->render('create',array(
			'model'=>$model,
		));
            
        }
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['NddMetroCssRingSplitDetail']))
		{
			$model->attributes=$_POST['NddMetroCssRingSplitDetail'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
//		$this->loadModel($id)->delete();
                $model = $this->loadModel($id);
                $model->attributes = array("is_active"=>0);
                $model->modified_by = Yii::app()->session['login']['user_id'];
                $model->modified_at = date('Y-m-d H:i:s');
                $model->save();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('NddMetroCssRingSplitDetail');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new NddMetroCssRingSplitDetail('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['NddMetroCssRingSplitDetail']))
			$model->attributes=$_GET['NddMetroCssRingSplitDetail'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return NddMetroCssRingSplitDetail the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=NddMetroCssRingSplitDetail::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param NddMetroCssRingSplitDetail $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='ndd-metro-css-ring-split-detail-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
        
        public function actionFetchCssRingId() {
            $ag1pair = $_REQUEST['pairId'];
            $css_ring_id='';            
            if($ag1pair!=''){
                $sql1 = "SELECT css_ring_id from ndd_asr920gm_css_output_master where (ag1_1_hostname = :hostname_1 OR ag1_2_hostname = :hostname_2) AND is_active = 1";

                $query = Yii::app()->db->createCommand($sql1);
                $query->params = array(':hostname_1' => $hostname1,':hostname_2'=>$hostname2 );
                $query_data = $query->queryRow();
                $css_ring_id = $query_data['css_ring_id'];
            }
                return json_encode($css_ring_id);
        }
        
        public  function actionFetchCSSRings(){
            $result=array();
            $ag1pair = $_REQUEST['pairId'];
            $pairAg1s = $ag1s = explode(',', $ag1pair);
            $i=1;
            $ring_type = $_REQUEST['ring_type'];
            $eastAg1hn = $_REQUEST['eastAg1hn'];
            $westAg1hn = $_REQUEST['westAg1hn'];        
            /*foreach($ag1s as $value){
                $rs[] = Yii::app()->db->createCommand('select t.*, t1.sfp_ag1_css_hn from ndd_asr920gm_ag1_output_master t left outer join ndd_metro_sfp_detail as t1 on(t.ag1_hostname= t1.sfp_ag1_css_hn) 
                        where t.ag1_hostname ="'.$value.'"')->queryRow();
            }*/
            if($eastAg1hn!='')
            $rs[] = Yii::app()->db->createCommand('select t.*, t1.sfp_ag1_css_hn from ndd_asr920gm_ag1_output_master t left outer join ndd_metro_sfp_detail as t1 on(t.ag1_hostname= t1.sfp_ag1_css_hn) 
                        where t.ag1_hostname ="'.$eastAg1hn.'"')->queryRow();
            if($westAg1hn!='')
            $rs[] = Yii::app()->db->createCommand('select t.*, t1.sfp_ag1_css_hn from ndd_asr920gm_ag1_output_master t left outer join ndd_metro_sfp_detail as t1 on(t.ag1_hostname= t1.sfp_ag1_css_hn) 
                        where t.ag1_hostname ="'.$westAg1hn.'"')->queryRow();
            
            foreach ($rs as $r){
              if(isset($r['sfp_ag1_css_hn'])){
                    $result['gm'][] = array("gm" => $r['sfp_ag1_css_hn'], "sapid" => $r['ag1_sapid'], "facid" => $r['ag1_facid'], "neid" => $r['ag1_neid'],"hostname" => $r['ag1_hostname'], "loopback" => $r['ag1_loopback'], "ag1_seq_no" => $r['ag1_seq_no']);
                }else {
                    $result['gm'][] = array("gm" => '', "sapid" => $r['ag1_sapid'], "facid" => $r['ag1_facid'], "neid" => $r['ag1_neid'],"hostname" => $r['ag1_hostname'], "loopback" => $r['ag1_loopback'], "ag1_seq_no" => $r['ag1_seq_no']);
                }
                $i++;  
            }
//            $r1[] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $pairAg1s[0] . '" and t.west_ag1_hostname = "' . $pairAg1s[1] . '") or (t.west_ag1_hostname = "' . $pairAg1s[0] . '" and t.east_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0 group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
            if($ring_type=='Incomplete Ring'){
                //echo 'select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $pairAg1s[0] . '" OR t.west_ag1_hostname = "' . $pairAg1s[1] . '" or t.west_ag1_hostname = "' . $pairAg1s[0] . '" OR t.east_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0  and (t.fiber_microwave="Fiber") and (t.ring_spur_type ="'.$ring_type.'") group by CONCAT(t.css_ring_id,"-",t.css_ring) ';
                $result['css_ring'] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $eastAg1hn . '" OR t.west_ag1_hostname = "' . $eastAg1hn . '")) and t.css_ring!=0  and (t.fiber_microwave="Fiber") and (t.ring_spur_type ="'.$ring_type.'") group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
            }
            else{
                $result['css_ring'] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $eastAg1hn . '" and t.west_ag1_hostname = "' . $westAg1hn . '") or (t.west_ag1_hostname = "' . $eastAg1hn . '" and t.east_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0  and (t.fiber_microwave="Fiber") and (t.ring_spur_type ="'.$ring_type.'") group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
            }
            
            //$result = array_merge($result, $r1);
            echo json_encode($result);
        }
        public function actionFetchag1pairdetails() 
        {
            $result=array();
            $ag1pair = $_REQUEST['pairId'];
            $pairAg1s = $ag1s = explode(',', $ag1pair);
            $i=1;
            $ring_type = $_REQUEST['ring_type'];
            foreach($ag1s as $value){
                $rs[] = Yii::app()->db->createCommand('select t.*, t1.sfp_ag1_css_hn from ndd_asr920gm_ag1_output_master t left outer join ndd_metro_sfp_detail as t1 on(t.ag1_hostname= t1.sfp_ag1_css_hn) 
                        where t.ag1_hostname ="'.$value.'"')->queryRow();
            }
            foreach ($rs as $r){
              if(isset($r['sfp_ag1_css_hn'])){
                    $result[] = array("gm$i" => $r['sfp_ag1_css_hn'], "sapid$i" => $r['ag1_sapid'], "facid$i" => $r['ag1_facid'], "neid$i" => $r['ag1_neid'],"hostname$i" => $r['ag1_hostname'], "loopback$i" => $r['ag1_loopback'], "ag1_seq_no_$i" => $r['ag1_seq_no']);
                }else {
                    $result[] = array("gm$i" => '', "sapid$i" => $r['ag1_sapid'], "facid$i" => $r['ag1_facid'], "neid$i" => $r['ag1_neid'],"hostname$i" => $r['ag1_hostname'], "loopback$i" => $r['ag1_loopback'], "ag1_seq_no_$i" => $r['ag1_seq_no']);
                }
                $i++;  
            }
//            $r1[] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $pairAg1s[0] . '" and t.west_ag1_hostname = "' . $pairAg1s[1] . '") or (t.west_ag1_hostname = "' . $pairAg1s[0] . '" and t.east_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0 group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
            if($ring_type=='Incomplete Ring'){
                if($_POST['singleag1']=='yes'){
                    $r1[] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $pairAg1s[0] . '" and t.west_ag1_hostname = "") or (t.east_ag1_hostname = "" and t.west_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0  and (t.fiber_microwave="Fiber") and (t.ring_spur_type ="'.$ring_type.'") group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
                }
                else{
                    $r1[] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $pairAg1s[0] . '" and t.west_ag1_hostname = "' . $pairAg1s[1] . '") or (t.west_ag1_hostname = "' . $pairAg1s[0] . '" and t.east_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0  and (t.fiber_microwave="Fiber") and (t.ring_spur_type ="'.$ring_type.'") group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
                }
                
            }
            else{
                $r1[] = Yii::app()->db->createCommand('select CONCAT(t.css_ring_id,"-",t.css_ring) AS css_ring_id from ndd_output_master t where ((t.east_ag1_hostname = "' . $pairAg1s[0] . '" and t.west_ag1_hostname = "' . $pairAg1s[1] . '") or (t.west_ag1_hostname = "' . $pairAg1s[0] . '" and t.east_ag1_hostname = "' . $pairAg1s[1] . '")) and t.css_ring!=0  and (t.fiber_microwave="Fiber") and (t.ring_spur_type ="'.$ring_type.'") group by CONCAT(t.css_ring_id,"-",t.css_ring) ')->queryAll();
            }
            
            $result = array_merge($result, $r1);
            echo json_encode($result);
        }
        
        public function getHopCountAG1($seqno){
            $hopcnt = 1;
            switch($seqno){
                case 1:$hopcnt=3;
                case 2:$hopcnt=4;
                case 3:$hopcnt=4;
                case 4:$hopcnt=3;    
            }
            return $hopcnt;
        }
        public function actionfetsplitcss(){
            $directions=array();
            $cssringIdNo = $_REQUEST['cssringId'];
            //$cssringdetail = explode('-',$cssringIdNo,2);
            $cssringdetails = explode('-', $cssringIdNo);
            $last = array_pop($cssringdetails);
            $cssringdetail = array(implode('-', $cssringdetails), $last);
            $cssringId =$cssringdetail[0];
            $cssringNo =$cssringdetail[1];
            $gm1 = $_REQUEST['gm1'];
            $gm2 = $_REQUEST['gm2'];
            $hop1 = $_REQUEST['hop1'];
            $hop2 = $_REQUEST['hop2'];
            $ag1_1_hostname = $_REQUEST['ag1_1_hostname'];
            $ag1_2_hostname = $_REQUEST['ag1_2_hostname'];
            $ring_type = $_REQUEST['ring_type'];
            
                /*echo 'select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.css_ring_id="'.$cssringId.'" and t1.is_disabled=0 and t.css_ring="'.$cssringNo.'" and (t.fiber_microwave="Fiber" OR t.fiber_microwave="Spur") and (t.ring_spur_type="Complete Ring" OR t.ring_spur_type="Incomplete Ring") order by t.css_sequence_in_ring';*/
                if($ring_type=="Incomplete Ring"){
                    $r1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.css_ring_id="'.$cssringId.'" and t1.is_disabled=0 and t.css_ring="'.$cssringNo.'" and (t.fiber_microwave="Fiber") and (t.ring_spur_type="Incomplete Ring") order by t.css_sequence_in_ring')->queryAll();
                }
                else if($ring_type=="Microwave"){
                    /*echo 'select t.host_name,t.microwave_hostname, t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where (t.microwave_hostname="'.$ag1_1_hostname.'" AND t.microwave_hostname="'.$ag1_2_hostname.'") and t1.is_disabled=0 and (t.fiber_microwave="microwave") and (t.ring_spur_type="Spur") order by t.css_sequence_in_ring';
                */ 
                    $r1 = Yii::app()->db->createCommand('select t.host_name,t.microwave_hostname, t1.request_id,t1.is_disabled from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where (t.microwave_hostname="'.$ag1_1_hostname.'" OR t.microwave_hostname="'.$ag1_2_hostname.'") and t1.is_disabled=0 and (t.fiber_microwave="microwave") and (t.ring_spur_type="Spur") order by t.css_sequence_in_ring')->queryAll();
                }
                else{
                    $r1 = Yii::app()->db->createCommand('select t.host_name, t.css_sequence_in_ring, t.east_ag1_ngbr_hostname, t.west_ag1_ngbr_hostname, t.enode_b_sapid, t.request_id, t.css_ring_id,t.css_ring,t.east_ag1_hostname, t.west_ag1_hostname,
                t1.sapid,t1.request_id,t1.is_disabled,t.ring_spur_type,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
                on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.css_ring_id="'.$cssringId.'" and t1.is_disabled=0 and t.css_ring="'.$cssringNo.'" and (t.fiber_microwave="Fiber") and (t.ring_spur_type="Complete Ring") order by t.css_sequence_in_ring')->queryAll();
                }
                
            
            $quantity = count($r1);
            if ($r1!=null && $quantity>0){
                $val1 ='';
                $val2 ='';
                $dir1 ='';
                $dir2 ='';
                //if($r1[0]['ring_spur_type']=="Incomplete Ring"){
                $dir = 'east';
                if($ring_type=="Incomplete Ring"){
                    $j=0;
                    //foreach ($r1 as $key => $value) {
                    
                    for ($key=0;$key<sizeof($r1);$key++) {
                            $value = $r1[$key];
                        if($j==0 && $value['css_sequence_in_ring']>1)
                            $dir = 'west';
                        
                        if($dir == 'west'){
                            /*if(strpos($dir2,$value['west_ag1_ngbr_hostname']) == false){
                                $dir2.= $value['west_ag1_hostname'];
                                $val2.= $value['west_ag1_hostname'];
                            }*/
                            if($value['fiber_microwave']=='Microwave')
                                $hop2=$hop2+3;
                            else
                            $hop2++;
                            $val2.= ",".$value['host_name'];
                            //$dir2.= "-(".$hop2.")".$value['host_name'];
                            $dir2.= "-(".$hop2.")".$value['host_name'];
                        }
                        else{
                            /*if(strpos($dir1,$value['east_ag1_ngbr_hostname']) == false){
                                $dir1.= $value['east_ag1_hostname'];
                                $val1.= $value['east_ag1_hostname'];
                            }*/
                            if($value['fiber_microwave']=='Microwave')
                                $hop1=$hop1+3;
                            else
                            $hop1++;
                            $val1.= ",".$value['host_name'];
                            $dir1.= "-(".$hop1.")".$value['host_name'];
                        }
                        $j++;                        
                        $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();                        
                        $pos = $key+1;
                        array_splice($r1, $pos, 0, $qry1);    
                    }
                    $directions['val1'] = $val1; 
                    $directions['dir1'] = $dir1; 
                    $directions['val2'] = $val2; 
                    $directions['dir2'] = $dir2;
                }
                else if($ring_type=="Microwave"){
                    
                    foreach ($r1 as $key => $value) {
                            if($value['microwave_hostname'] == $ag1_1_hostname){
                                $dir1.= $ag1_1_hostname;
                                $val1.= $ag1_1_hostname;
                                if($ring_type=="Microwave")
                                    $hop1 = $hop1+3;
                                else
                                    $hop1++;
                                $val1.= ",".$value['host_name'];
                                $dir1.= "-(".$hop1.")".$value['host_name'];
                            }
                            else if($value['microwave_hostname'] == $ag1_2_hostname){
                                $dir2.= $ag1_2_hostname;
                                $val2.= $ag1_2_hostname;
                                if($ring_type=="Microwave")
                                    $hop2 = $hop2+3;
                                else
                                    $hop2++;
                                $val2.= ",".$value['host_name'];
                                $dir2.= "-(".$hop2.")".$value['host_name'];
                            }
                        }
                    $directions['val1'] = $val1; 
                    $directions['dir1'] = $dir1; 
                    $directions['val2'] = $val2; 
                    $directions['dir2'] = $dir2;    
                }
                else{
                    $dir1 = $ag1_1_hostname;
                    $val1 = $ag1_1_hostname;
                    $dir2 = $ag1_2_hostname;
                    $val2 = $ag1_2_hostname;
                    if($gm1=='' && $gm2==''){
                        $pieces = array_chunk($r1, ceil($quantity / 2));
                        
                        for ($key=0;$key<sizeof($pieces[0]);$key++) {
                            $value = $pieces[0][$key];
                        //foreach ($pieces[0] as $key => $value) {
                            /*if(strpos($dir1,$value['east_ag1_ngbr_hostname']) == false){
                                $dir1.= $value['east_ag1_hostname'];
                                $val1.= $value['east_ag1_hostname'];
                            }*/
                            //$hopcnt = $hop1+$value['css_sequence_in_ring'];
                            if($value['fiber_microwave']=='Microwave')
                                $hop1=$hop1+3;
                            else
                            $hop1++;
                            $val1.= ",".$value['host_name'];
                            $dir1.= "-(".$hop1.")".$value['host_name'];
                            $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                            $pos = $key+1;
                            array_splice($pieces[0], $pos, 0, $qry1);
                            /*foreach($qry1 as $row){
                                //$pieces[0][] = $row;
                                array_splice($pieces[0], $pos, 0, $row);
                                $pos++;    
                            }*/
                        }
                        $pieces2 = array_reverse($pieces[1]);
                        
                        for ($key=0;$key<sizeof($pieces2);$key++) {
                            $value = $pieces2[$key];
                        //foreach ($pieces2 as $key => $value) {
                            /*if(strpos($dir2,$value['west_ag1_ngbr_hostname']) == false){
                                $dir2.= $value['west_ag1_hostname'];
                                $val2.= $value['west_ag1_hostname'];
                            }*/
                            //$hopcnt = $hop2+$value['css_sequence_in_ring'];
                            if($value['fiber_microwave']=='Microwave')
                                $hop2=$hop2+3;
                            else
                            $hop2++;
                            $val2.= ",".$value['host_name'];
                            $dir2.= "-(".$hop2.")".$value['host_name'];
                            $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                            $pos = $key+1;
                            array_splice($pieces2, $pos, 0, $qry1);
                            /*foreach($qry1 as $row){
                                //$pieces2[] = $row;
                                array_splice($pieces2, $pos, 0, $row);
                                $pos++;
                            }*/
                        }
                    }
                    if($gm1!=='' || $gm2!==''){
                        if($gm1!==''){
                            $pieces = array_chunk($r1, ceil($quantity / 2));
                            $collection1 = $pieces[0];
                            $collection2 = $pieces[1];
                        }
                        if($gm2!==''){
                            $collection1 = array_slice($r1, 0, intval($quantity / 2), true);
                            $collection2 = array_diff_key($r1, $collection1);
                            $collection2 = array_values($collection2);
                        }
        //                $collection1 = array_slice($r1, 0, intval($quantity / 2), true);
        //                $collection2 = array_diff_key($r1, $collection1);
                        //foreach ($collection1 as $key => $value) {
                        for ($key=0;$key<sizeof($collection1);$key++) {
                            $value = $collection1[$key];    
                            /*if(strpos($dir1,$value['east_ag1_ngbr_hostname']) == false){
                                $dir1.= $value['east_ag1_hostname'];
                                $val1.= $value['east_ag1_hostname'];
                            }*/
                            $val1.= ", ".$value['host_name'];
                            $dir1.= "-(".$value['css_sequence_in_ring'].")".$value['host_name'];
                            $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                            $pos = $key+1;
                            array_splice($collection1, $pos, 0, $qry1);
                            /*foreach($qry1 as $row){
                                //$collection1[] = $row;
                                array_splice($collection1, $pos, 0, $row);
                                $pos++;
                            }*/
                        }
                        $collection2 = array_reverse($collection2);
                        //foreach ($collection2 as $key => $value) {
                        for ($key=0;$key<sizeof($collection2);$key++) {
                            $value = $collection2[$key];
                            /*if(strpos($dir2,$value['west_ag1_ngbr_hostname']) === false){
                                $dir2.= $value['west_ag1_hostname'];
                                $val2.= $value['west_ag1_hostname'];
                            }*/
                            $val2.= ",".$value['host_name'];
                            $dir2.= "-(".$value['css_sequence_in_ring'].")".$value['host_name'];
                            $qry1 = Yii::app()->db->createCommand('select t.*,t1.sapid,t1.request_id,t1.is_disabled from ndd_output_master t 
                                            left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) 
                where t.microwave_hostname="'.$value['host_name'].'" and t1.is_disabled=0 order by t.css_sequence_in_ring')->queryAll();
                            $pos = $key+1;
                            array_splice($collection2, $pos, 0, $qry1);
                            /*foreach($qry1 as $row){
                                //$collection2[] = $row;
                                array_splice($collection2, $pos, 0, $row);
                                $pos++;
                            }*/
                        }
                    }
                    $directions['val1'] = $val1; 
                    $directions['dir1'] = $dir1; 
                    $directions['val2'] = $val2; 
                    $directions['dir2'] = $dir2;
                }
                 
            }else{
                $directions['imp']=false;
            }
            echo json_encode($directions);
        }
        
    public function actionDownloadNip($id,$outputMode='D'){
        $model = NddMetroCssRingSplitDetail::model()->findByPk($id);
        $suffix = $model->css_sapid;
        $reportFilename = 'NIP-' . $suffix . '.txt';
        $textContent = $this->renderPartial('//nddMetroCssRingSplitDetail/splitcssnip', array('model' => $model), true);
        $textContent = CommonUtility::convertCSSNIPHtmlToText($textContent, $model->css_sapid);
        if ($outputMode == 'S')
            return $textContent;
        if ($outputMode != 'F') {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename=' . $reportFilename);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($textContent));
            echo $textContent;
            exit();
        }
        return false;
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
        $this->generateDeltaNIP('', $id, 'splitcssnip_slave', $type);
    }
    
    public function behaviors() {
           return array(
               'exportableGrid' => array(
                   'class' => 'application.components.ExportableGridBehavior',
                   'filename' => 'NDD_Split_CSS_Outputmaster_' . date('Y-m-d H:i:s') . '.xls',
                   'csvDelimiter' => ',', //i.e. Excel friendly csv delimiter
           ));
       }
       
       public function generateDeltaNIP($p,$id,$html,$type, $return_text = false) {
        $results = NddMetroCssRingSplitDetail::model()->findByPk($id);
        $textContent = '';
        //echo $results['css_hostname'];
        if($results['css_master_lb300']==''){
            
        }
        $slaves = $this->getSlaveDetails($results['css_hostname']);
        //print_R($slaves);exit;
        if (!empty($results)) {
            $css_ring_type = $results['css_ring_type'];
            $replArr = array();
            if($css_ring_type=='Microwave'){               
                $cssResult = Yii::app()->db->createCommand("SELECT host_name,enode_b_sapid,microwave_takeoff_point,region FROM ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) WHERE microwave_hostname = '".$results['css_master_hostname']."' AND host_name='".$results['css_hostname']."' and t1.is_disabled=0")->queryRow();
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
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master_hostname']."' AND to_host_name='".$results['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr['ranwan_ip'] = $ranwanResult1['from_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$results['css_master_hostname']."' AND from_host_name='".$results['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr['ranwan_ip'] = $ranwanResult1['to_addr'];
                    }
                }
            }
            
            $suffix = $results['css_sapid'];
            $reportFilename = 'Css_' . $suffix . '_ptp_' . $p . 'delta_nip.txt';

            $textContent = $this->renderPartial('//nddMetroCssRingSplitDetail/' . $html, array('model' => $results, 'type' => $type,'replArr'=>$replArr,'slaveArr'=>$slaves), true);
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
    public function getSlaveDetails($hostname){
        $criteria = new CDbCriteria;
        $criteria->select = '*';
        $criteria->condition = " css_master_hostname = '{$hostname}' AND is_active ='1' AND downloadable=1";
        //$criteria->limit = 1;
        //$replArr['microwave_to_port']= 'gigabit';
        //$microwave_data = NddMicrowaveAddr::model()->findAll($criteria);
        $slaves = NddMetroCssRingSplitDetail::model()->findAll($criteria);
        $i=0;
        $replArr = array();
        foreach($slaves as $slave){
            $replArr[$i]['hostname']= $slave['css_hostname'];
            $replArr[$i]['hopcount']= $slave['hopcount'];
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
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$slave['css_master_hostname']."' AND to_host_name='".$slave['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr[$i]['css_slave_ranip'] = $ranwanResult1['to_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$slave['css_master_hostname']."' AND from_host_name='".$slave['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr[$i]['css_slave_ranip'] = $ranwanResult1['from_addr'];
                    }
                }
                $slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($cssResult['microwave_hostname'],$cssResult['microwave_takeoff_point'],$cssResult['region']);
                $replArr[$i]['css_slave_lb301'] = $slave['css_ptp_slave_lb301'];
                //$replArr[$i]['css_slave_lb301'] = $slavelb301Ips['ptp_slave_lb301'];
                //$replArr[$i]['css_slave_lb300'] = $slavelb301Ips['ptp_slave_lb300'];
            }
            else{
                $replArr[$i]['type']= 'Fiber';
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.from_host_name = '".$slave['css_master_hostname']."' AND to_host_name='".$slave['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr[$i]['css_slave_ranip'] = $ranwanResult1['to_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select from_addr,to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$slave['css_master_hostname']."' AND from_host_name='".$slave['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr[$i]['css_slave_ranip'] = $ranwanResult1['from_addr'];
                    }
                }
                //$replArr[$i]['css_slave_ranip'] = $slave['css_master_ranip'];
                $replArr[$i]['css_slave_lb301'] = $slave['css_ptp_slave_lb301'];
            }
            $i++;
        }
        return $replArr;
    }
    public function actiongetSlaveDetails1($hostname){
        
        $criteria = new CDbCriteria;
        $criteria->select = '*';
        $criteria->condition = " css_master_hostname = '{$hostname}' AND is_active ='1' ";
        //$criteria->limit = 1;
        //$replArr['microwave_to_port']= 'gigabit';
        //$microwave_data = NddMicrowaveAddr::model()->findAll($criteria);
        $slaves = NddMetroCssRingSplitDetail::model()->findAll($criteria);
        $i=0;
        $replArr = array();
        
        foreach($slaves as $slave){
            $replArr[$i]['hostname']= $slave['css_hostname'];
            $replArr[$i]['hopcount']= $slave['hopcount'];
            if($slave['css_ring_type']=='Microwave'){
                $replArr[$i]['type']= 'MW';                
                $cssResult = Yii::app()->db->createCommand("SELECT host_name,enode_b_sapid,microwave_takeoff_point,east_ag1_ngbr_hostname,west_ag1_ngbr_hostname,region FROM ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) WHERE microwave_hostname = '".$hostname."' AND host_name='".$slave['css_hostname']."' and t1.is_disabled=0")->queryRow();
                $criteria = new CDbCriteria;
                $criteria->select = 'to_port,from_port';
                $criteria->condition = " from_modified_sapid = '{$cssResult['microwave_takeoff_point']}' AND to_modified_sapid ='{$results['css_sapid']}' ";
                $criteria->limit = 1;
                //$replArr['microwave_to_port']= 'gigabit';
                $microwave_data = NddMicrowaveAddr::model()->findAll($criteria);             
                if(!empty($microwave_data)){
                    $replArr[$i]['to_port']= $microwave_data[0]['to_port'];
                    $replArr[$i]['from_port']= $microwave_data[0]['from_port'];
                    
                }
                //echo "select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master_hostname']."' AND from_modified_sapid='".$cssResult['microwave_takeoff_point']."' AND to_host_name='".$results['css_hostname']."'";exit;
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master_hostname']."' AND from_modified_sapid='".$cssResult['enode_b_sapid']."' AND to_host_name='".$results['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr[$i]['ranwan_ip'] = $ranwanResult1['from_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$results['css_master_hostname']."' AND to_modified_sapid='".$cssResult['microwave_takeoff_point']."' AND from_host_name='".$results['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr[$i]['ranwan_ip'] = $ranwanResult1['to_addr'];
                    }
                }
                $slavelb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($cssResult['microwave_hostname'],$cssResult['microwave_takeoff_point'],$cssResult['region']);
                $replArr[$i]['css_slave_lb301'] = $slavelb301Ips['ptp_slave_lb301'];
                $replArr[$i]['css_slave_lb300'] = $slavelb301Ips['ptp_slave_lb300'];
                
            }
            else{
                $replArr[$i]['type']= 'fiber';
                //if($slave['clock_source_on']=='E')
                //$cssResult = Yii::app()->db->createCommand("SELECT host_name,enode_b_sapid,microwave_takeoff_point,east_ag1_ngbr_hostname,west_ag1_ngbr_hostname,region FROM ndd_output_master t left join ndd_request_master t1 on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) WHERE microwave_hostname = '".$hostname."' AND host_name='".$slave['css_hostname']."' and t1.is_disabled=0")->queryRow();
                $criteria = new CDbCriteria;
                $criteria->select = 'to_port,from_port';
                $criteria->condition = " from_modified_sapid = '{$cssResult['microwave_takeoff_point']}' AND to_modified_sapid ='{$results['css_sapid']}' ";
                $criteria->limit = 1;
                //$replArr['microwave_to_port']= 'gigabit';
                $microwave_data = NddMicrowaveAddr::model()->findAll($criteria);             
                if(!empty($microwave_data)){
                    $replArr[$i]['to_port']= $microwave_data[0]['to_port'];
                    $replArr[$i]['from_port']= $microwave_data[0]['from_port'];
                    
                }
                //echo "select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master_hostname']."' AND from_modified_sapid='".$cssResult['microwave_takeoff_point']."' AND to_host_name='".$results['css_hostname']."'";exit;
                $ranwanResult1 = Yii::app()->db->createCommand("select from_addr from ndd_ran_wan t WHERE t.from_host_name = '".$results['css_master_hostname']."' AND from_modified_sapid='".$cssResult['enode_b_sapid']."' AND to_host_name='".$results['css_hostname']."'")->queryRow();
                if(!empty($ranwanResult1)){
                    $replArr[$i]['ranwan_ip'] = $ranwanResult1['from_addr'];
                }
                else{
                    $ranwanResult1 = Yii::app()->db->createCommand("select to_addr from ndd_ran_wan t WHERE t.to_host_name = '".$results['css_master_hostname']."' AND to_modified_sapid='".$cssResult['microwave_takeoff_point']."' AND from_host_name='".$results['css_hostname']."'")->queryRow();
                    if(!empty($ranwanResult1)){
                        $replArr[$i]['ranwan_ip'] = $ranwanResult1['to_addr'];
                    }
                }
            }
            $i++;
            //if()
        //$lb300Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['west_ag1_ngbr_hostname'],$value['west_ag1_ngbr_sapid'],$value['region']);
        //$lb301Ips = NddPtpLb300Lb301Master::model()->doLookupPtpLb300Master($value['east_ag1_ngbr_hostname'],$value['east_ag1_ngbr_sapid'],$value['region']);
        }
        
        exit;
    } 
    
    
    public function getHopCount($arr,$hopcnt,$dir='E'){
        $newArr = array();
        $hostArr = array();
        $hopArr = array();
        $dev_hopcnt=$hopcnt;
        
        for($i=0;$i<sizeof($arr);$i++){
            //$css_hostname_hop = explode('=>', $arr[$i]);
            $hostname = $arr[$i];
            $hostnameDtls = Yii::app()->db->createCommand('select t.microwave_hostname,t.fiber_microwave from ndd_output_master t left join ndd_request_master t1 
            on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where t.host_name="'.$hostname.'" and t1.is_disabled=0')->queryRow();
            if($hostnameDtls['fiber_microwave']=='Fiber'){
               $type='fiber';
               $hostname = $arr[$i];
               $master_hostname ='';
            }
            else{
                $hostname = $arr[$i];
                $type='MW';
                $hostnameDtls = Yii::app()->db->createCommand('select t.microwave_hostname from ndd_output_master t left join ndd_request_master t1 
            on (t.enode_b_sapid = t1.sapid and t.request_id = t1.request_id) where t.host_name="'.$hostname.'" and t1.is_disabled=0')->queryRow();
                $master_hostname = $hostnameDtls['microwave_hostname'];
            }
            
            if($type=='MW'){
                $position = array_search($master_hostname,$hostArr);
                if ($position !== false) {
                   $dev_hopcnt=$hopArr[$position];
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
}
