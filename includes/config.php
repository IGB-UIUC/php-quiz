<?php

//MySQL settings
@define ('DB_USER','training_user');
@define ('DB_PASSWORD','SomePassword');
@define ('DB_HOST','localhost');
@define ('DB_NAME','training');

//LDAP Settings
@define ('LDAP_HOST','authen.igb.uiuc.edu');
@define ('LDAP_PEOPLE_DN', 'ou=people,dc=XXX,dc=XXX,dc=XXX');
@define ('LDAP_GROUP_DN', 'ou=group,dc=XXX,dc=XXX,dc=XXX');
@define ('LDAP_SSL','0');
@define ('LDAP_PORT','389');

@define ('DEFAULT_PAGE','exam_list');

@define ('UPLOAD_DIR','uploads/');
@define ('DEFAULT_QUESTION_POINTS',1);
@define ('DEFAULT_PASS_SCORE',0);

/**
 * PAGES explanation
 * first element is the GET parameter to match page name to
 * Second element is the permissions level required for page
 * "all" for everyone,
 * ":auth" for authenticated users only,
 * "admin" for admins only
 * the last element is the path to the include file of the page
 */
$PAGES = array(

    "login"=>array("perm"=>"all","path"=>"includes/login.php"),
    "exam_list"=>array("perm"=>"auth","path"=>"includes/quiz_list.php"),
    "exam"=>array("perm"=>"auth","path"=>"includes/quiz.php"),
    "quizzes"=>array("perm"=>"admin","path"=>"includes/quiz_list_admin.php"),
    "results"=>array("perm"=>"admin","path"=>"includes/results.php"),
    "add_quiz"=>array("perm"=>"admin","path"=>"includes/add_quiz.php"),
    "edit_quiz"=>array("perm"=>"admin","path"=>"includes/edit_quiz.php"),
    "add_question"=>array("admin"=>"auth","path"=>"includes/add_question.php"),
    "users_results"=>array("perm"=>"admin","path"=>"includes/users_results.php"),
    "edit_question"=>array("perm"=>"admin","path"=>"includes/edit_question.php"),
    "admin"=>array("perm"=>"admin","path"=>"includes/admin.php"),
    "permission"=>array("perm"=>"admin","path"=>"includes/permission.php"),
    "user_results_questions"=>array("perm"=>"admin","path"=>"includes/user_results_questions.php")
);

?>