<?php
$userToEdit = new User($sqlDataBase);
$quiz = new Quiz($sqlDataBase);
$quizResults = new QuizResults($sqlDataBase);
$selectedQuiz = 0;
if(isset($_GET['quiz_id']))
{
    $selectedQuiz = $_GET['quiz_id'];
    $quizResultsList = $quizResults->UsersQuizResultsList($_GET['quiz_id']);
}



$quizList = $quiz->ListAllQuizzes();

echo "<div class=\"panel panel-primary\">";
echo "<div class=\"panel-heading\"><h3>User Quiz Results</h3></div>";
echo "<div class=\"panel-body\">";
//Form starts
echo "<form action=\"index.php?p=users_results\" method=\"get\">";

//Quiz results selection
echo "<table class=\"table\">";
foreach($quizList as $id=>$quizInfo)
{
    echo "<tr ";
    if($quizInfo['quiz_id']==$selectedQuiz)
    {
        echo "class=\"info\"";
    }
    echo "><td><b >".$quizInfo['quiz_text']."</b></td><td><a href=\"index.php?p=users_results&quiz_id=".$quizInfo['quiz_id']."\">View Results</a></td></tr>";
}
echo "</table>";
echo "<div class=\"panel panel-info\">";
echo "<div class=\"panel-heading\"><h3>Results</h3></div>";
echo "<div class=\"panel-body\">";
if($quizResultsList)
{
    echo "<table class=\"table\">";
    echo "<tr><th >User</th><th>Status</th><th>Grade</th><th>Date Completed</th></tr>";
    foreach($quizResultsList as $id=>$quizResultsInfo)
    {
        $quizResultsInfo['user_name'];
        if($quizResultsInfo['status']==QuizResults::PASSED)
        {
            echo "<tr class=\"success\">";
        }
        elseif($quizResultsInfo['status']==QuizResults::FAILED)
        {
            echo "<tr class=\"danger\">";
        }
        else{
            echo "<tr>";
        }

        echo "<td>".$quizResultsInfo['user_name']."</td>
                <td>".$quizResults->getStatusText($quizResultsInfo['status'])."</td>
                <td><a href=\"index.php?p=user_results_questions&quiz_results=".$quizResultsInfo['quiz_results_id']."\">". ( ($quizResultsInfo['status']==QuizResults::PASSED || $quizResultsInfo['status']==QuizResults::FAILED)?round((($quizResultsInfo['correct_points'] / $quizResultsInfo['total_points'] )* 100),2)."%":"n/a" )."</a></td>
                <td>".( ($quizResultsInfo['status']==QuizResults::PASSED)?date('m-d-Y',strtotime($quizResultsInfo['complete_date'])):"" ) ."</td>
                </tr>";
    }
    echo "</table>";
}
echo "</form>";
echo "</div></div>";
?>
</div>
</div>

