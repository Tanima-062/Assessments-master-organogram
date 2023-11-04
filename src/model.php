<?php
/**
 * Model - All kind of database query and fetching result.  
 *
 *
 * PHP version 7.3
 *
 *
 * @category   CategoryName
 * @package    Organogram
 * @author     Sarwar Hossain <sarwar@instabd.com>
 * @copyright  2020 Intalogic Bangaldesh
 * @version    1.0.1
 */
namespace Organogram;

// Include the configration file 
include_once 'config.php';


/**
 * Model Class Statically use to all over the system.
 * Usage: \Model::get()->
 * 
 */
class Model{

    /**
     * @var MySQLi Object  
     */
    private $_dbcon;

    /**
     * Constructor 
     */
    public function __construct(){
        $this->_dbcon = new \MySQLi(env('DB_HOST', 'localhost'), env('DB_USER', 'root'), env('DB_PASSWORD', ''), env('DB_NAME', 'employeeDB'));
        
        if ($this->_dbcon->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    
    
    /**
     * Static method get the Model Object 
     * @return \Organogram\Model
     */
    public static function get() {
        return new Model();
    }

    /**
     * Query : Execute the base query 
     * @param String $sql
     * @return mixed 
     */
    private function query($sql){
        return $this->_dbcon->query($sql);
    }
    
    /**
     * fetch : get the first result 
     * @param mixed $result
     * @return Array
     */
    private function fetch($result){
        $data = $result->fetch_assoc();
        $result->free_result();
        $this->_dbcon->close();
        return $data; 

    }
    /**
     * fetchAll : get the full result of query
     * @param type $result
     * @return type
     */
    private function fetchAll($result){        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free_result();
        $this->_dbcon->close();
        return $data; 
    }

    /**
     * employee: get the employee data
     * @param type $id
     * @return type
     */
    public function employees($id = false){
        $where = $id ? "WHERE id='{$id}'" : "";
        $sql= "SELECT * FROM users {$where}"; 
        $result = $this->query($sql);
        $data = $this->fetchAll($result);
        return $data; 
    }

    /**
     * ToDo:: // do something
     */
    public function roles(){
        // do something

        // return all roles data from roles table

        $sql = "SELECT * FROM roles";

        $result = $this->get()->query($sql);
        $data = $this->get()->fetchAll($result);

        return $data;

    }
    
    /**
     * ToDo:: // do something
     */

    public function department(){
        // do something

        //return all departments data from departments table

        $sql = "SELECT * FROM departments";

        $result = $this->get()->query($sql);
        $data = $this->get()->fetchAll($result);

        return $data;
    }
    
    /**
     * ToDo:: // do something
     */

    public function employeeUnderMe($employeeId, $departmentId){
        // do something

        // Since one user/employee from different departments can have different roles so user,role,department has many to many relationships, so I have created user_role_departments table where I put user_id,role_id,department_id as foreign key from users,roles,departments table.

        //This query is for getting role_id from given department of given user/employee 

        $sql = "SELECT * FROM user_role_departments WHERE user_id = '$employeeId' AND department_id = '$departmentId'";

        $result = $this->get()->query($sql);
        $data = $this->get()->fetch($result);

        $descendantIds = [];
        $parentId = $data['role_id'];

        //This loop is for getting the roles under the given user/employee role and push the role_id into descendantIds array.

        while(true){
            $sql = "SELECT * FROM roles WHERE parent_id = '$parentId'";
            $result = $this->get()->query($sql);
            if(mysqli_num_rows($result) > 0){
                $data = $this->get()->fetch($result);
                array_push($descendantIds, $data['id']);
                $parentId = $data['id'];
            }else{
                break;
            }
        }

        //if there is any role under the given user/employee then condition will be true.

        if(count($descendantIds) > 0){
            $descendantIds = '(' .implode(',', $descendantIds).')';

            //This query for getting the userIds who are under the given user/employee role.

            $sql = "SELECT * FROM user_role_departments WHERE role_id IN $descendantIds AND department_id = '$departmentId'";

            $result = $this->get()->query($sql);

            //if there is any userId who are under the given user/employee role then condition true otherwise return empty array. no need to execute query further.

            if(mysqli_num_rows($result) > 0){

                $data = $this->get()->fetchAll($result);
                $userIds = array_column($data, 'user_id');
                $userIds = '('.implode(',', $userIds).')';

                //This query is for getting all the information of that users/employees who are under are given user/employee

                $sql = "SELECT * FROM users WHERE id IN $userIds";

                $result = $this->get()->query($sql);
                $data = $this->get()->fetchAll($result);
            }else{
                return [];
            }
        }else{
            //if no one is under the given user/employee role then $data is empty array. no need to execute query further.

            return [];
        }

        return $data;
    }


}


