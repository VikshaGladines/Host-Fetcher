<?php
Class Database {

    private $dbh;

    public function __construct($user, $password, $bdd) {
        $this->dbh = new PDO('mysql:host=localhost;dbname='.$bdd, $user, $password);
    }
    public function getUser() {
        return $this->user;
    }
    public function getPassword() {
        return $this->password;
    }
    public function setUser($user) {
        $this->user = $user;
    }
    public function setPassword($password) {
        $this->password = $password;
    }


    public function load($table) {

        try {
            $query = $this->dbh->prepare('SELECT Postcode from '.$table.' LIMIT 20');
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }

    }
    public function truncate($table) {
        try {
    
            $query = $this->dbh->prepare('TRUNCATE TABLE ' . $table);
            $query->execute();
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    } 

    public function update($table, $uni, $host, $time) {
        try {
            $query = $this->dbh->prepare('INSERT INTO ' . $table . ' (HostPostCode, UniPostCode, TravelTime) VALUES (?,?,?)');
            $query->execute([$host, $uni, $time]);
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    public function selectAll($table, $selection) {
        try {
            $query = $this->dbh->prepare('SELECT ' . $selection . ' from ' . $table);
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    public function selectWhere($table, $selection, $colWhere, $where, $order, $colOrder) {
        try {
            $query = $this->dbh->prepare('SELECT ' . $selection . ' from ' . $table. ' WHERE ' . $colWhere . ' = ' . $where . ' ORDER BY ' . $colOrder . ' ' . $order);
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

}


?>