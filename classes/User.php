<?php
require_once "Database.php";

class User extends Database {

    // store()
    public function store($req)
    {
        $first_name = $req['first_name'];
        $last_name  = $req['last_name'];
        $username   = $req['username'];
        $password   = $req['password'];

        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, username, password) 
                VALUES ('$first_name', '$last_name', '$username', '$password')";

        if($this->conn->query($sql)){
            header('location: ../views');   // go to index.php which is the login page
            exit;
        } else {
            die('Error creating the user: ' . $this->conn->error);
        }
    }
    // end store()


    // login()
    public function login($request)
    {
        $username = $request['username'];
        $password = $request['password'];

        $sql = "SELECT * FROM users WHERE username = '$username'";

        $result = $this->conn->query($sql);
        // $result holds the record of the user

        # check the username
        if ($result->num_rows == 1){
            #check if the password is correct
            $user = $result->fetch_assoc();
            // $user = ['id' => 1, 'first_name' => 'Mike', 'last_name' => 'Jackson', 'password' => '234SD#$@s']

            if (password_verify($password, $user['password'])){
                # Create session variables for future use.
                session_start();
                $_SESSION['id']         = $user['id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['full_name']  = $user['first_name'] . " " . $user['last_name'];

                header('location: ../views/dashboard.php');
                exit;
            } else {
                die('Password is incorrect');
            }
        } else {
            die('Username not found.');
        }


    }
    // end login()


    // logout()
    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        header('location: ../views');
        exit;
    }
    // end logout()


    // getAllUsers()
    public function getAllUsers()
    {
        $sql = "SELECT id, first_name, last_name, username, photo FROM users";

        if ($result = $this->conn->query($sql)){
            // $result holds all the user
            return $result;
        } else {
            die('Error retrieving all users' . $this->conn->error);
        }
    }
    // end getAllUsers()


    // getUser()
    public function getUser()
    {

        $id = $_SESSION['id'];

        $sql = "SELECT first_name, last_name, username, photo FROM users WHERE id = $id";

        if ($result = $this->conn->query($sql)){
            return $result->fetch_assoc();
        } else {
            die('Error retrieving the user: ' . $this->conn->error);
        }
    }
    // end getUser()


    // update()
    public function update($request, $files)
    {
        session_start();
        $id         = $_SESSION['id'];
        $first_name = $request['first_name'];
        $last_name  = $request['last_name'];
        $username   = $request['username'];
        $photo_name = $files['photo']['name'];
        $tmp_photo  = $files['photo']['tmp_name'];
        // ['photo']    - is the name of our input type file
        // ['name']     - is the actual name of the image
        // ['tmp_name'] - is the temporary storage of the image before it will be saved in assets/images folder permanently.

        $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username = '$username' WHERE id = $id";

        if ($this->conn->query($sql)){
            $_SESSION['username']     = $username;
            $_SESSION['full_name']    = "$first_name $last_name";

            # If there is an uploaded photo, save the name to db and save the actual image to assets/images folder.
            if ($photo_name){
                $sql = "UPDATE users SET photo = '$photo_name' WHERE id = $id";
                
                $destination = "../assets/images/$photo_name";

                # Save the image name to db
                if ($this->conn->query($sql)){
                    # Save the actual image to assets/images folder
                    if (move_uploaded_file($tmp_photo, $destination)){
                        header('location: ../views/dashboard.php');
                        exit;
                    } else {
                        die('Error moving the photo.');
                    }
                } else {
                    die('Error uploading photo: ' . $this->conn->error);
                }
            }
            header('location: ../views/dashboard.php');
            exit;
        } else {
            die('Error updating the user: ' . $this->conn->error);
        }
    }
    // end update()


    // delete()
    public function delete()
    {
        session_start();
        $id = $_SESSION['id'];

        $sql = "DELETE FROM users WHERE id = $id";

        if ($this->conn->query($sql)){
            $this->logout();
        } else {
            die('Error deleting your account: ' . $this->conn->error);
        }
    }
    // end delete()
}

?>