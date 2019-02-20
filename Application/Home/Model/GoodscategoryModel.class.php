<?php
namespace Home\Model;
use Think\Model;

class GoodscategoryModel extends Model{
    
    //获取分类树的数据
    public function getNavCatData(){
         $data=array();
        $allcat=$this->select();
         
         //获取所有顶级分类
        foreach ($allcat as $k=>$v){
            if($v['parent_id']==0){
                
                //取出顶级分类的二级分类
                foreach ($allcat as $k1=>$v1){
                    if ($v1['parent_id']==$v['cat_id']){
                        
                        //取出二级分裂下的三级分类
                        foreach ($allcat as $k2=>$v2){
                            if ($v2['parent_id']==$v1['cat_id']){
                                $v1['children'][]=$v2;
                            }
                        }
                        $v['children'][]=$v1;
                    }
                    
                }
                $data[]=$v;
            }
        }
        
        return $data;
    }


    //面包屑
    public function position($cid){//传递当前栏目id

        static $pos=array(); //创建接受面包屑导航的数组
        
        if(empty($pos)){        //如果需要把当前栏目也放到面包屑导航中的话就要加上
            $cates = $this->field("cat_id,cat_name,parent_id")->find($cid);
            $pos[]=$cates;
        }

        $data = $this->field("cat_id,cat_name,parent_id")->select();//所有栏目信息
        $cates = $this->field('cat_id,cat_name,parent_id')->find($cid);//当前栏目信息
        
        foreach($data as $k => $v) {

            if($cates['parent_id'] == $v['cat_id']){
                $pos[]=$v;
                $this->position($v['cat_id']);
            }

        }
        
        return array_reverse($pos); //数组元素相反返回
    }


    //头部分类导航
    public function topCate() {
        $where['is_selection'] = array('eq','1');
        $where['parent_id'] = array('neq','0');
        return $this->where($where)->order('addtime desc')->limit('10')->select();
    }

}