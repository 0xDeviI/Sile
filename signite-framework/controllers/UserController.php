<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/core/HelperFunctions.php";
require_once "signite-framework/database/connection.php";
require_once "signite-framework/models/User.php";
require_once "signite-framework/modules/Validity.php";
require_once "signite-framework/modules/Security.php";
require_once "signite-framework/modules/Identifier.php";
require_once "signite-framework/modules/Session.php";

use Signite\Core\Signite;
use Signite\Core\SigniteRequest;
use Signite\Models\User;
use Signite\Modules\Security;
use Signite\Modules\Identifier;
use Signite\Modules\Validity;
use function Signite\Modules\initializeSession;
use function Signite\Core\response;
use function Signite\Core\view;

class UserController {
    
    private Signite $_signiteApp;
    private $db;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
        $this->db = $GLOBALS['connection']->connect();
    }

    public function index() {
        //
    }

    public function create() {
        //
    }

    public function getUser(User $user) {
        $query = 'SELECT * FROM users WHERE username = "' . $user->getUsername() . '"';
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return null;
        }
    }

    public function login(SigniteRequest $request) {
        $username = $request->get("username");
        $password = $request->get("password");
        if ($username == null || $password == null) {
            return response(400, "Invalid request");
        }
        else {
            $validated = Validity::isValidUsername($username) && Validity::isValidPassword($password);
            if ($validated) {
                $user = new User("", $username, $password, $this->db);
                if ($this->isUserExist($user)) {
                    $user = $this->getUser($user);
                    // check password using JWT
                    if (Security::verifyPasswordHash($password, $user["password"])) {
                        // set session
                        initializeSession();
                        $user["password"] = $password;
                        $_SESSION["user"] = $user;
                        $_SESSION["JWT"] = Security::generateJWT($user);
                        return response(200, [
                            'status' => 'success',
                            'message' => 'Login successful',
                            'token' => $_SESSION["JWT"]
                        ])->json();
                    } else {
                        return response(401, "Invalid credentials");
                    }
                } else {
                    return response(401, "Invalid credentials");
                }
            }
            else {
                return response(400, "Invalid request");
            }
        }
    }

    public function store(SigniteRequest $request) {
        $username = $request->get("username");
        $password = $request->get("password");
        if ($username == null || $password == null) {
            return response(400, "Invalid request");
        }
        else {
            $validated = Validity::isValidUsername($username) && Validity::isValidPassword($password);
            if ($validated) {
                $newUser = new User(Identifier::uuid4(), $username, $password, $this->db);
                if ($this->isUserExist($newUser)) {
                    return response(400, [
                        'status' => 'error',
                        'message' => 'User already exist'
                    ])->json();
                } else {
                    $password = Security::generatePasswordHash($password);
                    $query = 'INSERT INTO users (id, username, password) VALUES ("' . $newUser->getId() . '", "' . $newUser->getUsername() . '", "' . $password . '")';
                    $result = $this->db->query($query);
                    if ($result) {
                        return response(201, [
                            'status' => 'success',
                            'message' => 'User created successfully'
                        ])->json();
                    } else {
                        return response(400, [
                            'status' => 'error',
                            'message' => 'User creation failed'
                        ])->json();
                    }
                }
            }
            else {
                return response(400, "Invalid request");
            }
        }
    }

    private function isUserExist(User $user): bool {
        $query = 'SELECT * FROM users WHERE username = "' . $user->getUsername() . '"';
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function show($id) {
        //
    }

    public function edit($id) {
        //
    }

    public function update(SigniteRequest $request, $id) {
        $username = $request->get("username");
        $password = $request->get("password");
        if ($username == null || $password == null || $id == null) {
            return response(400, "Invalid request");
        }
        else {
            $validated = Validity::isValidUsername($username) && Validity::isValidPassword($password);
            if ($validated) {
                $user = new User($id, $username, $password, $this->db);
                if ($user->getUsername() !== $_SESSION["user"]["username"]) {
                    if ($this->isUserExist($user)) {
                        return response(200, [
                            'status' => 'error',
                            'message' => 'User already exist'
                        ])->json();
                    } else {
                        $password = Security::generatePasswordHash($password);
                        $query = 'UPDATE users SET username = "' . $user->getUsername() . '", password = "' . $password . '" WHERE id = "' . $user->getId() . '"';
                        $result = $this->db->query($query);
                        if ($result) {
                            $_SESSION["user"]["username"] = $user->getUsername();
                            $_SESSION["user"]["password"] = $request->get("password");
                            $_SESSION["JWT"] = Security::generateJWT($_SESSION["user"]);
                            return response(201, [
                                'status' => 'success',
                                'message' => 'User updated successfully'
                            ])->json();
                        } else {
                            return response(200, [
                                'status' => 'error',
                                'message' => 'User update failed'
                            ])->json();
                        }
                    }
                }
                else {
                    $password = Security::generatePasswordHash($password);
                    $query = 'UPDATE users SET username = "' . $user->getUsername() . '", password = "' . $password . '" WHERE id = "' . $user->getId() . '"';
                    $result = $this->db->query($query);
                    if ($result) {
                        $_SESSION["user"]["username"] = $user->getUsername();
                        $_SESSION["user"]["password"] = $request->get("password");
                        $_SESSION["JWT"] = Security::generateJWT($_SESSION["user"]);
                        return response(201, [
                            'status' => 'success',
                            'message' => 'User updated successfully'
                        ])->json();
                    } else {
                        return response(200, [
                            'status' => 'error',
                            'message' => 'User update failed'
                        ])->json();
                    }
                }
            }
            else {
                return response(200, [
                    'status' => 'error',
                    'message' => 'Invalid request'
                ])->json();
            }
        }
    }

    public function destroy($id) {
        //
    }

}

?>