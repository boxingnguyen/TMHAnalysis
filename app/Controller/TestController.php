<?php
/**
* 
*/
class TestController extends AppController
{

      public $component=['RequestHandler'];
    //public function index(){
        // for ($i = 0; $i<100; $i++){
        //     $arr[]=$i;
        // }
        // $this->set('arr',$arr);
   // }
    public function index(){
        // $this->layout=false;
        // $this->autoRender=false;
        if($this->request->isAjax()){
            $this->layout=false;
         $this->autoRender=false;
        if($this->Session->check('User.id')){
            $id = $this->Session->read('User.id');
        }else{
            $id = '';
        }

        $m= new MongoClient();
        $d= $m->follow;
        $col=$d->selectCollection(date('Y-m'));


         $page = isset($_POST['page']) ? $_POST['page'] : 1;
         $id=$_POST['ID'];

         $start=$page*2;
        $data= $col->find(array($id => array('$exists' => 1)),array($id =>array('$slice'=>[$start,2])));
        $follows=[];
        foreach ($data as $item) {
           
                $follows=$item;
                  }
                  $follows=$item[$id];
                  return json_encode($follows);
              }
                 
    }
    public function total(){
        $this->layout=false;
        $this->autoRender=false;

        if($this->Session->check('User.id')){
            $id = $this->Session->read('User.id');
        }else{
            $id = '';
        }
        
       $m= new MongoClient();
        $d= $m->follow;
        $col=$d->selectCollection(date('Y-m'));
        
        $query = array('id' => $id);
        $cursor = $col->find($query,array());
        $total = 0;
        foreach ($cursor as $value){
            $total = $value['media']['count'];
        }
        return $total;
    }
    public function detail(){
        $this->layout=false;
        $this->autoRender=false;
        $code=$_GET['code'];
        $token= $this->_instagram->getOAuthToken($code);
        $aaa=$this->_instagram->setAccessToken($token);
        $data= $this->_instagram->getUserFollower();
        echo "<pre";
        print_r($data);
 
    }
    public function popup(){
       
    }
    public function getFollower(){
       
    }
    public function typeFor(){
	$this->layout=false;
	$this->autoRender=false;
    ini_set('memory_limit','1G');
    $start=microtime(true);
    for($i=0; $i<1000000;$i++)
           $arr[]=[
                'user'=>'hang'.rand(1,200),
                'media'=>rand(1,200),
                'likes'=>rand(1,200),
                'comments'=>rand(1,200),
            ];
     
     
    
    $check[]="aaa";
    for ($i=0; $i <sizeof($arr) ; $i++) { 

     	if(in_array($arr[$i]['user'],$check)){
            continue;
     	}
     	else {
     		$check[sizeof($check)]=$arr[$i]['user'];
     		$media=0;
     		$likes=0;
     		$comments=0;
     		for($j=$i; $j<sizeof($arr);$j++){
     			$media=$arr[$j]['media']+$media;
     			$likes=$arr[$j]['likes']+$likes;
     			$comments=$arr[$j]['comments']+$comments;
     		}
     		echo "user".$arr[$i]['user'];
     		echo 'media'.$media;
     		echo 'likes'.$likes;
     		echo 'comments'.$comments;
     		echo "<br>";
     		echo "<br>";
     	}
    }

     $end=microtime(true);
     $total = $end-$start;
     echo "Time:".$total;

}

public function typeDB(){
    $this->layout=false;
    $this->autoRender=false;
    $m = new MongoClient();
    $db = $m->check;
	$collection = $db->users;
    $collection->drop();
    $check[]="hang1";

     //tao dua lieu
    $start=microtime(true);
    for($i=0; $i<1000000;$i++){
        $arr[] = [
            'user'=>'hang'.rand(1,200),
            'media'=>rand(1,200),
            'likes'=>rand(1,200),
            'comments'=>rand(1,200),
        ];
        if (count($arr == 10000)) {
            $collection->batchInsert($arr);
            unset ($arr);
        }
    }
    // $collection->batchInsert($arr);
    $end=microtime(true);
    $total=$end-$start;
    echo 'Time:'.$total;
     die;      //tim trong db
        $conditions=[ '$group' => [
                                 '_id'=>'$user',
                                  'Media'=>['$sum'=>'$media'],
                                  'Likes'=>['$sum'=>'$likes'],
                                  'Comments'=>['$sum'=>'$comments']
                                   ]];
     	$data=$collection->aggregate($conditions);
        foreach ($data['result'] as $item) {
           echo 'Name'.$item['_id'];
           echo 'Media'.$item['Media'];
           echo 'Likes'.$item['Likes'];
           echo 'Comments'.$item['Comments'];
           echo "<br>";
            echo "<br>";

        }
        $end=microtime(true);
        $total=$end-$start;
        echo 'Time:'.$total;

       

}
}