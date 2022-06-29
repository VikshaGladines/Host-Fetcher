<?php
class Database
{
    // Attribute that represente the database connection
    private $dbh;

    // Constructor to create a connection to the database
    public function __construct($user, $password, $bdd)
    {
        $this->dbh = new PDO('mysql:host=localhost;dbname=' . $bdd, $user, $password);
    }
    public function getUser()
    {
        return $this->user;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setUser($user)
    {
        $this->user = $user;
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * documented function
     *
     * @param string $table
     * @return array $result with all the Postcode from tables
     */
    public function load($table)
    {

        try {
            // MySql request select to select all Postcode from the tables
            $query = $this->dbh->prepare('SELECT Postcode from ' . $table . ' LIMIT 20');
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * documented function
     *
     * @param string $table
     * @return void
     * 
     * Function to empty a table
     */
    public function truncate($table)
    {
        try {

            // MySql request to empty the table  
            $query = $this->dbh->prepare('TRUNCATE TABLE ' . $table);
            $query->execute();
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * documented function
     *
     * @param string $table
     * @param string $whereCol
     * @param string $condition
     * @return void
     * 
     * function to delete line from the table you want
     */
    public function delete($table, $whereCol, $condition)
    {
        try {
            // MySql request to delete from a table the line you want
            $query = $this->dbh->prepare("DELETE FROM " . $table . " WHERE " . $whereCol . " = '" . $condition . "'");
            $query->execute();
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * documented function
     *
     * @param string $table
     * @param string $uni
     * @param string $host
     * @param int $time
     * @return void
     * 
     * function to update the table
     */
    public function update($table, $uni, $host, $time)
    {
        try {
            // MySql request to insert a the new value
            $query = $this->dbh->prepare('INSERT INTO ' . $table . ' (HostPostCode, UniPostCode, TravelTime) VALUES (?,?,?)');
            $query->execute([$host, $uni, $time]);
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * documented function
     *
     * @param string $table
     * @param string $selection
     * @return array $result
     * 
     * Function to select all the information from a table 
     */
    public function selectAll($table, $selection)
    {
        try {
            // MySql request to select all the value from a table
            $query = $this->dbh->prepare('SELECT ' . $selection . ' from ' . $table);
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * documented function
     *
     * @param string $table
     * @param string $selection
     * @param string $colWhere
     * @param string $where
     * @param string $order
     * @param string $colOrder
     * @return array $result
     * 
     * Function to select value in a table where the values match and order it  
     */
    public function selectWhere($table, $selection, $colWhere, $where, $order, $colOrder)
    {
        try {
            // MySql request to select 10 value with a condition WHERE and ordered
            $query = $this->dbh->prepare('SELECT ' . $selection . ' from ' . $table . ' WHERE ' . $colWhere . " = '" . $where . "' ORDER BY " . $colOrder . ' ' . $order . " LIMIT 10");
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * documented function
     *
     * @param string $table
     * @param string $selection
     * @param string $value
     * @return string $result
     * 
     * Function to select value from a table with multiply conditions WHERE 
     */
    public function selectWhereOr($table, $selection, $value)
    {
        try {
            $query = $this->dbh->prepare('SELECT ' . $selection . ' from ' . $table . " WHERE EstablishmentName = '" . $value . "' OR Postcode = '" . $value . "' OR Street = '" . $value . "'");
            $query->execute();
            $result = $query->fetch();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}
