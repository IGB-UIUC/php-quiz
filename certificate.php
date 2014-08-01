<?php
/*
This file is part of php-quiz.

    php-quiz is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    php-quiz is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with php-quiz.  If not, see <http://www.gnu.org/licenses/>.
*/

//Load initial configuration
include ("includes/config.php");

//Load php class auto loader
include ("includes/auto_load_class.php");

//Load PDO database object
include ("includes/connect.inc.php");
if(isset($_POST['key']) && isset($_POST['quiz_results_id']) && isset($_POST['user_id']))
{

    $user = new User($sqlDataBase);
    $user->LoadUser($_POST['user_id']);

    if($user->getAuthKey() == $_POST['key'] && $user->getAuthKey()!="")
    {
        $quizResults = new QuizResults($sqlDataBase);
        $quiz = new Quiz($sqlDataBase);

        $quizResults->LoadQuizResults($_POST['quiz_results_id']);
        $quiz->LoadQuiz($quizResults->getQuizId());

        if($quizResults->getUserId() == $user->getUserId() && $user->getUserId()>0 && $quizResults->getUserId()>0)
        {
            echo "<div style=\"width:800px; height:600px; padding:20px; text-align:center; border: 10px solid #787878\">
                <div style=\"width:750px; height:550px; padding:20px; text-align:center; border: 5px solid #787878\">
                    <span style=\"font-size:50px; font-weight:bold\">Certificate of Completion</span>
                    <br><br>
                    <span style=\"font-size:25px\"><i>This is to certify that</i></span>
                    <br><br>
                    <span style=\"font-size:30px\"><b>".$user->getUserName()."</b></span><br/><br/>
                    <span style=\"font-size:25px\"><i>has completed safety and compliance training for</i></span> <br/><br/>
                    <span style=\"font-size:30px\">".$quiz->getQuizName()."</span><br/><br/>
                    <span style=\"font-size:20px\">with a score of <b>".( ($quizResults->getCorrectPoints() / $quizResults->getTotalPoints()) * 100 )."%</b></span> <br/><br/><br/><br/>
                    <span style=\"font-size:25px\"><i>Completion Date</i></span><br>
                    <span style=\"font-size:30px\">".date('j.n.Y',strtotime($quizResults->getCompleteDate()))."</span>
                </div>
            </div>";
        }
    }
}
?>
