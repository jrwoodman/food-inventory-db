<?php
class UserController {
    private $database;
    private $db;
    private $auth;

    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->auth = new Auth($this->db);
    }

    public function login() {
        if ($this->auth->isLoggedIn()) {
            header('Location: index.php?action=dashboard');
            exit();
        }

        $error = '';
        $redirect = $_GET['redirect'] ?? 'index.php?action=dashboard';

        if ($_POST) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Please enter both username and password';
            } else {
                $result = $this->auth->login($username, $password);
                
                if ($result['success']) {
                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $error = $result['message'];
                }
            }
        }

        include '../src/views/auth/login.php';
    }

    public function logout() {
        $this->auth->logout();
        header('Location: index.php?action=login&message=You have been logged out');
        exit();
    }

    public function register() {
        // Check if current user is admin or if this is the first user
        $user_model = new User($this->db);
        $users_count = $user_model->getUsersCount();
        
        if ($users_count > 0) {
            $this->auth->requireAdmin();
        }

        // Get all groups for selection (if admin is creating user)
        $all_groups = [];
        if ($users_count > 0) {
            $group_model = new Group($this->db);
            $stmt = $group_model->read();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $all_groups[] = $row;
            }
        }

        $error = '';
        $success = '';

        if ($_POST) {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $role = $_POST['role'] ?? 'user';

            // Validate input
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Please fill in all required fields';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                // Check if username or email already exists
                $existing_user = new User($this->db);
                if ($existing_user->findByUsername($username)) {
                    $error = 'Username already exists';
                } elseif ($existing_user->findByEmail($email)) {
                    $error = 'Email already exists';
                } else {
                    // Create new user
                    $new_user = new User($this->db);
                    $new_user->username = $username;
                    $new_user->email = $email;
                    $new_user->password_hash = $password; // Will be hashed in the model
                    $new_user->first_name = $first_name;
                    $new_user->last_name = $last_name;
                    $new_user->role = $role;
                    $new_user->is_active = 1;

                    if ($new_user->create()) {
                        // Assign user to selected groups
                        if (isset($_POST['group_ids']) && is_array($_POST['group_ids'])) {
                            $group_model = new Group($this->db);
                            foreach ($_POST['group_ids'] as $group_id) {
                                $group_model->id = $group_id;
                                $group_model->addMember($new_user->id, 'member');
                            }
                        }
                        
                        if ($users_count == 0) {
                            // Auto-login first user and assign to default group
                            $group_model = new Group($this->db);
                            $stmt = $group_model->read();
                            if ($first_group = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $group_model->id = $first_group['id'];
                                $group_model->addMember($new_user->id, 'owner');
                            }
                            $this->auth->login($username, $password);
                            header('Location: index.php?action=dashboard&message=Welcome! Account created successfully');
                            exit();
                        } else {
                            $success = 'User created successfully';
                        }
                    } else {
                        $error = 'Failed to create user account';
                    }
                }
            }
        }

        include '../src/views/auth/register.php';
    }

    public function userManagement() {
        $this->auth->requireAdmin();
        
        $current_user = $this->auth->getCurrentUser();
        
        // Get users
        $user = new User($this->db);
        $users = $user->read();
        
        // Get groups
        $group_model = new Group($this->db);
        $groups_stmt = $group_model->readWithDetails();
        $groups = [];
        while ($row = $groups_stmt->fetch(PDO::FETCH_ASSOC)) {
            $groups[] = $row;
        }
        
        include '../src/views/user_management.php';
    }
    
    public function users() {
        $this->auth->requireAdmin();

        $user = new User($this->db);
        $users = $user->read();
        $current_user = $this->auth->getCurrentUser();

        include '../src/views/users/index.php';
    }

    public function editUser() {
        $this->auth->requireAdmin();

        $user_id = $_GET['id'] ?? 0;
        $user = new User($this->db);
        $user->id = $user_id;

        $error = '';
        $success = '';

        if (!$user->readOne()) {
            header('Location: index.php?action=users&error=User not found');
            exit();
        }

        if ($_POST) {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($username) || empty($email)) {
                $error = 'Username and email are required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                // Check for duplicates (excluding current user)
                $check_user = new User($this->db);
                if ($check_user->findByUsername($username) && $check_user->id != $user->id) {
                    $error = 'Username already exists';
                } elseif ($check_user->findByEmail($email) && $check_user->id != $user->id) {
                    $error = 'Email already exists';
                } else {
                    $user->username = $username;
                    $user->email = $email;
                    $user->first_name = $first_name;
                    $user->last_name = $last_name;
                    $user->role = $role;
                    $user->is_active = $is_active;

                    if ($user->update()) {
                        $success = 'User updated successfully';
                    } else {
                        $error = 'Failed to update user';
                    }
                }
            }
        }

        $current_user = $this->auth->getCurrentUser();
        include '../src/views/users/edit.php';
    }

    public function deleteUser() {
        $this->auth->requireAdmin();

        $user_id = $_GET['id'] ?? 0;
        $current_user = $this->auth->getCurrentUser();

        if ($user_id == $current_user->id) {
            header('Location: index.php?action=users&error=Cannot delete your own account');
            exit();
        }

        $user = new User($this->db);
        $user->id = $user_id;

        if ($user->delete()) {
            header('Location: index.php?action=users&message=User deleted successfully');
        } else {
            header('Location: index.php?action=users&error=Failed to delete user');
        }
        exit();
    }

    public function profile() {
        $this->auth->requireLogin();

        $current_user = $this->auth->getCurrentUser();
        $error = '';
        $success = '';

        if ($_POST) {
            if (isset($_POST['update_profile'])) {
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');

                if (empty($email)) {
                    $error = 'Email is required';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address';
                } else {
                    // Check if email is already in use by another user
                    $check_user = new User($this->db);
                    if ($check_user->findByEmail($email) && $check_user->id != $current_user->id) {
                        $error = 'Email already exists';
                    } else {
                        $current_user->first_name = $first_name;
                        $current_user->last_name = $last_name;
                        $current_user->email = $email;

                        if ($current_user->update()) {
                            $success = 'Profile updated successfully';
                        } else {
                            $error = 'Failed to update profile';
                        }
                    }
                }
            } elseif (isset($_POST['change_password'])) {
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                if (empty($current_password) || empty($new_password)) {
                    $error = 'Please fill in all password fields';
                } elseif (!$current_user->verifyPassword($current_password)) {
                    $error = 'Current password is incorrect';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password must be at least 6 characters long';
                } else {
                    if ($current_user->updatePassword($new_password)) {
                        $success = 'Password changed successfully';
                    } else {
                        $error = 'Failed to change password';
                    }
                }
            }
        }

        // Get user sessions
        $sessions = $this->auth->getUserSessions($current_user->id);

        include '../src/views/users/profile.php';
    }

    public function revokeSession() {
        $this->auth->requireLogin();

        $session_id = $_POST['session_id'] ?? '';
        $current_user = $this->auth->getCurrentUser();

        if ($this->auth->revokeSession($session_id, $current_user->id)) {
            header('Location: index.php?action=profile&message=Session revoked successfully');
        } else {
            header('Location: index.php?action=profile&error=Failed to revoke session');
        }
        exit();
    }

    public function revokeAllSessions() {
        $this->auth->requireLogin();

        $current_user = $this->auth->getCurrentUser();
        
        if ($this->auth->revokeAllSessions($current_user->id, true)) {
            header('Location: index.php?action=profile&message=All other sessions revoked successfully');
        } else {
            header('Location: index.php?action=profile&error=Failed to revoke sessions');
        }
        exit();
    }

    public function accessDenied() {
        include '../src/views/auth/access_denied.php';
    }
}
?>