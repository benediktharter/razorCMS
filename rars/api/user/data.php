<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
class UserData extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    // add or update content
    public function post($data)
    {
        // check we have a logged in user
        if ((int) $this->check_access() < 1) $this->response(null, null, 401);
        if (empty($data)) $this->response(null, null, 400);

        $db = new RazorDB();
        $db->connect("user");

        if ($this->user["id"] == $data["id"])
        {
            // if this is your account, alter name, email or password
            $search = array("column" => "id", "value" => $this->user["id"]);

            $row = array(
                "name" => $data["name"], 
                "email_address" => $data["email_address"]
            );

            if (isset($data["new_password"])) $row["password"] = $this->create_hash($data["new_password"]);

            $db->edit_rows($search, $row);
        }
        elseif ($this->check_access() == 10)
        {
            // if not account owner, but acces of 10, alter access level or active
            // do not allow anyone to be set to level 10, only one account aloud
            if (isset($data["access_level"]) && $data["access_level"] == 10) $this->response(null, null, 400);

            $search = array("column" => "id", "value" => $data["id"]);

            $row = array(
                "access_level" => $data["access_level"], 
                "active" => $data["active"]
            );

            $db->edit_rows($search, $row);
        }
        else $this->response(null, null, 401);

        $db->disconnect(); 

        // return the basic user details
        if (isset($data["new_password"])) $this->response(array("reload" => true), "json");

        $this->response("success", "json");
    }

    // remove a user
    public function delete($id)
    {
        // check we have a logged in user
        if ((int) $this->check_access() < 1) $this->response(null, null, 401);
        if (empty($id)) $this->response(null, null, 400);
        if ($id == 1) $this->response(null, null, 400);
        $id = (int) $id;

        $db = new RazorDB();
        $db->connect("user");

        if ($this->user["id"] == $id)
        {
            // this is your account, allow removal of own account
            $search = array("column" => "id", "value" => $this->user["id"]);
            $db->delete_rows($search);
            $response = "reload";
        }
        elseif ($this->check_access() == 10)
        {
            // if not account owner, but acces of 10, can remove account
            $search = array("column" => "id", "value" => $id);
            $db->delete_rows($search);
            $response = "success";
        }
        else $this->response(null, null, 401);

        $db->disconnect(); 

        $this->response($response, "json");        
    }
}

/* EOF */