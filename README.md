# tp-Rbac
一个tp5的RBAC库,基于tp5.0.21,使用composer来安装和更新你的项目对于RBAC的需求,包含了RBAC需要的数据表的数据迁移,能够很方便的开始开发。其他版本稍作修改也可使用。

## 安装
rbac支持composer安装 [Packagist](https://packagist.org/packages/phprookiehbb/Rbac)。在项目中的文件 `composer.json` 里添加:

```json
"phprookiehbb/rbac": "dev-master"
```

或者直接运行

```sh
composer require phprookiehbb/rbac:dev-master
```

### 配置 

首先在config.php下配置好相应的数据库对应的参数：
```
'auth_config' => [         
    'auth_role'        => 'auth_role',        // 角色数据表名
    'auth_role_access' => 'auth_role_access', // 用户-角色关系表
    'auth_rule'         => 'auth_rule',         // 权限规则表
    'auth_user'         => 'user'             // 用户信息表
 ]
```

其中：

```
   auth_role                     角色表
   auth_role_access              角色关系表
   auth_rule                     权限规则表
   auth_user                     用户表

```
### 数据迁移 (可选，可以直接使用包中的crasphb_rbac.sql文件导入

首先在你的项目的某个config.php中加入如下配置：
```
'migration' => [
        'path' => ROOT_PATH .'vendor/phprookiehbb/rbac/'
],
```
然后把命令行切换到你的项目根目录Windows是cmd运行如下命令
```
php think migrate:run
```
### Example

``` php
use Crasphb\Rbac;
$rbac = new Rbac();
$rbac->check(1,'url'); //检测当前操作是否有权限
```

