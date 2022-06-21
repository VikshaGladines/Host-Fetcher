<?php
Class Database {

    private $user = 'root';
    private $password = '';

    public function __construct($user, $password) {
        $this->user = $user;
        $this->password = $password;
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


    public function connect($bdd, $table) {

        try {
            $dbh = new PDO('mysql:host=localhost;dbname='.$bdd, $this->user, $this->password);
            $query = $dbh->prepare('SELECT Postcode from '.$table.' LIMIT 60');
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