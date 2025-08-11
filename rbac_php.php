<?php
enum TypePermission: string {
    case High = "high";
    case Medium = "medium";
    case Low = "low";
}
enum TypeProperty: string {
    case Name = "name";
    case Login = "login";
    case Password = "password";
    case Email = "email";
    case Title = "title";
    case Role = "role";
    case Permission = "permission";
    case ID_Role = "id_role";
    case ID_Permission = "id_permission";
    case ID_User = "id_user";
    case User = "user";
    case Worker = "worker";
    case SuperUser = "super";
    case ID_Role_Permission = "id_role_permission";
    case ID = "id";
}
final class Permission {
    private string $title;
    private int $permissionId = 0;
    public function __construct(string $title) {
        $this -> title = $title;
    }
    public function getTitle() : string {
        return $this -> title;
    }
    public function getPermissionId() : int {
        return $this -> permissionId;
    }
    public function setPermissionId(int $permissionId) : void {
        $this -> permissionId = $permissionId;
    }
    public function register() : void {
        $titleId = RegistrationPermission::returnId(
            $this);
        if ($titleId) {
            $this -> setPermissionId($titleId);
        } else {
            $lastId = RegistrationPermission::insertAndLastId(
                $this);
            $this -> setPermissionId($lastId);
        }
    }
}
final class Role {
    public const string TAB = "\n<hr>";
    private string $title;
    private int $roleId = 0;
    public function __construct(string $title) {
            $this->title = $title;
    }
    public function getTitle() : string {
        return $this -> title;
    }
    public function getRoleId() : int {
        return $this -> roleId;
    }
    public function setRoleId(int $roleId) : void {
        $this -> roleId = $roleId;
    }
    private array $permissions = [];
    public function addPermission(
        Permission $permission) : bool {
        if (!in_array(
            $permission,
            $this -> permissions)) {
                $this -> permissions[] = $permission;
                return true;
        } else {
                return false;
        }
    }
    public function hasPermission(
        Permission $permission) : bool {
        return in_array(
            $permission,
            $this -> permissions);
    }
    public function register() : void {
        $titleId = RegistrationRole::returnId($this);
        if ($titleId) {
            $this -> setRoleId($titleId);
        } else {
            $lastId = RegistrationRole::insertAndLastId(
                $this);
            $this -> setRoleId($lastId);
        }
    }
    public function regRolePermission() : void {
        foreach ($this -> permissions as $permission) {
            $id = RegistrationRolePermissions::returnId(
                $this, $permission
            );
            if (!$id) {
                RegistrationRolePermissions::insertAndLastId(
                    $this, $permission);
                }
        }
    }
}
final class User {
    private string $name;
    private string $email;
    private string $login;
    private string $password;
    private int $userId = 0;
    public function __construct(
        string $name,
        string $email,
        string $login,
        string $password) {
            $this -> name = $name;
            $this -> email = $email;
            $this -> login = $login;
            $this -> password = $password;
    }
    public function getName() : string {
        return $this -> name;
    }
    public function getEmail() : string {
        return $this -> email;
    }
    public function getLogin() : string {
        return $this -> login;
    }
    public function getPassword() : string {
        return $this -> password;
    }
    public function getUserId() : int {
        return $this -> userId;
    }
    public function setUserId(int $userId) : void {
        $this -> userId = $userId;
    }
    private array $roles = [];
    public function assignRole(Role $role) : bool {
        if (!in_array(
            $role,
            $this -> roles)) {
                $this -> roles[] = $role;
                return true;
        } else {
                return false;
        }
    }
    public function hasPermission(
        Permission $permission) : bool {
            $roles = $this -> roles;
            foreach ($roles as $role) {
                if ($role -> hasPermission($permission)) {
                    return true;
                }
            }
        return false;
    }
    public function hasRole(Role $role) : bool {
        return in_array(
            $role,
            $this -> roles
        );
    }
    public function register() : void {
        $emailId = RegistrationUser::returnId($this);
        if ($emailId) {
            $this -> setUserId($emailId);
        } else {
            $lastId = RegistrationUser::insertAndLastId(
                $this);
            $this -> setUserId($lastId);
        }
    }
    public function regUserRole() : void {
        foreach ($this -> roles as $role) {
            $id = RegistrationUserRole::returnId(
                $this, $role
            );
            if (!$id) {
                RegistrationUserRole::insertAndLastId(
                    $this, $role);
            }
        }
    }
}
final class RegistrationUser {
// ==== 1. REGISTRATION USER ====
    public static function insertAndLastId(
        User $user) : bool|int {
        
        $id = RegistrationUser::returnId($user);
        if ($id) {
            return false;
        }
        $query = Query::INSERT_USER;
        $bindParam = [
            ":" . TypeProperty::Name -> value =>
                $user -> getName(),
            ":" . TypeProperty::Email -> value =>
                $user -> getEmail(),
            ":" . TypeProperty::Login -> value =>
                $user -> getLogin(),
            ":" . TypeProperty::Password -> value =>
                $user -> getPassword()
        ];

        return WorkDB::insertAndReturnId(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }

// ==== 2. SEARCH USER BY EMAIL ====
    public static function returnId(User $user) : bool|int {

        $query = Query::SELECT_USER_ID;
        $bindParam = [
            ":" . TypeProperty::Email -> value =>
            $user -> getEmail()
        ];

        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
}
final class RegistrationPermission {
    public static function insertAndLastId(
        Permission $permission) : bool|int {

        $id = RegistrationPermission::returnId($permission);
        if ($id) {
            return false;
        }
        $query = Query::INSERT_PERMISSION;
        $bindParam = [
            ":" . TypeProperty::Title -> value =>
                $permission ->getTitle()];
        
        return WorkDB::insertAndReturnId(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function returnId(
        Permission $permission) : bool|int {

        $query = Query::SELECT_PERMISSION_ID;
        $bindParam = [
            ":" . TypeProperty::Title -> value =>
                $permission -> getTitle()];

        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
}
final class RegistrationRole {
    public static function insertAndLastId(
        Role $role) : bool|int {
        
        $id = RegistrationRole::returnId($role);
        if ($id) {
            return false;
        }

        $query = Query::INSERT_ROLE;
        $bindParam = [
            ":" . TypeProperty::Title -> value =>
                $role -> getTitle()];
        
        return WorkDB::insertAndReturnId(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function returnId(
        Role $role) : bool|int {

        $query = Query::SELECT_ROLE_ID;
        $bindParam = [
            ":" . TypeProperty::Title -> value =>
                $role -> getTitle()];

        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
}
final class RegistrationUserRole {
    public static function insertAndLastId(
        User $user, Role $role) : bool|int {

        $query = Query::INSERT_USER_ROLE;
        $bindParam = [
            ":id_user" => $user -> getUserId(),
            ":id_role" => $role -> getRoleId()
        ];
        
        return WorkDB::insertAndReturnId(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function returnId(
        User $user, Role $role) : bool|int {

        $query = Query::SELECT_USER_ROLE_ID;
        $bindParam = [
            ":id_user" => $user -> getUserId(),
            ":id_role" => $role -> getRoleId()
        ];

        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
}
final class RegistrationRolePermissions {
    public static function insertAndLastId(
        Role $role, Permission $permission) : bool|int {

        $query = Query::INSERT_ROLE_PERMISSION;
        $bindParam = [
            ":id_role" => $role -> getRoleId(),
            ":id_permission" => $permission -> getPermissionId()
        ];
        
        return WorkDB::insertAndReturnId(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function returnId(
        Role $role, Permission $permission) : bool|int {

        $query = Query::SELECT_ROLE_PERMISSION_ID;
        $bindParam = [
            ":id_role" => $role -> getRoleId(),
            ":id_permission" => $permission -> getPermissionId()
        ];

        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
}
final class Auth {

// ==== 1. SEARCH USER ID BY LOGIN AND PASSWORD ====
    public static function returnIdByLogPass(
        string $login,
        string $password) : bool|int {

        $query = Query::SELECT_USER_ID_BY_LOG_PASS;
        $bindParam = [
                ":" . TypeProperty::Login -> value =>
                    $login,
                ":" . TypeProperty::Password -> value =>
                    $password
        ];
        
        echo "<b>===> Here searching user id by log and pass:\t";
        echo __LINE__ . "</b>\n";
        
        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
// ==== 2. RETURN USER DATA BY ID ====
    public static function returnUserById(
        int $id) : bool|array {

        $query = Query::SELECT_USER_BY_ID;
        $bindParam = [
                ":" .TypeProperty::ID -> value => $id
        ];
        
        echo "<b>===> Here return user by id ($id):\t\t";
        echo __LINE__ . "</b>\n";
        
        return WorkDB::returnUserData(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
// ==== 3. CREATE NEW USER BY LOGIN AND PASSWORD ====
    public static function loginUser(
        string $login, string $password) : bool|User {
            $id = Auth::returnIdByLogPass(
                $login, $password);
            if ($id) {
                $userData = Auth::returnUserById($id);
                $user = new User(
                    $userData[TypeProperty::Name -> value],
                    $userData[TypeProperty::Email -> value],
                    $login,
                    $password
                );
                $user -> setUserId($id);
                echo "<b>===> Here creating new User:\t\t\t";
                echo __LINE__ . "</b>\n";
                
                return Auth::loadRolesPermissions($user);
            }
                
        echo "<b>===> User not found in the DB:\t\t\t";
        echo __LINE__ . "</b>\n";

        return false;
    }
// ==== 4. RETURN ROLES AND PERMISSIONS BY USER ID ====
    public static function returnRolePermissionById(
        int $id) : bool|array {

        $query = Query::SELECT_ID_RELATIONS_ROLE_PERMISSION;
        $bindParam = [
            ":" . TypeProperty::ID -> value => $id
        ];
        
        echo "<b>===> Here return roles and permissions by id:\t";
        echo __LINE__ . "</b>\n";
        
        return WorkDB::returnAllData(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
// ==== 5. SET ROLES AND PERMISSIONS TO USER ====
    public static function loadRolesPermissions(
        bool|User $user) : bool|User {

// 1. create new user
        if (!$user) {
            return false;
        }
// 2. return all roles and permissions by user id
        $rolePermissionData = Auth::returnRolePermissionById(
            $user -> getUserId());
// ============================================================
// 3. assign roles and permissions to new user
        if ($rolePermissionData) {

            $arrRoles = Auth::createRoles(
                Auth::loadIdTitle(
                    $rolePermissionData,
                    TypeProperty::ID_Role,
                    TypeProperty::Role
                    )
                );
            
            $arrPermissions = Auth::createPermission(
                Auth::loadIdTitle(
                    $rolePermissionData,
                    TypeProperty::ID_Permission,
                    TypeProperty::Permission
                    )
                );
            
            $roles = Auth::relationsRolePermission(
                $rolePermissionData,
                $arrRoles,
                $arrPermissions
            );

        echo "<b>===> Here loading roles and permissions:\t";
        echo __LINE__ . "</b>\n";

            foreach ($roles as $role) {
                $user -> assignRole($role);
            }

        echo "<b>===> Roles and permissions loaded:\t\t";
        echo __LINE__ . "</b>\n";

            return $user;
        }

        echo "<b>===> Roles and permissions not founded:\t";
        echo __LINE__ . "</b>\n";

        return false;
    }
    public static function loadIdTitle(
        array $dataDB,
        TypeProperty $id_enum,
        TypeProperty $title_enum) : array {
        
            $id = Auth::createObject(
                $dataDB,
                $id_enum);
            $title = Auth::createObject(
                $dataDB,
                $title_enum);
        
            return array_combine(
                $id,
                $title);
    }
    public static function createObject(
        array $array, TypeProperty $type) : array {
        return array_column(
            $array,
            $type -> value);
    }
    public static function createPermission(
        array $idTitle) : array {
            $permissions = [];
            foreach ($idTitle as $key => $value) {
                $permission = new Permission($value);
                $permission -> setPermissionId($key);
                $permissions[$key] = $permission;
            }
            return $permissions;
    }
    public static function createRoles(
        array $idTitle) : array {
            $roles = [];
            foreach ($idTitle as $key => $value) {
                $role = new Role($value);
                $role -> setRoleId($key);
                $roles[$key] = $role;
            }
            return $roles;
    }
    public static function relationsRolePermission(
        array $dataDB,
        array $roles,
        array $permissions) : array {

        foreach ($dataDB as $row) {
            $id_role = $row[TypeProperty::ID_Role -> value];
            $id_permission = $row[TypeProperty::ID_Permission -> value];
            $role = $roles[$id_role] ?? null;
            $permission = $permissions[$id_permission] ?? null;
            if ($role && $permission) {
                $role -> addPermission($permission);
            }
        }
        return $roles;
    }
}
final class WorkDB {
    public static function connectDB(
            string $db = "mysql",
            string $dbName = "rbac_db",
            string $host = "localhost",
            string $username = "root") : ?PDO {
        try {
            
            $dsn =
                strval($db) .
                ":host=" .
                strval($host) .
                ";dbname=" .
                strval($dbName);
            
            $dbh = new PDO(
                $dsn,
                $username,
                null);
            $dbh -> setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            return $dbh;
        } catch (PDOException $e) {
            throw new PDOException($e -> getMessage());
        }
    }
    public static function execQuery(
        PDO $dbh,
        string $query,
        array $bindParam = []) : void {
        try {
            $stmt = $dbh -> prepare($query);
            $stmt -> execute($bindParam);
            if ($query === Query::SELECT_USER_PERMISSION) {
                while ($row = $stmt -> fetch()) {
                    print_r($row);
                    echo "\n";
                }
            }
            $stmt = null;
            $dbh = null;
        } catch (Exception $e) {
            throw new Exception(
                $e -> getMessage());
        }
    }
// ==== RETURN ID ====
    public static function fetchSingleValue(
        PDO $dbh,
        string $query,
        array $bindParam = []) : bool|int {
        try {
            $stmt = $dbh -> prepare($query);
            $stmt -> execute($bindParam);
            $result = $stmt -> fetchColumn();
            return $result !== false ? $result : false;
        } catch (Exception $e) {
            echo "Error: " . $e -> getMessage();
            return false;
        }
    }
// ==== INSERT DATA AND RETURN LAST ID ====
    public static function insertAndReturnId(
        PDO $dbh,
        string $query,
        array $bindParam = []) : bool|int {
        try {
// ==== 1. INSERT NEW DATA TO DATA BASE ====
            $stmt = $dbh -> prepare($query);
            $stmt -> execute($bindParam);
// ==== 2. SELECT LAST ID OF NEW DATA ====
            $stmt = $dbh -> query(Query::SELECT_LAST_ID);
            $result = $stmt -> fetchColumn();

            $stmt = null;
            $dbh = null;
            return $result !== false ? $result : false;
        } catch (Exception $e) {
            echo "Error: " . $e -> getMessage();
            return false;
        }
    }
    public static function returnUserData(
        PDO $dbh,
        string $query,
        array $bindParam = []) : bool|array {
        try {
            $stmt = $dbh -> prepare($query);
            $stmt -> execute($bindParam);
            $result = $stmt -> fetch(PDO::FETCH_ASSOC);

            $stmt = null;
            $dbh = null;
            return $result !== false ? $result : false;
        } catch (Exception $e) {
            echo "Error: " . $e -> getMessage();
            return false;
        }
    }
    public static function returnAllData(
        PDO $dbh,
        string $query,
        array $bindParam = []) : bool|array {
        try {
            $stmt = $dbh -> prepare($query);
            $stmt -> execute($bindParam);
            $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            $dbh = null;
            return $result !== false ? $result : false;
        } catch (Exception $e) {
            echo "Error: " . $e -> getMessage();
            return false;
        }
    }
}
final class Query {
// ==== 1. INSERT USER, ROLE, PERMISSION ====
    public const string INSERT_USER =
        "insert into user
            (name, email, login, password)
        VALUES
            (:name, :email, :login, :password)";
    public const string INSERT_ROLE =
        "insert into role
            (id, title)
        VALUES
            (null, :title)";
    public const string INSERT_PERMISSION =
        "insert into permission
            (id, title)
            values
            (null, :title)";
// ==== 2. INSERT USER_ROLE, ROLE_PERMISSION ====
    public const string INSERT_USER_ROLE =
        "insert into user_role
            (id, id_user, id_role)
            values
            (null, :id_user, :id_role)";

    public const string INSERT_ROLE_PERMISSION =
        "insert into role_permission
            (id, id_role, id_permission)
            values
            (null, :id_role, :id_permission)";
// ==== 3. CREATE DATA BASE AND TABLES ====
    public const string CREATE_TABLE =
        "create table if not exists user (
            id INT NOT NULL AUTO_INCREMENT,
            name varchar(20) NOT NULL,
            email varchar(20) NOT NULL,
            login varchar(20) NOT NULL,
            password varchar(20) NOT NULL,
            
            PRIMARY KEY (id)
            ) ENGINE=INNODB;

        create table if not exists role (
            id INT NOT NULL AUTO_INCREMENT,
            title varchar(20) NOT NULL,
            
            PRIMARY KEY (id)
            ) ENGINE=INNODB;

        create table if not exists permission (
            id INT NOT NULL AUTO_INCREMENT,
            title varchar(20) NOT NULL,
            
            PRIMARY KEY (id)
            ) ENGINE=INNODB;

        create table if not exists user_role (
            id INT NOT NULL AUTO_INCREMENT,
            id_user INT NOT NULL,
            id_role INT NOT NULL,
            
            PRIMARY KEY (id),
            
            FOREIGN KEY (id_user)
                references user (id)
            ON UPDATE CASCADE ON DELETE CASCADE,
            FOREIGN KEY (id_role)
                references role (id)
            ON UPDATE CASCADE ON DELETE CASCADE
            
            ) ENGINE=INNODB;

        create table if not exists role_permission (
            id INT NOT NULL AUTO_INCREMENT,
            id_role INT NOT NULL,
            id_permission INT NOT NULL,
            
            PRIMARY KEY (id),
            
            FOREIGN KEY (id_role)
                references role (id)
            ON UPDATE CASCADE ON DELETE CASCADE,
            FOREIGN KEY (id_permission)
                references permission (id)
            ON UPDATE CASCADE ON DELETE CASCADE
            
            ) ENGINE=INNODB;";
// ==== 3. SELECT USER, ROLE, PERMISSION ID ====
    public const string SELECT_USER_PERMISSION =
        "select name, email from user where user.id = :id";
    public const string SELECT_ID_RELATIONS_ROLE_PERMISSION =
        "select
            user.id as user,
            user.name as name,
            user_role.id_user as id_user,
            user_role.id_role as id_role,
            role_permission.id_permission as id_permission,
            role.title as role,
            permission.title as permission,
            role_permission.id as id_role_permission
        from user
        left join user_role on
            user.id = user_role.id_user
        left join role on
            user_role.id_role = role.id
        left join role_permission on
            role.id =
            role_permission.id_role
        left join permission on
            role_permission.id_permission =
            permission.id
        where
            user.id = :id";
    public const string SELECT_ONLY_ROLE_PERMISSION =
        "select
            user.id as user,
            user.name as name,
            user.email as email,
            user.login as login,
            role.title as role,
            permission.title as permission
        from user
        left join user_role on
            user.id = user_role.id_user
        left join role on
            user_role.id_role = role.id
        left join role_permission on
            role.id =
            role_permission.id_role
        left join permission on
            role_permission.id_permission =
            permission.id
        where
            user.id = :id";
    public const string SELECT_USER_ID =
        "select id
            from user
        where
            email = :email";
    public const string SELECT_ROLE_ID =
        "select id
            from role
        where
            title = :title";
    public const string SELECT_PERMISSION_ID =
        "select id
            from permission
        where
            title = :title";
    public const string SELECT_USER_BY_ID =
        "select *
            from user
        where
            user.id = :id";
    public const string SELECT_USER_ID_BY_LOG_PASS =
        "select id
            from user
        where
            login = :login and
            password = :password";
// ==== 4. SELECT USER_ROLE, ROLE_PERMISSION ID, LAST ID ====
    public const string SELECT_USER_ROLE_ID =
        "select id
            from user_role
        where
            id_user = :id_user and
            id_role = :id_role";
    public const string SELECT_ROLE_PERMISSION_ID =
        "select id
            from role_permission
        where
            id_role = :id_role and
            id_permission = :id_permission";
    public const string SELECT_LAST_ID =
        "select last_insert_id()";
// ==== 5. CREATE, SELECT DATA FORM AND DELETE TABLE ====
    public const string TEST_CREATE_TABLE =
        "create table if not exists test_table_rbac (
            id INT NOT NULL AUTO_INCREMENT,
            name varchar(20) NOT NULL,
            email varchar(20) NOT NULL,
            login varchar(20) NOT NULL,
            password varchar(20) NOT NULL,
            
            PRIMARY KEY (id)
            ) ENGINE=INNODB";
    public const string TEST_HIGH_PERMISSION =
        "delete from test_table_rbac where email = :email";
    public const string TEST_MEDIUM_PERMISSION =
        "\rinsert into test_table_rbac
(name, email, login, password)
VALUES (:name, :email, :login, :password)";
    public const string TEST_LOW_PERMISSION =
        "select name, email from test_table_rbac where email = :email";
    public const string TEST_SELECT_ALL_TABLE =
        "select * from test_table_rbac";
    public const string TEST_SELECT_USER_ID =
        "select id
            from test_table_rbac
        where
            email = :email";
    public const string TEST_SELECT_ALL_USER =
        "select * from user";
}
final class Test {
// ==== TEST PERMISSIONS ====
    public static function testPermissions(
        User $user, Permission $permission) : bool|array {
        
        $title = $user -> hasPermission($permission) ?
            $permission -> getTitle() :
            TypePermission::Low -> value;
        
// ==== PERMISSION LEVEL CHANGE ACCESS (QUERY) ====
        $query = Test::matchQuery(
            $title);
        $bindParam = Test::matchParameter(
            $query,
            $user);

        return Test::queryTest(
            $bindParam,
            $query,
            $title,
            $user);
    }
    public static function matchQuery(
        string $type) : string {
        
            $query = match ($type) {
                TypePermission::High -> value =>
                    Query::TEST_HIGH_PERMISSION,
                TypePermission::Medium -> value =>
                    Query::TEST_MEDIUM_PERMISSION,
                TypePermission::Low -> value =>
                    Query::TEST_LOW_PERMISSION
            };
            return $query;
    }
    public static function matchParameter(
        string $query, User $user) : array {
        
        $bindParam = match ($query) {
            Query::TEST_HIGH_PERMISSION => [
                ":" . TypeProperty::Email -> value =>
                    $user -> getEmail()
            ],
            Query::TEST_MEDIUM_PERMISSION => [
                ":" . TypeProperty::Name -> value =>
                    $user -> getName(),
                ":" . TypeProperty::Email -> value =>
                    $user -> getEmail(),
                ":" . TypeProperty::Login -> value =>
                    $user -> getLogin(),
                ":" . TypeProperty::Password -> value =>
                    $user -> getPassword()
            ],
            Query::TEST_LOW_PERMISSION => [
                ":" . TypeProperty::Email -> value =>
                    $user -> getEmail()
            ]
        };
        return $bindParam;
    }
    public static function queryTest(
        array $bindParam,
        string $query,
        string $permission,
        User $user) : bool|array {
        
        $msg = "<hr>User by name:\t<mark><b>";
        $msg .= $user -> getName();
        $msg .= "</b></mark>\nhas permission:\t<mark><b>";
        $msg .= $permission;
        $msg .= "</b></mark>\nto send ";
        $msg .= "query:\t<mark><b>";
        $msg .= $query;
        $msg .= "</b></mark><hr><h3>Result:</h3>";

        $msg = strtoupper(trim($msg));
        echo $msg;

        return match ($query) {
            Query::TEST_LOW_PERMISSION =>
                    Test::selectUserByEmail(
                        $user, $query),

            Query::TEST_MEDIUM_PERMISSION =>
                    Test::insertAndLastId(
                        $user, $query),

            Query::TEST_HIGH_PERMISSION =>
                    Test::deleteUserByEmail(
                        $user, $query)
        };
    }
    public static function permissionTest(
        bool|null|User $user,
        Permission $permission) : bool|array {
        if (!$user) {
            
            echo "<b>===> User will be not insert to DB:\t\t";
            echo __LINE__ . "</b>\n";
            
            return false;
        }
        return Test::testPermissions(
            $user, $permission
        );
    }
    public static function deleteUserByEmail(
        bool|User $user, string $query) : bool|array {
        if (!$user) {

            echo "<b>===> User has not passed:\t";
            echo __LINE__ . "</b>\n";

            return false;
        }
        $id = Test::returnId($user);
        if (!$id) {

            echo "<b>===> User is not at DB:\t";
            echo __LINE__ . "</b>\n";

            return false;
        }
        $bindParam = [
            ":" . TypeProperty::Email -> value =>
                $user -> getEmail(),
        ];

        echo "<b>===> Query by High permission: successfully:\t";
        echo __LINE__ . "</b>\n";

        return WorkDB::returnAllData(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function insertAndLastId(
        bool|User $user, string $query) : bool|int {
        if (!$user) {

            echo "<b>===> User will be not insert to DB:\t";
            echo __LINE__ . "</b>\n";

            return false;
        }
        $id = Test::returnId($user);
        if ($id) {

            echo "<b>===> User already was inserted to DB:\t";
            echo __LINE__ . "</b>\n";

            return false;
        }
        $bindParam = [
            ":" . TypeProperty::Name -> value =>
                $user -> getName(),
            ":" . TypeProperty::Email -> value =>
                $user -> getEmail(),
            ":" . TypeProperty::Login -> value =>
                $user -> getLogin(),
            ":" . TypeProperty::Password -> value =>
                $user -> getPassword()
        ];

        echo "<b>===> Query by Medium permission: successfully:\t";
        echo __LINE__ . "</b>\n";

        return WorkDB::insertAndReturnId(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function selectUserByEmail(
        bool|User $user, string $query) : bool|array {
        if (!$user) {

            echo "<b>===> User has not passed:\t";
            echo __LINE__ . "</b>\n";

            return false;
        }
        $id = Test::returnId($user);
        if (!$id) {

            echo "<b>===> User is not at DB:\t";
            echo __LINE__ . "</b>\n";

            return false;
        }
        $bindParam = [
            ":" . TypeProperty::Email -> value =>
                $user -> getEmail(),
        ];

        echo "<b>===> Query by Low permission: successfully:\t";
        echo __LINE__ . "</b>\n";

        return WorkDB::returnAllData(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function returnId(User $user) : bool|int {

        $query = Query::TEST_SELECT_USER_ID;
        $bindParam = [
            ":" . TypeProperty::Email -> value =>
            $user -> getEmail()
        ];

        return WorkDB::fetchSingleValue(
            WorkDB::connectDB(),
            $query,
            $bindParam);
    }
    public static function loginTest(
        string $login, string $password) : bool|User {
    
            $user = Auth::loginUser(
                $login, $password
            );
            if (!$user) {

                echo "<b>===> <mark>Access to DB denied:</mark>\t\t\t";
                echo __LINE__ . "</b>\n";
                return false;
            }

            echo "<b>===> <mark>Access to DB allowed:</mark>\t\t\t";
            echo __LINE__ . "</b>\n";

            Test::tableDB(WorkDB::returnAllData(
                WorkDB::connectDB(),
                Query::SELECT_ONLY_ROLE_PERMISSION,
                [TypeProperty::ID -> value => $user -> getUserId()]
            ));

        return $user;
    }
    public static function regUser(
        string $name,
        string $email,
        string $login,
        string $password,
        Role $role
    ) : bool {
        $user = new User(
            $name,
            $email,
            $login,
            $password
        );
        if ($user -> assignRole($role)) {
            echo "<b>===> New user assign by role:\t\t\t";
            echo __LINE__ . "</b>\n";

            $user -> register();
            echo "<b>===> New user registered in the DB:\t\t";
            echo __LINE__ . "</b>\n";

            $user -> regUserRole();
            echo "<b>===> Registration relation with User and Role:\t";
            echo __LINE__ . "</b>\n";

            echo "<b>===> <mark>Registration new User completed:</mark>\t\t";
            echo __LINE__ . "</b>\n";

            Test::tableDB(WorkDB::returnAllData(
                WorkDB::connectDB(),
                Query::SELECT_ONLY_ROLE_PERMISSION,
                [TypeProperty::ID -> value => $user -> getUserId()]
            ));
            return true;
            
        }

        echo "<b>===> <mark>Can not assign user by role:</mark>\t\t\t";
        echo __LINE__ . "</b>\n";

        return false;
    }
    public static function tableDB(array|bool $table): bool  {
        if (!$table || !is_array($table)) {
            return Test::tableDB(
                WorkDB::returnAllData(
                WorkDB::connectDB(),
                Query::TEST_SELECT_ALL_TABLE));
        }
    echo <<<TABLE
    <style>
        table, th, td {
        border-style: ridge;
        border-color:rgb(0, 150, 150);
    }
        th {
        background-color: rgb(0, 200, 200);
    }
    </style>
    TABLE;
        echo "\n<table>\n";
        if (array_is_list($table)) {
            for ($i=0; $i < 1; $i++) {
                echo "\t<tr>\n";
                foreach ($table[$i] as $key => $value) {
                    echo "\t\t<th>$key</th>\n";
                }
                echo "\t</tr>\n";
            }
            foreach ($table as $column) {
                if (is_array($column)) {
                    echo "\t<tr>\n";
                    foreach ($column as $key => $value) {
                        echo "\t\t<td>$value</td>\n";
                    }
                    echo "\t</tr>\n";
                }
            }
        } else {
            echo "\t<tr>\t";
            foreach ($table as $key => $value) {
                echo "\t\t<th>$key</th>\n";
            }
            echo "\t</tr>\n";
            echo "\t<tr>\n";
            foreach ($table as $key => $value) {
                    echo "\t\t<td>$value</td>\n";
            }
            echo "\t</tr>\n";
        }
        echo "</table>";
        return true;
    }
    public static function test_input(string $input) : string {
        $input = trim($input);
        $input = stripcslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }
}

// ==== TESTING ====
// echo "<pre>";

// ==== 1. CREATING NEW USERS, ROLES, PERMISSIONS ====
$userBob = new User(
    "Bob",
    "bob@bob.com",
    "bobLogin",
    "bobPassword"
);
$userAlice = new User(
    "Alice",
    "alice@alice.com",
    "aliceLogin",
    "alicePassword"
);
$userTom = new User(
    "Tom",
    "tom@tom.com",
    "tomLogin",
    "tomPassword"
);

$roleUser = new Role(TypeProperty::User -> value);
$roleWorker = new Role(TypeProperty::Worker -> value);
$roleSuperUser = new Role(TypeProperty::SuperUser -> value);

$permissionHigh = new Permission(
    TypePermission::High -> value);
$permissionMedium = new Permission(
    TypePermission::Medium -> value);
$permissionLow = new Permission(
    TypePermission::Low -> value);

// ==== 2. TRY CONNECTION WITH DATA BASE ====
WorkDB::execQuery(
    WorkDB::connectDB(),
    Query::CREATE_TABLE
);

// ==== 3. REGISTRATION PERMISSIONS  ====
$permissionLow -> register();
$permissionMedium -> register();
$permissionHigh -> register();

// ==== 4. ASSIGN ROLES BY PERMISSIONS ====
$roleUser -> addPermission($permissionLow);

$roleWorker -> addPermission($permissionLow);
$roleWorker -> addPermission($permissionMedium);

$roleSuperUser -> addPermission($permissionLow);
$roleSuperUser -> addPermission($permissionMedium);
$roleSuperUser -> addPermission($permissionHigh);

// ==== 5. REGISTRATION ROLES AND RELATIONS (ROLE<->PERMISSION) ====
$roleUser -> register();
$roleWorker -> register();
$roleSuperUser -> register();

$roleUser -> regRolePermission();
$roleWorker -> regRolePermission();
$roleSuperUser -> regRolePermission();

// ==== 6. ASSIGN USERS BY ROLES ====
$userBob -> assignRole($roleUser);
$userAlice -> assignRole($roleWorker);
$userTom -> assignRole($roleSuperUser);
$userBob -> assignRole($roleWorker);

// ==== 7. REGISTRATION USERS AND RELATIONS (USER<->ROLE) ====
$userBob -> register();
$userAlice -> register();
$userTom -> register();

$userBob -> regUserRole();
$userAlice -> regUserRole();
$userTom -> regUserRole();

// ==== 8. TEST AUTHORIZATION ====
$userBob -> hasPermission($permissionHigh); // ERROR: PERMISSION
$userAlice -> hasPermission($permissionMedium);
$userTom -> hasPermission($permissionLow);

// ==== 9. CREATE NEW USER FOR TEST REGISTRATION ====
$userBird = new User(
    "Bird",
    "bird@bird.com",
    "bird_login",
    "bird_password"
);
$userBird -> assignRole($roleUser);
$userBird -> register();
$userBird -> regUserRole();
$userBird -> assignRole($roleSuperUser);
$userBird -> regUserRole();

// ==== 10. TEST AUTHENTICATION AND AUTHORIZATION ====

// ==== 1. NEW COPY USER ====
// ==== 2. LOAD ROLE AND PERMISSION ====
// ==== 3. TEST PERMISSION ====

// ============================================================

// echo "\n<hr>";
// echo "<h3>1. Test registration new user in the DB:</h3>";

// Test::regUser(
//     "Clare",
//     "clare@clare.com",
//     "clareLogin",
//     "clarePassword",
//     $roleWorker
// );

// echo "\n<hr>";
// echo "<h3>2. Test searching user by log and pass in the DB:</h3>";

// $login = $userAlice -> getLogin();
// $password = $userAlice -> getPassword();
// $passwordError = "passwordError";

// $userCopyAlice = Test::loginTest(
//     $login, $password
// );

// echo "\n<hr>";
// echo "<h3>3. Test permission send different queries in the DB:</h3>";
// Test::tableDB(Test::permissionTest(
//     $userAlice,
//     $permissionHigh));


$name = "";
$email = "";
$login = "";
$password_jerry = "";

$php_self = $_SERVER['PHP_SELF'];

$high = TypePermission::High -> value;
$medium = TypePermission::Medium -> value;
$low = TypePermission::Low -> value;

echo <<<_HTML

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport"
        content="width=device-width"
        initial-scale="1">
        <title>
            Role-based access control Example System
        </title>
        <style>
            table, th, td {
                border-style: ridge;
                border-color:rgb(0, 150, 150);
            }
        
            th {
                background-color: rgb(0, 200, 200);
            }
        </style>
    </head>
    <body>
        <form   method="post"
                action="{$php_self}">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">
                            Registration Authorization Authentication
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <label for="name">
                                Name
                            </label>
                        </td>
                        <td>
                            <input  type="text"
                                    id="name"
                                    name="name"
                                    value="{$name}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="email">
                                Email
                            </label>
                        </td>
                        <td>
                            <input  type="email"
                                    id="email"
                                    name="email"
                                    value="{$email}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="login">
                                Login
                            </label>
                        </td>
                        <td>
                            <input  type="text"
                                    id="login"
                                    name="login"
                                    value="{$login}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="password">
                                Password
                            </label>
                        </td>
                        <td>
                            <input  type="text"
                                    id="password"
                                    name="password"
                                    value="{$password_jerry}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="delete_user">
                                Delete user
                            </label>
                        </td>
                        <td>
                            <input  type="radio"
                                    id="delete_user"
                                    name="permission"
                                    value="{$high}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="create_user">
                                Create user
                            </label>
                        </td>
                        <td>
                            <input  type="radio"
                                    id="create_user"
                                    name="permission"
                                    value="{$medium}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="info_user">
                                Info user
                            </label>
                        </td>
                        <td>
                            <input  type="radio"
                                    id="info_user"
                                    name="permission"
                                    value="{$low}">
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <button type="submit" title="Submit">
                                Submit
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </body>
</html>

_HTML;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = Test::test_input($_POST["name"] ?? "");
    $email = Test::test_input($_POST["email"] ?? "");
    $login = Test::test_input($_POST["login"] ?? "");
    $password_jerry =
        Test::test_input($_POST["password"] ?? "");
    $permission = Test::test_input($_POST["permission"] ?? "");
}

$jerry = new User(
    $name,
    $email,
    $login,
    $password_jerry
);

$permission_object = match ($permission) {
    TypePermission::High -> value => $permissionHigh,
    TypePermission::Medium -> value => $permissionMedium,
    TypePermission::Low -> value => $permissionLow
};
echo "<pre>";
// ============================================================
echo "<hr><h3>" . Query::TEST_SELECT_ALL_USER . "</h3><hr>";
Test::tableDB(WorkDB::returnAllData(
    WorkDB::connectDB(),
    Query::TEST_SELECT_ALL_USER
));

// $jerry -> assignRole($roleSuperUser);
// $jerry -> register();
// $jerry -> regUserRole();
// Test::loginTest($login, $password_jerry);

Test::tableDB(Test::permissionTest(
    Auth::loginUser($login, $password_jerry),
    $permission_object));
