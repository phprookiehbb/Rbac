<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class Auth extends Migrator{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(){
        //auth_rule表
        $table = $this->table('auth_rule',['engine'=>'myisam','charset'=>'utf8','comment'=>'权限菜单表']);
        $table->addColumn('name','string', ['limit'=>80,'comment'=>'url地址'])
              ->addColumn('title','string',['limit'=>20,'comment'=>'名称'])
              ->addColumn('type','integer',['limit' => MysqlAdapter::INT_TINY,'default'=>1,'signed'=>false,'comment'=>'type'])
              ->addColumn('css','string',['limit'=>20,'comment'=>'css图标'])
              ->addColumn('status','integer',['limit' => MysqlAdapter::INT_TINY,'default'=>1,'signed'=>false,'comment'=>'状态，1：显示，0：不显示'])
              ->addColumn('pid','integer',['limit'=>5,'signed'=>false,'comment'=>'父栏目ID'])
              ->addColumn('sort','integer',['limit' => MysqlAdapter::INT_TINY,'default'=>10,'signed'=>false,'comment'=>'排序字段'])
              ->addColumn('level','integer',['limit' => MysqlAdapter::INT_TINY,'default'=>1,'signed'=>false,'comment'=>'菜单等级'])
              ->addColumn('addtime','integer',['limit'=>11,'default'=>0,'signed'=>false,'comment'=>'添加时间'])
              ->addColumn('notcheck','integer',['limit' => MysqlAdapter::INT_TINY,'signed'=>false,'default'=>0])
              ->addIndex(['pid'],['name'=>'pid'])
              ->addIndex(['name'],['name'=>'name'])
              ->create();

        //auth_role表
        $table = $this->table('auth_role',['engine'=>'myisam','charset'=>'utf8','comment'=>'角色权限表']);
        $table->addColumn('title','string', ['limit'=>100,'comment'=>'角色名称'])
              ->addColumn('rules','string',['limit'=>200,'comment'=>'拥有的权限'])
              ->addColumn('status','integer',['limit' => MysqlAdapter::INT_TINY,'default'=>1,'signed'=>false,'comment'=>'状态，1：显示，0：不显示'])
              ->addColumn('addtime','integer',['limit'=>11,'default'=>0,'signed'=>false,'comment'=>'添加时间'])
              ->create();   

        //auth_role_access表
        $table = $this->table('auth_role_access',['engine'=>'myisam','charset'=>'utf8','comment'=>'用户角色关系表']);
        $table->addColumn('uid','integer',['limit' => 11,'signed'=>false,'comment'=>'用户id'])
              ->addColumn('role_id','integer',['limit'=>MysqlAdapter::INT_MEDIUM,'signed'=>false,'comment'=>'角色id'])
              ->addIndex(['uid','role_id'],['name'=>'uid_role_id'])
              ->addIndex(['uid'],['name'=>'uid'])
              ->addIndex(['role_id'],['name'=>'role_id'])
              ->create();  
        $table = $this->table('auth_role_access');
        $table->removeColumn('id')
              ->save();                                
    }
}
