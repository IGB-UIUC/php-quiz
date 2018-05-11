<?php

//Load initial configuration
require_once ("includes/main.inc.php");

//Start certificate if a key, and quiz result and a user is given
if(isset($_POST['key']) && isset($_POST['quiz_results_id']) && isset($_POST['user_id']))
{

    $user = new User($sqlDataBase);
    $user->LoadUser($_POST['user_id']);

    //Make sure the key matches the user's key
    if($user->getAuthKey() == $_POST['key'] && $user->getAuthKey()!="")
    {
        $quizResults = new QuizResults($sqlDataBase);
        $quiz = new Quiz($sqlDataBase);

        $quizResults->LoadQuizResults($_POST['quiz_results_id']);
        $quiz->LoadQuiz($quizResults->getQuizId());

        if($quizResults->getUserId() == $user->getUserId() && $user->getUserId()>0 && $quizResults->getUserId()>0)
        {
            //Print certificate to the screen with user information
            echo "<div style=\"width:800px; height:600px; padding:20px; text-align:center; border: 10px solid #787878\">
                <div style=\"width:750px; height:550px; padding:20px; text-align:center; border: 5px solid #787878\">
                    <span style=\"font-size:50px; font-weight:bold\">Certificate of Completion</span>
                    <br><br>
                    <span style=\"font-size:25px\"><i>This is to certify that</i></span>
                    <br><br>
                    <span style=\"font-size:30px\"><b>".$user->getUserName()."</b></span><br/><br/>
                    <span style=\"font-size:25px\"><i>has completed safety and compliance training for</i></span> <br/><br/>
                    <span style=\"font-size:30px\">".$quiz->getQuizName()."</span><br/><br/>
                    <span style=\"font-size:25px\"><i>Completion Date</i></span><br>
                    <span style=\"font-size:30px\">".date('j.n.Y',strtotime($quizResults->getCompleteDate()))."</span>
                </div>
            </div>";
        }
    }
}
?>
