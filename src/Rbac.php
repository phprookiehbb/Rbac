<?php
/**
 * @Author: CraspHB彬
 * @Date:   2018-08-13 11:20:03
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-14 10:02:48
 */
namespace Crasphb;
use think\Db;
use Crasphb\Tree;
Class Rbac{

    /**
     * 表前缀
     * @var string
     */
	protected $prefix = 'hbb_';
    /**
     * 基础配置
     * @var [type]
     */
    protected $config = [
           
        'auth_on'           => true,                 // 认证开关
		'auth_role'        => 'auth_role',        // 角色数据表名
        'auth_role_access' => 'auth_role_access', // 用户-角色关系表
        'auth_rule'         => 'auth_rule',         // 权限规则表
        'auth_user'         => 'admin'             // 用户信息表
    ];
    /**
     * 不需要检查的权限
     * @var [type]
     */
    //protected $notcheck = [];
    
    /**
     * 初始化，所有的配置都放在config文件中
     */
    public function __construct(){
    	$prefix = config('database.prefix');
    	$this->prefix = $prefix;
    	//确认参数配置
    	try {
    		$config = config('auth_config');
    		$this->config = array_merge($this->config , $config);
    	} catch (\Exception $e) {
    		throw new \Exception('请先配置参数');
    	}
    }
    /**
     * 根据url找对应的rule的id
     * @param  string $url [description]
     * @return [type]      [description]
     */
    public function getRuleId($url = ''){
       if($url == ''){
       	  $url = request()->module() . '/' . request()->controller() . '/' . request()->action();
       }

       // //过滤不需要检测的url
       // if(in_array($url , $this->notcheck)){
       // 	  return -1;
       // }
       $id = $this->getRuleIdByName($url);
       if(!$id){
       	  return 0;
       }
       return $id;
    }
    /**
     * 检测是否有权限
     * @param  [type] $rule_id  [description]
     * @param  [type] $admin_id [description]
     * @param  string $type     [description]
     * @return [type]           [description]
     */
    public function check( $admin_id , $type = 'id' , $rule_id = ''){
    	if($type == 'url'){
         	$rule_id = $this->getRuleId();
         }
    	 $rules = [];
         $rules = $this->getAuthList($admin_id , $type );
         if(empty($rules)){
         	return false;
         }
         if(!$rule_id){
         	return false;
         }
         if(!in_array($rule_id , $rules)){
            return false;
         }
         return true;
    }
    /**
     * 获取权限菜单数组结构列表
     * @param  [type] $admin_id [description]
     * @return [type]           [description]
     */
    public function getRuleMenu($admin_id){
         $rules = $this->getAuthList($admin_id);
         $auth_rule = Db::name($this->config['auth_rule'])->where(['id'=>['in',$rules],'status'=>1])->order('sort')->select();
         $tree = new Tree($auth_rule);
         $menu = $tree->getArrayList();
         return $menu;
    }
    /**
     * 找寻admin_id所拥有的的权限
     * @param  [type] $rule_id [description]
     * @param  [type] $admin_id [description]
     * @param  string $type     [id:传入id来检测权限，url：传入url来检测权限]
     * @return [type]           [description]
     */
    public function getAuthList( $admin_id  , $type = 'id'){
      
         $rules = $this->getRulesByRoleId( $this->getRoleAccessByid($admin_id) );
         return $rules;
    }
     /**
     * 通过name早到rule表的id
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    protected function getRuleIdByName($name){
       $id = Db::name($this->config['auth_rule'])->where('name',$name)->value('id');
       return $id;
    }
    /**
     * 用户角色id
     * @param  [type] $admin_id [description]
     * @return [type]           [description]
     */
    protected function getRoleAccessByid($admin_id){
        return Db::name($this->config['auth_role_access'])->where('uid',$admin_id)->value('role_id');
    }
    /**
     * 得到角色对应权限id
     * @param  [type] $role_id [description]
     * @return [type]          [description]
     */
    protected function getRulesByRoleId($role_id){
    	//拥有的权限
        $rules = Db::name($this->config['auth_role'])->where('id',$role_id)->value('rules');
        //找出不需要检测的权限
        $rules_nocheck = Db::name($this->config['auth_rule'])->where('notcheck',1)->column('id');
        //合并权限并去重
        $rule_ids = array_unique( array_merge( explode(',',$rules) , $rules_nocheck ) );
        return $rule_ids;
    }
    /**
     * 修改角色权限
     * @param  [type] $role_id [角色id]
     * @param  [type] $rules [权限菜单id数组]
     * @return [type]        [description]
     */
    public function authRoleEdit($role_id , $data){
       if(isset($data['rules'])){
           if(!empty($data['rules'])){
           	  $data['rules'] = join(',',$data['rules']);
           }
        }
       if(Db::name($this->config['auth_role'])->where('id',$role_id)->data('rules',$ids)->update()){
       	   return true;
        }else{
       	   return false;
        }
    }
    /**
     * 添加角色
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function authRoleAdd($data){
        if(isset($data['rules'])){
           if(!empty($data['rules'])){
           	  $data['rules'] = join(',',$data['rules']);
           }
        }
		if(Db::name($this->config['auth_role'])->data($data)->insert()){
       	   return true;
       	}else{
       	   return false;
       	}  

    }
    /**
     * 删除角色
     * @param  [type] $role_id [description]
     * @return [type]          [description]
     */
    public function authRoleDel($role_id){
		if(Db::name($this->config['auth_role'])->where('id',$role_id)->delete()){
       	   return true;
       	}else{
       	   return false;
       	}        	
    }
    /**
     * 添加用户角色
     * @param  [type] $admin_id [description]
     * @param  [type] $role_id  [description]
     * @return [type]           [description]
     */
	public function authRoleAccessAdd($admin_id , $role_id){
		if(Db::name($this->config['auth_role_access'])->data(['uid'=>$admin_id,'role_id'=>$role_id])->insert()){
       	   return true;
       	}else{
       	   return false;
       	}    	
    }    
    /**
     * 修改用户的角色
     * @param  [type] $admin_id [description]
     * @param  [type] $role_id  [description]
     * @return [type]           [description]
     */
    public function authRoleAccessEdit($admin_id , $role_id){
		if(Db::name($this->config['auth_role_access'])->where('uid',$admin_id)->setField('role_id',$role_id)){
       	   return true;
       	}else{
       	   return false;
       	}    	
    }
    /**
     * [删除用户的角色]
     * @param  [type] $admin_id [description]
     * @return [type]           [description]
     */
	public function authRoleAccessDel($admin_id){
		if(Db::name($this->config['auth_role_access'])->where('uid',$admin_id)->delete()){
       	   return true;
       	}else{
       	   return false;
       	}    	
    }    
    /**
     * 添加权限菜单
     * @param  [type] $data [数组]
     * @return [type]       [description]
     */
    public function authRuleAdd($data){
       $res = Db::name($this->config['auth_rule'])->data($data)->insert();
       if($res){
       	  return true;
       }else{
       	  return false;
       }
    }
    /**
     * 修改权限菜单
     * @param  [type] $data [数组]
     * @return [type] [description]
     */
    public function authRuleEdit($data){
        $res = Db::name($this->config['auth_rule'])->data($data)->where('id',$data['id'])->update();
		if($res){
       	  	return true;
       	}else{
       		return false;
       	}        
    }
    /**
     * 删除权限菜单
     * @param  [type] $rule_id [description]
     * @return [type]          [description]
     */
	public function authRuleDel($rule_id){
        $res = Db::name($this->config['auth_rule'])->where('id',$rule_id)->delete();
		if($res){
       	  	return true;
       	}else{
       		return false;
       	}        
    }   
    /**
     * 获得面包屑导航
     * @return [type] [description]
     */
    public function getBreadcrumb($admin_id){
    	//获取有的权限列表
		 $rules = $this->getAuthList($admin_id);
		 $rule_id = $this->getRuleId();
         $auth_rule = Db::name($this->config['auth_rule'])->where(['id'=>['in',$rules],'status'=>1])->order('sort')->select();
         $tree = new Tree($auth_rule);
         //获取所有父类的id
         $ids = $tree->getParents($rule_id);   

         $breadcrumb = Db::name($this->config['auth_rule'])->where('id','in',$ids)->order('level')->field('id,title')->select(); 

         return $breadcrumb;      
    }
}