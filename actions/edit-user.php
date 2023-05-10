<?php

include "../classes/User.php";

$user = new User;

$user->update($_POST, $_FILES);
// $_FILES - holds the details of the image like name and the actual image