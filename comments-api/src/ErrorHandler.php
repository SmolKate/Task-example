<?php

namespace AppHandler;


function connect($sql)
{
    global $servername;
    global $username;
    global $password;
    global $database;
    try {
        $mysqli = new mysqli($servername, $username, $password, $database);
        $mysqli->set_charset("utf8");
        $link = mysqli_connect($servername, $username, $password, $database);
        $result = mysqli_query($link, $sql);
        $mysqli->close();
    } catch (PDOException $e) {
        $result = (array(
            "message" => $e->getMessage()
        ));
    };

    return $result;
};
